<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ResumeController extends Controller
{
    public function preview()
    {
        $staffCode = Auth::user()->staff_code;

        $person = DB::table('master_person')->where('staff_code', $staffCode)->first();
        $photoRecord = DB::table('person_picture')->where('staff_code', $staffCode)->first();
        $existingPhoto = $photoRecord ? 'data:image/jpeg;base64,' . base64_encode($photoRecord->picture) : null;
        $educations = DB::table('person_educate_history')->where('staff_code', $staffCode)->get();
        $careers = DB::table('person_career_history')->where('staff_code', $staffCode)->get();
        $licenses = DB::table('person_license')
            ->leftJoin('master_license', function($join) {
                $join->on('person_license.group_code', '=', 'master_license.group_code')
                    ->on('person_license.category_code', '=', 'master_license.category_code')
                    ->on('person_license.code', '=', 'master_license.code');
            })
            ->where('person_license.staff_code', $staffCode)
            ->select('person_license.*', 'master_license.name as license_name')
            ->get();
        $skills = DB::table('person_skill')
            ->leftJoin('master_code', 'person_skill.code', '=', 'master_code.code')
            ->where('staff_code', $staffCode)
            ->select('person_skill.*', 'master_code.detail')
            ->get();
        $self_pr = DB::table('person_self_pr')->where('staff_code', $staffCode)->value('self_pr');
        $resumePreference = DB::table('person_resume_other')->where('staff_code', $staffCode)->first();

        return view('resume.preview', compact(
            'person', 'existingPhoto', 'educations', 'careers', 'licenses', 'skills', 'self_pr', 'resumePreference'
        ));
    }

    public function confirm(Request $request)
    {
        // Tasdiqlangandan keyin Mypage yoki Thank You page ga redirect
        return redirect()->route('mypage')->with('success', '履歴書内容を確定しました！');
    }
}