@extends('layouts.top')

@section('title', 'ログイン')
@section('content')
<section class="bg-white p-0 m-0">
    <div class="container-flued h-100">
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="" style="border-radius: 1rem;">
                    <div class="card-body p-2 text-center">
                        @if (session('message'))
                        <script>
                            alert("{{ session('message') }}");

                        </script>
                        @endif
                        @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                        @endif

                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li class="small">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <h5 style="line-height: 2">ログイン</h5>
                            <!-- Error Messages -->
                            @if ($errors->any())
                            <div class="alert alert-danger" role="alert" style="background-color: #FDECEA; color: #D93025; border: 1px solid #D93025; border-radius: 5px; padding: 10px; margin-bottom: 15px; text-align: center;">
                                @foreach ($errors->all() as $error)
                                {{ $error }}
                                @endforeach
                            </div>
                            @endif
                            <!-- Staff Code -->
                            <div data-mdb-input-init class="form-outline mb-3">
                                <label class="form-label pt-3 float-start" for="staff_code">ログインIDまたはメールアドレス</label>
                                <input type="text" name="staff_code" id="staff_code" aria-label="ログインIDまたはメールアドレス" class="form-control form-control-lg border border-primary" required>
                            </div>

                            <!-- Password -->
                            <div data-mdb-input-init class="form-outline mb-5">
                                <label class="form-label float-start" for="password">パスワード</label>
                                <input type="password" name="password" id="password" class="form-control form-control-lg border-primary" required>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button class="btn btn-snavibt text-white fs-base btn-lg btn-block" type="submit" style="border-radius: 5px; padding: 10px 32px;">
                                    ログイン
                                </button>
                            </div>
                            <hr class="my-4">
                            {{-- <a href="" class="btn1 btn-lg btn-block btn-red mb-2">
                            </a>  --}}
                            <div class="row">
                                <div class="col-12 p-0 mb-s10" id="footer-bottom">
                                    <a href="/reset_password" data-mdb-button-init data-mdb-ripple-init class="btn-lg btn-block text-decoration-none fs-f14 mb-2" style="font-size: 10px; padding: 0;">
                                        パスワードを忘れた方はこちら
                                    </a>
                                </div>
                                <div class="col-12 col-sm p-0" id="footer-bottom">
                                    <a href="/signin" data-mdb-button-init data-mdb-ripple-init class="btn-lg btn-block text-decoration-none fs-f14 mb-2" style="font-size: 10px; padding: 0;">
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

@endsection
