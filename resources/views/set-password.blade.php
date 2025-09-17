@extends('layouts.top')

@section('title', '基本情報入力')
@section('content')
<section class="">
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card shadow-2-strong" style="border-radius: 1rem;">
                    <div class="card-body p-3 text-center">
                        <form id="registerForm" action="{{ route('set-password') }}" method="POST"
                            onsubmit="return validateForm()">
                            @csrf
                            <h5 style="line-height: 2">基本情報入力</h5>


                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="text-danger">{{ $error }}</div>
                                @endforeach
                            @endif
                            <input type="hidden" name="email" value="{{ request('email') }}">

                            {{--  <input type="hidden" name="email" value="{{ $email ?? '' }}">  --}}


                            <!-- Name KANJI-->
                            <div class="row">
                                <label class="col form-label text-start" for="surname">氏名（漢字） <span
                                        style="color: #ea544a;">必要</span></label>
                            </div>
                            <div class="row form-outline mb-4" data-mdb-input-init>
                                <div class="col-6">
                                    <input type="text" name="surname" id="surname"
                                        class="form-control border-primary" placeholder="姓" required>
                                </div>
                                <div class="col-6">
                                    <input type="text" name="name" id="name"
                                        class="form-control border-primary" placeholder="名" required>
                                </div>
                            </div>

                            <div id="kanjiError" class="text-danger mb-3"></div>

                            <!-- KATAKANA -->
                            <div class="row">
                                <label class="col form-label text-start" for="surname">氏名（フリガナ） <span
                                        style="color: #ea544a;">必要</span></label>
                            </div>
                            <div class="row form-outline mb-4" data-mdb-input-init>
                                <div class="col-6">
                                    <input type="text" name="katakana_surname" id="katakana_surname"
                                        class="form-control border-primary" placeholder="セイ" required>
                                </div>
                                <div class="col-6">
                                    <input type="text" name="katakana_name" id="katakana_name"
                                        class="form-control border-primary" placeholder="メイ" required>
                                </div>
                            </div>
                            <div id="katakanaError" class="text-danger mb-3"></div>

                            <!--パスワード-->
                            <div data-mdb-input-init class="form-outline mb-4" style="position: relative;">
                                <label class="form-label float-start" for="password">
                                    パスワード <span style="color: #ea544a;">必要</span>
                                </label>
                                <input type="password" name="password" id="password"
                                    class="form-control form-control-lg border-primary" />
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()" 
                                    style="position: absolute; right: 6px; top: 70%; transform: translateY(-50%);">見る</button>
                            </div>
                            <div data-mdb-input-init class="form-outline mb-4" style="position: relative;">
                                <label class="form-label float-start" for="password_confirmation">
                                    パスワードを認証する <span style="color: #ea544a;">必要</span>
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control form-control-lg border-primary" />
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()" 
                                    style="position: absolute; right: 6px; top: 70%; transform: translateY(-50%);">見る</button>
                            </div>

                            {{-- <p style="text-align: start; font-size: 12px">* 受メールの設定でドメイン制限をされている方は、<a
                                    href="https://www.shigotonavi.co.jp/">(shigotonabi.co.jp)</a> が受できるように解除してください。
                            </p> --}}

                            <div class="form-check py-3">
                                <input class="form-check-input" type="checkbox" value=""
                                    id="flexCheckChecked">
                                <label class="form-check-label" for="flexCheckChecked">
                                    <a
                                        href="https://www.shigotonavi.co.jp/privacy/privacymark.asp">しごとナビ利用規約・個人情報保護に関する事項に同意する</a>
                                </label>
                            </div>

                            <button id="submitButton" class="btn btn-red btn-lg btn-block" name="submit"
                                type="submit" disabled
                                style="background-color: rgba(255, 0, 0, 0.674); color: #fff;">
                                登録
                            </button>
                            <hr class="my-4">
                            <br>
                            <a href="/login" data-mdb-button-init data-mdb-ripple-init
                                class="btn btn-lg btn-block btn-red mb-2"
                                style="background-color: rgba(255, 0, 0, 0.674); font-size: 12px; color: #fff;">
                                ログインパスワードをお持ちの方
                            </a>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const button = event.target;
        if (passwordField.type === "password") {
            passwordField.type = "text";
            button.textContent = "隠る";
        } else {
            passwordField.type = "password";
            button.textContent = "見る";
        }
    }
</script>
@endsection


@extends('layouts.top')

@section('title', '希望条件登録')
@section('content')
    <section class="d-flex align-items-center justify-content-center py-0 my-0">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-12">
                    <div class="card-body">
                        <form id="registerForm" action="{{ route('register') }}" method="POST">
                            @csrf
                            <h3 class="text-center mb-4 mt-0 pt-0">希望条件登録</h3>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- 希望職種 -->
                            <div class="mb-3">
                                <label for="big_class_code" class="form-label">希望職種
                                    <span class="text-main-theme">必要</span>
                                </label>
                                <select name="big_class_code" id="big_class_code" class="form-control border-primary">
                                    <option value="">選択してください</option>
                                    @foreach ($bigClasses as $bigClass)
                                        <option value="{{ $bigClass->big_class_code }}">{{ $bigClass->big_class_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('big_class_code')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 職種タイプ -->
                            <div class="mb-3">
                                <label for="middle_class_code" class="form-label">職種タイプ
                                    <span class="text-main-theme">必要</span>
                                </label>
                                <select name="job_category" id="middle_class_code" class="form-control border-primary">
                                </select>
                                @error('job_category')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- 希望勤務地 -->
                            <div class="mb-3">
                                <label for="prefecture_code" class="form-label">希望勤務地
                                    <span class="text-main-theme">必要</span>
                                </label>
                                <select name="prefecture_code[]" id="prefecture_code" class="form-control border-primary">
                                    <option value="" selected>選択してください</option>
                                    <!-- 地域 -->
                                    @if (isset($regionGroups))
                                        @foreach ($regionGroups as $region)
                                            <optgroup label="{{ $region['detail'] }}">
                                                @foreach ($region['prefectures'] as $prefecture)
                                                    <option value="{{ $prefecture['code'] }}">
                                                        {{ $prefecture['detail'] }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    @endif

                                    <!-- 個別 -->
                                    @if (isset($individualPrefectures) && is_array($individualPrefectures))
                                        @foreach ($individualPrefectures as $prefecture)
                                            <option value="{{ $prefecture['code'] }}">
                                                {{ $prefecture['detail'] }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('prefecture_code[]')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 資格 -->
                            <div class="mb-3">
                                <label for="group_code" class="form-label">資格グループ</label>
                                <select name="group_code" id="group_code" class="form-control border-primary">
                                    <option value="">選択してください</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->group_code }}">{{ $group->group_name }}</option>
                                    @endforeach
                                </select>
                                @error('prefecture_code')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="category_code" class="form-label">資格カテゴリ</label>
                                <select name="category_code" id="category_code" class="form-control border-primary">
                                    <option value="" disabled selected>選択してください</option>
                                </select>
                                @error('category_code')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="license_code" class="form-label">資格</label>
                                <select name="license_code" id="license_code" class="form-control border-primary">
                                    <option value="" disabled selected>選択してください</option>
                                </select>
                                @error('license_code')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 希望給与 -->
                            <div class="mb-4">
                                <label for="desired_salary_type" class="form-label">希望給与：<span
                                        class="text-main-theme">必要</span></label><br>
                                <input type="radio" id="annual" name="desired_salary_type" value="年収"
                                    class=" border-primary" required onchange="toggleSalaryFields()">
                                <label for="annual">年収：<span class="text-main-theme">例: 400
                                        (万円)</span></label>
                                <input type="number" name="desired_salary_annual" id="desired_salary_annual"
                                    class="form-control mt-2" placeholder="" value="{{ old('desired_salary_annual') }}"
                                    disabled>

                                @error('desired_salary_annual')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                                <input type="radio" id="hourly" name="desired_salary_type" value="時給"
                                    class="border-primary" onchange="toggleSalaryFields()">
                                <label for="hourly">時給：<span class="text-main-theme">例: 1200
                                        (円)</span></label>
                                <input type="number" name="desired_salary_hourly" id="desired_salary_hourly"
                                    class="form-control mt-2" placeholder="" value="{{ old('desired_salary_hourly') }}"
                                    disabled>

                                @error('desired_salary_annual')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="d-grid">
                                <button class="btn btn-main-theme btn-lg" type="submit">登録</button>
                            </div>

                        </form>
                        <hr>

                        <div class="mb-3 text-center">
                            <a href="/login" data-mdb-button-init="" data-mdb-ripple-init=""
                                class="text-center btn-lg btn-block text-decoration-none fs-f14 mb-2 ">
                                ログインパスワードをお持ちの方
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('js/signin.js') }}"></script>

@endsection

