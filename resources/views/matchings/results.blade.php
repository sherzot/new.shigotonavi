@extends('layouts.top')

@section('title', '求人票一覧')
@section('content')
    <div class="container">
        <div class="row g-4">
            <h3 class="text-center mb-4">マッチングされた求人票一覧</h3>
            
            @foreach ($matchingResults as $job)
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 d-flex flex-column">
                        <div class="card-body">
                            <h6 class="card-title text-primary fw-400">
                                {{ $job->pr_title1 ?? ' ' }}
                            </h6>

                            <p class="card-title" style="color: #ea544a;">
                                {{ $job->job_type_detail ?? '詳細なし' }}
                            </p>

                            <p class="card-text mb-2">
                                <strong>給与例:</strong>
                                @if ($job->hourly_income_min > 0)
                                    時給
                                    {{ number_format($job->hourly_income_min) }}円{{ $job->hourly_income_max > 0 ? '〜' . number_format($job->hourly_income_max) . '円' : '〜' }}
                                @elseif($job->yearly_income_min > 0)
                                    年収
                                    {{ number_format($job->yearly_income_min) }}円{{ $job->yearly_income_max > 0 ? '〜' . number_format($job->yearly_income_max) . '円' : '〜' }}
                                @else
                                    未設定
                                @endif
                            </p>

                            <p class="card-text">
                                <strong>勤務地:</strong> {{ $job->prefecture_name ?? '情報なし' }}
                            </p>

                            <div class="tags">
                                @if (!empty($job->selectedFlagsArray))
                                    <div class="d-flex flex-wrap">
                                        @foreach ($job->selectedFlagsArray as $flag)
                                            @if (array_key_exists($flag, $checkboxOptions))
                                                <span
                                                    class="badge bg-white text-secondary border border-secondary me-2 mb-2 p-1">
                                                    {{ $checkboxOptions[$flag] }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <p>&nbsp;</p>
                                @endif
                            </div>
                        </div>

                        <!-- Footer section -->
                        <div class="card-footer bg-white border-top-0 mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('matchings.detail', ['id' => $job->id, 'staffCode' => auth()->user()->staff_code]) }}"
                                    class="btn btn-primary btn-sm">求人票を見る</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $matchingResults->appends(['query' => request('query')])->links('vendor.pagination.default') }}
        </div>
        @if ($matchingResults->isEmpty())
            <!-- 結果がない場合 -->
            <div class="row my-5 justify-content-center">
                <a href="{{ route('matchings.update') }}" class="btn btn-md active btn-main-theme">
                    マッチング条件を変更してみる
                </a>
            </div>
        @else
            <div class="container text-center my-4">
                <div class="row g-2 justify-content-center">
                    <div class="col-12 col-sm-6 d-flex justify-content-center">
                        <a href="{{ route('matchings.update') }}" class="btn btn-md btn-main-theme w-100">
                            マッチング条件を変更してみる
                        </a>
                    </div>
                    <div class="col-12 col-sm-6 d-flex justify-content-center" style="align-items: center">
                        <a href="{{ route('mypage') }}" class="btn btn-md btn-main-theme w-100">
                            マイページに戻る
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
