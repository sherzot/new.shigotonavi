@extends('layouts.top')

@section('title', 'csvFileのUpload')
@section('content')
    <div class="container-fluid	mt-4">
        <div class="row	d-flex	justify-content-center	align-items-center">
            <div class="col-12	col-md-10	col-lg-6	col-xl-5">
                <div class="card	p-4	shadow-lg	rounded-3	border-0">
                    <h3 class="text-center	mb-4	text-dark">求人票CSVのUPLOAD</h3>

                    {{--  <p>{{ $message }}</p>  --}}

                    {{--  <form id="csvupload-form" enctype="multipart/form-data"> --}}
                    <form method="post" action="/uploadcsv" enctype="multipart/form-data">
                        @csrf
                        <label name="csvFile">csvファイル</label>
                        <input type="file" name="csvFile" class="" id="csvFile" />
                        {{-- <input type="submit"></input> --}}

                        {{-- <input type="submit"> --}}
                        <button type="submit" id="uploadcsv-uplad"
                            class="btn btn-main-theme w-100 shadow-sm rounded-2">アップロード</button><!-- style="display: none;" -->
                    </form>
                </div>
                <hr>
                <div class="card     p-4     shadow-lg       rounded-3       border-0">
                    <h3 class="text-center      mb-4    text-dark">会社マスタCSVのUPLOAD</h3>

                    {{--  <p>{{ $message2 }}</p>  --}}

                    <form method="post" action="/uploadcompanycsv" enctype="multipart/form-data">
                        @csrf
                        <label name="csvFile">会社マスタcsvファイル</label>
                        <input type="file" name="companycCsv" class="" id="companyCsv" />

                        <button type="submit" id="companycsv-uplad"
                            class="btn btn-main-theme w-100 shadow-sm rounded-2">アップロード</button><!-- style="display: none;" -->
                    </form>
                </div>


                {{--  </div>
</div>
</div> --}}
            @endsection
