@extends('layouts.top')

@section('title', 'パスワード変更')
@section('content')
        <div class="container py-2 h-100">
            <div class="row d-flex justify-content-center align-items-center vh-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow-2-strong" style="border-radius: 1rem;">
                        <div class="card-body p-3 text-center">
                            <h3 class="mb-4">新しいパスワードの設定</h3>
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

                            <!-- Reset Password Form -->
                            <form action="{{ route('password.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                <input type="hidden" name="hashed_email" value="{{ $hashed_email }}">

                                <div class="form-group my-3">
                                    <label class="py-2" for="password">新しいパスワード</label>
                                    <input type="password" id="password" name="password" class="form-control"
                                        placeholder="新しいパスワードを入力してください" required>
                                </div>
                                <div class="form-group my-3">
                                    <label class="py-2" for="password_confirmation">新しいパスワード（確認）</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="form-control" placeholder="パスワードを再入力してください" required>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">パスワードをリセット</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
