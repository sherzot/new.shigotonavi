@extends('layouts.top')

@section('title', '履歴書')
@section('content')
    <section class="container py-3 my-3">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-9">
                <div class=" py-3">
                    <!-- ✅ Session Messages -->
                    @if (session('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success text-center">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger text-center">{{ session('error') }}</div>
                    @endif

                    <div class="mt-3">
                        <p class="text-main-theme fs-f24 text-center">学歴・職歴・自己PR・志望動機などの内容を入力して、履歴書または職務経歴書を作成</p>
                    </div>
                    <div>
                        <p>
                            転職活動に必要な履歴書や職務経歴書が「学歴・職歴」「志望動機」などの項目に沿って入力するだけで作成できます。<br>
                            豊富なテンプレートをご用意しています。印刷やメール添付に最適なPDF形式や、パソコンでの管理に便利なExcel形式<br>での出力が可能です。
                            写真登録機能によって、証明写真付きの履歴書が簡単に作成できます。
                        </p>
                    </div>
                    {{--  <!-- ✅ 学歴・職歴入力 -->  --}}
                    <div class="row mt-4 g-3 px-3">
                        <div class="col-md-6">
                            <a href="{{ route('educate-history') }}" class="btn btn-outline-primary w-100">
                                <i class="fa-solid fa-file text-main-theme"></i>
                                学歴・職歴入力
                            </a>
                        </div>
                        <div class="col-md-6">
                            {{--  <a href="{{ route('self_pr') }}" class="btn btn-outline-primary w-100">
                                <i class="fa-solid fa-user text-main-theme"></i>
                                自己PR 志望動機
                            </a>  --}}
                            <a class="btn btn-outline-primary w-100" href="{{ route('matchings.create') }}">
                                <i class="fa-solid fa-file-pen text-main-theme"></i> 
                                基本情報変更
                            </a>
                        </div>
                        
                    </div>
                    <div class="row mt-4 g-3 px-3">
                        <div class="col-md-6">
                            <a href="{{ route('self_pr') }}" class="btn btn-outline-primary w-100">
                                <i class="fa-solid fa-user text-main-theme"></i>
                                自己PR 志望動機
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('upload.form') }}" class="btn btn-outline-primary w-100">
                                <i class="fa-solid fa-image-portrait text-main-theme"></i>
                                証明写真の登録
                            </a>
                        </div>
                    </div>
                    {{--  <p class="row mt-4 px-4">基本情報・学歴・職歴・自己PR・証明写真などの内容を入力してから、履歴書または、職務経歴書ダウンロード</p>  --}}
                    <div class="mt-4 px-3">
                        <li>履歴書または職務経歴書を自分に最適な形式でダウンロードしてください。</li>
                    </div>
                    <!-- ✅ 履歴書ダウンロード -->
                    <div class="mt-4 px-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="{{ route('export') }}" class="btn btn-outline-primary w-100">
                                    <i class="fa-solid fa-file-arrow-down text-main-theme"></i>
                                    履歴書EXCELダウンロード
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('pdf') }}" class="btn btn-outline-primary w-100">
                                    <i class="fa-solid fa-file-arrow-down text-main-theme"></i>
                                    履歴書PDFダウンロード
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ 職務経歴書ダウンロード -->
                    <div class="mt-4 px-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="careersheet" class="btn btn-outline-primary w-100">
                                    <i class="fa-solid fa-file-arrow-down text-main-theme"></i>
                                    職務経歴書EXCELダウンロード
                                </a>

                            </div>
                            <div class="col-md-6">
                                <a href="careerpdf" class="btn btn-outline-primary w-100">
                                    <i class="fa-solid fa-file-arrow-down text-main-theme"></i>
                                    職務経歴書PDFダウンロード
                                </a>
                            </div>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </section>
@endsection
