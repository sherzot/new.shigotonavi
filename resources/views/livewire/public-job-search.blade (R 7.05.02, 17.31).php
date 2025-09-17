<div class="container py-5">
    <div class="row align-items-start">
        <!-- Block 1: HERO -->
        <div class="col-12 text-center text-lg-start top-block">
            {{-- <img src="{{ asset('img/toptop.jpg') }}" class="img-fluid mt-0 d-none d-sm-block" alt="Hero Image">
            <img src="{{ asset('img/toptop-sm.png') }}" class="img-fluid mt-0 d-block d-sm-none" alt="Hero Image"> --}}
            {{-- <img src="{{ asset('img/toptop2.png') }}" class="img-fluid mt-0 d-none d-sm-block" alt="Hero Image">
            <img src="{{ asset('img/toptop-sm2.png') }}" class="img-fluid mt-0 d-block d-sm-none" alt="Hero Image"> --}}
            <!-- Block 4: SEARCH + FILTER -->
            <h5 id="trial-Search"></h5>
            <div class="col-12 mt-0" style="margin-top: -100px; z-index: 1020; position: relative;">
                <div class="bg-white text-dark rounded shadow p-3 small" style="backdrop-filter: blur(8px); background-color: rgba(255,255,255,0.95); font-size: 0.85rem;">
                    <!-- Title -->
                    <h3 class="fw-bold mb-3 text-center" id="trial-Search">求人検索</h3>

                    <!-- Dropdowns -->
                    <div class="mb-2">
                        <select class="form-select form-select-sm mb-2" id="bigClassSelect">
                            <option value="">職種</option>
                            @foreach ($bigClasses as $class)
                            <option value="{{ $class->big_class_code }}">{{ $class->big_class_name }}</option>
                            @endforeach
                        </select>
                        <select class="form-select form-select-sm" id="jobCategorySelect">
                            <option value="">職種タイプ</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="d-grid mt-2 mb-3">
                        <button class="btn btn-success btn-sm" onclick="submitInitialSearch()">
                            <i class="fas fa-search"></i> 検索
                        </button>
                    </div>
                    <p class="text-success text-center mb-3">
                        マッチングされる求人票: {{ $jobs->count() }} 件 <span class="text-end text-primary small">{{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）更新</span>
                        {{-- <i class="fa-solid fa-arrow-down text-danger"></i>  --}}
                    </p>
                </div>
            </div>

            <div class="alert-job">
                <div class="alert-light custom-responsive-width m-auto" id="resultsBlock">
                    @if ($hasSearched)
                    <!-- Filters -->
                    {{--  <h5 class="fw-bold py-3 text-main-theme fs-f28 d-none d-sm-block">絞り込んで、条件に合うベストな求人票を選んで、オファーボタンを <br> 押してエージェントに知らせよう。</h5>  --}}
                    {{--  <h5 class="fw-bold py-3 text-main-theme d-block d-sm-none">絞り込んで、条件に合うベストな<br> 求人票を選んで、オファーボタンを<br>押してエージェントに知らせよう。</h5>  --}}
                    <div class="mb-2">
                        <select class="form-select form-select-sm mb-2" wire:model="salary">
                            <option value="">年収（給料）</option>
                            @foreach (range(100, 1000, 50) as $yen)
                            <option value="{{ $yen }}">{{ $yen }}万円以上</option>
                            @endforeach
                        </select>
                        <select class="form-select form-select-sm mb-2" wire:model="hourly_wage">
                            <option value="">時給</option>
                            @foreach (range(800, 3000, 100) as $yen)
                            <option value="{{ $yen }}">時給{{ $yen }}円以上</option>
                            @endforeach
                        </select>
                        <select class="form-select form-select-sm mb-2" wire:model="location">
                            <option value="">希望勤務地</option>
                            @foreach ($prefectures as $pref)
                            <option value="{{ $pref }}">{{ $pref }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Checkboxes -->
                    <div class="mb-2" style="max-height: 120px; overflow-y: auto; text-align: start;">
                        @foreach ($checkboxOptions as $key => $label)
                        <label class="btn btn-outline-secondary btn-sm me-1 mb-1">
                            <input type="checkbox" wire:model="supplementFlags" value="{{ $key }}">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>

                    <button class="btn btn-success btn-sm w-100 mb-2" wire:click="searchJobs">
                        <i class="fas fa-search"></i> さらに絞り込み
                    </button>
                    <p class="text-success text-center mb-3 d-none d-sm-block">
                        マッチングされた求人票: {{ $jobs->count() }} 件 <span class="text-end text-primary small">{{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）更新</span>
                        <i class="fa-solid fa-arrow-down text-danger"></i>
                    </p>
                    <p class="text-success text-center mb-3 d-block d-sm-none">
                        マッチングされた求人票: {{ $jobs->count() }} 件 <br> <span class="text-end text-primary small">{{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）更新</span>
                        <i class="fa-solid fa-arrow-down text-danger"></i>
                    </p>
                    {{--  <h5 class="fw-bold py-3 fs-f28 text-main-theme d-none d-sm-block">さあ、あなたの条件に合うベストな求人票を選んで、オファーボタンを <br> 押してエージェントに知らせよう。</h5>  --}}
                    {{--  <h5 class="fw-bold py-3 text-main-theme d-block d-sm-none">さあ、あなたの条件に合うベストな<br> 求人票を選んで、オファーボタンを<br>押してエージェントに知らせよう。</h5>  --}}
                    <!-- Table -->

                    <div class="border rounded shadow-sm p-2 mb-3 scroll-jobs" style="max-height: 300px; overflow-y: auto;">
                        @foreach ($jobs as $i => $job)
                        <div class="border rounded p-3 mb-2 bg-white position-relative" wire:key="job-{{ $job->id }}">
                            {{-- HIDDEN LINK to stretch full block --}}
                            <a href="{{ route('jobs.detail', ['id' => $job->id]) }}" class="stretched-link"></a>

                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start">
                                <div class="flex-grow-1 text-start">
                                    <h6 class="mb-1 fw-bold text-primary">
                                        {{ $job->job_type_detail ?? '' }}
                                    </h6>

                                    <div class="small text-muted">
                                        @if (!empty($job->prefecture_names))
                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                            @foreach (explode(',', $job->prefecture_names) as $prefecture)
                                            <span class="badge bg-light border text-dark">
                                                {{ trim($prefecture) }}
                                            </span>
                                            @endforeach
                                        </div>
                                        @endif

                                        <span class="badge bg-success me-1">
                                            @switch($job->order_type)
                                            @case(1) 派遣 @break
                                            @case(2) 正社員 @break
                                            @case(3) 紹介予定派遣 @break
                                            @endswitch
                                        </span>

                                        <span class="badge bg-primary text-white my-1">
                                            @if ($job->order_type == 2 && $job->yearly_income_min)
                                            {{ number_format($job->yearly_income_min) }}〜{{ number_format($job->yearly_income_max) }}円
                                            @elseif ($job->hourly_income_min)
                                            {{ number_format($job->hourly_income_min) }}〜{{ number_format($job->hourly_income_max) }}円
                                            @else
                                            -
                                            @endif
                                        </span>
                                    </div>

                                    @if (!empty($job->selectedFlagsArray))
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($job->selectedFlagsArray as $flag)
                                        <span class="badge rounded-pill border text-dark small" style="font-weight: 500;">
                                            {{ $flag }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- ✅ マーケティング文 {{-- ✅ PCサイズ用 --}}-->
        <section class="pt-4 alert alert-light border marketing d-none d-sm-block">
            <div class="container text-dark d-flex justify-content-center">
                <div class="w-100 text-white fs-f22" style="max-width: 900px;">
                    <h5 class="fw-bold text-white fs-f28 py-2">今年は採用革新の幕開け。効率採用システム</h5>
                    -求職者は希望条件を入力して、ベストな1社に絞り込み、
                    納得すれば面談依頼【オファー】ボタンを押してエージェントに知らせます。<br><br>

                    -求人企業は求人票の情報管理に責任を持ち待つだけ。<br><br>
                    -エージェントは届いた面談依頼【オファー】内容を確認して、求人条件とマッチして<br>いるかを
                    両者にヒアリングし、引き合わせる価値があるかを判断し、<br>
                    つなぐかやり直すかを適切にアドバイスし、活用に進めます。
                </div>
            </div>
        </section>
        <!-- ✅ マーケティング文  {{-- ✅ スマホサイズ用 --}}-->
        <section class="pt-4 alert alert-light border marketing d-block d-sm-none">
            <div class="container text-dark text-start d-flex justify-content-center">
                <div class="w-100 text-white" style="max-width: 900px;">
                    <h5 class="fw-bold text-white">今年は採用革新の幕開け。効率採用システム</h5>
                    -求職者は希望条件を入力して、ベストな1社に絞り込み、
                    納得すればオファーボタンを<br>押してエージェントに知らせます。<br><br>

                    -求人企業は求人票の情報管理に責任を<br>持ち待つだけ。<br>
                    -エージェントは届いたオファー内容を<br>確認して、求人条件とマッチしているかを<br>
                    両者にヒアリングし、引き合わせる価値が<br>あるかを判断し、
                    つなぐかやり直すかを<br>適切にアドバイスし、活用に進めます。
                </div>
            </div>
        </section>
    </div>
</div>
</div>

@push('styles')
<style>
    .stretched-link {
        z-index: 1;
    }

    .position-relative {
        position: relative;
    }

    .mobile-static-search {
        position: absolute;
        right: 10px;
        /* ✅ */
        top: 60px;
        max-width: 600px;
        width: 100%;
        box-sizing: border-box;
    }

    .scroll-jobs::-webkit-scrollbar {
        width: 6px;
    }

    .scroll-jobs::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 3px;
    }

    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination .page-item {
        margin: 2px 3px;
    }

    @media (max-width: 768px) {
        .mobile-static-search {
            position: static !important;
            margin-top: 1rem !important;
            width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
            overflow-y: visible !important;
            padding-left: 1rem;
            padding-right: 1rem;
            box-sizing: border-box !important;
        }
    }

    @media (max-width: 500px) {
        .mobile-static-search {
            position: static !important;
            margin-top: 2rem !important;
            width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
            overflow-y: visible !important;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        td a.btn {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        td {
            font-size: 0.85rem;
        }

        .pagination .page-link {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .stats-icon {
            width: 48px;
            height: 48px;
        }
    }

    .marketing {
        margin: 0px;
        background: #3F5EFB;
        background: radial-gradient(circle, rgba(63, 94, 251, 1) 0%, rgba(252, 70, 107, 1) 100%);
    }

    .custom-responsive-width {
        width: 100%;
        background-color: rgb(255, 255, 255);
        padding: 0 10px;
    }

    .job-prefecture-names {
        white-space: normal !important;
        /* ⚠️ majburiy o‘rash */
        word-break: break-word;
        /* Uzun so‘zlar ajralsin */
        overflow-wrap: break-word;
        display: block;
    }


    @media (min-width: 768px) {
        .custom-responsive-width {
            width: 80%;
        }
    }

    @media (min-width: 992px) {
        .custom-responsive-width {
            width: 100%;
        }
    }

    .scroll-jobs {
        max-height: 300px;
        overflow-y: auto;
    }

    .scroll-jobs::-webkit-scrollbar {
        width: 6px;
    }

    .scroll-jobs::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 3px;
    }

    @media (max-width: 768px) {
        .scroll-jobs {
            max-height: 50vh;
        }
    }

        {
            {
            -- .alert-job {
                background: #020024;
                background: linear-gradient(90deg, rgba(2, 0, 36, 1) 0%, rgba(9, 9, 121, 1) 35%, rgba(0, 212, 255, 1) 100%);
            }

            --
        }
    }

</style>
@endpush


@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 🔽 Scroll flag mavjudmi
        if (sessionStorage.getItem("scrollToResults") === "true") {
            const block = document.getElementById("resultsBlock");
            if (block) {
                block.scrollIntoView({
                    behavior: "smooth"
                    , block: "start"
                });
            }
            // 🔸 Bitta marta ishlatish uchun flagni o'chirib tashlaymiz
            sessionStorage.removeItem("scrollToResults");
        }
        const bigClassSelect = document.getElementById("bigClassSelect");
        const jobCategorySelect = document.getElementById("jobCategorySelect");

        const savedBig = sessionStorage.getItem("big_class_code");
        const savedMid = sessionStorage.getItem("job_category");

        if (savedBig) bigClassSelect.value = savedBig;

        if (savedBig) {
            fetch(`/api/job-categories/${savedBig}`)
                .then(res => res.json())
                .then(data => {
                    jobCategorySelect.innerHTML = `<option value="">職種タイプ</option>`;
                    data.forEach(item => {
                        const opt = document.createElement("option");
                        opt.value = item.middle_class_code;
                        opt.textContent = item.middle_clas_name;
                        if (item.middle_class_code === savedMid) opt.selected = false;
                        jobCategorySelect.appendChild(opt);
                    });
                });
        }

        bigClassSelect.addEventListener("change", function() {
            const selectedCode = this.value;
            sessionStorage.setItem("big_class_code", selectedCode);
            jobCategorySelect.innerHTML = '<option value="">職種タイプ</option>';
            if (selectedCode) {
                fetch(`/api/job-categories/${selectedCode}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(item => {
                            const opt = document.createElement("option");
                            opt.value = item.middle_class_code;
                            opt.textContent = item.middle_clas_name;
                            jobCategorySelect.appendChild(opt);
                        });
                    });
            }
        });

        jobCategorySelect.addEventListener("change", function() {
            sessionStorage.setItem("job_category", this.value);
        });
    });

    function submitInitialSearch() {
        const big = document.getElementById("bigClassSelect").value;
        const mid = document.getElementById("jobCategorySelect").value;

        // 🔹 Scroll flag'ni saqlab qo'yamiz
        sessionStorage.setItem("scrollToResults", "true");

        fetch(`/initial-search`, {
            method: 'POST'
            , headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                , 'Content-Type': 'application/json'
            }
            , body: JSON.stringify({
                big_class_code: big
                , job_category: mid
            })
        }).then(() => window.location.reload());
    }

</script>
@endpush
