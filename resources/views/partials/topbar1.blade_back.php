<nav class="navbar navbar-expand-lg">
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">

            <a href="/" class="logo d-flex align-items-center me-auto">
                <img src="{{ asset('img/logo02.png') }}" alt="">
            </a>
            {{--  <a href="https://www.shigotonavi.co.jp/indexm.asp" class="logo d-flex align-items-center me-auto">
                <img src="{{ asset('img/logo02.png') }}" alt="">
            </a>  --}}
            
            @if (Auth::check())
                @php
                    $user = Auth::user();
                @endphp

                @if (!empty($user->company_code))
                    {{-- 🔥ユーザーが企業の場合  --}}
                    <a class="btn-getstarted flex-md-shrink-0 text-decoration-none"
                        href="{{ route('company.dashboard') }}">企業ダッシュボード</a>
                @elseif (!empty($user->agent_code))
                    {{-- 🔥ユーザーがエージェントの場合  --}}
                    <a class="btn-getstarted flex-md-shrink-0 text-decoration-none"
                        href="{{ route('agent.dashboard') }}">エージェントダッシュボード</a>
                @else
                    {{-- 🔥一般求職者（staff_codeの場合）  --}}
                    <a class="btn-getstarted flex-md-shrink-0 text-decoration-none"
                        href="{{ route('mypage') }}">マイページ</a>
                    {{--  <form class="btn-getstarted flex-md-shrink-0 btn-sm" id="logout-form" action="{{ route('logout') }}"
                        method="POST">
                        @csrf
                        <a href="{{ route('logout') }}" class="text-white text-decoration-none"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            ログアウト
                        </a>
                    </form>  --}}
                    <header>
                        <button class="hamburger">
                            <div>
                                <p>&#9776;</p>
                            </div>
                        </button>
                        <button class="cross"><i class="fa-solid fa-xmark"></i></button>
                        <div class="menu">
                            <ul>
                                <a href="{{ route('profile.profile') }}">
                                    <li><i class="fa-solid fa-user-tie"></i>基本情報</li>
                                </a>
                                <a href="{{ route('matchings.edit') }}">
                                    <li><i class="fa-solid fa-file-pen"></i>基本情報変更</li>
                                </a>
                                <a href="#">
                                    <li>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <a href="{{ route('logout') }}"
                                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <li><i class="fa-solid fa-right-from-bracket"></i> ログアウト</li>
                                </a>
                                </form>
                                </li>
                                </a>
                            </ul>
                        </div>
                    </header>
                @endif
            @else
                {{-- 🔥ユーザーがログインしていない場合  --}}
                <a class="btn-getstarted flex-md-shrink-0 text-decoration-none" href="{{ route('signin') }}">会員登録</a>
                <a class="btn-getstarted flex-md-shrink-0 text-decoration-none" href="{{ route('login') }}">ログイン</a>
            @endif


        </div>
    </header>

</nav>
