<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;
use Jenssegers\Agent\Agent;

class ContactController extends Controller
{
    public function showContactForm()
    {
        // **地域情報を取得**
        $regions = DB::table('master_code')
            ->where('category_code', 'Region')
            ->get();

        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->get();

        // **地域に属する都道府県のグループ化**
        $regionGroups = $regions->map(function ($region) use ($prefectures) {
            return [
                'detail' => $region->detail,
                'prefectures' => $prefectures->filter(function ($prefecture) use ($region) {
                    return $prefecture->region_code === $region->code;
                }),
            ];
        });

        // **都道府県のリスト (個別)**
        $individualPrefectures = $prefectures->map(function ($prefecture) {
            return [
                'code' => $prefecture->code,
                'detail' => $prefecture->detail,
            ];
        })->toArray();
        return view('contact', compact('regionGroups', 'individualPrefectures')); // 各都道府県));
    }

    // 求職者お問い合わせの送信
    public function sendStaffContactEmail(Request $request)
    {
        $agent = new Agent();
        $data = $request->all();

        // 件名にinquiryTypeを入力
        $data['subject'] = $request->input('inquiryType', '未記入');

        // 選択された問い合わせタイプをタイトルに追加
        $data['form_type'] = "求職者お問い合わせ - " . ($request->input('inquiryType') ?? '未記入');

        // 県名を取得
        if ($request->has('prefecture_code')) {
            $data['prefecture_code'] = $request->input('prefecture_code');
        } else {
            $data['prefecture_code'] = '未記入';
        }

        $data['browser'] = $agent->browser(); // ブラウザ名取得
        Mail::to('kisui@lis21.co.jp')->send(new ContactMail($data));

        return back()->with('success', '求職者お問い合わせが送信されました。')->withInput();
    }



    public function showCompanyContactForm()
    {
        // **地域情報を取得**
        $regions = DB::table('master_code')
            ->where('category_code', 'Region')
            ->get();

        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->get();

        // **地域に属する都道府県のグループ化**
        $regionGroups = $regions->map(function ($region) use ($prefectures) {
            return [
                'detail' => $region->detail,
                'prefectures' => $prefectures->filter(function ($prefecture) use ($region) {
                    return $prefecture->region_code === $region->code;
                }),
            ];
        });

        // **都道府県のリスト (個別)**
        $individualPrefectures = $prefectures->map(function ($prefecture) {
            return [
                'code' => $prefecture->code,
                'detail' => $prefecture->detail,
            ];
        })->toArray();
        return view('company_contact', compact('regionGroups', 'individualPrefectures'));
    }
    // 求人企業お問い合わせの送信
    public function sendCompanyContactEmail(Request $request)
    {
        $agent = new Agent();
        $data = $request->all();
        $data['form_type'] = '求人企業お問い合わせ';
        $data['browser'] = $agent->browser(); // ブラウザ名を取得する

        // 勤務地 (Prefecture Detail)**
        if ($request->has('prefecture_code')) {
            // 都道府県コード文字列変換
            $prefecture_name = DB::table('master_code')
                ->where('category_code', 'Prefecture')
                ->where('code', $request->input('prefecture_code'))
                ->value('detail');
            $data['prefecture_code'] = $prefecture_name ?? '未記入';
        } else {
            $data['prefecture_code'] = '未記入';
        }

        // アンケート (Survey)**
        if ($request->has('survey_q1')) {
            $data['survey_q1'] = "<p style='color:red;'>Q1: " . e($request->input('survey_q1')) . "</p>";
        }
        if ($request->has('survey_q2') && !empty($request->input('survey_q2'))) {
            $data['survey_q2'] = "<p style='color:red;'>Q2: " . e($request->input('survey_q2')) . "</p>";
        }

        Mail::to('kisui@lis21.co.jp')->send(new ContactMail($data));

        return back()->with('success', '求人企業お問い合わせが送信されました。')->withInput();
    }
    public function showForm()
    {
        return view('questionnaire');
    }
    public function submitForm(Request $request)
    {
        // ✅ Formdan kelgan ma'lumotlarni tekshirish
        $request->validate([
            'feedback' => 'required|string|max:500',
            'email' => 'nullable|email',
        ]);

        // ✅ Emailga yuborish uchun ma'lumotlar
        $data = [
            'feedback' => $request->feedback ?? '追加のフィードバックはありません.',
        ];

        Mail::send('emails.questionnaire', $data, function ($message) {
            $message->to('kisui@lis21.co.jp') // ✅ Emailni `kisui@lis21.co.jp` ga yuborish
                ->subject("新しいアンケートが送信されました！");
        });

        return redirect()->route('questionnaire')->with('success', 'アンケートを送信しました！');
    }
}
