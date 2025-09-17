@extends('layouts.layout')

@section('title', 'オファー管理')

@section('content')
    <div class="container-fluid mt-4 w-100">
        <div class="row">
            <div class="col-md-12 text-start">
                <a href="{{ route('agent.dashboard') }}">
                    <img class="img-fluid mb-3" src="{{ asset('img/logo02.png') }}" alt="#" style="width: 150px;" />
                </a>
            </div>
        </div>

        <!-- ✅ セッションメッセージを表示 -->
        @if (session('success'))
            <div class="alert alert-success text-center">
                <p>{{ session('success') }}</p>
                <p><strong>スタッフコード:</strong> {{ session('staff_code') }}</p>
                <p><strong>求人コード:</strong> {{ session('order_code') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger text-center">
                {{ session('error') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <h4 class="text-center text-dark fw-bold mb-4">オファー管理</h4>
            </div>

            <!-- ✅ 有効なオファー (Faol offerlar) -->
            <div class="col-lg-12">
                <div class="card shadow-sm border-success">
                    <div class="card-header bg-success text-white text-center">
                        <i class="fa-solid fa-bell"></i> 有効なオファー <span
                            class="fs-3 font-weight-bold">({{ count($activeOffers) }})</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-success">
                                    <tr>
                                        <th>スタッフコード</th>
                                        <th>求人コード</th>
                                        <th>企業コード</th>
                                        <th>支店コード</th>
                                        <th>担当者コード</th>
                                        <th>担当者名</th>
                                        <th>更新日</th>
                                        <th>ステータス</th>
                                        <th>操作</th>
                                        <th>キャンセル</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activeOffers as $offer)
                                        <tr>
                                            <td>
                                                <a href="{{ route('agent.userDetail', ['staff_code' => $offer->staff_code]) }}"
                                                   class="text-primary">
                                                   {{ $offer->staff_code }}
                                                </a>
                                            </td>                                            
                                            <td><a href="https://mch.shigotonavi.co.jp/agent/company-job-details/{{ $offer->order_code }}"
                                                    class="text-primary">{{ $offer->order_code }}</a></td>
                                            <td>{{ $offer->company_code }}</td>
                                            <td>{{ $offer->branch_code ?? 'N/A' }}</td>
                                            <td>{{ $offer->head_code ?? 'N/A' }}</td>
                                            <td>{{ $offer->head_name ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($offer->update_at)->format('Y-m-d') }}</td>
                                            <td><span class="badge table-success text-dark p-2">オファー中</span></td>

                                            <!-- ✅ Offer tugadi -->
                                            <td>
                                                <form
                                                    action="{{ route('agent.confirmOfferCompletion', ['staff_code' => $offer->staff_code, 'order_code' => $offer->order_code]) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm">オファー完了</button>
                                                </form>
                                            </td>

                                            <!-- ✅ Offer bekor qilish -->
                                            <td>
                                                <form
                                                    action="{{ route('agent.confirmCancelOffer', ['staff_code' => $offer->staff_code, 'order_code' => $offer->order_code]) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">キャンセル確定</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ キャンセルされたオファー-->
            <div class="col-lg-12 mt-4">
                <div class="card shadow-sm border-danger">
                    <div class="card-header bg-danger text-white text-center">
                        <i class="fa-solid fa-bell-slash"></i> キャンセルされたオファー <span
                            class="fs-3 font-weight-bold">({{ count($canceledOffers) }})</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-danger">
                                    <tr>
                                        <th>スタッフコード</th>
                                        <th>求人コード</th>
                                        <th>企業コード</th>
                                        <th>支店コード</th>
                                        <th>担当者コード</th>
                                        <th>担当者名</th>
                                        <th>更新日</th>
                                        <th>ステータス</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($canceledOffers as $offer)
                                        <tr>
                                            <td>
                                                <a href="{{ route('agent.userDetail', ['staff_code' => $offer->staff_code]) }}"
                                                   class="text-primary">
                                                   {{ $offer->staff_code }}
                                                </a>
                                            </td>                                            
                                            <td><a href="https://mch.shigotonavi.co.jp/agent/company-job-details/{{ $offer->order_code }}"
                                                    class="text-primary">{{ $offer->order_code }}</a></td>
                                            <td>{{ $offer->company_code }}</td>
                                            <td>{{ $offer->branch_code ?? 'N/A' }}</td>
                                            <td>{{ $offer->head_code ?? 'N/A' }}</td>
                                            <td>{{ $offer->head_name ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($offer->update_at)->format('Y-m-d') }}</td>
                                            <td><span class="badge table-danger text-dark p-2">キャンセル</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <!-- ✅ オファー完了  -->
            <div class="col-lg-12 mt-4">
                <div class="card shadow-sm border-danger">
                    <div class="card-header bg-danger text-white text-center">
                        <i class="fa-solid fa-bell-slash"></i> オファー完了 <span
                            class="fs-3 font-weight-bold">({{ count($offerCompletion) }})</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-danger">
                                    <tr>
                                        <th>スタッフコード</th>
                                        <th>求人コード</th>
                                        <th>企業コード</th>
                                        <th>支店コード</th>
                                        <th>担当者コード</th>
                                        <th>担当者名</th>
                                        <th>更新日</th>
                                        <th>ステータス</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($offerCompletion as $offer)
                                        <tr>
                                            <td>
                                                <a href="{{ route('agent.userDetail', ['staff_code' => $offer->staff_code]) }}"
                                                   class="text-primary">
                                                   {{ $offer->staff_code }}
                                                </a>
                                            </td> 
                                            <td><a href="https://mch.shigotonavi.co.jp/agent/company-job-details/{{ $offer->order_code }}"
                                                    class="text-primary">{{ $offer->order_code }}</a></td>
                                            <td>{{ $offer->company_code }}</td>
                                            <td>{{ $offer->branch_code ?? 'N/A' }}</td>
                                            <td>{{ $offer->head_code ?? 'N/A' }}</td>
                                            <td>{{ $offer->head_name ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($offer->update_at)->format('Y-m-d') }}</td>
                                            <td><span class="badge table-danger text-dark p-2">オファー完了</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
