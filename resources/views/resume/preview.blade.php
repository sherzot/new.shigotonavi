@extends('layouts.top')

@section('content')
<div class="container-fluid bg-white border p-4" style="max-width: 850px;">
    <h4 class="text-center fw-bold mb-4">履 歴 書（確認画面）</h4>
    <p class="text-end">{{ now()->format('Y年 m月 d日') }} 現在</p>

    {{-- 📸 履歴書写真 --}}
    @if($existingPhoto)
    <div class="text-center mb-5">
        <img src="{{ $existingPhoto }}" class="img-thumbnail img-fluid" style="max-width: 180px;">
    </div>
    @endif

    {{-- 📋 基本情報 --}}
    <div class="mb-5">
        <h5 class="fw-bold border-bottom pb-2">基本情報</h5>
        <div class="row g-3">
            <div class="col-md-6 col-12">
                <p><strong>氏名:</strong> {{ $person->name }}</p>
            </div>
            <div class="col-md-6 col-12">
                <p><strong>氏名 (フリガナ):</strong> {{ $person->name_f }}</p>
            </div>
            <div class="col-md-6 col-12">
                <p><strong>メールアドレス:</strong> {{ $person->mail_address }}</p>
            </div>
            <div class="col-md-6 col-12">
                <p><strong>生年月日:</strong> {{ $person->birthday }}</p>
            </div>
            <div class="col-md-6 col-12">
                <p><strong>性別:</strong> {{ $person->sex == 1 ? '男' : '女' }}</p>
            </div>
            <div class="col-md-6 col-12">
                <p><strong>電話番号:</strong> {{ $person->portable_telephone_number }}</p>
            </div>
            <div class="col-12">
                <p><strong>住所:</strong> {{ $person->prefecture_name }} {{ $person->city }} {{ $person->town }} {{ $person->address }}</p>
            </div>
        </div>
    </div>

    {{-- 🎓 学歴 --}}
    <div class="mb-5">
        <h5 class="fw-bold border-bottom pb-2">学歴</h5>
        @forelse($educations as $edu)
        <p>・{{ $edu->entry_day }} 入学 / {{ $edu->graduate_day }} 卒業 - {{ $edu->school_name }} ({{ $edu->speciality }})</p>
        @empty
        <p class="text-muted">登録された学歴はありません。</p>
        @endforelse
    </div>

    {{-- 💼 職歴 --}}
    <div class="mb-5">
        <h5 class="fw-bold border-bottom pb-2">職歴</h5>
        @forelse($careers as $career)
        <p>・{{ $career->entry_day }} 入社 / {{ $career->retire_day }} 退社 - {{ $career->company_name }} ({{ $career->job_type_detail }})</p>
        @empty
        <p class="text-muted">登録された職歴はありません。</p>
        @endforelse
    </div>

    {{-- 🏅 資格 --}}
    <div class="mb-5">
        <h5 class="fw-bold border-bottom pb-2">資格</h5>
        @forelse($licenses as $license)
        <p>・{{ $license->license_name }} (取得日: {{ $license->get_day }})</p>
        @empty
        <p class="text-muted">登録された資格はありません。</p>
        @endforelse
    </div>

    {{-- ✍️ 自己PR --}}
    <div class="mb-5">
        <h5 class="fw-bold border-bottom pb-2">自己PR</h5>
        @if($self_pr)
        <p>{{ $self_pr }}</p>
        @else
        <p class="text-muted">自己PRは未登録です。</p>
        @endif
    </div>

    {{-- 🛠️ スキル --}}
    <div class="mb-5">
        <h5 class="fw-bold border-bottom pb-2">スキル</h5>
        @forelse($skills as $skill)
        <span class="badge bg-secondary mb-2 me-2">{{ $skill->detail }}</span>
        @empty
        <p class="text-muted">登録されたスキルはありません。</p>
        @endforelse
    </div>

    {{-- 📩 志望動機・希望条件 --}}
    <div class="mb-5">
        <h5 class="fw-bold border-bottom pb-2">志望動機・希望条件</h5>
        @if($resumePreference)
        <p><strong>志望動機:</strong> {{ $resumePreference->wish_motive }}</p>
        <p><strong>本人希望欄:</strong> {{ $resumePreference->hope_column }}</p>
        @else
        <p class="text-muted">志望動機・希望条件は未登録です。</p>
        @endif
    </div>

    {{-- ✅ 確認・戻るボタン --}}
    <div class="text-center mt-5">
        <a href="{{ route('resume.edit') }}" class="btn btn-outline-secondary btn-lg px-5 py-2 me-3">戻る</a>
        <form action="{{ route('resume.confirm') }}" method="POST" class="d-inline-block">
            @csrf
            <button type="submit" class="btn btn-primary btn-lg px-5 py-2">内容を確定する</button>
        </form>
    </div>
</div>
@endsection
