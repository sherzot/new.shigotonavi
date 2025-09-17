<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CompanyJobPerson;
use App\Models\CompanyPerson;
use App\Models\JobOrder;
use Illuminate\Support\Facades\Mail;
use App\Mail\CompanyVerifyEmail;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;



class CompanyRegisterController extends Controller
{
    public function showRegisterForm(){

        //業種
	$industoryTypes = DB::table('master_code')
	    ->where('category_code', 'IndustryType')
	   ->select('code', 'detail')
	   ->get();

	//dd($industoryTypes);
	// 業種 (ビッグクラスデータの取得)
        //$bigClasses = DB::table('master_job_type')
        //    ->select('big_class_code', 'big_class_name')
        //    ->distinct()
        //    ->get();
        // グループのデータを取得する（）
        //$groups = DB::table('master_job_type')
        //->select('middle_class_code', 'middle_clas_name')
        //->distinct() 
        //->get();
	
        return view('company.company_create', compact('industoryTypes'));
    }
    public function  store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'surname' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'mail_address' => [
                'required',
                'email',
                Rule::unique('company_person', 'mail_address')
            ],
            'tel' => 'required|string',
            'company_name' => ['required', 'string', 'max:255'],
            'section_name' => ['nullable', 'string', 'max:255'],
            'post' => 'required|string|max:7',
            'prefecture_code' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string',
            'working_place_companyname' => 'required|string',
            'job_category' => 'required|string',
            'job_detail' => 'required|string',
            'job_code' => 'required|string',
            'business_detail' => 'required|string',
        ]);

        // Agar validatorda xatolik bo'lsa //バリデーターにエラーがある場合
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', '入力エラーがあります。正しい情報を入力してください。');
        }

        // Tranzaksiyani ishga tushirish //トランザクションを開始する
        DB::transaction(function () use ($request) {

            $companyPerson = CompanyPerson::create([
                'person_name' => $request->surname . ' ' . $request->name,
                'mail_address' => $request->mail_address,
                'tel' => $request->tel,
                'company_name' => $request->company_name,
                'section_name' => $request->section_name,
                'post' => substr($request->post, 0, 7),
                'prefecture_code' => $request->prefecture_code,
                'city' => $request->city,
                'address' => $request->address,
                'active_flag' => '1',
            ]);
	    
	   // 県名を調べる
	   $prefecture = DB::table('master_code')
		->where('code', $request->prefecture_code)
		->where('category_code', 'Prefecture')
		->pluck('detail');
		//value('detail');
	    //dd($prefecture);
	
	    // max Compant_Code
	    $maxcode = DB::table('master_company')
		->max('cpmpany_code');	

	    $newcode = 'C' . str_pad(substr($maxcode,1,7) +1 ,7, 0, STR_PAD_LEFT);  
            // 2. 新しい `CompanyJobPerson` レコードを作成します
            // $companyJobPerson = CompanyJobPerson::create([
            //    'person_code' => $companyPerson->person_code, // 連絡先の person_code
            // ]);

	    // 3. 「master_company」エントリを作成します。
            MasterCompany::create([
                'company_code' => $newcode,
                'company_name_k' => $request->company_name,
                'post_u' => substr($request->post, 0, 3),
		'post_l' => substr($request->post,3),
                'prefecture' => $prefecture,
                'city_k' => $request->city,
                'address' => $request->address           
		]);
        

            // 3. 「JobOrder」エントリを作成します。
            //JobOrder::create([
            //    'order_code' => $companyJobPerson->order_code,
            //    'company_code' => $companyJobPerson->company_code,
            //    'job_type_detail' => $request->job_category . ' ' . $request->job_detail . ' ' . $request->job_code,
            //    'business_detail' => $request->business_detail,
            //    'working_place_companyname' => $request->working_place_companyname,
            //]);
        });

        return redirect('company_page')->with('message', '確認メールが受信箱に送信されました。メール内のリンクをクリックしてメッセージを確認してください。');
    }


    // メール確認
    public function verifyEmail($token)
    {
        $companyPerson = CompanyPerson::where('verification_token', $token)->first();

        if (!$companyPerson) {
            return redirect('signin')->with('message', '検証の有効期限が切れているか、トークンが無効です。');
        }

        $companyPerson->update([
            'verified_at' => now(),
            'verification_token' => null,
        ]);

        return redirect()->route('company_page', ['email' => $companyPerson->mail_address]);
    }
    public function CompanyPage()
    {
        $companyPerson = Auth::user();
        return view('company.company_page'); // Bladeファイルの位置に合わせる
    }

}
