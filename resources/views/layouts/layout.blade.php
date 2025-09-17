<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="viewport" content="initial-scale=1, maximum-scale=1">
        <!-- 🔸 Favicon for all browsers -->
        <link rel="icon" type="image/png" href="{{ asset('img/icon2.png') }}">
        <link rel="shortcut icon" href="{{ asset('img/icon2.png') }}" type="image/png">

        <!-- 🔹 For Apple devices -->
        <link rel="apple-touch-icon" href="{{ asset('img/icon2.png') }}">

        <!-- 🔸 Meta color for Android, PWA, etc. -->
        <meta name="theme-color" content="#ffffff">
        <!-- Google Tag Manager -->
        
        <!-- End Google Tag Manager -->
        <title>@yield('title', '企業ダッシュボード')</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.min.js" integrity="sha384-RuyvpeZCxMJCqVUGFI0Do1mQrods/hhxYlcVfGPOfQtPJh0JCw12tUAZ/Mv10S7D" crossorigin="anonymous"></script>
        {{--  <link rel="icon" href="{{ asset('img/dashboard/fevicon.png') }}" type="image/png" />  --}}
        <link rel="stylesheet" href="{{ asset('style/dashboard/bootstrap.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('style/dashboard/style.css') }}" />
        <link rel="stylesheet" href="{{ asset('style/dashboard/responsive.css') }}" />
        <link rel="stylesheet" href="{{ asset('style/dashboard/colors.css') }}" />
        <link rel="stylesheet" href="{{ asset('style/dashboard/bootstrap-select.css') }}" />
        <link rel="stylesheet" href="{{ asset('style/dashboard/perfect-scrollbar.css') }}" />
        <link rel="stylesheet" href="{{ asset('style/dashboard/custom.css') }}" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap" rel="stylesheet">
        <style>
            body{
                font-family: 'NotoSansJP', sans-serif;
                font-size: var(--bs-body-font-size);
                font-weight: var(--bs-body-font-weight);
                line-height: var(--bs-body-line-height);
            }
        </style>

    </head>

    <body class="dashboard dashboard_1">
        <div class="full_container">
            <div class="inner_container">
                <!-- Sidebar -->
                @include('partials.sidebar')

                <!-- right content -->
                <div id="content">
                    <!-- topbar -->
                    @include('partials.topbar')

                    <!-- Main Content -->
                    <div class="midde_cont">
                        <div class="container-fluid">
                            @yield('content')
                        </div>
                    </div>
                    <!-- End Main Content -->
                </div>
            </div>
        </div>
        <!-- Google Tag Manager (noscript) -->

        <!-- End Google Tag Manager (noscript) -->
        <!-- Scripts -->
        <script src="{{ asset('js/dashboard/jquery.min.js') }}"></script>
        <script src="{{ asset('js/dashboard/popper.min.js') }}"></script>
        <script src="{{ asset('js/dashboard/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/dashboard/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('js/dashboard/animate.js') }}"></script>
        <script src="{{ asset('js/dashboard/bootstrap-select.js') }}"></script>
        <script src="{{ asset('js/dashboard/owl.carousel.js') }}"></script>
        <script src="{{ asset('js/dashboard/custom.js') }}"></script>
        <script src="{{ asset('js/dashboard/semantic.min.js') }}"></script>
        <script src="{{ asset('js/createjob.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    </body>

</html>
