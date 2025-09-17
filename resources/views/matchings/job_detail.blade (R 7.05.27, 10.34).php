@extends('layouts.top')

@section('title', '仕事の詳細')
@section('content')
@php
use Illuminate\Support\Str;
//$request->session()->get('errors');
@endphp
<div class="container">

    <h2 class="text-center my-4">求人票詳細</h2>
    @if (session('error'))
    <div class="bg-danger">{{ session('error') }}</div>
    @endif
    <p><span class=" text-start">{{ $job->job_type_detail }} -
            ({{ $job->order_code }})</span><a class="text-break text-main-theme fs-f18" href="#offer">この求人にオファーする</a></p>
    {{-- <p class="text-end">  </p>  --}}
    <hr style="color: #ea544a; padding: 2px;">
    <div class="tags">
        @if (!empty($selectedFlagsArray))
        <div class="d-flex flex-wrap">
            @foreach ($selectedFlagsArray as $flag)
            @if (array_key_exists($flag, $checkboxOptions))
            <span class="badge bg-white text-secondary border border-secondary me-2 mb-2 p-1">{{ $checkboxOptions[$flag] }}</span>
            @endif
            @endforeach
        </div>
        @else
        <p>&nbsp;</p>
        @endif
    </div>

    <div class="card border p-4 mb-4">
        <p class="jobdetail"><strong>企業PR</strong></p>

        @for ($i = 1; $i <= 3; $i++) @php $titleVar="pr_title{$i}" ; $contentVar="pr_contents{$i}" ; @endphp @if (!empty($job->$titleVar) || !empty($job->$contentVar))
            <div class="p-3 border rounded mb-3">
                <h6 class="fw-bold text-secondary">{{ e($job->$titleVar) }}</h6>
                <hr style="color: #ea544a; padding: 2px;">
                <p class="text-dark">{!! Str::replace("\n", '<br>', e($job->$contentVar ?? '')) !!}</p>
            </div>
            @endif
            @endfor
    </div>


    <!-- 仕事内容 (Ish tavsifi) -->
    <div class="card border p-4 mb-4">
        <p class="jobdetail"><strong>担当業務</strong></p>

        <div class="p-3 border rounded ">
            <p class="text-dark">{!! Str::replace("\n", '<br>', e($job->business_detail ?? '')) !!}</p>
        </div>
    </div>

    <!-- ✅ 勤務条件 (Ish sharoitlari) -->
    <div class="card border p-4 mb-4">
        {{-- <p class="jobdetail"><strong>勤務条件</strong></p>  --}}

        <!-- ✅ 勤務形態 (Ish turi) -->
        <div class="p-3 border-bottom">
            <h6 class="fw-bold text-secondary">勤務形態</h6>
            <h6 class="text-start col-6 fs-6">
                @if (optional($job)->order_type == 1)
                <span class="text-dark">派遣</span>
                @elseif (optional($job)->order_type == 2)
                <span class="text-dark">紹介</span>
                @elseif (optional($job)->order_type == 3)
                <span class="text-dark">紹介予定派遣</span>
                @else
                <span class="text-dark">不明</span>
                @endif
            </h6>
        </div>

        <!-- ✅ 職種 (Kasb turi) -->
        <div class="p-3 border-bottom">
            <h6 class="fw-bold text-secondary">職種</h6>
            <p class="text-dark">{{ $job->job_type_detail ?? '' }}</p>
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
            {{-- <h6 class="fw-bold text-secondary">勤務時間について</h6>  --}}
            <p class="text-dark">{!! Str::replace("\n", '<br>', e($job->work_time_remark ?? '')) !!}</p>
        </div>

        <!-- ✅ 休日 (Dam olish kunlari) -->
        <div class="p-3 border-bottom">
            <h6 class="fw-bold text-secondary">休日</h6>
            <p class="text-dark">{!! Str::replace("\n", '<br>', e($job->holiday_remark ?? '')) !!}</p>
        </div>

        <!-- ✅ 勤務地 (Ish joylari) -->
        <div class="p-3">
            <h6 class="fw-bold text-secondary">勤務地</h6>

            @foreach ($locations as $location)
            <p class="text-dark">
                {{ $location->prefecture }}
                {{ $location->city ?? '' }}
                {{-- {{ $location->town ?? '町情報なし' }}
                {{ $location->address ?? '住所情報なし' }} --}}
            </p>
            @endforeach
        </div>
    </div>
    <div class="container my-3" id="offer">
        @php
            use Illuminate\Support\Facades\Auth;
            use Illuminate\Support\Facades\DB;

            $staffCode = Auth::id();

            $jobId = $job->order_code ?? null;

            // Get order_type
            //$orderType = DB::table('job_order')->where('order_code', $jobId)->value('order_type');

            // Only check resume if order_type = 2
            //$hasResume = true;
            //if ($orderType == 2) {
            //$hasResume =
            // DB::table('person_career_history')->where('staff_code', $staffCode)->exists() &&
            //DB::table('person_educate_history')->where('staff_code', $staffCode)->exists() &&
            //DB::table('person_self_pr')->where('staff_code', $staffCode)->exists();
            //}
            $hasResume =
            DB::table('person_educate_history')->where('staff_code', $staffCode)->exists();
            //&&
            //DB::table('person_career_history')->where('staff_code', $staffCode)->exists() &&
            //DB::table('person_self_pr')->where('staff_code', $staffCode)->exists();

            $isOffer = DB::table('person_offer')
            ->where('staff_code', $staffCode)
            ->where('order_code', $job->order_code)
            ->where('offer_flag', '1')
            ->exists();
            $isCanceled = DB::table('person_offer')
            ->where('staff_code', $staffCode)
            ->where('order_code', $job->order_code)
            ->where('offer_flag', '2')
            ->exists();
            $isCompleted = DB::table('person_offer')
            ->where('staff_code', $staffCode)
            ->where('order_code', $job->order_code)
            ->where('offer_flag', '3')
            ->exists();
            $hasActiveOffer = DB::table('person_offer')
            ->where('staff_code', $staffCode)
            ->where('offer_flag', '1')
            ->exists();
            $imageName = 'offerbutton-off.svg'; // default: disabled

            if (Auth::check() && $hasResume && !$isOffer && !$hasActiveOffer) {
            $imageName = 'offerbutton.svg'; // この場合のみアクティブボタン
            }

            // アクティビティ検出（この場合のみ送信されます）
            $isActive = Auth::check() && $hasResume && !$isOffer && !$hasActiveOffer;
        @endphp

        <div class="row d-flex flex-wrap justify-content-center gap-2">
            <div class="col-12 col-md-auto text-center">
                <form action="{{ route('offer.regist.submit', ['id' => $job->order_code]) }}" method="POST">
                    @csrf
                
                    @php
                        // 🔒 Default tugma (disable qilingan)
                        $imageName = 'offerbutton-off.svg';
                
                        // ✅ Agar foydalanuvchi login bo‘lsa, resume to‘liq bo‘lsa, va hali offer qilmagan bo‘lsa
                        if (Auth::check() && $hasResume && !$isOffer && !$hasActiveOffer) {
                            $imageName = 'offerbutton.svg'; // Faol holat
                        }
                
                        // ✅ Faol holatni aniqlab olish
                        $isActive = Auth::check() && $hasResume && !$isOffer && !$hasActiveOffer;
                    @endphp
                
                    {{-- 🔔 Tushuntirish xabari (auth, resume holatiga qarab) --}}
                    @if (!Auth::check())
                        <p class="text-main-theme fs-5 mt-2">オファーには会員登録またはログインが必要です。
                            <a href="{{ route('register.form') }}">登録はこちら</a>
                        </p>
                    @elseif (!$hasResume)
                        <p class="text-main-theme fs-5 mt-2">オファーには「履歴書」が必要です！
                            <a href="{{ route('resume.basic-info') }}" onclick="storeJobSession('{{ $job->order_code }}')">こちらで簡単に作成</a>
                        </p>
                    @endif
                
                    {{-- 🟠 Tugma / Rasm ko‘rinishida --}}
                    <div class="text-center">
                        @if ($isActive)
                            <button type="submit" name="offer" style="all: unset; cursor: pointer;">
                                <img src="{{ asset('img/' . $imageName) }}" class="img-fluid w-100" style="max-width: 400px;" alt="オファーボタン">
                            </button>
                        @else
                            <img src="{{ asset('img/' . $imageName) }}" class="img-fluid w-100" style="max-width: 400px;" alt="1オファーボタン">
                        @endif
                    </div>
                </form>                
            </div>
        </div>
    </div>

    <div class="my-4 px-0 m-auto">
        {{-- <h4 class="text-center py-3">オファーの長離</h4>  --}}
        <img src="{{ asset('img/offer4.png') }}" class="img-fluid w-100">
    </div>
</div>
@endsection
