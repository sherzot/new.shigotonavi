@extends('layouts.layout')

@section('title', '求人票を更新する')

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
    <div class="container-fluid p-3 bg-white">
        <h2 class="h4 text-dark fw-bold">[{{ $job->order_code }}]求人票を更新する</h2><br>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->keys() as $key)
                        <li>{{ $key }}: {{ $errors->first($key) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('jobs.update', ['orderCode' => $job->order_code]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- 勤務形態 -->
                <div class="mb-3 col-12 col-sm-6 col-md-4">
                    <label for="order_type" class="form-label fw-bold">勤務形態</label>
                    <select id="order_type" name="order_type" class="form-select form-select-lg mb-3">
                        <option disabled>選択してください</option>
                        @foreach ($orderTypes as $orderType)
                            <option value="{{ $orderType->code }}"
                                {{ $job->order_type == $orderType->code ? 'selected' : '' }}>
                                {{ $orderType->detail }}
                            </option>
                        @endforeach
                    </select>

                    @error('order_type')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 受注区分 -->
                <div class="mb-3 col-12 col-sm-6 col-md-4">
                    <label for="order_progress_type" class="form-label fw-bold">
                        受注区分
                    </label>
                    <select id="order_progress_type" name="order_progress_type" class="form-select form-select-lg">
                        <option value="0"
                            {{ old('order_progress_type', $job->order_progress_type ?? '0') == '0' ? 'selected' : '' }}>
                            選択してください
                        </option>
                        <option value="1"
                            {{ old('order_progress_type', $job->order_progress_type ?? '0') == '1' ? 'selected' : '' }}>
                            受注中
                        </option>
                        <option value="2"
                            {{ old('order_progress_type', $job->order_progress_type ?? '0') == '2' ? 'selected' : '' }}>
                            受注完
                        </option>
                    </select>
                    @error('order_progress_type')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- しごとナビ掲載有無 -->
                <div class="mb-3 col-12 col-sm-6 col-md-4">
                    <label for="public_flag" class="form-label fw-bold">
                        しごとナビ掲載有無
                    </label>
                    <select id="public_flag" name="public_flag" class="form-select form-select-lg">
                        <option value="0" {{ old('public_flag', $job->public_flag ?? '0') == '0' ? 'selected' : '' }}>
                            非掲載
                        </option>
                        <option value="1" {{ old('public_flag', $job->public_flag ?? '0') == '1' ? 'selected' : '' }}>
                            掲載
                        </option>
                    </select>
                    @error('public_flag')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

            </div>


            <!-- 職種名 -->
            <div class="mb-4">
                <p class="form-label">職種名</p>
                <input type="text" class="form-control" id="job_type_detail" name="job_type_detail"
                    value="{{ old('job_type_detail', $job->job_type_detail) }}">
                @error('job_type_detail')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- 募集職種 -->
            <p class="form-label">募集職種 <small style="color: rgba(255, 0, 0, 0.674);">
                上記に記入した職種に該当するカテゴリを以下より指定してください。（１つ以上必須）
            </small></p>
            <div class="mt-3">
                @foreach ($selectedBigClassCodes as $index => $bigClassCode)
                    <div class="row g-3 mt-3">
                        <input type="hidden" name="ids[]" value="{{ $ids[$index] }}">
                        <div class="mb-4 col-12 col-sm-12 col-md-6">
                            <label for="big_class_code_{{ $index }}" class="form-label">募集職種 {{ $index + 1 }}：</label>
                            <select name="big_class_code[]" id="big_class_code_{{ $index }}" class="form-control big-class-select"
                                onchange="updateMiddleClassOptions(this, {{ $index }})">
                                <option value="">選択してください</option>
                                @foreach ($bigClasses as $bigClass)
                                    <option value="{{ $bigClass->big_class_code }}"
                                        {{ $bigClassCode == $bigClass->big_class_code ? 'selected' : '' }}>
                                        {{ $bigClass->big_class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4 col-12 col-sm-12 col-md-6">
                            <label for="middle_class_code_{{ $index }}" class="form-label">職種タイプ {{ $index + 1 }}：</label>
                            <select name="middle_class_code[]" id="middle_class_code_{{ $index }}" class="form-control middle-class-select">
                                <option value="">選択してください</option>
                                @foreach ($jobCategories as $jobCategory)
                                    @if ($jobCategory->big_class_code == $bigClassCode)
                                        <option value="{{ $jobCategory->code }}"
                                            {{ $selectedMiddleClassCodes[$index] == $jobCategory->code ? 'selected' : '' }}>
                                            {{ $jobCategory->detail }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- PRセクション -->
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label for="pr_title1" class="form-label">PRタイトル 1</label>
                    <input type="text" class="form-control" id="pr_title1" name="pr_title1"
                        value="{{ old('pr_title1', $job->pr_title1) }}">
                </div>
                <div class="col-md-6">
                    <label for="pr_contents1" class="form-label">PR内容 1</label>
                    <textarea class="form-control" id="pr_contents1" name="pr_contents1" rows="4">{{ old('pr_contents1', $job->pr_contents1) }}</textarea>
                </div>
            </div>
            
            <!-- More PR Sections -->
            @for ($i = 2; $i <= 3; $i++)
                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label for="pr_title{{ $i }}" class="form-label">PRタイトル {{ $i }}</label>
                        <input type="text" class="form-control" id="pr_title{{ $i }}"
                            name="pr_title{{ $i }}"
                            value="{{ old("pr_title{$i}", $job->{"pr_title{$i}"} ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="pr_contents{{ $i }}" class="form-label">PR内容 {{ $i }}</label>
                        <textarea class="form-control" id="pr_contents{{ $i }}" name="pr_contents{{ $i }}" rows="4">{{ old("pr_contents{$i}", $job->{"pr_contents{$i}"} ?? '') }}</textarea>
                    </div>
                </div>
            @endfor
            
            {{-- 担当業務の説明 --}}
            <div class="row g-3 mt-3">
                <div class="col-12">
                    <p class="form-label">担当業務の説明</p>
                    <textarea class="form-control" id="business_detail" name="business_detail" rows="4">{{ old('business_detail', $job->business_detail ?? '') }}</textarea>
                </div>
                @error('business_detail')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <br>

            {{-- 求める経験 --}}
            <div class="row g-3 my-3">
                <div class="col-12">
                    <p class="form-label">求める経験</p>
                    <textarea class="form-control" id="note" name="BestMatch" rows="4">{{ old('BestMatch', $jobNoteData->note ?? '') }}</textarea>
                </div>
                @error('BestMatch')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- しごとナビ掲載期限 --}}
            <p>しごとナビ掲載期限:（例）20060401（半角入力）</p>
            <div class="row g-3 my-3">
                <div class="col-12">
                    <input type="text" class="form-control" id="public_limit_day" name="public_limit_day"
                        value="{{ old('public_limit_day', optional($job)->public_limit_day ? \Carbon\Carbon::parse($job->public_limit_day)->format('Ymd') : '') }}">
                </div>
                @error('public_limit_day')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- 会社の特徴 --}}
            <p>会社の特徴: （例）「音楽楽器の大手企業」</p>
            <div class="row g-3 my-3">
                <div class="col-12">
                    <input type="text" class="form-control" id="company_speciality" name="company_speciality"
                        value="{{ old('company_speciality', $prData->company_speciality ?? '') }}">
                </div>
                @error('company_speciality')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- キャッチコピー --}}
            <p>キャッチコピー</p>
            <div class="row g-3 my-3">
                <div class="col-12">
                    <textarea class="form-control" id="catch_copy" name="catch_copy" rows="1">{{ old('catch_copy', $prData->catch_copy ?? '') }}</textarea>
                </div>
                @error('catch_copy')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-4">
                <p>お仕事の割合</p>
                <div class="row">
                    <!-- 業務1 -->
                    <div class="col-12 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-end">
                                <label for="biz_name1" class="form-label">業務1</label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" id="biz_name1" name="biz_name1" class="form-control"
                                    value="{{ old('biz_name1', $prData->biz_name1 ?? '') }}">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">全体の</span>
                            </div>
                            @error('biz_name1')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <div class="col-md-2">
                                <input type="text" id="biz_percentage1" name="biz_percentage1"
                                    class="form-control text-end" maxlength="3"
                                    value="{{ old('biz_percentage1', $prData->biz_percentage1 ?? '') }}">
                            </div>
                            <div class="col-md-1">
                                <span class="fw-bold">%</span>
                            </div>
                            @error('biz_percentage1')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- 業務2 -->
                    <div class="col-12 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-end">
                                <label for="biz_name2" class="form-label">業務2</label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" id="biz_name2" name="biz_name2" class="form-control"
                                    value="{{ old('biz_name2', $prData->biz_name2 ?? '') }}">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">全体の</span>
                            </div>
                            @error('biz_name2')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <div class="col-md-2">
                                <input type="text" id="biz_percentage2" name="biz_percentage2"
                                    class="form-control text-end" maxlength="3"
                                    value="{{ old('biz_percentage2', $prData->biz_percentage2 ?? '') }}">
                            </div>
                            <div class="col-md-1">
                                <span class="fw-bold">%</span>
                            </div>
                            @error('biz_percentage2')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- 業務3 -->
                    <div class="col-12 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-end">
                                <label for="biz_name3" class="form-label">業務3</label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" id="biz_name3" name="biz_name3" class="form-control"
                                    value="{{ old('biz_name3', $prData->biz_name3 ?? '') }}">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">全体の</span>
                            </div>
                            @error('biz_name3')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <div class="col-md-2">
                                <input type="text" id="biz_percentage3" name="biz_percentage3"
                                    class="form-control text-end" maxlength="3"
                                    value="{{ old('biz_percentage3', $prData->biz_percentage3 ?? '') }}">
                            </div>
                            <div class="col-md-1">
                                <span class="fw-bold">%</span>
                            </div>
                            @error('biz_percentage3')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- 業務4 -->
                    <div class="col-12 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-end">
                                <label for="biz_name4" class="form-label">業務4</label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" id="biz_name4" name="biz_name4" class="form-control"
                                    value="{{ old('biz_name4', $prData->biz_name4 ?? '') }}">
                            </div>
                            <div class="col-md-1 text-center">
                                <span class="fw-bold">全体の</span>
                            </div>
                            @error('biz_name4')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <div class="col-md-2">
                                <input type="text" id="biz_percentage4" name="biz_percentage4"
                                    class="form-control text-end" maxlength="3"
                                    value="{{ old('biz_percentage4', $prData->biz_percentage4 ?? '') }}">
                            </div>
                            <div class="col-md-1">
                                <span class="fw-bold">%</span>
                            </div>
                            @error('biz_percentage4')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- 給与はjob_orderにある --}}
            <div class="mb-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label id="salary_label" class="form-label fw-bold" style="display: block;">
                            年収
                        </label>
                    </div>

                    <!-- 年収 -->
                    <div class="col-12 col-md-6">
                        <div class="row g-2 align-items-center">
                            <div class="col-5">
                                <input type="text" name="yearly_income_min" id="desired_salary_annual_min"
                                    class="form-control mt-2" placeholder="最低額"
                                    value="{{ old('yearly_income_min', $job->yearly_income_min ?? '') }}"
                                    {{ in_array($job->order_type, [1, 3]) ? 'readonly' : '' }}>
                            </div>
                            @error('yearly_income_min')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <div class="col-2 text-center">〜</div>
                            <div class="col-5">
                                <input type="text" name="yearly_income_max" id="desired_salary_annual_max"
                                    class="form-control mt-2" placeholder="最高額"
                                    value="{{ old('yearly_income_max', $job->yearly_income_max ?? '') }}"
                                    {{ in_array($job->order_type, [1, 3]) ? 'readonly' : '' }}>
                            </div>
                            @error('yearly_income_max')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <label id="salary_label2" class="form-label fw-bold m-0 pt-3" style="display: block;">
                            月給
                        </label>
                        <div class="row g-2 align-items-center">
                            <div class="col-5">
                                <input type="text" name="monthly_income_min" id="desired_salary_monthly_min"
                                    class="form-control mt-2" placeholder="最低月給"
                                    value="{{ old('monthly_income_min', $job->monthly_income_min ?? '') }}"
                                    {{ in_array($job->order_type, [1, 3]) ? 'readonly' : '' }}>
                            </div>
                            @error('monthly_income_min')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <div class="col-2 text-center">〜</div>
                            <div class="col-5">
                                <input type="text" name="monthly_income_max" id="desired_salary_monthly_max"
                                    class="form-control mt-2" placeholder="最高月給"
                                    value="{{ old('monthly_income_max', $job->monthly_income_max ?? '') }}"
                                    {{ in_array($job->order_type, [1, 3]) ? 'readonly' : '' }}>
                            </div>
                            @error('monthly_income_max')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>


            <div class="mb-4">
                <p class="form-label">給与備考</p>
                <input type="text" class="form-control" id="income_remark" name="income_remark"
                    value="{{ old('income_remark', $job->income_remark ?? '') }}">
                @error('income_remark')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            {{--  job_scheduled_to_intraduntion  --}}
            <div class="mb-4" id="employment_start_day">
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-12 col-sm-12 col-md-6">
                        <p class="form-label fw-bold">雇用開始予定日</span></p>
                        <input type="text" name="employment_start_day" id="employment_start_day" class="form-control"
                            value="{{ old('employment_start_day', isset($job->employment_start_day) ? \Carbon\Carbon::parse($job->employment_start_day)->format('Ymd') : '') }}">

                        <small class="form-text text-muted">(例: 20250420)</small>
                        @error('employment_start_day')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            {{-- 雇用形態 --}}
            <div class="p-2 mb-4 bg-white" id="employment_start_date2" style="display: none;">
                <!-- 勤務期間 -->
                <label for="workStartDay" class="form-label">勤務期間: <span style="color: rgba(255, 0, 0, 0.674);">(例:
                        2010年4月20日 ⇒ 20100420)</span></label>
                <div class="row align-items-center">
                    <!-- 開始日 -->
                    <div class="col-md-4">
                        <input type="text" class="form-control text-center" name="work_start_day" id="workStartDay"
                            placeholder="YYYYMMDD"
                            value="{{ old('work_start_day', isset($scheduledData->work_start_day) ? \Carbon\Carbon::parse($scheduledData->work_start_day)->format('Ymd') : '') }}">
                    </div>
                    @error('work_start_day')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <div class="col-md-1 text-center">〜</div>
                    <!-- 終了日 -->
                    <div class="col-md-4">
                        <input type="text" class="form-control text-center" name="work_end_day" id="workEndDay"
                            placeholder="YYYYMMDD"
                            value="{{ old('work_end_day', isset($scheduledData->work_end_day) ? \Carbon\Carbon::parse($scheduledData->work_end_day)->format('Ymd') : '') }}">
                    </div>
                    @error('work_end_day')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <div class="col-md-3 text-muted text-center"></div>
                </div>

                <!-- 更新有無 -->
                <label class="form-label mt-3">更新有無</label>
                <div class="row align-items-center">
                    <div class="col-auto">
                        <input type="radio" name="work_update_flag" value="1" id="updateYes"
                            {{ old('work_update_flag', $scheduledData->work_update_flag ?? '') == '1' ? 'checked' : '' }}>
                        <label for="updateYes">更新有り</label>
                    </div>
                    @error('work_update_flag')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <div class="col-auto">
                        <label for="workPeriod" class="form-label">（更新期間:</label>
                        <input type="text" name="work_period" id="workPeriod" class="form-control d-inline-block"
                            style="width: 50px; text-align: center;"
                            value="{{ old('work_period', $scheduledData->work_period ?? '') }}">
                        <span>ヶ月）</span>
                    </div>
                    @error('work_period')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <div class="col-auto">
                        <input type="radio" name="work_update_flag" value="0" id="updateNo"
                            {{ old('work_update_flag', $scheduledData->work_update_flag ?? '') == '0' ? 'checked' : '' }}>
                        <label for="updateNo">更新無し</label>
                    </div>
                    @error('work_update_flag')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            {{-- 勤務時間 work_start_time & work_end_time --}}
            <div class="row g-3 mt-3 align-items-center">
                <!-- 開始時間 -->
                <div class="col-12 col-md-5">
                    <label for="work_start_time" class="form-label my-1">開始時間</label>
                    <input type="text" class="form-control" id="work_start_time" name="work_start_time"
                        value="{{ $jobWorkingCondition->work_start_time ?? '' }}">
                </div>
                <!-- ~ -->
                <div class="col-12 col-md-1 text-center">
                    <span class="fw-bold">~</span>
                </div>
                <!-- 終了時間 -->
                <div class="col-12 col-md-5">
                    <label for="work_end_time" class="form-label my-1">終了時間</label>
                    <input type="text" class="form-control" id="work_end_time" name="Work_end_time"
                        value="{{ $jobWorkingCondition->Work_end_time ?? '' }}">
                </div>
            </div>

            {{-- 休憩時間 rest_start_time & rest_end_time --}}
            <div class="row g-3 mt-3 align-items-center">
                <!-- 休憩開始時間 -->
                <div class="col-12 col-md-5">
                    <label for="rest_start_time" class="form-label my-1">休憩開始時間</label>
                    <input type="text" class="form-control" id="rest_start_time" name="rest_start_time"
                        value="{{ $jobWorkingCondition->rest_start_time ?? '' }}">
                </div>
                <!-- ~ -->
                <div class="col-12 col-md-1 text-center">
                    <span class="fw-bold">~</span>
                </div>
                <!-- 休憩終了時間 -->
                <div class="col-12 col-md-5">
                    <label for="rest_end_time" class="form-label my-1">休憩終了時間</label>
                    <input type="text" class="form-control" id="rest_end_time" name="rest_end_time"
                        value="{{ $jobWorkingCondition->rest_end_time ?? '' }}">
                </div>
            </div>

            {{--  over_work_flag  --}}
            <div class="mb-4">
                <p class="form-label mt-3 mb-1">時間外</p>
                <input type="radio" id="over_work_flag" name="over_work_flag" value="1"
                    {{ $job->over_work_flag == 1 ? 'checked' : '' }}>
                <label for="over_work_flag" class="pr-2">有</label>
                <input type="radio" id="over_work_flag" name="over_work_flag" value="0"
                    {{ $job->over_work_flag == 0 ? 'checked' : '' }}>
                <label for="over_work_flag">無</label>
            </div>

            {{--  work_time_remark  --}}
            <div class="row g-3 my-3">
                <div class="col-12">
                    <textarea class="form-control" id="work_time_remark" name="work_time_remark" rows="2">{{ $job->work_time_remark ?? '' }}</textarea>
                </div>
            </div>

            {{--  weekly_holiday_type  --}}
            <div class="mb-4">
                <p class="form-label mb-1">休日</p>
                @foreach (['001' => '完全週休2日', '002' => '変則週休2日', '003' => '週休2日', '004' => '週休1日', '999' => 'その他'] as $value => $label)
                    <input type="radio" id="weekly_holiday_type_{{ $value }}" name="weekly_holiday_type"
                        value="{{ $value }}" {{ $job->weekly_holiday_type == $value ? 'checked' : '' }}>
                    <label for="weekly_holiday_type_{{ $value }}" class="pr-2">{{ $label }}</label>
                @endforeach
            </div>

            {{--  holiday_remark  --}}
            <div class="row g-3 my-3">
                <div class="col-12">
                    <textarea class="form-control" id="holiday_remark" name="holiday_remark" rows="2">{{ $job->holiday_remark ?? '' }}</textarea>
                </div>
            </div>


            {{--  希望勤務地はjob_working_placeにあります。  --}}
            <div class="mb-4 mt-3">
                <p class="form-label">勤務地(都道府県)</p>
                <!-- 希望勤務地 -->
                <div class="mb-4">
                    <label class="form-label">希望勤務地：</label>
                    <select name="prefecture_code[]" class="form-control" multiple>
                        @foreach ($prefectures as $prefecture)
                            <option value="{{ $prefecture->code }}"
                                {{ in_array($prefecture->code, $selectedPrefectures) ? 'selected' : '' }}>
                                {{ $prefecture->detail }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('prefecture_code[]')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                {{--  @foreach ($workingPlaces as $index => $workingPlace)
                    <p class="form-label my-0 pt-3">市区町村住所 {{ $index + 1 }}</p>
                    <div class="form-row">
                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="city_{{ $index }}">区/市</label>
                            <input type="text" class="form-control" id="city_{{ $index }}" name="city[]"
                                value="{{ old('city.' . $index, $workingPlace->city) }}">
                        </div>
                        @error('city.' . $index)
                            <div class="text-danger">{{ $message }}</div>
                        @enderror

                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="town_{{ $index }}">町</label>
                            <input type="text" class="form-control" id="town_{{ $index }}" name="town[]"
                                value="{{ old('town.' . $index, $workingPlace->town) }}">
                        </div>
                        @error('town.' . $index)
                            <div class="text-danger">{{ $message }}</div>
                        @enderror

                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="address_{{ $index }}">村住所</label>
                            <input type="text" class="form-control" id="address_{{ $index }}"
                                name="address[]" value="{{ old('address.' . $index, $workingPlace->address) }}">
                        </div>
                        @error('address.' . $index)
                            <div class="text-danger">{{ $message }}</div>
                        @enderror

                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="section_{{ $index }}">セクション</label>
                            <input type="text" class="form-control" id="section_{{ $index }}"
                                name="section[]" value="{{ old('section.' . $index, $workingPlace->section) }}">
                        </div>
                        @error('section.' . $index)
                            <div class="text-danger">{{ $message }}</div>
                        @enderror

                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="telephone_number_{{ $index }}">電話番号</label>
                            <input type="text" class="form-control" id="telephone_number_{{ $index }}"
                                name="telephone_number[]"
                                value="{{ old('telephone_number.' . $index, $workingPlace->telephone_number) }}">
                        </div>
                        @error('telephone_number.' . $index)
                            <div class="text-danger">{{ $message }}</div>
                        @enderror

                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="charge_person_post_{{ $index }}">担当者</label>
                            <input type="text" class="form-control" id="charge_person_post_{{ $index }}"
                                name="charge_person_post[]"
                                value="{{ old('charge_person_post.' . $index, $workingPlace->charge_person_post) }}">
                        </div>
                        @error('charge_person_post.' . $index)
                            <div class="text-danger">{{ $message }}</div>
                        @enderror

                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="charge_person_name_{{ $index }}">担当者名</label>
                            <input type="text" class="form-control" id="charge_person_name_{{ $index }}"
                                name="charge_person_name[]"
                                value="{{ old('charge_person_name.' . $index, $workingPlace->charge_person_name) }}">
                        </div>
                        @error('charge_person_name.' . $index)
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach  --}}
                @if (isset($workingPlaces[0]))
                    @php $workingPlace = $workingPlaces[0]; @endphp
                    <p class="form-label my-0 pt-3">市区町村住所</p>
                    <div class="form-row">
                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="city">区/市</label>
                            <input type="text" class="form-control" id="city" name="city[]"
                                   value="{{ old('city.0', $workingPlace->city) }}">
                        </div>
                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="town">町</label>
                            <input type="text" class="form-control" id="town" name="town[]"
                                   value="{{ old('town.0', $workingPlace->town) }}">
                        </div>
                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="address">村住所</label>
                            <input type="text" class="form-control" id="address" name="address[]"
                                   value="{{ old('address.0', $workingPlace->address) }}">
                        </div>
                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="section">セクション</label>
                            <input type="text" class="form-control" id="section" name="section[]"
                                   value="{{ old('section.0', $workingPlace->section) }}">
                        </div>
                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="telephone_number">電話番号</label>
                            <input type="text" class="form-control" id="telephone_number" name="telephone_number[]"
                                   value="{{ old('telephone_number.0', $workingPlace->telephone_number) }}">
                        </div>
                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="charge_person_post">担当者</label>
                            <input type="text" class="form-control" id="charge_person_post" name="charge_person_post[]"
                                   value="{{ old('charge_person_post.0', $workingPlace->charge_person_post) }}">
                        </div>
                        <div class="form-group col-sm-6 col-md-4 col-lg-3">
                            <label for="charge_person_name">担当者名</label>
                            <input type="text" class="form-control" id="charge_person_name" name="charge_person_name[]"
                                   value="{{ old('charge_person_name.0', $workingPlace->charge_person_name) }}">
                        </div>
                    </div>
                @endif

            </div>

            {{-- 年齢制限  制限理由 --}}
            <div class="mb-4">
                <p class="form-label m-0 fw-bold">年齢制限:</p>
                <div class="row g-2 align-items-center">
                    <!-- Age Inputs -->
                    <div class="col-5">
                        <input type="text" name="age_min" id="age_min" class="form-control mt-2"
                            placeholder="最低年齢" value="{{ $job->age_min ?? '' }}">
                    </div>
                    <div class="col-2 text-center">〜</div>
                    <div class="col-5">
                        <input type="text" name="age_max" id="age_max" class="form-control mt-2"
                            placeholder="最高年齢" value="{{ $job->age_max ?? '' }}">
                    </div>
                </div>

                <p class="form-label mt-3 fw-bold">制限理由</p>
                @foreach (['K' => '定年年齢を上限', 'L' => '業務・産業による表現の実現', 'M' => '長期継続によるキャリア形成', 'N' => '技能等の継承', 'O' => '芸術・芸能における表現', 'P' => '高年齢者又は国の雇用促進施策'] as $value => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="age_reason_flag"
                            id="age_reason_{{ $value }}" value="{{ $value }}"
                            {{ $job->age_reason_flag == $value ? 'checked' : '' }}>
                        <label class="form-check-label"
                            for="age_reason_{{ $value }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>

            {{-- 必要な資格 licenses --}}
            <div class="mb-4">
                <p class="form-label fw-bold">必要な資格</p>
                @for ($i = 1; $i <= 4; $i++)
                    @php
                        $license = $licenses[$i - 1] ?? null; // DB dan tanlangan malumotlar
                    @endphp
                    <div class="row g-4 align-items-center mb-3">
                        <!-- 資格グループ選択 (License Group Selection) -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-4 my-1">
                            <label for="group_code_{{ $i }}" class="form-label">資格グループ選択</label>
                            <select name="qualifications[{{ $i }}][group_code]"
                                id="group_code_{{ $i }}" class="form-select group-select py-1 px-2"
                                data-row="{{ $i }}">
                                <option value="" selected>選択してください</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->group_code }}"
                                        {{ $license && $license->group_code == $group->group_code ? 'selected' : '' }}>
                                        {{ $group->group_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 資格カテゴリ選択 (Category Selection) -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-4 my-1">
                            <label for="category_code_{{ $i }}" class="form-label">資格カテゴリ選択</label>
                            <select name="qualifications[{{ $i }}][category_code]"
                                id="category_code_{{ $i }}" class="form-select category-select py-1 px-2"
                                data-row="{{ $i }}">
                                <option value="" selected>選択してください</option>
                                @if ($license)
                                    <option value="{{ $license->category_code }}" selected>
                                        {{ $license->category_name }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        <!-- 資格 (License Selection) -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-4 my-1">
                            <label for="code_{{ $i }}" class="form-label">資格</label>
                            <select name="qualifications[{{ $i }}][code]"
                                id="license_code_{{ $i }}" class="form-select license-select py-1 px-2"
                                data-row="{{ $i }}">
                                <option value="" selected>選択してください</option>
                                @if ($license)
                                    <option value="{{ $license->code }}" selected>{{ $license->name }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                @endfor
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // 📌 **グループ選択 (group_code) が変更されたときに category_codes をロードする**
                    document.querySelectorAll(".group-select, .category-select").forEach((select) => {
                        select.addEventListener("change", function() {
                            const row = this.dataset.row;
                            const targetId = this.classList.contains("group-select") ?
                                `category_code_${row}` :
                                `license_code_${row}`;
                            fetchDataAndPopulate(this, targetId);
                        });
                    });

                    function fetchDataAndPopulate(select, targetId) {
                        const url = select.classList.contains("group-select") ?
                            `/get-license-categories?group_code=${select.value}` :
                            `/get-licenses?group_code=${groupCode}&category_code=${select.value}`;
                        fetch(url).then( /*...*/ );
                    }

                });
            </script>

            {{--  <!-- 学術情報 -->  --}}
            <p class="mb-4">希望学歴</p>
            <div class="mb-3 row align-items-center">
                <label for="academicInfo" class="col-md-3 col-form-label">学術情報</label>
                <div class="col-md-9">
                    <div class="row">
                        <!-- Academic Info Dropdown -->
                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                            <select name="hope_school_history_code" id="academicInfo" class="form-select">
                                <option value="">選択してください</option>
                                @foreach ($academicOptions as $option)
                                    <option value="{{ $option->code }}"
                                        {{ $job->hope_school_history_code == $option->code ? 'selected' : '' }}>
                                        {{ $option->detail }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('hope_school_history_code')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <!-- Graduation Year -->
                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                            <div class="col-md-9">
                                <label class="col-md-3 col-form-label">新卒</label>
                                <label class="me-4">
                                    <input type="radio" name="new_graduate_flag" value="1"
                                        {{ isset($scheduledData) && $scheduledData->new_graduate_flag == 1 ? 'checked' : '' }}>
                                    可能
                                </label>
                                <label>
                                    <input type="radio" name="new_graduate_flag" value="0"
                                        {{ isset($scheduledData) && $scheduledData->new_graduate_flag == 0 ? 'checked' : '' }}>
                                    不可能
                                </label>
                            </div>
                            @error('new_graduate_flag')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{--  <!-- 社員食堂 -->  --}}
            <div class="mb-3 row align-items-center">
                <label for="cafeteriaOption" class="col-md-3 col-form-label">社員食堂</label>
                <div class="col-md-3">
                    <select name="employee_restaurant_flag" id="cafeteriaOption" class="form-select">
                        <option value=""></option>
                        <option value="1" {{ $job->employee_restaurant_flag == 1 ? 'selected' : '' }}>有り</option>
                        <option value="0" {{ $job->employee_restaurant_flag == 0 ? 'selected' : '' }}>無し</option>
                    </select>
                    @error('employee_restaurant_flag')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <label for="cafeteriaOption" class="col-md-3 col-form-label">社員食堂</label>
                <div class="col-md-3">
                    <select name="board_flag" id="mealOption" class="form-select">
                        <option value=""></option>
                        <option value="1" {{ $job->board_flag == 1 ? 'selected' : '' }}>有り</option>
                        <option value="0" {{ $job->board_flag == 0 ? 'selected' : '' }}>無し</option>
                    </select>
                    @error('board_flag')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{--  <!-- 喫煙環境 -->  --}}
            <div class="mb-3 row align-items-center">
                <label for="smokingArea" class="col-md-3 col-form-label">喫煙環境</label>
                <div class="col-md-3">
                    <select name="smoking_flag" id="smokingArea" class="form-select">
                        <option value=""></option>
                        <option value="0" {{ $job->smoking_flag == 0 ? 'selected' : '' }}>無し</option>
                        <option value="1" {{ $job->smoking_flag == 1 ? 'selected' : '' }}>有り</option>
                    </select>
                    @error('smoking_flag')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <label for="smokingArea" class="col-md-3 col-form-label">喫煙エリア</label>
                <div class="col-md-3">
                    <select name="smoking_area_flag" id="smokingAreaOption" class="form-select">
                        <option value=""></option>
                        <option value="0" {{ $job->smoking_area_flag == 0 ? 'selected' : '' }}>無し</option>
                        <option value="1" {{ $job->smoking_area_flag == 1 ? 'selected' : '' }}>有り</option>
                    </select>
                    @error('smoking_area_flag')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- job_skillsにある --}}
            <p class="mt-4">会社が求めるスキルを選択</p>
            <div class="mb-4">
                <div class="row g-3">
                    @foreach ($categories as $categoryCode => $categoryName)
                        @php
                            $skills = DB::table('master_code')->where('category_code', $categoryCode)->get();
                            $selectedSkills = $jobSkills[$categoryCode] ?? [];
                        @endphp

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="border p-3" style="background-color: #e6f3d8;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>{{ $categoryName }}</strong>
                                    <button type="button" class="btn btn-sm btn-danger remove-selected">解除</button>
                                </div>
                                <select name="skills[{{ $categoryCode }}][]" class="form-control skill-select mt-2"
                                    multiple size="10">
                                    @foreach ($skills as $skill)
                                        <option value="{{ $skill->code }}"
                                            {{ collect($selectedSkills)->pluck('code')->contains($skill->code) ? 'selected' : '' }}>
                                            {{ $skill->detail }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <script>
                // "解除" (Remove selected) button functionality
                document.addEventListener("click", function(event) {
                    // Check if the clicked element is a "remove-selected" button
                    if (event.target.classList.contains("remove-selected")) {
                        const select = event.target.closest(".border").querySelector(".skill-select");

                        if (select) {
                            // Clear all selected options in the dropdown
                            [...select.options].forEach(option => option.selected = false);

                            // Trigger "change" event to notify any event listeners
                            select.dispatchEvent(new Event("change"));
                        } else {
                            console.error("Skill select dropdown not found for this button.");
                        }
                    }
                });
            </script>

            {{-- 特記事項はjob_supplement_infoにある --}}
            <p class="form-label fw-bold">選考手順</p>
            <div class="mb-4 p-4 border rounded bg-light">
                <div class="row g-3">
                    <!-- Step 1 -->
                    <div class="col-12 mb-2">
                        <label for="process1" class="form-label fw-bold">ステップ1：</label>
                        <input type="text" id="process1" name="process1" class="form-control"
                            value="{{ old('process1', $prData->process1 ?? '') }}">
                    </div>
                    @error('process1')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror

                    <!-- Step 2 -->
                    <div class="col-12 mb-2">
                        <label for="process2" class="form-label fw-bold">ステップ2：</label>
                        <input type="text" id="process2" name="process2" class="form-control"
                            value="{{ old('process2', $prData->process2 ?? '') }}">
                    </div>
                    @error('process2')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <!-- Step 3 -->
                    <div class="col-12 mb-2">
                        <label for="process3" class="form-label fw-bold">ステップ3：</label>
                        <input type="text" id="process3" name="process3" class="form-control"
                            value="{{ old('process3', $prData->process3 ?? '') }}">
                    </div>
                    @error('process3')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <!-- Step 4 -->
                    <div class="col-12">
                        <label for="process4" class="form-label fw-bold">ステップ4：</label>
                        <input type="text" id="process4" name="process4" class="form-control"
                            value="{{ old('process4', $prData->process4 ?? '') }}">
                    </div>
                    @error('process4')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>


            {{-- 特記事項はjob_supplement_infoにある --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="supplement_flags" class="form-label fw-bold">特記事項</label>
                    <button type="button" class="btn btn-danger btn-sm" id="clear-all">全解除</button>
                </div>
                <div class="row g-3">
                    @foreach ($checkboxOptions as $key => $label)
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check border rounded p-2 bg-light">
                                <input class="form-check-input" type="checkbox" id="checkbox_{{ $key }}"
                                    name="supplement_flags[]" value="{{ $key }}"
                                    {{ in_array($key, old('supplement_flags', $checkedSupplementFlags)) ? 'checked' : '' }}
                                    style="margin-left: 2px;">
                                <label class="form-check-label mb-0" for="checkbox_{{ $key }}"
                                    style="display: inline-block; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-left: 24px;">
                                    {{ $label }}
                                </label>
                            </div>
                            @error('supplement_flags')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <script>
                // ✅ "全解除" （すべてクリア）ボタンの機能
                document.getElementById("clear-all").addEventListener("click", function() {
                    document.querySelectorAll(".form-check-input").forEach((checkbox) => {
                        checkbox.checked = false;
                    });
                });
            </script>

            <div class="row g-3 mt-3">
                <div class="col-6">
                    <button type="button" onClick="history.back()" class="btn btn-primary w-100">
                        <i class="fa-solid fa-arrow-left"></i> 戻る
                    </button>
                </div>
                <div class="col-6">
                    <button type="submit" class="btn btn-primary w-100">更新する</button>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('js/updatejob.js') }}"></script>
@endsection
