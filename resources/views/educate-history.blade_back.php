@extends('layouts.top')

@php
    use Carbon\Carbon;
@endphp
@section('title', '学歴・職歴入力')
@section('content')
    <form method="POST" action="{{ route('educate-history.store') }}"> {{-- route('educate-history.store' --}}
        @csrf
        <div class="container-fluid mt-5 row">
            <h2 class="my-4 text-center">学歴・職歴入力</h2>
            @php
                $cnt = 0;
            @endphp
            <!-- 学歴フォーム -->
            <div id="education-forms" class="col-12 col-md-6 col-lg-6">
                <h3 class="text-main-theme mb-4 ">学歴</h3>
                @foreach ($schools as $key => $school)
                    @php
                        $cnt = $loop->count;
                    @endphp
                    @php
                        $entryYear = !empty($school->entry_day_year) ? $school->entry_day_year : '';
                        $entryMonth = !empty($school->entry_day_month)
                            ? str_pad($school->entry_day_month, 2, '0', STR_PAD_LEFT)
                            : '';

                        $graduateYear = !empty($school->graduate_day_year) ? $school->graduate_day_year : '';
                        $graduateMonth = !empty($school->graduate_day_month)
                            ? str_pad($school->graduate_day_month, 2, '0', STR_PAD_LEFT)
                            : '';
                    @endphp

                    <div class="">
                        <div class="card mb-4 shadow-sm border rounded-3 education-form">
                            <div class="card-header main-theme-color text-white">学歴 1</div>
                            <div class="card-body">{{ $key }}
                                <!-- 学校名 -->
                                <div class="mb-3">
                                    <label for="school_name" class="form-label">学校名</label>
                                    <input type="text" class="form-control border-secondary"
                                        name="educations[{{ $key }}][school_name]"
                                        value="{{ $school->school_name }}">
                                    @error('educations.{{ $key }}.school_name')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    <select class="form-select mt-2 border-secondary"
                                        name="educations[{{ $key }}][school_type_code]">
                                        <option value="">(選択してください)</option>
                                        <option value="{{ $school->school_type_code }}" selected>{{ $school->school_type }}
                                        </option> <!-- Tanlangan qiymat -->
                                        @foreach ($schoolTypes as $type)
                                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                                        @endforeach
                                    </select>
                                    @error('educations.{{ $key }}.school_type_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- 専攻 -->
                                <div class="mb-3">
                                    <label for="speciality" class="form-label">専攻</label>
                                    <input type="text" class="form-control border-secondary"
                                        name="educations[{{ $key }}][speciality]"
                                        value="{{ $school->speciality }}">
                                    @error('educations.{{ $key }}.speciality')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    <select class="form-select mt-2 border-secondary"
                                        name="educations[{{ $key }}][course_type]">
                                        <option value="">(選択してください)</option>
                                        <option value="{{ $school->course_type }}" selected>{{ $school->course }}</option>
                                        <!-- Tanlangan qiymat -->
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
                                    <label for="entry_day" class="form-label">入学年月</label>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <input type="text" class="form-control border-dark"
                                                name="educations[{{ $key }}][entry_day_year]"
                                                value ="{{ $entryYear }}">
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control border-dark"
                                                name="educations[{{ $key }}][entry_day_month]"
                                                value="{{ $entryMonth }}">
                                        </div>
                                    </div>
                                    {{--  <pre>{{ json_encode($entryTypes, JSON_PRETTY_PRINT) }}</pre>  --}}

                                    <select class="form-select border-secondary w-50"
                                        name="educations[{{ $key }}][entry_type_code]">
                                        <option value="">(選択してください)</option>
                                        @foreach ($entryTypes as $type)
                                            <option value="{{ $type->code }}"
                                                {{ isset($school->entry_type_code) && $school->entry_type_code == $type->code ? 'selected' : '' }}>
                                                {{ $type->detail }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('educations.{{ $key }}.entry_day_year')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    @error('educations.{{ $key }}.entry_day_month')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    @error('educations.{{ $key }}.entry_type_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!-- 卒業年 -->
                                <div class="mb-3">
                                    <label for="graduate_day" class="form-label">卒業年月</label>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <input type="text" class="form-control border-dark"
                                                name="educations[{{ $key }}][graduate_day_year]"
                                                value="{{ $graduateYear }}">
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control border-dark"
                                                name="educations[{{ $key }}][graduate_day_month]"
                                                value="{{ $graduateMonth }}">
                                        </div>
                                    </div>
                                    <select class="form-select border-secondary w-50"
                                        name="educations[{{ $key }}][graduate_type_code]">
                                        <option value="">(選択してください)</option>
                                        @foreach ($graduateTypes as $type)
                                            <option value="{{ $type->code }}"
                                                {{ isset($school->graduate_type_code) && $school->graduate_type_code == $type->code ? 'selected' : '' }}>
                                                {{ $type->detail }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('educations.{{ $key }}.graduate_day_year')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    @error('educations.{{ $key }}.graduate_day_month')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                    @error('educations.{{ $key }}.graduate_type_code')
                                        <span class="text-main-theme">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

            </div>
            @php
                ++$cnt;
            @endphp
            @endforeach
            @php
                if (count($schools) == 0) {
                    $cnt = 0;
                }
            @endphp
            <!-- 学歴追加ボタン -->
            <div class="text-center my-3">
                <button type="button" id="add-education" class="btn btn-primary">学歴を追加</button>
            </div>
        </div>

        <!-- 職歴フォーム -->
        @if (count($careers) > 1)
            {{ $careers[0]->company_name }} {{-- 20250210 commentout   --}}
        @endif
        @foreach ($careers as $key => $career)
            <div id="career-forms" class="col-12 col-md-6 col-lg-6">
                <h3 class="text-main-theme mb-4">職歴</h3>
                <div>
                    <!-- 職歴フォーム -->
                    <div class="card mb-4 shadow-sm border rounded-3 career-form">
                        <div class="card-header main-theme-color text-white">職歴 1</div>
                        <div class="card-body">
                            <!-- 会社名 -->
                            <div class="mb-3">
                                <label for="company_name" class="form-label">会社名</label>
                                <input type="text" class="form-control border-secondary" name="careers[0][company_name]"
                                    value="{{ $career->company_name }}">
                                @error('careers.0.company_name')
                                    <span class="text-main-theme">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 資本金 -->
                            <div class="mb-3">
                                <label for="capital" class="form-label">資本金</label>
                                <input type="text" class="form-control border-secondary" name="careers[0][capital]"
                                    value="{{ $career->capital }}"> 円
                                @error('careers.0.capital')
                                    <span class="text-main-theme">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 従業員数 -->
                            <div class="mb-3">
                                <label for="number_employees" class="form-label">従業員数</label>
                                <input type="text" class="form-control border-secondary"
                                    name="careers[0][number_employees]" value="{{ $career->number_employees }}"> 人
                                @error('careers.0.number_employees')
                                    <span class="text-main-theme">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 期間 -->
                            @php
                                $entryYear = $career->entry_day_year ?? '';
                                $entryMonth = isset($career->entry_day_month)
                                    ? str_pad($career->entry_day_month, 2, '0', STR_PAD_LEFT)
                                    : '';

                                $retireYear = $career->retire_day_year ?? '';
                                $retireMonth = isset($career->retire_day_month)
                                    ? str_pad($career->retire_day_month, 2, '0', STR_PAD_LEFT)
                                    : '';
                            @endphp

                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2">(入社日)</span>
                                <input type="text" class="form-control me-2 border-secondary w-25"
                                    name="careers[{{ $key }}][entry_day_year]"
                                    value="{{ old('careers.' . $key . '.entry_day_year', $entryYear) }}">
                                <span class="me-2">年</span>
                                <input type="text" class="form-control me-2 border-secondary w-25"
                                    name="careers[{{ $key }}][entry_day_month]"
                                    value="{{ old('careers.' . $key . '.entry_day_month', (int) $entryMonth) }}">
                                <span class="me-2">月</span>
                            </div>
                            @error("careers.$key.entry_day_year")
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            @error("careers.$key.entry_day_month")
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                            <div class="d-flex align-items-center">
                                <span class="me-2">(退社日)</span>
                                <input type="text" class="form-control me-2 border-secondary w-25"
                                    name="careers[{{ $key }}][retire_day_year]"
                                    value="{{ old('careers.' . $key . '.retire_day_year', $retireYear) }}">
                                <span class="me-2">年</span>
                                <input type="text" class="form-control me-2 border-secondary w-25"
                                    name="careers[{{ $key }}][retire_day_month]"
                                    value="{{ old('careers.' . $key . '.retire_day_month', (int) $retireMonth) }}">
                                <span class="me-2">月</span>
                            </div>
                            @error("careers.$key.retire_day_year")
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            @error("careers.$key.retire_day_month")
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                            <!-- 業種 -->
                            <div class="mb-3">
                                <label for="industry_type_code" class="form-label">業種</label>
                                <select class="form-select border-secondary"
                                    name="careers[{{ $key }}][industry_type_code]">
                                    <option value="">(選択してください)</option>
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
                                <select class="form-select border-secondary" name="careers[0][working_type_code]">
                                    <option value="">(選択してください)</option>
                                    <option value="{{ $career->working_type_code }}" selected>{{ $career->working_type }}
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
                                    name="careers[0][job_type_detail]" value="{{ $career->job_type_detail }}">
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
                                        @if ($type->big_class_code == $career->big_class_code)
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
                                <textarea class="form-control border-secondary" name="careers[0][business_detail]" rows="4"
                                    value="{{ $career->business_detail }}"></textarea>
                                @error('careers.0.business_detail')
                                    <span class="text-main-theme">{{ $message }}</span>
                                @enderror
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
                        <label for="company_name" class="form-label">会社名</label>
                        <input type="text" class="form-control border-secondary" name="careers[0][company_name]"
                            placeholder="例: 株式会社ABC">
                        @error('careers.0.company_name')
                            <span class="text-main-theme">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- 資本金 -->
                    <div class="mb-3">
                        <label for="capital" class="form-label">資本金</label>
                        <input type="text" class="form-control border-secondary" name="careers[0][capital]"
                            placeholder="例: 1000000"> 円
                        @error('careers.0.capital')
                            <span class="text-main-theme">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- 従業員数 -->
                    <div class="mb-3">
                        <label for="number_employees" class="form-label">従業員数</label>
                        <input type="text" class="form-control border-secondary" name="careers[0][number_employees]"
                            placeholder="例: 50"> 人
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
                                name="careers[0][entry_day_month]" placeholder="例: 4">
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
                                name="careers[0][retire_day_month]" placeholder="例: 3">
                            <span class="me-2">月</span>
                            {{--  <input type="checkbox" class="form-check-input border-dark" name="careers[0][currently_employed]"
                                        value="1" > 在籍中  --}}
                            @error('careers.0.retire_day_year')
                                <span class="text-main-theme">{{ $message }}</span>
                            @enderror
                            @error('careers.0.retire_day_month')
                                <span class="text-main-theme">{{ $message }}</span>
                            @enderror
                        </div>
                        {{--  <p class="text-main-theme">※在籍中の場合は、在籍中にチェックをお願いします。</p>  --}}
                    </div>

                    <!-- 業種 -->
                    <div class="mb-3">
                        <label for="industry_type_code" class="form-label">業種</label>
                        <select class="form-select border-secondary" name="careers[0][industry_type_code]">
                            <option value="">(選択してください)</option>
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
                            <option value="">(選択してください)</option>
                            @foreach ($workingTypes as $type)
                                <option value="{{ $type->code }}">{{ $type->detail }}</option>
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
                        <select class="form-select border-secondary mb-2" name="careers[0][job_type_big_code]"
                            onchange="updateSubCategories(this, 'jobTypeSubCategory_0')">
                            <option value="">▼大分類</option>
                            @foreach ($jobTypes->unique('big_class_code') as $type)
                                <option value="{{ $type->big_class_code }}">{{ $type->big_class_name }}</option>
                            @endforeach
                        </select>
                        @error('careers.0.job_type_big_code')
                            <span class="text-main-theme">{{ $message }}</span>
                        @enderror
                        <!-- 小分類 -->
                        <select class="form-select border-secondary" name="careers[0][job_type_small_code]"
                            id="jobTypeSubCategory_0">
                            <option value="">▼小分類</option>
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
                        <textarea class="form-control border-secondary" name="careers[0][business_detail]" rows="4"
                            placeholder="例: プロジェクト管理、プログラミング作業..."></textarea>
                        @error('careers.0.business_detail')
                            <span class="text-main-theme">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        @endif
        <!-- 職歴追加ボタン -->
        <div class="text-center my-3">
            <button type="button" id="add-career" class="btn btn-primary">職歴を追加</button>
        </div>
        </div>


        <!-- 保存ボタン -->
        <div class="row g-3 mt-3">
            <div class="col-6">
                <button type="button" onClick="history.back()" class="btn btn-primary w-100">戻る</button>
            </div>
            <div class="col-6">
                <button type="submit" class="btn btn-main-theme w-100">保存</button>
            </div>
        </div>
        </div>
    </form>
    <script>
        let educationIndex = 1;
        document.getElementById('add-education').addEventListener('click', function() {
            const educationForm = document.querySelector('.education-form').cloneNode(true);
            educationForm.querySelectorAll('input, select').forEach((input) => {
                input.name = input.name.replace(/\[0\]/, `[${educationIndex}]`);
                input.value = '';
            });
            educationForm.querySelector('.card-header').textContent = `学歴 ${educationIndex + 1}`;
            document.getElementById('education-forms').appendChild(educationForm);
            educationIndex++;
        });

        let careerIndex = document.querySelectorAll('.career-form').length;

        document.getElementById('add-career').addEventListener('click', function() {
            // Formani nusxalash
            const careerIndexForm = document.querySelector('.career-form').cloneNode(true);

            // Ichki elementlarni yangilash
            careerIndexForm.querySelectorAll('input, select, textarea').forEach((input) => {
                input.name = input.name.replace(/\[\d+\]/,
                `[${careerIndex}]`); // Eski indeksni yangi indeksga o'zgartirish
                input.value = ''; // Malumotlarni tozalash
            });

            // Formaning sarlavhasini o'zgartirish
            careerIndexForm.querySelector('.card-header').textContent = `職歴 ${careerIndex + 1}`;

            // Formani sahifaga qo'shish
            document.getElementById('career-forms').appendChild(careerIndexForm);

            // Indeksni oshirish
            careerIndex++;
        });

        const jobTypes = @json($jobTypes);

        // サブカテゴリ更新機能
        function updateSubCategories(selectElement, subCategoryId) {
            const selectedBigClassCode = selectElement.value;
            const subCategorySelect = document.getElementById(subCategoryId);

            // Sub-kategoriyalarni tozalash
            subCategorySelect.innerHTML = '<option value="">▼小分類</option>';

            // Filtrlash
            const filteredSubCategories = jobTypes.filter(jobType => jobType.big_class_code === selectedBigClassCode);

            // Sub-kategoriyalarni qo'shish
            filteredSubCategories.forEach(subCategory => {
                const option = document.createElement('option');
                option.value = subCategory.middle_class_code;
                option.textContent = subCategory.middle_clas_name;
                subCategorySelect.appendChild(option);
            });
        }


        // 新しい仕事を追加する
        document.getElementById('add-career').addEventListener('click', function() {
            const careerForm = document.querySelector('.career-form').cloneNode(true);

            // フォーム内のすべての入力、選択、テキストエリア要素を更新する
            careerForm.querySelectorAll('input, select, textarea').forEach((input) => {
                if (input.name) {
                    input.name = input.name.replace(/\[0\]/, `[${careerIndex}]`);
                    input.id = input.id ? input.id.replace(/_C1/, `_C${careerIndex + 1}`) : input.id;
                }
                input.value = '';
            });

            // フォームのタイトルを更新
            careerForm.querySelector('.card-header').textContent = `職歴 ${careerIndex + 1}`;

            // フォームを追加する
            document.getElementById('career-forms').appendChild(careerForm);

            // 各主要カテゴリ選択にonchangeイベントを追加する
            careerForm.querySelectorAll('[name^="careers"][name$="[job_type_big_code]"]').forEach((
                bigCategorySelect) => {
                bigCategorySelect.addEventListener('change', function() {
                    updateSubCategories(this, `jobTypeSubCategory_${careerIndex}`);
                });
            });

            careerIndex++;
        });
    </script>

@endsection
