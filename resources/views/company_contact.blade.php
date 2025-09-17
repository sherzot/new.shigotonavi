@extends('layouts.top')

@section('title', '求人企業お問い合わせ')

@section('content')
    <div class="container mt-5">
        <h3 class="my-5 pt-5 text-center">求人企業様お問い合わせ</h3>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>{{ session('success') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <p class="text-muted text-center">
            求人広告についてのお申込み・お問合わせは、下記フォームにご記入の上、「送信」ボタンでお送りください。
        </p>
        <div class="alert alert-warning text-center" role="alert">
            ※ 求人広告以外のお問合せはこちら → <a href="contact" class="text-main-theme">求職者さま用フォーマット</a>
        </div>

        <form action="{{ route('sendcontact.company') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-12">
                <label class="form-label">お問合せ内容</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="subject" value="エージェントNEOについて">
                    <label class="form-check-label">求人に関するお問い合わせ</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="subject" value="その他の問い合わせ">
                    <label class="form-check-label">その他のお問い合わせ・ご質問</label>
                </div>
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">メールアドレス</label>
                <input type="email" class="form-control border-dark" id="email" name="email" required>
            </div>
            <div class="col-md-6">
                <label for="name" class="form-label">ご担当者名</label>
                <input type="text" class="form-control border-dark" id="name" name="name" required>
            </div>
            <div class="col-md-6">
                <label for="company" class="form-label">貴社名</label>
                <input type="text" class="form-control border-dark" id="company" name="company">
            </div>
            <!-- 勤務地 -->
            <div class="col-md-6">
                <label for="prefecture_code" class="form-label">都道府県</label>
                <select name="prefecture_code[]" id="prefecture_code" class="form-control border-dark">
                    <option value="" selected disabled>選択してください</option>
                    <!-- 地域 -->
                    @if (isset($regionGroups))
                        @foreach ($regionGroups as $region)
                            <optgroup label="{{ $region['detail'] }}">
                                @foreach ($region['prefectures'] as $prefecture)
                                    <option value="{{ $prefecture['code'] }}">
                                        {{ $prefecture['detail'] }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    @endif

                    <!-- 個別 -->
                    @if (isset($individualPrefectures) && is_array($individualPrefectures))
                        @foreach ($individualPrefectures as $prefecture)
                            <option value="{{ $prefecture['code'] }}">
                                {{ $prefecture['detail'] }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">電話番号</label>
                <input type="text" class="form-control border-dark" id="phone" name="phone">
            </div>
            <div class="col-md-6">
                <label for="trouble" class="form-label">人材や採用等でお悩みの点</label>
                <textarea class="form-control border-dark" id="trouble" name="trouble" rows="3"></textarea>
            </div>
            <div class="col-12">
                <label for="message" class="form-label">内容</label>
                <textarea class="form-control border-dark" id="message" name="message" rows="5" required></textarea>
            </div>

            <!-- アンケート追加 -->
            <div class="col-12 mt-5">
                <h5 class="text-main-theme fw-bold">アンケートにご協力ください。（任意）</h5>
                <div class="border p-3">
                    <label class="fw-bold pb-3">Q1. しごとナビはどこでお知りになりましたか？</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="survey_q1" value="検索エンジン">
                        <label class="form-check-label">【A】リリース記事</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="survey_q1" value="検索エンジン">
                        <label class="form-check-label">【B】検索エンジンで検索して知った</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="survey_q1" value="知人の紹介">
                        <label class="form-check-label">【C】知人の紹介</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="survey_q1" value="当社営業">
                        <label class="form-check-label">【D】当社営業からの紹介</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="survey_q1" value="その他">
                        <label class="form-check-label">【E】その他</label>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-3">
                <label class="fw-bold pb-3">Q2. 【B】を選択された方に質問です。検索された語彙を教えてください。</label>
                <input type="text" class="form-control border-dark" name="survey_q2" placeholder="検索ワードを入力してください">
            </div>

            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary mt-3">送信</button>
            </div>
        </form>
    </div>
@endsection
