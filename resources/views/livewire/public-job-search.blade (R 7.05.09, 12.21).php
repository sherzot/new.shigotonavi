<div class="container py-0">
    <div class="row align-items-start">
        
        <div class="col-12 text-center text-lg-start top-block">
            
            <!-- Block 4: SEARCH + FILTER -->
            <h5 id="trial-Search"></h5>
            <div class="col-12 mt-0" style="margin-top: -100px; z-index: 1020; position: relative;">
                <div class="bg-white text-dark rounded p-3 small" style="backdrop-filter: blur(8px); background-color: rgba(255,255,255,0.95); font-size: 0.85rem;">
                    {{--  <div class="d-flex align-items-start mb-3 p-3 bg-light rounded shadow-sm">
                        <div class="me-3 text-center">
                            <span class="badge btn btn-main-theme fs-6 px-3 py-2 d-block">Step②</span>
                        </div>
                        <div><h5 class="mb-0 fw-bold">自分で理想の求人を選んで、オファー</h5></div>
                    </div>   --}}
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
                    {{--  <div class="d-grid mt-2 mb-3">
                        <button class="btn btn-success btn-sm" onclick="submitInitialSearch()">
                            <i class="fas fa-search"></i> 検索
                        </button>
                    </div>  --}}
                    <div class="d-grid mt-2 mb-3">
                        <button class="btn btn-success btn-sm" onclick="submitInitialSearch()">
                            <i class="fas fa-search"></i> 検索
                        </button>
                    </div>                    
                </div>
            </div>
            {{--  <p class="text-success text-center mb-3 d-none d-sm-block">
                マッチングされた求人票: {{ count($jobs) }} 件 <span class="text-end text-primary small">{{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）更新</span>
                <i class="fa-solid fa-arrow-down text-danger"></i>
            </p>  --}}

            <div class="alert-job">
                <div class="alert-light custom-responsive-width m-auto" id="resultsBlock">
                    @if ($hasSearched)
                    <!-- Filters -->
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
                        マッチングされた求人票: {{ count($jobs) }} 件 <span class="text-end text-primary small">{{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）更新</span>
                        <i class="fa-solid fa-arrow-down text-danger"></i>
                    </p>
                    <p class="text-success text-center mb-3 d-block d-sm-none">
                        マッチングされた求人票: {{ count($jobs) }} 件 <br> <span class="text-end text-primary small">{{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）更新</span>
                        <i class="fa-solid fa-arrow-down text-danger"></i>
                    </p>
                    {{--  <h5 class="fw-bold py-3 fs-f28 text-secondary d-none d-sm-block">条件に合うベストな求人票を選んで、オファーボタンを <br> 押してエージェントに知らせよう。</h5>  --}}
                    <h5 class="fw-bold py-3 fs-f24 text-secondary d-none d-sm-block">理想の求人を選んでオファーボタンを押してください！　担当エージェントが内定までサポートします。</h5>
                    <h5 class="fw-bold py-3 text-start text-secondary d-block d-sm-none">条件に合う<br>ベストな求人票を選んで、<br>オファーボタンを押して<br>エージェントに知らせよう。</h5>
                    <!-- Table -->
                    <div class="row text-start">
                        @foreach ($jobs as $i => $job)
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow rounded-3 position-relative job-card" wire:key="job-{{ $job->id }}">
                                <a href="{{ route('jobs.detail', ['id' => $job->id]) }}" class="stretched-link"></a>
                    
                                <div class="card-body">
                                    {{-- 職種タイトル --}}
                                    <h5 class="fw-bold text-main-theme mb-3" style="font-size: 1.1rem;">
                                        {{ $job->job_type_detail ?? '' }}
                                        <span class="badge bg-secondary ms-2">
                                            @switch($job->order_type)
                                                @case(1)
                                                    派遣
                                                    @break
                                                @case(2)
                                                    正社員
                                                    @break
                                                @case(3)
                                                    契約社員
                                                    @break
                                                @default
                                                    -
                                            @endswitch
                                        </span>
                                    </h5>                                    
                    
                                    {{-- 線 --}}
                                    <hr class="my-2">
                    
                                    {{-- 給与 --}}
                                    <p class="mb-2">
                                        <span class="fw-bold text-secondary">給与例:</span>
                                        <span class="text-secondary">
                                            @if ($job->order_type == 2 && $job->yearly_income_min)
                                                年収 {{ number_format($job->yearly_income_min) }}円〜{{ number_format($job->yearly_income_max) }}円
                                            @elseif ($job->hourly_income_min)
                                                時給 {{ number_format($job->hourly_income_min) }}円〜{{ number_format($job->hourly_income_max) }}円
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </p>
                    
                                    {{-- 勤務地 --}}
                                    <p class="mb-3">
                                        <span class="fw-bold text-secondary">勤務地:</span>
                                        {{ $job->prefecture_names }}
                                    </p>
                    
                                    {{-- フラグ表示 --}}
                                    @if (!empty($job->selectedFlagsArray))
                                    <div class="mb-3 d-flex flex-wrap gap-1">
                                        @foreach ($job->selectedFlagsArray as $flag)
                                        <span class="badge bg-secondary border text-white small px-2 py-1 rounded-pill">
                                            {{ $flag }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                    
                                    {{-- ボタンと休日 --}}
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                        <a href="{{ route('jobs.detail', ['id' => $job->id]) }}" class="btn btn-primary btn-sm">
                                            求人票を見る
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    
                    @endif
                </div>
            </div>
            <!-- ✅ マーケティング文: PCサイズ -->
            <section class="d-none d-sm-block py-5 bg-dark text-white">
                <div class="container">
                    <div class="mx-auto" style="max-width: 900px;">
                        <h4 class="fw-bold mb-4 text-center text-warning">今年は採用革新の幕開け。効率採用システム</h4>
                        <ul class="list-unstyled fs-5 lh-lg text-start">
                            <li><i class="fa-solid fa-check text-warning"></i> 求職者は希望条件を入力して、<strong>ベストな1社</strong>に絞り込み、納得すれば　<br>「面談依頼する【オファー】」ボタンを押してエージェントに知らせます。</li>
                            <li><i class="fa-solid fa-check text-warning"></i> 求人企業は求人票の情報管理に責任を持ち、<strong>待つだけ</strong>。</li>
                            <li><i class="fa-solid fa-check text-warning"></i> エージェントは届いた【オファー】内容を確認し、<strong>双方にヒアリング</strong>。マッチすればつなぎ、<br>違えばやり直す適切なアドバイス。</li>
                        </ul>
                    </div>
                </div>
            </section>
    
            <!-- ✅ マーケティング文: スマホサイズ -->
            <section class="d-block d-sm-none py-4 bg-dark text-white">
                <div class="container">
                    <div class="mx-auto" style="max-width: 900px;">
                        <h5 class="fw-bold mb-3 text-center text-warning">今年は採用革新の幕開け。<br>効率採用システム</h5>
                        <div class="fs-6 lh-lg text-start">
                            <i class="fa-solid fa-check text-warning"></i> 求職者は希望条件を入力して、<br>
                            ベストな1社に絞り込み、<br>
                            納得すれば【オファー】ボタンで<br>
                            エージェントに通知。<br><br>
    
                            <i class="fa-solid fa-check text-warning"></i> 求人企業は求人票を管理し、<br>
                            待つだけの簡単運用。<br><br>
    
                            <i class="fa-solid fa-check text-warning"></i> エージェントはオファーを確認後、<br>
                            条件が合うか両者にヒアリング。<br>
                            マッチすれば繋ぎ、違えば<br>
                            アドバイスでやり直し！
                        </div>
                    </div>
                </div>
            </section>
        </div>
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
            {{--  sessionStorage.removeItem("scrollToResults");  --}}
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
        @this.set('big_class_code', big);
        @this.set('job_category', mid);
        @this.call('submitInitialSearch');
    }
</script>
@endpush
