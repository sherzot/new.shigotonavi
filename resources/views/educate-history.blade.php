@extends('layouts.top')

@php
    use Carbon\Carbon;
@endphp
@section('title', '学歴・職歴入力')
@section('content')
    <div class="container mt-5">
        <form method="POST" action="{{ route('educate-history.store') }}" class=""> {{-- route('educate-history.store' --}}
            @csrf

            <h2 class="my-4 text-center">学歴・職歴入力</h2>
            @php
                $cnt = 0;
            @endphp
            <!-- 学歴フォーム -->
            <div id="education-forms" class="">
                <h3 class="text-main-theme mb-4 ">学歴</h3>
                @if (count($schools) == 0)
                    @php
                        $schools = [
                            [
                                'school_name' => '',
                                'school_type_code' => '',
                                'speciality' => '',
                                'course_type' => '',
                                'entry_day_year' => '',
                                'entry_day_month' => '',
                                'graduate_day_year' => '',
                                'graduate_day_month' => '',
                            ],
                        ];
                    @endphp
                @endif

                @foreach ($schools as $key => $school)
                    @php
                        $cnt = $loop->count;
                        $entryYear = old(
                            'educations.' . $key . '.entry_day_year',
                            !empty($school->entry_day_year) ? (int) $school->entry_day_year : '',
                        );
                        $entryMonth = old(
                            'educations.' . $key . '.entry_day_month',
                            !empty($school->entry_day_month) ? sprintf('%02d', (int) $school->entry_day_month) : '',
                        );
                        $graduateYear = old(
                            'educations.' . $key . '.graduate_day_year',
                            !empty($school->graduate_day_year) ? (int) $school->graduate_day_year : '',
                        );
                        $graduateMonth = old(
                            'educations.' . $key . '.graduate_day_month',
                            !empty($school->graduate_day_month)
                                ? sprintf('%02d', (int) $school->graduate_day_month)
                                : '',
                        );
                    @endphp
                    {{--  @dump($school)  --}}
                    @php
                        $cnt = $loop->count;
                    @endphp
                    @php
                        $entryYear = !empty($school->entry_day_year) ? (int) $school->entry_day_year : '';
                        $entryMonth = !empty($school->entry_day_month)
                            ? sprintf('%02d', (int) $school->entry_day_month) // `04`として保存
                            : '';
                        $graduateYear = !empty($school->graduate_day_year) ? (int) $school->graduate_day_year : '';
                        $graduateMonth = !empty($school->graduate_day_month)
                            ? sprintf('%02d', (int) $school->graduate_day_month) // `04`として保存
                            : '';
                    @endphp

                    <div class="">
                        <div class="card mb-4 shadow-sm border rounded-3 education-form"
                            data-id="{{ $school->id ?? 'NO_ID' }}">
                            <input type="hidden" name="educations[{{ $key }}][id]"
                                value="{{ $school->id ?? 'NO_ID' }}">
                            <div class="card-header main-theme-color text-white">
                                学歴 {{ $key + 1 }}
                            </div>
                            <div class="card-body">{{ $key }}
                                <!-- 学校名 -->
                                <div class="mb-3">
                                    <label for="school_name" class="form-label">学校名<span class="text-main-theme">
                                            必須</span></label>
                                    <input type="text" class="form-control border-secondary"
                                        name="educations[{{ $key }}][school_name]"
                                        value="{{ is_object($school) ? $school->school_name : $school['school_name'] ?? '' }}">
                                    @error('educations.{{ $key }}.school_name')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    <select class="form-select mt-2 border-secondary"
                                        name="educations[{{ $key }}][school_type_code]">
                                        <option value="" >選択してください</option>
                                        <option selected
                                            value="{{ is_object($school) ? $school->school_type_code : $school['school_type_code'] ?? '' }}">
                                            {{ is_object($school) ? $school->school_type : $school['school_type'] ?? '' }}
                                        </option>
                                        </option>
                                        @foreach ($schoolTypes as $type)
                                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                                        @endforeach
                                    </select>
                                    @error('educations.{{ $key }}.school_type_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{--  <!-- 専攻 -->  --}}
                                <div class="mb-3">
                                    <label for="speciality" class="form-label">専攻</label>
                                    <input type="text" class="form-control border-secondary"
                                        name="educations[{{ $key }}][speciality]"
                                        value="{{ is_object($school) ? $school->speciality : $school['speciality'] ?? '' }}">

                                    @error('educations.{{ $key }}.speciality')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    <select class="form-select mt-2 border-secondary"
                                        name="educations[{{ $key }}][course_type]">
                                        <option value="" >選択してください</option>
                                        <option selected
                                            value="{{ is_object($school) ? $school->course_type : $school['course_type'] ?? '' }}">
                                            {{ is_object($school) ? $school->course : $school['course'] ?? '' }}
                                        </option>
                                        @foreach ($courseTypes as $type)
                                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                                        @endforeach
                                    </select>
                                    @error('educations.{{ $key }}.course_type')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!-- 入学年 -->
                                <div class="mb-3">
                                    <label for="entry_day" class="form-label">入学年月 <span class="text-main-theme">例:
                                            2025〜03</span></label>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <input type="text" class="form-control border-dark"
                                                name="educations[{{ $key }}][entry_day_year]"
                                                value ="{{ $entryYear }}" placeholder="入学年を入力してください">
                                        </div>
                                        <div class="col-6">
                                            {{--  <input type="text" class="form-control border-dark"
                                                name="educations[{{ $key }}][entry_day_month]"
                                                value="{{ $entryMonth }}">  --}}
                                            <input type="text" class="form-control me-2 border-secondary w-50"
                                                name="educations[{{ $key }}][entry_day_month]"
                                                value="{{ old('educations.' . $key . '.entry_day_month', sprintf('%02d', (int) $entryMonth)) }}"
                                                placeholder="入学月を入力してください">
                                        </div>
                                    </div>
                                    {{--  <pre>{{ json_encode($entryTypes, JSON_PRETTY_PRINT) }}</pre>  --}}

                                    <select class="form-select border-secondary w-50"
                                        name="educations[{{ $key }}][entry_type_code]">
                                        <option value="">選択してください</option>
                                        @foreach ($entryTypes as $type)
                                            <option value="{{ $type->code }}"
                                                {{ isset($school->entry_type_code) && $school->entry_type_code == $type->code ? 'selected' : '' }}>
                                                {{ $type->detail }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error("educations.$key.entry_day_year")
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    @error("educations.$key.entry_day_month")
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    @error('educations.{{ $key }}.entry_type_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!-- 卒業年 -->
                                <div class="mb-3">
                                    <label for="graduate_day" class="form-label">卒業年月 <span class="text-main-theme">例:
                                            2025〜03</span></label>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <input type="text" class="form-control border-dark"
                                                name="educations[{{ $key }}][graduate_day_year]"
                                                value="{{ $graduateYear }}" placeholder="卒業年を入力してください">
                                        </div>
                                        <div class="col-6">
                                            {{--  <input type="text" class="form-control border-dark"
                                                name="educations[{{ $key }}][graduate_day_month]"
                                                value="{{ $graduateMonth }}">  --}}
                                            <input type="text" class="form-control me-2 border-secondary w-50"
                                                name="educations[{{ $key }}][graduate_day_month]"
                                                value="{{ old('educations.' . $key . '.graduate_day_month', sprintf('%02d', (int) $graduateMonth)) }}"
                                                placeholder="卒業月を入力してください">
                                        </div>
                                    </div>
                                    <select class="form-select border-secondary w-50"
                                        name="educations[{{ $key }}][graduate_type_code]">
                                        <option value="">選択してください</option>
                                        @foreach ($graduateTypes as $type)
                                            <option value="{{ $type->code }}"
                                                {{ isset($school->graduate_type_code) && $school->graduate_type_code == $type->code ? 'selected' : '' }}>
                                                {{ $type->detail }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error("educations.$key.graduate_day_year")
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    @error("educations.$key.graduate_day_month")
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    @error('educations.{{ $key }}.graduate_type_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                {{--  <!-- 学歴追加ボタン -->  --}}
                                <div class="text-center my-3 col-6">
                                    <button type="button" class="btn btn-primary add-education">学歴を追加</button>
                                </div>
                                {{--  <!-- 下部に削除ボタンを追加 -->  --}}
                                <div class="col-6 text-center my-3">
                                    <button type="button" class="btn btn-danger btn-sm delete-education"
                                        data-id="{{ $school->id ?? '' }}" onclick="removeEducation(this)">
                                        削除
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            window.removeEducation = function(button) {
                                const educationId = button.getAttribute('data-id');
                                console.log("⬇️ 削除リクエストID:", educationId);

                                if (!educationId || educationId === 'NO_ID' || educationId === '') {
                                    console.warn("⚠️ 教育IDが見つかりません。 UI からのみ削除されます。");
                                    button.closest('.education-form').remove();
                                    return;
                                }

                                fetch(`/educate-history/${educationId}/delete`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                        }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        console.log("✅ Server javobi:", data);
                                        if (data.success) {
                                            console.log("🗑️ 教育は削除されました!");
                                            button.closest('.education-form').remove();
                                        } else {
                                            console.error("⚠️ サーバーから無効な応答を受信しました。:", data);
                                            alert("削除に失敗しました。");
                                        }
                                    })
                                    .catch(error => {
                                        console.error("❌ エラーが発生しました。:", error);
                                        alert("削除に失敗しました。");
                                    });
                            };
                        });
                    </script>

                    @php
                        ++$cnt;
                    @endphp
                @endforeach
                @php
                    if (count($schools) == 0) {
                        $cnt = 0;
                    }
                @endphp


                <!-- 職歴フォーム -->
                <div id="career-forms" class="">
                    <h3 class="text-main-theme mb-4 ">職歴</h3>
                    @if (count($careers) > 1)
                        {{-- $careers[0]->company_name --}} {{-- 20250210 commentout   --}}
                    @endif
                    @foreach ($careers as $key => $career)
                        @php
                            $index = $key + 1;
                        @endphp
                        <!-- 職歴フォーム -->
                        <div class="">
                            <div class="card mb-4 shadow-sm border rounded-3 career-form"
                                data-id="{{ $career->id ?? '' }}">
                                <input type="hidden" name="careers[{{ $key }}][id]"
                                    value="{{ $career->id ?? '' }}">
                                <div class="card-header main-theme-color text-white">職歴 {{ $key + 1 }}</div>
                                <div class="card-body">{{ $key }}
                                    <!-- 会社名 -->
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">会社名 <span class="text-main-theme">
                                                必須</span></label>
                                        <input type="text" class="form-control border-secondary"
                                            name="careers[{{ $key }}][company_name]"
                                            value="{{ $career->company_name }}">
                                        @error('careers.0.company_name')
                                            <span class="text-main-theme">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- 資本金 -->
                                    <div class="mb-3">
                                        <label for="capital" class="form-label">資本金</label>
                                        <input type="text" class="form-control border-secondary"
                                            name="careers[{{ $key }}][capital]"
                                            value="{{ old("careers.$key.capital", number_format($career->capital / 10000, 0, '', '')) }}">
                                        万円
                                        @error('careers.0.capital')
                                            <span class="text-main-theme">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- 従業員数 -->
                                    <div class="mb-3">
                                        <label for="number_employees" class="form-label">従業員数</label>
                                        <input type="text" class="form-control border-secondary"
                                            name="careers[{{ $key }}][number_employees]"
                                            value="{{ $career->number_employees }}"> 人
                                        @error('careers.0.number_employees')
                                            <span class="text-main-theme">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- 期間 -->
                                    @php
                                        $entryYear = !empty($career->entry_day_year)
                                            ? (int) $career->entry_day_year
                                            : '';
                                        $entryMonth = !empty($career->entry_day_month)
                                            ? sprintf('%02d', (int) $career->entry_day_month) // `04` shaklida saqlaydi
                                            : '';

                                        $retireYear = !empty($career->retire_day_year)
                                            ? (int) $career->retire_day_year
                                            : '';
                                        $retireMonth = !empty($career->retire_day_month)
                                            ? sprintf('%02d', (int) $career->retire_day_month) // `04` shaklida saqlaydi
                                            : '';
                                    @endphp
                                    <p for="entry_day" class="form-label">期間</p>
                                    <div class="d-flex align-items-center mb-2">

                                        <span class="me-2">(入社日)</span>
                                        <input type="text" class="form-control me-2 border-secondary w-25"
                                            name="careers[{{ $key }}][entry_day_year]"
                                            value="{{ old('careers.' . $key . '.entry_day_year', $entryYear) }}">
                                        <span class="me-2">年</span>
                                        {{--  <input type="text" class="form-control me-2 border-secondary w-25"
                                        name="careers[{{ $key }}][entry_day_month]"
                                        value="{{ old('careers.' . $key . '.entry_day_month', $entryMonth) }}">  --}}
                                        <input type="text" class="form-control me-2 border-secondary w-50"
                                            name="careers[{{ $key }}][entry_day_month]"
                                            value="{{ old('careers.' . $key . '.entry_day_month', sprintf('%02d', (int) $entryMonth)) }}">

                                        <span class="me-2">月</span>
                                    </div>
                                    @error("careers.$key.entry_day_year")
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    @error("careers.$key.entry_day_month")
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror

                                    <div class="d-flex align-items-center">
                                        <span class="me-2">(退社日)</span>
                                        <input type="text" class="form-control me-2 border-secondary w-25"
                                            name="careers[{{ $key }}][retire_day_year]"
                                            value="{{ old('careers.' . $key . '.retire_day_year', $retireYear) }}">
                                        <span class="me-2">年</span>
                                        {{--  <input type="text" class="form-control me-2 border-secondary w-25"
                                        name="careers[{{ $key }}][retire_day_month]"
                                        value="{{ old('careers.' . $key . '.retire_day_month', $retireMonth) }}">  --}}
                                        <input type="text" class="form-control me-2 border-secondary w-50"
                                            name="careers[{{ $key }}][retire_day_month]"
                                            value="{{ old('careers.' . $key . '.retire_day_month', sprintf('%02d', (int) $retireMonth)) }}">

                                        <span class="me-2">月</span>
                                    </div>
                                    @error("careers.$key.retire_day_year")
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    @error("careers.$key.retire_day_month")
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror


                                    <!-- 業種 -->
                                    <div class="mb-3">
                                        <label for="industry_type_code" class="form-label">業種</label>
                                        <select class="form-select border-secondary"
                                            name="careers[{{ $key }}][industry_type_code]">
                                            <option value="">選択してください</option>
                                            @foreach ($industryTypes as $type)
                                                <option value="{{ $type->code }}"
                                                    {{ $type->code == old("careers.{$key}.industry_type_code", $career->industry_type_code) ? 'selected' : '' }}>
                                                    {{ $type->detail }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>
                                    @error('careers.0.industry_type_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror

                                    <!-- 勤務形態 -->
                                    <div class="mb-3">
                                        <label for="working_type_code" class="form-label">勤務形態</label>
                                        <select class="form-select border-secondary"
                                            name="careers[{{ $key }}][working_type_code]">
                                            <option value="" >選択してください</option>
                                            <option selected value="{{ $career->working_type_code }}">
                                                {{ $career->working_type_code }}
                                            </option>
                                            @foreach ($workingTypes as $type)
                                                <option value="{{ $type->code }}">{{ $type->detail }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('careers.0.working_type_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror

                                    <!-- 職種 -->
                                    <div class="mb-3">
                                        <p class="form-label">職種</p>
                                        <label for="job_type_detail">(1)職種名</label>
                                        <input type="text" class="form-control border-secondary mb-2"
                                            name="careers[{{ $key }}][job_type_detail]"
                                            value="{{ $career->job_type_detail }}">
                                        @error('careers.0.job_type_detail')
                                            <span class="text-main-theme">{{ $message }}</span>
                                        @enderror
                                        <!-- 大分類 -->
                                        <label for="job_type_detail">(2) 分 類</label>
                                        <select class="form-select border-secondary mb-2"
                                            name="careers[{{ $key }}][job_type_big_code]"
                                            onchange="updateSubCategories(this, 'jobTypeSubCategory_{{ $key }}')">
                                            <option value="">▼大分類</option>
                                            @foreach ($jobTypes->unique('big_class_code') as $type)
                                                <option value="{{ $type->big_class_code }}"
                                                    {{ old('careers.' . $key . '.job_type_big_code', $career->big_class_code ?? '') == $type->big_class_code ? 'selected' : '' }}>
                                                    {{ $type->big_class_name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('careers.0.job_type_big_code')
                                            <span class="text-main-theme">{{ $message }}</span>
                                        @enderror
                                        <!-- 小分類 -->
                                        <select class="form-select border-secondary"
                                            name="careers[{{ $key }}][job_type_small_code]"
                                            id="jobTypeSubCategory_{{ $key }}">
                                            <option value="">▼小分類</option>
                                            @foreach ($jobTypes as $type)
                                                @if (!empty($career->big_class_code) && $type->big_class_code == $career->big_class_code)
                                                    <option value="{{ $type->middle_class_code }}"
                                                        {{ isset($career->middle_class_code) && $career->middle_class_code == $type->middle_class_code ? 'selected' : '' }}>
                                                        {{ $type->middle_clas_name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>

                                        @error('careers.0.job_type_small_code')
                                            <span class="text-main-theme">{{ $message }}</span>
                                        @enderror
                                    </div>


                                    <!-- 職務内容 -->
                                    <div class="mb-3">
                                        <label for="business_detail" class="form-label">職務内容</label>
                                        <p>
                                            ※全角で1000字以内です。<br>
                                            ※職務経歴書出力項目です。<br>
                                            <span class="text-main-theme">※PDF印刷時は63文字/1行で16行まで。</span><br>
                                            ※企業が閲覧できる項目です。（ここに会社名を書かないようにご注意下さい）<br>
                                            ※応募時の書類選考で、最も重要視される指標の1つです。<br>
                                            職務内容は具体的にかつ明確にお書き頂くと書類選考通過の可能性が高まります
                                        </p>
                                        <textarea class="form-control border-secondary" name="careers[{{ $key }}][business_detail]"
                                            rows="4">{{ old('careers.' . $key . '.business_detail', $career->business_detail) }}</textarea>
                                        @error("careers.$key.business_detail")
                                            <span class="text-main-theme">{{ $message }}</span>
                                        @enderror

                                    </div>
                                </div>

                                <div class="row">
                                    {{--  <!-- 職歴追加ボタン -->  --}}
                                    <div class="text-center my-3 col-6">
                                        <button type="button" class="btn btn-primary add-career">職歴を追加</button>
                                    </div>
                                    {{--  <!-- 下部に削除ボタンを追加 -->  --}}
                                    <div class="col-6">
                                        @if (!empty($career->id))
                                            <div class="text-center my-3">
                                                <button type="button" class="btn btn-danger btn-sm delete-career"
                                                    data-id="{{ $career->id }}">削除</button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endforeach

                    @if (count($careers) === 0)
                        <div class="card mb-4 shadow-sm border rounded-3 career-form">
                            <div class="card-header main-theme-color text-white">職歴 1</div>
                            <div class="card-body">
                                <!-- 会社名 -->
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">会社名 <span class="text-main-theme">
                                            必須</span></label>
                                    <input type="text" class="form-control border-secondary"
                                        name="careers[0][company_name]" placeholder="例: 株式会社ABC">
                                    @error('careers.0.company_name')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- 資本金 -->
                                <div class="mb-3">
                                    <label for="capital" class="form-label">資本金</label>
                                    <input type="text" class="form-control border-secondary"
                                        name="careers[{{ $key }}][capital]"
                                        value="{{ old("careers.$key.capital", number_format($career->capital / 1000, 0, '', '')) }}">
                                    万円
                                    @error('careers.0.capital')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- 従業員数 -->
                                <div class="mb-3">
                                    <label for="number_employees" class="form-label">従業員数</label>
                                    <input type="text" class="form-control border-secondary"
                                        name="careers[0][number_employees]" placeholder="例: 50"> 人
                                    @error('careers.0.number_employees')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- 期間 -->
                                <div class="mb-3">
                                    <label for="entry_day" class="form-label">期間</label>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="me-2">(入社日)</span>
                                        <input type="text" class="form-control me-2 border-secondary w-25"
                                            name="careers[0][entry_day_year]" placeholder="例: 2020">
                                        <span class="me-2">年</span>
                                        <input type="text" class="form-control me-2 border-secondary w-25"
                                            name="careers[0][entry_day_month]" placeholder="例: 04">
                                        <span class="me-2">月</span>
                                        @error('careers.0.entry_day_year')
                                            <span class="text-main-theme">{{ $message }}</span>
                                        @enderror
                                        @error('careers.0.entry_day_month')
                                            <span class="text-main-theme">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">(退社日)</span>
                                        <input type="text" class="form-control me-2 border-secondary w-25"
                                            name="careers[0][retire_day_year]" placeholder="例: 2024">
                                        <span class="me-2">年</span>
                                        <input type="text" class="form-control me-2 border-secondary w-25"
                                            name="careers[0][retire_day_month]" placeholder="例: 03">
                                        <span class="me-2">月</span>
                                        @error('careers.0.retire_day_year')
                                            <span class="text-main-theme">{{ $message }}</span>
                                        @enderror
                                        @error('careers.0.retire_day_month')
                                            <span class="text-main-theme">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- 業種 -->
                                <div class="mb-3">
                                    <label for="industry_type_code" class="form-label">業種</label>
                                    <select class="form-select border-secondary" name="careers[0][industry_type_code]">
                                        <option value="">選択してください</option>
                                        @foreach ($industryTypes as $type)
                                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                                        @endforeach
                                    </select>
                                    @error('careers.0.industry_type_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- 勤務形態 -->
                                <div class="mb-3">
                                    <label for="working_type_code" class="form-label">勤務形態</label>
                                    <select class="form-select border-secondary" name="careers[0][working_type_code]">
                                        <option value="" >選択してください</option>
                                        @foreach ($workingTypes as $type)
                                            <option selected value="{{ $type->code }}">{{ $type->detail }}</option>
                                        @endforeach
                                    </select>
                                    @error('careers.0.working_type_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- 職種 -->
                                <div class="mb-3">
                                    <p class="form-label">職種</p>
                                    <label for="job_type_detail">(1)職種名</label>
                                    <input type="text" class="form-control border-secondary mb-2"
                                        name="careers[0][job_type_detail]">
                                    @error('careers.0.job_type_detail')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    <!-- 大分類 -->
                                    <label for="job_type_detail">(2) 分 類</label>
                                    <select class="form-select border-secondary mb-2"
                                        name="careers[{{ $key }}][job_type_big_code]"
                                        onchange="updateSubCategories(this, 'jobTypeSubCategory_{{ $key }}')">
                                        <option value="">▼大分類</option>
                                        @foreach ($jobTypes->unique('big_class_code') as $type)
                                            <option value="{{ $type->big_class_code }}"
                                                {{ old('careers.' . $key . '.job_type_big_code', $career->big_class_code ?? '') == $type->big_class_code ? 'selected' : '' }}>
                                                {{ $type->big_class_name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('careers.0.job_type_big_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    <!-- 小分類 -->
                                    <select class="form-select border-secondary"
                                        name="careers[{{ $key }}][job_type_small_code]"
                                        id="jobTypeSubCategory_{{ $key }}">
                                        <option value="">▼小分類</option>
                                    </select>

                                    @error('careers.0.job_type_small_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>


                                <!-- 職務内容 -->
                                <div class="mb-3">
                                    <label for="business_detail" class="form-label">職務内容 <span class="text-main-theme">
                                            必須</span></label>
                                    <p>
                                        ※全角で1000字以内です。<br>
                                        ※職務経歴書出力項目です。<br>
                                        <span class="text-main-theme">※PDF印刷時は63文字/1行で16行まで。</span><br>
                                        ※企業が閲覧できる項目です。（ここに会社名を書かないようにご注意下さい）<br>
                                        ※応募時の書類選考で、最も重要視される指標の1つです。<br>
                                        職務内容は具体的にかつ明確にお書き頂くと書類選考通過の可能性が高まります
                                    </p>
                                    <textarea class="form-control border-secondary" name="careers[0][business_detail]" rows="4"
                                        placeholder="例: プロジェクト管理、プログラミング作業..."></textarea>
                                    @error('careers.0.business_detail')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                {{--  <!-- 職歴追加ボタン -->  --}}
                                <div class="text-center my-3 col-6">
                                    <button type="button" class="btn btn-primary add-career">職歴を追加</button>
                                </div>
                                {{--  <!-- 下部に削除ボタンを追加 -->  --}}
                                @if (!empty($career->id))
                                    <div class="text-center my-3 col-6">
                                        <button type="button" class="btn btn-danger btn-sm delete-career"
                                            data-id="{{ $career->id }}">削除</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                </div>
                @endif



                <!-- 保存ボタン -->
                <div class="row g-3 my-3">
                    <div class="col-6">
                        <a href="{{ route('resume') }}" class="btn btn-primary w-100">戻る</a>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-main-theme w-100">保存</button>
                    </div>
                </div>
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let educationIndex = document.querySelectorAll('.education-form').length || 1;
            let careerIndex = document.querySelectorAll('.career-form').length || 1;
            const jobTypes = @json($jobTypes);

            // 🏫 学歴を追加 (Ta'lim qo‘shish)
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('add-education')) {
                    const parent = event.target.closest(
                        '.education-form'); // ボタンが配置されているフォームを見つける
                    if (!parent) {
                        console.error("❌ エラー: 'education-form' が見つかりません。");
                        return;
                    }

                    const educationForm = parent.cloneNode(true); // フォームを複製する
                    educationIndex++;

                    // 新しい ID を割り当てます (バックエンドから提供される必要があります)
                    let newId = Math.floor(Math.random() * 1000000);
                    educationForm.setAttribute('data-id', newId);
                    educationForm.querySelector('input[name^="educations"][name$="[id]"]').value = newId;

                    // 入力を更新し、名前の値を選択する
                    educationForm.querySelectorAll('input, select').forEach((input) => {
                        if (input.name) {
                            input.name = input.name.replace(/\[\d+\]/, `[${educationIndex}]`);
                        }
                        input.value = ''; // データクリーニング
                    });

                    // フォームを更新して新しいフォームを追加する
                    educationForm.querySelector('.card-header').textContent = `学歴 ${educationIndex}`;
                    parent.after(educationForm);

                    // 古いボタンを削除して新しいボタンを追加します
                    const oldButton = educationForm.querySelector('.add-education');
                    if (oldButton) oldButton.remove();

                    const newButton = document.createElement('div');
                    newButton.classList.add("text-center", "my-3");
                    newButton.innerHTML =
                        '<button type="button" class="btn btn-primary add-education">学歴を追加</button>';
                    educationForm.appendChild(newButton);

                    // ボタンの前のフォームの後に新しいフォームを追加します
                    parent.after(educationForm);
                }

            });

            // 🏢 職歴を追加 (Ish tajribasi qo‘shish)
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('add-career')) {
                    const parent = event.target.closest('.career-form'); // ボタンが配置されているフォームを見つける
                    if (!parent) {
                        console.error("❌ ERROR: 'career-form' topilmadi.");
                        return;
                    }

                    const careerForm = parent.cloneNode(true);
                    careerIndex++;

                    // 新しい ID を割り当てます (バックエンドから提供される必要があります)
                    let newId = Math.floor(Math.random() * 1000000);
                    careerForm.setAttribute('data-id', newId);
                    careerForm.querySelector('input[name^="careers"][name$="[id]"]').value = newId;

                    // 入力を更新し、名前の値を選択する
                    careerForm.querySelectorAll('input, select, textarea').forEach((input) => {
                        if (input.name) {
                            input.name = input.name.replace(/\[\d+\]/, `[${careerIndex}]`);
                        }
                        if (input.id && input.id.includes("jobTypeSubCategory")) {
                            input.id = `jobTypeSubCategory_${careerIndex}`;
                        }
                        input.value = ''; // データクリーニング
                    });

                    // フォームを更新して新しいフォームを追加する
                    careerForm.querySelector('.card-header').textContent = `職歴 ${careerIndex}`;
                    parent.after(careerForm);

                    // 古いボタンを削除して新しいボタンを追加します
                    const oldButton = careerForm.querySelector('.add-career');
                    if (oldButton) oldButton.remove();

                    const newButton = document.createElement('div');
                    newButton.classList.add("text-center", "my-3");
                    newButton.innerHTML =
                        '<button type="button" class="btn btn-primary add-career">職歴を追加</button>';
                    careerForm.appendChild(newButton);

                    parent.after(careerForm); // ボタンの前のフォームの後に新しいフォームを追加します
                }
                // 職歴 （キャリア）削除
                if (event.target.classList.contains('delete-career')) {
                    const careerId = event.target.getAttribute('data-id');
                    if (confirm("この職歴を削除してもよろしいですか？")) {
                        fetch(`/career-history/${careerId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .content
                                }
                            }).then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    event.target.closest('.career-form').remove();
                                } else {
                                    alert("削除に失敗しました。");
                                }
                            });
                    }
                }
            });



            // 🔄 より大きなカテゴリを選択するときにサブカテゴリを更新する
            function updateSubCategories(selectElement, subCategoryId) {
                const selectedBigClassCode = selectElement.value;
                const subCategorySelect = document.getElementById(subCategoryId);

                console.log("Selected Big Class Code:", selectedBigClassCode);
                console.log("Sub Category Select Element:", subCategorySelect);

                if (!subCategorySelect) {
                    console.error("❌ ERROR: subCategorySelect is NULL. Check subCategoryId:", subCategoryId);
                    return;
                }

                subCategorySelect.innerHTML = '<option value="">▼小分類</option>';

                const filteredSubCategories = jobTypes.filter(jobType => jobType.big_class_code ===
                    selectedBigClassCode);

                console.log("Filtered Sub Categories:", filteredSubCategories);
                console.log(document.getElementById("jobTypeSubCategory_" + careerIndex));

                filteredSubCategories.forEach(subCategory => {
                    const option = document.createElement('option');
                    option.value = subCategory.middle_class_code;
                    option.textContent = subCategory.middle_clas_name;
                    subCategorySelect.appendChild(option);
                });
            }

            // 🛠 ページで利用可能なすべてのジョブタイプのイベントを設定します
            document.querySelectorAll('[name^="careers"][name$="[job_type_big_code]"]').forEach((
                bigCategorySelect) => {
                bigCategorySelect.addEventListener('change', function() {
                    let careerIndexMatch = this.name.match(/\[(\d+)\]/);
                    let careerIndexFromName = careerIndexMatch ? careerIndexMatch[1] : 0;
                    const subCategoryId = `jobTypeSubCategory_${careerIndexFromName}`;
                    updateSubCategories(this, subCategoryId);
                });
            });
        });
    </script>

@endsection
