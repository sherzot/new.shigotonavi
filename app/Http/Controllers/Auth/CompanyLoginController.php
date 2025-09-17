<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CustomCompanyUser;

class CompanyLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('jobs.company_login');
    }

    public function companyLogin(Request $request)
    {
        // 1. ログイン情報の検証
        Log::info('ログインフォームが送信されました。', ['input' => $request->all()]);
        $request->validate([
            'company_code' => 'required|string',
            'password' => 'required|min:3|string',
        ]);

        // 2. 「master_company」テーブルから「company_code」でユーザーを検索します。
        $companyUser = DB::table('master_company')
            ->where('company_code', $request->company_code)
            ->first();

        // ユーザーデータのロギング
        Log::info('ユーザーは DB から取得されます。', ['companyUser' => $companyUser]);

        // ユーザーが見つからない場合はエラーを返します
        if (!$companyUser) {
            Log::warning('この company_code を持つユーザーは見つかりませんでした。', ['company_code' => $request->company_code]);
            return back()->withErrors([
                'company_code' => 'company_codeが見つかりません。',
            ]);
        }

        // 3. ユーザーが入力したパスワードを取得し、MD5 に変換します。
        $inputPassword = $request->password;
        $hashedPassword = strtoupper(md5($inputPassword)); // 入力したパスワードをMD5に変換します

        // 入力および保存されたパスワードのログ記録
        Log::info('ユーザーが入力してハッシュ化されたパスワード:', [
            'inputPassword' => $inputPassword,
            'hashedPassword' => $hashedPassword,
            'dbPassword' => strtoupper($companyUser->password)
        ]);

        // 4. パスワードの確認
        if (strtoupper($companyUser->password) !== $hashedPassword) {
            Log::warning('無効なログイン試行です。', [
                'company_code' => $request->company_code,
            ]);

            return back()->withErrors([
                'company_code' => 'IDまたはパスワードが間違っています.',
            ]);
        }

        // 5. ユーザーログイン
        // `stdClass` を `CustomCompanyUser` モデルに変換します
        $user = new CustomCompanyUser((array) $companyUser);  // `stdClass`をモデルオブジェクトに変換します

        Auth::guard('master_company')->login($user);

        Log::info('ユーザーは正常にログインしました。', ['company_code' => $companyUser->company_code]);
        Log::info('セッション情報:', session()->all());

        // 6. 最終アクセス時刻を更新する
        DB::table('master_company')
            ->where('company_code', $companyUser->company_code)
            ->update([
                'updated_at' => now(),
            ]);

        // 7. ログイン成功時の会社管理者への転送
        Log::info('会社の経営者に宛てたものです。');
        return redirect()->route('company.dashboard')->with('status', 'ログイン成功しました！');
    }
    public function logout(Request $request)
    {
        Auth::guard('master_company')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/company/login')->with('status', 'ログアウトしました！');
    }
}
