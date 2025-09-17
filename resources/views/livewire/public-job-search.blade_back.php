<div class="container py-4">
    <!-- STEP 1: Top Search Bar -->
    <div class="input-group input-group-lg mb-4">
        <input type="text" class="form-control border-dark" placeholder="キーワード" wire:model.debounce.500ms="keyword">

        <select class="form-select border-dark" id="big_class_code" wire:model="big_class_code">
            <option value="">職種</option>
            @foreach ($bigClasses as $class)
                <option value="{{ $class->big_class_code }}">{{ $class->big_class_name }}</option>
            @endforeach
        </select>

        <select class="form-select border-dark" id="job_category" wire:model="job_category">
            <option value="">職種タイプ</option>
            @foreach ($jobCategories as $category)
                <option value="{{ $category->middle_class_code }}"
                    {{ $job_category == $category->middle_class_code ? 'selected' : '' }}>
                    {{ $category->middle_clas_name }}
                </option>
            @endforeach
        </select>

        <select class="form-select border-dark" wire:model="salary">
            <option value="">年収</option>
            @foreach (range(200, 1000, 50) as $yen)
                <option value="{{ $yen }}">{{ $yen }}万円以上</option>
            @endforeach
        </select>
        <select class="form-select border-dark" wire:model="hourly_wage">
            <option value="">時給</option>
            @foreach (range(800, 3000, 100) as $yen)
                <option value="{{ $yen }}">時給{{ $yen }}円以上</option>
            @endforeach
        </select>

        <button class="btn btn-success" wire:click="searchJobs">
            <i class="fas fa-search"></i>
        </button>
    </div>

    <!-- STEP 2: Filtering options (only after jobs found) -->
    @if (!empty($jobs))
        <div class="mb-4">
            <div class="row g-2 mb-2">
                <div class="col-md-4">
                    <select class="form-select" wire:model="salary">
                        <option value="">年収（給料）</option>
                        @foreach (range(300, 1000, 50) as $yen)
                            <option value="{{ $yen }}">{{ $yen }}万円以上</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select" wire:model="location">
                        <option value="">年期住所</option>
                        @foreach ($prefectures as $pref)
                            <option value="{{ $pref }}">{{ $pref }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" wire:model.debounce.500ms="certificate"
                        placeholder="証明書で精緻">
                </div>
            </div>

            <div class="form-group mt-2">
                @foreach ($checkboxOptions as $key => $label)
                    <label class="btn btn-outline-secondary btn-sm me-1 mb-2">
                        <input type="checkbox" wire:model="supplementFlags" value="{{ $key }}">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <!-- STEP 3: Results -->
    @if (!empty($jobs))
        <h5 class="mb-3 text-main-theme">マッチングされた招待票一覧: {{ count($jobs) }} 件</h5>
        @if (count($jobs))
            <div class="row g-4">
                @foreach ($jobs as $job)
                    <div class="col-12">
                        <a href="{{ route('jobs.detail', ['id' => $job->id]) }}"
                            class="text-decoration-none text-dark">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body">
                                    <!-- Kompaniya nomi -->
                                    {{--  <h6 class="text-muted">{{ $job->company_name_k }}</h6>  --}}

                                    <!-- Ish sarlavhasi -->
                                    <h5 class="fw-bold text-primary">
                                        {{ $job->pr_title1 ?? '' }}
                                    </h5>

                                    <!-- Badgelar -->
                                    @if (!empty($job->selectedFlagsArray))
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            @foreach ($job->selectedFlagsArray as $flagLabel)
                                                <span
                                                    class="badge rounded-pill text-bg-light border border-secondary px-3 py-2 fw-normal">
                                                    {{ $flagLabel }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Info table -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <table class="table table-bordered table-sm">
                                                <tbody>
                                                    <tr>
                                                        <th class="bg-light w-25">職種</th>
                                                        <td>{{ $middleClassMap[$job->job_type_code] ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">雇用形態</th>
                                                        <td>
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
                                                            @endswitch
                                                        </td>
                                                        {{--  <td>{{ $job->employment_label }}</td>  --}}
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">年収</th>
                                                        <td>
                                                            {{ number_format($job->yearly_income_min) }}円 〜
                                                            {{ number_format($job->yearly_income_max) }}円
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">時給</th>
                                                        <td>
                                                            {{ number_format($job->monthly_income_min) }}円 〜
                                                            {{ number_format($job->monthly_income_max) }}円
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">時給</th>
                                                        <td>
                                                            {{ number_format($job->hourly_income_min) }}円 〜
                                                            {{ number_format($job->hourly_income_max) }}円
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">勤務地</th>
                                                        <td>{{ $job->prefecture_name ?? '' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Rasm qismi -->
                                        <div class="col-md-6">
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <img src="{{ asset('img/hero-img.png') }}"
                                                        class="img-fluid rounded w-100"
                                                        style="height: 150px; object-fit: cover;" alt="main">
                                                </div>
                                                <div class="col-4">
                                                    <img src="{{ asset('img/bg2.png') }}" class="img-fluid rounded"
                                                        style="height: 70px; object-fit: cover;" alt="sub1">
                                                </div>
                                                <div class="col-4">
                                                    <img src="{{ asset('img/bg1.png') }}" class="img-fluid rounded"
                                                        style="height: 70px; object-fit: cover;" alt="sub2">
                                                </div>
                                                <div class="col-4">
                                                    <img src="{{ asset('img/bg2.png') }}" class="img-fluid rounded"
                                                        style="height: 70px; object-fit: cover;" alt="sub3">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ish mazmuni -->
                                    <h6 class="fw-bold">【担当業務の説明】</h6>
                                    <p class="small">
                                        {{ $job->business_detail ?? 'システム設計職・ＳＥ・官庁系システム改修...' }}
                                    </p>
                                    <!-- Tugmalar -->
                                    <div class="d-flex flex-wrap mt-3">
                                        <a href="{{ route('jobs.detail', ['id' => $job->id]) }}"
                                            class="btn btn-primary me-2 mb-2">
                                            求人の詳細を見る
                                        </a>
                                        <button class="btn btn-outline-secondary mb-2">
                                            お気に入りリストに追加
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted">該当する招待が見つかりませんでした。</p>
        @endif
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
