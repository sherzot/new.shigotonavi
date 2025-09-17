@extends('layouts.layout')

@section('title', '関連求人')

@section('content')
<div class="row column_title">
    <div class="col-md-12">
        <div class="page_title">
            <a href="{{ route('agent.dashboard') }}"><img class="img-responsive" src="{{ asset('img/logo02.png') }}" alt="#" style="width: 150px;" /></a>
        </div>
    </div>
</div>
<div class="container-fluid mt-4">
    <h1 class="text-center mb-4">関連求人</h1>

    <!-- 検索フォーム -->
    <form method="GET" action="{{ route('agent.linked_jobs') }}" class="mb-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="input-group shadow-sm">
                    <input type="text" name="query" class="form-control form-control-lg border-dark" value="{{ request('query') }}">
                    <button type="submit" class="btn btn-primary btn-lg px-4">検索</button>
                </div>
                <p class="form-text text-muted mt-2 text-center" style="font-size: 18px;">
                    以下の項目で検索できます：<br>
                    <span class="badge bg-light text-primary border">拠点名</span>
                    <span class="badge bg-light text-dark border">求人コード</span>
                    <span class="badge bg-light text-dark border">求人タイトル</span>
                    <span class="badge bg-light text-dark border">企業コード</span>
                    <span class="badge bg-light text-dark border">企業名</span>
                    <span class="badge bg-light text-dark border">担当者コード</span>
                    <span class="badge bg-light text-dark border">担当者名</span>
                    <span class="badge bg-light text-dark border">勤務地（都道府県）</span>
                    <span class="badge bg-light text-dark border">職種名</span>
                    <span class="badge bg-light text-dark border">スキル</span>
                    <span class="badge bg-light text-dark border">作成日</span>
                </p>
            </div>
        </div>
    </form>
    @if (request()->filled('query'))
    <div class="mb-1 text-center">
        <span class="badge bg-primary text-white px-4 py-3 m-1" style="font-size: 20px;">合計: {{ $totalCount }} 件</span>
        {{-- 🔹 応募可能求人数 --}}
        <span class="badge bg-success text-white px-4 py-3 m-1" style="font-size: 20px;">
            応募可能求人票数: {{ $satisfyingNeedsCount }} 件
        </span>
        <span class="badge bg-primary text-white px-4 py-3 m-1" style="font-size: 20px;">受注中: {{ $publicCount }} 件</span>
        <span class="badge bg-primary text-white px-4 py-3 m-1" style="font-size: 20px;">受注終了: {{ $endCount }} 件</span>
        <span class="badge bg-info text-white px-4 py-3 m-1" style="font-size: 20px;">派遣: {{ $orderType1 }} 件</span>
        <span class="badge bg-primary text-white px-4 py-3 m-1" style="font-size: 20px;">紹介: {{ $orderType2 }} 件</span>
        <span class="badge bg-secondary text-white px-4 py-3 m-1" style="font-size: 20px;">紹介予定派遣: {{ $orderType3 }} 件</span>
        <span class="badge bg-primary text-white px-4 py-3 m-1" style="font-size: 20px;">掲載: {{ $publishedCount }} 件</span>
        <span class="badge bg-primary  text-white px-4 py-3 m-1" style="font-size: 20px;">非掲載: {{ $expiredCount }} 件</span>
    </div>
    @endif
     @php
        $query = request()->query('query', '');
        $isValidExpiredSearch = preg_match('/^(.*?)\s+掲載期限切れ(?:\s+(\d{4}-\d{2}-\d{2})(?:~(\d{4}-\d{2}-\d{2}))?)?$/u', $query);
        $newLimitDay = \Carbon\Carbon::now()->addDays(14)->format('Y年m月d日');
    @endphp

    @if(session('success'))
    <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    @if ($linkedJobs->count() > 0 && $isValidExpiredSearch)
        <div class="text-center my-4">
            <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#extendModal">
                掲載期限を {{ $newLimitDay }} まで延長
            </button>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="extendModal" tabindex="-1" aria-labelledby="extendModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-warning">
                    <div class="modal-header">
                        <h5 class="modal-title text-warning fw-bold" id="extendModalLabel">掲載期限の延長確認</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                    </div>
                    <div class="modal-body text-center">
                        現在表示されている <strong>{{ $linkedJobs->total() }}</strong> 件の求人票の掲載期限を<br>
                        <strong class="text-danger">{{ $newLimitDay }}</strong> まで延長します。<br>
                        よろしいですか？
                    </div>
                    <div class="modal-footer justify-content-center">
                        <form action="{{ route('agent.extend_public_limit') }}" method="POST">
                            @csrf
                            <input type="hidden" name="query" value="{{ $query }}">
                            <button type="submit" class="btn btn-danger">はい、延長する</button>
                        </form>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        {{-- Agar query bo‘lsa va natija topilmagan bo‘lsa --}}
        @if (request()->filled('query') && $linkedJobs->isEmpty())
        <div class="alert alert-warning text-center">
            該当する求人が見つかりませんでした。
        </div>
        @endif

        @foreach ($linkedJobs as $job)
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">求人コード: {{ $job->order_code }}</h5>
                    <div class="row">
                        <p class="text-start col-4">
                            @if ($job->public_flag == 1)
                            <span class="badge bg-success text-white p-2">受注中・掲載中</span>
                            @elseif ($job->public_flag == 0)
                            <span class="badge bg-danger text-white p-2">受注完・非掲載</span>
                            @else
                            <span class="badge bg-secondary text-white p-2">不明</span>
                            @endif
                        </p>
                        <p class="text-center col-4">
                            @if ($job->order_type == 1)
                            <span class="badge bg-info text-white p-2">派遣</span>
                            @elseif ($job->order_type == 2)
                            <span class="badge bg-primary text-white p-2">紹介</span>
                            @elseif ($job->order_type == 3)
                            <span class="badge bg-secondary text-white p-2">紹介予定派遣</span>
                            @else
                            <span class="badge bg-secondary text-white p-2">不明</span>
                            @endif
                        </p>
                        <p class="text-end col-4">
                            <span class="badge {{ $job->is_expired ? 'bg-danger text-white' : 'bg-success text-white' }} p-2">
                                {{ $job->is_expired ? '掲載期限切れ' : '掲載期間中' }}
                            </span>
                        </p>
                    </div>
                    <p>作成日:　<span>{{ \Carbon\Carbon::parse($job->created_at)->format('Y-m-d') }}</span></p>
                    <p>掲載期間:　<span>{{ \Carbon\Carbon::parse($job->public_limit_day)->format('Y-m-d') }}</span></p>
                    <p>更新日:　<span>{{ \Carbon\Carbon::parse($job->update_at)->format('Y-m-d') }}</span></p>
                    <p class="card-text"><strong>求人タイトル:</strong><br> {{ $job->job_type_detail }}</p>
                    <p class="card-text"><strong>企業名:</strong><br> {{ $job->company_name_k }}</p>
                    <p class="card-text"><strong>企業コード:</strong> {{ $job->company_code }}</p>
                    <p class="card-text"><strong>担当者名:</strong><br> {{ $job->employee_name }}</p>
                    <p class="card-text"><strong>担当者コード:</strong> {{ $job->employee_code }}</p>
                    <a href="{{ route('agent.company_job_details', ['order_code' => $job->order_code]) }}" class="btn btn-outline-primary btn-sm">詳細を見る</a>
                    <span class="badge" style="color: #ea544a;">
                        <i class="fas fa-eye"></i> {{ $job->browse_cnt ?? 0 }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if (request()->filled('query') && method_exists($linkedJobs, 'links'))
    <div class="mt-4 d-flex justify-content-center">
        {{ $linkedJobs->appends(['query' => request('query')])->links('vendor.pagination.default') }}
    </div>
    @endif

</div>

@endsection
