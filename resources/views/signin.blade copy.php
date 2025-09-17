<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>会員登録</title>
        <link rel="stylesheet" href="{{ asset('style/responsive') }}">
        <link rel="stylesheet" href="{{ asset('style/first-form/form.css') }}">
        <link href="{{ asset('img/icon.png') }}" rel="icon" type="image/svg+xml">
        <!-- Bootstrap CSS va JavaScript -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
            integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
            integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
        </script>
        <script src="{{ asset('js/form.js') }}"></script>
    </head>

    <body>
        <section class="" style="background-color: #508bfc;">
            <div class="container py-3 h-100">
                <div class="row d-flex justify-content-center align-items-center vh-100">
                    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                        <div class="card shadow-2-strong">
                            <div class="card-body p-3 text-center">
                                <h3 class="mb-3 mt-2">
                                    <a href="/">
                                        <img src="{{ asset('img/logo02.png') }}" alt="logo"
                                            class="logo w-sm-25 w-50">
                                    </a>
                                </h3>
                                <h5>新規会員登録</h5>
                                <!-- エラーメッセージ -->
                                @if (session('status'))
                                    <div class="alert alert-info">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                <!-- Forma -->
                                <form id="registerForm" action="{{ route('register') }}" method="POST">
                                    @csrf
                                    <!-- エラーメッセージ -->
                                    @if ($errors->has('mail_address'))
                                        <div class="alert alert-danger">
                                            {{ $errors->first('mail_address') }}
                                        </div>
                                    @endif
                                    <!-- Modal -->
                                    <div class="modal fade" id="successModal" tabindex="-1"
                                        aria-labelledby="successModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="successModalLabel">確認メール送信</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    確認メールが受信箱に送信されました。リンクをクリックしてメールをご確認ください。
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">閉じる</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <label class="form-label pt-1 float-start" for="email">メールアドレス
                                            <span style="color: rgba(255, 0, 0, 0.674);">必須/</span>
                                        </label>
                                        <input type="email" name="mail_address" id="email" aria-label="email"
                                            class="form-control form-control-lg border border-primary {{ $errors->has('mail_address') ? 'is-invalid' : '' }}"
                                            value="{{ old('mail_address') }}" required />
                                        <span id="error-message"></span>
                                    </div>

                                    <p>* 受信メールの設定でドメイン制限をされている方は、<a
                                            href="https://www.shigotonavi.co.jp/">(shigotonabi.co.jp)</a>
                                        が受信できるように解除してください。
                                    </p>

                                    <div class="form-check py-3">
                                        <input class="form-check-input" type="checkbox" value=""
                                            id="flexCheckChecked">
                                        <label class="form-check-label" for="flexCheckChecked">
                                            <a
                                                href="https://www.shigotonavi.co.jp/privacy/privacymark.asp">しごとナビ利用規約・個人情報保護に関する事項に同意する</a>
                                        </label>
                                    </div>

                                    <button id="submitButton" class="btn btn-red btn-lg btn-block" name="submit"
                                        type="submit" disabled>
                                        登録する
                                    </button>
                                    <hr class="my-4">
                                    <br>
                                    <div class="row" id="footer-bottom">
                                        <a href="/login" data-mdb-button-init data-mdb-ripple-init
                                            class="btn1 btn-lg btn-block mb-2">
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
            @if(session('showSuccessModal'))
                var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            @endif
        </script>

    </body>

</html>
