@extends('layouts.top')

@section('title', '会員登録')
@section('content')
<section class="d-flex align-items-center justify-content-center py-0 my-0">
    <div class="container-fluid container-lg">
        <img src="{{ asset('img/toptop2.png') }}" class="img-fluid mt-0 d-none d-sm-block" alt="Hero Image">
        <img src="{{ asset('img/toptop-sm2.png') }}" class="img-fluid mt-0 d-block d-sm-none" alt="Hero Image">
        {{--  <div class="my-3 text-center">
            <a href="#register" data-mdb-button-init="" data-mdb-ripple-init="" class="text-center btn btn-main-theme btn-lg btn-block text-decoration-none fs-f28 mb-2 w-50">
                基本情報登録
            </a>
        </div>  --}}
        {{--  <img src="{{ asset('img/steep.png') }}" class="img-fluid mt-0" alt="Hero Image">  --}}
        <div class="container py-5">
            {{--  <h2 class="text-center fw-bold mb-5">しごとナビ利用の流れ</h2>  --}}
        
            <div class="row justify-content-center">
                <div class="col-md-8">
        
                    <!-- Step 1 -->
                    <div class="d-flex align-items-start mb-3 p-3 bg-light rounded shadow-sm">
                        <div class="me-3 text-center">
                            <span class="badge btn btn-main-theme fs-6 px-3 py-2 d-block">Step①</span>
                        </div>
                        <div><h5 class="mb-0 fw-bold">基本情報登録</h5></div>
                    </div>
        
                    <!-- ▼ under Step 1 -->
                    <div class="text-center mb-3">
                        <span style="font-size: 2rem;" class="text-main-theme">▼</span>
                    </div>
        
                    {{--  <!-- Step 2 -->
                    <div class="d-flex align-items-start mb-3 p-3 bg-light rounded shadow-sm">
                        <div class="me-3 text-center">
                            <span class="badge btn btn-main-theme fs-6 px-3 py-2 d-block">Step②</span>
                        </div>
                        <div><h5 class="mb-0 fw-bold">自分で理想の求人を選んで、オファー</h5></div>
                    </div>  --}}
        
                    {{--  <!-- ▼ under Step 2 -->
                    <div class="text-center mb-3">
                        <span style="font-size: 2rem;" class="text-main-theme">▼</span>
                    </div>  --}}
        
                    {{--  <!-- Step 3 -->
                    <div class="d-flex align-items-start mb-3 p-3 bg-light rounded shadow-sm">
                        <div class="me-3 text-center">
                            <span class="badge btn btn-main-theme fs-6 px-3 py-2 d-block">Step③</span>
                        </div>
                        <div>
                            <p class="mb-2 fw-bold">
                                求人がマッチングされたら、最適な求人を絞り込み、納得すれば
                                <span class="text-main-theme">面談依頼する【オファー】</span>
                            </p>
                            <p class="mb-0 fw-bold">ボタンを押してエージェントに通知します。</p>
                        </div>
                    </div>  --}}
        
                    {{--  <!-- ▼ under Step 3 -->
                    <div class="text-center mb-3">
                        <span style="font-size: 2rem;" class="text-main-theme">▼</span>
                    </div>  --}}
        
                    {{--  <!-- OFFER BLOCK -->
                    <div class="d-flex align-items-center justify-content-center border border-danger text-center rounded shadow-sm py-4">
                        <div>
                            <span class="text-main-theme fw-bold fs-4">オファー</span>　
                            <span class="fw-bold fs-4">自身を求人に</span>
                            <span class="text-main-theme fw-bold fs-4">オファー</span>
                        </div>
                    </div>  --}}
        
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12">
                <div class="container my-5">
                    <div class="d-flex justify-content-center align-items-center step-flow">
                
                        <!-- Step 1 -->
                        <div class="text-center">
                            <div class="step-circle active ">①</div>
                            {{--  <div class="step-label">基本情報登録</div>  --}}
                            {{--  <div class="step-label">基本情報</div>  --}}
                        </div>
                
                        <!-- Line -->
                        <div class="step-line"></div>
                        {{--  <div class="step-line filled"></div>  --}}
                
                        <!-- Step 2 -->
                        <div class="text-center">
                            <div class="step-circle">②</div>
                            {{--  <div class="step-label">希望条件登録</div>  --}}
                            {{--  <div class="step-label">希望条件</div>  --}}
                        </div>
                
                        {{--  <!-- Line -->
                        <div class="step-line"></div>
                
                        <!-- Step 3 -->
                        <div class="text-center">
                            <div class="step-circle">③</div>
                            <div class="step-label">オファーする</div>
                            <div class="step-label">オファー</div>
                        </div>  --}}
                
                    </div>
                </div>
                <div class="card-body" id="register">
                    <form id="registerForm" action="{{ route('registration') }}" method="POST">
                        @csrf

                        <h3 class="text-center pt-5">基本情報登録</h3>
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
        <!-- ✅ マーケティング文: PCサイズ -->
                <section class="d-none d-sm-block py-5 bg-dark text-white">
                    <div class="container">
                        <div class="mx-auto" style="max-width: 900px;">
                            <h4 class="fw-bold mb-4 text-center text-warning">今年は採用革新の幕開け。効率採用システム</h4>
                            <ul class="list-unstyled fs-5 lh-lg">
                                <li><i class="fa-solid fa-check text-warning"></i> 求職者は希望条件を入力して、<strong>ベストな1社</strong>に絞り込み、納得すれば面談依頼【オファー】<br> ボタンを押してエージェントに知らせます。</li>
                                <li><i class="fa-solid fa-check text-warning"></i> 求人企業は求人票の情報管理に責任を持ち、<strong>待つだけ</strong>。</li>
                                <li><i class="fa-solid fa-check text-warning"></i> エージェントは届いた【オファー】内容を確認し、<strong>双方にヒアリング</strong>。マッチすればつなぎ、<br>違えばやり直す適切なアドバイス。</li>
                            </ul>
                        </div>
                    </div>
                </section>
        
                <!-- ✅ マーケティング文: スマホサイズ -->
                <section class="d-block d-sm-none py-4 bg-dark text-white">
                    <div class="container">
                        <div class="mx-auto" style="max-width: 900px;">
                            <h5 class="fw-bold mb-3 text-center text-warning">今年は採用革新の幕開け。<br>効率採用システム</h5>
                            <div class="fs-6 lh-lg">
                                <i class="fa-solid fa-check text-warning"></i> 求職者は希望条件を入力して、<br>
                                ベストな1社に絞り込み、<br>
                                納得すれば【オファー】ボタンで<br>
                                エージェントに通知。<br><br>
        
                                <i class="fa-solid fa-check text-warning"></i> 求人企業は求人票を管理し、<br>
                                待つだけの簡単運用。<br><br>
        
                                <i class="fa-solid fa-check text-warning"></i> エージェントはオファーを確認後、<br>
                                条件が合うか両者にヒアリング。<br>
                                マッチすれば繋ぎ、違えば<br>
                                アドバイスでやり直し！
                            </div>
                        </div>
                    </div>
                </section>
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
