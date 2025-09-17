@extends('layouts.top')

@section('title', '履歴書プレビュー')

@section('content')
    <div class="container my-5">
        <h2 class="text-center pb-3">履歴書と職務経歴書情報のプレビュー</h2>
        <h3 class="text-start text-main-theme mt-5 pt-5">履歴書</h3>
        <div class="row">
            <div class="col-md-8 d-flex m-auto justify-content-end align-items-end">
                <table class="table table-bordered">
                    <tr>
                        <th>氏名</th>
                        <td>{{ $person->name ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>生年月日</th>
                        <td>
                            {{ $person->birth_year ?? '--' }} 年
                            {{ str_pad($person->birth_month ?? '--', 2, '0', STR_PAD_LEFT) }} 月
                            {{ str_pad($person->birth_day ?? '--', 2, '0', STR_PAD_LEFT) }} 日生
                            （満 {{ $age ?? '--' }} 歳）{{ $person->gender }}
                        </td>
                    </tr>
                    

                    <tr>
                        <th>住所</th>
                        <td>
                            {{--  <p>〒 166-0012</p>  --}}
                            {{ '〒' . $person->post_u . '-' . $person->post_l ?? '' }} {{ $personPrefecture->implode(', ') }}
                            {{ $person->city ?? '' }} {{ $person->town ?? '' }} {{ $person->address ?? '' }}
                        </td>
                    </tr>
                    {{--  <tr>
                        <th>住所ふりがな</th>
                        <td>
                            {{ $person->city_f ?? '' }} {{ $person->town_f ?? '' }} {{ $person->address_f ?? '' }}
                        </td>
                    </tr>  --}}
                    <tr>
                        <th>電話番号</th>
                        <td>{{ $person->portable_telephone_number ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>メール</th>
                        <td>{{ $person->mail_address ?? '' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-4 text-center">
                @if ($userImage)
                    <img src="{{ $userImage }}" alt="ユーザー写真" class="img-fluid rounded shadow-sm w-50">
                @else
                    <img src="{{ asset('default-avatar.png') }}" alt="デフォルト写真" class="img-fluid rounded shadow-sm w-50">
                @endif
            </div>
        </div>

        <h4 class="text-center mt-4">学歴</h4>
        <table class="table table-bordered">
            <tr>
                <th>年</th>
                <th>月</th>
                <th>学歴</th>
            </tr>
            @foreach ($educations as $education)
                {{-- 入学 (Kirish) yozuvi --}}
                <tr>
                    <td>{{ $education->entry_day_year ?? '----' }}</td>
                    <td>{{ $education->entry_day_month ?? '--' }}</td>
                    <td>
                        {{ $education->school_name ?? '' }} {{ $education->entry_type ?? '' }}
                    </td>
                </tr>

                {{-- Agar bitiruv sanasi mavjud bo'lsa, alohida qator qilib chiqaramiz --}}
                @if (!empty($education->graduate_day_year) && !empty($education->graduate_type))
                    <tr>
                        <td>{{ $education->graduate_day_year }}</td>
                        <td>{{ $education->graduate_day_month ?? '--' }}</td>
                        <td>
                            {{ $education->school_name ?? '' }} {{ $education->graduate_type }}
                        </td>
                    </tr>
                @endif
            @endforeach
        </table>

        <h4 class="text-center mt-4">職歴</h4>
        @foreach ($careers as $career)
            <p class="fw-bold">
                （{{ $career->company_name ?? '' }}
                {{ $career->entry_day_year ?? '----' }}年{{ $career->entry_day_month ?? '--' }}月 ～
                {{ $career->retire_day_year ?? '----' }}年{{ $career->retire_day_month ?? '--' }}月）
            </p>
            <table class="table table-bordered">
                <tr>
                    <th style="width: 15%;">業種</th>
                    <td>{{ $career->industry_type ?? '' }}</td>
                    <th style="width: 15%;">従業員数</th>
                    <td>{{ $career->number_employees ? $career->number_employees . '人' : '' }}</td>
                </tr>
                <tr>
                    <th>職種</th>
                    <td colspan="3">{{ $career->job_type_detail ?? '' }}</td>
                </tr>
                <tr>
                    <th>資本金</th>
                    <td colspan="3">
                        {{ $career->capital ? number_format($career->capital / 10000) . '万円' : '' }}
                    </td>
                </tr>
                @if (!empty($career->business_detail))
                    <tr>
                        <th>職務内容</th>
                        <td colspan="3">{{ $career->business_detail }}</td>
                    </tr>
                @endif
            </table>
        @endforeach



        <div class="row mt-4">
            <div class="col-12">
                <h4 class="text-center mt-4">資格・免許</h4>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 10%;">年</th>
                        <th style="width: 10%;">月</th>
                        <th>資格・免許</th>
                    </tr>
                    @foreach ($licenses as $license)
                        <tr>
                            <td>{{ $license->get_day ? date('Y', strtotime($license->get_day)) : '----' }}</td>
                            <td>{{ $license->get_day ? date('m', strtotime($license->get_day)) : '--' }}</td>
                            <td>{{ $license->group_name ?? '' }} - {{ $license->category_name ?? '' }} -
                                {{ $license->name ?? '' }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <h4 class="text-center mt-4">スキル</h4>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 20%;">カテゴリ</th>
                        <th>スキル名</th>
                    </tr>
                    @foreach ($selectedSkills as $category => $skills)
                        @foreach ($skills as $index => $skill)
                            <tr>
                                @if ($index == 0)
                                    <td rowspan="{{ count($skills) }}">{{ $category }}</td>
                                @endif
                                <td>{{ $skill->skill_name }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </table>
            </div>
        </div>


        <div class="row mt-4">
            <div class="col-12">
                <h4 class="text-center">自己PR</h4>
                <p class="border p-3">{{ $selfPR->self_pr ?? '自己PRが未登録です。' }}</p>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <h4 class="text-center">志望動機</h4>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 50%;">志望の動機・特技・好きな学科等</th>
                        <th style="width: 50%;">通勤時間</th>
                    </tr>
                    <tr>
                        <td rowspan="3">
                            {{ $resumeOther->wish_motive ?? '' }}
                        </td>
                        <td class="text-start align-middle">約 {{ $commuteHours }} 時間 {{ $commuteMinutes }} 分</td>
                    </tr>
                    <tr>
                        <th class="text-start align-middle">扶養家族数（配偶者を除く）</th>
                    </tr>
                    <tr>
                        <td class="text-start align-middle">
                            <div class="d-flex justify-content-start align-items-start">
                                <span>{{ $person->dependent_number ?? 0 }}</span>
                                <span class="ms-1">人</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-start align-middle">配偶者 ※</th>
                        <th class="text-start align-middle">配偶者の扶養義務 ※</th>
                    </tr>
                    <tr>
                        <td class="text-start align-middle">{{ $person->marriage_flag == 1 ? '有' : '無' }}</td>
                        <td class="text-start align-middle">{{ $person->dependent_flag == 1 ? '有' : '無' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <table class="table table-bordered">
                    <tr>
                        <th class="text-start align-middle">本人希望記入欄</th>
                    </tr>
                    <tr>
                        <td class="align-middle">{{ $resumeOther->hope_column ?? '' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <h3 class="text-start text-main-theme mt-5 pt-5">職務経歴書</h3>
        {{--  <div class="row d-flex justify-content-end align-items-end pt-5">  --}}
        <div class="row d-flex justify-content-start align-items-start pt-5">
            <div class="col-md-8">
                <table class="table table-bordered">
                    <tr>
                        <th>氏名</th>
                        <td>{{ $person->name ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>生年月日</th>
                        <td>
                            {{ $person->birth_year ?? '--' }} 年
                            {{ str_pad($person->birth_month ?? '--', 2, '0', STR_PAD_LEFT) }} 月
                            {{ str_pad($person->birth_day ?? '--', 2, '0', STR_PAD_LEFT) }} 日生
                            （満 {{ $person->age ?? '--' }} 歳 )
                            @if (!empty($person->gender))
                                {{ $person->gender }}
                            @else
                                <span class="text-danger"></span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>住所</th>
                        <td>
                            {{ '〒' . $person->post_u . '-' . $person->post_l ?? '' }}
                            {{ $personPrefecture->implode(', ') }}
                            {{ $person->city ?? '' }} {{ $person->town ?? '' }} {{ $person->address ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>電話番号</th>
                        <td>{{ $person->portable_telephone_number ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>メール</th>
                        <td>{{ $person->mail_address ?? '' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @foreach ($careers as $career)
            <p class="fw-bold text-primary">
                （{{ $career->company_name ?? '' }}
                {{ $career->entry_day_year ?? '----' }}年{{ $career->entry_day_month ?? '--' }}月 ～
                {{ $career->retire_day_year ?? '----' }}年{{ $career->retire_day_month ?? '--' }}月）
            </p>
            <table class="table table-bordered">
                <tr>
                    <th style="width: 15%;">業種</th>
                    <td>{{ $career->industry_type ?? '' }}</td>
                    <th style="width: 15%;">従業員数</th>
                    <td>{{ $career->number_employees ? $career->number_employees . '人' : '' }}</td>
                </tr>
                <tr>
                    <th>勤務形態</th>
                    <td colspan="3">{{ $career->working_type ?? '' }}</td> <!-- 勤務形態 qo‘shildi -->
                </tr>
                <tr>
                    <th>職種</th>
                    <td colspan="3">{{ $career->job_type_detail ?? '' }}</td>
                </tr>
                {{--  <tr>
                    <th>資本金</th>
                    <td colspan="3">{{ $career->capital ? $career->capital . '円' : '' }}</td>
                </tr>  --}}
                <tr>
                    <th>資本金</th>
                    <td colspan="3">
                        {{ $career->capital ? number_format($career->capital / 10000) . '万円' : '' }}
                    </td>
                </tr>
                
                @if (!empty($career->business_detail))
                    <tr>
                        <th>職務内容</th>
                        <td colspan="3">{{ $career->business_detail }}</td>
                    </tr>
                @endif
                <tr>
                    <th>自己PR</th>
                    <td colspan="3">{{ $selfPR->self_pr ?? '自己PRが未登録です。' }}</td>
                </tr>
                <tr>
                    <th>志望動機</th>
                    <td colspan="3">{{ $resumeOther->wish_motive ?? '' }}</td>
                </tr>
               <tr>
                    <th>本人希望</th>
                    <td colspan="3">{{ $resumeOther->hope_column ?? '' }}</td>
                </tr>

            </table>
        @endforeach

        <div class="row mt-5">
            <div class="col-6">
                <a href="{{ route('resume.basic-info') }}" class="btn btn-primary w-100">戻る</a>
            </div>
            <div class="col-6">
                <form method="POST" action="{{ route('resume.proceed') }}">
                    @csrf
                    <button type="submit" class="btn btn-main-theme w-100" id="applyJobButton">
                        <span id="applyJobText">進む</span> {{-- default: fallback --}}
                    </button>
                </form>
            </div>
        </div>
        
        {{--  @if (!session()->has('apply_job'))
            <script>
                window.location.href = "{{ route('resume.preview') }}";
            </script>
        @endif  --}}


        {{--  @php
            $applyJob = session('apply_job');
        @endphp  --}}

        {{--  @if (!session()->has('apply_job'))
            <script>
                window.location.href = "{{ route('upload.form') }}"; // ✅ To‘g‘ri yo‘naltirish
            </script>
        @endif  --}}

    </div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    fetch("{{ route('session.check') }}")
        .then(response => response.json())
        .then(data => {
            const buttonText = document.getElementById("applyJobText");
            if (data.apply_job) {
                buttonText.innerText = "オファーへ進む";
            } else {
                buttonText.innerText = "進む";
            }
        })
        .catch(error => {
            console.error("Session check error:", error);
        });
});
</script>
@endpush

