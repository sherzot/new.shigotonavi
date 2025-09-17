@extends('layouts.top')

@section('title', 'マスターページ')
@section('content')
<main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">

        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center">
                    <!-- <h1 data-aos="fade-up">しごとナビの転職サポートするサービスをご利用ください</h1> -->
                    <!-- <p data-aos="fade-up" data-aos-delay="100">しごとナビで希望の仕事を探したり、スタッフが仕事を紹介したりできます。</p> -->
                    <div class="d-flex flex-column flex-md-row" data-aos="fade-up" data-aos-delay="200">
                        <a href="/signin" class="btn-get-started">今すぐ登録してみる <i
                                class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-out">
                    <img src="{{ asset('img/hero-img.png') }}" class="img-fluid animated" alt="">
                </div>
            </div>
        </div>

    </section>

</main>


<!-- Scroll Top -->
<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
        class="bi bi-arrow-up-short"></i></a>
@endsection
