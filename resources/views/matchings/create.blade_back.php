@extends('layouts.top')

@section('title', 'マスターページ')
@section('content')
    <section class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8 col-sm-12">
                    <div class="">
                        <div class="card-body">
                            <h3 class="text-center mb-4">基本情報登録</h3>
                            <form action="{{ route('matchings.store') }}" method="POST">
                                @csrf
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif


                                <!-- 性別 -->
                                <div class="mb-3">
                                    <label for="gender" class="form-label">性別：<span
                                            style="color: rgba(255, 0, 0, 0.674);">必要</span></label><br>
                                    <input type="radio" id="male" name="gender" value="1" required>
                                    <label for="male">男性　</label>
                                    <input type="radio" id="female" name="gender" value="2">
                                    <label for="female">女性</label>
                                    @error('gender')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                

                                <!-- 生年月日 -->
                                <div class="mb-3">
                                    <label class="form-label">生年月日：<small style="color: rgba(255, 0, 0, 0.674);">(必要)
                                            ※西暦
                                            数字のみ入力（例：19710401）</small></label>
                                    <input type="text" name="birthday" class="form-control border-dark"
                                        value="{{ old('birthday', isset($person) ? \Carbon\Carbon::parse($person->birthday)->format('Ymd') : '') }}"
                                        maxlength="8" pattern="\d{8}" required>
                                </div>

                                <!-- 郵便番号 -->
                                <label for="postal_code" class="form-label">郵便番号：<span
                                        style="color: rgba(255, 0, 0, 0.674);">必要</span></label>
                                <div class="row g-2 mb-4 mx-0 px-0 w-75 w-md-100">
                                    <!-- 上3桁 -->
                                    <div class="col-12 col-md-5">
                                        <input type="text" name="post_u" id="post_u"
                                            class="form-control text-start border-dark"
                                            value="{{ old('post_u', isset($person) ? $person->post_u : '') }}"
                                            maxlength="3" pattern="\d{3}">
                                    </div>

                                    <!-- ハイフン ( - ) -->
                                    <div class="col-auto d-flex align-items-center justify-content-center">
                                        <span class="fw-bold">-</span>
                                    </div>

                                    <!-- 下4桁 -->
                                    <div class="col-12 col-md-5">
                                        <input type="text" name="post_l" id="post_l"
                                            name="post_l" id="post_l" class="form-control text-start border-dark"
                                            value="{{ old('post_l', isset($person) ? ltrim($person->post_l ?? '', '-') : '') }}"
                                            maxlength="4" pattern="\d{4}">
                                    </div>
                                </div>
                                {{--  <div class="mb-4">
                                
                            </div>  --}}
                                <!-- 区-市 町 住所 -->
                                <p>住所</p>
                                <div class="mb-4">
                                    <label class="form-label">区-市</label>
                                    <input type="text" name="city" class="form-control border-dark"
                                        value="{{ old('city', Auth::user()->city ?? '') }}">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">区-市 (フリガナ)</label>
                                    <input type="text" name="city_f" class="form-control border-dark"
                                        value="{{ old('city_f', Auth::user()->city_f ?? '') }}">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">町</label>
                                    <input type="text" name="town" class="form-control border-dark"
                                        value="{{ old('town', Auth::user()->town ?? '') }}">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">町 (フリガナ)</label>
                                    <input type="text" name="town_f" class="form-control border-dark"
                                        value="{{ old('town_f', Auth::user()->town_f ?? '') }}">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">住居表示</label>
                                    <input type="text" name="address" class="form-control border-dark"
                                        value="{{ old('address', Auth::user()->address ?? '') }}">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">住居表示 (フリガナ)</label>
                                    <input type="text" name="address_f" class="form-control border-dark"
                                        value="{{ old('address_f', Auth::user()->address_f ?? '') }}">
                                </div>

                                <!-- 氏名（漢字） -->
                                <div class="mb-4">
                                    <label for="name" class="form-label">氏名（漢字）</label>
                                    <input type="text" name="name" id="name" class="form-control border-dark"
                                        value="{{ old('name', Auth::user()->name ?? '') }}" required>
                                </div>
                                <!-- 氏名（フリガナ） -->
                                <div class="mb-4">
                                    <label for="katakana_name" class="form-label">氏名（フリガナ）</label>
                                    <input type="text" name="name_f" id="name_f" class="form-control border-dark"
                                        value="{{ old('name_f', Auth::user()->name_f ?? '') }}" required>
                                </div>
                                {{--  <div class="mb-4 row mx-0 px-0">



                            </div>  --}}
                                <!-- 電話番号 -->
                                <div class="mb-4">
                                    <label for="phone_number" class="form-label">電話番号：<span
                                            style="color: rgba(255, 0, 0, 0.674);">必要 ( 例：0359094174
                                            )</span></label>
                                    <input type="text" name="phone_number" id="phone_number"
                                        class="form-control border-dark"
                                        value="{{ old('phone_number', Auth::user()->phone_number ?? '') }}"
                                        placeholder="※ハイフン「-」なし 数字のみ入力" required>
                                    @error('phone_number')
                                        <div class="alert alert-danger ">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 希望職種 -->
                                <div class="mb-4">
                                    <label for="big_class_code" class="form-label">希望職種：<span
                                            style="color: rgba(255, 0, 0, 0.674);">必要</span></label>
                                    <select name="big_class_code" id="big_class_code" class="form-control border-dark"
                                        required>
                                        <option class="options" value="">選択してください</option>
                                        @foreach ($bigClasses as $bigClass)
                                            <option value="{{ $bigClass->big_class_code }}"
                                                {{ old('big_class_code') == $bigClass->big_class_code ? 'selected' : '' }}>
                                                {{ $bigClass->big_class_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('big_class_code')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 職種タイプ -->
                                <div class="mb-4">
                                    <label for="middle_class_code" class="form-label">職種タイプ：<span
                                            style="color: rgba(255, 0, 0, 0.674);">必要</span></label>
                                    <select name="job_category" id="middle_class_code" class="form-control border-dark"
                                        required>
                                        <option class="options" value="" disabled selected>選択してください</option>
                                    </select>
                                    @error('job_category')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- License Selection -->
                                <div class="mb-4">
                                    <label for="group_code" class="form-label" style="color">資格グループ選択</label>
                                    <select name="group_code" id="group_code" class="form-control border-dark">
                                        <option class="options" value="">選択してください</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->group_code }}">{{ $group->group_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="category_code" class="form-label">資格カテゴリ選択</label>
                                    <select name="category_code" id="category_code" class="form-control border-dark">
                                        <option class="options" value="" disabled selected>選択してください</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="license_code" class="form-label">資格</label>
                                    <select name="license_code" id="license_code" class="form-control border-dark">
                                        <option class="options" value="" disabled selected>選択してください</option>
                                    </select>
                                </div>


                                <!-- 希望勤務地 -->
                                <div class="mb-4">
                                    <label for="prefecture_code" class="form-label">希望勤務地(都道府県)：<span
                                            style="color: rgba(255, 0, 0, 0.674);">必要</span></label>
                                    <select name="prefecture_code[]" id="prefecture_code"
                                        class="form-control border-dark" multiple required>
                                        <!-- 地域 -->
                                        @if (isset($regionGroups))
                                            @foreach ($regionGroups as $region)
                                                <optgroup label="{{ $region['detail'] }}">
                                                    @foreach ($region['prefectures'] as $prefecture)
                                                        <option value="{{ $prefecture['code'] }}">
                                                            {{ $prefecture['detail'] }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        @endif

                                        <!-- 個別 -->
                                        @if (isset($individualPrefectures) && is_array($individualPrefectures))
                                            <optgroup label="個別 (各都道府県)">
                                                @foreach ($individualPrefectures as $prefecture)
                                                    <option value="{{ $prefecture['code'] }}">
                                                        {{ $prefecture['detail'] }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                    @error('prefecture_code[]')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>




                                <!-- 希望給与 -->
                                <div class="mb-4">
                                    <label for="desired_salary_type" class="form-label">希望給与タイプ：<span
                                            style="color: rgba(255, 0, 0, 0.674);">必要</span></label><br>
                                    <input type="radio" id="annual" name="desired_salary_type" value="年収"
                                        required onchange="toggleSalaryFields()">
                                    <label for="annual">年収：<span style="color: rgba(255, 0, 0, 0.674);">例: 400
                                            (万円)</span></label>
                                    <input type="number" name="desired_salary_annual" id="desired_salary_annual"
                                        class="form-control mt-2 border-dark" placeholder=""
                                        value="{{ old('desired_salary_annual') }}" disabled>

                                    @error('desired_salary_annual')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <input type="radio" id="hourly" name="desired_salary_type" value="時給"
                                        onchange="toggleSalaryFields()">
                                    <label for="hourly">時給：<span style="color: rgba(255, 0, 0, 0.674);">例: 1200
                                            (円)</span></label>
                                    <input type="number" name="desired_salary_hourly" id="desired_salary_hourly"
                                        class="form-control mt-2 border-dark" placeholder=""
                                        value="{{ old('desired_salary_hourly') }}" disabled>

                                    @error('desired_salary_annual')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
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
                                                                <div class="form-check" style="font-size: 12px;">
                                                                    <input class="form-check-input border-dark"
                                                                        type="checkbox" id="checkbox_{{ $key }}"
                                                                        name="supplement_flags[]"
                                                                        value="{{ $key }}"
                                                                        {{ in_array($key, old('supplement_flags', [])) ? 'checked' : '' }}>
                                                                    <label class="form-check-label"
                                                                        for="checkbox_{{ $key }}">
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


                                <!-- 同意 -->

                                {{--  <div class="form-check mb-3"
                                    style="width: 100%: display: block; margin: auto; text-align: center;">
                                    <div>
                                        <span style="color: rgba(255, 0, 0, 0.674);">しごとナビ利用規約に同意して下さい</span>
                                    </div>
                                    <input type="checkbox" id="flexCheckChecked" class="form-check-input border-dark">
                                    <label for="flexCheckChecked" class="form-check-label">
                                        <a href="https://www.shigotonavi.co.jp/privacy/privacymark.asp"
                                            style="font-size: 12px;">しごとナビ利用規約・個人情報保護に関する事項に同意する</a>
                                    </label>
                                </div>  --}}

                                <hr>

                                <!-- 登録ボタン -->
                                <button class="btn btn-red btn-lg btn-block" name="submit"
                                    type="submit"
                                    style="background-color: rgba(255, 0, 0, 0.674); color: #fff; width: 150px; display: block; margin: auto;">
                                    登録
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <script src="{{ asset('js/edit.js') }}"></script>
@endsection
