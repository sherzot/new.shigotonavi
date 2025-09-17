<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
        <title>マッチングに登録</title>
        {{--  <link rel="stylesheet" href="{{ asset('style/responsive.css') }}">  --}}
        <link rel="stylesheet" href="{{ asset('style/register.css') }}">
        <script src="{{ asset('js/form.js') }}"></script>
        <link href="{{ asset('img/icon.png') }}" rel="icon" type="image/svg+xml">
        <script src="{{ asset('js/humburger.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        {{--  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>  --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
            integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
    </head>

    <body>
        <section>
            <div class="container py-5 h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-12 col-md-8 col-lg-6 col-xl-5 w-md-75 w-sm-100">
                        <div class="card shadow-2-strong" style="border-radius: 1rem;">
                            <div class="card-body p-4">
                                <header>
                                    <h3 class="mb-3 mt-2">
                                        <a href="/">
                                            <img src="{{ asset('img/logo02.png') }}" alt="logo"
                                                class="w-sm-25 w-50">
                                        </a>
                                    </h3>
                                    <button class="hamburger">
                                        <div>
                                            <p>&#9776;</p>
                                        </div>
                                    </button>
                                    <button class="cross"><i class="fa-solid fa-xmark"></i></button>
                                    <div class="menu">
                                        <ul>
                                            <a href="{{ route('profile.profile') }}">
                                                <li><i class="fa-solid fa-user-tie"></i>基本情報</li>
                                            </a>
                                            <a href="{{ route('matchings.edit') }}">
                                                <li><i class="fa-solid fa-file-pen"></i>基本情報変更</li>
                                            </a>
                                            <a href="#">
                                                <li>
                                                    <form id="logout-form" action="{{ route('logout') }}"
                                                        method="POST">
                                                        @csrf
                                                        <a href="{{ route('logout') }}"
                                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                <li><i class="fa-solid fa-right-from-bracket"></i> ログアウト</li>
                                            </a>
                                            </form>
                                            </li>
                                            </a>
                                        </ul>
                                    </div>
                                </header>
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
                                    <div class="mb-4">
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
                                    <div class="mb-4">
                                        <label class="form-label">生年月日：<small
                                                style="color: rgba(255, 0, 0, 0.674);">(必要)
                                                ※西暦
                                                数字のみ入力（例：19710401）</small></label>
                                        <input type="text" name="birthday" class="form-control"
                                            value="{{ old('birthday', isset($person) ? \Carbon\Carbon::parse($person->birthday)->format('Ymd') : '') }}"
                                            maxlength="8" pattern="\d{8}" required>
                                    </div>

                                    <!-- 郵便番号 -->
                                    <div class="mb-4 w-50">
                                        <label for="postal_code" class="form-label">郵便番号：<span
                                                style="color: rgba(255, 0, 0, 0.674);">必要</span></label>
                                        <div class="row g-2">
                                            <!-- 上3桁 -->
                                            <div class="col-12 col-md-5">
                                                <input type="text" name="post_u" id="post_u"
                                                    class="form-control text-start"
                                                    value="{{ old('post_u', isset($person) ? $person->post_u : '') }}"
                                                    maxlength="3" pattern="\d{3}">
                                            </div>

                                            <!-- ハイフン ( - ) -->
                                            <div class="col-auto d-flex align-items-center justify-content-center">
                                                <span class="fw-bold">-</span>
                                            </div>

                                            <!-- 下4桁 -->
                                            <div class="col-12 col-md-5">
                                                <input type="text" name="post_l" id="post_l" <input
                                                    type="text" name="post_l" id="post_l"
                                                    class="form-control text-start"
                                                    value="{{ old('post_l', isset($person) ? ltrim($person->post_l ?? '', '-') : '') }}"
                                                    maxlength="4" pattern="\d{4}">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 区-市 町 住所 -->
                                    <p>住所</p>
                                    <div class="mb-4">
                                        <label class="form-label">区-市</label>
                                        <input type="text" name="city" class="form-control"
                                            value="{{ old('city', Auth::user()->city ?? '') }}">
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">区-市 (フリガナ)</label>
                                        <input type="text" name="city_f" class="form-control"
                                            value="{{ old('city_f', Auth::user()->city_f ?? '') }}">
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">町</label>
                                        <input type="text" name="town" class="form-control"
                                            value="{{ old('town', Auth::user()->town ?? '') }}">
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">町 (フリガナ)</label>
                                        <input type="text" name="town_f" class="form-control"
                                            value="{{ old('town_f', Auth::user()->town_f ?? '') }}">
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">住居表示</label>
                                        <input type="text" name="address" class="form-control"
                                            value="{{ old('address', Auth::user()->address ?? '') }}">
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">住居表示 (フリガナ)</label>
                                        <input type="text" name="address_f" class="form-control"
                                            value="{{ old('address_f', Auth::user()->address_f ?? '') }}">
                                    </div>


                                    <div class="mb-4 row mx-0">
                                        <!-- 氏名（漢字） -->
                                        <div class="container-fluid col-sm-6 col-md-6 col-12">
                                            <label for="name" class="form-label">氏名（漢字）</label>
                                            <input type="text" name="name" id="name" class="form-control"
                                                value="{{ old('name', Auth::user()->name ?? '') }}" required>
                                        </div>

                                        <!-- 氏名（フリガナ） -->
                                        <div class="container-fluid col-sm-6 col-md-6 col-12">
                                            <label for="katakana_name" class="form-label">氏名（フリガナ）</label>
                                            <input type="text" name="name_f" id="name_f" class="form-control"
                                                value="{{ old('name_f', Auth::user()->name_f ?? '') }}" required>
                                        </div>
                                    </div>
                                    <!-- 電話番号 -->
                                    <div class="mb-4">
                                        <label for="phone_number" class="form-label">電話番号：<span
                                                style="color: rgba(255, 0, 0, 0.674);">必要 ( 例：0359094174
                                                )</span></label>
                                        <input type="text" name="phone_number" id="phone_number"
                                            class="form-control"
                                            value="{{ old('phone_number', Auth::user()->phone_number ?? '') }}"
                                            placeholder="※ハイフン「-」なし 数字のみ入力" required>
                                        @error('phone_number')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- 希望職種 -->
                                    <div class="mb-4">
                                        <label for="big_class_code" class="form-label">希望職種：<span
                                                style="color: rgba(255, 0, 0, 0.674);">必要</span></label>
                                        <select name="big_class_code" id="big_class_code" class="form-control"
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
                                        <select name="job_category" id="middle_class_code" class="form-control"
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
                                        <select name="group_code" id="group_code" class="form-control">
                                            <option class="options" value="">選択してください</option>
                                            @foreach ($groups as $group)
                                                <option value="{{ $group->group_code }}">{{ $group->group_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label for="category_code" class="form-label">資格カテゴリ選択</label>
                                        <select name="category_code" id="category_code" class="form-control">
                                            <option class="options" value="" disabled selected>選択してください</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label for="license_code" class="form-label">資格</label>
                                        <select name="license_code" id="license_code" class="form-control">
                                            <option class="options" value="" disabled selected>選択してください</option>
                                        </select>
                                    </div>


                                    <!-- 希望勤務地 -->
                                    <div class="mb-4">
                                        <label for="prefecture_code" class="form-label">希望勤務地(都道府県)：<span
                                                style="color: rgba(255, 0, 0, 0.674);">必要</span></label>
                                        <select name="prefecture_code[]" id="prefecture_code" class="form-control"
                                            multiple required>
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
                                        <input type="radio" id="annual" name="desired_salary_type"
                                            value="年収" required onchange="toggleSalaryFields()">
                                        <label for="annual">年収：<span style="color: rgba(255, 0, 0, 0.674);">例: 400
                                                (万円)</span></label>
                                        <input type="number" name="desired_salary_annual" id="desired_salary_annual"
                                            class="form-control mt-2" placeholder=""
                                            value="{{ old('desired_salary_annual') }}" disabled>

                                        @error('desired_salary_annual')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                        <input type="radio" id="hourly" name="desired_salary_type"
                                            value="時給" onchange="toggleSalaryFields()">
                                        <label for="hourly">時給：<span style="color: rgba(255, 0, 0, 0.674);">例: 1200
                                                (円)</span></label>
                                        <input type="number" name="desired_salary_hourly" id="desired_salary_hourly"
                                            class="form-control mt-2" placeholder=""
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
                                                                        <input class="form-check-input"
                                                                            type="checkbox"
                                                                            id="checkbox_{{ $key }}"
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

                                    <div class="form-check mb-3"
                                        style="width: 100%: display: block; margin: auto; text-align: center;">
                                        <div>
                                            <span style="color: rgba(255, 0, 0, 0.674);">しごとナビ利用規約に同意して下さい</span>
                                        </div>
                                        <input type="checkbox" id="flexCheckChecked" class="form-check-input">
                                        <label for="flexCheckChecked" class="form-check-label">
                                            <a href="https://www.shigotonavi.co.jp/privacy/privacymark.asp"
                                                style="font-size: 12px;">しごとナビ利用規約・個人情報保護に関する事項に同意する</a>
                                        </label>
                                    </div>



                                    <!-- 登録ボタン -->
                                    <button id="submitButton" class="btn btn-red btn-lg btn-block" name="submit"
                                        type="submit" disabled
                                        style="background-color: rgba(255, 0, 0, 0.674); color: #fff; width: 120px; display: block; margin: auto;">
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



    </body>

</html>
