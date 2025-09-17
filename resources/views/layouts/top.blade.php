<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- 🔸 Favicon for all browsers -->
    <link rel="icon" type="image/png" href="{{ asset('img/icon2.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/icon2.png') }}" type="image/png">

    <!-- 🔹 For Apple devices -->
    <link rel="apple-touch-icon" href="{{ asset('img/icon2.png') }}">

    <!-- 🔸 Meta color for Android, PWA, etc. -->
    <meta name="theme-color" content="#ffffff">

    {{-- <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">  --}}
    <!-- CDN (6.4.2 ishlovchi versiya) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    {{-- GOOGLE FONT  --}}
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js'])  --}}
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- リスの Google Tag Manager -->
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'G-6YVRJF2XSX', {
            'linker': {
                'domains': ['shigotonavi.co.jp', 'mch.shigotonavi.co.jp']
            }
            , 'cookie_domain': 'auto'
            , 'cookie_flags': 'SameSite=None;Secure'
        });

    </script>
    <!--　リスの Google Tag Manager End　-->
    <!-- リス2Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime()
                , event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0]
                , j = d.createElement(s)
                , dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-WV9TQS27');

    </script>
    <!-- リス2End Google Tag Manager -->


    <!-- ジオコードGoogle Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime()
                , event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0]
                , j = d.createElement(s)
                , dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-WRJGM8M8');

    </script>
    <!-- ジオコードEnd Google Tag Manager -->
    <!-- Bootstrap -->
    <link href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('style/main.css') }}">
    <link rel="stylesheet" href="{{ asset('style/snavi.css') }}">

    @stack('styles')
    @livewireStyles

    <style>
    {{-- @font-face {
            font-family: 'NotoSansJP';
            src: url('/fonts/noto/NotoSansJP-Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'NotoSansJP';
            src: url('/fonts/noto/NotoSansJP-Bold.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }  --}}

    html,
    body {
    font-family: 'NotoSansJP', sans-serif;
    font-size: var(--bs-body-font-size);
    font-weight: var(--bs-body-font-weight);
    line-height: var(--bs-body-line-height);
    margin: 0;
    height: 100%;
    scroll-behavior: smooth;
    overflow-x: hidden !important;
    }
    main {
    flex: 1;
    }

    .btn-main-theme {
    background-color: #FF6347 !important;
    color: white !important;
    }

    .btn-main-theme:hover {
    background-color: #FF4500 !important;
    }

    .btn-main-theme2 {
    background-color: #bc1c45 !important;
    color: white !important;
    }

    .btn-main-theme2:hover {
    background-color: #FF4500 !important;
    }

    .text-main-theme {
    color: #FF6347 !important;
    }

    .text-main-theme2 {
    color: #bc1c45 !important;
    }
    .main-title{
        color: #495057;
    }
    .check-button img {
    max-width: 250px;
    width: 100%;
    height: auto;
    }

    @keyframes pulse {
    0% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(255, 99, 71, 0.7);
    }

    70% {
    transform: scale(1.05);
    box-shadow: 0 0 0 10px rgba(255, 99, 71, 0);
    }

    100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(255, 99, 71, 0);
    }
    }

    #scroll-top {
    position: fixed;
    right: 20px;
    bottom: 80px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    font-size: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1060;
    cursor: pointer;
    }

    #scroll-top img {
    width: 60px;
    height: 60px;
    }

    #scroll-top:hover {
    opacity: 1;
    transform: scale(1.1);
    animation: none;
    }

    @media screen and (max-width: 500px) {
    #scroll-top {
    right: 10px;
    bottom: 50px;
    }

    .fs-f28 {
    font-size: 18px !important;
    line-height: 1.6 !important;
    text-align: center !important;
    }

    .fs-f18 {
    font-size: 14px !important;
    line-height: 1.6 !important;
    padding-left: 10px !important;
    padding-right: 10px !important;
    text-align: center !important;
    }

    .text-center {
    text-align: center !important;
    }
    }

    .step-circle {
    width: 40px;
    height: 40px;
    line-height: 40px;
    border-radius: 50%;
    background-color: #dee2e6;
    color: #495057;
    display: inline-block;
    font-weight: bold;
    transition: background-color 0.3s ease, color 0.3s ease;
    }

    .step-circle.active {
    background-color: #dc3545;
    /* 赤色 */
    color: #fff;
    }

    .step-circle:hover {
    background-color: #c82333;
    color: white;
    cursor: pointer;
    }

    .step-line {
    flex: 1;
    height: 4px;
    background-color: #e9ecef;
    margin: 0 10px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
    }

    .step-line.filled {
    background-color: #dc3545;
    }

    .step-label {
    font-weight: bold;
    margin-top: 10px;
    }
    

    @media (max-width: 576px) {
    .step-flow {
    flex-direction: row;
    gap: 20px;
    }

    .step-line {
    height: 30px;
    width: 4px;
    margin: 10px auto;
    }
    }

    .timeline::before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
    }

    </style>
    @livewireStyles
    @stack('styles')
</head>

<body class="d-flex flex-column min-vh-100">
    <div id="top"></div>

    {{-- Sticky Navbar --}}
     @include('partials.topbar1') 

    {{-- Main --}}
    <main class="p-0 p-sm-3">
        @yield('content')
    </main>    

    {{-- Sticky Footer --}}
    {{-- @include('partials.footer1')  --}}
    <footer class="bg-secondary text-white text-center py-3 shadow">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                <a href="{{ route('agent.login') }}" class="btn btn-outline-light btn-sm">エージェント</a>
                <a href="{{ route('company.login') }}" class="btn btn-outline-light btn-sm">企業</a>
                <a href="{{ route('contact.form') }}" class="btn btn-outline-light btn-sm">お問い合わせ</a>
            </div>
            <a href="#" target="_blank" class="d-inline-block text-white text-decoration-none">
                <div class="small">しごとナビ利用規約・<br class="d-sm-none">個人情報保護に関する事項</div>
            </a>
        </div>
    </footer>


    {{-- Scroll-to-top button --}}
    <a href="#top" id="scroll-top">
        <img src="/img/page-top.svg" alt="">
    </a>
    
    @stack('scripts')

    <script>
        const scrollTopButton = document.getElementById("scroll-top");

        scrollTopButton.addEventListener("click", function(e) {
            e.preventDefault();
            const target = document.getElementById('top');
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth"
                });
            } else {
                window.scrollTo({
                    top: 0
                    , behavior: "smooth"
                });
            }
        });

    </script>
    <!-- リスGoogle Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WV9TQS27" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- リスEnd Google Tag Manager (noscript) -->
    <!-- ジオコードGoogle Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WRJGM8M8"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- ジオコードEnd Google Tag Manager (noscript) -->
    
    @livewireScripts
</body>

</html>
