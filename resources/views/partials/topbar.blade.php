<div class="topbar">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="full">
            <button type="button" id="sidebarCollapse" class="sidebar_toggle"><i class="fa fa-bars"></i></button>
            <div class="right_topbar">
                <div class="icon_info">
                    {{--  <ul>
                        <li>
                            <a href="#">
                                <i class="fa-solid fa-bell"></i>
                                <span class="badge">{{ session('notifications') ? count(session('notifications')) : 0 }}</span>
                            </a>
                            <div class="dropdown-menu">
                                @if(session('notifications') && count(session('notifications')) > 0)
                                    @foreach (session('notifications') as $notification)
                                        <div class="dropdown-item">
                                            <p>{{ $notification['message'] }}</p>
                                            @if($notification['order_code'] && ($notification['message'] === '新しい「派遣」または「紹介予定派遣」の求人が登録されました。内容をご確認ください。'))
                                                <a href="{{ route('job.edit', $notification['order_code']) }}" class="btn btn-sm btn-primary">
                                                    編集する
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="dropdown-item">
                                        <p>通知はありません。</p>
                                    </div>
                                @endif
                            </div>
                        </li>
                        <li><a href="#"><i class="fa fa-question-circle"></i></a></li>
                        <li><a href="#"><i class="fa-regular fa-envelope"></i><span class="badge">0</span></a></li>
                    </ul>  --}}
                    <ul class="user_profile_dd">
                        <li>
                            @if (Auth::guard('master_company')->check())
                                {{-- 企業ユーザー --}}
                                <a class="dropdown-toggle" data-toggle="dropdown">
                                    <span class="name_user">{{ Auth::guard('master_company')->user()->company_name_k }}</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">My Profile</a>
                                    <a class="dropdown-item" href="{{ route('company.logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('company-logout-form').submit();">
                                        ログアウト <i class="fa fa-sign-out"></i>
                                    </a>
                                    <form id="company-logout-form" action="{{ route('company.logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            @elseif (Auth::guard('master_agent')->check())
                                {{-- エージェントユーザー --}}
                                <a class="dropdown-toggle" data-toggle="dropdown">
                                    <span class="name_user">{{ Auth::guard('master_agent')->user()->agent_name }}</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('agent.profile') }}">My Profile</a>
                                    <a class="dropdown-item" href="{{ route('agent.logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('agent-logout-form').submit();">
                                        ログアウト <i class="fa fa-sign-out"></i>
                                    </a>
                                    <form id="agent-logout-form" action="{{ route('agent.logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            @else
                                {{-- Default logout --}}
                                <a class="dropdown-toggle" data-toggle="dropdown">
                                    <span class="name_user">{{ Auth::user()->name }}</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('default-logout-form').submit();">
                                        ログアウト <i class="fa fa-sign-out"></i>
                                    </a>
                                    <form id="default-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            @endif
                        </li>
                    </ul>
                    

                </div>
            </div>
        </div>
    </nav>
</div>
