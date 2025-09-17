@extends('layouts.top')

@section('title', 'アンケート')

@section('content')
    <div class="container my-5">
        <h2 class="text-center">アンケートフォーム</h2>

        @if (session('success'))
            <div class="alert alert-success text-center">{{ session('success') }}</div>
        @endif

        <form action="{{ route('questionnaire.submit') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">ご意見・ご感想</label>
                <textarea name="feedback" class="form-control border-dark" rows="4" placeholder="自由にご記入ください"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">メールアドレス</label>
                <input type="email" name="email" class="form-control border-dark">
            </div>

            <button type="submit" class="btn btn-main-theme w-100">送信</button>
        </form>
    </div>
@endsection
