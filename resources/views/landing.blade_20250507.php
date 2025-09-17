<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>転職サポート | しごとナビ</title>
    <meta name="description" content="しごとナビは、効率的で信頼できる転職支援サービスを提供する日本最大級の求人プラットフォームです。">
    <meta name="keywords" content="転職, 求人, 就職支援, キャリア, 正社員, パート, しごとナビ">
    <meta name="author" content="LIS Co., Ltd.">
    <meta property="og:title" content="しごとナビ | 日本最大級の転職支援サービス">
    <meta property="og:description" content="登録するだけで最適な求人にマッチング。エージェントによるサポート付き。">
    <meta property="og:image" content="{{ asset('img/og-image.jpg') }}">
    <meta property="og:url" content="https://mch.shigotonavi.co.jp">
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime()
                , event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0]
                , j = d.createElement(s)
                , dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-WV9TQS27');

    </script>
    <!-- End Google Tag Manager -->
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js'])  --}}
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="manifest" href="/site.webmanifest">
    <script type="application/ld+json">
        {
            "@context": "https://schema.org"
            , "@type": "Organization"
            , "name": "しごとナビ"
            , "url": "https://www.shigotonavi.co.jp"
            , "logo": "https://www.shigotonavi.co.jp/img/og-image.jpg"
            , "sameAs": [
                "https://www.facebook.com/shigotonavi"
                , "https://twitter.com/shigotonavi"
            ]
            , "contactPoint": {
                "@type": "ContactPoint"
                , "telephone": "+81-3-1234-5678"
                , "contactType": "カスタマーサポート"
                , "areaServed": "JP"
                , "availableLanguage": ["Japanese"]
            }
        }

    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.core.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            font-size: var(--bs-body-font-size);
            font-weight: var(--bs-body-font-weight);
            line-height: var(--bs-body-line-height);
            margin: 0;
            height: 100%;
            scroll-behavior: smooth;
            overflow-x: hidden !important;
        }

        @keyframes flicker {

            0%,
            19%,
            21%,
            23%,
            25%,
            54%,
            56%,
            100% {
                opacity: 1;
            }

            20%,
            22%,
            24%,
            55% {
                opacity: 0.4;
            }
        }

        @keyframes bounce-glow {

            0%,
            100% {
                transform: translateY(0);
                box-shadow: 0 0 0px rgba(234, 85, 20, 0.3);
            }

            50% {
                transform: translateY(-5px);
                box-shadow: 0 0 15px rgba(234, 85, 20, 0.8);
            }
        }

        .animate-bounce-glow {
            animation: bounce-glow 1.8s infinite;
        }

    </style>
</head>

<body class="p-0 m-0 bg-white">
    <div class="max-w-screen-xl mx-auto">
        <!-- ✅ Enhanced Stats Notification Banner -->
        <!-- ✅ Enhanced Stats Notification Banner -->
        <div class="w-full bg-gradient-to-r from-green-100 via-lime-200 to-green-100 text-green-900 border-t border-b border-green-400 text-sm py-3 text-center animate-fade-in-down shadow-md">
            <!-- 📱 Mobil uchun -->
            <div class="font-extrabold text-xl sm:text-2xl animate-pulse block sm:hidden">
                おかげさまで『しごとナビ』登録者が<br>{{ number_format($userCount) }}人突破しました。
            </div>

            <!-- 💻 PC uchun -->
            <div class="font-extrabold text-xl sm:text-2xl animate-pulse hidden sm:block">
                おかげさまで『しごとナビ』登録者が{{ number_format($userCount) }}人突破しました。
            </div>

            <div class="text-xs sm:text-sm mt-1 tracking-wide font-medium animate-[flicker_3s_infinite]">
                <!-- ✅ PC・タブレット -->
                <p class="hidden sm:block">
                    求人{{ number_format($jobCount) }}件 ・ 求職者{{ number_format($userCount) }}人　
                    {{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）現在
                </p>
                <!-- ✅ スマホ -->
                <p class="block sm:hidden">
                    求人{{ number_format($jobCount) }}件 ・ 求職者{{ number_format($userCount) }}人 <br>
                    {{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）現在
                </p>
                {{--  <!-- ✅ PC・タブレット -->
                <p class="hidden sm:block">
                    求人{{ number_format($jobCount) }}件 ・ 企業{{ number_format($companyCount) }}社 ・ 求職者{{ number_format($userCount) }}人　
                    {{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）現在
                </p>
                <!-- ✅ スマホ -->
                <p class="block sm:hidden">
                    求人{{ number_format($jobCount) }}件 ・ 企業{{ number_format($companyCount) }}社 ・ 求職者{{ number_format($userCount) }}人 <br>
                    {{ now()->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）現在
                </p>  --}}
            </div>
        </div>


        <!-- Hero Section -->
        <section class="text-center">
            <img src="{{ asset('img/LPtop.png') }}" class="w-full h-auto" alt="">
        </section>

        <!-- Match About Section -->
        <section class="relative py-10 px-4 bg-cover bg-center text-center" style="background-image: url('{{ asset('img/top2-bg.jpg') }}');">
            <div class="absolute inset-0 bg-white/80"></div>
            <div class="relative z-10 max-w-4xl mx-auto flex flex-col items-center space-y-6">
                <img src="{{ asset('img/matchabout.png') }}" alt="マッチング図" class="w-full max-w-[1200px] sm:max-w-[90%] md:max-w-[100%] h-auto rounded shadow-md" data-aos="fade-up" data-aos-duration="1000">
                <!-- ✅ PC・タブレット専用表示 -->
                <p class="hidden sm:block text-gray-800 text-base md:text-lg leading-relaxed px-2" data-aos="fade-up" data-aos-delay="200">
                    完成した応募種類をそのまま利用できるため、<br class="md:inline">
                    無駄なく効率的にお仕事の幅を広げることができます。<br class="md:inline">
                    履歴者の作成を通じて、あなたに最適な転職をサポートします。
                </p>

                <!-- ✅ SP専用表示 -->
                <p class="block sm:hidden text-gray-800 text-sm leading-relaxed px-2" data-aos="fade-up" data-aos-delay="200">
                    完成した応募種類をそのまま利用できるため、<br>
                    無駄なく効率的にお仕事の幅を広げることが<br>できます。
                    履歴者の作成を通じて、あなたに最適な<br>転職をサポートします。
                </p>
            </div>
        </section>
        <!-- 🔹 Register CTA Section - Only for Medium and Up (PC・タブレット) -->
        <section class="py-12 px-4 bg-gradient-to-r from-orange-100 via-sky-100 to-lime-100 hidden sm:block">
            <div class="max-w-3xl mx-auto text-center" data-aos="zoom-in" data-aos-duration="800">
                <h2 class="inline-block bg-[#EA5514] text-white font-bold text-xl sm:text-2xl md:text-3xl py-3 px-6 rounded-md leading-snug tracking-wide shadow-lg hover:bg-[#d94d13] transition duration-300">
                    <a href="/" class="block sm:inline">
                        会員登録はこちら<br class="sm:hidden">求人お試し検索もできます →
                    </a>
                </h2>
            </div>
        </section>
        <!-- 🔹 Register CTA Section - Only for Small Devices (スマホ用) -->
        <section class="py-10 px-4 bg-gradient-to-br from-yellow-100 via-orange-100 to-pink-100 block sm:hidden">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="bg-[#EA5514] text-white font-bold text-base sm:text-lg md:text-xl py-2 px-4 rounded-md inline-block leading-snug tracking-wide shadow-md hover:bg-[#d94d13] transition duration-300">
                    <a href="/" class="block">
                        会員登録はこちら<br>求人お試し検索もできます →
                    </a>
                </h2>
            </div>
        </section>

        <!-- ✅ Register CTA Section (PC / Tablet) -->
        <section class="hidden sm:block py-16 px-6 bg-gradient-to-br from-[#FFF5EB] via-[#ECFDF5] to-[#E0F2FE]">
            <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10 items-center bg-white rounded-2xl shadow-xl p-10" <!-- 📄 Text Content -->
                <div class="space-y-5 text-left">

                    <p class="text-gray-700 leading-relaxed text-base">
                        応募したい求人票が見つかりましたら、ぜひ
                        <span class="inline-block bg-red-500 text-white font-bold px-3 py-1 rounded shadow whitespace-nowrap">
                            面談依頼する【オファー】
                        </span>
                        ボタンを押してください。<br>
                        担当エージェントが企業との間に入り、あなたの転職活動を<br>丁寧にサポートします。

                        直接企業への応募ではないため、<br>安心してオファーいただけます。
                    </p>

                    <p class="text-red-600 font-semibold text-sm">
                        ※履歴書と職務経歴書が必要です。
                    </p>
                </div>

                <!-- 📷 Image Block -->
                <div class="text-center md:text-right">
                    <img src="{{ asset('img/interview.png') }}" alt="面談サポート画像" class="w-74 md:w-80 mx-auto md:ml-auto transition-transform duration-300 hover:scale-105">
                </div>
            </div>
        </section>
        <!-- ✅ Register CTA Section (Smartphone Only) -->
        <section class="block sm:hidden py-10 px-5 bg-gradient-to-br from-yellow-50 via-orange-50 to-pink-50">
            <div class="bg-white rounded-xl shadow-md p-6 text-left space-y-5">
                <p class="text-gray-800 text-sm leading-relaxed">
                    応募したい求人票が見つかりましたら、ぜひ
                    <span class="inline-block bg-red-500 text-white font-bold px-2 py-1 rounded shadow whitespace-nowrap">
                        面談依頼する【オファー】
                    </span>
                    ボタンを押して<br>ください。
                    エージェントが企業との間に入り、転職活動を丁寧にサポートします。
                </p>

                <p class="text-red-600 font-semibold text-xs">
                    ※履歴書と職務経歴書が必要です。
                </p>

                <div class="text-center">
                    <img src="{{ asset('img/interview.png') }}" alt="面談イメージ" class="w-50 h-auto mx-auto transition duration-300 hover:scale-105">
                </div>
            </div>
        </section>
        <!-- ✅ Responsive 機能紹介 Section -->
        <section class="bg-gray-50 py-12 px-4">
            <h2 class="text-center text-2xl font-bold text-[#EA5514] mb-10">しごとナビの主な機能</h2>

            <!-- 🔸 For Mobile: Glide Carousel -->
            <div class="sm:hidden">
                <div class="glide">
                    <div class="glide__track" data-glide-el="track">
                        <ul class="glide__slides">
                            <!-- Slide 1 -->
                            <li class="glide__slide bg-white rounded-lg shadow p-6">
                                <img src="/img/lp/feature-search.png" class="w-16 h-16 mx-auto mb-3" alt="検索">
                                <h3 class="text-[#EA5514] font-bold text-lg mb-1">求人を検索できる</h3>
                                <p class="text-sm text-gray-600">希望職種・勤務地・給与条件で全国の求人を検索。</p>
                            </li>

                            <!-- Slide 2 -->
                            <li class="glide__slide bg-white rounded-lg shadow p-6">
                                <img src="/img/lp/feature-agent.png" class="w-16 h-16 mx-auto mb-3" alt="エージェント">
                                <h3 class="text-[#EA5514] font-bold text-lg mb-1">エージェントに相談できる</h3>
                                <p class="text-sm text-gray-600">プロが転職活動を徹底サポート。不安も相談OK。</p>
                            </li>

                            <!-- Slide 3 -->
                            <li class="glide__slide bg-white rounded-lg shadow p-6">
                                <img src="/img/lp/feature-resume.png" class="w-16 h-16 mx-auto mb-3" alt="履歴書">
                                <h3 class="text-[#EA5514] font-bold text-lg mb-1">履歴書作成サポートあり</h3>
                                <p class="text-sm text-gray-600">簡単操作でPDFやExcelの履歴書を作成可能。</p>
                            </li>

                            <!-- Slide 4 -->
                            <li class="glide__slide bg-white rounded-lg shadow p-6">
                                <img src="/img/lp/feature-notify.png" class="w-16 h-16 mx-auto mb-3" alt="通知">
                                <h3 class="text-[#EA5514] font-bold text-lg mb-1">リアルタイム通知</h3>
                                <p class="text-sm text-gray-600">条件に合う求人が見つかるとすぐに通知！</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 🔸 For PC: Grid Layout -->
            <div class="hidden sm:grid grid-cols-1 md:grid-cols-2 gap-6 max-w-6xl mx-auto">
                <!-- Block 1 -->
                <div class="bg-white rounded-lg shadow p-6 flex items-center gap-4">
                    <img src="/img/searchjob.png" class="w-20 h-20" alt="検索">
                    <div>
                        <h3 class="font-bold text-[#EA5514] mb-1">求人を検索できる</h3>
                        <p class="text-sm text-gray-600">詳細条件で全国の求人を検索可能。あなたに最適な仕事が見つかる！</p>
                    </div>
                </div>

                <!-- Block 2 -->
                <div class="bg-white rounded-lg shadow p-6 flex items-center gap-4">
                    <img src="/img/consult.png" class="w-20 h-20" alt="エージェント">
                    <div>
                        <h3 class="font-bold text-[#EA5514] mb-1">エージェントに相談できる</h3>
                        <p class="text-sm text-gray-600">不安な点をプロに相談可能。安心して転職活動を進められる！</p>
                    </div>
                </div>

                <!-- Block 3 -->
                <div class="bg-white rounded-lg shadow p-6 flex items-center gap-4">
                    <img src="/img/resume-creation.png" class="w-20 h-20" alt="履歴書">
                    <div>
                        <h3 class="font-bold text-[#EA5514] mb-1">履歴書作成サポートあり</h3>
                        <p class="text-sm text-gray-600">フォーマット付きで簡単作成。PDF/Excel形式でダウンロード可！</p>
                    </div>
                </div>

                <!-- Block 4 -->
                <div class="bg-white rounded-lg shadow p-6 flex items-center gap-4">
                    <img src="/img/job-listings.png" class="w-20 h-20" alt="通知">
                    <div>
                        <h3 class="font-bold text-[#EA5514] mb-1">入力した希望条件で求人票がマッチングされます</h3>
                        <p class="text-sm text-gray-600">条件に合う求人を選んで、面談依頼する【オファー】押して <br> ください！</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- 🔹 Register CTA Section - Only for Medium and Up (PC・タブレット) -->
        <section class="py-12 px-4 bg-gradient-to-r from-orange-100 via-sky-100 to-lime-100 hidden sm:block">
            <div class="max-w-3xl mx-auto text-center" data-aos="zoom-in" data-aos-duration="800">
                <h2 class="inline-block bg-[#EA5514] text-white font-bold text-xl sm:text-2xl md:text-3xl py-3 px-6 rounded-md leading-snug tracking-wide shadow-lg hover:bg-[#d94d13] transition duration-300">
                    <a href="/" class="block sm:inline">
                        会員登録はこちら<br class="sm:hidden">求人お試し検索もできます →
                    </a>
                </h2>
            </div>
        </section>
        <!-- 🔹 Register CTA Section - Only for Small Devices (スマホ用) -->
        <section class="py-10 px-4 bg-gradient-to-br from-yellow-100 via-orange-100 to-pink-100 block sm:hidden">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="bg-[#EA5514] text-white font-bold text-base sm:text-lg md:text-xl py-2 px-4 rounded-md inline-block leading-snug tracking-wide shadow-md hover:bg-[#d94d13] transition duration-300">
                    <a href="/" class="block">
                        会員登録はこちら<br>求人お試し検索もできます →
                    </a>
                </h2>
            </div>
        </section>
        <!-- FAQ Section -->
        <section class="bg-gray-100 py-12 px-4">
            <h2 class="text-xl sm:text-2xl md:text-3xl text-[#EA5514] font-bold text-center mb-6">よくある質問</h2>
            <div class="max-w-4xl mx-auto space-y-4">
                <details class="bg-white rounded shadow p-4" open>
                    <summary class="cursor-pointer font-semibold">Q. サービスは無料ですか？</summary>
                    <p class="mt-2 text-gray-700">はい、全ての求職者の方に無料でご利用いただけます。</p>
                </details>
                <details class="bg-white rounded shadow p-4">
                    <summary class="cursor-pointer font-semibold">Q. オファーした後でどうすればいいですか？</summary>
                    <p class="mt-2 text-gray-700">エージェントからの連絡をお待ちください。条件などを詳しくご説明します。</p>
                </details>
                <details class="bg-white rounded shadow p-4">
                    <summary class="cursor-pointer font-semibold">Q. 履歴書を保存できますか？</summary>
                    <p class="mt-2 text-gray-700">はい、PDFやExcel形式でダウンロード可能です。</p>
                </details>
            </div>
        </section>
    </div>
    <div class="py-20"></div>

    <!-- ✅ Sticky Footer with 3 CTA Buttons -->
    <footer class="fixed bottom-0 left-0 w-full bg-[#3B414A] text-white px-4 py-7 z-50 shadow-inner">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4 text-sm">

            <!-- 🔹 CTA Buttons -->
            <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                <a href="{{ route('contact.form') }}" class="bg-white text-[#3B414A] font-semibold text-xs sm:text-sm px-5 py-2 rounded-full shadow hover:bg-gray-100 transition">
                    お問い合わせ
                </a>
                <a href="/#trial-Search" class="bg-[#4CAF50] text-white font-semibold text-xs sm:text-sm px-5 py-2 rounded-full shadow hover:bg-green-600 transition">
                    求人検索
                </a>
                <a href="{{ route('signin') }}" class="bg-[#EA5514] text-white font-semibold text-xs sm:text-sm px-5 py-2 rounded-full shadow hover:bg-[#d94d13] transition">
                    会員登録
                </a>
            </div>

            <!-- 🔹 利用規約・プライバシー -->
            <div class="text-xs sm:text-sm text-center sm:text-right leading-snug">
                <a href="#" class="hover:underline">
                    {{ date('Y') }}年　しごとナビ利用規約・<span class="block sm:inline">個人情報保護に関する事項</span>
                </a>
            </div>
        </div>
    </footer>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();

    </script>
    <script src="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/glide.min.js"></script>
    <script>
        new Glide('.glide', {
            type: 'carousel'
            , autoplay: 4000
        }).mount();

    </script>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WV9TQS27" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
</body>

</html>
