@extends('layouts.top')

@section('title', '会員登録')
@section('content')
    <section class="d-flex align-items-center justify-content-center py-0 my-0">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-12">
                    <div class="card-body">
                        <form id="registerForm" action="{{ route('register') }}" method="POST">
                            @csrf
                            <h3 class="text-center mb-4 mt-0 pt-3">マイページ取得</h3>
                            
                            <p class="mb-4">
                                <span class="text-main-theme">「しごとナビ」</span>で、転職活動に必要な履歴書や職務経歴書を
                                学歴や職歴、志望動機などの項目に沿って入力するだけで、自動的に文書が作成され、豊富なテンプレートから印刷用のPDFやExcel形式で出力可能です。
                                豊富なテンプレートからPDFやExcel形式で印刷でき、証明写真の登録や「コンビニプリント」にも対応しています。
                                マイページには履歴書作成のポイントも掲載されており、採用担当者に好印象を与える履歴書作りをサポートします。
                            </p>
                            <br>
                            <h5 class="text-center mb-4 mt-0 pt-0 text-main-theme">基本情報登録</h5>
                            @if ($errors->any())
                                <div class="alert text-main-theme">
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
                                <!-- 名前 -->
                                <input type="text" name="name" id="name"
                                    class="form-control border-primary py-1 @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- メールアドレス -->
                            <div class="mb-2">
                                <label for="email" class="form-label">メールアドレス
                                    <span class="text-main-theme">必須</span>
                                </label>
                                <input type="email" name="mail_address" id="email"
                                    class="form-control border-primary py-1 @error('mail_address') is-invalid @enderror"
                                    value="{{ old('mail_address') }}" required>

                                @error('mail_address')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>


                            <!-- パスワード -->
                            <div class="mb-2 position-relative">
                                <label class="form-label float-start" for="password">
                                    パスワード <span class="text-main-theme">必須</span>
                                </label>
                                <input type="password" name="password" id="password"
                                    class="form-control border-primary py-1 @error('password') is-invalid @enderror" />
                                <button type="button" class="btn toggle-password" data-target="password"
                                    style="position: absolute; right: 6px; top: 70%; transform: translateY(-50%);">
                                    <i class="fa fa-eye"></i>
                                </button>
                                @error('password')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- パスワード確認 -->
                            <div class="mb-2 position-relative">
                                <label class="form-label float-start" for="password_confirmation">
                                    パスワードを認証する <span class="text-main-theme">必須</span>
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control border-primary py-1" />
                                <button type="button" class="btn toggle-password" data-target="password_confirmation"
                                    style="position: absolute; right: 6px; top: 70%; transform: translateY(-50%);">
                                    <i class="fa fa-eye"></i>
                                </button>
                                @error('password_confirmation')
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



                            {{--  生年月日  --}}
                            <div class="mb-2">
                                <label class="form-label">生年月日 <small class="text-main-theme">必須
                                        (例：19710401)</small></label>
                                <input type="text" name="birthday" class="form-control border-primary py-1"
                                    value="{{ old('birthday') }}" maxlength="8" pattern="\d{8}" required>
                                @error('birthday')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr>
                            <p class="">これであなたは100万人サイト <span class="text-main-theme">[しごとナビ]</span> にマイページを取得します。
                            </p>
                            <h5 class="text-main-theme">『保有権利』</h5>
                            <ul>
                                <li>学歴・職歴・自己などの内容を入力・証明写真の登録してから、<br> 履歴書または職務経歴書作成</li>
                                <li><span class="text-main-theme">しごと探し</span>(自分の希望だけでオファー）</li>
                                <li>応募記録(応募先、結果を日付順に記録）</li>
                                {{--  <li>コンプリ(３大コンビニどこからでもプリント可）</li>  --}}
                            </ul>
                           
                            {{--  しごと探しですか？  --}}
                            {{--  <div class="text-center my-3" id="option">
                                <h5 class="fw-bold text-main-theme fs-f28">次にしごと探しですか？</h5>
                                <div class="d-flex justify-content-center gap-3">
                                    <div class="form-check px-2">
                                        <input class="form-check-input border-dark fs-f20" type="radio"
                                            name="job_search" id="job_search_yes" value="yes"
                                            {{ old('job_search') == 'yes' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-primary fs-f24"
                                            for="job_search_yes">はい</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input border-dark fs-f20" type="radio"
                                            name="job_search" id="job_search_no" value="no"
                                            {{ old('job_search') == 'no' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-primary fs-f24"
                                            for="job_search_no">いいえ</label>
                                    </div>
                                </div>
                                @error('job_search')
                                    <div class="alert text-main-theme fw-bold mt-2">
                                        * しごと探しですか？のオプションを選んでください!
                                    </div>
                                @enderror
                            </div>  --}}
                            <hr>
                            <div class="form-check py-3">
                                <input class="form-check-input border-dark" type="checkbox" value=""
                                    id="flexCheckChecked">
                                <label class="form-check-label fs-f12" for="flexCheckChecked">
                                    <a
                                        href="https://www.shigotonavi.co.jp/privacy/privacymark.asp">しごとナビ利用規約・個人情報保護に関する事項</a>に同意する
                                </label>
                            </div>
                            <div class="d-grid">
                                <button id="registerButton" class="btn btn-main-theme btn-lg">登録</button>
                            </div>
                            <div id="jobSearchError" class="text-main-theme fw-bold mt-2"
                                style="display: none; font-size: 18px;">
                                * しごと探しですか？のオプションを選んでください!　<a href="#option"><i
                                        class="fa-solid fa-arrow-down fs-f24 btn btn-outline-primary"></i></a>
                            </div>

                            <hr>
                            <div class="my-3 text-center">
                                <a href="/login" data-mdb-button-init="" data-mdb-ripple-init=""
                                    class="text-center btn-lg btn-block text-decoration-none fs-f14 mb-2 ">
                                    ログインパスワードをお持ちの方
                                </a>
                            </div>
                            <hr>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const registerButton = document.getElementById("registerButton");
            const jobSearchError = document.getElementById("jobSearchError");
        
            registerButton.addEventListener("click", function(event) {
                const selectedJobSearch = document.querySelector("input[name='job_search']:checked");
        
                if (!selectedJobSearch) {
                    jobSearchError.style.display = "block";
                    event.preventDefault();
                }
            });
        
            // ✅ Agar foydalanuvchi radio button tanlasa, xatolik xabari yo‘qoladi
            document.querySelectorAll("input[name='job_search']").forEach(radio => {
                radio.addEventListener("change", function() {
                    jobSearchError.style.display = "none";
                });
            });
        
            // ✅ Agar xatolik bo‘lsa, avtomatik skroll qilish
            const firstError = document.querySelector(".text-main-theme");
            if (firstError) {
                firstError.scrollIntoView({ behavior: "smooth", block: "center" });
            }
        });
        
    </script>


    {{--  <script src="{{ asset('js/signin.js') }}"></script>  --}}

@endsection
