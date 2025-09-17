@extends('layouts.top')

@section('title', '会員登録')
@section('content')
    <section class="bg-white p-0 m-0">
        <div class="row justify-content-center py-5">
            <div class="col-12 col-md-8 col-lg-6 p-3">
                <h5>新規会員登録</h5>
                <!-- エラーメッセージ -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Modal -->
                @if (session('message'))
                    <div class="alert alert-success">
                        {{ session('message') }}
                    </div>
                @endif
                <!-- Modal - 登録完了 -->
                <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="successModalLabel">確認メール送信</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                確認メールが受信箱に送信されました。入力したメールをご確認ください。
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Forma -->
                <form id="registerForm" action="{{ route('register') }}" method="POST">
                    @csrf
                    <div data-mdb-input-init class="form-outline mb-4">
                        <label class="form-label pt-1 float-start" for="email">メールアドレス
                            <span style="color: rgba(255, 0, 0, 0.674);">必須</span>
                        </label>
                        <input type="email" name="mail_address" id="email" aria-label="email"
                            class="form-control form-control-lg border border-primary {{ $errors->has('mail_address') ? 'is-invalid' : '' }}"
                            value="{{ old('mail_address') }}" required />
                        <span id="error-message"></span>
                    </div>

                    <p class="text-start fs-f12">*受信メールの設定でドメイン制限をされている方は、<br>
                        <a href="https://www.shigotonavi.co.jp/">(shigotonabi.co.jp)</a>
                        が受信できるように解除してください。
                    </p>

                    <div class="form-check py-3">
                        <input class="form-check-input border-dark" type="checkbox" value="" id="flexCheckChecked">
                        <label class="form-check-label fs-f12" for="flexCheckChecked">
                            <a href="https://www.shigotonavi.co.jp/privacy/privacymark.asp">しごとナビ利用規約・個人情報保護に関する事項</a>に同意する
                        </label>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-snavibt text-white fs-base btn-lg btn-block" type="submit"
                            style="border-radius: 5px; padding: 10px 32px;">
                            登録する
                        </button>
                    </div>
                    <hr class="my-4">
                    <div class="row text-center">
                        <div class="col-12 p-0 mb-s10 text-center" id="footer-bottom">
                            <a href="/login" data-mdb-button-init data-mdb-ripple-init
                                class="btn-lg btn-block text-decoration-none fs-f14 mb-2 text-center">
                                ログインパスワードをお持ちの方
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <script>
        @if (session('showSuccessModal'))
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        @endif
    </script>

@endsection
