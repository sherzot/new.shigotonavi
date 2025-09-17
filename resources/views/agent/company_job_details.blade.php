@extends('layouts.layout')

@section('title', '求人票詳細')

@section('content')
<div class="row column_title">
    <div class="col-md-12">
        <div class="page_title">
            <a href="{{ route('company.dashboard') }}">
                <img class="img-responsive" src="{{ asset('img/logo02.png') }}" alt="#" style="width: 150px;" />
            </a>
        </div>
    </div>
</div>
    <div class="container py-4">
        <h2 class="text-center mb-4">求人票詳細</h2>

        @if (session('error'))
            <div class="alert alert-danger text-center">{{ session('error') }}</div>
        @endif

        <div class="card shadow p-4">
            <h4 class="text-primary">{{ $jobDetails->job_type_detail }} - ({{ $jobDetails->order_code }})</h4>
            <hr class="border-danger">

            <!-- ✅ 企業PR (Kompaniya PR) -->
            <div class="mb-4">
                <h5 class="fw-bold text-secondary">企業PR</h5>
                @for ($i = 1; $i <= 3; $i++)
                    @php
                        $titleVar = "pr_title{$i}";
                        $contentVar = "pr_contents{$i}";
                    @endphp

                    @if (!empty($jobDetails->$titleVar) || !empty($jobDetails->$contentVar))
                        <div class="border rounded bg-light p-3 mb-3">
                            <h6 class="fw-bold">{{ e($jobDetails->$titleVar) }}</h6>
                            <p class="text-dark">{!! nl2br(e($jobDetails->$contentVar ?? '情報なし')) !!}</p>
                        </div>
                    @endif
                @endfor
            </div>

            <!-- ✅ 仕事内容 (Ish tavsifi) -->
            <div class="mb-4">
                <h5 class="fw-bold text-secondary">担当業務</h5>
                <div class="border rounded bg-light p-3">
                    <p class="text-dark">{!! nl2br(e($jobDetails->business_detail ?? '情報なし')) !!}</p>
                </div>
            </div>

            <!-- ✅ 勤務条件 (Ish sharoitlari) -->
            <div class="mb-4">
                <h5 class="fw-bold text-secondary">勤務条件</h5>

                <!-- ✅ 勤務形態 (Ish turi) -->
                <div class="border-bottom pb-2">
                    <h6 class="fw-bold">勤務形態:</h6>
                    <p class="text-dark">
                        @switch(optional($jobDetails)->order_type)
                            @case(1)
                                派遣
                            @break

                            @case(2)
                                紹介
                            @break

                            @case(3)
                                紹介予定派遣
                            @break

                            @default
                                不明
                        @endswitch
                    </p>
                </div>

                <!-- ✅ 給与 (Ish haqi) -->
                <div class="border-bottom pb-2 mt-3">
                    <h6 class="fw-bold">給与:</h6>
                    <p class="text-dark">
                        @if (!empty($jobDetails->hourly_income_min) && $jobDetails->hourly_income_min > 0)
                            時給 {{ number_format($jobDetails->hourly_income_min) }}円
                            {{ !empty($jobDetails->hourly_income_max) && $jobDetails->hourly_income_max > 0 ? '〜' . number_format($jobDetails->hourly_income_max) . '円' : '' }}
                        @elseif (!empty($jobDetails->yearly_income_min) && $jobDetails->yearly_income_min > 0)
                            年収 {{ number_format($jobDetails->yearly_income_min) }}円
                            {{ !empty($jobDetails->yearly_income_max) && $jobDetails->yearly_income_max > 0 ? '〜' . number_format($jobDetails->yearly_income_max) . '円' : '' }}
                        @else
                            未設定
                        @endif
                    </p>
                </div>

                <!-- ✅ 勤務地 (Ish joylari) -->
                <div class="mt-3">
                    <h6 class="fw-bold">勤務地:</h6>
                    @if (count($all_prefectures) > 0)
                        @foreach ($all_prefectures as $prefecture)
                            <p class="text-dark">{{ $prefecture->prefecture }} - {{ $prefecture->city ?? '市区町村情報なし' }}</p>
                        @endforeach
                    @else
                        <p class="text-muted">勤務地情報なし</p>
                    @endif
                </div>
            </div>

            <!-- ✅ 必要な資格 (Kerakli sertifikatlar) -->
            <div class="mb-4">
                <h5 class="fw-bold text-secondary">必要な資格</h5>
                @if (count($licenses) > 0)
                    <ul class="list-group">
                        @foreach ($licenses as $license)
                            <li class="list-group-item">{{ $license->category_name }} - {{ $license->name }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">資格情報なし</p>
                @endif
            </div>

            <!-- ✅ 必要なスキル (Kerakli skiller) -->
            <div class="mb-4">
                <h5 class="fw-bold text-secondary">必要なスキル</h5>
                @if (count($skills) > 0)
                    <ul class="list-group">
                        @foreach ($skills as $skill)
                            <li class="list-group-item">{{ $skill->category_code }} -
                                {{ isset($skill->name) ? $skill->name : '' }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">スキル情報なし</p>
                @endif
            </div>

            <!-- ✅ 備考 (Eslatmalar) -->
            <div class="mb-4">
                <h5 class="fw-bold text-secondary">備考</h5>
                <p class="text-dark">{!! nl2br(e($jobNoteData->note ?? '備考情報なし')) !!}</p>
            </div>

            <!-- ✅ ボタン群 (Tugmalar) -->

            <div class="text-center mt-4">
                <div class="row row-cols-2 row-cols-md-4 g-2">
                    <div class="col">
                        <button type="button" onClick="history.back()" class="btn btn-primary w-100 m-1">
                            <i class="fa-solid fa-arrow-left"></i> 戻る
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn btn-grey w-100 m-1" id="agentStartJobButton"
                            data-order="{{ $jobDetails->order_code }}">
                            <i class="fa-solid fa-bell text-dark"></i> 募集開始
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn btn-danger w-100 m-1" id="agentPauseJobButton"
                            data-order="{{ $jobDetails->order_code }}">
                            <i class="fa-solid fa-bell-slash"></i> 募集の一時停止
                        </button>
                    </div>
                    <div class="col">
                        <a href="{{ route('agent.agentJobEdit', ['order_code' => $jobDetails->order_code]) }}"
                            class="btn btn-primary w-100 m-1">
                             <i class="fa-solid fa-file-pen"></i> 変更する
                         </a>                         
                    </div>
                </div>
            </div>
            
            <script>
                function updateJobStatus(url, message, button) {
                    let orderCode = button.getAttribute('data-order');
                    
                    if (!orderCode) {
                        alert("❌ 求人コードが指定されていません。");
                        return;
                    }
            
                    if (!confirm(message)) {
                        return;
                    }
            
                    fetch(url, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                order_code: orderCode
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === "success") {
                                alert(data.message);
                                location.reload();
                            } else {
                                alert("エラー：" + data.message);
                            }
                        })
                        .catch(error => console.error("エラー:", error));
                }
            
                // ✅ "this" ni uzatamiz — bu eng muhim fix
                document.getElementById('agentPauseJobButton').addEventListener('click', function () {
                    updateJobStatus("{{ route('agent.jobs.pause') }}", "本当にこの求人を一時停止しますか？", this);
                });
            
                document.getElementById('agentStartJobButton').addEventListener('click', function () {
                    updateJobStatus("{{ route('agent.jobs.start') }}", "本当にこの求人を開始しますか？", this);
                });
            </script>
            
        </div>
    </div>
@endsection
