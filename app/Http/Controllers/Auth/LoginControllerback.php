<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\MasterPerson;
use App\Models\PersonUserInfo;
use App\Mail\ResetPasswordTokenMail;
use Carbon\Carbon;

class LoginController extends Controller
{
    /**
     * ログインフォームを表示します。
     */
    public function showLoginForm()
    {
        return view('auth.login'); // ログインフォームを表示
    }

    /**
     * ユーザー認証。
     */
    public function login(Request $request)
    {

        $startdate = Carbon::now()->format('Y-m-d H:i:s.u');
        $request->validate([
            'staff_code' => 'required|string',
            'password' => 'required|min:3|string',
        ]);

        // Staff_code による PersonUserInfo からのユーザーの検証
        $personInfo = null;

        // Masterperson (staff_code または mail_address) によるユーザーの検証
        $person = MasterPerson::select('staff_code')
            ->where('staff_code', $request->input('staff_code'))
            ->orWhere('mail_address', $request->input('staff_code'))
            ->first();
        // `$person` null bo'lsa, xatolikni oldini olish
        if (!$person) {
            return back()->withErrors([
                'staff_code' => 'IDまたはパスワードが間違っています。',
            ]);
        }
        //dd($person);
        //20250120 ＄personを検索してから、そのstaff_codeでpersonUserinfoを調べる
        $personInfo = PersonUserInfo::select('staff_code', 'password')
            ->where('staff_code', $person->staff_code)
            ->first();


        // ユーザーが存在しないかどうかを確認する
        if (!$person || ($personInfo && strtoupper(md5($request->input('password'))) !== $personInfo->password)) {
            return back()->withErrors([
                'staff_code' => 'IDまたはパスワードが間違っています。',
            ]);
        }
        $enddate = Carbon::now()->format('Y-m-d H:i:s.u');
        //dd('かかった時間  ' . $startdate.  '  ' .$enddate );

        // 認証
        Auth::login($person);

        // ログインアカウントを更新する
        if ($personInfo) {
            $personInfo->increment('access_count');
            $personInfo->last_access_day = now();
            $personInfo->save();
        }

        return redirect()->route('mypage'); // ログインが成功した場合
    }
    /**
     * パスワードリセットフォームを表示
     */
    public function resetPasswordRequestForm()
    {
        return view('auth.reset_password'); // パスワードリセットフォームを表示
    }

    /**
     * パスワードリセットメールを送信します。
     */
    public function resetPasswordRequest(Request $request)
    {
        $request->validate([
            'mail_address' => 'required|email',
        ]);

        // メールアドレスに基づいてユーザーを検索
        $person = MasterPerson::select('staff_code', 'mail_address', 'verified_at', 'verification_token')
            ->where('mail_address', $request->mail_address)->first();

        if (!$person) {
            return back()->withErrors(['error' => 'ユーザーが見つかりませんでした。']);
        }

        // Email の確認状況をチェック
        // if (is_null($person->verified_at)) {
        //     return back()->withErrors(['error' => 'メールアドレスが確認されていません。']);
        // }

        // メールが認証されているかどうかを確認します（認証されていない場合は自動確認されます）
        if (is_null($person->verified_at)) {
            // Tasdiqlanmagan foydalanuvchilarni avtomatik tasdiqlash
            // 未認証ユーザーの自動承認
            $person->verified_at = now();
            $person->save();
            \Log::info("未認証のユーザーが自動的に認証されました: {$person->mail_address}");
        }
        // if (is_null($person->verified_at)) {
        //     \Log::info("認証されていないユーザーがパスワードをリセットしようとしました: {$person->mail_address}");
        // }

        // 新しい verification_token を生成して保存
        $verificationToken = Str::random(64);
        $person->verification_token = $verificationToken;
        $person->save();

        // Emailをハッシュ化
        $hashedEmail = Hash::make($person->mail_address);

        // パスワードリセットリンクの生成
        $resetUrl = url('/password/reset?token=' . urlencode($verificationToken) . '&hashed_email=' . urlencode($hashedEmail));

        // メールを送信
        Mail::to($person->mail_address)->send(new ResetPasswordTokenMail($resetUrl));

        return redirect()->route('login')->with('status', 'パスワードリセット手順がメールで送信されました。');
    }

    /**
     * パスワードリセットフォームを表示。
     */
    public function showResetPasswordForm(Request $request)
    {
        $token = $request->query('token');
        $hashed_email = $request->query('hashed_email');

        // Token yoki hashed_email mavjudligini tekshirish
        if (!$token || !$hashed_email) {
            return redirect()->route('reset_password.form')->withErrors(['error' => 'リンクが無効です。']);
        }

        return view('auth.reset_password_form', compact('token', 'hashed_email'));
    }

    /**
     * パスワードをリセットする。
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'hashed_email' => 'required',
            'password' => 'required|confirmed|min:3|string',
        ]);

        // verification_token に基づいてユーザーを検索
        $person = MasterPerson::select('staff_code', 'mail_address', 'verified_at', 'verification_token')
            ->where('verification_token', $request->token)->first();

        if (!$person) {
            return back()->withErrors(['error' => 'トークンが無効です。']);
        }

        // ハッシュされたメールアドレスの確認
        if (!Hash::check($person->mail_address, $request->hashed_email)) {
            return back()->withErrors(['error' => 'セキュリティ上の問題が発生しました。']);
        }

        // PersonUserInfo のパスワードを更新
        $personInfo = PersonUserInfo::select('staff_code', 'password')
            ->where('staff_code', $person->staff_code)->first();

        if (!$personInfo) {
            return back()->withErrors(['error' => 'パスワードリセットに失敗しました。']);
        }

        $personInfo->password = strtoupper(md5($request->password));
        $personInfo->save();

        // verification_token をリセット
        $person->verification_token = null;
        $person->save();

        return redirect()->route('login')->with('status', 'パスワードが正常にリセットされました。');
    }


    /**
     * .ユーザーをログアウトします
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Foydalanuvchini boshqa domen sahifasiga yo‘naltirish
        return redirect()->away('https://www.shigotonavi.co.jp/indexm.asp');
    }
}
