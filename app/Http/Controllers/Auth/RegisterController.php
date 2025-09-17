<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\InitialVerifyEmail;
use App\Mail\VerifyEmail;
use App\Models\MasterPerson;
use App\Models\PersonUserInfo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\PersonHopeWorkingCondition;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Transliterator;

class RegisterController extends Controller
{
    public function landing()
    {
        return view('landing');
    }
    public function landing_z()
    {
        return view('landing_z');
    }
    public function showEmailCreate()
    {
        // 希望職種
        $bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()
            ->get();

        // 資格グループ
        $groups = DB::table('master_license')
            ->select('group_code', 'group_name')
            ->distinct()
            ->get();

        // 都道府県
        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->get();
        $checkboxOptions = $this->checkboxOptions();

        return view("signin", compact('bigClasses', 'groups', 'prefectures', 'checkboxOptions'));
    }
    public function registration(Request $request)
    {
        Log::info("会員登録開始 (User Registration Started)");

        $messages = [
            'name.required' => 'お名前は必須です。',
            'birthday.required' => '生年月日は必須です。',
            'birthday.digits' => '生年月日は8桁（例: 19900101）で入力してください。',
            'portable_telephone_number.required' => '電話番号は必須です。',
            'mail_address.required' => 'メールアドレスは必須です。',
            'mail_address.email' => '有効なメールアドレス形式で入力してください。',
            'mail_address.unique' => 'このメールアドレスは既に登録されています。',
            'password.required' => 'パスワードは必須です。',
            'password.min' => 'パスワードは3文字以上で入力してください。',
        ];

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'birthday' => 'required|digits:8',
            'portable_telephone_number' => 'required|string',
            'mail_address' => 'required|email|unique:master_person,mail_address',
            'password' => 'required|string|min:3',
        ], $messages);

        $exists = DB::table('master_person')->where('mail_address', $request->mail_address)->exists();
        if ($exists) {
            Log::warning('❌ このメールアドレスは既に登録されています: ' . $request->mail_address);
            return redirect()->back()->withErrors(['mail_address' => 'このメールアドレスは既に登録されています。'])->withInput();
        }

        try {
            DB::beginTransaction();

            $lastId = DB::table('master_person')->max(DB::raw("CAST(SUBSTRING(staff_code, 2) AS UNSIGNED)"));
            $nextId = $lastId ? $lastId + 1 : 1;
            $staffCode = 'S' . str_pad($nextId, 7, '0', STR_PAD_LEFT);

            $person = MasterPerson::create([
                'staff_code' => $staffCode,
                'mail_address' => $request->mail_address,
                'name' => $request->name,
                'portable_telephone_number' => $request->portable_telephone_number,
                'birthday' => Carbon::createFromFormat('Ymd', $request->birthday)->format('Y-m-d'),
                'age' => Carbon::parse($request->birthday)->age,
                'regist_commit' => 1,
            ]);

            PersonUserInfo::updateOrCreate(
                ['staff_code' => $staffCode],
                [
                    'password' => strtoupper(md5($request->password)),
                    'regist_commit' => 1,
                    'created_at' => now(),
                ]
            );

            DB::commit();

            Auth::login($person);
            session(['registered' => true]);

            try {
                Mail::to($person->mail_address)->send(new VerifyEmail($person));
            } catch (\Exception $e) {
                Log::error("メール送信エラー: " . $e->getMessage());
            }

            DB::table('log_person_signin')->insert([
                'staff_code' => $staffCode,
                'created_at' => now(),
                'update_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', '登録完了！希望条件を入力してください。')
                ->with('scrollTo', 'registResume');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ 登録エラー: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => '登録に失敗しました。'])->withInput();
        }
    }

    private function checkboxOptions()
    {
        return [
            'inexperienced_person_flag' => '未経験者OK',
            'balancing_work_flag' => '仕事と生活のバランス',
            'ui_turn_flag' => 'UIターン',
            'many_holiday_flag' => '休日120日',
            'flex_time_flag' => 'フレックス',
            'near_station_flag' => '駅近5分',
            'no_smoking_flag' => '禁煙分煙',
            'newly_built_flag' => '新築',
            'landmark_flag' => '高層ビル',
            'company_cafeteria_flag' => '社員食堂',
            'short_overtime_flag' => '残業少なめ',
            'maternity_flag' => '産休育休',
            'dress_free_flag' => '服装自由',
            'mammy_flag' => '主婦(夫)',
            'fixed_time_flag' => '固定時間勤務',
            'short_time_flag' => '短時間勤務',
            'handicapped_flag' => '障がい者歓迎',
            'rent_all_flag' => '住宅全額補助',
            'rent_part_flag' => '住宅一部補助',
            'meals_flag' => '食事付き',
            'meals_assistance_flag' => '食事補助',
            'training_cost_flag' => '研修費用支給',
            'entrepreneur_cost_flag' => '起業補助',
            'money_flag' => '金銭補助',
            'land_shop_flag' => '店舗提供',
            'appointment_flag' => '正社員登録',
            'license_acquisition_support_flag' => '資格取得支援あり'
            //'find_job_festive_flag' => '就職祝金',//20250402
        ];
    }
    // public function searchMatchingJobs(Request $request)
    // {
    //     $staffCode = Auth::user()->staff_code;

    //     Log::info('🔍 searchMatchingJobs() started', ['request' => $request->all()]);

    //     $filters = $request->all();
    //     $query = DB::table('job_order')
    //         ->leftJoin('job_supplement_info', 'job_order.order_code', '=', 'job_supplement_info.order_code')
    //         ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
    //         ->leftJoin('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
    //         ->leftJoin('master_code', function ($join) {
    //             $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
    //                 ->where('master_code.category_code', 'Prefecture');
    //         })
    //         ->where('job_order.public_flag', 1)
    //         ->where('job_order.order_progress_type', 1)
    //         ->where('job_order.public_limit_day', '>=', now());

    //     // 🔹 FILTER: 職種 + 職種タイプ
    //     if ($request->filled('big_class_code') && $request->filled('job_category')) {
    //         $code = $request->big_class_code . $request->job_category . '000';
    //         Log::info('🔎 Filtering by job_type_code', ['code' => $code]);
    //         $query->whereExists(function ($subquery) use ($code) {
    //             $subquery->select(DB::raw(1))
    //                 ->from('job_job_type')
    //                 ->whereRaw('job_job_type.order_code = job_order.order_code')
    //                 ->where('job_type_code', $code);
    //         });
    //     }

    //     // 🔹 FILTER: 勤務地 (location)
    //     if ($request->has('prefecture_code')) {
    //         $prefCodes = array_filter((array) $request->prefecture_code, fn($c) => !is_null($c) && $c !== '');
    //         if (count($prefCodes) > 0) {
    //             Log::info('📍 Filtering by prefecture', ['prefecture_code' => $prefCodes]);
    //             $query->whereIn('job_working_place.prefecture_code', $prefCodes);
    //         }
    //     }

    //     // 🔹 FILTER: 希望年収
    //     if (!empty($filters['salary']) && $request->salary_type === 'annual') {
    //         $salaryYen = $filters['salary'] * 10000;
    //         Log::info('💰 Filtering by 希望年収', ['希望年収' => $salaryYen]);

    //         $query->where(function ($q) use ($salaryYen) {
    //             $q->where(function ($q2) use ($salaryYen) {
    //                 // ✅ min <= 希望年収 <= max
    //                 $q2->where('job_order.yearly_income_min', '<=', $salaryYen)
    //                     ->where('job_order.yearly_income_max', '>=', $salaryYen);
    //             })
    //                 ->orWhere(function ($q2) use ($salaryYen) {
    //                     // ✅ max is null or 0, and min <= 希望年収 (範囲が開放されている)
    //                     $q2->where('job_order.yearly_income_min', '<=', $salaryYen)
    //                         ->where(function ($q3) {
    //                             $q3->whereNull('job_order.yearly_income_max')
    //                                 ->orWhere('job_order.yearly_income_max', 0);
    //                         });
    //                 })
    //                 ->orWhere(function ($q2) use ($salaryYen) {
    //                     // ✅ min is null or 0, and max >= 希望年収
    //                     $q2->where(function ($q3) {
    //                         $q3->whereNull('job_order.yearly_income_min')
    //                             ->orWhere('job_order.yearly_income_min', 0);
    //                     })->where('job_order.yearly_income_max', '>=', $salaryYen);
    //                 })
    //                 ->orWhere(function ($q2) use ($salaryYen) {
    //                     // ✅ faqat min >= 希望年収 (max mavjud emas)
    //                     $q2->where('job_order.yearly_income_min', '>=', $salaryYen)
    //                         ->where(function ($q3) {
    //                             $q3->whereNull('job_order.yearly_income_max')->orWhere('job_order.yearly_income_max', 0);
    //                         });
    //                 });
    //         });
    //     }

    //     // 🔹 FILTER: 希望時給 (hourly)
    //     elseif (!empty($filters['hourly_wage']) && $request->salary_type === 'hourly') {
    //         $hourlyYen = $filters['hourly_wage'];
    //         Log::info('💸 Filtering by 希望時給', ['希望時給' => $hourlyYen]);

    //         $query->where(function ($q) use ($hourlyYen) {
    //             $q->where(function ($q2) use ($hourlyYen) {
    //                 // ✅ min <= 希望時給 <= max
    //                 $q2->where('job_order.hourly_income_min', '<=', $hourlyYen)
    //                     ->where('job_order.hourly_income_max', '>=', $hourlyYen);
    //             })
    //                 ->orWhere(function ($q2) use ($hourlyYen) {
    //                     // ✅ max is null or 0, and min <= 希望時給 (範囲が開放されている)
    //                     $q2->where('job_order.hourly_income_min', '<=', $hourlyYen)
    //                         ->where(function ($q3) {
    //                             $q3->whereNull('job_order.hourly_income_max')
    //                                 ->orWhere('job_order.hourly_income_max', 0);
    //                         });
    //                 })
    //                 ->orWhere(function ($q2) use ($hourlyYen) {
    //                     // ✅ min is null or 0, and max >= 希望時給
    //                     $q2->where(function ($q3) {
    //                         $q3->whereNull('job_order.hourly_income_min')
    //                             ->orWhere('job_order.hourly_income_min', 0);
    //                     })->where('job_order.hourly_income_max', '>=', $hourlyYen);
    //                 })
    //                 ->orWhere(function ($q2) use ($hourlyYen) {
    //                     // ✅ faqat min >= 希望時給 (max mavjud emas)
    //                     $q2->where('job_order.hourly_income_min', '>=', $hourlyYen)
    //                         ->where(function ($q3) {
    //                             $q3->whereNull('job_order.hourly_income_max')->orWhere('job_order.hourly_income_max', 0);
    //                         });
    //                 });
    //         });
    //     }
    //     // 🔹 FILTER: 補足条件（job_supplement_info flags）
    //     $checkboxFlags = [
    //         'inexperienced_person_flag', 'balancing_work_flag', 'ui_turn_flag', 'many_holiday_flag',
    //         'flex_time_flag', 'near_station_flag', 'no_smoking_flag', 'newly_built_flag', 'landmark_flag',
    //         'company_cafeteria_flag', 'short_overtime_flag', 'maternity_flag', 'dress_free_flag', 'mammy_flag',
    //         'fixed_time_flag', 'short_time_flag', 'handicapped_flag', 'rent_all_flag', 'rent_part_flag',
    //         'meals_flag', 'meals_assistance_flag', 'training_cost_flag', 'entrepreneur_cost_flag',
    //         'money_flag', 'land_shop_flag', 'appointment_flag', 'license_acquisition_support_flag',
    //     ];

    //     foreach ($checkboxFlags as $flag) {
    //         if ($request->has($flag)) {
    //             $query->where("job_supplement_info.$flag", 1);
    //             Log::info("🟢 Filtering by checkbox: $flag");
    //         }
    //     }

    //     // 🔹 FILTER: 年齢
    //     if ($request->filled('birthday')) {
    //         $age = \Carbon\Carbon::parse($request->birthday)->age;
    //         Log::info('🎂 Filtering by age', ['age' => $age]);

    //         $query->where(function ($q) use ($age) {
    //             $q->where(function ($q2) use ($age) {
    //                 $q2->where('job_order.age_min', '<=', $age)
    //                     ->where('job_order.age_max', '>=', $age);
    //             })
    //                 ->orWhere(function ($q2) use ($age) {
    //                     $q2->where('job_order.age_min', '<=', $age)
    //                         ->where(function ($q3) {
    //                             $q3->whereNull('job_order.age_max')->orWhere('job_order.age_max', 0);
    //                         });
    //                 })
    //                 ->orWhere(function ($q2) use ($age) {
    //                     $q2->where('job_order.age_max', '>=', $age)
    //                         ->where(function ($q3) {
    //                             $q3->whereNull('job_order.age_min')->orWhere('job_order.age_min', 0);
    //                         });
    //                 })
    //                 ->orWhere(function ($q2) {
    //                     $q2->where(function ($q3) {
    //                         $q3->whereNull('job_order.age_min')->orWhere('job_order.age_min', 0);
    //                     })->where(function ($q3) {
    //                         $q3->whereNull('job_order.age_max')->orWhere('job_order.age_max', 0);
    //                     });
    //                 });
    //         });
    //     }

    //     // 🔹 給与項目存在チェック (maosh bo‘lmaganlar chiqmasin)
    //     $query->where(function ($q) {
    //         $q->where(function ($q2) {
    //             $q2->whereNotNull('job_order.yearly_income_min')->where('job_order.yearly_income_min', '>', 0);
    //         })->orWhere(function ($q2) {
    //             $q2->whereNotNull('job_order.yearly_income_max')->where('job_order.yearly_income_max', '>', 0);
    //         })->orWhere(function ($q2) {
    //             $q2->whereNotNull('job_order.hourly_income_min')->where('job_order.hourly_income_min', '>', 0);
    //         })->orWhere(function ($q2) {
    //             $q2->whereNotNull('job_order.hourly_income_max')->where('job_order.hourly_income_max', '>', 0);
    //         });
    //     });

    //     // 🔹 Select fields
    //     $jobs = $query->select([
    //         'job_order.order_code',
    //         'job_order.job_type_detail',
    //         'job_order.yearly_income_min',
    //         'job_order.yearly_income_max',
    //         'job_order.hourly_income_min',
    //         'job_order.hourly_income_max',
    //         'job_supplement_info.pr_title1',
    //         'job_supplement_info.pr_contents1',
    //         'master_company.company_name_k',
    //         'master_code.detail as prefecture_name',
    //         'job_order.update_at',
    //     ])
    //     ->addSelect(array_keys($this->checkboxOptions()))
    //     // ->where('job_order.public_flag', 1)
    //     // ->where('job_order.order_progress_type', 1)
    //     // ->where('job_order.public_limit_day', '>=', now())
    //     // ->orderByDesc('job_order.update_at')
    //     ->groupBy('job_order.order_code')
    //     ->get();
    //     $checkboxOptions = $this->checkboxOptions(); // プライベート関数経由

    //     foreach ($jobs as $job) {
    //         $flags = [];
    //         foreach ($checkboxOptions as $field => $label) {
    //             if (!empty($job->$field) && $job->$field == 1) {
    //                 $flags[] = $label;
    //             }
    //         }
    //         $job->selectedFlagsArray = $flags;
    //     }
    //     if (Auth::check()) {
    //         $staffCode = Auth::user()->staff_code;

    //         $existingLog = DB::table('log_person_signin')->where('staff_code', $staffCode)->first();

    //         $commonData = [
    //             'search_big_class_code' => $request->big_class_code ?? null,
    //             'search_job_category' => $request->job_category ?? null,
    //             'filter_salary' => $request->salary ?? null,
    //             'filter_hourly_wage' => $request->hourly_wage ?? null,
    //             'filter_location' => optional(
    //                 DB::table('master_code')
    //                     ->where('category_code', 'Prefecture')
    //                     ->where('code', $request->prefecture_code)
    //                     ->first()
    //             )->detail,
    //             'filter_flags' => json_encode(array_keys(array_filter($request->only(array_keys($this->checkboxOptions()))))),
    //             'update_at' => now(),
    //         ];

    //         if (!$existingLog) {
    //             // 🔹 insert: match_count 初回のみセット
    //             DB::table('log_person_signin')->insert(array_merge([
    //                 'staff_code' => $staffCode,
    //                 'match_count' => $jobs->count(),
    //                 'update_count' => $jobs->count(),
    //                 'created_at' => now(),
    //             ], $commonData));
    //         } else {
    //             // 🔸 update: match_count を更新しない
    //             DB::table('log_person_signin')->where('staff_code', $staffCode)->update(array_merge([
    //                 'update_count' => $jobs->count(),
    //             ], $commonData));
    //         }
    //     }        

    //     Log::info('✅ Matching jobs result', ['count' => $jobs->count()]);

    //     return response()->json(['jobs' => $jobs]);
    // }
    public function searchMatchingJobs(Request $request)
    {
        $staffCode = Auth::user()->staff_code;

        Log::info('🔍 searchMatchingJobs() started', ['request' => $request->all()]);

        $filters = $request->all();
        $checkboxFlags = [
            'inexperienced_person_flag',
            'balancing_work_flag',
            'ui_turn_flag',
            'many_holiday_flag',
            'flex_time_flag',
            'near_station_flag',
            'no_smoking_flag',
            'newly_built_flag',
            'landmark_flag',
            'company_cafeteria_flag',
            'short_overtime_flag',
            'maternity_flag',
            'dress_free_flag',
            'mammy_flag',
            'fixed_time_flag',
            'short_time_flag',
            'handicapped_flag',
            'rent_all_flag',
            'rent_part_flag',
            'meals_flag',
            'meals_assistance_flag',
            'training_cost_flag',
            'entrepreneur_cost_flag',
            'money_flag',
            'land_shop_flag',
            'appointment_flag',
            'license_acquisition_support_flag',
        ];

        $query = DB::table('job_order')
            ->leftJoin('job_supplement_info', 'job_order.order_code', '=', 'job_supplement_info.order_code')
            ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
            ->leftJoin('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
            ->leftJoin('master_code', function ($join) {
                $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                    ->where('master_code.category_code', 'Prefecture');
            })
            ->where('job_order.public_flag', 1)
            ->where('job_order.order_progress_type', 1)
            ->where('job_order.public_limit_day', '>=', now());

        if ($request->filled('big_class_code') && $request->filled('job_category')) {
            $code = $request->big_class_code . $request->job_category . '000';
            Log::info('🔎 Filtering by job_type_code', ['code' => $code]);
            $query->whereExists(function ($subquery) use ($code) {
                $subquery->select(DB::raw(1))
                    ->from('job_job_type')
                    ->whereRaw('job_job_type.order_code = job_order.order_code')
                    ->where('job_type_code', $code);
            });
        }

        $prefectureNames = [];

        if ($request->has('prefecture_code')) {
            $prefCodes = array_filter((array) $request->prefecture_code, fn($c) => !is_null($c) && $c !== '');
            if (count($prefCodes) > 0) {
                Log::info('📍 Filtering by prefecture', ['prefecture_code' => $prefCodes]);

                $query->whereIn('job_working_place.prefecture_code', $prefCodes);

                // 📌 Prefecture name-larini filter_location ga loglash uchun
                $prefectureNames = DB::table('master_code')
                    ->where('category_code', 'Prefecture')
                    ->whereIn('code', $prefCodes)
                    ->pluck('detail')
                    ->toArray();
            }
        }

        if (!empty($filters['salary']) && $request->salary_type === 'annual') {
            $salaryYen = $filters['salary'] * 10000;
            Log::info('💰 Filtering by 希望年収', ['希望年収' => $salaryYen]);

            $query->where(function ($q) use ($salaryYen) {
                $q->where(function ($q2) use ($salaryYen) {
                    $q2->where('job_order.yearly_income_min', '<=', $salaryYen)
                        ->where('job_order.yearly_income_max', '>=', $salaryYen);
                })
                    ->orWhere(function ($q2) use ($salaryYen) {
                        $q2->where('job_order.yearly_income_min', '<=', $salaryYen)
                            ->where(function ($q3) {
                                $q3->whereNull('job_order.yearly_income_max')->orWhere('job_order.yearly_income_max', 0);
                            });
                    })
                    ->orWhere(function ($q2) use ($salaryYen) {
                        $q2->where(function ($q3) {
                            $q3->whereNull('job_order.yearly_income_min')->orWhere('job_order.yearly_income_min', 0);
                        })->where('job_order.yearly_income_max', '>=', $salaryYen);
                    })
                    ->orWhere(function ($q2) use ($salaryYen) {
                        $q2->where('job_order.yearly_income_min', '>=', $salaryYen)
                            ->where(function ($q3) {
                                $q3->whereNull('job_order.yearly_income_max')->orWhere('job_order.yearly_income_max', 0); // ✅ SHU YER
                            });
                    });
            });
        } elseif (!empty($filters['hourly_wage']) && $request->salary_type === 'hourly') {
            $hourlyYen = intval($filters['hourly_wage']);
            Log::info('💸 Filtering by 希望時給', ['希望時給' => $hourlyYen]);

            $query->where(function ($q) use ($hourlyYen) {
                $q->where(function ($q2) use ($hourlyYen) {
                    // ✅ 希望時給 min-max oralig'ida bo'lishi kerak
                    $q2->whereNotNull('job_order.hourly_income_min')
                        ->where('job_order.hourly_income_min', '<=', $hourlyYen)
                        ->whereNotNull('job_order.hourly_income_max')
                        ->where('job_order.hourly_income_max', '>=', $hourlyYen);
                })
                    ->orWhere(function ($q2) use ($hourlyYen) {
                        // ✅ max mavjud emas (開放範囲): min <= 希望時給
                        $q2->whereNotNull('job_order.hourly_income_min')
                            ->where('job_order.hourly_income_min', '<=', $hourlyYen)
                            ->where(function ($q3) {
                                $q3->whereNull('job_order.hourly_income_max')
                                    ->orWhere('job_order.hourly_income_max', 0);
                            });
                    })
                    ->orWhere(function ($q2) use ($hourlyYen) {
                        // ✅ min mavjud emas: max >= 希望時給
                        $q2->where(function ($q3) {
                            $q3->whereNull('job_order.hourly_income_min')
                                ->orWhere('job_order.hourly_income_min', 0);
                        })
                            ->whereNotNull('job_order.hourly_income_max')
                            ->where('job_order.hourly_income_max', '>=', $hourlyYen);
                    });
            });

            // 🔒 faqat maoshi mavjud ishlar (最低一部 ma’lumot bor)
            $query->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('job_order.hourly_income_min')->where('job_order.hourly_income_min', '>', 0);
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('job_order.hourly_income_max')->where('job_order.hourly_income_max', '>', 0);
                });
            });
        }


        foreach ($checkboxFlags as $flag) {
            if ($request->has($flag)) {
                $query->where("job_supplement_info.$flag", 1);
                Log::info("🟢 Filtering by checkbox: $flag");
            }
        }



        // $hasUserInput = $request->filled('big_class_code') || $request->filled('job_category') ||
        //     $request->filled('salary') || $request->filled('hourly_wage') ||
        //     $request->filled('birthday') || $request->has('prefecture_code') ||
        //     collect($request->only($checkboxFlags))->filter()->isNotEmpty();
        $hasUserInput =
            $request->filled('big_class_code') ||
            $request->filled('job_category') ||
            $request->filled('salary') ||
            $request->filled('hourly_wage') ||
            $request->filled('birthday') ||
            (is_array($request->prefecture_code) && count(array_filter($request->prefecture_code)) > 0) ||
            collect($request->only($checkboxFlags))->filter()->isNotEmpty();

        if ($hasUserInput) {
            $query->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('job_order.yearly_income_min')->where('job_order.yearly_income_min', '>', 0);
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('job_order.yearly_income_max')->where('job_order.yearly_income_max', '>', 0);
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('job_order.hourly_income_min')->where('job_order.hourly_income_min', '>', 0);
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('job_order.hourly_income_max')->where('job_order.hourly_income_max', '>', 0);
                });
            });
        }

        $jobs = $query->select([
            'job_order.order_code',
            'job_order.job_type_detail',
            'job_order.yearly_income_min',
            'job_order.yearly_income_max',
            'job_order.hourly_income_min',
            'job_order.hourly_income_max',
            'job_supplement_info.pr_title1',
            'job_supplement_info.pr_contents1',
            'master_company.company_name_k',
            'master_code.detail as prefecture_name',
            'job_order.update_at',
        ])->addSelect(array_keys($this->checkboxOptions()))
            ->groupBy('job_order.order_code')
            ->get();

        $checkboxOptions = $this->checkboxOptions();
        foreach ($jobs as $job) {
            $flags = [];
            foreach ($checkboxOptions as $field => $label) {
                if (!empty($job->$field) && $job->$field == 1) {
                    $flags[] = $label;
                }
            }
            $job->selectedFlagsArray = $flags;
        }
        if (Auth::check()) {
            $staffCode = Auth::user()->staff_code;

            $existingLog = DB::table('log_person_signin')->where('staff_code', $staffCode)->first();

            $commonData = [
                'search_big_class_code' => $request->big_class_code ?? null,
                'search_job_category' => $request->job_category ?? null,
                'filter_salary' => $request->salary ?? null,
                'filter_hourly_wage' => $request->hourly_wage ?? null,
                'filter_location' => implode(', ', $prefectureNames),
                'filter_flags' => json_encode(array_keys(array_filter($request->only(array_keys($this->checkboxOptions()))))),
                'update_at' => now(),
            ];

            if (!$existingLog) {
                // 🔹 insert: match_count 初回のみセット
                DB::table('log_person_signin')->insert(array_merge([
                    'staff_code' => $staffCode,
                    'match_count' => $jobs->count(),
                    'update_count' => $jobs->count(),
                    'created_at' => now(),
                ], $commonData));
            } else {
                // 🔸 update: match_count を更新しない
                DB::table('log_person_signin')->where('staff_code', $staffCode)->update(array_merge([
                    'update_count' => $jobs->count(),
                ], $commonData));
            }
        }

        Log::info('✅ Matching jobs result', ['count' => $jobs->count()]);

        return response()->json(['jobs' => $jobs]);
    }
}
