<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="{{ asset('img/icon.png') }}" rel="icon" type="image/svg+xml">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
        {{--  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>  --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkbox = document.getElementById('flexCheckChecked');
                const submitButton = document.getElementById('submitButton');

                checkbox.addEventListener('change', function() {
                    submitButton.disabled = !checkbox.checked;
                });
            });
        </script>
        <title>ログイン</title>

        {{--  <link rel="stylesheet" href="{{ asset('style/responsive.css') }}">  --}}
        {{--  <link rel="stylesheet" href="{{ asset('style/first-form/form.css') }}">  --}}
        <link rel="stylesheet" href="{{ asset('style/snavi.css') }}">
    </head>

    <body>
        <section class="bg-white">
            <div class="container py-2 h-100">
                <div class="row d-flex justify-content-center align-items-center vh-100">
                    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                        <div class="card shadow-2-strong" style="border-radius: 1rem;">
                            <div class="card-body p-3 text-center">
                                <form action="{{ route('login') }}" method="POST">
                                    @csrf
                                    <h3 class="mb-3 mt-2">
                                        <a href="/">
                                            <img src="{{ asset('img/logo02.png') }}" alt="logo"
                                                class="w-sm-25 w-50">
                                        </a>
                                    </h3>
                                    <h5 style="line-height: 2">ログイン</h5>
                                    <!-- Error Messages -->
                                    @if ($errors->any())
                                        <div class="alert alert-danger" role="alert"
                                            style="background-color: #FDECEA; color: #D93025; border: 1px solid #D93025; border-radius: 5px; padding: 10px; margin-bottom: 15px; text-align: center;">
                                            @foreach ($errors->all() as $error)
                                                {{ $error }}
                                            @endforeach
                                        </div>
                                    @endif
                                    <!-- Staff Code -->
                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <label class="form-label pt-3 float-start"
                                            for="staff_code">ログインIDまたはメールアドレス</label>
                                        <input type="text" name="staff_code" id="staff_code"
                                            aria-label="ログインIDまたはメールアドレス"
                                            class="form-control form-control-lg border border-primary" required>
                                    </div>

                                    <!-- Password -->
                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <label class="form-label float-start" for="password">パスワード</label>
                                        <input type="password" name="password" id="password"
                                            class="form-control form-control-lg border-primary" required>
                                    </div>

                                    <!-- Privacy Agreement -->
                                    {{--  <div class="form-check py-3">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckChecked">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        <a href="https://www.shigotonavi.co.jp/privacy/privacymark.asp" style="font-size: 10px;">しごとナビ利用規約・個人情報保護に関する事項に同意する</a>
                                    </label>
                                </div>  --}}

                                    <!-- Submit Button -->
                                    <div class="d-grid">
                                        <button class="btn btn-snavibt text-white fs-base btn-lg btn-block" type="submit"
                                            style="border-radius: 5px; padding: 10px 32px;">
                                            ログイン
                                        </button>
                                    </div>
                                    <hr class="my-4">
                                    {{--  <a href="" class="btn1 btn-lg btn-block btn-red mb-2">

                                </a>  --}}
                                    <div class="row">
                                        <div class="col-12 p-0 mb-s10" id="footer-bottom">
                                            <a href="/reset_password" data-mdb-button-init data-mdb-ripple-init
                                                class="btn-lg btn-block text-decoration-none fs-f14 mb-2" style="font-size: 10px; padding: 0;">
                                                パスワードを忘れた方はこちら
                                            </a>
                                        </div>
                                        <div class="col-12 col-sm p-0" id="footer-bottom">
                                            <a href="/signin" data-mdb-button-init data-mdb-ripple-init
                                                class="btn-lg btn-block text-decoration-none fs-f14 mb-2" style="font-size: 10px; padding: 0;">
                                                会員登録がまだの方はこちら
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </body>

</html>
