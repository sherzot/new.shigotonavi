@extends('layouts.top')

@section('title', '基本情報変更')
@section('content')
    <div class="container py-5">
        <h2 class="text-center mb-4" style="color: #0d6efd;">基本情報と希望条件を変更</h2>

        @if (session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert　text-main-theme">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <form action="{{ route('matchings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4 row">
                {{--  <h4 class="text-start text-primary mb-4">基本情報変更</h4>  --}}
                <!-- 氏名（漢字） -->
                <div class=" col-sm-6 col-md-6 col-12">
                    <label for="name" class="form-label">氏名（漢字）<span class="text-main-theme">必須</span></label>
                    <input type="text" name="name" id="name" class="form-control border-dark"
                        value="{{ old('name', Auth::user()->name ?? '') }}" required>
                </div>
                @error('name')
                    <div class="alert　text-main-theme">{{ $message }}</div>
                @enderror

                <!-- 氏名（フリガナ） -->
                <div class="col-sm-6 col-md-6 col-12">
                    <label for="katakana_name" class="form-label">氏名（フリガナ）<span class="text-main-theme">必須</span></label>
                    <input type="text" name="name_f" id="name_f" class="form-control border-dark"
                        value="{{ old('name_f', Auth::user()->name_f ?? '') }}" required>
                </div>
                @error('name_f')
                    <div class="alert　text-main-theme">{{ $message }}</div>
                @enderror
            </div>


            <!-- 性別 -->
            <div class="mb-4">
                <label class="form-label">性別：<span class="text-main-theme">必須</span></label><br>
                <input type="radio" name="gender" value="1" {{ $person->sex == 1 ? 'checked' : '' }} required>
                男性
                <input type="radio" name="gender" value="2" {{ $person->sex == 2 ? 'checked' : '' }}> 女性
            </div>
            @error('gender')
                <div class="alert text-main-theme">{{ $message }}</div>
            @enderror
            <!-- 生年月日 -->
            <div class="mb-3">
                <label class="form-label">生年月日：<small style="color: rgba(255, 0, 0, 0.674);">(必須)
                        ※西暦
                        数字のみ入力（例：19710401）</small></label>
                <input type="text" name="birthday" class="form-control border-dark"
                    value="{{ old('birthday', isset($person) ? \Carbon\Carbon::parse($person->birthday)->format('Ymd') : '') }}"
                    maxlength="8" pattern="\d{8}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">メールアドレス：<span class="text-main-theme">必須</span></label>
                <input type="email" name="mail_address" class="form-control border-dark"
                    value="{{ old('mail_address', $person->mail_address ?? '') }}">
            </div>
            <!-- 郵便番号 -->
            {{--  <h4>住所 <span class="text-main-theme">必須</span></h4>  --}}
            <div class="mb-4 w-50">
                <label class="form-label">住所：<span class="text-main-theme">必須</span></label><br>
                <label for="postal_code" class="form-label">郵便番号：</label>
                <div class="row g-2">
                    <!-- 上3桁 -->
                    <div class="col-12 col-md-5">
                        <input type="text" name="post_u" id="post_u" class="form-control text-start border-dark"
                            value="{{ old('post_u', $person->post_u ?? '') }}" maxlength="3" pattern="\d{3}"
                            placeholder="123">
                    </div>
                    @error('post_u')
                        <div class="alert text-main-theme">{{ $message }}</div>
                    @enderror

                    <!-- ハイフン ( - ) -->
                    <div class="col-auto d-flex align-items-center justify-content-center">
                        <span class="fw-bold">-</span>
                    </div>

                    <!-- 下4桁 -->
                    <div class="col-12 col-md-5">
                        <input type="text" name="post_l" id="post_l" class="form-control text-start border-dark"
                            value="{{ old('post_l', ltrim($person->post_l ?? '', '-')) }}" maxlength="4" pattern="\d{4}"
                            placeholder="4567">
                    </div>
                    @error('post_l')
                        <div class="alert　text-main-theme">{{ $message }}</div>
                    @enderror
                </div>
            </div>


            <div class="mb-4">
                <label class="form-label">区-市</label>
                <input type="text" name="city" class="form-control border-dark"
                    value="{{ old('city', $person->city ?? '') }}">
            </div>

            @error('city')
                <div class="alert　text-main-theme">{{ $message }}</div>
            @enderror
            <div class="mb-4">
                <label class="form-label">区-市 (フリガナ)</label>
                <input type="text" name="city_f" class="form-control border-dark"
                    value="{{ old('city_f', $person->city_f ?? '') }}">
            </div>
            @error('city_f')
                <div class="alert　text-main-theme">{{ $message }}</div>
            @enderror
            <div class="mb-4">
                <label class="form-label">町</label>
                <input type="text" name="town" class="form-control border-dark"
                    value="{{ old('town', $person->town ?? '') }}">
            </div>
            @error('town')
                <div class="alert　text-main-theme">{{ $message }}</div>
            @enderror
            <div class="mb-4">
                <label class="form-label">町 (フリガナ)</label>
                <input type="text" name="town_f" class="form-control border-dark"
                    value="{{ old('town_f', $person->town_f ?? '') }}">
            </div>
            @error('town_f')
                <div class="alert　text-main-theme">{{ $message }}</div>
            @enderror
            <div class="mb-4">
                <label class="form-label">住居表示</label>
                <input type="text" name="address" class="form-control border-dark"
                    value="{{ old('address', $person->address ?? '') }}">
            </div>
            @error('address')
                <div class="alert　text-main-theme">{{ $message }}</div>
            @enderror
            <div class="mb-4">
                <label class="form-label">住居表示 (フリガナ)</label>
                <input type="text" name="address_f" class="form-control border-dark"
                    value="{{ old('address_f', $person->address_f ?? '') }}">
            </div>
            @error('address_f')
                <div class="alert text-main-theme">{{ $message }}</div>
            @enderror


            <!-- 電話番号 -->
            <div class="mb-4">
                <label for="phone_number" class="form-label">電話番号<span class="text-main-theme">(必須)</span>-
                    <span class="text-main-theme">例：0359094174</span>
                </label>
                <input type="text" name="phone_number" id="phone_number" class="form-control border-dark"
                    value="{{ old('phone_number', $person->portable_telephone_number ?? '') }}" required>
                @error('phone_number')
                    <div class="alert text-main-theme">{{ $message }}</div>
                @enderror
            </div>
            @error('phone_number')
                <div class="alert　text-main-theme">{{ $message }}</div>
            @enderror
            {{--  <h4 class="text-start text-primary mb-4">希望条件を変更</h4>  --}}
            <!-- 希望勤務地 -->
            <div class="mb-4">
                <label class="form-label">希望勤務地：</label>
                <select name="prefecture_code[]" class="form-control border-dark" multiple required>
                    @foreach ($prefectures as $prefecture)
                        <option value="{{ $prefecture->code }}"
                            {{ in_array($prefecture->code, $selectedPrefectures) ? 'selected' : '' }}>
                            {{ $prefecture->detail }}
                        </option>
                    @endforeach
                </select>
                @error('prefecture_code[]')
                    <div class="alert" style="color: rgba(255, 0, 0, 0.674);">{{ $message }}</div>
                @enderror
            </div>
            <p>職種</p>
            <div class="mb-4">
                <label for="big_class_code" class="form-label">希望職種</label>
                <select id="big_class_code" name="big_class_code" class="form-control border-dark"
                    data-saved-value="{{ $savedBigClassCode ?? '' }}">
                    <option value="">選択してください</option>
                    @foreach ($bigClasses as $bigClass)
                        <option value="{{ $bigClass->big_class_code }}"
                            {{ isset($savedBigClassCode) && $savedBigClassCode == $bigClass->big_class_code ? 'selected' : '' }}>
                            {{ $bigClass->big_class_name }}
                        </option>
                    @endforeach
                </select>
                @error('big_class_code')
                    <div class="alert　text-main-theme">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="middle_class_code" class="form-label">職種タイプ</label>
                <select id="middle_class_code" name="middle_class_code" class="form-control border-dark"
                    data-saved-value="{{ $savedMiddleClassCode ?? '' }}">
                    <option value="">選択してください</option>
                </select>
                @error('middle_class_code')
                    <div class="alert　text-main-theme">{{ $message }}</div>
                @enderror
            </div>

            <p>資格</p>
            <!-- グループ選択 -->
            <label class="form-label">資格グループ</label>
            <select name="group_code" id="group_code" class="form-control border-dark">
                <option value="">グループを選択</option>
                @foreach ($groups as $group)
                    <option value="{{ $group->group_code }}"
                        {{ $selectedGroupCode == $group->group_code ? 'selected' : '' }}>
                        {{ $group->group_name }}
                    </option>
                @endforeach
            </select>
            @error('group_code')
                <div class="alert　text-main-theme">{{ $message }}</div>
            @enderror
            <br>

            <!-- カテゴリ選択 -->
            <label class="form-label">資格カテゴリ</label>
            <select name="category_code" id="category_code" class="form-control border-dark">
                <option value="">カテゴリを選択</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->category_code }}"
                        {{ $selectedCategoryCode == $category->category_code ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
            @error('category_code')
                <div class="alert　text-main-theme">{{ $message }}</div>
            @enderror
            <br>

            <!-- 資格選択 -->
            <label class="form-label">資格名</label>
            <select name="license_code" id="license_code" class="form-control border-dark">
                <option value="">資格を選択</option>
                @foreach ($licenses as $license)
                    <option value="{{ $license->code }}" {{ $selectedLicenseCode == $license->code ? 'selected' : '' }}>
                        {{ $license->name }}
                    </option>
                @endforeach
            </select>
            @error('license_code"')
                <div class="alert　text-main-theme">{{ $message }}</div>
            @enderror
            <br>
            <!-- 希望給与 -->
            <div class="mb-4">
                <label for="desired_salary_type" class="form-label">希望給与 <span class="text-primary">（最低額を入力）</span></label>
                <div class="row">
                    <!-- 年収 (Yillik maosh) -->
                    <div class="col-12 col-md-6  align-items-center">
                        <div class="align-items-center py-1">
                            <input type="radio" id="annual" name="desired_salary_type" value="年収"
                                {{ $personHopeWorkingCondition->yearly_income_min > 0 ? 'checked' : '' }}
                                onchange="toggleSalaryFields()">
                            <label class="px-1" for="annual">年収：<span class="text-main-theme">例: 400
                                    (万円〜)</span></label>
                        </div>
                        <div class="d-flex flex-grow-1 position-relative">
                            <input type="number" name="desired_salary_annual" id="desired_salary_annual"
                                class="form-control text-start border-dark"
                                value="{{ $personHopeWorkingCondition->yearly_income_min ?? '' }}"
                                {{ isset($personHopeWorkingCondition) && $personHopeWorkingCondition->yearly_income_min > 0 ? '' : 'disabled' }}>
                            <label class="position-absolute end-0 pe-5 ml-3 align-self-center">万円〜</label>
                        </div>
                        @error('desired_salary_annual')
                            <div class="alert　text-main-theme">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- 時給 (Soatlik maosh) -->
                    <div class="col-12 col-md-6  align-items-center mt-2 mt-md-0">
                        <div class="d-flex align-items-center py-1">
                            <input type="radio" id="hourly" name="desired_salary_type" value="時給"
                                {{ $personHopeWorkingCondition->hourly_income_min > 0 ? 'checked' : '' }}
                                onchange="toggleSalaryFields()">
                            <label class="px-2" for="hourly">時給：<span class="text-main-theme">例: 1200
                                    (円〜)</span></label>
                        </div>
                        <div class="d-flex flex-grow-1 position-relative">
                            <input type="number" name="desired_salary_hourly" id="desired_salary_hourly"
                                class="form-control text-start border-dark"
                                value="{{ $personHopeWorkingCondition->hourly_income_min }}"
                                {{ $personHopeWorkingCondition->hourly_income_min > 0 ? '' : 'disabled' }}>
                            <label class="position-absolute end-0 pe-5 ml-3 align-self-center">円〜</label>
                        </div>
                        @error('desired_salary_hourly')
                            <div class="alert　text-main-theme">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row g-3 mt-3">
                <div class="col-6">
                    <button type="button" onClick="history.back()" class="btn btn-primary w-100">
                        <i class="fa-solid fa-arrow-left"></i> 戻る
                    </button>
                </div>
                <div class="col-6 justfiy_">
                    <button type="submit" class="btn btn-main-theme w-100">変更する</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // HTML要素の取得
            const bigClassSelect = document.getElementById('big_class_code');
            const middleClassSelect = document.getElementById('middle_class_code');
            const groupSelect = document.getElementById('group_code');
            const categorySelect = document.getElementById('category_code');
            const licenseSelect = document.getElementById('license_code');

            // サーバーから取得したデータを保存する
            const savedBigClassCode = bigClassSelect ? bigClassSelect.dataset.savedValue : null;
            const savedMiddleClassCode = middleClassSelect ? middleClassSelect.dataset.savedValue : null;
            const selectedGroup = "{{ $selectedGroupCode ?? '' }}";
            const selectedCategory = "{{ $selectedCategoryCode ?? '' }}";
            const selectedLicense = "{{ $selectedLicenseCode ?? '' }}";

            /**
             * 動的選択ドロップダウン読み込み機能
             * @param {string} url - リクエストURL
             * @param {HTMLElement} selectElement - 要素を選択
             * @param {string} keyValue - 選択可能な項目の値
             * @param {string} keyText - テキストを表示
             * @param {string|null} savedValue - 事前選択された値
             */
            async function populateSelect(url, selectElement, keyValue, keyText, savedValue = null) {
                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error(`Failed to fetch data from ${url}`);
                    const data = await response.json();

                    if (!Array.isArray(data)) {
                        console.error(`Invalid JSON format from ${url}:`, data);
                        return;
                    }

                    selectElement.innerHTML = '<option value="">選択してください</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item[keyValue];
                        option.textContent = item[keyText];
                        selectElement.appendChild(option);
                    });

                    if (savedValue) {
                        selectElement.value = savedValue;
                    }
                } catch (error) {
                    console.error(`Error populating ${selectElement.id}:`, error);
                }
            }

            /**
             * ミドルクラスをロード
             */
            function loadMiddleClasses(bigClassCode, savedValue = '') {
                if (bigClassCode) {
                    populateSelect(
                        `/get-job-types?big_class_code=${bigClassCode}`,
                        middleClassSelect,
                        'middle_class_code',
                        'middle_clas_name',
                        savedValue
                    );
                } else {
                    middleClassSelect.innerHTML = '<option value="">選択してください</option>';
                }
            }
            // **ページが読み込まれるときに middle_class_code を自動的に読み込む**
            if (savedBigClassCode) {
                loadMiddleClasses(savedBigClassCode, savedMiddleClassCode);
            }

            // **big_class_code が変更されたら middle_class_code をロードする**
            bigClassSelect.addEventListener('change', function() {
                loadMiddleClasses(this.value);
            });

            /**
             * カテゴリデータを読み込んでいます
             */
            function loadCategories(groupCode, selectedCategory = '', selectedLicense = '') {
                categorySelect.innerHTML = '<option value="">カテゴリを選択</option>';
                licenseSelect.innerHTML = '<option value="">資格を選択</option>';

                if (!groupCode) return;

                fetch(`/get-license-categories?group_code=${groupCode}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!Array.isArray(data.categories)) {
                            console.error("カテゴリデータが無効です:", data);
                            return;
                        }

                        data.categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.category_code;
                            option.textContent = category.category_name;
                            categorySelect.appendChild(option);
                        });

                        if (selectedCategory) {
                            categorySelect.value = selectedCategory;
                            loadLicenses(groupCode, selectedCategory, selectedLicense);
                        }
                    })
                    .catch(error => console.error("カテゴリ取得エラー:", error));
            }

            /**
             * ライセンスをアップロード
             */
            function loadLicenses(groupCode, categoryCode, savedLicense = '') {
                licenseSelect.innerHTML = '<option value="">選択してください</option>';

                if (!groupCode || !categoryCode) return;

                fetch(`/get-licenses?group_code=${groupCode}&category_code=${categoryCode}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!Array.isArray(data.licenses)) {
                            console.error("資格取得エラー:", data);
                            return;
                        }

                        data.licenses.forEach(license => {
                            const option = document.createElement('option');
                            option.value = license.code;
                            option.textContent = license.name;
                            licenseSelect.appendChild(option);
                        });

                        if (savedLicense) {
                            licenseSelect.value = savedLicense;
                        }
                    })
                    .catch(error => console.error("資格取得エラー:", error));
            }

            /**
             * イベント リスナー (ドロップダウンの変更を追跡)
             */
            if (bigClassSelect) {
                bigClassSelect.addEventListener('change', function() {
                    loadMiddleClasses(this.value);
                });
            }

            groupSelect.addEventListener('change', function() {
                loadCategories(this.value);
            });

            categorySelect.addEventListener('change', function() {
                loadLicenses(groupSelect.value, this.value);
            });

            /**
             * **ページが読み込まれるときに事前に選択されたデータを読み込む**
             */
            if (savedBigClassCode) {
                loadMiddleClasses(savedBigClassCode, savedMiddleClassCode);
            }

            if (selectedGroup) {
                groupSelect.value = selectedGroup;
                loadCategories(selectedGroup, selectedCategory, selectedLicense);
            }
        });
    </script>


@endsection
