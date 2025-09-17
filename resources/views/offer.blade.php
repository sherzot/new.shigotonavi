<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>

    <title>オファーページ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('style/mypage-style/mypage.css') }}">
    <link rel="stylesheet" href="{{ asset('style/responsive.css') }}">
    <script src="{{ asset('js/humburger.js') }}"></script>
    <link href="{{ asset('img/icon.png') }}" rel="icon" type="image/svg+xml">
    {{-- HUMBURGER --}}
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <h3 class="mb-3 mt-2">
                <a href="/">
                    <img src="{{ asset('img/logo02.png') }}" alt="logo" class="w-sm-25 w-50">
                </a>
            </h3>
            <button class="hamburger">
                <div>
                    <p>&#9776;</p>
                </div>
            </button>
            <button class="cross"><i class="fa-solid fa-xmark"></i></button>
       </header>
        <div class="menu">
            <ul>
                <a href="{{ route('profile.profile') }}">
                    <li><i class="fa-solid fa-user-tie"></i>基本情報</li>
                </a>
                 <!-- <a href="{{ route('matchings.edit') }}">
                    <li><i class="fa-solid fa-file-pen"></i>基本情報変更</li>
                </a> -->
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

        <div class="content">
            @if (session('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif

            <div>
                <!-- Display Name depending on User Role -->
                <p>
                    {{ $isCompany ?? false ? Auth::user()->company_name : Auth::user()->name }}
                     様

               </p>
                   <div class="alert alert-danger">
		    @if( $mode==="regist")
                        {{ $id  }}  の求人案件にオファーしました。
		   @else
			{{ $id  }}  の求人案件へのオファーをキャンセルしました。
		   @endif
			<br>仕事内容は  {{ $jobDetail }}
                    </div>

                @if ($errors->has('msg'))
                    <div class="alert alert-danger">
                        {{ $errors->first('msg') }}
                    </div>
                @endif

                @if ($errors->has('error'))
                    <div class="alert alert-danger">
                        {{ $errors->first('error') }}
                    </div>
                @endif
            </div>
            <br>
            {{--  <div class="important">
                <h5>ID: {{ Auth::user()->staff_code }}</h5>
                <p>ログイン時は必ずIDとパスワードをご利用ください。</p>
            </div>  --}}

            <!-- Display Content based on Role -->
            <div class="content-section">
                <<div class="content-section">
                    <div class="line">
                        {{--  <p class="text-start">履歴書作成</p>  --}}
                        {{--  <hr class="line-best">  --}}
                    </div>
                    <div class="cards">
                        <div class="card cursor-pointer bg-light border">
                            <a href="{{ route('educate-history') }}" class="text-center">・学歴・職歴入力</a>
                            
                        </div>
                        <div class="card cursor-pointer bg-light border">
                            <a href="{{ route('self_pr') }}" class="text-center">・自己PR 志望動機</a>
                            {{--  <a href="#">・コンビニ印刷</a>  --}}
                        </div>
                        {{--  <div class="card">
                            
                            <br>
                            
                        </div>  --}}
                    </div>
                    {{--  履歴書印刷  --}}
                    <div class="line">
                        <p class="text-start">履歴書ダウンロード</p>
                        <hr class="line-best">
                    </div>
                    <div class="cards">
                        <div class="card cursor-pointer bg-light border">
                            <a href="{{ route('export') }}" class="text-center">・EXCELダウンロード</a>
                        </div>
                        <div class="card cursor-pointer bg-light border">
                            <a href="{{ route('pdf') }}" class="text-center">・PDFダウンロード</a>
                            
                        </div>
                        {{--  <div class="card text-center">
                            <a href="#">・コンビニ印刷</a>
                        </div>  --}}
                    </div>
                    {{--  職務経歴書印刷  --}}
                    <div class="line">
                        <p class="text-start">職務経歴書ダウンロード</p>
                        <hr class="line-best">
                    </div>
                    <div class="cards">
                        <div class="card cursor-pointer bg-light border">
                            <a href="" class="text-center">・EXCELダウンロード</a>
                        </div>
                        <div class="card cursor-pointer bg-light border">
                            <a href="" class="text-center">・PDFダウンロード</a>
                            
                        </div>
                        {{--  <div class="card text-center">
                            <a href="#">・コンビニ印刷</a>
                        </div>  --}}
                    </div>
                </div>
                <!-- マイページへのリンク -->
                <div class="row my-5 w-75 w-md-50 justify-content-center m-auto">
                    <a href="{{ route('mypage') }}" class="btn btn-danger active my-btn" style="font-size: 16px;">
                        マイページに戻る
                    </a>
                </div>



                </div>
            </div>
        </div>
</body>

</html>

