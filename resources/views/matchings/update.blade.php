@extends('layouts.top')

@section('title', '条件を変更して再検索')
@section('content')
    <div class="container">
        <h2 class="text-center my-3">条件を変更して再検索</h2>
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif


        <form action="{{ route('matchings.updateResults') }}" method="GET">
            @csrf
            @if (session('debug'))
                <pre>
        {{ print_r($savedJobTypes, true) }}
        {{ print_r($savedPrefecture, true) }}
        {{ print_r($savedWorkingCondition, true) }}
    </pre>
            @endif

            <div class="mb-4">
                <label for="big_class_code" class="form-label">希望職種</label>
                <select id="big_class_code" name="big_class_code" class="form-control"
                    data-saved-value="{{ $savedBigClassCode ?? '' }}">
                    <option value="">選択してください</option>
                    @foreach ($bigClasses as $bigClass)
                        <option value="{{ $bigClass->big_class_code }}"
                            {{ isset($savedBigClassCode) && $savedBigClassCode == $bigClass->big_class_code ? 'selected' : '' }}>
                            {{ $bigClass->big_class_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="middle_class_code" class="form-label">職種タイプ</label>
                <select id="middle_class_code" name="middle_class_code" class="form-control"
                    data-saved-value="{{ $savedMiddleClassCode ?? '' }}">
                    <option value="">選択してください</option>
                </select>
            </div>

            <!-- Prefecture -->
            <div class="mb-3">
                <label for="prefecture_code" class="form-label">希望勤務地(都道府県):</label>
                <select name="prefecture_code[]" id="prefecture_code" class="form-control" multiple>
                    @foreach ($prefectures as $prefecture)
                        <option value="{{ $prefecture->code }}"
                            {{ in_array($prefecture->code, $savedPrefectures) ? 'selected' : '' }}>
                            {{ $prefecture->detail }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="desired_salary_type" class="form-label">希望給与タイプ</label><br>
                <input type="radio" id="annual" name="desired_salary_type" value="年収"
                    {{ old('desired_salary_type', '年収') === '年収' ? 'checked' : ($savedWorkingCondition->yearly_income_min ? 'checked' : '') }}>
                <label for="annual">年収</label>
                <input type="number" name="desired_salary_annual" id="desired_salary_annual" class="form-control mt-2"
                    placeholder="例: 400 (万円)" value="{{ old('desired_salary_annual') }}"
                    data-saved-value="{{ $savedWorkingCondition->yearly_income_min ? $savedWorkingCondition->yearly_income_min / 10000 : '' }}"
                    disabled>

                <input type="radio" id="hourly" name="desired_salary_type" value="時給"
                    {{ old('desired_salary_type') === '時給' ? 'checked' : ($savedWorkingCondition->hourly_income_min ? 'checked' : '') }}>
                <label for="hourly">時給</label>
                <input type="number" name="desired_salary_hourly" id="desired_salary_hourly" class="form-control mt-2"
                    placeholder="例: 1200 (円)" value="{{ old('desired_salary_hourly') }}"
                    data-saved-value="{{ $savedWorkingCondition->hourly_income_min }}" disabled>
            </div>

            <div class="mb-4">
                <label for="supplement_flags" class="form-label">特記事項</label>
                <div class="table-responsive-sm">
                    <table class="table table-borderless table-sm">
                        <tbody>
                            @foreach (array_chunk($checkboxOptions, 4, true) as $chunk)
                                <tr>
                                    @foreach ($chunk as $key => $label)
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    id="checkbox_{{ $key }}" name="supplement_flags[]"
                                                    value="{{ $key }}"
                                                    {{ in_array($key, old('supplement_flags', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="checkbox_{{ $key }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>


            <div style="width: 200px; display: block; margin: auto; padding-bottom: 10px">
                <p style="color: #508bfc; text-align: center;">基本情報は更新されません</p>
            </div>
            <div class="col-6 m-auto mb-3">
                <button type="submit" class="btn btn-main-theme w-100">変更する</button>
            </div>

        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            //const bigClassSelect = document.getElementById('big_class_code');
            //const middleClassSelect = document.getElementById('middle_class_code');
            //const savedBigClassCode = bigClassSelect.dataset.savedValue;
            //const savedMiddleClassCode = middleClassSelect.dataset.savedValue;

            const bigClassSelect = document.getElementById('big_class_code');
            const middleClassSelect = document.getElementById('middle_class_code');

            const savedBigClassCode = bigClassSelect.dataset.savedValue || null;
            const savedMiddleClassCode = middleClassSelect.dataset.savedValue || null;

            /**
             * 動的選択ドロップダウン読み込み機能
             */
            async function populateSelect(url, selectElement, keyValue, keyText, savedValue = null) {
                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error(`Failed to fetch data from ${url}`);
                    const data = await response.json();

                    selectElement.innerHTML = '<option value="">選択してください</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item[keyValue];
                        option.textContent = item[keyText];
                        selectElement.appendChild(option);
                    });

                    // 保存されている値がある場合はそれを選択します。
                    if (savedValue) {
                        selectElement.value = savedValue;
                    }
                } catch (error) {
                    console.error(`Error populating ${selectElement.id}:`, error);
                }
            }

            /**
             * ページが読み込まれたときに `savedBigClassCode` が存在する場合、
             * `middle_class_code` を自動的にロードします
             */
            async function initializeMiddleClassSelection() {
                if (savedBigClassCode) {
                    await populateSelect(
                        `/get-job-types?big_class_code=${savedBigClassCode}`,
                        middleClassSelect,
                        'middle_class_code',
                        'middle_clas_name',
                        savedMiddleClassCode
                    );
                }
            }
            // **ページが読み込まれると自動的に読み込まれます**
            initializeMiddleClassSelection();

            // **big_class_code が変更されたら middle_class_code をロードする**
            
        });
        document.addEventListener('DOMContentLoaded', function() {
            const annualInput = document.getElementById('desired_salary_annual');
            const hourlyInput = document.getElementById('desired_salary_hourly');
            const annualRadio = document.getElementById('annual');
            const hourlyRadio = document.getElementById('hourly');

            function toggleSalaryFields() {
                if (annualRadio.checked) {
                    annualInput.disabled = false;
                    hourlyInput.disabled = true;
                    hourlyInput.value = '';
                } else if (hourlyRadio.checked) {
                    hourlyInput.disabled = false;
                    annualInput.disabled = true;
                    annualInput.value = '';
                }
            }

            function loadSavedSalaryValues() {
                const savedSalaryType = document.querySelector('input[name="desired_salary_type"]:checked');
                const savedAnnualSalary = annualInput.dataset.savedValue;
                const savedHourlySalary = hourlyInput.dataset.savedValue;

                if (savedSalaryType && savedSalaryType.value === '年収') {
                    annualInput.value = savedAnnualSalary || '';
                    annualRadio.checked = true;
                } else if (savedSalaryType && savedSalaryType.value === '時給') {
                    hourlyInput.value = savedHourlySalary || '';
                    hourlyRadio.checked = true;
                }
                toggleSalaryFields();
            }

            loadSavedSalaryValues();

            annualRadio.addEventListener('change', toggleSalaryFields);
            hourlyRadio.addEventListener('change', toggleSalaryFields);
        });
    </script>


@endsection
