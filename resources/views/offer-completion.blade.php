@extends('layouts.top')

@section('title', 'オファー完了')

@section('content')
<div class="container py-5 text-center">
    <div class="container my-5">
        {{-- <img src="{{ asset('img/steep.png') }}" class="img-fluid mt-0 mb-3 d-none d-sm-block" alt="Hero Image"> --}}
        <div class="d-flex justify-content-center align-items-center step-flow">

            <!-- Step 1 -->
            <div class="text-center">
                <div class="step-circle active">①</div>
                {{--  <div class="step-label">基本情報登録</div>  --}}
                <div class="step-label">基本情報</div>
            </div>
    
            <!-- Line -->
            <div class="step-line filled"></div>
    
            <!-- Step 2 -->
            <div class="text-center">
                <div class="step-circle active">②</div>
                {{--  <div class="step-label">希望条件登録</div>  --}}
                <div class="step-label ">オファー</div>
            </div>
        </div>
    </div>
    <!-- ✅ Title -->
    <h4 class="fw-bold mb-3 text-primary-emphasis">【しごとナビ求人票】へのオファーありがとうございました！</h4>
    {{-- <p class="text-muted mb-4">しごとナビ求人票へのオファーありがとうございます。</p>  --}}
    {{-- <!-- ✅ Banner Image -->
    <div class="mb-5">
        <img src="{{ asset('img/offer-complete-banner.png') }}" alt="Offer Complete" class="img-fluid mx-auto d-block" style="max-width: 480px;">
</div> --}}

<!-- ✅ Email notice -->
<div class="alert alert-light d-flex align-items-center justify-content-center shadow-sm mx-auto" role="alert" style="max-width: 700px;">
    <i class="bi bi-check-circle-fill me-2"></i>
    ご登録のメールアドレスに確認用のメールを送信しました。<br>
    オファー内容は、しごとナビエージェントが​
    転職支援サポートをさせていただきます。
</div>

<!-- ✅ Offered Jobs Section -->
@if (!empty($jobs) && count($jobs) > 0)
<h5 class="text-primary fw-bold mt-5 mb-3 border-bottom pb-1 d-inline-block">あなたがオファーした求人</h5>
<div class="row row-cols-1 g-4 justify-content-center mt-3">
    @foreach ($jobs as $job)
    @if ($job->offer_flag !== '3')
    <div class="col" style="max-width: 600px;">
        <div class="card border border-2 border-primary-subtle shadow-sm h-100 text-start">
            <a href="https://mch.shigotonavi.co.jp/matchings/detail/{{ $job->order_code }}" class="text-decoration-none">
                <div class="card-body">
                    <h6 class="card-title fw-bold text-primary mb-1">
                        {{ $job->order_code }}
                    </h6>
                    <p class="text-secondary small mb-2">{{ $job->job_type_detail }}</p>

                    @if ($job->offer_flag === '1')
                    <span class="badge rounded-pill text-success px-3 py-1 fs-7">にオファー済み</span>
                    @elseif ($job->offer_flag === '2')
                    <span class="badge rounded-pill text-main-theme px-3 py-1 fs-7">キャンセル確定済み、再オファーできます。</span>
                    @endif
                </div>
            </a>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endif

<!-- ✅ CTA Buttons -->
<div class="mt-5 d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3">
    <a href="{{ route('mypage') }}" class="btn btn-main-theme btn-lg px-4 shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> 利用中のサービスに戻る
    </a>
    <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-lg px-4 shadow-sm">
        ログアウトする
    </a>
</div>
</div>
@endsection
