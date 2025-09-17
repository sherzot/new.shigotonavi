<div class="container bg-white border p-4" style="max-width: 850px;">
    <h4 class="text-center fw-bold">履 歴 書</h4>
    <p class="text-end">{{ now()->format('Y年 m月 d日') }} 現在</p>

    <form wire:submit.prevent="save" class="container px-3 py-4" style="max-width: 850px;">
        @csrf

        {{-- 📌 基本情報 --}}
        <div class="row g-3">
            <h5 class="fw-medium text-primary">基本情報</h5>
            {{-- 氏名・フリガナ --}}
            <div class="col-md-6">
                <label class="form-label">氏名 (漢字)</label>
                <input type="text" name="name" class="form-control form-control-sm" wire:model.lazy="name">
                @error('name')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">氏名 (フリガナ)</label>
                <input type="text" name="name_f" class="form-control form-control-sm" wire:model.lazy="name_f">
                @error('name_f')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>

            {{-- メール・生年月日 --}}
            <div class="col-md-6">
                <label class="form-label">メールアドレス</label>
                <input type="email" name="mail_address" class="form-control form-control-sm" wire:model.lazy="mail_address">
                @error('mail_address')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">生年月日 <span class="text-main-theme">(例: 19710401)</span></label>
                <input type="text" name="birthday" class="form-control form-control-sm" wire:model.lazy="birthday">
                @error('birthday')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>

            {{-- 性別 --}}
            <div class="col-md-6">
                <label class="form-label d-block">性別</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sex" value="1" wire:model.lazy="sex" id="male">
                    <label class="form-check-label" for="male">男</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sex" value="2" wire:model.lazy="sex" id="female">
                    <label class="form-check-label" for="female">女</label>
                </div>
                @error('sex')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>

            {{-- 電話番号 --}}
            <div class="col-md-6">
                <label class="form-label">電話番号 <span class="text-main-theme">(例: 07009090808)</span></label>
                <input type="text" name="portable_telephone_number" class="form-control form-control-sm" wire:model.lazy="portable_telephone_number">
                @error('portable_telephone_number')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>

            {{-- 郵便番号 --}}
            <div class="col-md-6">
                <label class="form-label">郵便番号</label>
                <div class="row g-2">
                    <div class="col-5">
                        <input type="text" name="post_u" class="form-control form-control-sm" wire:model.lazy="post_u" maxlength="3" placeholder="123">
                        @error('post_u')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-1 d-flex align-items-center justify-content-center">-</div>
                    <div class="col-6">
                        <input type="text" name="post_l" class="form-control form-control-sm" wire:model.lazy="post_l" maxlength="4" placeholder="4567">
                        @error('post_l')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- 都道府県 --}}
            <div class="col-md-6">
                <label class="form-label">都道府県</label>
                <select name="prefecture_code" class="form-control form-control-sm" wire:model.lazy="prefecture_code" required>
                    <option value="">選択してください</option>
                    @foreach ($prefectures as $prefecture)
                    <option value="{{ $prefecture->code }}">{{ $prefecture->detail }}</option>
                    @endforeach
                </select>
                @error('prefecture_code')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>

            {{-- 住所 --}}
            <div class="col-md-6">
                <label class="form-label">区・市</label>
                <input type="text" name="city" class="form-control form-control-sm mb-1" wire:model.lazy="city">
                @error('city') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">区・市 (フリガナ)</label>
                <input type="text" name="city_f" class="form-control form-control-sm mb-1" wire:model.lazy="city_f">
                @error('city_f') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">町</label>
                <input type="text" name="town" class="form-control form-control-sm mb-1" wire:model.lazy="town">
                @error('town') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">町 (フリガナ)</label>
                <input type="text" name="town_f" class="form-control form-control-sm" wire:model.lazy="town_f">
                @error('town_f') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">番地など </label>
                <input type="text" name="address" class="form-control form-control-sm mb-1" wire:model.lazy="address">
                @error('address')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">番地など (フリガナ)</label>
                <input type="text" name="address_f" class="form-control form-control-sm mb-1" wire:model.lazy="address_f">
                @error('address_f')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>
        </div>
        {{-- 🎓 学歴情報 --}}
        <div class="mt-5">
            <h5 class="fw-medium text-primary">学歴</h5>
            @foreach ($educations as $index => $education)
            <div class="card mb-3 border rounded p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">学校名</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="educations.{{ $index }}.school_name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">学校種別</label>
                        <select class="form-select form-select-sm" wire:model.lazy="educations.{{ $index }}.school_type_code">
                            <option value="">選択してください</option>
                            @foreach ($schoolTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                        @error('school_type_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">専攻</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="educations.{{ $index }}.speciality">
                        @error('speciality') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">コース種別</label>
                        <select class="form-select form-select-sm" wire:model.lazy="educations.{{ $index }}.course_type">
                            <option value="">選択してください</option>
                            @foreach ($courseTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                        @error('course_type') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">入学年</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="educations.{{ $index }}.entry_day_year">
                        @error('entry_day_year') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">入学月</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="educations.{{ $index }}.entry_day_month">
                        @error('entry_day_month') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">入学タイプ</label>
                        <select class="form-select form-select-sm" wire:model.lazy="educations.{{ $index }}.entry_type_code">
                            <option value="">選択してください</option>
                            @foreach ($entryTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                        @error('entry_type_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">卒業年</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="educations.{{ $index }}.graduate_day_year">
                        @error('graduate_day_year') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">卒業月</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="educations.{{ $index }}.graduate_day_month">
                        @error('graduate_day_month') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">卒業タイプ</label>
                        <select class="form-select form-select-sm" wire:model.lazy="educations.{{ $index }}.graduate_type_code">
                            <option value="">選択してください</option>
                            @foreach ($graduateTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                        @error('graduate_type_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="button" class="btn btn-danger btn-sm mt-2" wire:click.prevent="removeEducationRow({{ $index }})">
                            削除
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="text-start">
                <button type="button" class="btn btn-outline-primary btn-sm" wire:click.prevent="addEducationRow">
                    + 学歴追加
                </button>
            </div>
        </div>
        {{-- 💼 職歴情報 --}}
        <div class="mt-5">
            <h5 class="fw-medium text-primary">職歴</h5>
            {{-- 💼 職歴情報 --}}
            @foreach ($careers as $index => $career)
            <div class="card mb-3 border rounded p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">会社名</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.company_name">
                        @error('company_name') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">資本金 (万円)</label>
                        <input type="number" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.capital">
                        @error('capital') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">従業員数</label>
                        <input type="number" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.number_employees">
                        @error('number_employees') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">入社年</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.entry_day_year">
                        @error('entry_day_year') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">入社月</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.entry_day_month">
                        @error('entry_day_month') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">退社年</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.retire_day_year">
                        @error('retire_day_year') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">退社月</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.retire_day_month">
                        @error('retire_day_month') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">業種</label>
                        <select class="form-select form-select-sm" wire:model.lazy="careers.{{ $index }}.industry_type_code">
                            <option value="">選択してください</option>
                            @foreach ($industryTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                        @error('industry_type_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">勤務形態</label>
                        <select class="form-select form-select-sm" wire:model.lazy="careers.{{ $index }}.working_type_code">
                            <option value="">選択してください</option>
                            @foreach ($workingTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                        @error('working_type_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">職種名</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.job_type_detail">
                        @error('job_type_detail') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">職種（大分類）</label>
                        <select class="form-select form-select-sm" wire:model.lazy="careers.{{ $index }}.job_type_big_code">
                            <option value="">選択してください</option>
                            @foreach ($jobTypes->unique('big_class_code') as $type)
                            <option value="{{ $type->big_class_code }}">{{ $type->big_class_name }}</option>
                            @endforeach
                        </select>
                        @error('job_type_big_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">職種（小分類）</label>
                        <select class="form-select form-select-sm" wire:model.lazy="careers.{{ $index }}.job_type_small_code">
                            <option value="">選択してください</option>
                            @foreach ($this->getSmallJobTypes($index) as $type)
                            <option value="{{ $type->middle_class_code }}">{{ $type->middle_clas_name }}</option>
                            @endforeach
                        </select>
                        @error('job_type_small_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">職務内容</label>
                        <textarea class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.business_detail" rows="2"></textarea>
                        @error('business_detail') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="button" class="btn btn-danger btn-sm mt-2" wire:click.prevent="removeCareerRow({{ $index }})">
                            削除
                        </button>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="text-start">
                <button type="button" class="btn btn-outline-primary btn-sm" wire:click.prevent="addCareerRow">
                    + 職歴追加
                </button>
            </div>
        </div>
        @if ($errors->any())
        <div id="error-summary" class="alert alert-danger">
            <h5 class="fw-bold">入力エラーがあります:</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        {{-- <button type="button" class="btn btn-success" wire:click="addResumePreference">＋志望動機追加</button>  --}}
        <div wire:loading wire:target="save" class="text-center mt-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">保存中...</span>
            </div>
            <div class="text-primary small mt-2">保存中...</div>
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('mypage') }}" class="btn btn-outline-secondary btn-sm px-4">戻る</a>
            <button type="submit" class="btn btn-primary btn-sm px-4 me-2">保存して求人検索<i class="fa-solid fa-arrow-down px-2"></i></button>
        </div>
    </form>

</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ✅ 開発モード判定 (Laravel環境をBladeで渡す)
    const isDev = "{{ app()->environment('local') }}" === "local";

    function showAlert(type, message, detail = null) {
        console.log('⚡ showAlert called with:', type, message, detail);

        if (!type || (type !== 'success' && type !== 'error')) {
            console.warn('⚠️ 未知のtypeが渡されました:', type);
            type = 'error';
        }

        // fallback message
        let fallbackMessage = (type === 'success') ?
            '保存に成功しました。' :
            'エラーが発生しました。';

        let formattedMessage = (message || fallbackMessage).replace(/\n/g, '<br>');

        // ➡️ local環境なら詳細エラーも表示
        if (isDev && type === 'error' && detail) {
            formattedMessage += `<hr><pre style="text-align:left;">${detail}</pre>`;
        }

        Swal.fire({
            icon: type
            , title: type === 'success' ? '成功' : 'エラー'
            , html: formattedMessage
            , confirmButtonText: 'OK'
            , allowOutsideClick: false
            , allowEscapeKey: false
            , timer: type === 'success' ? 3000 : undefined
            , timerProgressBar: type === 'success'
        }).then(() => {
            if (type === 'success') {
                {{--  window.location.href = "{{ route('resume.preview') }}"; // 成功時はプレビュー画面へ遷移  --}}
                Swal.fire({ ... }); // リダイレクトなし
            }
        });
        // ✅ エラーがある要素にスクロールする（少し遅延して実行）
        if (type === 'error') {
            setTimeout(() => {
                const el = document.querySelector('.scroll-target');
                if (el) {
                    el.scrollIntoView({
                        behavior: 'smooth'
                        , block: 'center'
                    });
                    el.classList.add('border', 'border-danger', 'rounded', 'p-1');
                }
            }, 300);
        }
    }

    // ✅ Livewireイベントリスナー

    window.addEventListener('saved', event => {
        console.log('✅ [saved] event received:', event.detail);
        showAlert('success', event.detail.message);
    });

    window.addEventListener('alert', event => {
        console.log('⚠️ [alert] event received:', event.detail);
        const type = event.detail.type || 'error';
        const message = event.detail.message;
        const detail = event.detail.trace || null; // ← trace情報も渡せる
        showAlert(type, message, detail);
    });
</script>
@endpush


