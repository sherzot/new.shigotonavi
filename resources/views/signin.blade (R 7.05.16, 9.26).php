@extends('layouts.top')

@section('title', '会員登録')
@section('content')
<section class="d-flex align-items-center justify-content-center py-0 my-0">
    <div class="container-fluid container-lg">
        <img src="{{ asset('img/toptop2.png') }}" class="img-fluid mt-0 d-none d-sm-block" alt="Hero Image">
        <img src="{{ asset('img/toptop-sm2.png') }}" class="img-fluid mt-0 d-block d-sm-none" alt="Hero Image">
        <div class="container py-1">
            <div class="row justify-content-center">
                <div class="col-md-8">

                    <!-- Step 1 -->
                    <div class="d-flex align-items-start mb-3 p-3 bg-light rounded shadow-sm">
                        <div class="me-3 text-center">
                            <span class="badge btn btn-main-theme fs-6 px-3 py-2 d-block">Step①</span>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">基本情報登録</h5>
                        </div>
                    </div>

                    <!-- ▼ under Step 1 -->
                    <div class="text-center mb-3">
                        <span style="font-size: 2rem;" class="text-main-theme">▼</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12">
                <div class="card-body" id="register">
                    <form id="registerForm" action="{{ route('registration') }}" method="POST">
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
                        {{--  <div class="d-grid">
                            <button id="submitButton" class="btn btn-main-theme btn-lg" disabled onchange="submitForm()">登録して求人を見る</button>
                        </div>  --}}
                        <div class="row g-2"> {{-- g-2 = 0.5rem gap --}}
                            <div class="col-12 col-md-6">
                                <button name="action" value="view_jobs" class="btn btn-main-theme btn-lg w-100" id="submitButtonView" disabled>
                                    登録して求人を見る
                                </button>
                            </div>
                            <div class="col-12 col-md-6">
                                <button name="action" value="create_resume" class="btn btn-primary btn-lg w-100" id="submitButtonResume" disabled>
                                    履歴書と職務経歴書作成
                                </button>
                            </div>
                        </div>
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const checkbox = document.getElementById('flexCheckChecked');
                                const submitButtonView = document.getElementById('submitButtonView');
                                const submitButtonResume = document.getElementById('submitButtonResume');
                        
                                checkbox.addEventListener('change', function() {
                                    const enabled = checkbox.checked;
                                    submitButtonView.disabled = !enabled;
                                    submitButtonResume.disabled = !enabled;
                                });
                            });
                        </script>                        
                        <hr>
                    </form>
                </div>

            </div>
        </div>
        <img src="{{ asset('img/systeminfo.svg') }}" class="img-fluid mt-0 d-none d-sm-block" alt="Hero Image">
        <img src="{{ asset('img/systeminfo-sm.svg') }}" class="img-fluid mt-0 d-block d-sm-none" alt="Hero Image">
    </div>
</section>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        const registerButton = document.getElementById("registerButton");
        const jobSearchErrorLg = document.getElementById("jobSearchErrorLg");
        const jobSearchErrorSm = document.getElementById("jobSearchErrorSm");
        const searchZipcode = document.getElementById("searchZipcode");
        const fetchUrl = searchZipcode.getAttribute("data-url");
        const jobSearchRadios = document.querySelectorAll("input[name='job_search']");
        const registrationForm = document.getElementById("registerForm");
        const jobSearchValueInput = document.getElementById("job_search_value");

        // 📌 郵便番号で住所を取得

        searchZipcode.addEventListener("click", function() {
            let post_u = document.getElementById("post_u").value;
            let post_l = document.getElementById("post_l").value;

            if (post_u.length === 3 && post_l.length === 4) {
                fetch(fetchUrl, {
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

