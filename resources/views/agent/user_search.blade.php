@extends('layouts.layout')

@section('title', 'スタッフ検索')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <a href="{{ route('agent.dashboard') }}"><img class="img-responsive" src="{{ asset('img/logo02.png') }}"
                        alt="#" style="width: 150px;" /></a>
            </div>
        </div>
    </div>
    <div class="container-flued mt-5">
        <div class="row justify-content-start">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg p-4">
                    <h4 class="text-center mb-4 text-primary">スタッフ検索</h4>
                    <form method="POST" action="{{ route('agent.usersearch') }}">
                        @csrf

                        <!-- スタッフコード -->
                        <div class="mb-3">
                            <label for="staff_code" class="form-label fw-bold">スタッフコード:</label>
                            <input type="text" name="staff_code" id="staff_code" class="form-control border-primary">
                        </div>

                        <div class="fs-f22 py-2">または</div>

                        <!-- メールアドレス -->
                        <div class="mb-3">
                            <label for="mail" class="form-label fw-bold">メールアドレス:</label>
                            <input type="text" name="mail" id="mail" class="form-control border-primary">
                        </div>

                        <!-- 検索ボタン -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">検索</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row justify-content-start mt-5">
            <!-- 20250317 -->
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg p-4">
                    <h4 class="text-center mb-4 text-primary">日付検索</h4>
                    <form method="POST" action="{{ route('agent.listuser') }}">
                        @csrf

                        <!-- スタッフコード -->
                        <div class="mb-3">
                            <label for="search_date" class="form-label fw-bold">登録日</label>
                            <input type="date" name="search_date" class="form-control border-primary"
                                value="{{ date('Y-m-d', strtotime('-1 day')) }}">
                        </div>
                        <!-- 検索ボタン -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">検索</button>
                        </div>
                    </form>
                </div>
            </div>
            <!--  20250317  -->
        </div>
        <div class="row justify-content-start mt-5">
            <!-- 20250321 -->
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg p-4">
                    <h4 class="text-center mb-4 text-primary">日付ごとExceldownLoad</h4>
                    <form method="POST" action="{{ route('agent.dailysheet') }}">
                        @csrf

                        <!-- スタッフコード -->
                        <div class="mb-3">
                            <label for="search_date" class="form-label fw-bold">登録日</label>
                            <input type="date" name="select_date" class="form-control border-primary"
                                value="{{ date('Y-m-d', strtotime('-1 day')) }}">
                        </div>
                        <!-- 検索ボタン -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">ダウンロード</button>
                        </div>
                    </form>
                </div>
            </div>
            <!--  20250321  -->
        </div>



        <div class="justify-content-start mt-5">
            <a href="{{ route('agent.dashboard') }}" class="btn btn-primary btn-lg">戻る</a>
        </div>
    </div>
@endsection
