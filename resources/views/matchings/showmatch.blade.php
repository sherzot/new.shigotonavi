@extends('layouts.top')

@section('title', 'しごと探し')
@section('content')
<section class="d-flex align-items-center justify-content-center py-0 px-2 my-0">
    <div class="container-flued">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-12">
                <div class="container my-5">
                    {{-- <img src="{{ asset('img/steep.png') }}" class="img-fluid mt-0 mb-3 d-none d-sm-block" alt="Hero Image"> --}}
                    <div class="d-flex justify-content-center align-items-center step-flow">

                        <!-- Step 1 -->
                        <div class="text-center">
                            <div class="step-circle active">①</div>
                            {{--  <div class="step-label">基本情報登録</div>  --}}
                            <div class="step-label">基本情報</div>
                        </div>
                
                        <!-- Line -->
                        <div class="step-line filled"></div>
                
                        <!-- Step 2 -->
                        <div class="text-center">
                            <div class="step-circle active">②</div>
                            {{--  <div class="step-label">希望条件登録</div>  --}}
                            <div class="step-label ">希望条件</div>
                        </div>
                
                        <!-- Line -->
                        <div class="step-line filled"></div>
                
                        <!-- Step 3 -->
                        <div class="text-center">
                            <div class="step-circle">③</div>
                            {{--  <div class="step-label">オファーする</div>  --}}
                            <div class="step-label">オファー</div>
                        </div>
                    </div>
                </div>
                {{-- 📌 ユーザーの条件に一致する求人を表示する --}}
                <div class="row g-4">
                    <div id="loading-message" style="display: none; text-align: center; font-size: 18px; padding: 10px;">
                        🔄 結果を取得中です...
                    </div>


                    <!-- ✅ Session Messages -->
                    @if (session('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif


                    <!-- ✅ Session Messages -->
                    @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        @if (session('mode') === 'regist')
                        <br>仕事内容は {{ session('jobDetail') }}
                        @elseif (session('mode') === 'cancel')
                        <br>求人票内容は {{ session('jobDetail') }} のオファーをキャンセルしました。
                        @endif

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if (!isset($matchingJobs) || (is_iterable($matchingJobs) && count($matchingJobs) <= 6)) {{-- ✅ PCサイズ用 --}} <div class="row">
                       <div class="d-none d-sm-block">
                            <h3 class="text-center mb-4 mt-5 pt-3">
                                条件に合う自動でマッチングされた求人票
                                <span>: <span class="text-main-theme" id="total-jobs">{{ $matchingJobs->total() }} 件 <span class="text-end text-primary small alert alert-light border">{{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）更新</span></span>
                            </h3>
                        </div>
                        {{-- ✅ スマホサイズ用 --}}
                        <h3 class="text-center mb-4 mt-5 pt-3 d-block d-sm-none">
                            条件に合う自動でマッチング <br> された求人票
                             <span>: <span class="text-main-theme" id="total-jobs">{{ $matchingJobs->total() }} 件 <br> <br><span class="text-end text-primary small alert alert-light border">{{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）更新</span></span>
                        </h3>

                        {{-- ✅ 共通部分（PC + SP）--}}
                        <div class="alert alert-light border mt-2 ">

                            {{-- ✅ PCサイズ用 --}}
                            <div class="d-none d-sm-block">
                                <p>
                                    良いなと思う求人票を選んで、<span class="text-main-theme">面談依頼 【オファー】</span>をしましょう！<br>
                                    表示が少ない場合は、<a href="{{ route('matchings.create') }}">登録条件を変更</a> しましょう
                                </p>
                                <p class="mb-0">
                                    【勤務地】、【希望給与】、【年齢】、【職種】、【資格】～の5項目から求人票を自動で検索し表示しています。
                                </p>
                            </div>

                            {{-- ✅ スマホサイズ用 --}}
                            <div class="d-block d-sm-none">
                                <p>
                                    良いなと思う求人票を選んで、<br><span class="text-main-theme">面談依頼【オファー】</span>をしましょう！<br>
                                    表示が少ない場合は、<a href="{{ route('matchings.create') }}">登録条件を変更</a><br>しましょう
                                </p>
                                <p class="mb-0">
                                    【勤務地】【希望給与】【年齢】【職種】<br>【資格】～の5項目から求人票を自動で検索し<br>表示しています。
                                </p>
                            </div>
                        </div>
                    @endif
                </div>



                @if (is_iterable($matchingJobs) && count($matchingJobs) > 0)
                @foreach ($matchingJobs as $job)
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
                                @if (!empty($job->hourly_income_min) && $job->hourly_income_min > 0)
                                時給
                                {{ number_format($job->hourly_income_min) }}円{{ !empty($job->hourly_income_max) ? '〜' . number_format($job->hourly_income_max) . '円' : '〜' }}
                                @elseif(!empty($job->yearly_income_min) && $job->yearly_income_min > 0)
                                年収
                                {{ number_format($job->yearly_income_min) }}円{{ !empty($job->yearly_income_max) ? '〜' . number_format($job->yearly_income_max) . '円' : '〜' }}
                                @else
                                未設定
                                @endif
                            </p>
                            <p class="card-text">
                                <strong>勤務地:</strong> {{ $job->prefecture_name ?? '情報なし' }}
                            </p>
                        </div>

                        <div class="tags px-3">
                            @if (!empty($job->selectedFlagsArray) && count($job->selectedFlagsArray) > 0)
                            <div class="d-flex flex-wrap">
                                @foreach ($job->selectedFlagsArray as $flag)
                                @if (array_key_exists($flag, $checkboxOptions))
                                <span class="badge bg-light text-dark border border-secondary me-2 mb-2 p-1">
                                    {{ $checkboxOptions[$flag] }}
                                </span>
                                @endif
                                @endforeach
                            </div>
                            @else
                            <p>&nbsp;</p>
                            @endif
                        </div>


                        <div class="card-footer bg-white border-top-0 mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('matchings.detail', ['id' => $job->id, 'staffCode' => auth()->user()->staff_code]) }}" class="btn btn-primary btn-sm">求人票を見る</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                @endif
            </div>

            @if (isset($matchingJobs) && $matchingJobs instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-5 d-flex justify-content-center pagination">
                {!! $matchingJobs->appends(request()->except('page'))->links('vendor.pagination.default') !!}
            </div>
            @endif
            <div class="mb-4">
                <label for="supplement_flags" class="form-label">特記事項</label>
                <div class="row">
                    @foreach (array_chunk($checkboxOptions, ceil(count($checkboxOptions) / 4), true) as $column)
                    <div class="col-lg-3 col-md-3 col-sm-6 col-6">
                        @foreach ($column as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input filter-checkbox border-dark" type="checkbox" id="checkbox_{{ $key }}" name="supplement_flags[]" value="{{ $key }}">
                            <label class="form-check-label" for="checkbox_{{ $key }}">
                                {{ $label }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
            <hr>
        </div>
    </div>
    </div>
</section>
<script src="{{ asset('js/filter.js') }}"></script>
@endsection
