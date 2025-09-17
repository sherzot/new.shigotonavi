<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomExport;
use App\Exports\AgentExport;
use Illuminate\Support\Facades\Event;

class AgentDashboardController extends Controller
{
    private function getAuthenticatedUser()
    {
        $companyUser = Auth::guard('master_company')->user();
        $agentUser = Auth::guard('master_agent')->user();

        if ($companyUser) {
            return ['companyUser' => $companyUser, 'agentUser' => null];
        }

        if ($agentUser) {
            return ['agentUser' => $agentUser, 'companyUser' => null];
        }

        return redirect()->route('agent.login')->withErrors(['msg' => 'このページを閲覧するにはログインが必要です。']);
    }
    public function getOfferDetail()
    {
        // 現在ログインしているユーザーを確認する
        $companyUser = Auth::guard('master_company')->user();
        // ユーザー認証を取得する
        $authUser = $this->getAuthenticatedUser();

        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }
        if (!$authUser) {
            return redirect()->route('agent.login')->withErrors(['msg' => 'ログインする必要があります。.']);
        }

        $agentUser = $authUser['agentUser'];

        $authUser = Auth::guard('master_agent')->user()->agent_code;

        // このエージェントが所有する企業を検索
        $companyCodes = DB::table('company_agent')
            ->where('agent_code', $authUser)
            ->pluck('company_code');

        // ✅ 有効なオファー (offer_flag = 1)
        // ✅ オファー情報を取得する
        $offers = DB::table('person_offer')
            ->join('job_order', 'person_offer.order_code', '=', 'job_order.order_code')
            ->leftJoin('master_offer_branch', 'job_order.branch_code', '=', 'master_offer_branch.branch_code')
            // ->leftJoin('master_employee', 'master_offer_branch.head_code', '=', 'master_employee.employee_code')
            ->whereIn('job_order.company_code', $companyCodes)
            ->select(
                'person_offer.*',
                'job_order.company_code',
                'job_order.branch_code',
                'master_offer_branch.head_code',
                'master_offer_branch.head_name',
                'master_offer_branch.mail_address'
            )
            ->orderBy('person_offer.update_at', 'desc')
            ->get();

        // ✅ 各オファーのステータスごとに分ける
        $activeOffers = $offers->where('offer_flag', '1');
        $canceledOffers = $offers->where('offer_flag', '2');
        $offerCompletion = $offers->where('offer_flag', '3');

        return view('agent.offercontrol', compact('canceledOffers', 'activeOffers', 'offerCompletion', 'agentUser', 'companyUser'));
    }
    public function confirmCancelOffer(Request $request, $staff_code, $order_code)
    {
        $offer = DB::table('person_offer')
            ->where('staff_code', $staff_code)
            ->where('order_code', $order_code)
            ->where('offer_flag', '1') // Faqat aktiv offerlar
            ->first();

        if (!$offer) {
            return redirect()->route('agent.offercontrol')->withErrors(['error' => 'オファーが見つかりませんでした。']);
        }

        // ✅ `offer_flag = 2` (Agent cancelni tasdiqladi)
        DB::table('person_offer')
            ->where('staff_code', $staff_code)
            ->where('order_code', $order_code)
            ->update([
                'offer_flag' => '2',
                'update_at' => now(),
            ]);

        $user = DB::table('master_person')
            ->where('staff_code', $staff_code)
            ->select('name', 'mail_address')
            ->first();

        if ($user && !empty($user->mail_address)) {
            Mail::raw(
                "{$user->name} さん\n\nあなたのオファーがエージェントによって正式にキャンセルされました。",
                function ($message) use ($user) {
                    $message->subject("オファーキャンセル確定");
                    $message->to($user->mail_address);
                }
            );
        }

        return redirect()->route('agent.offercontrol')->with([
            'success' => "オファーキャンセルを確定しました、新しいオファーができます。",
            'staff_code' => $staff_code,
            'order_code' => $order_code,
        ]);
    }

    public function confirmOfferCompletion(Request $request, $staff_code, $order_code)
    {
        $offer = DB::table('person_offer')
            ->where('staff_code', $staff_code)
            ->where('order_code', $order_code)
            ->where('offer_flag', '1') // Faol offer
            ->first();

        if (!$offer) {
            return redirect()->route('agent.offercontrol')->withErrors(['error' => 'オファーが見つかりませんでした。']);
        }

        // ✅ `offer_flag = 3` (Offer tugadi)
        DB::table('person_offer')
            ->where('staff_code', $staff_code)
            ->where('order_code', $order_code)
            ->update([
                'offer_flag' => '3',
                'update_at' => now(),
            ]);

        $user = DB::table('master_person')
            ->where('staff_code', $staff_code)
            ->select('name', 'mail_address')
            ->first();

        if ($user && !empty($user->mail_address)) {
            $surveyUrl = "https://match.shigotonavi.co.jp/questionnaire";

            Mail::raw(
                "{$user->name} さん\n\nオファーが完了しました。下記のリンクからアンケートを記入してください。\n\n{$surveyUrl}",
                function ($message) use ($user) {
                    $message->subject("オファー完了通知");
                    $message->to($user->mail_address);
                }
            );
        }

        return redirect()->route('agent.offercontrol')->with([
            'success' => "オファーが完了しました、新しいオファーができます。",
            'staff_code' => $staff_code,
            'order_code' => $order_code,
        ]);
    }

    public function showAgentDetails()
    {
        $authUser = $this->getAuthenticatedUser();

        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }

        $companyUser = $authUser['companyUser'];
        $agentUser = $authUser['agentUser'];


        // エージェントがログインしている場合はエージェント情報を取得します
        $agentDetails = $agentUser ? DB::table('master_agent')
            ->select(
                'agent_code',
                'agent_name',
                'agent_frigana',
                'agent_company_name',
                'agent_company_name_f',
                'password',
                'mail_address',
                'sex',
                'birthday',
                'post_u',
                'post_l',
                'prefecture_code',
                'city',
                'town',
                'address',
                'office_telephone_number',
                'portable_telephone_number',
                'branch_code',
                'department',
                'tmp_licenseday',
                'int_licenseday',
                'ent_licenseday',
                'entry_date',
                'retire_date',
                'retire_memo'
            )
            ->where('agent_code', $agentUser->agent_code)
            ->first() : null;

        return view('agent.agent_details', compact('agentUser', 'agentDetails',));
    }

    public function showAgentProfile()
    {
        $authUser = $this->getAuthenticatedUser();
        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }
        $agentUser = $authUser['agentUser'];

        // エージェントがログインしている場合はエージェント情報を取得します
        $agentDetails = $agentUser ? DB::table('master_agent')
            ->select(
                'agent_code',
                'agent_name',
                'agent_frigana',
                'agent_company_name',
                'agent_company_name_f',
                'password',
                'mail_address',
                'sex',
                'birthday',
                'post_u',
                'post_l',
                'prefecture_code',
                'city',
                'town',
                'address',
                'office_telephone_number',
                'portable_telephone_number',
                'branch_code',
                'department',
                'tmp_licenseday',
                'int_licenseday',
                'ent_licenseday',
                'entry_date',
                'retire_date',
                'retire_memo'
            )
            ->where('agent_code', $agentUser->agent_code)
            ->first() : null;
        return view('agent.agent_profile', compact('agentUser'));
    }

    public function showLinkedCompanies(Request $request)
    {
        $authUser = $this->getAuthenticatedUser();

        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }

        $agentUser = $authUser['agentUser'];
        $linkedCompanyCodes = collect(); // Sahifa ochilganda bo'sh

        if ($agentUser && $agentUser->agent_code) {
            if ($request->has('query') && !empty(trim($request->query('query')))) {
                $search = $request->query('query');

                $query = DB::table('company_agent')
                    ->join('master_company', 'company_agent.company_code', '=', 'master_company.company_code')
                    ->where('company_agent.agent_code', $agentUser->agent_code)
                    ->where(function ($q) use ($search) {
                        $q->where('master_company.company_code', 'LIKE', "%{$search}%")
                            ->orWhere('master_company.company_name_k', 'LIKE', "%{$search}%")
                            ->orWhere('master_company.lis_person_code', 'LIKE', "%{$search}%")
                            ->orWhere('master_company.lis_person_name', 'LIKE', "%{$search}%");
                    })
                    ->select(
                        'master_company.company_code',
                        'master_company.company_name_k',
                        'master_company.lis_person_name',
                        'master_company.lis_person_code',
                        'master_company.created_at',
                        'master_company.updated_at',
                        'master_company.keiyaku_ymd',
                        'master_company.intbase_contract_day'
                    )
                    ->orderBy('master_company.updated_at', 'desc');

                $linkedCompanyCodes = $query->paginate(9)->appends(['query' => $search]);
            }

            return view('agent.linked_companies', compact('linkedCompanyCodes', 'agentUser'));
        }

        return redirect()->route('unauthorized');
    }
    public function showCompanyDetail($companyCode)
    {
        $authUser = $this->getAuthenticatedUser();
        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }

        $agentUser = $authUser['agentUser'];

        // Ushbu agentga tegishli bo‘lgan kompaniyalargina ko‘rsatiladi
        $isLinked = DB::table('company_agent')
            ->where('agent_code', $agentUser->agent_code)
            ->where('company_code', $companyCode)
            ->exists();

        if (!$isLinked) {
            return redirect()->route('unauthorized');
        }

        $company = DB::table('master_company')->where('company_code', $companyCode)->first();

        return view('agent.company_detail', compact('company', 'agentUser'));
    }
    public function showLinkedJobs(Request $request)
    {
        $linkedJobs = collect(); // default: empty
        $publicCount = 0;
        $endCount = 0;
        $orderType1 = 0;
        $orderType2 = 0;
        $orderType3 = 0;
        $publishedCount = 0;
        $expiredCount = 0;
        $totalCount = 0;

        $authUser = $this->getAuthenticatedUser();
        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }

        $agentUser = $authUser['agentUser'];
        $linkedJobs = collect(); // default: empty

        if ($agentUser && $agentUser->agent_code) {
            // Faqat formadan query yuborilgan bo‘lsa izlash
            if ($request->filled('query')) {
                $search = trim($request->query('query'));

                $linkedCompanyCodes = DB::table('company_agent')
                    ->where('agent_code', $agentUser->agent_code)
                    ->pluck('company_code');

                $query = DB::table('job_order')
                    ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
                    ->leftJoin('log_access_history_order', 'job_order.order_code', '=', 'log_access_history_order.order_code')
                    ->leftJoin('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
                    ->leftJoin('master_code', function ($join) {
                        $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                            ->where('master_code.category_code', '=', 'Prefecture');
                    })
                    ->leftJoin('master_employee', 'job_order.employee_code', '=', 'master_employee.employee_code')
                    ->whereIn('job_order.company_code', $linkedCompanyCodes)
                    ->where(function ($q) use ($search) {
                        $q->where('job_order.order_code', 'LIKE', "%{$search}%")
                            ->orWhere('job_order.job_type_detail', 'LIKE', "%{$search}%")
                            ->orWhere('job_order.company_code', 'LIKE', "%{$search}%")
                            ->orWhere('master_company.company_name_k', 'LIKE', "%{$search}%")
                            ->orWhere('job_order.employee_code', 'LIKE', "%{$search}%")
                            ->orWhere('master_company.lis_person_name', 'LIKE', "%{$search}%")
                            ->orWhere('master_employee.employee_name', 'LIKE', "%{$search}%")
                            ->orWhere('master_code.detail', 'LIKE', "%{$search}%") // 都道府県
                            ->orWhere('job_order.job_type_detail', 'LIKE', "%{$search}%"); // 職種
                        // ->orWhere('job_order.skill', 'LIKE', "%{$search}%");
                    })
                    ->select(
                        'job_order.order_code',
                        'job_order.order_type',
                        'job_order.company_code',
                        'job_order.public_flag',
                        'master_company.company_name_k',
                        'master_company.lis_person_name',
                        'master_employee.employee_name',
                        'job_order.employee_code',
                        'job_order.created_at',
                        'job_order.update_at',
                        'job_order.job_type_detail',
                        DB::raw('COALESCE(SUM(log_access_history_order.browse_cnt), 0) as browse_cnt'),
                        DB::raw('GROUP_CONCAT(DISTINCT master_code.detail SEPARATOR ", ") as all_prefectures'),
                        DB::raw('DATE(job_order.public_day) as public_day'),
                        DB::raw('DATE(job_order.public_limit_day) as public_limit_day')
                    )
                    ->groupBy(
                        'job_order.order_code',
                        'job_order.order_type',
                        'job_order.job_type_detail',
                        'job_order.public_flag',
                        'job_order.public_day',
                        'job_order.public_limit_day',
                        'job_order.created_at',
                        'job_order.update_at',
                        'master_company.company_name_k',
                        'master_company.lis_person_name',
                        'master_employee.employee_name',
                        'job_order.employee_code',
                        'job_order.company_code'
                    )

                    ->orderByDesc('job_order.update_at');

                $allJobs = (clone $query)->get(); // query ni klonlab buzmaymiz

                $totalCount = $allJobs->count();
                $linkedJobs = $query->paginate(9)->appends(['query' => $search]);
                // 🔁 各求人票について "期限切れかどうか" が追加されます。
                $linkedJobs = $linkedJobs->setCollection(
                    $linkedJobs->getCollection()->transform(function ($job) {
                        $job->is_expired = \Carbon\Carbon::createFromFormat('Y-m-d', $job->public_limit_day)->isPast();
                        return $job;
                    })
                );
                // 🔢 件数を集計
                $publicCount = $allJobs->where('public_flag', 1)->count();
                $endCount = $allJobs->where('public_flag', 0)->count();

                $orderType1 = $allJobs->where('order_type', 1)->count(); // 派遣
                $orderType2 = $allJobs->where('order_type', 2)->count(); // 紹介
                $orderType3 = $allJobs->where('order_type', 3)->count(); // 紹介予定派遣

                $publishedCount = $allJobs->filter(
                    fn($job) =>
                    \Carbon\Carbon::createFromFormat('Y-m-d', $job->public_limit_day)->isFuture()
                )->count(); // 記載

                $expiredCount = $allJobs->filter(
                    fn($job) =>
                    \Carbon\Carbon::createFromFormat('Y-m-d', $job->public_limit_day)->isPast()
                )->count(); // 非記載
            }
            return view('agent.linked_jobs', compact(
                'linkedJobs',
                'agentUser',
                'publicCount',
                'endCount',
                'orderType1',
                'orderType2',
                'orderType3',
                'publishedCount',
                'expiredCount',
                'totalCount',
            ));
        }

        return redirect()->route('unauthorized');
    }

    public function showCompanyJobDetails($order_code)
    {
        // ユーザー認証を取得する
        $authUser = $this->getAuthenticatedUser();

        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }

        $agentUser = $authUser['agentUser'];

        // 求人情報を取得する
        $jobDetails = DB::table('job_order')
            ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
            ->select(
                'job_order.order_code',
                'job_order.order_type',
                'job_order.job_type_detail',
                'job_order.business_detail',
                'job_order.hourly_income_min',
                'job_order.hourly_income_max',
                'job_order.yearly_income_min',
                'job_order.yearly_income_max',
                'master_company.company_name_k',
                'master_company.company_code',
                'master_company.prefecture',
                'master_company.city_k',
                'master_company.town',
                'master_company.address',
                'master_company.telephone_number'
            )
            ->where('job_order.order_code', $order_code)
            ->first();

        // 情報がない場合はリダイレクトします
        if (!$jobDetails) {
            return redirect()->route('agent.dashboard')
                ->with('error', '求人情報が見つかりません。');
        }

        // 📌 `job_working_place`テーブルから都道府県データを取得する
        $all_prefectures = DB::table('job_working_place')
            ->join('master_code', 'job_working_place.prefecture_code', '=', 'master_code.code')
            ->where('job_working_place.order_code', $order_code)
            ->where('master_code.category_code', 'Prefecture')
            ->select('master_code.detail as prefecture', 'job_working_place.city as city')
            ->get();

        // 📌 `job_license`テーブルからデータを取得する
        $licenses = DB::table('job_license')
            ->join('master_license', function ($join) {
                $join->on('job_license.group_code', '=', 'master_license.group_code')
                    ->on('job_license.category_code', '=', 'master_license.category_code')
                    ->on('job_license.code', '=', 'master_license.code');
            })
            ->where('job_license.order_code', $order_code)
            ->select(
                'job_license.group_code',
                'job_license.category_code',
                'job_license.code',
                'master_license.category_name',
                'master_license.name'
            )
            ->get()
            ->toArray();

        // 📌 仕事に必要なスキルを取得
        $skills = DB::table('job_skill')
            ->where('order_code', $order_code)
            ->get();

        // 📌 `job_note`データを取得
        $jobNoteData = DB::table('job_note')
            ->where('order_code', $order_code)
            ->where('category_code', 'Note') // 静的値 'Note'
            ->where('code', 'BestMatch')    // 静的値 'BestMatch'
            ->first();

        // 📌 ユーザーが選択した都道府県
        $selectedPrefectures = DB::table('job_working_place')
            ->where('order_code', $order_code)
            ->pluck('prefecture_code')
            ->toArray();

        $workingPlaces = DB::table('job_working_place')
            ->where('order_code', $order_code)
            ->get();

        // 📌 `job_working_condition`テーブルからデータを取得する
        $jobWorkingCondition = DB::table('job_working_condition')
            ->where('order_code', $order_code)
            ->orderBy('id', 'desc')
            ->first();

        // 📌 都道府県一覧
        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->select('code', 'detail')
            ->get();

        return view('agent.company_job_details', compact(
            'jobDetails',
            'agentUser',
            'all_prefectures',
            'licenses',
            'skills',
            'jobNoteData',
            'selectedPrefectures',
            'workingPlaces',
            'prefectures',
            'jobWorkingCondition'
        ));
    }

    // staff 検索フォーム
    public function showUserForm()
    {
        $authUser = $this->getAuthenticatedUser();

        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }

        $agentUser = $authUser['agentUser'];

        return view('agent.user_search', compact('agentUser'));
    }

    // staff 検索結果の表示
    public function searchUser(Request $request)
    {
        DB::enableQueryLog();
        // ユーザー認証を取得する
        $authUser = $this->getAuthenticatedUser();

        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }

        $agentUser = $authUser['agentUser'];
        //dd($request->mail);
        try {
            if ($request->staff_code) {
                $staffCode = $request->staff_code;
            } elseif ($request->tell) {
                $user = DB::table('master_person')
                    ->select('staff_code')
                    ->where('portabletelephone_number', $request->tel)
                    ->first();
                if ($user) {
                    $staffCode = $user->staff_code;
                }
            } elseif ($request->mail) {
                $user = DB::table('master_person')
                    ->select('staff_code')
                    ->where('mail_address', $request->mail) //mail_address
                    ->first();
                if ($user) {
                    $staffCode = $user->staff_code;
                }
            } // end if($request->staff_code)
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'ユーザー情報が見つかりませんでした。']);
            //return back()->withErrors($e->errors()); 
        }

        //if(!isset($user) || !$user) {
        //	//dd($user);
        //	return redirect()->back()->withErrors(['msg' => 'ユーザー情報が見つかりませんでした。']);
        //	//return back()->withErrors($e->errors());
        //}
        //dd(DB::getQueryLog());
        //dd($user);	

        //$this->getUserDetail($staffCode);    
        // ✅ Foydalanuvchi ma'lumotlarini olish
        $user = DB::table('master_person')
            ->where('staff_code', $staffCode)
            ->select(
                'staff_code',
                'name',
                'name_f',
                'mail_address',
                'portable_telephone_number'
            )
            ->first();

        if (!$user) {
            return redirect()->back()->withErrors(['msg' => 'ユーザー情報が見つかりませんでした。']);
        }

        // ✅ 希望職種 (希望職掌希望勤務地)
        $jobTypeDetail = DB::table('person_hope_job_type')
            ->where('staff_code', $user->staff_code)
            ->value('job_type_detail');

        // ✅ 希望勤務地 (希望勤務地希望勤務地)
        $jobWorkingPlaces = DB::table('person_hope_working_place')
            ->join('master_code', 'person_hope_working_place.prefecture_code', '=', 'master_code.code')
            ->where('master_code.category_code', 'Prefecture')
            ->where('person_hope_working_place.staff_code', $user->staff_code)
            ->pluck('master_code.detail'); // Barcha prefecture-larni olish

        // ✅ 希望年収 (年間収入)
        $yearlyIncome = DB::table('person_hope_working_condition')
            ->where('staff_code', $user->staff_code)
            ->value('yearly_income_min');
        // ✅ 希望時給 (時給)
        $hourlyIncome = DB::table('person_hope_working_condition')
            ->where('staff_code', $user->staff_code)
            ->value('hourly_income_min');

        // ✅ 保有資格 (資格情報)
        $personLicenses = DB::table('person_license')
            ->join('master_license', function ($join) {
                $join->on('person_license.group_code', '=', 'master_license.group_code')
                    ->on('person_license.category_code', '=', 'master_license.category_code')
                    ->on('person_license.code', '=', 'master_license.code');
            })
            ->where('person_license.staff_code', '=', $user->staff_code)
            ->select(
                'master_license.group_name',
                'master_license.category_name',
                'master_license.name as license_name'
            )
            ->distinct() // 重複を避ける
            ->get();

        $careers = DB::table('person_career_history')
            ->select('staff_code', 'id', 'company_name', 'job_type_detail', 'entry_day', 'retire_day')
            ->where('staff_code', $user->staff_code)
            ->get();

        $schools = DB::table('person_educate_history as his')
            ->join('master_code as mcd', function ($join) {
                $join->on('mcd.code', '=', 'his.school_type_code')
                    ->where('mcd.category_code', '=', 'SchoolType');
            })
            ->select('his.school_name as school_name', 'his.entry_day as entry_day', 'his.graduate_day as graduate_day', 'his.speciality as speciality', 'mcd.detail as kind')
            ->where('staff_code', $user->staff_code)
            ->get();


        return view('agent.user_detail', compact('user', 'jobTypeDetail', 'jobWorkingPlaces', 'yearlyIncome', 'hourlyIncome', 'personLicenses', 'agentUser', 'careers', 'schools'));
    } // end function

    // staff 登録日付検索一覧の表示
    public function listUser(Request $request)
    {
        DB::enableQueryLog();
        // ユーザー認証を取得する
        $authUser = $this->getAuthenticatedUser();

        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }

        $agentUser = $authUser['agentUser'];

        if (!$request->search_date) {
            return redirect()->back()->withErrors(['msg' => '検索する登録日がみつかりません。']);
        } else {
            $searchDay = $request->search_date;
            $tNextDay = strtotime($searchDay . ' +1 day');
            //echo date('Y-m-d', $tNextDay) . "\n";
            $searchNextDay = date('Y-m-d H:i:s', $tNextDay);
        }

        $users = DB::table('master_person as mp')
            ->leftJoin('log_person_signin as lps', 'mp.staff_code', '=', 'lps.staff_code')
            ->leftJoin('person_hope_job_type as phj', 'mp.staff_code', '=', 'phj.staff_code')
            ->select('mp.staff_code', 'mp.name', 'phj.job_type_detail', 'mp.created_at', 'lps.match_count', 'lps.update_count', 'lps.detail_count')
            ->where('mp.created_at', '>=', $searchDay . ' 00:00:00')
            ->where('mp.created_at', '<', $searchNextDay)
            ->get();
        //dd(DB::getQueryLog());

        return view('agent.user_list', compact('users', 'searchDay', 'agentUser'));
    } // end function listUser

    //登録日ごとのエクセル作成
    public function dailySheet(Request $request)
    {
        DB::enableQueryLog();
        // ユーザー認証を取得する
        $authUser = $this->getAuthenticatedUser();

        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }
        if (!$request->select_date) {
            return redirect()->back()->withErrors(['msg' => '検索する登録日がみつかりません。']);
        } else {
            $selectDay = $request->search_date;
            //$tNextDay = strtotime($searchDay . ' +1 day');
            //echo date('Y-m-d', $tNextDay) . "\n";
            //$searchNextDay = date('Y-m-d H:i:s', $tNextDay);
        }


        $agentUser = $authUser['agentUser'];
        //}
        $selectDate = $request->select_date;
        //dd($selectDate);

        // エクスポート実行（テンプレートに書き込み）
        $export = new AgentExport($selectDate);
        //export classの makeCareerSheet関数を呼び出す
        $event = new Event([
            'id' => 0,
            'name' => 'Dummy Event',
            'date' => now(),
            // 必要なフィールドを埋める
        ]);
        $saveDate = str_replace('-', '', $selectDate);
        $export->makeDailySheet($selectDate, $event);
        $file_name = 'exports/stafflist-' . $selectDate . '.xlsx';
        //Excel::store($export, $file_name, 'local');//これを実行するとすでにあるファイルに空のファイルが上書きされる
        //$export->makeDailySheet($selectDate);

        //dd(storage_path('app/exports/stafflist-'. $selectDate . '.xlsx'));

        // ダウンロード用のレスポンスを返す
        return response()->download(storage_path('app/private/exports/stafflist-' . $saveDate . '.xlsx')); //20250324
        //return response()->download('stafflist-' . $selectDate . '.xlsx');//20250322


    } // end dailySheet


    public function getUserDetail($staffCode)
    {
        // ユーザー認証を取得する
        $authUser = $this->getAuthenticatedUser();

        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }

        $agentUser = $authUser['agentUser'];
        // ✅ Foydalanuvchi ma'lumotlarini olish
        $user = DB::table('master_person')
            ->where('staff_code', $staffCode)
            ->select(
                'staff_code',
                'name',
                'name_f',
                'mail_address',
                'portable_telephone_number'
            )
            ->first();

        if (!$user) {
            return redirect()->back()->withErrors(['msg' => 'ユーザー情報が見つかりませんでした。']);
        }

        // ✅ 希望職種 (希望職掌希望勤務地)
        $jobTypeDetail = DB::table('person_hope_job_type')
            ->where('staff_code', $user->staff_code)
            ->value('job_type_detail');

        // ✅ 希望勤務地 (希望勤務地希望勤務地)
        $jobWorkingPlaces = DB::table('person_hope_working_place')
            ->join('master_code', 'person_hope_working_place.prefecture_code', '=', 'master_code.code')
            ->where('master_code.category_code', 'Prefecture')
            ->where('person_hope_working_place.staff_code', $user->staff_code)
            ->pluck('master_code.detail'); // Barcha prefecture-larni olish

        // ✅ 希望年収 (年間収入)
        $yearlyIncome = DB::table('person_hope_working_condition')
            ->where('staff_code', $user->staff_code)
            ->value('yearly_income_min');

        // ✅ 希望時給 (時給)
        $hourlyIncome = DB::table('person_hope_working_condition')
            ->where('staff_code', $user->staff_code)
            ->value('hourly_income_min');

        // ✅ 保有資格 (資格情報)
        $personLicenses = DB::table('person_license')
            ->join('master_license', function ($join) {
                $join->on('person_license.group_code', '=', 'master_license.group_code')
                    ->on('person_license.category_code', '=', 'master_license.category_code')
                    ->on('person_license.code', '=', 'master_license.code');
            })
            ->where('person_license.staff_code', '=', $user->staff_code)
            ->select(
                'master_license.group_name',
                'master_license.category_name',
                'master_license.name as license_name'
            )
            ->distinct() // 重複を避ける
            ->get();

        $careers = DB::table('person_career_history')
            ->select('staff_code', 'id', 'company_name', 'job_type_detail', 'entry_day', 'retire_day')
            ->where('staff_code', $user->staff_code)
            ->get();

        $schools = DB::table('person_educate_history as his')
            ->join('master_code as mcd', function ($join) {
                $join->on('mcd.code', '=', 'his.school_type_code')
                    ->where('mcd.category_code', '=', 'SchoolType');
            })
            ->select('his.school_name as school_name', 'his.entry_day as entry_day', 'his.graduate_day as graduate_day', 'his.speciality as speciality', 'mcd.detail as kind')
            ->where('staff_code', $user->staff_code)
            ->get();

        return view('agent.user_detail', compact('user', 'jobTypeDetail', 'jobWorkingPlaces', 'yearlyIncome', 'hourlyIncome', 'personLicenses', 'agentUser', 'careers', 'schools'));
    }

    public function pauseJob(Request $request)
    {
        $agent = Auth::guard('master_agent')->user();
        if (!$agent) {
            return response()->json(['status' => 'error', 'message' => 'ログインが必要です。'], 403);
        }

        $orderCode = $request->input('order_code');

        // agent_code orqali kompaniyalar ro‘yxatini olish
        $companyCodes = DB::table('company_agent')
            ->where('agent_code', $agent->agent_code)
            ->pluck('company_code')
            ->toArray();

        if (empty($companyCodes)) {
            return response()->json(['status' => 'error', 'message' => '担当企業が存在しません。'], 404);
        }

        $affected = DB::table('job_order')
            ->where('order_code', $orderCode)
            ->whereIn('company_code', $companyCodes)
            ->update([
                'public_flag' => 0,
                'order_progress_type' => 2,
                'update_at' => now(),
            ]);

        if ($affected > 0) {
            return response()->json(['status' => 'success', 'message' => '募集が一時停止されました。']);
        }

        return response()->json(['status' => 'error', 'message' => '募集の一時停止に失敗しました。'], 400);
    }

    public function startJob(Request $request)
    {
        $agent = Auth::guard('master_agent')->user();
        if (!$agent) {
            return response()->json(['status' => 'error', 'message' => 'ログインが必要です。'], 403);
        }

        $orderCode = $request->input('order_code');

        $companyCodes = DB::table('company_agent')
            ->where('agent_code', $agent->agent_code)
            ->pluck('company_code')
            ->toArray();

        if (empty($companyCodes)) {
            return response()->json(['status' => 'error', 'message' => '担当企業が存在しません。'], 404);
        }

        $affected = DB::table('job_order')
            ->where('order_code', $orderCode)
            ->whereIn('company_code', $companyCodes)
            ->update([
                'public_flag' => 1,
                'order_progress_type' => 1,
                'update_at' => now(),
            ]);

        if ($affected > 0) {
            return response()->json(['status' => 'success', 'message' => '募集が開始されました。']);
        }

        return response()->json(['status' => 'error', 'message' => '募集開始に失敗しました。'], 400);
    }
}
