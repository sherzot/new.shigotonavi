@extends('layouts.top')

@section('title', 'ホーム')

@section('content')
    <div class="container py-5">
        {{-- @livewire('public-job-search')  --}}
        <img src="{{ asset('img/top-z.svg') }}" class="img-fluid mt-0 d-none d-sm-block" alt="Hero Image">
        <img src="{{ asset('img/toptop-sm2.png') }}" class="img-fluid mt-0 d-block d-sm-none" alt="Hero Image">
        {{-- <div class="aboutsnabi">
            <img src="{{ asset('img/aboutsnabi.png') }}" class="img-fluid p-5" alt="しごとナビについて">
        </div>
        <div class="aboutlis">
            <img src="{{ asset('img/aboutlis.png') }}" class="img-fluid p-5" alt="しごとナビを運営するリス株式会社">
        </div> --}}
    </div>
@endsection
