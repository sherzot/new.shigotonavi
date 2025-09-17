@extends('layouts.top')

@section('title', 'パスワードリセット')
@section('content')
    <section class="bg-white">
        <div class="container h-100">
            <div class="row d-flex justify-content-center align-items-center">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div style="border-radius: 1rem;">
                        <div class="card-body p-3 text-center">
                            <h5 style="line-height: 2">パスワードのリセット</h5>
                            <!-- Error Messages -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Success Message -->
                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <!-- Reset Password Form -->
                            <form action="{{ route('reset_password.request') }}" method="POST">
                                @csrf
                                <div class="form-group py-3 mb-3" 
                                <label class="form-label pt-3 float-start fs-6"
                                    for="staff_code">登録済みのメールアドレスを入力してください</label>
                                    <br>
                                    <br>
                                    <input type="email" id="mail_address" name="mail_address" class="form-control border-gray"
                                        required>
                                </div>
                                <div class="d-grid">
                                    <p class="text-center text-main-theme">パスワードを変更手順のメールが送信されます。30分以内にメールをご確認いただき、手続きを完了してください。</p>
                                    <button class="btn btn-snavibt text-white fs-base btn-lg btn-block" type="submit">
                                        パスワードを変更リンクを送信
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('flexCheckChecked');
            const submitButton = document.getElementById('submitButton');

            checkbox.addEventListener('change', function() {
                submitButton.disabled = !checkbox.checked;
            });
        });
    </script>
@endsection
