<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CustomAgentUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class AgentLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('agent.agent_login');
    }
    public function agentLogin(Request $request)
    {
        Log::info('エージェントログインフォームが送信されました.', ['input' => $request->all()]);

        $request->validate([
            'agent_code' => 'required|string',
            'password' => 'required|string|min:3',
        ]);

        $agentUser = DB::table('master_agent')
            ->where('agent_code', $request->input('agent_code'))
            ->first();

        if (!$agentUser) {
            Log::warning('エージェントコードが見つかりません.', ['agent_code' => $request->input('agent_code')]);
            return back()->withErrors([
                'agent_code' => 'エージェントコードが見つかりません。',
            ]);
        }

        $inputPassword = $request->input('password');
        $hashedPassword = strtoupper(md5($inputPassword));

        if (strtoupper($agentUser->password) !== $hashedPassword) {
            Log::warning('無効なログイン試行.', ['agent_code' => $request->input('agent_code')]);
            return back()->withErrors([
                'agent_code' => 'エージェントコードまたはパスワードが正しくありません。',
            ]);
        }

        $authUser = new CustomAgentUser((array) $agentUser);
        Auth::guard('master_agent')->login($authUser);

        session([
            'user_id' => $agentUser->agent_code,
            'role' => 'agent',
            'agent_data' => [
                'name' => $agentUser->agent_name,
                'email' => $agentUser->mail_address,
            ]
        ]);

        Log::info('エージェントが正常にログインしました.', ['agent_code' => $agentUser->agent_code]);
        return redirect()->route('agent.dashboard')->with('status', 'ログイン成功!');
    }

    public function logout(Request $request)
    {
        Auth::guard('master_agent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/agent/login')->with('status', 'ログアウトしました！');
    }
}
