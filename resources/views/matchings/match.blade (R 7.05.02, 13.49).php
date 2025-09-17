@extends('layouts.top')

@section('title', '希望条件登録')
@section('content')
{{--  <div class="container py-5">
    @livewire('public-job-search')
</div>  --}}
<section class="d-flex align-items-center justify-content-center py-0 my-0">
    <div class="container">
        <img src="{{ asset('img/steep.png') }}" class="img-fluid mt-0" alt="Hero Image">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-12">
                <div class="container my-5">
                    <div class="d-flex justify-content-center align-items-center step-flow">
                        <!-- Step 1 -->
                        <div class="text-center">
                            <div class="step-circle active">①</div>
                        </div>
                        <!-- Line -->
                        <div class="step-line filled"></div>
                        <!-- Step 2 -->
                        <div class="text-center">
                            <div class="step-circle active">②</div>
                        </div>
                        <!-- Line -->
                        <div class="step-line"></div>
                        <!-- Step 3 -->
                        <div class="text-center">
                            <div class="step-circle">③</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="registerForm" action="{{ route('matchings.matchstore') }}" method="POST">
                        @csrf
                        <h3 class="text-center py-3">希望条件登録</h3>

                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="big_class_code" class="form-label">希望職種 <span class="text-main-theme">必須</span></label>
                                    <select name="big_class_code" id="big_class_code" class="form-control border-primary w-100">
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
                            <div class="col-11 offset-1 col-md-10 offset-md-2 col-lg-10 offset-lg-2">
                                <div class="mb-3">
                                    <select name="job_category" id="middle_class_code" class="form-control border-primary w-100">

                                    </select>
                                    @error('job_category')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- 希望勤務地 -->
                            <div class="mb-4">
                                <label class="form-label">希望勤務地 <span class="text-main-theme">必須</span></label>
                                <select name="prefecture_code[]" class="form-control border-dark" multiple required>
                                    @foreach ($prefectures as $prefecture)
                                    <option value="{{ $prefecture->code }}">
                                        {{ $prefecture->detail }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('prefecture_code[]')
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
                                    <select name="category_code" id="category_code" class="form-control border-primary w-100">
                                        <option value="" disabled selected>選択してください</option>
                                    </select>
                                    @error('category_code')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                            <div class="col-10 offset-2 col-md-9 offset-md-3 col-lg-9 offset-lg-3">
                                <div class="mb-3">
                                    <select name="license_code" id="license_code" class="form-control border-primary w-100">
                                        <option value="" disabled selected>選択してください</option>
                                    </select>
                                    @error('license_code')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                        </div>

                        <!-- 希望給与 -->
                        <div class="mb-4">
                            <label for="desired_salary_type" class="form-label">希望給与<span class="text-primary">（最低額を入力）</span><span class="text-main-theme">必須</span></label><br>
                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <input type="radio" id="annual" name="desired_salary_type" value="年収" class="border-primary" required onchange="toggleSalaryFields()">
                                    <label for="annual">年収：<span class="text-main-theme">例: 400 (万円〜)</span></label>
                                    <input type="number" name="desired_salary_annual" id="desired_salary_annual"
                                           class="form-control mt-2"
                                           placeholder="" step="50"
                                           value="{{ old('desired_salary_annual') }}" disabled>
                                    @error('desired_salary_annual')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                    @enderror
                                </div>
                            
                                <div class="col-md-6 col-12">
                                    <input type="radio" id="hourly" name="desired_salary_type" value="時給" class="border-primary" onchange="toggleSalaryFields()">
                                    <label for="hourly">時給：<span class="text-main-theme">例: 1200 (円〜)</span></label>
                                    <input type="number" name="desired_salary_hourly" id="desired_salary_hourly"
                                           class="form-control mt-2"
                                           placeholder="" step="100"
                                           value="{{ old('desired_salary_hourly') }}" disabled>
                                    @error('desired_salary_hourly')
                                    <div class="alert text-main-theme">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>                            
                        </div>
                        <script>
                            function toggleSalaryFields() {
                                const annual = document.getElementById('annual');
                                const hourly = document.getElementById('hourly');
                                const annualInput = document.getElementById('desired_salary_annual');
                                const hourlyInput = document.getElementById('desired_salary_hourly');

                                if (annual.checked) {
                                    annualInput.disabled = false;
                                    hourlyInput.disabled = true;
                                    hourlyInput.value = '';
                                } else if (hourly.checked) {
                                    annualInput.disabled = true;
                                    annualInput.value = '';
                                    hourlyInput.disabled = false;
                                }
                            }
                            window.onload = toggleSalaryFields;

                        </script>

                        <div class="mb-4">
                            <p class="text-primary fs-f18 text-center">登録すると入力した希望条件で、求人票一覧が表示されます！</p>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-main-theme btn-lg w-100" type="submit">登録</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="{{ asset('js/signin.js') }}"></script>
@endsection
