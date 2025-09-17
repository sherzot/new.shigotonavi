@extends('layouts.top')

@section('title', 'マイページ')

@section('content')
<section class="container py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-9">

            {{-- ✅ Flash messages --}}
            @if (session('message') || session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('message') ?? session('success') }}
                    @if (session('jobDetail'))
                        <br>{{ session('jobDetail') }}
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- ✅ Error --}}
            @if ($errors->has('error'))
                <div class="alert alert-danger">{{ $errors->first('error') }}</div>
            @endif

            {{-- ✅ Hero Image --}}
            <div class="mb-4">
                <img src="{{ asset('img/canpin-PC.webp') }}" class="img-fluid rounded shadow-sm d-none d-md-block w-100" alt="PC">
                <img src="{{ asset('img/canpin-SP.webp') }}" class="img-fluid rounded shadow-sm d-block d-md-none" alt="SP">
            </div>

            {{-- ✅ Search Button --}}
            <div class="text-center mb-5">
                <a href="{{ route('signin') }}#searchJob" class="btn btn-main-theme btn-lg rounded-pill px-5 shadow-sm">
                    しごとを探す
                </a>
            </div>

            {{-- ✅ Resume Creation Section --}}
            <div class="bg-white rounded-4 shadow-sm p-4 mb-5 border border-light">
                <h4 class="text-main-theme fw-semibold mb-3">履歴書または職務経歴書作成</h4>
                <p class="d-none d-sm-block">「しごとナビ」では、転職サポートだけではなく、手書きの履歴書や職務経歴書作成にかかる手間を軽減するため、<br>登録者全員に無料で作成できるウェブサービスを提供しています。 これにより、志望動機やアピールポイントを<br>変更する際のミスや時間の浪費を防げます。</p>
                <p class="d-block d-sm-none">「しごとナビ」では、転職サポートだけ<br>ではなく、手書きの履歴書や職務経歴書<br>作成にかかる手間を軽減するため、<br>登録者全員に無料で作成できるウェブ<br>サービスを提供しています。 これにより、<br>志望動機やアピールポイントを変更する<br>際のミスや時間の浪費を防げます。</p>
                <p class="mb-3 d-none d-sm-block">「コンプリ（コンビニプリント）」を使えば、各種履歴書を以下のコンビニで印刷できます:</p>
                <p class="mb-3 d-block d-sm-none">「コンプリ（コンビニプリント）」を<br>使えば、各種履歴書を以下のコンビニで印刷<br>できます:</p>
                <div class="d-flex flex-wrap justify-content-start gap-3 mb-4">
                    @foreach (['seven.png', 'family.png', 'lowson.webp', 'ministop.jpg'] as $img)
                        <img src="{{ asset('img/' . $img) }}" class="img-fluid rounded shadow" style="width: 60px;">
                    @endforeach
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="{{ route('resume.basic-info') }}" class="btn btn-outline-primary w-100">履歴書と職務経歴書作成</a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('matchings.create') }}" class="btn btn-outline-primary w-100">基本情報変更</a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('export') }}" class="btn btn-outline-primary w-100">履歴書EXCELダウンロード</a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('pdf') }}" class="btn btn-outline-primary w-100">履歴書PDFダウンロード</a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ url('careersheet') }}" class="btn btn-outline-primary w-100">職務経歴書EXCELダウンロード</a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ url('careerpdf') }}" class="btn btn-outline-primary w-100">職務経歴書PDFダウンロード</a>
                    </div>
                </div>
            </div>

            {{-- ✅ マイページ統計情報 --}}
            @php
                use Illuminate\Support\Facades\DB;
                $staffCode = Auth::user()->staff_code;
                $log = DB::table('log_person_signin')->where('staff_code', $staffCode)->first();
                $jobTypeName = '-';
                if ($log && $log->search_big_class_code && $log->search_job_category) {
                    $jobType = DB::table('master_job_type')
                        ->where('big_class_code', $log->search_big_class_code)
                        ->where('middle_class_code', $log->search_job_category)
                        ->select('big_class_name', 'middle_clas_name')
                        ->first();
                    if ($jobType) {
                        $jobTypeName = $jobType->big_class_name . '、' . $jobType->middle_clas_name;
                    }
                }
                $flags = json_decode($log->filter_flags ?? '[]', true);
                $flagLabels = [
                    'inexperienced_person_flag' => '未経験者OK', 'balancing_work_flag' => '仕事と生活のバランス',
                    'ui_turn_flag' => 'UIターン', 'many_holiday_flag' => '休日120日', 'flex_time_flag' => 'フルリモート',
                    'near_station_flag' => '駅近5分', 'no_smoking_flag' => '禁煙分煙', 'newly_built_flag' => '新築',
                    'landmark_flag' => '高層ビル', 'company_cafeteria_flag' => '社員食堂', 'short_overtime_flag' => '残業少なめ',
                    'maternity_flag' => '産休育休', 'dress_free_flag' => '服装自由', 'mammy_flag' => '主婦(夫)',
                    'fixed_time_flag' => '固定時間勤務', 'short_time_flag' => '短時間勤務', 'handicapped_flag' => '障がい者歓迎',
                    'rent_all_flag' => '住宅全額補助', 'rent_part_flag' => '住宅一部補助', 'meals_flag' => '食事付き',
                    'meals_assistance_flag' => '食事補助', 'training_cost_flag' => '研修費用支給',
                    'entrepreneur_cost_flag' => '起業補助', 'money_flag' => '金銭補助', 'land_shop_flag' => '店舗提供',
                    'find_job_festive_flag' => '就職祝金', 'license_acquisition_support_flag' => '資格取得支援あり'
                ];
            @endphp
            @if ($log)
                <div class="bg-white rounded-4 shadow-sm p-4 mb-5 border border-light">
                    <h4 class="fw-semibold text-start text-main-theme mb-4">
                        マイページ統計情報
                        <span class="text-secondary d-inline d-sm-none"><br>{{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）</span>
                        <span class="text-secondary d-none d-sm-inline">：{{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）</span>
                    </h4>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                🔍 最初の検索件数<br>
                                <small class="text-muted">職種: {{ $jobTypeName }}</small>
                            </div>
                            <span class="badge bg-primary fs-6">{{ $log->match_count ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                🎯 フィルター後の検索件数<br>
                                <small class="text-muted">給料: {{ $log->filter_salary ?? '-' }}, 時給: {{ $log->filter_hourly_wage ?? '-' }}, 勤務地: {{ $log->filter_location ?? '-' }}</small><br>
                                @if (!empty($flags))
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        @foreach ($flags as $flag)
                                            <span class="badge bg-light border text-secondary">{{ $flagLabels[$flag] ?? $flag }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <small class="text-muted">補足条件: -</small>
                                @endif
                            </div>
                            <span class="badge bg-info text-dark fs-6">{{ $log->update_count ?? 0 }}</span>
                        </li>
                        <!-- ✅ 求人詳細ページ閲覧数 -->
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                👁️ <strong>求人詳細ページ閲覧数</strong><br>
                                <span class="small text-muted">
                                    最後に見た:
                                    @if (!empty($log->last_viewed_job))
                                    <a href="https://mch.shigotonavi.co.jp/jobs/detail/{{ $log->last_viewed_job }}" class="text-decoration-none">
                                        {{ $log->last_viewed_job }}
                                    </a>
                                    @else
                                    -
                                    @endif
                                </span>
                            </div>
                            <span class="badge bg-info text-dark fs-6">{{ $log->detail_count ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            🏠 マイページアクセス数
                            <span class="badge bg-secondary fs-6">{{ $log->mypage ?? 0 }}</span>
                        </li>
                    </ul>
                </div>
            @endif
                {{-- ✅ Search Button --}}
            <div class="text-center mb-5">
                <a href="{{ route('signin') }}#searchJob" class="btn btn-main-theme btn-lg rounded-pill px-5 shadow-sm">
                    しごとを探す
                </a>
            </div>
            {{-- ✅ 最近見た求人 --}}
            @if (isset($viewedJobs) && $viewedJobs->count() > 0)
            <div class="bg-white rounded-4 shadow-sm p-4 border border-light mb-5">
                <h4 class="fw-semibold text-main-theme mb-4 text-start">最近見た求人（閲覧履歴）</h4>
                <div class="row g-4">
                    @foreach ($viewedJobs as $job)
                    <div class="col-12">
                        <div class="border rounded-3 p-3 d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 bg-light">
                            <div class="flex-grow-1">
                                <a href="{{ url('jobs/detail/' . $job->order_code) }}" class="text-decoration-none text-secondary fw-bold">
                                    {{ $job->job_type_detail }}
                                    <span class="badge bg-secondary ms-1">
                                        @switch($job->order_type)
                                            @case(1) 派遣 @break
                                            @case(2) 正社員 @break
                                            @case(3) 契約社員 @break
                                            @default -
                                        @endswitch
                                    </span>
                                </a>
                                <div class="small text-muted mt-1">
                                    {{--  {{ $job->company_name_k }}<br>  --}}
                                    {{ \Carbon\Carbon::parse($job->update_at)->format('Y年m月d日 H:i') }} に閲覧
                                </div>
                            </div>
                            <div class="mt-2 mt-md-0">
                                <a href="{{ url('jobs/detail/' . $job->order_code) }}" class="btn btn-sm btn-outline-primary w-100">
                                    詳細を見る
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {!! $viewedJobs->appends(request()->except('page'))->links('vendor.pagination.default') !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
