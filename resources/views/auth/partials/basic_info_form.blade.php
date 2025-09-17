<form id="registerForm" action="{{ route('registration') }}" method="POST">
    @csrf
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="mb-2 row">
        {{-- 氏名 --}}
        <div class="col-md-6 col-12">
            <label for="name" class="form-label">お名前（漢字）<span class="badge bg-danger fs-f10 form-label">必須</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
        </div>

        {{-- 生年月日 --}}
        <div class="col-md-6 col-12">
            <label class="form-label">生年月日 <span class="badge bg-danger fs-f10 form-label">必須</span> <small class="text-secondary">(例：19710401)</small></label>
            {{--  <input type="text" name="birthday" class="form-control" maxlength="8" pattern="\d{8}" value="{{ old('birthday') }}">  --}}
            <input type="text" name="birthday" class="form-control" maxlength="8" pattern="(19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])" value="{{ old('birthday') }}">
        </div>
    </div>
    <div class="mb-2 row">
        {{-- 電話番号 --}}
        <div class="col-md-6 col-12">
            <label class="form-label">電話番号  <span class="badge bg-danger fs-f10 form-label">必須</span> <small class="text-secondary">(例：0359094120)</small></label>
            <input type="text" name="portable_telephone_number" class="form-control" value="{{ old('portable_telephone_number') }}">
        </div>

        {{-- メール --}}
        <div class="col-md-6 col-12">
            <label for="mail_address" class="form-label">メールアドレス <span class="badge bg-danger fs-f10">必須</span></label>
            <input type="email" name="mail_address" class="form-control" value="{{ old('mail_address') }}">
        </div>
    </div>

    {{-- パスワード --}}
    <div class="mb-2">
        <label class="form-label">パスワード <span class="badge bg-danger fs-f10">必須</span></label>
        <input type="password" name="password" class="form-control">
    </div>

    {{-- ボタン --}}
    <div class="col-12 col-md-6 m-auto">
        {{-- チェックボックス --}}
        <div class="form-check py-2">
            <input class="form-check-input" type="checkbox" id="agree">
            <label class="form-check-label fs-f12" for="agree">
                <a href="https://www.shigotonavi.co.jp/privacy/privacymark.asp" target="_blank" rel="noopener noreferrer">しごとナビ利用規約・個人情報保護に関する事項</a>に同意する
            </label>
        </div>
        <button name="action" value="view_jobs" class="btn btn-main-theme btn-lg w-100" id="submitButtonView" disabled>
            会員情報登録
        </button>
    </div>
   
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkbox = document.getElementById('agree');
            const submitButtonView = document.getElementById('submitButtonView');
            const submitButtonResume = document.getElementById('submitButtonResume');

            checkbox.addEventListener('change', function () {
                const enabled = checkbox.checked;
                submitButtonView.disabled = !enabled;
                submitButtonResume.disabled = !enabled;
            });
        });
    </script>
</form>
