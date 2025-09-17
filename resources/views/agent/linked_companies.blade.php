@extends('layouts.layout')

@section('title', '関連企業')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <a href="{{ route('agent.dashboard') }}"><img class="img-responsive" src="{{ asset('img/logo02.png') }}"
                        alt="#" style="width: 150px;" /></a>
            </div>
        </div>
    </div>
    <div class="container-fluid mt-4">
        <h1 class="text-center mb-4">関連企業</h1>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->has('msg'))
            <div class="alert alert-danger">
                <strong>{{ $errors->first('msg') }}</strong><br>
                @if ($errors->has('error_detail'))
                    <small class="text-danger">[詳細] {{ $errors->first('error_detail') }}</small>
                @endif
            </div>
        @endif
        <!-- 検索フォーム -->
        <form method="GET" action="{{ route('agent.linked_companies') }}" class="mb-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" name="query" class="form-control rounded-start"
                            placeholder="企業コード・企業名・担当者コード・担当者名で検索" value="{{ request('query') }}">
                        <button type="submit" class="btn btn-primary rounded-end">検索</button>
                    </div>
                    <p class="form-text text-muted mt-1 text-center">
                        企業コード、企業名、または担当者コード、担当者名で検索してください。
                    </p>
                </div>
            </div>
        </form>

        <!-- 結果リスト -->
        @if (request()->has('query'))
            @if ($linkedCompanyCodes->isNotEmpty())
                <div class="row">
                    @foreach ($linkedCompanyCodes as $companyCode)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-subtitle mb-2 text-muted fw-bold">企業コード</h5>
                                    <p class="fw-bold text-primary">{{ $companyCode->company_code }}</p>

                                    <h5 class="card-subtitle mb-2 text-muted fw-bold">企業名</h5>
                                    <p class="fw-bold">{{ $companyCode->company_name_k }}</p>

                                    <h5 class="card-subtitle mb-2 text-muted fw-bold">担当者名</h5>
                                    <p class="fw-bold">{{ $companyCode->lis_person_name }}</p>

                                    <h5 class="card-subtitle mb-2 text-muted fw-bold">担当者コード</h5>
                                    <p class="fw-bold">{{ $companyCode->lis_person_code }}</p>

                                    <hr>

                                    <p class="mb-1"><strong class="fw-bold">契約締結日:</strong>
                                        {{ \Carbon\Carbon::parse($companyCode->keiyaku_ymd)->format('Y-m-d') }}</p>
                                    <p><strong class="fw-bold">更新日:</strong>
                                        {{ \Carbon\Carbon::parse($companyCode->updated_at)->format('Y-m-d') }}</p>
                                    <a href="{{ route('agent.company.detail', ['companyCode' => $companyCode->company_code]) }}" class="btn btn-outline-primary btn-sm mt-2">
                                        詳細を見る
                                    </a>                            
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (method_exists($linkedCompanyCodes, 'links'))
                    <div class="d-flex justify-content-center mt-4">
                        {{ $linkedCompanyCodes->appends(['query' => request('query')])->links('vendor.pagination.default') }}
                    </div>
                @endif
            @else
                <div class="alert alert-warning text-center">
                    該当する企業が見つかりませんでした。
                </div>
            @endif
        @endif

        {{--  <!-- Pagination for Companies -->
        @if (request('query') && method_exists($linkedCompanyCodes, 'links'))
            <div class="mt-4 d-flex justify-content-center">
                {{ $linkedCompanyCodes->appends(['query' => request('query')])->links('vendor.pagination.default') }}
            </div>
        @endif  --}}

    </div>
@endsection
