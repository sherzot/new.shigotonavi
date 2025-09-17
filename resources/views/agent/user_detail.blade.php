@extends('layouts.layout')

@section('title', '求職者情報')

@section('content')
    <div class="container-fluid mt-4 w-100">
        <div class="row">
            <div class="col-md-12 text-start">
                <a href="{{ route('agent.dashboard') }}">
                    <img class="img-fluid mb-3" src="{{ asset('img/logo02.png') }}" alt="#" style="width: 150px;" />
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-primary text-white text-center">求職者情報</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>スタッフコード</th>
                        <td>{{ $user->staff_code }}</td>
                    </tr>
                    <tr>
                        <th>氏名 (漢字)</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>氏名 (フリガナ)</th>
                        <td>{{ $user->name_f }}</td>
                    </tr>
                    <tr>
                        <th>連絡先</th>
                        <td>{{ $user->portable_telephone_number }}</td>
                    </tr>
                    <tr>
                        <th>メールアドレス</th>
                        <td>{{ $user->mail_address }}</td>
                    </tr>
                    <tr>
                        <th>希望職種</th>
                        <td>{{ $jobTypeDetail ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>希望勤務地</th>
                        <td>
                            @if ($jobWorkingPlaces->isEmpty())
                                N/A
                            @else
                                {{ implode(', ', $jobWorkingPlaces->toArray()) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>希望年収</th>
                        <td>{{ $yearlyIncome ? number_format($yearlyIncome) . ' 円' : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>希望時給</th>
                        <td>{{ $hourlyIncome ? number_format($hourlyIncome) . ' 円' : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>保有資格</th>
                        <td>
                            @if ($personLicenses->isEmpty())
                                N/A
                            @else
                                <ul>
                                    @foreach ($personLicenses as $license)
                                        <li>{{ $license->group_name }} - {{ $license->category_name }} -
                                            {{ $license->license_name }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                    </tr>
                </table>
                
            </div>

        <div class="card">
            <div class="card-header bg-primary text-white text-center">学歴</div>
            <div class="card-body">
                <table class="table table-bordered">
		@foreach ($schools as $school)
                    <tr>
                        <th>学校名</th>
                        <td>{{ $school->school_name }}({{ $school->kind }})</td>
                    </tr>
                    <tr>
                        <th>入学・卒業</th>
                        <td>{{ \Carbon\Carbon::parse($school->entry_day)->format("Y年m月d日") }} -{{ \Carbon\Carbon::parse($school->graduate_day)->format("Y年m月d日") }}</td>
                    </tr>

		@endforeach
		</table>
	    </div>
	  </div>
       <div class="card">
            <div class="card-header bg-primary text-white text-center">職歴</div>
            <div class="card-body">
                <table class="table table-bordered">
                @foreach ($careers as $career)
                    <tr>
                        <th>会社名</th>
                        <td>{{ $career->company_name }}</td>
                    </tr>
                    <tr>
                        <th>入社・退社</th>
                        <td>{{ \Carbon\Carbon::parse($career->entry_day)->format("Y年m月d日") }} -{{ \Carbon\Carbon::parse($career->retire_day)->format("Y年m月d日") }}</td>
                    </tr>
                    <tr>
                        <th>仕事内容</th>
                        <td>{{ $career->job_type_detail }} </td>

                @endforeach
                </table>
            </div>
          </div>


        </div>
        {{--  <a href="{{ url()->previous() }}" class="btn btn-primary btn-lg">戻る</a>  --}}
        <div class="justify-content-start mt-3">
            <a href="{{ route('agent.usersearch') }}" class="btn btn-primary btn-lg">戻る</a>
        </div>
    </div>
@endsection
