<div class="container bg-white border px-1 px-sm-4 py-3" style="max-width: 850px;">
    <h4 class="text-center fw-bold">履歴書・職務経歴書入力</h4>
    <p class="text-end">{{ now()->format('Y年 m月 d日') }} 現在</p>
    @php
        if (session('apply_job')) {
            session()->put('apply_job', session('apply_job'));
        }
    @endphp

    <form wire:submit.prevent="save" class="container px-3 py-4" style="max-width: 850px;">
        @csrf

        {{-- 📌 基本情報 --}}
        <div class="row g-3">
            <h5 class="fw-medium text-primary">基本情報</h5>
            <div class="col-md-4">
                <label class="form-label">履歴書写真</label>
                <div class="d-flex align-items-start gap-3">
                    {{-- プレビュー表示 --}}
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" class="img-thumbnail" style="width: 80px; height: auto;">
                    @elseif ($existingPhoto)
                        <img src="{{ $existingPhoto }}" class="img-thumbnail" style="width: 80px; height: auto;">
                    @endif
            
                    <div class="flex-grow-1">
                        {{-- アップロードボタン --}}
                        <label class="btn btn-outline-secondary btn-sm mb-1">
                            ファイルを選択
                            <input type="file" wire:model="photo" class="d-none">
                        </label>
            
                        {{-- アップロード状態 --}}
                        <div wire:loading wire:target="photo" class="mt-2">
                            <div class="spinner-border text-primary spinner-border-sm" role="status">
                                <span class="visually-hidden">アップロード中...</span>
                            </div>
                            <span class="text-primary small ms-2">写真アップロード中...</span>
                        </div>
            
                        {{-- ファイル名 / 状態 --}}
                        <div class="mt-1 small text-muted">
                            @if ($photo)
                                選択: {{ $photo->getClientOriginalName() }}
                            @elseif ($existingPhoto)
                                登録済みの写真を表示中。
                            @else
                                
                            @endif
                        </div>
            
                        @error('photo')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            
            {{-- 氏名・フリガナ --}}
            <div class="col-md-4">
                <label class="form-label">お名前 (漢字)<span class="badge bg-danger fs-f10">必須</span></label>
                <input type="text" name="name" class="form-control form-control-sm" wire:model.lazy="name">
                @error('name')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">お名前 (フリガナ) <span class="badge bg-danger fs-f10">必須</span></label>
                <input type="text" name="name_f" class="form-control form-control-sm" wire:model.lazy="name_f">
                @error('name_f')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>

            {{-- 性別 --}}
            <div class="col-md-4">
                <label class="form-label d-block">性別 <span class="badge bg-danger fs-f10">必須</span></label>
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
            <div class="col-md-4">
                <label class="form-label">生年月日 <span class="badge bg-danger fs-f10">必須</span><span class="text-main-theme fs-f12">(例: 19710401)</span></label>
                <input type="text" name="birthday" class="form-control form-control-sm" wire:model.lazy="birthday">
                @error('birthday')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>
            {{-- 電話番号 --}}
            <div class="col-md-4">
                <label class="form-label">電話番号 <span class="badge bg-danger fs-f10">必須</span><span class="text-main-theme fs-f12">(例: 07009090808)</span></label>
                <input type="text" name="portable_telephone_number" class="form-control form-control-sm" wire:model.lazy="portable_telephone_number">
                @error('portable_telephone_number')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>
            {{-- 電話番号 --}}
            <div class="col-md-4">
                <label class="form-label">緊急連絡先 <span class="text-main-theme fs-f12">(例: 07009090808)</span></label>
                <input type="text" name="home_telephone_number" class="form-control form-control-sm" wire:model.lazy="home_telephone_number">
                @error('home_telephone_number')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>

            {{-- 郵便番号 --}}
            <div class="col-md-4">
                <label class="form-label">郵便番号<span class="badge bg-danger fs-f10">必須</span></label>
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
            <div class="col-md-4">
                <label class="form-label">都道府県<span class="badge bg-danger fs-f10">必須</span></label>
                <select name="prefecture_code" class="form-control form-control-sm" wire:model.lazy="prefecture_code">
                    <option value="">選択してください</option>
                    @foreach ($prefectures as $prefecture)
                    <option value="{{ $prefecture->code }}">{{ $prefecture->detail }}</option>
                    @endforeach
                </select>
                @error('prefecture_code')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>

            {{-- 住所 --}}
            <div class="col-md-4">
                <label class="form-label">区・市</label>
                <input type="text" name="city" class="form-control form-control-sm mb-1" wire:model.lazy="city">
                @error('city') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">区・市 (フリガナ)</label>
                <input type="text" name="city_f" class="form-control form-control-sm mb-1" wire:model.lazy="city_f">
                @error('city_f') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">町</label>
                <input type="text" name="town" class="form-control form-control-sm mb-1" wire:model.lazy="town">
                @error('town') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">町 (フリガナ)</label>
                <input type="text" name="town_f" class="form-control form-control-sm" wire:model.lazy="town_f">
                @error('town_f') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">番地など </label>
                <input type="text" name="address" class="form-control form-control-sm mb-1" wire:model.lazy="address">
                @error('address')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">番地など (フリガナ)</label>
                <input type="text" name="address_f" class="form-control form-control-sm mb-1" wire:model.lazy="address_f">
                @error('address_f')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>

            <p class="text-main-theme pb-0 mb-0">メールアドレスとパスワードがログインする時に必要です！</p>
             {{-- メール --}}
             <div class="col-md-6 pt-0 mt-0">
                <label class="form-label">メールアドレス <span class="badge bg-danger fs-f10">必須</span></label>
                <input type="email" name="mail_address" class="form-control form-control-sm" wire:model.lazy="mail_address">
                @error('mail_address')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>
             {{-- パスワード --}}
             <div class="col-md-6 pt-0 mt-0">
                <label class="form-label">パスワード<span class="badge bg-danger fs-f10">必須</span></label>
                <input type="password" name="password" class="form-control form-control-sm" wire:model.lazy="password">
                @error('password')<div class="text-danger small scroll-target">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- 🎓 学歴情報 --}}
        <div class="mt-5">
            <h5 class="fw-medium text-primary">学歴 <span class="badge bg-danger fs-f10">必須</span></h5>
            @foreach ($educations as $index => $education)
            <div class="card mb-3 border rounded p-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">学校名 <span class="badge bg-danger fs-f10">必須</span></label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="educations.{{ $index }}.school_name" required>
                        {{--  <input type="text" class="form-control form-control-sm" wire:model.defer="educations.{{ $index }}.school_name" required>  --}}
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">学校種別</label>
                        <select class="form-select form-select-sm" wire:model.lazy="educations.{{ $index }}.school_type_code">
                            <option value="">選択してください</option>
                            @foreach ($schoolTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                        @error('school_type_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">専攻</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="educations.{{ $index }}.speciality">
                        @error('speciality') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
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
            <div class="text-end">
                <button type="button" class="btn btn-outline-primary btn-sm" wire:click.prevent="addEducationRow">
                    + 学歴追加
                </button>
            </div>
        </div>
        {{-- 💼 職歴情報 --}}
        <div class="mt-5">
            <h5 class="fw-medium text-primary">職歴 <span class="badge bg-danger fs-f10">必須</span></h5>
            {{-- 💼 職歴情報 --}}
            @foreach ($careers as $index => $career)
            <div class="card mb-3 border rounded p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">会社名 <span class="badge bg-danger fs-f10">必須</span></label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.company_name" required>
                        {{--  <input type="text" class="form-control form-control-sm" wire:model.defer="careers.{{ $index }}.company_name" required>  --}}
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
                    <div class="col-md-4">
                        <label class="form-label">業種</label>
                        <select class="form-select form-select-sm" wire:model.lazy="careers.{{ $index }}.industry_type_code">
                            <option value="">選択してください</option>
                            @foreach ($industryTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                        @error('industry_type_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">勤務形態</label>
                        <select class="form-select form-select-sm" wire:model.lazy="careers.{{ $index }}.working_type_code">
                            <option value="">選択してください</option>
                            @foreach ($workingTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                        @error('working_type_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">職種名</label>
                        <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.job_type_detail">
                        @error('job_type_detail') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">職種（大分類）</label>
                        <select class="form-select form-select-sm" wire:model.lazy="careers.{{ $index }}.job_type_big_code">
                            <option value="">選択してください</option>
                            @foreach ($jobTypes->unique('big_class_code') as $type)
                            <option value="{{ $type->big_class_code }}">{{ $type->big_class_name }}</option>
                            @endforeach
                        </select>
                        @error('job_type_big_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">職種（小分類）</label>
                        <select class="form-select form-select-sm" wire:model.lazy="careers.{{ $index }}.job_type_small_code">
                            <option value="">選択してください</option>
                            @foreach ($this->getSmallJobTypes($index) as $type)
                            <option value="{{ $type->middle_class_code }}">{{ $type->middle_clas_name }}</option>
                            @endforeach
                        </select>
                        @error('job_type_small_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
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

            <div class="text-end">
                <button type="button" class="btn btn-outline-primary btn-sm" wire:click.prevent="addCareerRow">
                    + 職歴追加
                </button>
            </div>
            <!-- 🎓 資格セクション -->
            <div class="mt-5">
                <h5 class="fw-medium text-primary">資格</h5>
                @foreach ($licenses as $index => $license)
                <div class="card mb-3 border rounded p-3">
                    <div class="row g-3">
                        <!-- 資格グループ -->
                        <div class="col-md-4">
                            <label class="form-label">資格グループ</label>
                            <select class="form-select form-select-sm" wire:model.lazy="licenses.{{ $index }}.group_code">
                                <option value="">選択してください</option>
                                @foreach ($licenseGroups as $group)
                                <option value="{{ $group->group_code }}">
                                    {{ $group->group_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('group_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                        </div>

                        <!-- カテゴリ名 -->
                        <div class="col-md-4">
                            <label class="form-label">カテゴリ名</label>
                            <select class="form-select form-select-sm" wire:model.lazy="licenses.{{ $index }}.category_code">
                                <option value="">選択してください</option>
                                @foreach ($licenseCategories[$license['group_code']] ?? [] as $category)
                                <option value="{{ $category->category_code }}">
                                    {{ $category->category_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                        </div>

                        <!-- 資格名 -->
                        <div class="col-md-4">
                            <label class="form-label">資格名</label>
                            <select class="form-select form-select-sm" wire:model.lazy="licenses.{{ $index }}.code">
                                <option value="">選択してください</option>
                                @php
                                $comboKey = $license['group_code'] . '_' . $license['category_code'];
                                @endphp
                                @foreach ($licenseNames[$comboKey] ?? [] as $item)
                                <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @error('code') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                        </div>

                        <!-- 取得年月日 -->
                        <div class="col-md-6">
                            <label class="form-label">取得年月日 <span class="text-main-theme">(例: 20240101)</span></label>
                            <input type="text" class="form-control form-control-sm" wire:model.lazy="licenses.{{ $index }}.get_day" maxlength="8">
                            @error('get_day') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                        </div>

                        <!-- 削除ボタン -->
                        <div class="col-md-12 text-end">
                            <button type="button" class="btn btn-danger btn-sm mt-2" wire:click.prevent="removeLicenseRow({{ $index }})">
                                削除
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- 追加ボタン -->
                <div class="text-end">
                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click.prevent="addLicenseRow">
                        ＋ 資格追加
                    </button>
                </div>
            </div>
        </div>
        <!-- 自己PR -->
        <div class="col-12 mb-5">
            <h5 class="text-primary mb-4 fw-medium">自己PR</h5>
            <textarea class="form-control border-dark" rows="2" wire:model.lazy="self_pr" placeholder="ここに自己PRを入力してください"></textarea>
            <small class="text-muted">※履歴書へ表示されるのは全角200文字までです。(出力形式により多少前後します。)</small>
            @error('self_pr') <div class="text-danger scroll-target">自己PRは200文字以内で入力してください。</div> @enderror
        </div>
        <div class="mb-4">
            <h5 class="text-primary mb-3">志望動機・希望欄</h5>
            <div class="row g-2">
        
                {{-- 希望通勤時間 --}}
                <div class="col-md-4">
                    <label class="form-label">希望通勤時間</label>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control border-dark" placeholder="例: 10" wire:model.defer="resumePreference.commute_time">
                        <span class="input-group-text">分</span>
                    </div>
                    @error('resumePreference.commute_time') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                </div>
        
                {{-- 結婚状況 --}}
                <div class="col-md-4">
                    <label class="form-label">結婚状況</label>
                    <select class="form-select form-select-sm border-dark" wire:model.defer="marriage_flag">
                        <option value="">選択してください</option>
                        <option value="0">未婚</option>
                        <option value="1">既婚</option>
                    </select>
                    @error('marriage_flag') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                </div>
        
                {{-- 扶養家族 --}}
                <div class="col-md-4">
                    <label class="form-label">扶養家族（配偶者を除く）</label>
                    <input type="number" class="form-control form-control-sm border-dark" wire:model.defer="dependent_number">
                    @error('dependent_number') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                </div>
        
                {{-- 配偶者の扶養義務 --}}
                <div class="col-md-4">
                    <label class="form-label">配偶者の扶養義務</label>
                    <select class="form-select form-select-sm border-dark" wire:model.defer="dependent_flag">
                        <option value="">選択してください</option>
                        <option value="0">無</option>
                        <option value="1">有</option>
                    </select>
                    @error('dependent_flag') <div class="text-danger small scroll-target">{{ $message }}</div> @enderror
                </div>
        
                {{-- 志望動機 --}}
                <div class="col-md-4">
                    <label class="form-label">志望動機</label>
                    <textarea class="form-control form-control-sm border-dark" rows="2" wire:model.defer="resumePreference.wish_motive"></textarea>
                    <small class="text-muted">※履歴書へ表示されるのは全角200文字までです。</small>
                    @error('resumePreference.wish_motive') <div class="text-danger scroll-target">志望動機は200文字以内で入力してください。</div> @enderror
                </div>
        
                {{-- 本人希望欄 --}}
                <div class="col-md-4">
                    <label class="form-label">本人希望欄</label>
                    <textarea class="form-control form-control-sm border-dark" rows="2" wire:model.defer="resumePreference.hope_column"></textarea>
                    <small class="text-muted">※履歴書へ表示されるのは全角200文字までです。</small>
                    @error('resumePreference.hope_column') <div class="text-danger scroll-target">本人希望欄は200文字以内で入力してください。</div> @enderror
                </div>
        
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
            <button type="submit" class="btn btn-primary btn-sm p-2 me-2 w-50 fs-f18">保存する</button>
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

            let fallbackMessage = (type === 'success') ? '保存に成功しました。' : 'エラーが発生しました。';
            let formattedMessage = (message || fallbackMessage).replace(/\n/g, '<br>');

            if (isDev && type === 'error' && detail) {
                formattedMessage += `<hr><pre style="text-align:left;">${detail}</pre>`;
            }

            Swal.fire({
                icon: type,
                title: type === 'success' ? '成功' : 'エラー',
                html: formattedMessage,
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                allowEscapeKey: false,
                timer: type === 'success' ? 3000 : undefined,
                timerProgressBar: type === 'success'
            });

            if (type === 'error') {
                setTimeout(() => {
                    const el = document.querySelector('.scroll-target');
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.classList.add('border', 'border-danger', 'rounded', 'p-1');
                    }
                }, 300);
            }
        }

        window.addEventListener('saved', event => {
            console.log('✅ [saved] event received:', event.detail);
            showAlert('success', event.detail.message);
        });
        window.addEventListener('resumeCompleted', () => {
            setTimeout(() => {
                const resumeBlock = document.getElementById('registResume');
                const searchBlock = document.getElementById('searchJob');
    
                if (resumeBlock && searchBlock) {
                    resumeBlock.parentNode.insertBefore(searchBlock, resumeBlock);
                    searchBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    console.log('🔼 求人検索フォーム moved above 履歴書');
                } else {
                    console.warn('⛔ Block not found:', { resumeBlock, searchBlock });
                }
            }, 500); // DOM tayyor bo‘lguncha kutish
        });

        window.addEventListener('alert', event => {
            console.log('⚠️ [alert] event received:', event.detail);
            const type = event.detail.type || 'error';
            const message = event.detail.message;
            const detail = event.detail.trace || null;
            showAlert(type, message, detail);
        });
    </script>
@endpush


