<nav id="sidebar">
    <div class="sidebar_blog_1">
        <div class="sidebar-header">
            <div class="logo_section">
                <a href="/">
                    <img class="logo_icon img-responsive" src="{{ asset('img/icon.png') }}" alt="#" />
                </a>
            </div>
        </div>
        <div class="sidebar_user_info">
            <div class="icon_setting"></div>
            <div class="user_profle_side">
                <div class="user_info">

                    @if (isset($agentUser) && $agentUser)
                        <h6>{{ $agentUser->agent_name }}</h6>
                        <p><span class="online_animation"></span> {{ $agentUser->agent_code }}</p>
                    @elseif(isset($companyUser) && $companyUser)
                        <h6>{{ $companyUser->company_name_k }}</h6>
                        <p><span class="online_animation"></span> {{ $companyUser->company_code }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar_blog_2">
        @if (isset($agentUser) && $agentUser)
            <!-- エージェントユーザー向けサイドバー -->
            <h4>エージェント</h4>

            <ul class="list-unstyled components">
                <li>
                    <a href="{{ route('agent.profile') }}">
                        <i class="fa fa-user-circle orange_color"></i>
                        <span>エージェント管理</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('agent.linked_companies') }}">
                        <i class="fa fa-briefcase orange_color"></i>
                        <span>関連企業を見る</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('agent.create_company') }}">
                        <i class="fa fa-user-circle orange_color"></i>
                        <span>求人企業登録</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('agent.linked_jobs') }}">
                        <i class="fa fa-briefcase orange_color"></i>
                        <span>関連求人を見る</span>
                    </a>
                </li>
                @php
                    $agentCode = Auth::guard('master_agent')->user()->agent_code;
                    $companyCodes = DB::table('company_agent')->where('agent_code', $agentCode)->pluck('company_code');

                    // ✅ 有効なオファーの数
                    $activeOffersCount = DB::table('person_offer')
                        ->join('job_order', 'person_offer.order_code', '=', 'job_order.order_code')
                        ->whereIn('job_order.company_code', $companyCodes)
                        ->where('person_offer.offer_flag', '1')
                        ->count();

                    // ✅ キャンセルされたオファーの数
                    $canceledOffersCount = DB::table('person_offer')
                        ->join('job_order', 'person_offer.order_code', '=', 'job_order.order_code')
                        ->whereIn('job_order.company_code', $companyCodes)
                        ->where('person_offer.offer_flag', '2')
                        ->count();
                @endphp

                <li>
                    <a href="{{ route('agent.offercontrol') }}">
                        <i class="fa-solid fa-bell orange_color"></i>
                        <span>オファー管理</span>
                        @if ($activeOffersCount > 0 || $canceledOffersCount > 0)
                            <span class="badge bg-danger p-2 fs-4">{{ $activeOffersCount }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('agent.usersearch') }}">
                        <i class="fa fa-user orange_color"></i>
                        <span>スタッフ検索</span>
                    </a>
                </li>


                <li>
                    @if (Auth::guard('master_agent')->check())
                        <a class="" href="{{ route('agent.logout') }}"
                            onclick="event.preventDefault(); document.getElementById('agent-logout-form').submit();">
                            ログアウト <i class="fa fa-sign-out"></i>
                        </a>
                        <form id="agent-logout-form" action="{{ route('agent.logout') }}" method="POST"
                            style="display: none;">
                            @csrf
                        </form>
                    @endif

                </li>
                {{--  <a href="{{ route('agent.linked_companies') }}" class="btn btn-primary">関連企業を見る</a>
                <a href="{{ route('agent.linked_jobs') }}" class="btn btn-success">関連求人を見る</a>  --}}

            </ul>
        @elseif (isset($companyUser) && $companyUser)
            <!-- 企業ユーザー向けサイドバー -->
            <h4>企業ダッシュボード</h4>
            <ul class="list-unstyled components">
                <li>
                    <a href="{{ route('jobs.job_list') }}">
                        <i class="fa fa-clock-o orange_color"></i>
                        <span>求人票リスト</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('jobs/create_job') }}">
                        <i class="fa fa-clock-o orange_color"></i>
                        <span>求人票を作成する</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('default-logout-form').submit();">
                        ログアウト <i class="fa fa-sign-out orange_color"></i>
                    </a>
                    <form id="default-logout-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                    </form>
                </li>
            </ul>
        @else
            <!-- ユーザーはログインしていません -->
            <p>ログインしてください。</p>
        @endif
    </div>

</nav>
