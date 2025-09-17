@extends('layouts.top')

@section('title', 'しごと探し')
@section('content')
    <section class="d-flex align-items-center justify-content-center py-0 px-2 my-0">
        <div class="container-flued">
            <div class="row justify-content-center">
                <div class="col-lg-9 col-md-12">
                    {{-- 📌 ユーザーの条件に一致する求人を表示する --}}
                    <div class="row g-4">
                        @if (!isset($matchingJobs) || (is_iterable($matchingJobs) && count($matchingJobs) === 0))
                            <!-- Ish topilmagan vaqtda -->
                            {{--  <h3 class="text-center">しごと探し</h3>  --}}
                            <h3 class="text-center  mb-4 mt-5 pt-0 text-main-theme">希望条件を入力するとマッチングされた求人票が表示されます。</h3>
                        @endif

                        @if (isset($matchingJobs) && is_iterable($matchingJobs) && count($matchingJobs) > 0)
                            <!-- Ish topilgan vaqtda -->
                            <h3 class="text-center mb-4 mt-5 pt-0">マッチングされた求人票一覧</h3>
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
                                                @if ($job->salary_hourly > 0)
                                                    時給 {{ number_format($job->salary_hourly) }}円
                                                @elseif($job->yearly_income_min > 0)
                                                    年収 {{ number_format($job->yearly_income_min) }}円
                                                @else
                                                    未設定
                                                @endif

                                            </p>

                                            <p class="card-text">
                                                <strong>勤務地:</strong> {{ $job->prefecture_name ?? '情報なし' }}
                                            </p>
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
                        @else
                        @endif
                    </div>

                    <!-- Paginatsiya -->
                    @if (isset($matchingJobs) && $matchingJobs instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $matchingJobs->appends(request()->except('page'))->links('vendor.pagination.default') }}
                        </div>
                    @endif
                    @if (isset($matchingJobs) && $matchingJobs)
                        <h3 class="text-center text-main-theme pt-5">希望条件変更するとマッチングされた求人票が更新されます。</h3>
                    @endif


                    <form id="filterForm" action="{{ route('matchings.showmatch') }}" method="POST">
                        @csrf


                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row mt-5">
                            {{--  <!--　大きな要素（すべて全画面） -->  --}}
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="big_class_code" class="form-label">希望職種 <span
                                            class="text-main-theme">必須</span></label>
                                    <select name="big_class_code" id="big_class_code"
                                        class="form-control border-primary w-100">
                                        <option value="">選択してください</option>
                                        @foreach ($bigClasses as $bigClass)
                                            <option value="{{ $bigClass->big_class_code }}">
                                                {{ $bigClass->big_class_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('big_class_code')
                                        <div class="alert text-main-theme">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{--  <!-- 小さな要素（左側が内側、右側が平ら） -->  --}}
                            <div class="col-11 offset-1 col-md-10 offset-md-2 col-lg-10 offset-lg-2">
                                <div class="mb-3">
                                    {{--  <label for="middle_class_code" class="form-label">職種タイプ <span
                                                class="text-main-theme">必要</span></label>  --}}
                                    <select name="job_category" id="middle_class_code"
                                        class="form-control border-primary w-100">
                                    </select>
                                    @error('job_category')
                                        <div class="alert text-main-theme">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- 希望勤務地 -->
                            <div class="mb-4">
                                <label class="form-label">希望勤務地 <span class="text-main-theme">必須</span></label>
                                <select name="prefecture_code" class="form-control border-dark" required>
                                    <option value="">選択してください</option> <!-- Tanlash uchun default option -->
                                    @foreach ($prefectures as $prefecture)
                                        <option value="{{ $prefecture->code }}">
                                            {{ $prefecture->detail }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('prefecture_code')
                                    <div class="alert" style="color: rgba(255, 0, 0, 0.674);">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="group_code" class="form-label">資格</label>
                                <select name="group_code" id="group_code" class="form-control border-primary w-100">
                                    <option value="">選択してください</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->group_code }}">{{ $group->group_name }}</option>
                                    @endforeach
                                </select>
                                @error('group_code')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-11 offset-1 col-md-10 offset-md-2 col-lg-10 offset-lg-2">
                                <div class="mb-3">
                                    {{--  <label for="category_code" class="form-label">資格カテゴリ</label>  --}}
                                    <select name="category_code" id="category_code"
                                        class="form-control border-primary w-100">
                                        <option value="" disabled selected>選択してください</option>
                                    </select>
                                    @error('category_code')
                                        <div class="alert text-main-theme">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                            <div class="col-10 offset-2 col-md-9 offset-md-3 col-lg-9 offset-lg-3">
                                <div class="mb-3">
                                    {{--  <label for="license_code" class="form-label">資格</label>  --}}
                                    <select name="license_code" id="license_code" class="form-control border-primary w-100">
                                        <option value="" disabled selected>選択してください</option>
                                    </select>
                                    @error('license_code')
                                        <div class="alert text-main-theme">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                            <!-- 希望給与 -->
                            <div class="mb-4">
                                <label for="desired_salary_type" class="form-label">希望給与：<span
                                        class="text-main-theme">必須</span></label><br>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <input type="radio" id="annual" name="desired_salary_type" value="年収"
                                            class="border-primary" required onchange="toggleSalaryFields()">
                                        <label for="annual">年収：<span class="text-main-theme">例: 400 (万円)</span></label>
                                        <input type="number" name="desired_salary_annual" id="desired_salary_annual"
                                            class="form-control mt-2" placeholder=""
                                            value="{{ old('desired_salary_annual') }}" disabled>
                                        @error('desired_salary_annual')
                                            <div class="alert text-main-theme">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <input type="radio" id="hourly" name="desired_salary_type" value="時給"
                                            class="border-primary" onchange="toggleSalaryFields()">
                                        <label for="hourly">時給：<span class="text-main-theme">例: 1200 (円)</span></label>
                                        <input type="number" name="desired_salary_hourly" id="desired_salary_hourly"
                                            class="form-control mt-2" placeholder=""
                                            value="{{ old('desired_salary_hourly') }}" disabled>
                                        @error('desired_salary_hourly')
                                            <div class="alert text-main-theme">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                        </div>

                        @if (isset($matchingJobs) && $matchingJobs)
                            <div class="d-grid">
                                <button class="btn btn-main-theme btn-lg w-100" type="submit">変更する</button>
                            </div>
                        @else
                        <div class="d-grid">
                            <button class="btn btn-main-theme btn-lg w-100" type="submit">登録するÏ</button>
                        </div>
                        @endif
                    </form>

                    <hr>

                    <div class="mb-3 text-center">
                        <a href="/login" class="text-center btn-lg btn-block text-decoration-none fs-f14 mb-2 ">
                            ログインパスワードをお持ちの方
                        </a>
                    </div>
                    <hr>
                    <br>
                    @if (!isset($matchingJobs) || (is_iterable($matchingJobs) && count($matchingJobs) === 0))
                        <!-- Ish topilmagan vaqtda -->
                        <div class="alert alert-info text-center">一致する仕事が見つかりませんでした。</div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('js/signin.js') }}"></script>
@endsection
