<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        $isCompany = isset($user->company_id);

        DB::enableQueryLog();

        // 希望職掌希望勤務地 
        $jobTypeDetail = DB::table('person_hope_job_type')
            ->where('staff_code', $user->staff_code)
            ->value('job_type_detail');

        // 希望勤務地希望勤務地 
        $jobWorkingPlaces = DB::table('person_hope_working_place')
            ->join('master_code', 'person_hope_working_place.prefecture_code', '=', 'master_code.code')
            ->where('master_code.category_code', 'Prefecture')
            ->where('person_hope_working_place.staff_code', $user->staff_code)
            ->pluck('master_code.detail'); // Barcha prefecture-larni olib keladi


        // 希望年収と希望時給の情報を入手
        $yearlyIncome = DB::table('person_hope_working_condition')
            ->where('staff_code', $user->staff_code)
            ->value('yearly_income_min');

        $hourlyIncome = DB::table('person_hope_working_condition')
            ->where('staff_code', $user->staff_code)
            ->value('hourly_income_min');

        // 🔥 ユーザー固有のライセンスの取得
        $personLicenses = DB::table('person_license')
            ->join('master_license', function ($join) {
                $join->on('person_license.group_code', '=', 'master_license.group_code')
                    ->on('person_license.category_code', '=', 'master_license.category_code')
                    ->on('person_license.code', '=', 'master_license.code');
            })
            ->where('person_license.staff_code', '=', $user->staff_code) // ✅ Faqat ushbu user
            ->select(
                'master_license.group_name',
                'master_license.category_name',
                'master_license.name as license_name'
            )
            ->distinct() // ✅ 重複を避ける
            ->get();

        return view('profile.profile', compact(
            'user',
            'isCompany',
            'jobTypeDetail',
            'jobWorkingPlaces',
            'yearlyIncome',
            'hourlyIncome',
            'personLicenses'
        ));
    }
}
