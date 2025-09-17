@extends('layouts.layout')

@section('title', '企業詳細情報')

@section('content')
<div class="container-fluid mt-0">
    <div class="card shadow-lg p-4">
        <h2 class="text-center mb-4">企業詳細情報</h2>

        <table class="table table-bordered table-hover table-sm">
            <tbody>
                @php
                    $labels = [
                        'company_name_k' => '企業名',
                        'company_name_f' => '企業名カナ',
                        'establish_year' => '設立年',
                        'establish_month' => '設立月',
                        'capital_amount' => '資本金',
                        'telephone_number' => '電話番号',
                        'homepage_address' => 'ホームページ',
                        'mailaddr' => 'メールアドレス',
                        'keiyaku_ymd' => '労働者派遣 基本契約締結日',
                        'intbase_contract_day' => '人材紹介 基本契約締結日',
                        'all_employee_num' => '全社員数',
                        'man_employee_num' => '男性社員数',
                        'woman_employee_num' => '女性社員数',
                        'post_u' => '郵便番号上3けた',
                        'post_l' => '郵便番号下4けた',
                        'prefecture' => '都道府県',
                        'city_k' => '市郡',
                        'town' => '町村',
                        'address' => '番地等',
                        'industry_type_name' => '業種名',
                        'business_contents' => '事業内容',
                        'company_pr' => '企業PR'
                    ];
                @endphp

                @foreach ($labels as $key => $label)
                    <tr>
                        <th class="text-nowrap" style="width: 220px;">{{ $label }}</th>
                        <td>
                            @if (Str::contains($key, 'mail') && !empty($company->$key))
                                <a href="mailto:{{ $company->$key }}">{{ $company->$key }}</a>
                            @elseif (Str::contains($key, 'homepage') && !empty($company->$key))
                                <a href="{{ $company->$key }}" target="_blank" rel="noopener">{{ $company->$key }}</a>
                            @else
                                {{ $company->$key ?? '-' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row g-3 mt-4">
            <div class="col-6">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary w-100">
                    <i class="fa-solid fa-arrow-left"></i> 戻る
                </a>
            </div>
            <div class="col-6">
                <a href="{{ route('agent.create_job', ['companyCode' => $company->company_code]) }}" class="btn btn-primary w-100">
                    この企業で求人票を作成する
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
