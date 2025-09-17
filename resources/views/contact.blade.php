@extends('layouts.top')

@section('title', '求職者お問い合わせ')

@section('content')
    <div class="container my-5">
        <h3 class="my-5 pt-5 text-center">お問い合わせ</h3>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>{{ session('success') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <p class="text-center text-danger">★こちらは転職・就職者向けのお問合せフォームです。求人企業様向けのお問合せフォームはこちら-> <a href="company_contact">求人企業</a>
        </p>

        <form action="{{ route('sendcontact.staff') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-12">
                <label class="form-label fw-bold">お問い合わせ</label>
                {{--  <div class="form-check">
                    <input class="form-check-input" type="radio" name="inquiryType" id="option1"
                        value="弊社（J 株式会社）の社員応募関連">
                    <label class="form-check-label" for="option1">弊社（J 株式会社）の社員応募関連（派遣・紹介は除く）</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="inquiryType" id="option2" value="法律・制度についてのご質問">
                    <label class="form-check-label" for="option2">法律・制度についてのご質問</label>
                </div>  --}}
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="inquiryType" id="option1"
                        value="しごとナビ操作方法・エラーについてのご質問">
                    <label class="form-check-label" for="option3">しごとナビ操作方法・エラーについてのご質問</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="inquiryType" id="option2" value="その他のご質問・苦情など">
                    <label class="form-check-label" for="option4">その他のご質問・苦情など</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="inquiryType" id="option3" value="仕事を見つかれませんでした">
                    <label class="form-check-label" for="option4">仕事を見つかれませんでした</label>
                </div>
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">メールアドレス</label>
                <input type="email" class="form-control border-dark" name="email" id="email" required>
            </div>

            <div class="col-md-6">
                <label for="name" class="form-label">お名前</label>
                <input type="text" class="form-control border-dark" name="name" id="name" required>
            </div>

            <div class="col-md-6">
                <label for="phone" class="form-label">電話番号</label>
                <input type="tel" class="form-control border-dark" name="phone" id="phone">
            </div>
            <div class="col-md-6">
                <label for="jobSeekerID" class="form-label">求職者ID (Sで始まるIDをお持ちの方)</label>
                <input type="text" class="form-control border-dark" id="jobSeekerID">
            </div>

            <div class="col-md-6">
                <label for="prefecture_code" class="form-label">都道府県</label>
                <select name="prefecture_code" id="prefecture_code" class="form-control border-dark">
                    <option value="" selected disabled>選択してください</option>
                    @foreach ($individualPrefectures as $prefecture)
                        <option value="{{ $prefecture['detail'] }}">
                            {{ $prefecture['detail'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label for="message" class="form-label">内容</label>
                <textarea class="form-control border-dark" name="message" id="message" rows="5" required></textarea>
            </div>

            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary px-5">送信</button>
            </div>
        </form>
    </div>
@endsection
