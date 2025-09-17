@extends('layouts.layout')

@section('title', '求職者一覧')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('agent.dashboard') }}">
            <img src="{{ asset('img/logo02.png') }}" alt="Logo" style="height: 50px;" />
        </a>
    </div>
    {{-- 🔵 ページタイトル --}}
    <div class="mb-4">
        <div class="bg-primary text-white p-3 rounded shadow-sm text-center fs-5 fw-bold">
            {{ $searchDay }} 求職者一覧（スタッフコードクリックで詳細表示）
        </div>
    </div>

    {{-- ✅ 新規登録者一覧 --}}
    <div class="card mb-5 border-0 shadow-sm">
        <div class="card-header bg-success bg-gradient text-white fs-6 fw-bold">
            <i class="fas fa-user-plus me-2"></i>新規登録者一覧
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light text-center align-middle">
                        <tr class="small text-nowrap">
                            <th>No</th><th>登録日時</th><th>スタッフコード</th><th>氏名</th><th>誕生日</th><th>年齢</th>
                            <th>性別</th><th>都道府県</th><th>市町村</th><th>電話</th><th>Mail</th>
                            <th>直近勤務先</th><th>登録アクション</th><th>経験職種</th><th>希望職種</th><th>希望勤務形態</th>
                            <th>Mypageアクセス</th><th>求人一覧</th><th>絞込数</th><th>詳細閲覧</th><th>オファー</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach ($users as $i => $user)
                            @php
                                $workingStyle = $user->yearly_income_min > 0 ? '正社員' : ($user->hourly_income_min > 0 ? '派遣' : '');
                            @endphp
                            <tr class="text-nowrap">
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td>{{ $user->created_at }}</td>
                                <td>
                                    <a href="{{ route('agent.userDetail', ['staff_code' => $user->staff_code]) }}"
                                       class="text-primary fw-bold text-decoration-none">
                                        {{ $user->staff_code }}
                                    </a>
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($user->birthday)->format('Y-m-d') }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($user->birthday)->age }}</td>
                                <td class="text-center">{{ $user->sex }}</td>
                                <td>{{ $user->prefecture }}</td>
                                <td>{{ $user->city }}</td>
                                <td>{{ $user->tel }}</td>
                                <td>{{ $user->mail_address }}</td>
                                <td>{{ $user->company_name }}</td>
                                <td>{{ $user->register_action }}</td>
                                <td>{{ $user->career_job }}</td>
                                <td>{{ $user->hope_job }}</td>
                                <td class="text-center">{{ $workingStyle }}</td>
                                <td>{{ $user->mypage_at }}</td>
                                <td class="text-center">{{ $user->match_count }}</td>
                                <td class="text-center">{{ $user->update_count }}</td>
                                <td class="text-center">{{ $user->detail_count }}</td>
                                <td class="text-center">{{ $user->offer }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 🔸 旧しごとナビ登録者 --}}
    @if ($oldUsers->count())
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-warning bg-gradient text-dark fs-6 fw-bold">
            <i class="fas fa-history me-2"></i>旧しごとナビ登録者マッチング等
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light text-center align-middle">
                        <tr class="small text-nowrap">
                            <th>No</th><th>登録日時</th><th>スタッフコード</th><th>氏名</th><th>誕生日</th><th>年齢</th>
                            <th>性別</th><th>都道府県</th><th>市町村</th><th>電話</th><th>Mail</th>
                            <th>直近勤務先</th><th>登録アクション</th><th>経験職種</th><th>希望職種</th><th>希望勤務形態</th>
                            <th>Mypageアクセス</th><th>求人一覧</th><th>絞込数</th><th>詳細閲覧</th><th>オファー</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach ($oldUsers as $j => $user)
                            @php
                                $workingStyle = $user->yearly_income_min > 0 ? '正社員' : ($user->hourly_income_min > 0 ? '派遣' : '');
                            @endphp
                            <tr class="text-nowrap">
                                <td class="text-center">{{ $j + 1 }}</td>
                                <td>{{ $user->created_at }}</td>
                                <td>
                                    <a href="{{ route('agent.userDetail', ['staff_code' => $user->staff_code]) }}"
                                       class="text-primary fw-bold text-decoration-none">
                                        {{ $user->staff_code }}
                                    </a>
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($user->birthday)->format('Y-m-d') }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($user->birthday)->age }}</td>
                                <td class="text-center">{{ $user->sex }}</td>
                                <td>{{ $user->prefecture }}</td>
                                <td>{{ $user->city }}</td>
                                <td>{{ $user->tel }}</td>
                                <td>{{ $user->mail_address }}</td>
                                <td>{{ $user->company_name }}</td>
                                <td>{{ $user->register_action }}</td>
                                <td>{{ $user->career_job }}</td>
                                <td>{{ $user->hope_job }}</td>
                                <td class="text-center">{{ $workingStyle }}</td>
                                <td>{{ $user->mypage_at }}</td>
                                <td class="text-center">{{ $user->match_count }}</td>
                                <td class="text-center">{{ $user->update_count }}</td>
                                <td class="text-center">{{ $user->detail_count }}</td>
                                <td class="text-center">{{ $user->offer }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('agent.usersearch') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> 戻る
        </a>
    </div>
</div>
@endsection
