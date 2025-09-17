@extends('layouts.layout')

@section('title', '求人票を作成する')

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
    <h2>求人票を作成する</h2><br>
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('jobs.create_job.post') }}" method="POST">
        @csrf
        <div class="row">
            <!-- 勤務形態 -->
            <div class="mb-3 col-12 col-sm-6 col-md-4">
                <label for="order_type" class="form-label fw-bold">
                    勤務形態
                    <span style="color: rgba(255, 0, 0, 0.674);">（必須）</span>:
                </label>
                <select id="order_type" name="order_type" class="form-select form-select-lg">
                    <option disabled {{ old('order_type') ? '' : 'selected' }}>選択してください</option>
                    @foreach ($orderTypes as $orderType)
                    <option value="{{ $orderType->code }}" {{ old('order_type') == $orderType->code ? 'selected' : '' }}>
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
                    <span style="color: rgba(255, 0, 0, 0.674);">（必須）</span>:
                </label>
                <select id="order_progress_type" name="order_progress_type" class="form-select form-select-lg">
                    <option value="">選択してください</option>
                    <option value="1" {{ old('order_progress_type') == '1' ? 'selected' : '' }}>受注中</option>
                    <option value="2" {{ old('order_progress_type') == '2' ? 'selected' : '' }}>受注完</option>
                </select>
                @error('order_progress_type')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- しごとナビ掲載有無 -->
            <div class="mb-3 col-12 col-sm-6 col-md-4">
                <label for="public_flag" class="form-label fw-bold">
                    しごとナビ掲載有無
                    <span style="color: rgba(255, 0, 0, 0.674);">（必須）</span>:
                </label>
                <select id="public_flag" name="public_flag" class="form-select form-select-lg">
                    <option selected value="">選択してください</option>
                    <option value="0" {{ old('public_flag') == '0' ? 'selected' : '' }}>非掲載</option>
                    <option value="1" {{ old('public_flag') == '1' ? 'selected' : '' }}>掲載</option>
                </select>
                @error('public_flag')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- 希望職種 -->
        {{-- job_type_detailはjob_orderテブルにある  --}}
        <div class="mb-4">
            <p for="job_type_detail" class="form-label">職種名 <span style="color: rgba(255, 0, 0, 0.674);">（必須）</span></p>
            <input type="text" class="form-control" id="job_type_detail" name="job_type_detail" value="{{ old('job_type_detail') }}">
            @error('job_type_detail')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <!-- 希望職種 (最大3つ) -->
        {{-- 希望職種はjob_working_typeテブルのjob_type_codeです  --}}
        <p class="form-label">募集職種 <small style="color: rgba(255, 0, 0, 0.674);">上記に記入した職種に該当するカテゴリを以下より指定してください。（１つ以上必須）</small></p>
        @for ($i = 1; $i <= 3; $i++) <div class="row g-3 mt-3">
            <div class="mb-4 col-12 col-sm-12 col-md-6">
                <label for="big_class_code_{{ $i }}" class="form-label">募集職種　{{ $i }}：</label>
                <select name="big_class_code[]" id="big_class_code_{{ $i }}" class="form-control big-class-select">
                    <option value="">選択してください</option>
                    @foreach ($bigClasses as $bigClass)
                    <option value="{{ $bigClass->big_class_code }}" {{ old('big_class_code.' . ($i - 1)) == $bigClass->big_class_code ? 'selected' : '' }}>
                        {{ $bigClass->big_class_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- 職種タイプ -->
            <div class="mb-4 col-12 col-sm-12 col-md-6">
                <label for="middle_class_code_{{ $i }}" class="form-label">職種タイプ　{{ $i }}：</label>
                <select name="job_category[]" id="middle_class_code_{{ $i }}" class="form-control middle-class-select">
                    <option value="">選択してください</option>
                    {{-- Agar kerak bo‘lsa bu yerga dynamic options qo‘shishingiz mumkin --}}
                </select>
            </div>
</div>
@error('big_class_code', 'middle_class_code')
<div class="text-danger">{{ $message }}</div>
@enderror
@endfor


{{-- PRは全部job_supplement_infoテブルにある  --}}
<!-- 1-PR -->
<p class="form-label m-0 pt-3">フリーＰＲ【１】<span style="color: rgba(255, 0, 0, 0.674);">（必須）</span></p>
<div class="row g-3 mt-3">
    <div class="col-12 col-sm-12 col-md-6">
        <label for="pr_title1" class="form-label">ＰＲタイトル: <span style="color: rgba(255, 0, 0, 0.674);">（必須）</span> </label>
        <input type="text" class="form-control" id="pr_title1" name="pr_title1" value="{{ old('pr_title1') }}">
    </div>
    @error('pr_title1')
    <div class="text-danger">{{ $message }}</div>
    @enderror
    <div class="col-12 col-sm-12 col-md-6">
        <label for="pr_contents1" class="form-label ">ＰＲ内容: <span style="color: rgba(255, 0, 0, 0.674);">（必須）</span></label>
        <textarea class="form-control" id="pr_contents1" name="pr_contents1" rows="4">{{ old('pr_contents1') }}</textarea>
    </div>
    @error('pr_contents1')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<!-- 2-PR -->
<p class="form-label m-0 pt-3">フリーＰＲ【２】</p>
<div class="row g-3 mt-3">
    <div class="col-12 col-sm-12 col-md-6">
        <label for="pr_title2" class="form-label">ＰＲタイトル:</label>
        <input type="text" class="form-control" id="pr_title2" name="pr_title2" value="{{ old('pr_title2') }}">
    </div>
    @error('pr_title2')
    <div class="text-danger">{{ $message }}</div>
    @enderror
    <div class="col-12 col-sm-12 col-md-6">
        <label for="pr_contents2" class="form-label">ＰＲ内容:</label>
        <textarea class="form-control" id="pr_contents2" name="pr_contents2" rows="4">{{ old('pr_contents2') }}</textarea>
    </div>
    @error('pr_contents2')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<!-- 3-PR -->
<p class="form-label m-0 pt-3">フリーＰＲ【３】</p>
<div class="row g-3 mt-3">
    <div class="col-12 col-sm-12 col-md-6">
        <label for="pr_title3" class="form-label">ＰＲタイトル:</label>
        <input type="text" class="form-control" id="pr_title3" name="pr_title3" value="{{ old('pr_title3') }}">
    </div>
    @error('pr_title3')
    <div class="text-danger">{{ $message }}</div>
    @enderror
    <div class="col-12 col-sm-12 col-md-6">
        <label for="pr_contents3" class="form-label">ＰＲ内容:</label>
        <textarea class="form-control" id="pr_contents3" name="pr_contents3" rows="4">{{ old('pr_contents3') }}</textarea>
    </div>
    @error('pr_contents3')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
<div class="row g-3 mt-3">
    <div class="col-12">
        <p class="form-label">担当業務の説明</p>
        <textarea class="form-control" id="business_detail" name="business_detail" rows="4">{{ old('business_detail') }}</textarea>
    </div>
    @error('business_detail')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div><br>

{{-- 求める経験はjob_noteにある  --}}
<div class="row g-3 my-3">
    <div class="col-12">
        <p class="form-label">求める経験</p>
        <textarea class="form-control" id="note" name="BestMatch" rows="4">{{ old('BestMatch') }}</textarea>
    </div>
    @error('BestMatch')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

{{-- job_orderにある --}}
<p>しごとナビ掲載期限: <span class="text-danger">必須：（例）20060401（半角入力）</span></p>
<div class="row g-3 my-3">
    <div class="col-12">
        <input type="text" class="form-control" id="public_limit_day" name="public_limit_day" value="{{ old('public_limit_day') }}">
    </div>
    @error('public_limit_day')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

{{-- 特記事項はjob_supplement_infoにある --}}
<p>会社の特徴: （例）「音楽楽器の大手企業」</p>
<div class="row g-3 my-3">
    <div class="col-12">
        <input type="text" class="form-control" id="company_speciality" name="company_speciality" value="{{ old('company_speciality') }}">
    </div>
    @error('company_speciality')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
<p>キャッチコピー</p>
<div class="row g-3 my-3">
    <div class="col-12">
        <textarea class="form-control" id="catch_copy" name="catch_copy" rows="1">{{ old('catch_copy') }}</textarea>
    </div>
    @error('catch_copy')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mt-4">
    <p>お仕事の割合</p>
    <div class="row">
        <!-- Row for each task -->
        <div class="col-12 mb-3">
            <div class="row align-items-center">
                <div class="col-md-2 text-end">
                    <label for="biz_name1" class="form-label">業務1</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="biz_name1" name="biz_name1" class="form-control" value="{{ old('biz_name1') }}">
                </div>
                <div class="col-md-1 text-center">
                    <span class="fw-bold">全体の</span>
                </div>
                @error('biz_name1')
                <div class="text-danger">{{ $message }}</div>
                @enderror
                <div class="col-md-2">
                    <input type="text" id="biz_percentage1" name="biz_percentage1" class="form-control text-end" maxlength="3" value="{{ old('biz_percentage1') }}">
                </div>
                <div class="col-md-1">
                    <span class="fw-bold">%</span>
                </div>
                @error('biz_percentage1')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Repeat for other tasks -->
        <div class="col-12 mb-3">
            <div class="row align-items-center">
                <div class="col-md-2 text-end">
                    <label for="biz_name2" class="form-label">業務2</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="biz_name2" name="biz_name2" class="form-control" value="{{ old('biz_name2') }}">
                </div>

                <div class="col-md-1 text-center">
                    <span class="fw-bold">全体の</span>
                </div>
                @error('biz_name2')
                <div class="text-danger">{{ $message }}</div>
                @enderror
                <div class="col-md-2">
                    <input type="text" id="biz_percentage2" name="biz_percentage2" class="form-control text-end" maxlength="3" value="{{ old('biz_percentage2') }}">
                </div>
                <div class="col-md-1">
                    <span class="fw-bold">%</span>
                </div>
                @error('biz_percentage2')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Add rows for 業務3 and 業務4 -->
        <div class="col-12 mb-3">
            <div class="row align-items-center">
                <div class="col-md-2 text-end">
                    <label for="BizName3" class="form-label">業務3</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="biz_name3" name="biz_name3" class="form-control" value="{{ old('biz_name3') }}">
                </div>
                <div class="col-md-1 text-center">
                    <span class="fw-bold">全体の</span>
                </div>
                @error('biz_name3')
                <div class="text-danger">{{ $message }}</div>
                @enderror
                <div class="col-md-2">
                    <input type="text" id="biz_percentage3" name="biz_percentage3" class="form-control text-end" maxlength="3" value="{{ old('biz_percentage3') }}">
                </div>
                <div class="col-md-1">
                    <span class="fw-bold">%</span>
                </div>
                @error('biz_percentage3')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-12 mb-3">
            <div class="row align-items-center">
                <div class="col-md-2 text-end">
                    <label for="BizName4" class="form-label">業務4</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="biz_name4" name="biz_name4" class="form-control" value="{{ old('biz_name4') }}">
                </div>
                <div class="col-md-1 text-center">
                    <span class="fw-bold">全体の</span>
                </div>
                @error('biz_name4')
                <div class="text-danger">{{ $message }}</div>
                @enderror
                <div class="col-md-2">
                    <input type="text" id="biz_percentage4" name="biz_percentage4" class="form-control text-end" maxlength="3" value="{{ old('biz_percentage4') }}">
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

{{-- 給与はjob_orderにある. 年収 と　月給  --}}
<div class="mb-4">
    <p class="form-label">給料<span style="color: rgba(255, 0, 0, 0.674);">（必須）</span></p>
    <div class="row g-4">
        {{-- 年収・月給 --}}
        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">年収 <span style="color: rgba(255, 0, 0, 0.674);">（例：4000000 ~ 5000000）</span></label>
            <div class="row g-2 align-items-center">
                <div class="col-5">
                    <input type="text" name="yearly_income_min" class="form-control" placeholder="最低額" value="{{ old('yearly_income_min') }}">
                </div>
                <div class="col-2 text-center">〜</div>
                <div class="col-5">
                    <input type="text" name="yearly_income_max" class="form-control" placeholder="最高額" value="{{ old('yearly_income_max') }}">
                </div>
                @error('yearly_income_min')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
                @error('yearly_income_max')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <label class="form-label fw-bold mt-3">月給 <span style="color: rgba(255, 0, 0, 0.674);">（例：400000 ~ 500000）</span></label>
            <div class="row g-2 align-items-center">
                <div class="col-5">
                    <input type="text" name="monthly_income_min" class="form-control" placeholder="最低月給" value="{{ old('monthly_income_min') }}">
                </div>
                <div class="col-2 text-center">〜</div>
                <div class="col-5">
                    <input type="text" name="monthly_income_max" class="form-control" placeholder="最高月給" value="{{ old('monthly_income_max') }}">
                </div>
                @error('monthly_income_min')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
                @error('monthly_income_max')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- 時給・日給 --}}
        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">時給 <span style="color: rgba(255, 0, 0, 0.674);">（例：1000 ~ 1800）</span></label>
            <div class="row g-2 align-items-center">
                <div class="col-5">
                    <input type="text" name="hourly_income_min" class="form-control" placeholder="最低時給" value="{{ old('hourly_income_min') }}">
                </div>
                <div class="col-2 text-center">〜</div>
                <div class="col-5">
                    <input type="text" name="hourly_income_max" class="form-control" placeholder="最高時給" value="{{ old('hourly_income_max') }}">
                </div>
                @error('hourly_income_min')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
                @error('hourly_income_max')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <label class="form-label fw-bold mt-3">日給 <span style="color: rgba(255, 0, 0, 0.674);">（例：8000 ~ 12000）</span></label>
            <div class="row g-2 align-items-center">
                <div class="col-5">
                    <input type="text" name="daily_income_min" class="form-control" placeholder="最低日給" value="{{ old('daily_income_min') }}">
                </div>
                <div class="col-2 text-center">〜</div>
                <div class="col-5">
                    <input type="text" name="daily_income_max" class="form-control" placeholder="最高日給" value="{{ old('daily_income_max') }}">
                </div>
                @error('daily_income_min')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
                @error('daily_income_max')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>


{{-- 給与備考  --}}
<div class="mb-4">
    <p class="form-label">給与備考</p>
    <input type="text" class="form-control" id="income_remark" name="income_remark" value="{{ old('income_remark') }}">
    @error('income_remark')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

{{-- job_scheduled_to_intraduntion-  雇用開始予定日 --}}
<div class="mb-4">
    <div class="row g-3 align-items-center mb-3">
        <div class="col-12 col-sm-12 col-md-6">
            <p class="form-label fw-bold">雇用開始予定日 <span style="color: rgba(255, 0, 0, 0.674);">（必須）</span></p>
            <input type="text" name="employment_start_day" class="form-control" value="{{ old('employment_start_day') }}">

            <small class="form-text text-muted">(例: 20250420)</small>
            {{-- <p id="notification_message2" class="text-danger" style="display: none;">
                            この会社の求人は派遣または紹介予定派遣なので、エージェントの担当者に通知されます。
                        </p>  --}}
            @error('employment_start_day')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- <!-- 雇用形態 ->  --}}
<div class="p-2 mb-4 bg-white" id="employment_start_date2">
    <!-- 勤務期間 -->
    <label for="workStartDay" class="form-label">勤務期間: <span style="color: rgba(255, 0, 0, 0.674);">(例: 20100420 〜　20100420)</span></label>
    <div class="row align-items-center">
        <!-- 開始日 -->
        <div class="col-md-4">
            <input type="text" class="form-control text-center" name="work_start_day" id="workStartDay" placeholder="YYYYMMDD" value="{{ old('work_start_day') }}">
        </div>
        @error('work_start_day')
        <div class="text-danger">{{ $message }}</div>
        @enderror
        <div class="col-md-1 text-center">〜</div>
        <!-- 終了日 -->
        <div class="col-md-4">
            <input type="text" class="form-control text-center" name="work_end_day" id="workEndDay" placeholder="YYYYMMDD" value="{{ old('work_end_day') }}">
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
            <input type="radio" name="work_update_flag" value="1" id="updateYes" {{ old('work_update_flag') == '1' ? 'checked' : '' }}>
            <label for="updateYes">更新有り</label>
        </div>
        @error('work_update_flag')
        <div class="text-danger">{{ $message }}</div>
        @enderror

        <div class="col-auto">
            <label for="workPeriod" class="form-label">（更新期間:</label>
            <input type="text" name="work_period" id="workPeriod" class="form-control d-inline-block" style="width: 50px; text-align: center;" value="{{ old('work_period') }}">
            <span>ヶ月）</span>
        </div>
        @error('work_period')
        <div class="text-danger">{{ $message }}</div>
        @enderror

        <div class="col-auto">
            <input type="radio" name="work_update_flag" value="0" id="updateNo" {{ old('work_update_flag') == '0' ? 'checked' : '' }}>
            <label for="updateNo">更新無し</label>
        </div>
        @error('work_update_flag')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>



<p class="form-label fw-bold">勤務時間 <span style="color: rgba(255, 0, 0, 0.674);">（必須）</span></p>

{{-- 勤務時間 work_start_time & work_end_time --}}
<div class="row g-3 mt-3 align-items-center">
    <!-- 開始時間 -->
    <div class="col-12 col-md-5">
        <label for="work_start_time" class="form-label my-1">開始時間 <span style="color: rgba(255, 0, 0, 0.674);">例；0900</span></label>
        <input type="text" class="form-control" id="work_start_time" name="work_start_time" value="{{ old('work_start_time') }}">
    </div>
    @error('work_start_time')
    <div class="text-danger">{{ $message }}</div>
    @enderror

    <!-- ~  -->
    <div class="col-12 col-md-1 text-center">
        <span class="fw-bold">~</span>
    </div>

    <!-- 終了時間 -->
    <div class="col-12 col-md-5">
        <label for="work_end_time" class="form-label my-1">終了時間 <span style="color: rgba(255, 0, 0, 0.674);">例；1800</span></label>
        <input type="text" class="form-control" id="work_end_time" name="Work_end_time" value="{{ old('Work_end_time') }}">
    </div>
    @error('work_end_time')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

{{-- 休憩時間 rest_start_time & rest_end_time --}}
<div class="row g-3 mt-3 align-items-center">
    <!-- 休憩開始時間 -->
    <div class="col-12 col-md-5">
        <label for="rest_start_time" class="form-label my-1">休憩開始時間 <span style="color: rgba(255, 0, 0, 0.674);">例；1200</span></label>
        <input type="text" class="form-control" id="rest_start_time" name="rest_start_time" value="{{ old('rest_start_time') }}">
    </div>
    @error('rest_start_time')
    <div class="text-danger">{{ $message }}</div>
    @enderror

    <!-- ~  -->
    <div class="col-12 col-md-1 text-center">
        <span class="fw-bold">~</span>
    </div>

    <!-- 休憩終了時間 -->
    <div class="col-12 col-md-5">
        <label for="rest_end_time" class="form-label my-1">休憩終了時間 <span style="color: rgba(255, 0, 0, 0.674);">例；1300</span></label>
        <input type="text" class="form-control" id="rest_end_time" name="rest_end_time" value="{{ old('rest_end_time') }}">
    </div>
    @error('rest_end_time')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
{{-- over_work_flag  --}}
<div class="mb-4">
    <p class="form-label mt-3 mb-1">時間外 <span style="color: rgba(255, 0, 0, 0.674);">必須</span></p>

    <input type="radio" id="over_work_yes" name="over_work_flag" value="1" {{ old('over_work_flag') == '1' ? 'checked' : '' }}>
    <label for="over_work_yes" class="pr-2">有</label>

    <input type="radio" id="over_work_no" name="over_work_flag" value="0" {{ old('over_work_flag') == '0' ? 'checked' : '' }}>
    <label for="over_work_no">無</label>

    @error('over_work_flag')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
</div>

{{-- work_time_remark  --}}
<div class="row g-3 my-3">
    <div class="col-12">
        <p class="form-label">就業時間備考:</p>
        <textarea class="form-control" id="work_time_remark" name="work_time_remark" rows="2">{{ old('work_time_remark') }}</textarea>
    </div>
    @error('work_time_remark')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
{{-- weekly_holiday_type  --}}
<div class="mb-4">
    <p class="form-label mb-1">休日</p>
    <input type="radio" id="weekly_holiday_type" name="weekly_holiday_type" value="001" {{ old('weekly_holiday_type') == '001' ? 'checked' : '' }}>
    <label for="weekly_holiday_type" class="pr-2">完全週休2日</label>
    <input type="radio" id="weekly_holiday_type" name="weekly_holiday_type" value="002" {{ old('weekly_holiday_type') == '002' ? 'checked' : '' }}>
    <label for="weekly_holiday_type" class="pr-2">変則週休2日</label>
    <input type="radio" id="weekly_holiday_type" name="weekly_holiday_type" value="003" {{ old('weekly_holiday_type') == '003' ? 'checked' : '' }}>
    <label for="weekly_holiday_type" class="pr-2">週休2日</label>
    <input type="radio" id="weekly_holiday_type" name="weekly_holiday_type" value="004" {{ old('weekly_holiday_type') == '004' ? 'checked' : '' }}>
    <label for="weekly_holiday_type" class="pr-2">週休1日</label>
    <input type="radio" id="weekly_holiday_type" name="weekly_holiday_type" value="999" {{ old('weekly_holiday_type') == '999' ? 'checked' : '' }}>
    <label for="weekly_holiday_type" class="pr-2">その他</label>
    @error('weekly_holiday_type')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
</div>
{{-- holiday_remark  --}}
<div class="row g-3 my-3">
    <div class="col-12">
        <textarea class="form-control" id="holiday_remark" name="holiday_remark" rows="2">{{ old('holiday_remark') }}</textarea>
    </div>
    @error('holiday_remark')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

{{-- 希望勤務地はjob_working_placeにあります。  --}}
<div class="mb-4 mt-3">
    <p class="form-label">勤務地(都道府県)：<span style="color: rgba(255, 0, 0, 0.674);">必須</span></p>
    <select name="prefecture_code[]" id="prefecture_code" class="form-control" multiple>
        <!-- 地域 -->
        @if (isset($regionGroups))
        @foreach ($regionGroups as $region)
        <optgroup label="{{ $region['detail'] }}">
            @foreach ($region['prefectures'] as $prefecture)
            <option value="{{ $prefecture['code'] }}" {{ collect(old('prefecture_code'))->contains($prefecture['code']) ? 'selected' : '' }}>
                {{ $prefecture['detail'] }}
            </option>
            @endforeach
        </optgroup>
        @endforeach
        @endif

        <!-- 個別 -->
        @if (isset($individualPrefectures) && is_array($individualPrefectures))
        <optgroup label="個別 (各都道府県)">
            @foreach ($individualPrefectures as $prefecture)
            <option value="{{ $prefecture['code'] }}" {{ collect(old('prefecture_code'))->contains($prefecture['code']) ? 'selected' : '' }}>
                {{ $prefecture['detail'] }}
            </option>
            @endforeach
        </optgroup>
        @endif
    </select>
    @error('prefecture_code')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    <p class="form-label my-0 pt-3">市区町村住所</p>
    <div class="form-row">
        <div class="form-group col-sm-6 col-md-4 col-lg-3">
            <label for="city">市区郡</label>
            <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}">
        </div>
        @error('city')
        <div class="text-danger">{{ $message }}</div>
        @enderror
        <div class="form-group col-sm-6 col-md-4 col-lg-3">
            <label for="town">町村</label>
            <input type="text" class="form-control" id="town" name="town" value="{{ old('town') }}">
        </div>
        @error('town')
        <div class="text-danger">{{ $message }}</div>
        @enderror
        <div class="form-group col-sm-6 col-md-4 col-lg-3">
            <label for="address">番地等</label>
            <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}">
        </div>
        @error('address')
        <div class="text-danger">{{ $message }}</div>
        @enderror
        <div class="form-group col-sm-6 col-md-4 col-lg-3">
            <label for="telephone_number">電話番号</label>
            <input type="text" class="form-control" id="telephone_number" name="telephone_number" value="{{ old('telephone_number') }}">
        </div>
        @error('telephone_number')
        <div class="text-danger">{{ $message }}</div>
        @enderror
        <div class="form-group col-sm-6 col-md-4 col-lg-3">
            <label for="section">部署名</label>
            <input type="text" class="form-control" id="section" name="section" value="{{ old('section') }}">
        </div>
        @error('section')
        <div class="text-danger">{{ $message }}</div>
        @enderror

        <div class="form-group col-sm-6 col-md-4 col-lg-3">
            <label for="charge_person_post">役職</label>
            <input type="text" class="form-control" id="charge_person_post" name="charge_person_post" value="{{ old('charge_person_post') }}">
        </div>
        @error('charge_person_post')
        <div class="text-danger">{{ $message }}</div>
        @enderror
        <div class="form-group col-sm-6 col-md-4 col-lg-3">
            <label for="charge_person_name">担当者</label>
            <input type="text" class="form-control" id="charge_person_name" name="charge_person_name" value="{{ old('charge_person_name') }}">
        </div>
        @error('charge_person_name')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

</div>
{{-- 年齢制限 & 制限理由  --}}
<div class="mb-4">
    <p class="form-label m-0 fw-bold">年齢制限:<span style="color: rgba(255, 0, 0, 0.674);">必須</span></p>
    <div class="row g-2 align-items-center">
        <!-- Age Inputs -->
        <div class="col-5">
            <input type="text" name="age_min" id="age_min" class="form-control mt-2" placeholder="最低年齢" value="{{ old('age_min') }}">
        </div>
        @error('age_min')
        <div class="text-danger">{{ $message }}</div>
        @enderror
        <div class="col-2 text-center">〜</div>
        <div class="col-5">
            <input type="text" name="age_max" id="age_max" class="form-control mt-2" placeholder="最高年齢" value="{{ old('age_max') }}">
        </div>
        @error('age_max')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <p class="form-label mt-3 fw-bold">制限理由</p>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="age_reason_flag" id="noLimit" value="{{ old('age_reason_flag') }}">
        <label class="form-check-label" for="noLimit">年齢不問</label>
    </div>

    <div class="form-check">
        {{-- <input class="form-check-input" type="radio" name="age_reason_flag" id="upperLimit" value="K" {{ old('age_reason_flag') === 'K' ? 'checked' : '' }}> --}}
        <input type="radio" name="age_reason_flag" id="careerReasonM" value="K" {{ old('age_reason_flag') === 'K' ? 'checked' : '' }}>
        <label class="form-check-label" for="upperLimit">
            定年年齢を上限 <span style="color: rgba(255, 0, 0, 0.674);">（下限年齢入力不可）.</span>
        </label>
    </div>
    <div class="form-check">
        {{-- <input class="form-check-input" type="radio" name="age_reason_flag" id="industryRequirement" value="L" {{ old('age_reason_flag') === 'L' ? 'checked' : '' }}> --}}
        <input type="radio" name="age_reason_flag" id="careerReasonM" value="L" {{ old('age_reason_flag') === 'L' ? 'checked' : '' }}>
        <label class="form-check-label" for="industryRequirement">
            業務・産業による表現の実現
        </label>
    </div>
    <div class="form-check">
        {{-- <input class="form-check-input" type="radio" name="age_reason_flag" id="careerReason" value="M" {{ old('age_reason_flag') === 'M' ? 'checked' : '' }}> --}}
        <input type="radio" name="age_reason_flag" id="careerReasonM" value="M" {{ old('age_reason_flag') === 'M' ? 'checked' : '' }}>
        <label class="form-check-label" for="careerReason">
            長期継続によるキャリア形成のため若年者を採用 <span style="color: rgba(255, 0, 0, 0.674);">（下限年齢入力不可、経験者募集不可）</span>
        </label>
    </div>
    <div class="form-check">
        {{-- <input class="form-check-input" type="radio" name="age_reason_flag" id="careerReason" value="N" {{ old('age_reason_flag') === 'N' ? 'checked' : '' }}> --}}
        <input type="radio" name="age_reason_flag" id="careerReasonM" value="N" {{ old('age_reason_flag') === 'N' ? 'checked' : '' }}>
        <label class="form-check-label" for="careerReason">
            技能等の継承のため労働者数の少ない年齢層を対象 <span style="color: rgba(255, 0, 0, 0.674);">（5～10歳の幅で上下同年齢層の1/2であること）</span>
        </label>
    </div>
    <div class="form-check">
        {{-- <input class="form-check-input" type="radio" name="age_reason_flag" id="careerReason" value="O" {{ old('age_reason_flag') === 'O' ? 'checked' : '' }}> --}}
        <input type="radio" name="age_reason_flag" id="careerReasonM" value="O" {{ old('age_reason_flag') === 'O' ? 'checked' : '' }}>
        <label class="form-check-label" for="careerReason">
            芸術・芸能における表現の真実性
        </label>
    </div>
    <div class="form-check">
        {{-- <input class="form-check-input" type="radio" name="age_reason_flag" id="careerReason" value="P" {{ old('age_reason_flag') === 'P' ? 'checked' : '' }}> --}}
        <input type="radio" name="age_reason_flag" id="careerReasonM" value="P" {{ old('age_reason_flag') === 'P' ? 'checked' : '' }}>
        <label class="form-check-label" for="careerReason">
            高年齢者又は国の雇用促進施策に係わる年齢層に限定
        </label>
    </div>
    @if ($errors->has('age_reason_flag'))
    <div class="text-danger">
        {{ $errors->first('age_reason_flag') }}
    </div>
    @endif
</div>

{{-- 資格はjob_licenseにある  --}}
@for ($i = 1; $i <= 4; $i++) @php $oldQualifications=old('qualifications') ?? []; $groupOld=$oldQualifications[$i]['group_code'] ?? '' ; $categoryOld=$oldQualifications[$i]['category_code'] ?? '' ; $codeOld=$oldQualifications[$i]['code'] ?? '' ; @endphp <div class="row g-4 align-items-center mb-3">
    <!-- 資格グループ選択 -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-4 my-1">
        <label for="group_code_{{ $i }}" class="form-label">資格グループ選択</label>
        <select name="qualifications[{{ $i }}][group_code]" id="group_code_{{ $i }}" class="form-select group-select py-1 px-2" data-row="{{ $i }}">
            <option value="">選択してください</option>
            @foreach ($groups as $group)
            <option value="{{ $group->group_code }}" {{ $groupOld == $group->group_code ? 'selected' : '' }}>
                {{ $group->group_name }}
            </option>
            @endforeach
        </select>
    </div>

    <!-- 資格カテゴリ選択 -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-4 my-1">
        <label for="category_code_{{ $i }}" class="form-label">資格カテゴリ選択</label>
        <select name="qualifications[{{ $i }}][category_code]" id="category_code_{{ $i }}" class="form-select category-select py-1 px-2" data-row="{{ $i }}">
            <option value="" disabled {{ $categoryOld == '' ? 'selected' : '' }}>選択してください</option>
            @if (isset($categoryOptions[$i]))
            @foreach ($categoryOptions[$i] as $category)
            <option value="{{ $category->category_code }}" {{ $categoryOld == $category->category_code ? 'selected' : '' }}>
                {{ $category->category_name }}
            </option>
            @endforeach
            @endif
        </select>
    </div>

    <!-- 資格選択 -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-4 my-1">
        <label for="code_{{ $i }}" class="form-label">資格</label>
        <select name="qualifications[{{ $i }}][code]" id="license_code_{{ $i }}" class="form-select license-select py-1 px-2" data-row="{{ $i }}">
            <option value="" disabled {{ $codeOld == '' ? 'selected' : '' }}>選択してください</option>
            @if (isset($licenseOptions[$i]))
            @foreach ($licenseOptions[$i] as $license)
            <option value="{{ $license['code'] }}" {{ $codeOld == $license['code'] ? 'selected' : '' }}>
                {{ $license['name'] ?? '' }}
            </option>
            @endforeach
            @endif
        </select>
    </div>

    @error("qualifications.$i.code")
    <div class="text-danger">{{ $message }}</div>
    @enderror
    </div>
    @endfor


    {{-- 学術情報 hope_school_history_code & new_graduate_flag --}}
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
                        <option value="{{ $option->code }}" {{ old('hope_school_history_code') == $option->code ? 'selected' : '' }}>
                            {{ $option->detail }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @error('hope_school_history_code')
                <div class="text-danger">{{ $message }}</div>
                @enderror

                <!-- Graduation Year (新卒) -->
                <div class="col-md-6 col-12 mb-2 mb-md-0">
                    <div class="col-md-9">
                        <label class="col-md-3 col-form-label">新卒</label>
                        <label class="me-4">
                            <input type="radio" name="new_graduate_flag" value="1" {{ old('new_graduate_flag') === '1' ? 'checked' : '' }}>可能
                        </label>
                        <label>
                            <input type="radio" name="new_graduate_flag" value="0" {{ old('new_graduate_flag') === '0' ? 'checked' : '' }}>不可能
                        </label>
                    </div>
                    @error('new_graduate_flag')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    {{-- <!-- 社員食堂 -->  --}}
    <div class="mb-3 row align-items-center">
        <label for="cafeteriaOption" class="col-md-3 col-form-label">社員食堂</label>
        <div class="col-md-3">
            <select name="employee_restaurant_flag" id="cafeteriaOption" class="form-select">
                <option value="" {{ old('employee_restaurant_flag') == '' ? 'selected' : '' }}></option>
                <option value="1" {{ old('employee_restaurant_flag') == '1' ? 'selected' : '' }}>有り</option>
                <option value="0" {{ old('employee_restaurant_flag') == '0' ? 'selected' : '' }}>無し</option>
            </select>
            @error('employee_restaurant_flag')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <label for="mealOption" class="col-md-3 col-form-label">社員食堂</label>
        <div class="col-md-3">
            <select name="board_flag" id="mealOption" class="form-select">
                <option value="" {{ old('board_flag') == '' ? 'selected' : '' }}></option>
                <option value="1" {{ old('board_flag') == '1' ? 'selected' : '' }}>有り</option>
                <option value="0" {{ old('board_flag') == '0' ? 'selected' : '' }}>無し</option>
            </select>
            @error('board_flag')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- <!-- 喫煙環境 -->  --}}
    <div class="mb-3 row align-items-center">
        <label for="smokingArea" class="col-md-3 col-form-label">喫煙環境</label>
        <div class="col-md-3">
            <select name="smoking_flag" id="smokingArea" class="form-select">
                <option value="" {{ old('smoking_flag') == '' ? 'selected' : '' }}></option>
                <option value="0" {{ old('smoking_flag') == '0' ? 'selected' : '' }}>無し</option>
                <option value="1" {{ old('smoking_flag') == '1' ? 'selected' : '' }}>有り</option>
            </select>
            @error('smoking_flag')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <label for="smokingAreaOption" class="col-md-3 col-form-label">喫煙エリア</label>
        <div class="col-md-3">
            <select name="smoking_area_flag" id="smokingAreaOption" class="form-select">
                <option value="" {{ old('smoking_area_flag') == '' ? 'selected' : '' }}></option>
                <option value="0" {{ old('smoking_area_flag') == '0' ? 'selected' : '' }}>無し</option>
                <option value="1" {{ old('smoking_area_flag') == '1' ? 'selected' : '' }}>有り</option>
            </select>
            @error('smoking_area_flag')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- job_skill テブル  --}}
    @php
    // カテゴリーの定義
    $categories = [
    'OS' => 'オペレーションシステム',
    'Application' => 'アプリケーション',
    'DevelopmentLanguage' => '開発言語',
    'Database' => 'データベース',
    ];
    @endphp

    <p class="mt-4">会社が求めるスキルを選択</p>
    <div class="mb-4">
        <div class="row g-3">
            @foreach ($categories as $categoryCode => $categoryName)
            @php
            $skills = DB::table('master_code')->where('category_code', $categoryCode)->get();
            $selectedSkills = old('skills.' . $categoryCode, []); // old qiymatlarni array sifatida olamiz
            @endphp

            <div class="col-12 col-sm-6 col-md-3">
                <div class="border p-3" style="background-color: #e6f3d8;">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>{{ $categoryName }}</strong>
                        <button type="button" class="btn btn-sm btn-danger remove-selected">解除</button>
                    </div>
                    <select name="skills[{{ $categoryCode }}][]" class="form-control skill-select mt-2" multiple size="10">
                        @foreach ($skills as $skill)
                        <option value="{{ $skill->code }}" {{ in_array($skill->code, $selectedSkills) ? 'selected' : '' }}>
                            {{ $skill->detail }}
                        </option>
                        @endforeach
                    </select>
                    @error('skills.' . $categoryCode)
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- 特記事項はjob_supplement_infoにある --}}
    <p class="form-label fw-bold">選考手順 <span style="color: rgba(255, 0, 0, 0.674);">例：応募 -> 書類選考 -> WEB面接 -> 内定</span></p>
    <div class="mb-4 p-4 border rounded bg-light">
        <div class="row g-3">
            <!-- Step 1 -->
            <div class="col-12 mb-2">
                <label for="process1" class="form-label fw-bold">ステップ1：</label>
                <input type="text" id="process1" name="process1" class="form-control" value="{{ old('process1') }}">
            </div>
            @error('process1')
            <div class="text-danger">{{ $message }}</div>
            @enderror

            <!-- Step 2 -->
            <div class="col-12 mb-2">
                <label for="process2" class="form-label fw-bold">ステップ2：</label>
                <input type="text" id="process2" name="process2" class="form-control" value="{{ old('process2') }}">
            </div>
            @error('process2')
            <div class="text-danger">{{ $message }}</div>
            @enderror

            <!-- Step 3 -->
            <div class="col-12 mb-2">
                <label for="process3" class="form-label fw-bold">ステップ3：</label>
                <input type="text" id="process3" name="process3" class="form-control" value="{{ old('process3') }}">
            </div>
            @error('process3')
            <div class="text-danger">{{ $message }}</div>
            @enderror

            <!-- Step 4 -->
            <div class="col-12">
                <label for="process4" class="form-label fw-bold">ステップ4：</label>
                <input type="text" id="process4" name="process4" class="form-control" value="{{ old('process4') }}">
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
                    <input class="form-check-input" type="checkbox" id="checkbox_{{ $key }}" name="supplement_flags[]" value="{{ $key }}" {{ in_array($key, old('supplement_flags', [])) ? 'checked' : '' }} style="margin-left: 2px;">
                    <label class="form-check-label mb-0" for="checkbox_{{ $key }}" style="display: inline-block; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-left: 24px;">
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
        // ✅ "全解除" (Clear All) tugmasi funksiyasi
        document.getElementById("clear-all").addEventListener("click", function() {
            document.querySelectorAll(".form-check-input").forEach((checkbox) => {
                checkbox.checked = false;
            });
        });

    </script>

    <div class="container-fluid text-center mt-4">
        <div class="row justify-content-center row-cols-2 row-cols-md-4 g-2">
            <div class="col">
                <button type="button" onClick="history.back()" class="btn btn-primary w-100 m-1">
                    <i class="fa-solid fa-arrow-left"></i> 戻る
                </button>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-danger w-100 m-1" name="submit">
                    求人票を作成
                </button>
            </div>
        </div>
    </div>
    </form>
    </div>
    @endsection

    @section('scripts')
    <script src="{{ asset('js/createjob.js') }}"></script>
    @endsection
