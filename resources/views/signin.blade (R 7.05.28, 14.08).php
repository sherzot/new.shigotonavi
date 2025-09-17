@extends('layouts.top')

@section('title', '会員登録')
@section('content')
<section class="d-flex align-items-center justify-content-center py-0 my-0">
    <div class="container-fluid container-lg">
        {{-- 🔹 Hero Images --}}
        <div>
            {{-- <img src="{{ asset('img/systemabout2.svg') }}" class="img-fluid mt-0 d-none d-sm-block" alt="Hero Image"> --}}
            {{--  <img src="{{ asset('img/top-z.svg') }}" class="img-fluid mt-0 d-none d-sm-block" alt="Hero Image">  --}}
            <img src="{{ asset('img/toppc.svg') }}" class="img-fluid mt-0 d-none d-sm-block w-100" alt="Hero Image">
            {{--  <img src="{{ asset('img/toptop-sm2.png') }}" class="img-fluid mt-0 d-block d-sm-none" alt="Hero Image">  --}}
            <img src="{{ asset('img/topsm.svg') }}" class="img-fluid mt-0 d-block d-sm-none w-100 p-0" alt="Hero Image">
            <img src="{{ asset('img/z-pc.svg') }}" class="img-fluid mt-0 d-none d-sm-block w-100" alt="Z">
        </div>
        <div class="container p-0">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    @php
                    $user = Auth::user();
                    $hasBasicInfo = false;
                    if ($user) {
                    $hasBasicInfo = DB::table('master_person')
                    ->where('staff_code', $user->staff_code)
                    ->exists();
                    }
                    @endphp
                    {{--  <div class="align-items-center pt-3">
                        <img src="{{ asset('img/z-pc.svg') }}" class="img-fluid d-none d-sm-block" alt="求人ステップガイド">
                    </div>  --}}

                    {{-- ✅ Step①: 基本情報登録フォーム（未登録 or ログインしてないユーザー） --}}
                    @if (!$user || ($user && !$hasBasicInfo))
                    <div class="align-items-center pt-3">
                        <img src="{{ asset('img/step-regist.svg') }}" class="img-fluid" alt="会員登録">
                    </div>
                    <div class="my-3 p-0 rounded border-0">
                        {{-- ✅ Step① va タイトル (Responsive) --}}
                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between">
                            <div class="d-flex align-items-center mb-2 mb-sm-0 pt-2">
                                {{-- <span class="badge btn btn-main-theme fs-6 px-3 py-2 me-2">Step①</span>  --}}
                                <h5 class="mb-0 fw-bold main-title">
                                    会員情報入力
                                    (<span class="text-main-theme fs-f14">マイページ取得</span>)
                                </h5>
                            </div>
                        </div>

                        {{-- ✅ モバイルでは、マイページは右下に表示されます。 --}}
                        <hr class="my-2">
                        <div class="text-end">
                            <span data-bs-toggle="modal" data-bs-target="#mypageModal" class="text-main-theme fw-bold text-decoration-underline" style="cursor: pointer;">
                                マイページ機能とは <i class="fa-solid fa-arrow-up-right-from-square text-primary"></i>
                            </span>
                        </div>
                    </div>

                    {{-- 基本情報フォーム --}}
                    @include('auth.partials.basic_info_form')

                    @endif

                    @php
                    use Illuminate\Support\Facades\Auth;
                    use Illuminate\Support\Facades\DB;

                    $staffCode = Auth::id();

                    // 両方のテーブルにレコードが存在する場合は True です。
                    $hasResume = DB::table('person_educate_history')->where('staff_code', $staffCode)->exists()
                    && DB::table('person_career_history')->where('staff_code', $staffCode)->exists();
                    @endphp
                    {{-- <div>
                        <img src="{{ asset('img/resume-match.svg') }}" class="img-fluid" alt="Hero Image">
                </div> --}}

                {{-- 履歴書フォーム  --}}
                @if ($user && $hasBasicInfo && !$hasResume)
                <div class="py-4" id="registResume">
                    <div class="align-items-center pt-3">
                        <img src="{{ asset('img/save-resume.svg') }}" class="img-fluid" alt="履歴書保存">
                    </div>
                    <div class="text-end py-3">
                        <span data-bs-toggle="modal" data-bs-target="#mypageModal" class="text-main-theme fw-bold text-decoration-underline" style="cursor: pointer;">
                            マイページ機能とは　<i class="fa-solid fa-arrow-up-right-from-square text-primary"></i>
                        </span>
                    </div>
                    @livewire('resume-info')
                </div>
                @endif

                {{-- ✅ Step②: 求人検索フォーム --}}
                <div id="searchJob" style="{{ $hasResume ? 'display: block;' : 'display: none;' }}">
                        <div class="align-items-center pt-3">
                            <img src="{{ asset('img/job-search.svg') }}" class="img-fluid" alt="求人検索">
                        </div>
                        <h5 class="mb-0 fw-bold text-center mb-3 pt-4 pb-3 fs-f24 text-main-theme">求人検索</h5>
                        {{--  <div class="d-flex align-items-center mb-3 p-3 rounded">
                            <div>
                                <h5 class="mb-0 fw-bold main-title text-center">検索条件</h5>
                            </div>
                        </div>  --}}
                        @include('auth.partials.hope_condition_form')
                        <div class="text-center mt-3">
                            <button class="btn btn-main-theme w-50" id="filterButton" data-clicked="false">
                                {{--  <i id="filterIcon" class="fa-solid fa-magnifying-glass"></i>  --}}
                                <span id="filterText">検索</span>
                            </button>
                            <div id="loadingSpinner" class="spinner-border text-primary ms-3" role="status" style="display: none;">
                                <span class="visually-hidden" id="loadingText">検索中...</span>
                            </div>
                        </div>
                        <div id="jobCount" class="container mt-4"></div> <!-- 件数 va alert -->
                        <div id="jobResults" class="container mt-4"></div> <!-- Kartalar -->
                    </div> 
                </div>
                <!-- マイページ モーダルウィンドウ -->
                <div class="modal fade" id="mypageModal" tabindex="-1" aria-labelledby="mypageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-main-theme text-white">
                                <h5 class="modal-title fw-normal text-main-theme" id="mypageModalLabel">マイページ機能</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <strong><span class="text-primary">①</span> 履歴書・職務経歴書</strong><br>
                                        作成・更新・ダウンロード
                                    </li>
                                    <li>
                                        <strong><span class="text-primary">②</span> 行動記録 </strong><br>
                                        オファー年月日・求人票・面接・内定・入社
                                    </li>
                                </ul>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">閉じる</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

{{-- 🔽 Scroll if needed --}}
@if (session('scrollTo'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById("{{ session('scrollTo') }}");
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
</script>
@endif

{{-- 🔽 Job Result Block (after 絞り込み) --}}
<div id="jobResults" class="container mt-4"></div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
     if (typeof window.isDev === 'undefined') {
        window.isDev = "{{ app()->environment('local') === 'local' }}";
    }

    let shouldScrollToSearch = false;
    let scrollTargetId = null;

    // ✅ アラート機能
    function showAlert(type, message, detail = null) {
        let formattedMessage = (message || (type === 'success' ? '保存に成功しました。' : 'エラーが発生しました。')).replace(/\n/g, '<br>');
        if (isDev && type === 'error' && detail) {
            formattedMessage += `<hr><pre style="text-align:left;">${detail}</pre>`;
        }

        Swal.fire({
            icon: type,
            title: type === 'success' ? '成功' : 'エラー',
            html: formattedMessage,
            confirmButtonText: 'OK',
            allowOutsideClick: false,
            allowEscapeKey: false,
            timer: type === 'success' ? 3000 : undefined,
            timerProgressBar: type === 'success'
        });

        if (type === 'error') {
            setTimeout(() => {
                const el = document.querySelector('.scroll-target');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    el.classList.add('border', 'border-danger', 'rounded');
                }
            }, 300);
        }
    }

    // ✅ Event listeners
    window.addEventListener('saved', event => showAlert('success', event.detail.message));
    window.addEventListener('alert', event => {
        const { type = 'error', message, trace } = event.detail;
        showAlert(type, message, trace);
    });

    window.addEventListener('savedAndScroll', event => {
        scrollTargetId = event.detail.scrollTo || null;
        shouldScrollToSearch = true;
        showAlert('success', event.detail.message);
    });
   // 📌 scroll trigger event
    {{--  window.addEventListener('resumeCompleted', () => {
        console.log('✅ resumeCompleted event received');

        const resumeBlock = document.getElementById('registResume');
        const searchBlock = document.getElementById('searchJob');

        if (resumeBlock && searchBlock) {
            resumeBlock.parentNode.insertBefore(searchBlock, resumeBlock);
            console.log('🔼 searchJob moved above registResume');

            // 🔄 scroll holatini saqlaymiz
            scrollTargetId = 'searchJob';
            shouldScrollToSearch = true;

            // ✅ DOM update bo‘lgach restart qilamiz
            Livewire.restart();
        }
    });  --}}
    window.addEventListener('resumeCompleted', () => {
        const resumeBlock = document.getElementById('registResume');
        const searchJob = document.getElementById('searchJob');
        if (resumeBlock) resumeBlock.style.display = 'none';
        if (searchJob) {
            searchJob.style.display = 'block';
            searchJob.scrollIntoView({ behavior: 'smooth' });
        }
    });

    // ✅ Job検索ボタンイベント
    document.addEventListener('DOMContentLoaded', function () {
        const filterButton = document.getElementById("filterButton");
        const loadingSpinner = document.getElementById("loadingSpinner");

        if (filterButton) {
            filterButton.addEventListener("click", function () {
                const form = document.getElementById("hopeForm");
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                data.prefecture_code = formData.getAll('prefecture_code[]').filter(code => code !== '');

                fetch("{{ route('matchings.filterJobs') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => renderJobCards(data.jobs, data.jobs.length))
                .catch(error => console.error("Error filtering jobs:", error))
                .finally(() => loadingSpinner.style.display = "none");

                if (filterButton.getAttribute('data-clicked') !== 'true') {
                    document.getElementById('filterText').innerText = '絞り込む';
                    {{--  document.getElementById('filterIcon').className = 'fa-solid fa-filter';  --}}
                    document.getElementById('loadingText').innerText = '読み込み中...';
                    filterButton.setAttribute('data-clicked', 'true');
                }

                loadingSpinner.style.display = 'inline-block';
            });
        }

        // ✅ 求人カードのレンダリング
        function renderJobCards(jobs, count) {
            document.getElementById('jobCount').innerHTML = `<div class="text-danger mt-4 fw-bold">検索条件に該当する求人：${count} 件</div>`;

            if (!jobs || jobs.length === 0) {
                document.getElementById('jobResults').innerHTML = `<div class="alert alert-warning mt-2">該当する求人が見つかりませんでした。</div>`;
                return;
            }

            if (jobs.length >= 10) {
                document.getElementById('jobResults').innerHTML = `<div class="alert alert-danger mt-2">※求人件数が10以下になると求人票が表示されます。検索条件を変更して10以下になるまで絞り込んでください！</div>`;
                return;
            }

            const salaryType = document.querySelector('input[name="salary_type"]:checked')?.value;
            let html = '';

            jobs.forEach(job => {
                let salaryHtml = '';
                const formatSalary = (min, max, label) => {
                    if (min > 0 && max > 0) return `${label}: ${min.toLocaleString()} ~ ${max.toLocaleString()} 円`;
                    if (min > 0) return `${label}: ${min.toLocaleString()} ~`;
                    if (max > 0) return `${label}: ~ ${max.toLocaleString()} 円`;
                    return '';
                };

                const annual = formatSalary(job.yearly_income_min, job.yearly_income_max, '年収');
                const hourly = formatSalary(job.hourly_income_min, job.hourly_income_max, '時給');

                if (salaryType === 'annual' && annual) {
                    salaryHtml += `<p class="card-text text-secondary fw-bolder">${annual}</p>`;
                } else if (salaryType === 'hourly' && hourly) {
                    salaryHtml += `<p class="card-text text-secondary fw-bolder">${hourly}</p>`;
                } else {
                    if (annual) salaryHtml += `<p class="card-text text-secondary fw-bolder">${annual}</p>`;
                    if (hourly) salaryHtml += `<p class="card-text text-secondary fw-bolder">${hourly}</p>`;
                    if (!annual && !hourly) salaryHtml += `<p class="card-text text-secondary fw-bolder">給与情報: -</p>`;
                }

                if (job.selectedFlagsArray?.length > 0) {
                    salaryHtml += '<div class="mb-2 d-flex flex-wrap gap-1">';
                    job.selectedFlagsArray.forEach(flag => {
                        salaryHtml += `<span class="badge bg-secondary border text-white small px-2 py-1 rounded-pill">${flag}</span>`;
                    });
                    salaryHtml += '</div>';
                }

                html += `
                    <a href="/jobs/detail/${job.order_code}" style="text-decoration: none;">
                        <div class="card my-3 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title text-main-theme fw-bold">${job.job_type_detail}</h5>
                                <p class="card-text text-secondary fw-bolder">勤務地: <span class="fw-normal">${job.prefecture_name}</span></p>
                                ${salaryHtml}
                                <p class="card-text small text-secondary">${job.pr_title1 ?? ''}</p>
                                <p class="card-text small text-secondary">${job.pr_contents1 ?? ''}</p>
                                <a href="/jobs/detail/${job.order_code}" class="btn btn-outline-primary mt-2">詳細を見る</a>
                            </div>
                        </div>
                    </a>
                `;
            });

            document.getElementById('jobResults').innerHTML = html;
        }
    });
</script>
@endpush


