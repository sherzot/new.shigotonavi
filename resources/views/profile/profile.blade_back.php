<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>プロフィール</title>
        {{--  <link rel="stylesheet" href="{{ asset('style/responsive.css') }}">  --}}
        <link rel="stylesheet" href="{{ asset('style/register.css') }}">
        <link href="{{ asset('img/Logo-shigotonavi-mark.svg') }}" rel="icon" type="image/svg+xml">
        <!-- Bootstrap CSS va JavaScript -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
        <link href="{{ asset('img/icon.png') }}" rel="icon" type="image/svg+xml">
        <script src="{{ asset('js/humburger.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        {{--  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
            integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />  --}}
        <style>
            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            td,
            th {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
                font-size: 13px;
            }

            tr:nth-child(even) {
                background-color: #dddddd;
            }
        </style>
    </head>

    <body>
        <section class="w-100">
            <div class="container py-5 h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                        <div class="card shadow-2-strong">
                            <div class="card-body p-3 text-center">
                                <header>
                                    <h3 class="mb-3 mt-2">
                                        <a href="/">
                                            <img src="{{ asset('img/logo02.png') }}" alt="logo" class="w-sm-25 w-50"
                                                id="logo">
                                        </a>
                                    </h3>
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
                                                    <form id="logout-form" action="{{ route('logout') }}"
                                                        method="POST">
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
                                <h5>基本情報</h5>

                                <!-- プロフィール情報 -->
                                <form id="profileForm">
                                    <table>
                                        @if ($isCompany)
                                            <h5>会社名: {{ $user->company_name }}</h5><br>
                                            <h5>会社ID: {{ $user->company_id }}</h5><br>
                                        @else
                                            <tr>
                                                <th>氏名（漢字）</th>
                                                <td>{{ $user->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>氏名（フリガナ）</th>
                                                <td> {{ $user->name_f }} </td>
                                            </tr>
                                            <tr>
                                                <th>個人ID</th>
                                                <td> {{ $user->staff_code }} </td>
                                            </tr>
                                            <tr>
                                                <th>希望職種</th>
                                                <td> {{ $jobTypeDetail ?? '未設定' }} </td>
                                            </tr>
                                            <!-- 保有資格 (取得したライセンス) -->
                                            <tr>
                                                <th>保有資格</th>
                                                <td>
                                                    @if ($personLicenses->isEmpty())
                                                        未設定
                                                    @else
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>グループ</th>
                                                                        <th>カテゴリ</th>
                                                                        <th>ライセンス名</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($personLicenses as $license)
                                                                        <tr>
                                                                            <td>{{ $license->group_name }}</td>
                                                                            <td>{{ $license->category_name }}</td>
                                                                            <td>{{ $license->license_name }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>



                                            <tr>
                                                <th>希望勤務地</th>
                                                <td>
                                                    @if (!empty($jobWorkingPlaces) && $jobWorkingPlaces->count() > 0)
                                                        {{ $jobWorkingPlaces->implode(', ') }}
                                                    @else
                                                        未設定
                                                    @endif
                                                </td>
                                            </tr>

                                            @if ($yearlyIncome > 0)
                                                <tr>
                                                    <th>希望年収</th>
                                                    <td> {{ $yearlyIncome }}円 </td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <th>希望時給</th>
                                                    <td> {{ $hourlyIncome }}円 </td>
                                                </tr>
                                            @endif
                                        @endif
                                        <tr>
                                            <th>メールアドレス</th>
                                            <td> {{ $user->mail_address }}</td>
                                        </tr>

                                    </table>
                                </form>

                                <p>
                                    <a href="{{ route('mypage') }}" class="btn btn-lg mt-4">マイページに戻る</a>
                                </p>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <a href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                        style="text-decoration: none">
                                        <i class="fa-solid fa-right-from-bracket"></i> ログアウト
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </body>

</html>
