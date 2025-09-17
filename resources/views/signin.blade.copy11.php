@extends('layouts.top')

@section('title', '会員登録')
@section('content')

<section class="d-flex align-items-center justify-content-center py-0 my-0">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12">
                <img src="{{ asset('img/toptop2.png') }}" class="img-fluid mt-0 d-none d-sm-block" alt="Hero Image">
                <img src="{{ asset('img/toptop-sm2.png') }}" class="img-fluid mt-0 d-block d-sm-none" alt="Hero Image">
            </div>
            <div class="container col-lg-8 col-md-12">
                <form id="registerForm" action="{{ route('registration') }}" method="POST">
                    @csrf
                    <h3 class="text-center mb-4 mt-0 pt-3">マイページ取得</h3>
                    <h5 class="text-main-theme">マイページでできること：</h5>
                    <ul>
                        <li>学歴・職歴・自己PRなどを入力し、履歴書または職務経歴書を作成</li>
                        <li><span class="text-main-theme">おしごと探し</span>(自分の希望だけでオファー ）</li>
                        <li>応募記録 (応募先、結果を日付順に記録）</li>
                    </ul>
                    <br>
                    <h5 class="text-center mb-4 mt-0 pt-0 text-main-theme">基本情報登録</h5>

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="mb-2">
                        <label for="name" class="form-label">お名前（漢字）
                            <span class="text-main-theme">必須</span>
                        </label>
                        <input type="text" name="name" id="name" class="form-control border-primary py-1" value="{{ old('name') }}" required>
                        @error('name')
                        <div class="alert text-main-theme">{{ $message }}</div>
                        @enderror
                    </div>


                    {{-- <!-- 生年月日 -->  --}}
                    <div class="mb-2">
                        <label class="form-label">生年月日 <small class="text-main-theme">必須
                                (例：19710401)</small></label>
                        <input type="text" name="birthday" class="form-control border-primary py-1" maxlength="8" pattern="\d{8}" value="{{ old('birthday') }}" required>
                        @error('birthday')
                        <div class="alert text-main-theme">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- <!-- ☎️電話番号 -->  --}}
                    <div class="mb-2">
                        <label class="form-label">電話番号 <small class="text-main-theme">必須
                                (例：07090908080)</small></label>
                        <input type="text" name="portable_telephone_number" class="form-control border-primary py-1" value="{{ old('portable_telephone_number') }}" required>
                        @error('portable_telephone_number')
                        <div class="alert text-main-theme">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">郵便番号 <span class="text-main-theme">必須</span></label>
                        <div class="d-flex">
                            <input type="text" name="post_u" id="post_u" class="form-control border-primary py-1" value="{{ old('post_u') }}" maxlength="3" required style="width: 20%;">
                            <span class="mx-2">-</span>
                            <input type="text" name="post_l" id="post_l" class="form-control border-primary py-1" value="{{ old('post_l') }}" maxlength="4" required style="width: 25%;">
                            <button type="button" class="btn btn-primary ms-2" id="searchZipcode">検索</button>
                        </div>
                        @error('post_u')
                        <div class="alert text-main-theme">{{ $message }}</div>
                        @enderror
                        @error('post_l')
                        <div class="alert text-main-theme">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label for="full_address" class="form-label">住所</label>
                        <input type="text" id="full_address" name="full_address" class="form-control border-primary py-1" value="{{ old('full_address') }}">
                    </div>
                    <!-- メールアドレス -->
                    <div class="mb-2">
                        <label for="email" class="form-label">メールアドレス
                            <span class="text-main-theme">必須</span>
                        </label>
                        <input type="email" name="mail_address" id="email" class="form-control border-primary py-1" value="{{ old('mail_address') }}" required>
                        @error('email')
                        <div class="alert text-main-theme">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- パスワード -->
                    <div class="mb-2 position-relative">
                        <label class="form-label float-start" for="password">
                            パスワード <span class="text-main-theme">必須</span>
                        </label>
                        <input type="password" name="password" id="password" class="form-control border-primary py-1" />
                        <button type="button" class="btn toggle-password" data-target="password" style="position: absolute; right: 6px; top: 70%; transform: translateY(-50%);">
                            <i class="fa fa-eye"></i>
                        </button>
                        @error('password')
                        <div class="alert text-main-theme">{{ $message }}</div>
                        @enderror
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            document.querySelectorAll(".toggle-password").forEach(button => {
                                button.addEventListener("click", function() {
                                    const targetId = this.getAttribute("data-target");
                                    const inputField = document.getElementById(targetId);
                                    const icon = this.querySelector("i");

                                    inputField.type = inputField.type === "password" ? "text" : "password";
                                    icon.classList.toggle("fa-eye");
                                    icon.classList.toggle("fa-eye-slash");
                                });
                            });
                        });

                    </script>
                    <div class="form-check py-3">
                        <input class="form-check-input border-dark" type="checkbox" value="" id="flexCheckChecked">
                        <label class="form-check-label fs-f12" for="flexCheckChecked">
                            <a href="https://www.shigotonavi.co.jp/privacy/privacymark.asp">しごとナビ利用規約・個人情報保護に関する事項</a>に同意する
                        </label>
                    </div>
                    <div class="d-grid">
                        <button id="submitButton" class="btn btn-main-theme btn-lg" disabled onchange="submitForm()">登録</button>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // チェックボックスを取得する
                            const checkbox = document.getElementById('flexCheckChecked');
                            const submitButton = document.getElementById('submitButton');

                            checkbox.addEventListener('change', function() {
                                submitButton.disabled = !checkbox.checked;
                            });

                        });

                    </script>
                    <hr>
                    <div class="my-3 text-center">
                        <a href="/login" data-mdb-button-init="" data-mdb-ripple-init="" class="text-center btn-lg btn-block text-decoration-none fs-f14 mb-2 ">
                            ログインパスワードをお持ちの方
                        </a>
                    </div>
                    <hr>
                </form>
            </div>
        </div>
    </div>
</section>
<!-- Registration 9 - Bootstrap Brain Component -->
<section class="m-0 p-0">
    <div class="container bg-primary py-3 py-md-5 py-xl-8">
        <div class="row gy-4 align-items-center">
            <div class="col-12 col-md-6 col-xl-6">
                <div class="d-flex justify-content-center text-bg-primary">
                    <div class="col-12 col-xl-9">
                        {{--  <img class="img-fluid rounded mb-4" loading="lazy" src="./assets/img/bsb-logo-light.svg" width="245" height="80" alt="BootstrapBrain Logo">  --}}
                        <hr class="border-primary-subtle mb-4">
                        {{--  <h2 class="h1 mb-4">We make digital products that drive you to stand out.</h2>  --}}
                        <h2 class="h1 mb-4">マイページ取得</h2>
                        {{--  <p class="lead mb-5">We write words, take photos, make videos, and interact with artificial intelligence.</p>  --}}
                        <div class="text-endx">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-grip-horizontal" viewBox="0 0 16 16">
                                <path d="M2 8a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                            </svg>
                        </div>
                        <div class="card shadow-sm rounded-4 border-0 p-4 mb-4">
                            {{--  <h3 class="text-center fw-bold text-dark mb-3">マイページ取得</h3>  --}}
                            
                            <h5 class="text-main-theme fw-bold mb-3">
                                <i class="bi bi-stars me-2"></i>マイページでできること：
                            </h5>
                        
                            <ul class="list-unstyled ps-3">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    学歴・職歴・自己PRなどを入力し、履歴書または職務経歴書を作成
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-search-heart-fill text-danger me-2"></i>
                                    <span class="text-main-theme fw-bold">おしごと探し</span>（自分の希望を登録し、求人を選んで、オファーボタンを押してエジンートにお知らせ。）
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-pencil-square text-primary me-2"></i>
                                    応募記録（応募先、結果を日付順に記録）
                                </li>
                            </ul>
                        </div>
                        
                        {{--  <h3 class="text-center mb-4 mt-0 pt-3">マイページ取得</h3>
                            <h5 class="text-main-theme">マイページでできること：</h5>
                            <ul>
                                <li>学歴・職歴・自己PRなどを入力し、履歴書または職務経歴書を作成</li>
                                <li><span class="text-main-theme">おしごと探し</span>(自分の希望だけでオファー ）</li>
                                <li>応募記録 (応募先、結果を日付順に記録）</li>
                            </ul>
                            <br>  --}}
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-6">
                <div class="card border-0 rounded-1">
                    <div class="card-body p-3 p-md-4 p-xl-5">
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-4">
                                    <h2 class="h3">基本情報登録</h2>
                                    {{--  <h3 class="fs-6 fw-normal text-secondary m-0">Enter your details to register</h3>  --}}
                                </div>
                            </div>
                        </div>
                        <form id="registerForm" action="{{ route('registration') }}" method="POST">
                            @csrf
                            
                            {{--  <h5 class="text-center mb-4 mt-0 pt-0 text-main-theme">基本情報登録</h5>  --}}
    
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            <div class="mb-2">
                                <label for="name" class="form-label">お名前（漢字）
                                    <span class="text-main-theme">必須</span>
                                </label>
                                <input type="text" name="name" id="name" class="form-control border-primary py-1" value="{{ old('name') }}" required>
                                @error('name')
                                <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>
    
    
                            {{-- <!-- 生年月日 -->  --}}
                            <div class="mb-2">
                                <label class="form-label">生年月日 <small class="text-main-theme">必須
                                        (例：19710401)</small></label>
                                <input type="text" name="birthday" class="form-control border-primary py-1" maxlength="8" pattern="\d{8}" value="{{ old('birthday') }}" required>
                                @error('birthday')
                                <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- <!-- ☎️電話番号 -->  --}}
                            <div class="mb-2">
                                <label class="form-label">電話番号 <small class="text-main-theme">必須
                                        (例：07090908080)</small></label>
                                <input type="text" name="portable_telephone_number" class="form-control border-primary py-1" value="{{ old('portable_telephone_number') }}" required>
                                @error('portable_telephone_number')
                                <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label class="form-label">郵便番号 <span class="text-main-theme">必須</span></label>
                                <div class="d-flex">
                                    <input type="text" name="post_u" id="post_u" class="form-control border-primary py-1" value="{{ old('post_u') }}" maxlength="3" required style="width: 20%;">
                                    <span class="mx-2">-</span>
                                    <input type="text" name="post_l" id="post_l" class="form-control border-primary py-1" value="{{ old('post_l') }}" maxlength="4" required style="width: 25%;">
                                    <button type="button" class="btn btn-primary ms-2" id="searchZipcode">検索</button>
                                </div>
                                @error('post_u')
                                <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                                @error('post_l')
                                <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>
    
                            <div class="mb-2">
                                <label for="full_address" class="form-label">住所</label>
                                <input type="text" id="full_address" name="full_address" class="form-control border-primary py-1" value="{{ old('full_address') }}">
                            </div>
                            <!-- メールアドレス -->
                            <div class="mb-2">
                                <label for="email" class="form-label">メールアドレス
                                    <span class="text-main-theme">必須</span>
                                </label>
                                <input type="email" name="mail_address" id="email" class="form-control border-primary py-1" value="{{ old('mail_address') }}" required>
                                @error('email')
                                <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>
    
                            <!-- パスワード -->
                            <div class="mb-2 position-relative">
                                <label class="form-label float-start" for="password">
                                    パスワード <span class="text-main-theme">必須</span>
                                </label>
                                <input type="password" name="password" id="password" class="form-control border-primary py-1" />
                                <button type="button" class="btn toggle-password" data-target="password" style="position: absolute; right: 6px; top: 70%; transform: translateY(-50%);">
                                    <i class="fa fa-eye"></i>
                                </button>
                                @error('password')
                                <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>
    
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    document.querySelectorAll(".toggle-password").forEach(button => {
                                        button.addEventListener("click", function() {
                                            const targetId = this.getAttribute("data-target");
                                            const inputField = document.getElementById(targetId);
                                            const icon = this.querySelector("i");
    
                                            inputField.type = inputField.type === "password" ? "text" : "password";
                                            icon.classList.toggle("fa-eye");
                                            icon.classList.toggle("fa-eye-slash");
                                        });
                                    });
                                });
    
                            </script>
                            <div class="form-check py-3">
                                <input class="form-check-input border-dark" type="checkbox" value="" id="flexCheckChecked">
                                <label class="form-check-label fs-f12" for="flexCheckChecked">
                                    <a href="https://www.shigotonavi.co.jp/privacy/privacymark.asp">しごとナビ利用規約・個人情報保護に関する事項</a>に同意する
                                </label>
                            </div>
                            <div class="d-grid">
                                <button id="submitButton" class="btn btn-main-theme btn-lg" disabled onchange="submitForm()">登録</button>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // チェックボックスを取得する
                                    const checkbox = document.getElementById('flexCheckChecked');
                                    const submitButton = document.getElementById('submitButton');
    
                                    checkbox.addEventListener('change', function() {
                                        submitButton.disabled = !checkbox.checked;
                                    });
    
                                });
    
                            </script>
                            <hr>
                            <div class="my-3 text-center">
                                <a href="/login" data-mdb-button-init="" data-mdb-ripple-init="" class="text-center btn-lg btn-block text-decoration-none fs-f14 mb-2 ">
                                    ログインパスワードをお持ちの方
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        {
            {
                --
                const registerButton = document.getElementById("registerButton");
                --
            }
        }
        const jobSearchErrorLg = document.getElementById("jobSearchErrorLg");
        const jobSearchErrorSm = document.getElementById("jobSearchErrorSm");
        const searchZipcode = document.getElementById("searchZipcode");
        const jobSearchRadios = document.querySelectorAll("input[name='job_search']");
        const registrationForm = document.getElementById("registerForm");
        const jobSearchValueInput = document.getElementById("job_search_value");

        // 📌 郵便番号で住所を取得
        searchZipcode.addEventListener("click", function() {
            let post_u = document.getElementById("post_u").value;
            let post_l = document.getElementById("post_l").value;

            if (post_u.length === 3 && post_l.length === 4) {
                fetch("{{ route('get.address.zipcloud') }}", {
                        method: "POST"
                        , headers: {
                            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                            , "Content-Type": "application/json"
                        }
                        , body: JSON.stringify({
                            post_u: post_u
                            , post_l: post_l
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.full_address) {
                            document.getElementById("full_address").value = data.full_address;
                        } else {
                            alert("住所が見つかりませんでした。");
                        }
                    })
                    .catch(error => console.error("Error:", error));
            } else {
                alert("郵便番号を正しく入力してください (例: 166-0012)");
            }
        });

        // 📌 "登録" ボタンがクリックされたときにラジオボタンをチェックする
        registerButton.addEventListener("click", function(event) {
            const selectedJobSearch = document.querySelector("input[name='job_search']:checked");

            if (!selectedJobSearch) {
                jobSearchErrorLg.style.display = "block";
                jobSearchErrorSm.style.display = "block";
                event.preventDefault();
            }
        });

        // 📌 ユーザーがラジオ ボタンを選択すると、エラー メッセージは消えます。
        document.querySelectorAll("input[name='job_search']").forEach(radio => {
            radio.addEventListener("change", function() {
                jobSearchErrorLg.style.display = "none";
                jobSearchErrorSm.style.display = "none";
            });
        });

        // 📌 Laravel 検証エラーが発生した場合、ページは自動的にエラー セクションまでスクロールします。
        if (jobSearchErrorLg.style.display === "block" || jobSearchErrorSm.style.display === "block") {
            jobSearchErrorLg.scrollIntoView({
                behavior: "smooth"
                , block: "center"
            });
        }
    });

</script>
{{-- <script src="{{ asset('js/signin.js') }}"></script> --}}
@endsection
