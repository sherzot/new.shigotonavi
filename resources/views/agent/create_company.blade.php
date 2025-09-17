@extends('layouts.layout')

@section('title', '求人企業登録')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <a href="{{ route('agent.dashboard') }}"><img class="img-responsive" src="{{ asset('img/logo02.png') }}"
                        alt="#" style="width: 150px;" /></a>
            </div>
        </div>
    </div>
    <div class="container-fluid mt-0">
        <div class="card shadow-lg p-4">
            <h2 class="text-center mb-4">企業登録フォーム</h2>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->has('msg'))
                <div class="alert alert-danger">
                    <strong>{{ $errors->first('msg') }}</strong><br>
                    @if ($errors->has('error_detail'))
                        <small class="text-danger">[詳細] {{ $errors->first('error_detail') }}</small>
                    @endif
                </div>
            @endif

            <form action="{{ route('create_company.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">企業名</label>
                        <input type="text" name="company_name_k" class="form-control border-dark"
                            value="{{ old('company_name_k') }}" required>
                    </div>
                    @error('company_name_k')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror

                    <div class="col-md-6 mb-3">
                        <label class="form-label">企業名カナ</label>
                        <input type="text" name="company_name_f" class="form-control border-dark"
                            value="{{ old('company_name_f') }}">
                    </div>
                    @error('company_name_f')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">設立年</label>
                        <input type="text" name="establish_year" class="form-control border-dark"
                            value="{{ old('establish_year') }}">
                    </div>
                    @error('establish_year')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror

                    <div class="col-md-4 mb-3">
                        <label class="form-label">設立月</label>
                        <input type="text" name="establish_month" class="form-control border-dark"
                            value="{{ old('establish_month') }}">
                    </div>
                    @error('establish_month')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <div class="col-md-4 mb-3">
                        <label class="form-label">資本金</label>
                        <input type="text" name="capital_amount" class="form-control border-dark"
                            value="{{ old('capital_amount') }}">
                    </div>
                    @error('capital_amount')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">電話番号</label>
                        <input type="text" name="telephone_number" class="form-control border-dark"
                            value="{{ old('telephone_number') }}">
                    </div>
                    @error('telephone_number')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ホームページ</label>
                        <input type="text" name="homepage_address" class="form-control border-dark"
                            value="{{ old('homepage_address') }}">
                    </div>
                    @error('homepage_address')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">メールアドレス</label>
                        <input type="email" name="mailaddr" class="form-control border-dark"
                            value="{{ old('mailaddr') }}">
                    </div>
                    @error('mailaddr')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <div class="col-md-4 mb-3">
                        <label class="form-label">パスワード</label>
                        <input type="password" name="password" class="form-control border-dark"
                            value="{{ old('password') }}">
                    </div>
                    @error('password')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <div class="col-md-4 mb-3">
                        <label class="form-label">パスワードを認証する</label>
                        <input type="password" name="password_confirmation" class="form-control border-dark"
                            value="{{ old('password_confirmation') }}">
                    </div>
                    @error('password_confirmation')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <!-- 勤務地 -->
                <div class="mb-3">
                    <label for="prefecture_code" class="form-label">勤務地</label>
                    <select name="prefecture_code[]" id="prefecture_code" class="form-control border-dark">
                        <option value="" selected disabled>選択してください</option>
                        <!-- 地域 -->
                        @if (isset($regionGroups))
                            @foreach ($regionGroups as $region)
                                <optgroup label="{{ $region['detail'] }}">
                                    @foreach ($region['prefectures'] as $prefecture)
                                        <option value="{{ $prefecture['code'] }}"
                                            {{ collect(old('prefecture_code'))->contains($prefecture['code']) ? 'selected' : '' }}>
                                            {{ $prefecture['detail'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        @endif

                        <!-- 個別 -->
                        @if (isset($individualPrefectures) && is_array($individualPrefectures))
                            @foreach ($individualPrefectures as $prefecture)
                                <option value="{{ $prefecture['code'] }}">
                                    {{ $prefecture['detail'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('prefecture_code[]')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="row">
                    <!-- 上3桁 -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">郵便番号上３けた</label>
                        <input type="text" name="post_u" id="post_u" class="form-control text-start border-dark"
                            value="{{ old('post_u', isset($person) ? $person->post_u : '') }}" maxlength="3"
                            pattern="\d{3}">
                    </div>
                    @error('post_u')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <!-- ハイフン ( - ) -->
                    <div class="col-1 d-flex align-items-center justify-content-center">
                        <span class="fw-bold">-</span>
                    </div>

                    <!-- 下4桁 -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">郵便番号下４けた</label>
                        <input type="text" name="post_l" id="post_l" class="form-control text-start border-dark"
                            value="{{ old('post_l', isset($person) ? ltrim($person->post_l ?? '', '-') : '') }}"
                            maxlength="4" pattern="\d{4}">
                    </div>
                    @error('post_l')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <div class="col-md-5 mb-3">
                        <label class="form-label">市郡</label>
                        <input type="text" name="city_k" class="form-control border-dark"
                            value="{{ old('city_k') }}">
                    </div>
                    @error('city_k')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">町村</label>
                        <input type="text" name="town" class="form-control border-dark"
                            value="{{ old('town') }}">
                    </div>
                    @error('town')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <div class="col-md-4 mb-3">
                        <label class="form-label">番地等 </label>
                        <input type="text" name="address" class="form-control border-dark"
                            value="{{ old('address') }}">
                    </div>
                    @error('address')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <!-- 業種 -->
                    <div class="col-12 col-md-4 col-lg-4 mb-3">
                        <label for="industry_type_code" class="form-label">業種</label>
                        <select class="form-control border-dark" name="industry_type_code">
                            <option value="">(選択してください)</option>
                            @foreach ($industryTypes as $type)
                                <option value="{{ $type->code }}"
                                    {{ old('industry_type_code', $selectedIndustry ?? '') == $type->code ? 'selected' : '' }}>
                                    {{ $type->detail }}
                                </option>
                            @endforeach
                        </select>
                        @error('industry_type_code')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('industry_type_code')
                        <span class="text-main-theme">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">資本金</label>
                        <input type="text" name="capital_amount" class="form-control border-dark"
                            value="{{ old('capital_amount') }}">
                    </div>
                    @error('capital_amount')
                        <span class="text-main-theme">{{ $message }}</span>
                    @enderror
                    {{--  19710401 xuddi shu xolatda saqlanishi kerak   --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">労働者派遣 基本契約締結日 (例：19710401)</label>
                        <input type="text" name="keiyaku_ymd" class="form-control border-dark"
                            value="{{ old('keiyaku_ymd') }}">
                    </div>
                    @error('keiyaku_ymd')
                        <span class="text-main-theme">{{ $message }}</span>
                    @enderror
                    {{--  0000-00-00 00:00:00 xuddi shu xolatda saqlanishi kerak   --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">人材紹介 基本契約締結日 (例：19710401)</label>
                        <input type="text" name="intbase_contract_day" class="form-control border-dark"
                            value="{{ old('intbase_contract_day') }}">
                    </div>
                    @error('intbase_contract_day')
                        <span class="text-main-theme">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">全社員数</label>
                        <input type="text" name="all_employee_num"
                            class="form-control border-dark"　value="{{ old('all_employee_num') }}">
                    </div>
                    @error('all_employee_num')
                        <span class="text-main-theme">{{ $message }}</span>
                    @enderror
                    <div class="col-md-4 mb-3">
                        <label class="form-label">男性社員数</label>
                        <input type="text" name="man_employee_num"
                            class="form-control border-dark"　value="{{ old('man_employee_num') }}">
                    </div>
                    @error('man_employee_num')
                        <span class="text-main-theme">{{ $message }}</span>
                    @enderror
                    <div class="col-md-4 mb-3">
                        <label class="form-label">女性社員数</label>
                        <input type="text" name="woman_employee_num"
                            class="form-control border-dark"　value="{{ old('woman_employee_num') }}">
                    </div>
                    @error('woman_employee_num')
                        <span class="text-main-theme">{{ $message }}</span>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">事業内容</label>
                        <input type="text" name="business_contents"
                            class="form-control border-dark"　value="{{ old('business_contents') }}">
                    </div>
                    @error('business_contents')
                        <span class="text-main-theme">{{ $message }}</span>
                    @enderror
                    <div class="col-md-6 mb-3">
                        <label class="form-label">企業PR</label>
                        <input type="text" name="company_pr"
                            class="form-control border-dark"　value="{{ old('company_pr') }}">
                    </div>
                    @error('company_pr')
                        <span class="text-main-theme">{{ $message }}</span>
                    @enderror
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-6">
                        <button type="button" onClick="history.back()" class="btn btn-primary w-100">
                            <i class="fa-solid fa-arrow-left"></i> 戻る
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-primary w-100">登録</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

@endsection
