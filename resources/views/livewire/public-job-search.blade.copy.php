<div> <!-- Yagona root element -->
    <!-- HERO SECTION + SEARCH UI -->
    <div class="container-fluid bg-dark text-white py-5 px-0">
        <div class="container">
            <div class="row align-items-center justify-content-center text-center text-lg-start">
                <!-- Image Side -->
                <div class="col-lg-6 mb-4 mb-lg-0 text-center">
                    <img src="https://mch.shigotonavi.co.jp/img/hero-img.png" class="img-fluid" alt="Hero Image" style="max-height: 300px;">
                </div>

                <!-- Text and Search -->
                <div class="col-lg-6">
                    <h1 class="fw-bold display-5">しごとナビにようこそ!</h1>
                    <p class="lead">Connecting Talent with Opportunity: Your Gateway to Career Success</p>

                    <!-- Search Box -->
                    <div class="bg-white text-dark rounded shadow p-4 mt-4">
                        <h4 class="fw-bold mb-3">お試し検索</h4>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select class="form-select" id="big_class_code" wire:model="big_class_code">
                                    <option value="">職種選択</option>
                                    @foreach ($bigClasses as $class)
                                        <option value="{{ $class->big_class_code }}">{{ $class->big_class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="job_category" wire:model="job_category">
                                    <option value="">職種タイプ選択</option>
                                    @foreach ($jobCategories as $category)
                                        <option value="{{ $category->middle_class_code }}">
                                            {{ $category->middle_clas_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="d-grid mt-3">
                            <button type="button" class="btn btn-success" wire:click="searchJobs">
                                <i class="fas fa-search me-1"></i> 検索
                            </button>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="row text-white text-center mt-5 g-3">
                        <div class="col-4">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="fas fa-briefcase fa-lg me-2"></i>
                                <strong>25,850</strong><span class="ms-1">Jobs</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="fas fa-users fa-lg me-2"></i>
                                <strong>10,250</strong><span class="ms-1">Candidates</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="fas fa-building fa-lg me-2"></i>
                                <strong>18,400</strong><span class="ms-1">Companies</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

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
                        <input type="checkbox" wire:model="supplementFlags" value="{{ $key }}"> {{ $label }}
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
                                                <span class="badge bg-light border text-dark">{{ $flagLabel }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <ul class="list-group list-group-flush mb-3">
                                        <li class="list-group-item">職種: {{ $middleClassMap[$job->job_type_code] ?? '' }}</li>
                                        <li class="list-group-item">雇用形態:
                                            @switch($job->order_type)
                                                @case(1) 派遣 @break
                                                @case(2) 正社員 @break
                                                @case(3) 契約社員 @break
                                            @endswitch
                                        </li>
                                        <li class="list-group-item">年収: {{ number_format($job->yearly_income_min) }}円〜{{ number_format($job->yearly_income_max) }}円</li>
                                        <li class="list-group-item">時給: {{ number_format($job->hourly_income_min) }}円〜{{ number_format($job->hourly_income_max) }}円</li>
                                        <li class="list-group-item">勤務地: {{ $job->prefecture_name ?? '' }}</li>
                                    </ul>

                                    <p class="small">{{ $job->business_detail ?? 'システム設計職・ＳＥ・官庁系システム改修...' }}</p>

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('jobs.detail', ['id' => $job->id]) }}" class="btn btn-primary btn-sm">求人の詳細を見る</a>
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
                <p class="text-success">エージェントと相談してください！<a href="{{ route('contact.form') }}" class="text-primary ms-2">お問い合わせ</a></p>
            @endif
        </div>
    @endif
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bigClass = document.getElementById('big_class_code');
            const jobCategory = document.getElementById('job_category');

            bigClass.addEventListener('change', function() {
                const val = this.value;

                // ⛔️ Tozalash (eskilarni olib tashlash)
                jobCategory.innerHTML = '<option value="">職種タイプ</option>';

                if (val) {
                    fetch(`/api/job-categories/${val}`)
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(item => {
                                const opt = document.createElement('option');
                                opt.value = item.middle_class_code;
                                opt.text = item.middle_clas_name;
                                jobCategory.appendChild(opt);
                            });
                        });
                }
            });
        });
    </script>
@endpush