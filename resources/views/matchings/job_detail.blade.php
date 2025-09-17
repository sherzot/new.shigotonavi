@extends('layouts.top')

@section('title', '求人票詳細')
@section('content')
@php
use Illuminate\Support\Str;
//$request->session()->get('errors');
@endphp
<div class="container py-4">

    <h2 class="text-center my-4">求人票詳細</h2>
    <!-- 🔹 Job Summary Header -->
    <div class="bg-white mb-4">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h4 class="fw-bold text-main-theme mb-3 fs-4">
                    {{ $job->job_type_detail }}　<a href="#offer" class="btn btn-primary btn-sm">この求人にオファーする<i class="fa-solid fa-arrow-down px-2"></i></a>
                </h4>
                
                <div class="p-3">
                    <p class="mb-2"><strong>求人ID：</strong>{{ $job->order_code }}</p>
                    <p class="mb-2"><strong>勤務形態：</strong>{{ $job->order_type == 1 ? '派遣' : ($job->order_type == 2 ? '正社員' : ($job->order_type == 3 ? '紹介予定派遣' : '不明')) }}</p>
                    <p class="mb-2"><strong>給与：</strong>
                        @if ($job->hourly_income_min > 0)
                            時給 {{ number_format($job->hourly_income_min) }}円
                            @if ($job->hourly_income_max > 0)
                                〜{{ number_format($job->hourly_income_max) }}円
                            @else
                                〜
                            @endif
                        @elseif ($job->yearly_income_min > 0)
                            年収 {{ number_format($job->yearly_income_min) }}円
                            @if ($job->yearly_income_max > 0)
                                〜{{ number_format($job->yearly_income_max) }}円
                            @else
                                〜
                            @endif
                        @else
                            
                        @endif
                    </p>
                </div>
                <!-- 🔹 Feature Tags -->
                @if (!empty($selectedFlagsArray))
                    <div class="mb-4 d-flex flex-wrap gap-2">
                        @foreach ($selectedFlagsArray as $flag)
                            @if (isset($checkboxOptions[$flag]))
                                <span class="badge bg-secondary-subtle border border-secondary text-dark px-3 py-2 rounded-pill">{{ $checkboxOptions[$flag] }}</span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
            {{--  <div class="col-md-3 text-md-end text-center mt-4 mt-md-0">
                
            </div>  --}}
        </div>
    </div>
    @if ($jobTypes && $jobTypes->count())
        <h5 class="fw-bold border-start border-4 border-warning ps-3 mb-3">職種</h5>
        <div class="bg-white border rounded shadow-sm p-3 mb-4">
            @foreach ($jobTypes as $index => $type)
                <p class="mb-1">({{ $index + 1 }}) {{ $type->big_class_name ?? '' }}　{{ $type->middle_clas_name ?? '' }}</p>
            @endforeach
        </div>
    @endif

    <!-- 🔹 企業PR -->
    <div class="mb-4">
        <h5 class="fw-bold border-start border-4 border-primary ps-3 mb-3">企業PR</h5>
        @for ($i = 1; $i <= 3; $i++)
            @php $title = "pr_title{$i}"; $content = "pr_contents{$i}"; @endphp
            @if (!empty($job->$title) || !empty($job->$content))
                <div class="mb-3 px-3 py-2 bg-white border rounded shadow-sm">
                    @if ($job->$title)
                        <h6 class="fw-bold text-primary">{{ e($job->$title) }}</h6>
                    @endif
                    <p class="mb-0">{!! Str::replace("\n", '<br>', e($job->$content ?? '')) !!}</p>
                </div>
            @endif
        @endfor
    </div>
    @if ($company)
        <h5 class="fw-bold border-start border-4 border-info ps-3 mb-4">紹介先企業情報</h5>
        <div class="bg-white border rounded shadow-sm p-4 mb-4">
            <div class="mb-2">
                <p class="mb-1"><strong class="text-secondary me-3">業種：</strong>{{ $company->industry_type_name ?? 'ー' }}</p>
            </div>

            <div class="mb-2">
                <p class="mb-1"><strong class="text-secondary me-3">事業内容：</strong>{{ $company->business_contents ?? 'ー' }}</p>
            </div>

            <div class="mb-2">
                <p class="mb-1"><strong class="text-secondary me-3">資本金：</strong>{{ $company->capital_amount ?? 'ー' }}</p>
            </div>

            <div>
                <p class="mb-0"><strong class="text-secondary me-3">社員数：</strong>{{ $company->all_employee_num ?? 'ー' }}</p>
            </div>
        </div>
    @endif


    <!-- 🔹 担当業務 -->
    <div class="mb-4">
        <h5 class="fw-bold border-start border-4 border-warning ps-3 mb-3">担当業務</h5>
        <div class="px-3 py-2 bg-white border rounded shadow-sm">
            <p class="mb-0 text-dark">{!! Str::replace("\n", '<br>', e($job->business_detail ?? '')) !!}</p>
        </div>
    </div>

    <!-- 🔹 勤務時間・休日 -->
    <h5 class="fw-bold border-start border-4 border-success ps-3 mb-4">勤務時間・休日</h5>
    <div class="bg-white border rounded shadow-sm p-4 mb-5">
        <div class="mb-3">
            <h6 class="fw-bold text-secondary">勤務時間</h6>
            @if (!empty($workingTime))
                <p>{{ substr_replace($workingTime->work_start_time, ':', 2, 0) }} - {{ substr_replace($workingTime->Work_end_time, ':', 2, 0) }}</p>
                <p>休憩: {{ substr_replace($workingTime->rest_start_time, ':', 2, 0) }} - {{ substr_replace($workingTime->rest_end_time, ':', 2, 0) }}</p>
            @else
                
            @endif
        </div>
        <div class="mb-3">
            <h6 class="fw-bold text-secondary">休日</h6>
            <p class="mb-0">{!! Str::replace("\n", '<br>', e($job->holiday_remark ?? '')) !!}</p>
        </div>
    </div>

    <h5 class="fw-bold border-start border-4 border-success ps-3 mb-4">勤務地</h5>
    <div class="bg-white border rounded shadow-sm p-4 mb-5">
        <div class="mb-3">
            <p class="mb-0">
                @foreach ($locations as $location)
                    {{ $location->prefecture }}{{ $location->city ? ' ' . $location->city : '' }}<br>
                @endforeach
            </p>
        </div>
    </div>
    @if ($job->process1 || $job->process2 || $job->process3 || $job->process4)
    <h5 class="fw-bold border-start border-4 border-warning ps-3 mb-3">選考手順</h5>
        <div class="bg-white border rounded shadow-sm p-4 mb-4">
            @php
                $processes = [
                    $job->process1,
                    $job->process2,
                    $job->process3,
                    $job->process4,
                ];
            @endphp

            <div class="ps-2">
                @foreach ($processes as $i => $process)
                    @if (!empty($process))
                        <p class="mb-1">
                            ステップ{{ $i + 1 }}　{{ $process }}
                            @if ($i < count($processes) - 1 && !empty($processes[$i + 1]))
                                <br>▼
                            @endif
                        </p>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

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
            DB::table('person_educate_history')->where('staff_code', $staffCode)->exists()
            &&
            DB::table('person_career_history')->where('staff_code', $staffCode)->exists();
            //&&
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
            $imageName = 'offer-button-off.svg'; // default: disabled

            if (Auth::check() && $hasResume && !$isOffer && !$hasActiveOffer) {
            $imageName = 'offer-button.svg'; // この場合のみアクティブボタン
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
                        $imageName = 'offer-button-off.svg';
                
                        // ✅ Agar foydalanuvchi login bo‘lsa, resume to‘liq bo‘lsa, va hali offer qilmagan bo‘lsa
                        if (Auth::check() && $hasResume && !$isOffer && !$hasActiveOffer) {
                            $imageName = 'offer-button.svg'; // Faol holat
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
