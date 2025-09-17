@if (!isset($matchingJobs) || (is_iterable($matchingJobs) && count($matchingJobs) <= 6))
    <h3 class="text-center mb-4 mt-5 pt-3">マッチングされた求人票一覧<strong>: <span class="text-main-theme"
                id="total-jobs">{{ $matchingJobs->total() }} 件</span></strong></h3>
    <h5 class="text-center mb-4 mt-5 pt-0 text-main-theme">あなたにベストな求人票を選んでオファーボタンを押してください！</h5>
@endif
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
                    @if (isset($checkboxOptions) && !empty($checkboxOptions))
                        <div class="tags">
                            @if (!empty($job->selectedFlagsArray) && count($job->selectedFlagsArray) > 0)
                                <div class="d-flex flex-wrap">
                                    @foreach ($job->selectedFlagsArray as $flag)
                                        @if (array_key_exists($flag, $checkboxOptions))
                                            <span
                                                class="badge bg-light text-dark border border-secondary me-2 mb-2 p-1">
                                                {{ $checkboxOptions[$flag] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p>&nbsp;</p>
                            @endif
                        </div>
                    @else
                        {{--  <p>!</p>  --}}
                    @endif

                </div>


                <div class="card-footer bg-white border-top-0 mt-auto">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('matchings.detail', ['id' => $job->id, 'staffCode' => auth()->user()->staff_code]) }}"
                            class="btn btn-primary btn-sm">求人票を見る</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
@endif
