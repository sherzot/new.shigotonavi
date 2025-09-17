@extends('layouts.layout')

@section('title', '求人リスト')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <a href="{{ route('company.dashboard') }}">
                    <img class="img-responsive" src="{{ asset('img/logo02.png') }}" alt="#" style="width: 150px;" />
                </a>
            </div>
        </div>
    </div>

    <!-- Job Listings -->
    <div class="row column1">
        <div class="col-md-12">
            <div class="white_shd full margin_bottom_30">
                <div class="full graph_head">
                    <div class="heading1 margin_0">
                        <h2>求人リスト</h2>
                    </div>
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                </div>
                <div class="full price_table padding_infor_info">
                    <div class="row">
                        @if (isset($jobs) && count($jobs) > 0)
                            @foreach ($jobs as $job)
                                <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 profile_details margin_bottom_30">
                                    <div class="contact_blog">
                                        <div class="row">
                                            <p class="text-end col-6">
                                                @if ($job->public_flag == 1)
                                                    <span class="badge bg-success text-white p-2">掲載中</span>
                                                @elseif ($job->public_flag == 0)
                                                    <span class="badge bg-danger text-white p-2">非掲載</span>
                                                @else
                                                    <span class="badge bg-secondary text-white p-2">不明</span>
                                                @endif
                                            </p>
                                            <p class="text-end col-6">
                                                @if ($job->order_type == 1)
                                                    <span class="badge bg-primary text-white p-2">派遣</span>
                                                @elseif ($job->order_type == 2)
                                                    <span class="badge bg-primary text-white p-2">紹介</span>
                                                @elseif ($job->order_type == 3)
                                                    <span class="badge bg-primary text-white p-2">紹介予定派遣</span>
                                                @else
                                                    <span class="badge bg-secondary text-white p-2">不明</span>
                                                @endif
                                            </p>
                                        </div>
                                        <p>({{ $job->order_code }})</p>
                                        <h4 class="brief"> {{ $job->job_type_detail }}</h4>
                                        <p>作成日:　<span>{{ \Carbon\Carbon::parse($job->created_at)->format('Y-m-d') }}</span>
                                        </p>
                                        <p>更新日:　<span>{{ \Carbon\Carbon::parse($job->update_at)->format('Y-m-d') }}</span>
                                        </p>

                                        <div class="contact_inner">
                                            <div class="left">
                                                <h3>{{ $job->company_name }}</h3>
                                                <p>
                                                    <i class="fa-solid fa-map-location"></i> :
                                                    {{ $job->all_prefectures ?? 'N/A' }} {{ $job->city }}
                                                    {{ $job->town }}
                                                </p>
                                            </div>
                                            <div class="bottom_list">

                                                <div class="right_button">
                                                    <a href="{{ route('jobs.job_detail', ['id' => $job->order_code]) }}"
                                                        class="btn btn-primary btn-xs">
                                                        <i class="fa-regular fa-file"></i> 詳細を見る
                                                    </a>
                                                    <span class="badge" style="color: #ea544a;">
                                                        <i class="fas fa-eye"></i> {{ $jobs->browse_cnt ?? 0 }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="mt-4 d-flex justify-content-center m-auto">
                                {{ $jobs->appends(['query' => request('query')])->links('vendor.pagination.bootstrap-4') }}
                            </div>
                        @else
                            <p class="text-center">現在、求人情報がありません。</p>
                        @endif
                    </div>
                    
                </div>

            </div>
        </div>
    </div>
@endsection
