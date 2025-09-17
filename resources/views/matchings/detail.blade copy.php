<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="{{ asset('img/icon.png') }}" rel="icon" type="image/svg+xml">
        <title>仕事の詳細</title>
        {{--  BOOTSTRAP 5.3.3  --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
        {{-- LOCAL JS && CSS  --}}
        <link rel="stylesheet" href="{{ asset('style/detail.css') }}">
        <link rel="stylesheet" href="{{ asset('style/snavi.css') }}">
        {{--  HUMBURGER QUERY  --}}
        <script src="{{ asset('js/humburger.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        {{--  FONT AWESOME  --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
            integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
    </head>

    <body>
        @php
            use Illuminate\Support\Str;
            //$request->session()->get('errors');
        @endphp
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
                <div class="menu">
                    <ul>
                        <a class="text-center" href="{{ route('profile.profile') }}">
                            <li class="px-2">基本情報<i class="fa-solid fa-user-tie"></i></li>
                        </a>
                        <a class="text-center" href="{{ route('matchings.edit') }}">
                            <li class="px-2">基本情報変更<i class="fa-solid fa-file-pen"></i></li>
                        </a>
                        <a class="text-center" href="{{ route('mypage') }}">
                            <li class="px-2">マイページに戻る<i class="fa-solid fa-right-from-bracket"></i></li>
                        </a>
                    </ul>
                </div>
            </header>
            <h5>
                {{ $isCompany ?? false ? Auth::user()->company_name : Auth::user()->name }}様
            </h5>
            <br>
            @if (session('error'))
                <div class="bg-danger">{{ session('error') }}</div>
            @endif
            <p><strong>職種:</strong> <span class="highlight">{{ $job->job_type_detail }} -
                    ({{ $job->order_code }})</span></p>
            <hr style="color: #ea544a; padding: 2px;">
            <div class="tags">
                @if (!empty($selectedFlagsArray))
                    <div class="d-flex flex-wrap">
                        @foreach ($selectedFlagsArray as $flag)
                            @if (array_key_exists($flag, $checkboxOptions))
                                <span
                                    class="badge bg-white text-secondary border border-secondary me-2 mb-2 p-1">{{ $checkboxOptions[$flag] }}</span>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p>選択された特記事項はありません。</p>
                @endif
            </div>

            <div class="card border p-4 mb-4">
                <p class="jobdetail"><strong>企業PR</strong></p>

                @for ($i = 1; $i <= 3; $i++)
                    @php
                        $titleVar = "pr_title{$i}";
                        $contentVar = "pr_contents{$i}";
                    @endphp

                    @if (!empty($job->$titleVar) || !empty($job->$contentVar))
                        <div class="p-3 border rounded bg-light mb-3">
                            <h6 class="fw-bold text-secondary">{{ e($job->$titleVar) }}</h6>
                            <hr style="color: #ea544a; padding: 2px;">
                            <p class="text-dark">{!! Str::replace("\n", '<br>', e($job->$contentVar ?? '情報なし')) !!}</p>
                        </div>
                    @endif
                @endfor
            </div>


            <!-- 仕事内容 (Ish tavsifi) -->
            <div class="card border p-4 mb-4">
                <p class="jobdetail"><strong>担当業務</strong></p>

                <div class="p-3 border rounded bg-light">
                    <p class="text-dark">{!! Str::replace("\n", '<br>', e($job->business_detail ?? '情報なし')) !!}</p>
                </div>
            </div>


            <!-- ✅ 勤務条件 (Ish sharoitlari) -->
            <div class="card border p-4 mb-4">
                <p class="jobdetail"><strong>勤務条件</strong></p>

                <!-- ✅ 勤務形態 (Ish turi) -->
                <div class="p-3 border-bottom">
                    <h6 class="fw-bold text-secondary">勤務形態</h6>
                    <p class="text-dark">{{ $job->employment_type ?? '情報なし' }}</p>
                </div>

                <!-- ✅ 職種 (Kasb turi) -->
                <div class="p-3 border-bottom">
                    <h6 class="fw-bold text-secondary">職種</h6>
                    <p class="text-dark">{{ $job->job_type_detail ?? '情報なし' }}</p>
                    <ul class="list-unstyled text-dark">

                    </ul>
                </div>


                <!-- ✅ 給与 (Ish haqi) -->
                <div class="p-3 border-bottom">
                    <h6 class="fw-bold text-secondary">給与</h6>
                    <p class="card-text mb-2">
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
                </div>

                <!-- ✅ 勤務時間 (Ish vaqti)  -->
                <div class="p-3 border-bottom">
                    <h6 class="fw-bold text-secondary">勤務時間</h6>
                    <p class="text-dark">
                        {{ substr_replace($workingTime->work_start_time, ':', 2, 0) }}
                        -
                        {{ substr_replace($workingTime->Work_end_time, ':', 2, 0) }}
                    </p>
                </div>
                <div class="p-3 border-bottom">
                    <h6 class="fw-bold text-secondary">休憩時間</h6>
                    <p class="text-dark">
                        {{ substr_replace($workingTime->rest_start_time, ':', 2, 0) }}
                        -
                        {{ substr_replace($workingTime->rest_end_time, ':', 2, 0) }}
                    </p>
                </div>

                <div class="p-3 border-bottom">
                    {{--  <h6 class="fw-bold text-secondary">勤務時間について</h6>  --}}
                    <p class="text-dark">{!! Str::replace("\n", '<br>', e($job->work_time_remark ?? '情報なし')) !!}</p>
                </div>

                <!-- ✅ 休日 (Dam olish kunlari) -->
                <div class="p-3 border-bottom">
                    <h6 class="fw-bold text-secondary">休日</h6>
                    <p class="text-dark">{!! Str::replace("\n", '<br>', e($job->holiday_remark ?? '情報なし')) !!}</p>
                </div>

                <!-- ✅ 勤務地 (Ish joylari) -->
                {{--  <div class="p-3">
                    <h6 class="fw-bold text-secondary">都道府県</h6>
                    <p class="text-dark">{!! Str::replace("\n", '<br>', e(implode("\n", $prefecturesArray ?? ['勤務地情報がありません。']))) !!}</p>
                    <p class="text-dark">
                        {{ implode(', ', array_filter([
                            implode(', ', $prefecturesArray ?? ['勤務地情報がありません。']),
                            $job->city ?? '',
                            $job->town ?? '',
                            $job->address ?? ''
                        ])) }}
                    </p>                    
                </div>  --}}

                <div class="p-3">
                    <h6 class="fw-bold text-secondary">勤務地</h6>
                    
                    @foreach($locations as $location)
                        <p class="text-dark">
                            {{ $location->prefecture }} 
                            {{ $location->city ?? '市区町村情報なし' }}
                            {{ $location->town ?? '町情報なし' }}
                            {{ $location->address ?? '住所情報なし' }}
                        </p>
                    @endforeach
                </div>
                
            </div>






            <div class="button-container">
                <button type="button" onClick="history.back()" class="btn btn-primary">戻る</button>
                {{--  <button class="offer-button"> オファー</button>  --}}
                <form action="{{ route('offer.regist', ['id' => $job->order_code]) }}" method="POST">
                    @csrf
                    @if (!$isOffer)
                        <button type="submit" class="btn text-white px-4 py-2"
                            style="background-color: #ff4c4c; border-radius: 8px; font-size: 14px; font-weight: bold;"
                            name="offer">
                            オファー
                        </button>
                    @elseif ($offerFlag === '2')
                        <button type="submit" class="btn text-white px-4 py-2"
                            style="background-color: #ff4c4c; border-radius: 8px; font-size: 14px; font-weight: bold;"
                            name="offer">
                            再オファー
                        </button>
                    @else
                        <span class = "btn px-4 py-2 text-white" style="background-color:#666666; font-size: 14px; ">
                            オファー済み </span>
                    @endif
                    @if ($errors->has('offer'))
                        <div class="text-danger">
                            {{ $errors->first('offer') }}
                        </div>
                    @endif
                </form>

                {{--  <button class="offer-button"> オファー</button>  --}}
                <form action="{{ route('offer.cancel', ['id' => $job->order_code]) }}" method="POST">
                    @csrf

                    @if ($isOffer && $offerFlag === '1')
                        <button type="submit" class="btn text-white px-4 py-2"
                            style="background-color: #ff4c4c; border-radius: 8px; font-size: 14px; font-weight: bold;"
                            name="offercansel">
                            オファーキャンセル
                        </button>
                    @elseif ($offerFlag === '2')
                        <span class = "btn px-4 py-2 text-white" style="background-color:#666666; font-size: 14px; ">
                            オファーキャンセル中 </span>
                    @endif
                    {{--  エラーがあったときの項目はいったん削除  --}}
                </form>



                <button class="favorite-button">お気に入り登録</button>
            </div>
        </div>
    </body>

</html>
