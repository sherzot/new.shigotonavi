<div>
    <!-- HERO SECTION + SEARCH UI -->
    <div class="container-fluid bg-dark text-white py-5 px-0">
        <div class="container">
            <div class="row align-items-center justify-content-center text-center text-lg-start">
                <!-- Image Side -->
                <div class="col-lg-6 mb-4 mb-lg-0 text-center">
                    <img src="https://mch.shigotonavi.co.jp/img/hero-img.png" class="img-fluid" alt="Hero Image"
                        style="max-height: 300px;">
                </div>

                <!-- Text and Search -->
                <div class="col-lg-6">
                    <h1 class="fw-bold display-5">しごとナビにようこそ!</h1>
                    {{-- <p class="lead">Connecting Talent with Opportunity: Your Gateway to Career Success</p> --}}

                    <!-- Search Box -->
                    <div class="bg-white text-dark rounded shadow p-4 mt-4">
                        <h4 class="fw-bold mb-3">お試し検索</h4>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select class="form-select border-dark" id="bigClassSelect">
                                    <option value="">職種</option>
                                    @foreach ($bigClasses as $class)
                                        <option value="{{ $class->big_class_code }}">{{ $class->big_class_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <select class="form-select border-dark" id="jobCategorySelect">
                                    <option value="">職種タイプ</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-grid mt-3">
                            <button class="btn btn-success" onclick="submitInitialSearch()">
                                <i class="fas fa-search"></i> 検索
                            </button>
                        </div>
                        <div class="mt-3">
                            @if ($hasSearched)
                                <h5 class="mb-4 text-success">マッチングされた求人票一覧: {{ $jobs->total() }} 件 <span
                                        class="text-main-theme"><i class="fa-solid fa-down-long"></i></span></h5>
                            @endif
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="row text-white text-center mt-5 g-3">
                        <div class="col-4">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="fas fa-briefcase fa-lg me-2"></i>
                                <strong>25,850</strong><span class="ms-1">求人票</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="fas fa-users fa-lg me-2"></i>
                                <strong>10,250</strong><span class="ms-1">登録者</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="fas fa-building fa-lg me-2"></i>
                                <strong>18,400</strong><span class="ms-1">求人企業</span>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const bigClassSelect = document.getElementById("bigClassSelect");
            const jobCategorySelect = document.getElementById("jobCategorySelect");

            // Load from session
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
                            if (item.middle_class_code === savedMid) opt.selected = true;
                            jobCategorySelect.appendChild(opt);
                        });
                    });
            }

            // On change
            bigClassSelect.addEventListener("change", function() {
                const selectedCode = this.value;
                sessionStorage.setItem("big_class_code", selectedCode);
                jobCategorySelect.innerHTML = '<option value="">職種タイプ</option>';

                if (selectedCode) {
                    fetch(`/api/job-categories/${selectedCode}`)
                        .then(response => response.json())
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

            // Redirect with parameters (Livewire uses session)
            fetch(`/initial-search`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    big_class_code: big,
                    job_category: mid
                })
            }).then(() => window.location.reload());
        }
    </script>


    <!-- FILTERING SECTION -->
    @if ($hasSearched)
        <div class="container my-5">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <select class="form-select" wire:model="salary">
                        <option value="">年収（給料）</option>
                        @foreach (range(300, 1000, 50) as $yen)
                            <option value="{{ $yen }}">{{ $yen }}万円以上</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model="hourly_wage">
                        <option value="">時給</option>
                        @foreach (range(800, 3000, 100) as $yen)
                            <option value="{{ $yen }}">時給{{ $yen }}円以上</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model="location">
                        <option value="">希望勤務地</option>
                        @foreach ($prefectures as $pref)
                            <option value="{{ $pref }}">{{ $pref }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                @foreach ($checkboxOptions as $key => $label)
                    <label class="btn btn-outline-secondary btn-sm me-2 mb-2">
                        <input type="checkbox" wire:model="supplementFlags" value="{{ $key }}">
                        {{ $label }}
                    </label>
                @endforeach
            </div>

            <button class="btn btn-success" wire:click="searchJobs">
                <i class="fas fa-search"></i> さらに絞り込む
            </button>
        </div>
    @endif

    <!-- JOB RESULTS -->
    @if ($hasSearched)
        <div class="container">
            <h5 class="mb-4 text-success">マッチングされた求人票一覧: {{ $jobs->total() }} 件</h5>

            @if ($jobs->count())
                <div class="row g-4">
                    @foreach ($jobs as $job)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="fw-bold text-primary">{{ $job->pr_title1 ?? '' }}</h5>

                                    @if (!empty($job->selectedFlagsArray))
                                        <div class="d-flex flex-wrap gap-1 mb-3">
                                            @foreach ($job->selectedFlagsArray as $flagLabel)
                                                <span
                                                    class="badge bg-light border text-dark">{{ $flagLabel }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <ul class="list-group list-group-flush mb-3">
                                        <li class="list-group-item">職種:
                                            {{ $middleClassMap[$job->job_type_code] ?? '' }}</li>
                                        <li class="list-group-item">雇用形態:
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
                                            @endswitch
                                        </li>
                                        <li class="list-group-item">年収:
                                            {{ number_format($job->yearly_income_min) }}円〜{{ number_format($job->yearly_income_max) }}円
                                        </li>
                                        <li class="list-group-item">月給:
                                            {{ number_format($job->monthly_income_min) }}円〜{{ number_format($job->monthly_income_max) }}円
                                        </li>
                                        <li class="list-group-item">時給:
                                            {{ number_format($job->hourly_income_min) }}円〜{{ number_format($job->hourly_income_max) }}円
                                        </li>
                                        <li class="list-group-item">勤務地: {{ $job->prefecture_name ?? '' }}</li>
                                    </ul>

                                    <p class="small">{{ $job->business_detail ?? '' }}</p>

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('jobs.detail', ['id' => $job->id]) }}"
                                            class="btn btn-primary btn-sm">求人の詳細を見る</a>
                                        <button class="btn btn-outline-secondary btn-sm">お気に入りリストに追加</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $jobs->links('pagination.bootstrap-5-ja') }}
                </div>
            @else
                <p class="text-muted">該当する招待が見つかりませんでした。</p>
                <p class="text-success">エージェントと相談してください！<a href="{{ route('contact.form') }}"
                        class="text-primary ms-2">お問い合わせ</a></p>
            @endif
        </div>
    @endif
</div>
@if (session()->has('search.keyword') ||
        session()->has('search.big_class_code') ||
        session()->has('search.salary') ||
        session()->has('search.location') ||
        session()->has('search.certificate'))
@endif
