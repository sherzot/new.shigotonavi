<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
    <title>会社登録</title>
    <link rel="stylesheet" href="{{ asset('style/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('style/first-form/form.css') }}">
    <script src="{{ asset('js/form.js') }}"></script>
    <link href="{{ asset('img/Logo-shigotonavi-mark.svg') }}" rel="icon" type="image/svg+xml">
    <script src="{{ asset('js/humburger.js') }}"></script>
    <link href="{{ asset('img/icon.png') }}" rel="icon" type="image/svg+xml">
    {{-- HUMBURGER --}}
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>
    <!-- 会社情報登録フォーム -->
    <section>
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5" id="content">
                    <div class="card shadow-2-strong" style="border-radius: 1rem;">
                        <div class="card-body p-3 text-center">
                        <header>
            <h3 class="mb-3 mt-2">
                <a href="/">
                    <img src="{{ asset('img/logo02.png') }}" alt="logo" class="w-sm-25 w-50">
                </a>
            </h3>
            <button class="hamburger">
                <div>
                    <p>&#9776;</p>
                </div>
            </button>
            <button class="cross"><i class="fa-solid fa-xmark"></i></button>
        </header>
        <div class="menu">
            <ul>
                <a href="{{ route('profile.profile') }}">
                    <li><i class="fa-solid fa-user-tie"></i>基本情報</li>
                </a>
                 <a href="{{ route('matchings.create') }}">
                    <li><i class="fa-solid fa-file-pen"></i>基本情報変更</li>
                </a>
                <a href="#">
                    <li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST">
                            @csrf
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <li><i class="fa-solid fa-right-from-bracket"></i> ログアウト</li>
                            </a>
                        </form>
                    </li>
                </a>
            </ul>
        </div>
                            <form action="{{ route('create_company.store') }}" method="POST">
                                @csrf
                                <h3 class="mb-3 mt-2">
                                    <a href="/">
                                        <img src="{{ asset('img/logo02.png') }}" alt="logo"
                                            class="logo w-sm-25 w-50">
                                    </a>
                                </h3>
            
                                <h5 style="line-height: 2;">会社登録</h5>

                                <div class="row">
                                    <!-- 会社名前 -->
                                    <div class="mb-4 col-6">
                                        <label for="company_name" class="form-label" id="radioCheck">会社名<span
                                                style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                        <input type="text" name="company_name" id="company_name"
                                            class="form-control border-primary" required>
                                    </div>

                                    <!-- 支店名  -->
                                    <div class="mb-4 col-6">
                                        <label for="section_name" class="form-label" id="radioCheck">支店名<span
                                                style="color: rgba(255, 0, 0, 0.674);"></span></label><!- 必須  -->
                                        <input type="text" name="section_name" id="section_name"
                                            class="form-control border-primary" >
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="surname" class="col form-label text-start">ご担当者（フルネーム）<span
                                            style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                </div>
                                <div class="row form-outline mb-4" data-mdb-input-init>
                                    <div class="col-6">
                                        <input type="text" name="surname" id="surname"
                                            class="form-control border-primary" placeholder="姓" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="name" id="name"
                                            class="form-control border-primary" placeholder="名" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div data-mdb-input-init class="form-outline mb-4 col-6">
                                        <label class="form-label pt-1 float-start" for="email">メール <span
                                                style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                        <input type="email" name="mail_address" id="email" aria-label="メールアドレス"
                                            class="form-control form-control-lg border border-primary" required />
                                        <span id="error-message"></span>
                                    </div>
                                    <!-- 電話番号 -->
                                    <div class="mb-4 col-6">
                                        <label for="tel" class="form-label" id="radioCheck">電話番号<span
                                                style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                        <input type="text" name="tel" id="tel"
                                            class="form-control border-primary" required>
                                    </div>
                                </div>

                                <div class="row">
                                    {{--  <!-- 会社名前 -->  --}}
                                    <!-- <div class="mb-4 col-6">
                                        <label for="company_name" class="form-label" id="radioCheck">会社名<span
                                                style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                        <input type="text" name="company_name" id="company_name"
                                            class="form-control border-primary" required>
                                    </div> -->

                                    {{--  <!-- 支店名  -->  --}}
                                    <!-- <div class="mb-4 col-6">
                                        <label for="section_name" class="form-label" id="radioCheck">支店名<span
                                                style="color: rgba(255, 0, 0, 0.674);"></span></label>
                                        <input type="text" name="section_name" id="section_name"
                                            class="form-control border-primary" ><!-- required -->
                                    </div>
                                </div> -->
                               <div class="row">
                                {{--  <!-- 就業場所名  -->  --}}
                                <div class="mb-4">
                                    <label for="working_place_companyname" class="form-label" id="radioCheck">就業場所名<span
                                            style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                    <input type="text" name="working_place_companyname" id="working_place_companyname"
                                        class="form-control border-primary" required>
                                </div> 
                                {{--  <!-- 郵便番号 -->  --}}
                                <div class="mb-4">
                                    <label for="post" class="form-label" id="radioCheck">郵便番号<span
                                            style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                    <input type="text" name="post" id="post"
                                        class="form-control border-primary" required maxlength="7">
                                </div>
                                <!-- <div class="row">
                                    <div class="mb-4 col-6">
                                        <label for="prefecture_code" class="form-label"
                                            id="radioCheck">県を選択してください<span
                                            style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                        <select class="col-12" id="prefecture_code" name="prefecture_code"
                                            onchange="needcount();" onkeyup="needcount();">
                                            <option value="">(選択してください)</option>
                                            <option value="090">全国</option>
                                            <option value="091">東北</option>
                                            <option value="092">関東</option>
                                            <option value="093">甲信越</option>
                                            <option value="094">北陸</option>
                                            <option value="095">東海地方</option>
                                            <option value="096">関西</option>
                                            <option value="097">中国</option>
                                            <option value="098">四国</option>
                                            <option value="099">九州</option>
                                        </select>

                                    </div> -->
                                    <div class="mb-4 col-6">
                                        <label for="city" class="form-label"
                                            id="radioCheck">県名を選択してください<span
                                            style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                        <select class="col-12" name="prefecture_code" id="prefecture_code" onchange="needcount();"
                                            onkeyup="needcount();">
                                            <option value="001">北海道</option>
                                            <option value="002">青森県</option>
                                            <option value="003">岩手県</option>
                                            <option value="004">宮城県</option>
                                            <option value="005">秋田県</option>
                                            <option value="006">山形県</option>
                                            <option value="007">福島県</option>
                                            <option value="008">茨城県</option>
                                            <option value="009">栃木県</option>
                                            <option value="010">群馬県</option>
                                            <option value="011">埼玉県</option>
                                            <option value="012">千葉県</option>
                                            <option value="013">東京都</option>
                                            <option value="014">神奈川県</option>
                                            <option value="015">山梨県</option>
                                            <option value="016">長野県</option>
                                            <option value="017">岐阜県</option>
                                            <option value="018">新潟県</option>
                                            <option value="019">富山県</option>
                                            <option value="020">石川県</option>
                                            <option value="021">福井県</option>
                                            <option value="022">静岡県</option>
                                            <option value="023">愛知県</option>
                                            <option value="024">三重県</option>
                                            <option value="025">滋賀県</option>
                                            <option value="026">奈良県</option>
                                            <option value="027">和歌山県</option>
                                            <option value="028">京都府</option>
                                            <option value="029">大阪府</option>
                                            <option value="030">兵庫県</option>
                                            <option value="031">鳥取県</option>
                                            <option value="032">島根県</option>
                                            <option value="033">岡山県</option>
                                            <option value="034">広島県</option>
                                            <option value="035">山口県</option>
                                            <option value="036">徳島県</option>
                                            <option value="037">香川県</option>
                                            <option value="038">愛媛県</option>
                                            <option value="039">高知県</option>
                                            <option value="040">福岡県</option>
                                            <option value="041">佐賀県</option>
                                            <option value="042">長崎県</option>
                                            <option value="043">熊本県</option>
                                            <option value="044">大分県</option>
                                            <option value="045">宮崎県</option>
                                            <option value="046">鹿児島県</option>
                                            <option value="047">沖縄県</option>
                                        </select>

                                    </div>
                                    <div class="mb-4">
                                        <label for="address" class="form-label" id="radioCheck">完全な住所<span
                                                style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                        <input type="text" name="address" id="address"
                                            class="form-control border-primary" required>
                                    </div>
                                </div>



                                <!-- 業種 -->
                                <div class="row">
                                    <!-- 業種ー -->
                                    <div class="mb-4 col-6">
                                        <label for="industorytype" class="form-label">業種<span
                                            style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                        <select name="industory_typey" id="industory_type" required
                                            class="form-control border-primary p-3">
                                            <option value="">業種を選択してください</option>
                                    	@foreach ($industoryTypes as $industoryType)
                                          <option value="{{ $industoryType->code }}" {{ old('big_class_code') == $industoryType->code ? 'selected' : '' }}>
                                            {{ $industoryType->detail }}
                                          </option>
                                    	@endforeach
                                        </select>
                                    </div>


                                    <!-- 仕事内容 -->
                                    <div class="mb-3">
                                        <label for="exampleFormControlTextarea1" class="form-label">仕事内容<span
                                                style="color: rgba(255, 0, 0, 0.674);">必須</span></label>
                                        <textarea class="form-control" name="business_detail" id="exampleFormControlTextarea1" rows="3"></textarea>
                                    </div>
                                </div>


                                <div class="form-check py-3">
                                    <input type="checkbox" id="flexCheckChecked" class="form-check-input">
                                    <label for="flexCheckChecked" class="form-check-label">
                                        <a
                                            href="https://www.shigotonavi.co.jp/privacy/privacymark.asp">しごとナビ利用規約・個人情報保護に関する事項に同意する</a>
                                    </label>
                                </div>

                                <button id="submitButton" class="btn btn-red btn-lg btn-block" name="submit"
                                    type="submit" disabled
                                    style="background-color: rgba(255, 0, 0, 0.674); color: #fff;">
                                    登録
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const annualRadio = document.getElementById('annual-radio');
            const hourlyRadio = document.getElementById('hourly-radio');
            const annualInput = document.getElementById('annual-input');
            const hourlyInput = document.getElementById('hourly-input');

            function toggleInputs() {
                annualInput.disabled = !annualRadio.checked;
                annualInput.required = annualRadio.checked;

                hourlyInput.disabled = !hourlyRadio.checked;
                hourlyInput.required = hourlyRadio.checked;

                if (!annualRadio.checked) annualInput.value = '';
                if (!hourlyRadio.checked) hourlyInput.value = '';
            }

            annualRadio.addEventListener('change', toggleInputs);
            hourlyRadio.addEventListener('change', toggleInputs);

            toggleInputs();
        });
    </script>

    <script>
        document.getElementById('postal_code').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // 数字以外の文字を削除する
            if (value.length > 3) {
                value = value.slice(0, 3) + '-' + value.slice(3);
            }
            e.target.value = value;
        });
    </script>
</body>

</html>
