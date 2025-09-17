@extends('layouts.top')

@section('title', 'マイページ')
@section('content')
    <section class="container py-2">
        <div class="row justify-content-center">
            <div class="col-12 col-md-9 col-lg-10">
                <div class=" py-3">
                    <div class="card-body text-center">
                        {{--  <h5 class="fw-bold">
                            {{ Auth::user()->name ?? '' }} 様
                        </h5>  --}}
                        @if (!empty($matchJobCount) && !is_null($matchingJobCount))
                            <h5 class="fw-bold">
                                マッチングされた求人:
                                <span class="text-main-theme">{{ $matchingJobCount }}</span> 件
                            </h5>
                        @endif

                        <!-- ✅ Session Messages -->
                        @if (session('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- ✅ Session Messages -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                @if (session('mode') === 'regist')
                                    <br>仕事内容は {{ session('jobDetail') }}
                                @elseif (session('mode') === 'cancel')
                                    <br>求人票内容は {{ session('jobDetail') }} のオファーをキャンセルしました。
                                @endif

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->has('error'))
                            <div class="alert text-main-theme">{{ $errors->first('error') }}</div>
                        @endif
                        {{--  <div class="mt-4 px-3">
                            <img src="{{ asset('img/offer.png') }}" class="img-fluid">
                        </div>  --}}
                        <!-- ✅ Jobs List -->
                        <div class="text-start mt-3">
                            @if (!empty($jobs) && count($jobs) > 0)
                                @foreach ($jobs as $job)
                                    @if ($job->offer_flag !== '3')
                                        <p>
                                            (<a
                                                href="https://match.shigotonavi.co.jp/matchings/detail/{{ $job->order_code }}">{{ $job->order_code }}</a>)
                                            {{ $job->job_type_detail }}

                                            @if ($job->offer_flag === '1')
                                                <span class="badge bg-success">にオファー済み</span>
                                            @elseif ($job->offer_flag === '2')
                                                <span class="badge bg-danger">キャンセル確定済み、再オファーできます。</span>
                                            @endif
                                        </p>
                                    @endif
                                @endforeach

                                @if ($hasConfirmedCancel || $hasCompletedOffer)
                                    {{--  <p class="text-main-theme">新しいオファーができます。</p>  --}}
                                @endif
                            @else
                                {{--  <p class="text-muted">まだオファーされていません</p>  --}}
                            @endif
                        </div>
                    </div>
                    <div class="container mt-2">
                        <div class="card py-3 px-1" style="border: 1px #bc1c45 solid;">
                            <h5 class="text-center fw-bold fs-f28 text-main-theme2">【しごとナビ】ご登録の求職者の方へ</h5>
                            <hr class="border-0 mx-auto my-3 text-main-theme" style="height: 1px; background-color: #000; width: 100%;">
                    
                            {{-- ✅ 「／」で改行 --}}
                            <p class="text-center px-2 fs-f18">
                                いつも当サービスをご利用いただき、誠にありがとうございます。<br>
                                この度、お客様の登録データの一部が正しく保存されていない事象が確認されましたので、<br>
                                ご報告と案内をさせていただきます。
                            </p>
                    
                            <div class="text-center fw-bold text-main-theme2">
                                <hr class="mx-auto my-3" style="border-top: 1px dashed #000; width: 100%;">
                                発生日時と該当期間<br>
                                2025年4月3日 13時頃 ～ 4月7日 16時頃
                                <hr class="mx-auto my-3" style="border-top: 1px dashed #000; width: 100%;">
                            </div>
                    
                            <p class="text-center fs-f18">
                                本来、サーバーに登録されるはずの個人情報の一部が正しく保存されないままでログオフの状態になっています。<br>
                                そのため、お手数ですが、下記のボタン【基本情報を確認する】より該当の期間に登録の方は、<br>
                                データが反映されているかご確認ください。
                            </p>
                    
                            {{-- ✅ ボタンのサイズを半分に縮小 --}}
                            <div class="text-center my-4 check-button">
                                <a href="{{ route('matchings.create') }}">
                                    <img src="{{ asset('/img/sn-web-button-check.png') }}" alt="基本情報を確認する">
                                </a>
                            </div>
                    
                            <p class="text-center fs-f18">
                                なお、この件に関しての外部への流出や漏れなどはしておりませんのでご安心ください。
                            </p>
                            <p class="text-center fs-f18">
                                お客様にはご不便をおかけしましたこと、深くお詫び申し上げます。<br>
                                今後はこのような事が無いように再発防止に努めてまいります。
                            </p>
                            <p class="text-center fs-f18">
                                何かご不明点やご質問がございましたら、お気軽にお知らせください。
                            </p>
                    
                            <p class="text-center mt-5">しごとナビ運営　リス株式会社</p>
                        </div>
                    </div>                    
                    
                    <div class="my-4 px-0">
                        {{--  <h4 class="text-center py-3">オファーの長離</h4>  --}}
                        <img src="{{ asset('img/canpin.png') }}" class="img-fluid w-100">
                    </div>
                    {{--  <div class="mt-4">
                        
                        <p class="fs-f22">
                            <span class="text-main-theme fs-f22">「しごとナビ」</span>では、転職サポートだけではなく、
                            手書きの履歴書や職務経歴書作成にかかる手間を軽減するため、
                            登録者全員に無料で作成できるウェブサービスを提供しています。
                            これにより、志望動機やアピールポイントを変更する際のミスや時間の浪費を防げます。
                            
                            <a href="{{ route('resume') }}">履歴書または職務経歴書作成はこちら</a>
                        </p>
                        <p class="fs-f22">
                            会員登録後に利用できるサービス「コンビニプリント」を使えば、履歴書をコンビニで印刷できます。
                        </p>
                        <img src="{{ asset('img/seven.png') }}" alt="">
                        <img src="{{ asset('img/family.png') }}" alt="">
                        <img src="{{ asset('img/ministop.jpg') }}" alt="">
                        <img src="{{ asset('img/lowson.webp') }}" alt=""> 
                    </div>  --}}
                    <div class="mt-5 container">
                        <h5 class="text-main-theme2 mt-5 fs-f27">履歴書または職務経歴書作成</h5>
                        <ul>
                            <li>
                                <p class="fs-f17">
                                    <span class="text-main-theme2 fw-bold">「しごとナビ」</span>では、転職サポートだけではなく、
                                    手書きの履歴書や職務経歴書作成にかかる手間を軽減するため、
                                    登録者全員に無料で作成できるウェブサービスを提供しています。
                                    これにより、志望動機やアピールポイントを変更する際のミスや時間の浪費を防げます。<br>
                                    <a href="{{ route('resume') }}" class="text-primary fs-f22">
                                        履歴書または職務経歴書作成はこちら
                                    </a>
                                </p>
                                <p class="fs-f17">
                                    会員登録後に利用できるサービス「コンプリ（コンビニプリント）」を使えば、履歴書をコンビニで印刷できます。
                                </p>
                                <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                                    <div class="col-2 col-sm-2 col-md-1 col-lg-1">
                                        <img src="{{ asset('img/seven.png') }}" alt="Seven Eleven"
                                            class="img-fluid rounded shadow w-100">
                                    </div>
                                    <div class="col-2 col-sm-2 col-md-1 col-lg-1">
                                        <img src="{{ asset('img/family.png') }}" alt="Family Mart"
                                            class="img-fluid rounded shadow w-100">
                                    </div>

                                    <div class="col-2 col-sm-2 col-md-1 col-lg-1">
                                        <img src="{{ asset('img/lowson.webp') }}" alt="Lawson"
                                            class="img-fluid rounded shadow w-100">
                                    </div>
                                    <div class="col-2 col-sm-2 col-md-1 col-lg-1">
                                        <img src="{{ asset('img/ministop.jpg') }}" alt="Family Mart"
                                            class="img-fluid rounded shadow w-100">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('resume') }}" class="text-primary mt-5 fs-f22">
                                        履歴書または職務経歴書作成はこちら
                                    </a>
                                </div>
                            </li>
                        </ul>

                        <div class="mt-3">
                            @if (!$hasMatching)
                                <div class="row">
                                    {{--  @foreach (explode("\n", '希望条件を登録してください。求人票が自動マッチングされます。') as $line)
                                        <div class="col-12 col-md-12 col-lg-12 my-2 ">
                                            <p class="text-break fs-f18">{{ $line }}</p>
                                        </div>
                                    @endforeach  --}}
                                    @foreach (explode("\n", '希望条件を登録すると、求人票が自動マッチングされます。') as $line)
                                        <div class="col-12 col-md-12 col-lg-12 my-2 ">
                                            <p class="text-break text-main-theme fs-f28 text-start">{{ $line }}</p>
                                        </div>
                                    @endforeach
                                </div>

                                <a href="{{ url('matchings/match') }}"
                                    class="btn btn-primary my-3 btn-sm py-3 px-3 fs-f18">
                                    希望条件でおしごとを探す
                                </a>
                            @else
                                <a href="{{ route('matchings.showmatch') }}"
                                    class="btn btn-main-theme btn-sm my-3 py-3 px-3 fs-f18">マッチングされた求人票一覧を見る</a>
                            @endif
                        </div>

                        <h5 class="text-main-theme2 mt-5 fs-f27">運営会社</h5>

                        <ul>
                            <li>
                                <div class="mt-3">
                                    {{--  <p>
                                        は44年以上の歴史を持ち、「人と企業、誰もがつながり合える社会創り」を目指しています。
                                        運営する転職サイトは110万人以上の会員を誇り、「人の成長が組織を成長させる」という信念を基に、努力と成果が実を結びやすい環境を提供しています。
                                        評価制度は明確で、固定給制を採用し、成長した社員には新たなポジションも用意しています。
                                        やる気があり、社会に貢献したいという思いを持つ方を歓迎し、共に新しい未来を築いていきたいと考えています。
                                    </p>  --}}
                                    {{-- <p class="fs-f18">
                                        <span
                                            class="text-main-theme">リス株式会社</span>は44年以上の歴史を持ち、「人と企業、誰もがつながり合える社会づくり」を目指しています。
                                        運営する転職サイト<span
                                            class="text-main-theme">「しごとナビ」は110万人以上の会員</span>を誇り、「人の成長が組織を成長させる」という信念を基に、努力と成果が実を結びやすい環境を提供しています。
                                        評価制度は明確で、固定給制を採用し、成長した社員には新たなポジションも用意しています。
                                        やる気があり、社会に貢献したいという思いを持つ方を歓迎し、ともに新しい未来を築いていきたいと考えています。
                                    </p> --}}
                                    <p>
                                        44年以上の歴史を持つ総合人材サービス<a href="https://www.lis21.co.jp" target="_new"
                                            rel="noopener noreferrer">リス株式会社</a>
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>


            </div>
        </div>
    </section>
@endsection
