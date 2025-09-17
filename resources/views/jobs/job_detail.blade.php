@extends('layouts.layout')

@section('title', '求人詳細')

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
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-10 col-md-10 col-sm-12">
            <div class="card shadow-sm p-4">
                <p class="text-end">
                    @if ($job->public_flag == 1)
                    <span class="badge bg-success text-white p-2">掲載中</span>
                    @elseif ($job->public_flag == 0)
                    <span class="badge bg-danger text-white p-2">非掲載</span>
                    @else
                    <span class="badge bg-secondary text-white p-2">不明</span>
                    @endif
                </p>

                <!-- 会社名 Kompaniya nomi -->
                <h4 class="text-primary fw-bold">{{ $job->job_type_detail }}</h4>

                <!-- 職種 Ish turi -->
                <p><strong>情報コード:</strong> <span class="badge bg-secondary text-white">({{ $job->order_code }})</span>
                </p>

                <hr class="text-danger">

                <!-- 給与例 Ish haqi -->
                <div class="salary-info mb-3">
                    <h5 class="fw-bold"> 給与例:</h5>
                    @if ($desiredSalaryType)
                    <p class="fs-5 text-success">
                        <strong>{{ $desiredSalaryType }}:</strong>
                        {{ isset($salary_min) ? number_format($salary_min) . '円' : '' }}
                        @if (isset($salary_max) && $salary_max > 0)
                        〜 {{ number_format($salary_max) }}円
                        @endif
                    </p>
                    @else
                    <p class="text-muted">給与情報がありません。</p>
                    @endif
                </div>

                <!-- 勤務地 Joylashuv -->
                <div class="location-info mb-3">
                    <h5 class="fw-bold"><i class="fa-solid fa-map-marker-alt"></i> 勤務地情報:</h5>
                    <p>{{ $job->all_prefectures ?? '' }} {{ $job->city ?? '' }} {{ $job->town ?? '' }}
                        {{ $job->address ?? '' }}</p>
                    {{-- <p><strong>勤務地:</strong> </p>  --}}
                </div>

                <!-- Kerakli ko‘nikmalar -->
                <div class="skills-info mb-3">
                    <h5 class="fw-bold"><i class="fa-solid fa-tools"></i> 必要なスキル:</h5>
                    <p>{{ $job->skill_detail ?? '情報なし' }}</p>
                </div>

                <!-- Ish tavsifi -->
                <div class="job-description mb-3">
                    <h5 class="fw-bold"><i class="fa-solid fa-briefcase"></i> 仕事内容:</h5>
                    <p>{{ $job->business_detail }}</p>
                </div>

                <!-- Qo‘shimcha Ish Haqi Tafsilotlari -->
                <div class="other-info p-3 border rounded">
                    <h5 class="fw-bold"><i class="fa-solid fa-info-circle"></i> 特記事項</h5><br>
                    <div class="d-flex flex-wrap gap-2">
                        @if ($job->inexperienced_person_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">未経験OK</span><br>
                        @endif
                        @if ($job->utilize_language_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">語学を生かす</span><br>
                        @endif
                        @if ($job->flex_time_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">フレックスタイム</span><br>
                        @endif
                        @if ($job->near_station_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">駅近</span><br>
                        @endif
                        @if ($job->no_smoking_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">禁煙分煙</span><br>
                        @endif
                        @if ($job->newly_built_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">新築</span><br>
                        @endif
                        @if ($job->landmark_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">高層ビル</span><br>
                        @endif
                        @if ($job->maternity_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">産休暇育休</span><br>
                        @endif
                        @if ($job->dress_free_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">服装自由</span><br>
                        @endif
                        @if ($job->rent_all_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">住宅費全額補助</span><br>
                        @endif
                        @if ($job->rent_part_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">住宅費部分補助</span><br>
                        @endif
                        @if ($job->entrepreneur_cost_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">起業支援</span><br>
                        @endif
                        @if ($job->telework_flag == 1)
                        <span class="btn btn-outline-secondary btn-sm m-1">テレワーク</span><br>
                        @endif
                    </div>
                </div>



                <!-- Tugmalar (Bootstrap bilan to‘g‘ri joylashgan) -->
                <div class="text-center mt-4">
                    <div class="row row-cols-2 row-cols-md-4 g-2">
                        <div class="col">
                            <button type="button" onClick="history.back()" class="btn btn-primary w-100 m-1">
                                <i class="fa-solid fa-arrow-left"></i> 戻る
                            </button>
                        </div>
                        <div class="col">
                            <button class="btn btn-grey w-100 m-1" id="startJobButton" data-order="{{ $job->order_code }}">
                                <i class="fa-solid fa-bell text-dark"></i> 募集開始
                            </button>
                        </div>
                        <div class="col">
                            <button class="btn btn-danger w-100 m-1" id="pauseJobButton" data-order="{{ $job->order_code }}">
                                <i class="fa-solid fa-bell-slash"></i> 募集の一時停止
                            </button>
                        </div>
                        <div class="col">
                            <a href="{{ route('jobs.edit', ['orderCode' => $job->order_code]) }}" class="btn btn-primary w-100 m-1">
                                <i class="fa-solid fa-file-pen"></i> 更新する
                            </a>
                        </div>
                    </div>
                    @php
                        use Illuminate\Support\Facades\Route;
                        use Illuminate\Support\Facades\Auth;

                        $pauseUrl = '';
                        $startUrl = '';

                        try {
                        if (Auth::guard('master_company')->check() && Route::has('jobs.pause')) {
                        $pauseUrl = route('jobs.pause');
                        $startUrl = route('jobs.start');
                        } elseif (Auth::guard('master_agent')->check() && Route::has('agent.jobs.pause')) {
                        $pauseUrl = route('agent.jobs.pause');
                        $startUrl = route('agent.jobs.start');
                        }
                        } catch (\Exception $e) {
                        $pauseUrl = '';
                        $startUrl = '';
                        }
                    @endphp

                    <script>
                        const pauseUrl = "{{ $pauseUrl }}";
                        const startUrl = "{{ $startUrl }}";

                        function updateJobStatus(url, message, button) {
                            const orderCode = button.getAttribute('data-order');
                            if (!confirm(message)) return;

                            fetch(url, {
                                    method: "POST"
                                    , headers: {
                                        "Content-Type": "application/json"
                                        , "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                    }
                                    , body: JSON.stringify({
                                        order_code: orderCode
                                    })
                                })
                                .then(async response => {
                                    try {
                                        const data = await response.json();
                                        if (data.status === "success") {
                                            alert(data.message);
                                            location.reload();
                                        } else {
                                            alert("エラー：" + data.message);
                                        }
                                    } catch (e) {
                                        console.error("❌ JSON parse error", e);
                                        alert("⚠️ サーバーから無効な応答が返されました。");
                                    }
                                })
                                .catch(error => {
                                    console.error("❌ Fetch error:", error);
                                    alert("⚠️ サーバーエラーが発生しました。");
                                });
                        }

                        document.getElementById('pauseJobButton') ? .addEventListener('click', function() {
                            updateJobStatus(pauseUrl, "本当にこの求人を一時停止しますか？", this);
                        });

                        document.getElementById('startJobButton') ? .addEventListener('click', function() {
                            updateJobStatus(startUrl, "本当にこの求人を開始しますか？", this);
                        });

                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
