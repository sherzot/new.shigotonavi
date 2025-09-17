@extends('layouts.top')

@section('title', '基本情報')
@section('content')
    <section class="w-100">
        <div class="container-flued py-5">
            <div class="row d-flex justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="text-center mb-4 fw-bold">基本情報</h5>

                            <!-- プロフィール情報 -->
                            <div class="table-responsive-sm">
                                <table class="table table-sm table-bordered">
                                    @if ($isCompany)
                                        <tr>
                                            <th class="bg-light text-start w-25">会社名</th>
                                            <td class="text-start">{{ $user->company_name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-start">会社ID</th>
                                            <td class="text-start">{{ $user->company_id }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <th class="bg-light text-start w-25">氏名(漢字)</th>
                                            <td class="text-start">{{ $user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-start">氏名(フリガナ)</th>
                                            <td class="text-start">{{ $user->name_f }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-start">個人ID</th>
                                            <td class="text-start">{{ $user->staff_code }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-start">希望職種</th>
                                            <td class="text-start">{{ $jobTypeDetail ?? '未設定' }}</td>
                                        </tr>
                                        <!-- 保有資格 -->
                                        <tr>
                                            <th class="bg-light text-start">保有資格</th>
                                            <td class="text-start">
                                                @if ($personLicenses->isEmpty())
                                                    
                                                @else
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach ($personLicenses as $license)
                                                            <li>{{ $license->group_name }} / {{ $license->category_name }} /
                                                                {{ $license->license_name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-start">希望勤務地</th>
                                            <td class="text-start">
                                                @if (!empty($jobWorkingPlaces) && $jobWorkingPlaces->count() > 0)
                                                    {{ $jobWorkingPlaces->implode(', ') }}
                                                @else
                                                
                                                @endif
                                            </td>
                                        </tr>
                                        @if ($yearlyIncome > 0)
                                            <tr>
                                                <th class="bg-light text-start">希望年収</th>
                                                <td class="text-start">{{ number_format($yearlyIncome) }}円〜</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <th class="bg-light text-start">希望時給</th>
                                                <td class="text-start">{{ number_format($hourlyIncome) }}円〜</td>
                                            </tr>
                                        @endif
                                    @endif
                                    <tr>
                                        <th class="bg-light text-start">メールアドレス</th>
                                        <td class="text-start">{{ $user->mail_address }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Buttons -->
                            <div class="text-center mt-4">
                                <a href="{{ route('mypage') }}" class="btn btn-main-theme">マイページに戻る</a>
                            </div>

                            <!-- Logout -->
                            <div class="text-center mt-3">
                                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fa-solid fa-right-from-bracket"></i> ログアウト
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        /* Mobilda yon tarafga scroll chiqmasligi uchun */
        body {
            overflow-x: hidden;
        }

        /* Jadvalni kichik ekranda chiroyli ko‘rinishini ta'minlash */
        .table-sm th,
        .table-sm td {
            padding: 8px;
            font-size: 14px;
        }

        @media (max-width: 576px) {

            .table th,
            .table td {
                font-size: 12px;
                /* sm ekranda matn hajmini kichikroq qilish */
            }
        }
    </style>

@endsection
