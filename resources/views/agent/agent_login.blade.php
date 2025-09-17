
@extends('layouts.top')

@section('title', 'エージェントログイン')
@section('content')
<section class="">
    <div class="container py-2 h-100">
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div style="border-radius: 1rem;">
                    <div class="card-body p-3 text-center">
                        <form action="{{ route('agent.login') }}" method="POST">
                            @csrf
                            <h5 style="line-height: 2">エージェントログイン</h5>
                            <!-- Error Messages -->
                            @if ($errors->any())
                            <div class="alert alert-danger" role="alert" style="background-color: #FDECEA; color: #D93025; border: 1px solid #D93025; border-radius: 5px; padding: 10px; margin-bottom: 15px; text-align: center;">
                                @foreach ($errors->all() as $error)
                                    {{ $error }}
                                @endforeach
                            </div>
                            @endif
                            <!-- company_code -->
                            <div data-mdb-input-init class="form-outline mb-4">
                                <label class="form-label pt-3 float-start" for="agent_code">エージェントID</label>
                                <input type="text" name="agent_code" id="agent_code" aria-label="登録している ID" class="form-control form-control-lg border border-primary" required>
                            </div>

                            <!-- Password -->
                            <div data-mdb-input-init class="form-outline mb-4">
                                <label class="form-label float-start" for="password">エージェントパスワード</label>
                                <input type="password" name="password" id="password" class="form-control form-control-lg border-primary" required>
                            </div>

                            <!-- Privacy Agreement -->
                            {{--  <div class="form-check py-3">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckChecked">
                                <label class="form-check-label" for="flexCheckChecked">
                                    <a href="https://www.shigotonavi.co.jp/privacy/privacymark.asp" style="font-size: 10px;">しごとナビ利用規約・個人情報保護に関する事項に同意する</a>
                                </label>
                            </div>  --}}

                            <!-- Submit Button -->
                            <button class="btn btn-lg btn-block" type="submit" style="background-color: rgba(255, 0, 0, 0.674); color: #fff; border-radius: 5px; padding: 10px 32px;">
                                ログイン
                            </button>
                            <hr class="my-4">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

