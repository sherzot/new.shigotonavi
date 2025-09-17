<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="{{ asset('img/icon.png') }}" rel="icon" type="image/svg+xml">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
            integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
        </script>
        {{-- LOCAL JS && CSS  --}}
        <link rel="stylesheet" href="{{ asset('style/results.css') }}">
        {{-- HUMBURGER --}}
        <script src="{{ asset('js/humburger.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        {{--  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>  --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
            integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
            .badge {
                padding: 0.5em 1em;
                font-size: 0.9em;
                border-radius: 0.25em;
            }

            .badge.bg-white {
                font-size: 12px;
            }

            .btn.btn-outline-secondary {
                background-color: #ea544a;
            }
        </style>

        <title>マッチング結果</title>
    </head>

    <body>
        <div class="py-2">
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
                <div class="row g-4">
                    @foreach ($matchingResults as $job)
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100 d-flex flex-column">
                                <div class="card-body">
                                    <h6 class="card-title text-primary fw-400">
                                        {{ $job->pr_title1 ?? ' ' }}
                                    </h6>

                                    <p class="card-title" style="color: #ea544a;">
                                        <span>職種:</span> {{ $job->job_type_detail ?? '詳細なし' }}
                                    </p>

                                    <p class="card-text mb-2">
                                        <strong>給与例:</strong>
                                        @if ($job->hourly_income_min > 0)
                                            時給
                                            {{ number_format($job->hourly_income_min) }}円{{ $job->hourly_income_max > 0 ? '〜' . number_format($job->hourly_income_max) . '円' : '〜' }}
                                        @elseif($job->yearly_income_min > 0)
                                            年収
                                            {{ number_format($job->yearly_income_min) }}円{{ $job->yearly_income_max > 0 ? '〜' . number_format($job->yearly_income_max) . '円' : '〜' }}
                                        @else
                                            未設定
                                        @endif
                                    </p>

                                    <p class="card-text">
                                        <strong>勤務地:</strong> {{ $job->prefecture_name ?? '情報なし' }}
                                    </p>

                                    <div class="tags">
                                        @if (!empty($job->selectedFlagsArray))
                                            <div class="d-flex flex-wrap">
                                                @foreach ($job->selectedFlagsArray as $flag)
                                                    @if (array_key_exists($flag, $checkboxOptions))
                                                        <span
                                                            class="badge bg-white text-secondary border border-secondary me-2 mb-2 p-1">
                                                            {{ $checkboxOptions[$flag] }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <p>特記事項はありません。</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Footer section -->
                                <div class="card-footer bg-white border-top-0 mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('matchings.detail', ['id' => $job->id, 'staffCode' => auth()->user()->staff_code]) }}"
                                            class="btn btn-primary btn-sm">詳細を見る</a>
                                        <span class="badge" style="color: #ea544a;">
                                            <i class="fas fa-eye"></i> {{ $job->browse_cnt ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                <div class="mt-4 d-flex justify-content-center">
                    {{ $matchingResults->appends(['query' => request('query')])->links('vendor.pagination.default') }}
                </div>
                @if ($matchingResults->isEmpty())
                    <!-- 結果がない場合 -->
                    <div class="row my-5 justify-content-center">
                        <a href="{{ route('matchings.update') }}" class="btn btn-lg active" id="my-btn">
                            マッチング条件を変更してみる
                        </a>
                    </div>
                @else
                    <div class="row my-5 justify-content-center">
                        <a href="{{ route('matchings.update') }}" class="btn btn-lg active" id="my-btn">
                            マッチング条件を変更してみる
                        </a>
                    </div>
                    <!-- マイページへのリンク -->
                    <div class="row my-5 justify-content-center">
                        <a href="{{ route('mypage') }}" class="btn btn-lg active" id="my-btn">
                            マイページに戻る
                        </a>
                    </div>
                @endif
            </div>
    </body>

</html>
