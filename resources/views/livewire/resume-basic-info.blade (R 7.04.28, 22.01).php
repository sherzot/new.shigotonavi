<div class="container bg-white border p-4" style="max-width: 850px;">
    <h4 class="text-center fw-bold">履 歴 書</h4>
    <p class="text-end">{{ now()->format('Y年 m月 d日') }} 現在</p>

    <form wire:submit.prevent="save" class="container px-3 py-4" style="max-width: 850px;">
        @csrf
        <div class="col-md-6 offset-md-6 text-end mb-3">
            <label class="form-label d-block">履歴書写真</label>

            @if ($photo)
            <img src="{{ $photo->temporaryUrl() }}" class="img-thumbnail mb-2" style="width: 120px; height: auto;">
            @elseif ($existingPhoto)
            <img src="{{ $existingPhoto }}" class="img-thumbnail mb-2" style="width: 120px; height: auto;">
            @endif

            <label class="btn btn-outline-secondary btn-sm">
                ファイルを選択
                <input type="file" wire:model="photo" class="d-none">
            </label>

            <div class="mt-1 small text-muted">
                @if ($photo)
                選択されたファイル: {{ $photo->getClientOriginalName() }}
                @elseif ($existingPhoto)
                登録済みの写真が表示されています。
                @else
                ファイルが選択されていません。
                @endif
            </div>

            @error('photo')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        {{-- 📌 基本情報 --}}
        <div class="row g-3">
            {{-- 氏名・フリガナ --}}
            <div class="col-md-6">
                <label class="form-label">氏名 (漢字)</label>
                <input type="text" name="name" class="form-control form-control-sm" wire:model.debounce.500ms="name">
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">氏名 (フリガナ)</label>
                <input type="text" name="name_f" class="form-control form-control-sm" wire:model.debounce.500ms="name_f">
                @error('name_f')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            {{-- メール・生年月日 --}}
            <div class="col-md-6">
                <label class="form-label">メールアドレス</label>
                <input type="email" name="mail_address" class="form-control form-control-sm" wire:model.debounce.500ms="mail_address">
                @error('mail_address')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">生年月日 <span class="text-main-theme">(例: 19710401)</span></label>
                <input type="text" name="birthday" class="form-control form-control-sm" wire:model.debounce.500ms="birthday">
                @error('birthday')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            {{-- 性別 --}}
            <div class="col-md-6">
                <label class="form-label d-block">性別</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sex" value="1" wire:model.debounce.500ms="sex" id="male">
                    <label class="form-check-label" for="male">男</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sex" value="2" wire:model.debounce.500ms="sex" id="female">
                    <label class="form-check-label" for="female">女</label>
                </div>
                @error('sex')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            {{-- 電話番号 --}}
            <div class="col-md-6">
                <label class="form-label">電話番号 <span class="text-main-theme">(例: 07009090808)</span></label>
                <input type="text" name="portable_telephone_number" class="form-control form-control-sm" wire:model.debounce.500ms="portable_telephone_number">
                @error('portable_telephone_number')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            {{-- 郵便番号 --}}
            <div class="col-md-6">
                <label class="form-label">郵便番号</label>
                <div class="row g-2">
                    <div class="col-5">
                        <input type="text" name="post_u" class="form-control form-control-sm" wire:model.debounce.500ms="post_u" maxlength="3" placeholder="123">
                        @error('post_u')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-1 d-flex align-items-center justify-content-center">-</div>
                    <div class="col-6">
                        <input type="text" name="post_l" class="form-control form-control-sm" wire:model.debounce.500ms="post_l" maxlength="4" placeholder="4567">
                        @error('post_l')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- 都道府県 --}}
            <div class="col-md-6">
                <label class="form-label">都道府県</label>
                <select name="prefecture_code" class="form-control form-control-sm" wire:model.debounce.500ms="prefecture_code" required>
                    <option value="">選択してください</option>
                    @foreach ($prefectures as $prefecture)
                    <option value="{{ $prefecture->code }}">{{ $prefecture->detail }}</option>
                    @endforeach
                </select>
                @error('prefecture_code')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            {{-- 住所 --}}
            <div class="col-md-6">
                <label class="form-label">区・市</label>
                <input type="text" name="city" class="form-control form-control-sm mb-1" wire:model.debounce.500ms="city">
            </div>
            <div class="col-md-6">
                <label class="form-label">区・市 (フリガナ)</label>
                <input type="text" name="city_f" class="form-control form-control-sm mb-1" wire:model.debounce.500ms="city_f">
            </div>
            <div class="col-md-6">
                <label class="form-label">町</label>
                <input type="text" name="town" class="form-control form-control-sm mb-1" wire:model.debounce.500ms="town">
            </div>
            <div class="col-md-6">
                <label class="form-label">町 (フリガナ)</label>
                <input type="text" name="town_f" class="form-control form-control-sm" wire:model.debounce.500ms="town_f">
            </div>
            <div class="col-md-6">
                <label class="form-label">番地など </label>
                <input type="text" name="address" class="form-control form-control-sm mb-1" wire:model.debounce.500ms="address">
                @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">番地など (フリガナ)</label>
                <input type="text" name="address_f" class="form-control form-control-sm mb-1" wire:model.debounce.500ms="address_f">
                @error('address_f')<div class="text-danger small">{{ $message }}</div>@enderror
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
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="educations.{{ $index }}.school_name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">学校種別</label>
                        <select class="form-select form-select-sm" wire:model.debounce.500ms="educations.{{ $index }}.school_type_code">
                            <option value="">選択してください</option>
                            @foreach ($schoolTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">専攻</label>
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="educations.{{ $index }}.speciality">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">コース種別</label>
                        <select class="form-select form-select-sm" wire:model.debounce.500ms="educations.{{ $index }}.course_type">
                            <option value="">選択してください</option>
                            @foreach ($courseTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">入学年</label>
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="educations.{{ $index }}.entry_day_year">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">入学月</label>
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="educations.{{ $index }}.entry_day_month">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">入学タイプ</label>
                        <select class="form-select form-select-sm" wire:model.debounce.500ms="educations.{{ $index }}.entry_type_code">
                            <option value="">選択してください</option>
                            @foreach ($entryTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">卒業年</label>
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="educations.{{ $index }}.graduate_day_year">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">卒業月</label>
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="educations.{{ $index }}.graduate_day_month">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">卒業タイプ</label>
                        <select class="form-select form-select-sm" wire:model.debounce.500ms="educations.{{ $index }}.graduate_type_code">
                            <option value="">選択してください</option>
                            @foreach ($graduateTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
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
            <h5 class="fw-medium text-primary">職歴</h5>
            {{-- 💼 職歴情報 --}}
            @foreach ($careers as $index => $career)
            <div class="card mb-3 border rounded p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">会社名</label>
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="careers.{{ $index }}.company_name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">資本金 (万円)</label>
                        <input type="number" class="form-control form-control-sm" wire:model.debounce.500ms="careers.{{ $index }}.capital">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">従業員数</label>
                        <input type="number" class="form-control form-control-sm" wire:model.debounce.500ms="careers.{{ $index }}.number_employees">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">入社年</label>
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="careers.{{ $index }}.entry_day_year">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">入社月</label>
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="careers.{{ $index }}.entry_day_month">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">退社年</label>
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="careers.{{ $index }}.retire_day_year">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">退社月</label>
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="careers.{{ $index }}.retire_day_month">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">業種</label>
                        <select class="form-select form-select-sm" wire:model.debounce.500ms="careers.{{ $index }}.industry_type_code">
                            <option value="">選択してください</option>
                            @foreach ($industryTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">勤務形態</label>
                        <select class="form-select form-select-sm" wire:model.debounce.500ms="careers.{{ $index }}.working_type_code">
                            <option value="">選択してください</option>
                            @foreach ($workingTypes as $type)
                            <option value="{{ $type->code }}">{{ $type->detail }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">職種名</label>
                        <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="careers.{{ $index }}.job_type_detail">
                    </div>
                    @foreach ($careers as $index => $career)
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">職種（大分類）</label>
                            <select class="form-select" wire:model="careers.{{ $index }}.job_type_big_code">
                                <option value="">選択してください</option>
                                @foreach ($jobTypes->unique('big_class_code') as $type)
                                <option value="{{ $type->big_class_code }}">{{ $type->big_class_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">職種（小分類）</label>
                            <select class="form-select" wire:model="careers.{{ $index }}.job_type_small_code">
                                <option value="">選択してください</option>
                        
                                @foreach ($this->getSmallJobTypes($career['job_type_big_code']) as $type)
                                    <option value="{{ $type->middle_class_code }}">{{ $type->middle_clas_name }}</option>
                                @endforeach
                            </select>
                        </div>                        
                    </div>
                    @endforeach

                    <div class="col-md-12">
                        <label class="form-label">職務内容</label>
                        <textarea class="form-control form-control-sm" wire:model.debounce.500ms="careers.{{ $index }}.business_detail" rows="2"></textarea>
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

                <div id="license-section">
                    @foreach ($licenses as $index => $license)
                    <div class="card mb-3 border rounded p-3 license-row">
                        <div class="row g-3">
                            <!-- 資格グループ -->
                            <div class="col-md-6">
                                <label class="form-label">資格グループ</label>
                                <select class="form-select license-group" name="licenses[{{ $index }}][group_code]" wire:model="licenses.{{ $index }}.group_code">
                                    <option value="">選択してください</option>
                                    @foreach ($licenseGroups as $group)
                                    <option value="{{ $group->group_code }}">{{ $group->group_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- カテゴリ名 -->
                            <div class="col-md-6">
                                <label class="form-label">カテゴリ名</label>
                                <select class="form-select license-category" name="licenses[{{ $index }}][category_code]" wire:model="licenses.{{ $index }}.category_code">
                                    <option value="">選択してください</option>
                                </select>
                            </div>

                            <!-- 資格名 -->
                            <div class="col-md-6">
                                <label class="form-label">資格名</label>
                                <select class="form-select license-name" name="licenses[{{ $index }}][code]" wire:model="licenses.{{ $index }}.code">
                                    <option value="">選択してください</option>
                                </select>
                            </div>

                            <!-- 取得年月日 -->
                            <div class="col-md-6">
                                <label class="form-label">取得年月日 <span class="text-main-theme">(例: 20240101)</span></label>
                                <input type="text" class="form-control form-control-sm" name="licenses[{{ $index }}][get_day]" maxlength="8" wire:model="licenses.{{ $index }}.get_day">
                            </div>

                            <!-- 削除 -->
                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-danger btn-sm mt-2 remove-license">
                                    削除
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="text-end">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-license-row">
                        ＋ 資格追加
                    </button>
                </div>
            </div>
        </div>
        <!-- 自己PR -->
        <div class="col-12 mb-5">
            <h5 class="text-primary mb-4 fw-medium">自己PR</h5>
            <textarea class="form-control" rows="5" wire:model.debounce.500ms="self_pr" placeholder="ここに自己PRを入力してください"></textarea>
            <small class="text-muted">※履歴書へ表示されるのは全角200文字までです。(出力形式により多少前後します。)</small>
            @error('self_pr') <div class="text-danger">自己PRは200文字以内で入力してください。</div> @enderror
        </div>
        <!-- resume-basic-info.blade.php -->

        <!-- 🛠️ スキル選択 -->
        <div class="mb-4">
            {{-- <label class="form-label"></label>  --}}
            <h5 class="text-primary mb-4 fw-medium">スキル選択</h5>
            @foreach ($skillRows as $index => $skillRow)
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-3">
                    <select class="form-select" wire:model="skillRows.{{ $index }}.OS">
                        <option value="">OS選択</option>
                        @foreach ($allSkills['OS'] ?? [] as $skill)
                        <option value="{{ $skill->code }}">{{ $skill->detail }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model="skillRows.{{ $index }}.Application">
                        <option value="">Application選択</option>
                        @foreach ($allSkills['Application'] ?? [] as $skill)
                        <option value="{{ $skill->code }}">{{ $skill->detail }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model="skillRows.{{ $index }}.DevelopmentLanguage">
                        <option value="">Development Language選択</option>
                        @foreach ($allSkills['DevelopmentLanguage'] ?? [] as $skill)
                        <option value="{{ $skill->code }}">{{ $skill->detail }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model="skillRows.{{ $index }}.Database">
                        <option value="">Database選択</option>
                        @foreach ($allSkills['Database'] ?? [] as $skill)
                        <option value="{{ $skill->code }}">{{ $skill->detail }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endforeach

            @if (count($skillRows) < 10) <div class="mt-2">
                <button type="button" class="btn btn-outline-primary" wire:click="addSkillRow">＋追加</button>
        </div>
        @endif
</div>

<!-- ✅ 選択済みスキル表示 -->
<div class="mt-3">
    @foreach ($skills as $skill)
    <div class="badge bg-secondary me-1">{{ $skill['detail'] }}</div>
    @endforeach
</div>

<div class="mb-4">
    <h5 class="text-primary mb-4">志望動機</h5>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">志望動機を選ぶための識別名</label>
            <input type="text" class="form-control" wire:model.defer="resumePreference.subject">
        </div>

        <div class="mb-3">
            <label class="form-label">希望通勤時間</label>
            <div class="input-group">
                <input type="number" class="form-control" placeholder="例: 10" wire:model.defer="resumePreference.commute_time">
                <span class="input-group-text">分</span>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">結婚状況</label>
            <select class="form-control" wire:model.defer="marriage_flag">
                <option value="">選択してください</option>
                <option value="0">未婚</option>
                <option value="1">既婚</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">扶養家族（配偶者を除く）</label>
            <input type="number" class="form-control" wire:model.defer="dependent_number">
        </div>

        <div class="mb-3">
            <label class="form-label">配偶者の扶養義務</label>
            <select class="form-control" wire:model.defer="dependent_flag">
                <option value="">選択してください</option>
                <option value="0">無</option>
                <option value="1">有</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">志望動機</label>
            <textarea class="form-control" rows="5" wire:model.defer="resumePreference.wish_motive"></textarea>
            <small class="text-muted">※履歴書へ表示されるのは全角200文字までです。(出力形式により多少前後します。)</small>
            @error('wish_motive') <div class="text-danger">志望動機は200文字以内で入力してください。</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">本人希望欄</label>
            <textarea class="form-control" rows="5" wire:model.defer="resumePreference.hope_column"></textarea>
            <small class="text-muted">※履歴書へ表示されるのは全角200文字までです。(出力形式により多少前後します。)</small>
            @error('hope_column') <div class="text-danger">本人希望欄は200文字以内で入力してください。</div> @enderror
        </div>
    </div>
</div>
{{-- <button type="button" class="btn btn-success" wire:click="addResumePreference">＋志望動機追加</button>  --}}
<div class="text-center mt-4">
    <a href="{{ route('mypage') }}" class="btn btn-outline-secondary btn-sm px-4">戻る</a>
    <button type="submit" class="btn btn-primary btn-sm px-4 me-2">保存する</button>
</div>

{{-- <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary btn-sm px-4">保存する</button>
    </div>  --}}
<div wire:loading wire:target="save" class="text-center mt-3">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">保存中...</span>
    </div>
    <div class="text-primary small mt-2">保存中...</div>
</div>
</form>
</div>
{{-- @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ✅ 成功・失敗トースト処理
            if (typeof Livewire !== 'undefined') {
                Livewire.on('success', message => {
                    showAlert('success', message);
                });
                Livewire.on('error', message => {
                    showAlert('error', message);
                });
            }

            // ✅ 最初のエラーにスクロール＆フォーカス
            setTimeout(() => {
                const firstError = document.querySelector('.text-danger');
                if (firstError) {
                    const input = firstError.closest('div')?.querySelector('input, select, textarea');
                    if (input) {
                        input.focus();
                    }
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }, 300);

            // ✅ 大分類を選択したら小分類を取得する (希望職種)
            const bigClassSelect = document.getElementById('job_type_big');
            const smallClassSelect = document.getElementById('job_type_small');
            if (bigClassSelect && smallClassSelect) {
                bigClassSelect.addEventListener('change', function() {
                    const bigClassCode = this.value;
                    smallClassSelect.innerHTML = '<option value="">選択してください</option>';

                    if (bigClassCode) {
                        fetch(`/api/job-types/${bigClassCode}`)
                            .then(response => response.json())
                            .then(data => {
                                data.forEach(item => {
                                    const option = document.createElement('option');
                                    option.value = item.code;
                                    option.textContent = item.detail;
                                    smallClassSelect.appendChild(option);
                                });
                            })
                            .catch(error => console.error('小分類取得エラー:', error));
                    }
                });
            }

            // ✅ 資格グループを選択したらカテゴリ取得
            const licenseGroupSelect = document.getElementById('license_group');
            const licenseCategorySelect = document.getElementById('license_category');
            const licenseNameSelect = document.getElementById('license_name');
            if (licenseGroupSelect && licenseCategorySelect && licenseNameSelect) {
                licenseGroupSelect.addEventListener('change', function() {
                    const groupCode = this.value;
                    licenseCategorySelect.innerHTML = '<option value="">選択してください</option>';
                    licenseNameSelect.innerHTML = '<option value="">選択してください</option>';

                    if (groupCode) {
                        fetch(`/get-license-categories?group_code=${groupCode}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.categories) {
                                    data.categories.forEach(item => {
                                        const option = document.createElement('option');
                                        option.value = item.category_code;
                                        option.textContent = item.category_name;
                                        licenseCategorySelect.appendChild(option);
                                    });
                                }
                            })
                            .catch(error => console.error('カテゴリ取得エラー:', error));
                    }
                });

                // ✅ カテゴリ選択したら資格名取得
                licenseCategorySelect.addEventListener('change', function() {
                    const groupCode = licenseGroupSelect.value;
                    const categoryCode = this.value;
                    licenseNameSelect.innerHTML = '<option value="">選択してください</option>';

                    if (groupCode && categoryCode) {
                        fetch(`/api/license-names/${groupCode}/${categoryCode}`)
                            .then(response => response.json())
                            .then(names => {
                                names.forEach(item => {
                                    const option = document.createElement('option');
                                    option.value = item.code;
                                    option.textContent = item.name;
                                    licenseNameSelect.appendChild(option);
                                });
                            })
                            .catch(error => console.error('資格名取得エラー:', error));
                    }
                });
            }

            // ✅ アラート表示関数
            function showAlert(type, message) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: type === 'success' ? 'success' : 'error',
                        title: type === 'success' ? '成功' : 'エラー',
                        text: message,
                        confirmButtonText: 'OK',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        timer: type === 'success' ? 3000 : undefined,
                        timerProgressBar: type === 'success'
                    }).then((result) => {
                        if (type === 'success') {
                            window.location.href = "{{ route('resume.preview') }}";
}
});
} else {
const alert = document.createElement('div');
alert.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 start-50 translate-middle-x mt-3 shadow`;
alert.style.zIndex = 2000;
alert.style.maxWidth = '450px';
alert.innerHTML = `
<div class="d-flex justify-content-between align-items-center">
    <div>${message}</div>
    <button type="button" class="btn-close ms-3" onclick="this.parentElement.parentElement.remove();"></button>
</div>
`;
document.body.appendChild(alert);

setTimeout(() => {
alert.remove();
if (type === 'success') {
window.location.href = "{{ route('resume.preview') }}";
}
}, 3000);
}
}
});
</script>
@endpush --}}

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ✅ 成功・失敗トースト処理 (Livewire alert)
        if (typeof Livewire !== 'undefined') {
            Livewire.on('success', message => {
                showAlert('success', message);
            });
            Livewire.on('error', message => {
                showAlert('error', message);
            });
        }

        // ✅ 最初のエラーにスクロール＆フォーカス
        setTimeout(() => {
            const firstError = document.querySelector('.text-danger');
            if (firstError) {
                const input = firstError.closest('div') ? .querySelector('input, select, textarea');
                if (input) {
                    input.focus();
                }
                firstError.scrollIntoView({
                    behavior: 'smooth'
                    , block: 'center'
                });
            }
        }, 300);

        // ✅ 大分類選択 → 小分類ロード (希望職種)
        const bigClassSelect = document.getElementById('job_type_big');
        const smallClassSelect = document.getElementById('job_type_small');
        if (bigClassSelect && smallClassSelect) {
            bigClassSelect.addEventListener('change', function() {
                const bigClassCode = this.value;
                smallClassSelect.innerHTML = '<option value="">選択してください</option>';

                if (bigClassCode) {
                    fetch(`/api/job-types/${bigClassCode}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.code;
                                option.textContent = item.detail;
                                smallClassSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('小分類取得エラー:', error));
                }
            });
        }

        // ✅ 既存の資格行にセットアップ
        document.querySelectorAll('.license-row').forEach(row => {
            setupLicenseSelects(row);
        });

        // ✅ 資格追加ボタン (＋資格追加)
        document.getElementById('add-license-row').addEventListener('click', function() {
            const licenseSection = document.getElementById('license-section');
            const index = licenseSection.querySelectorAll('.license-row').length;

            const newRow = document.createElement('div');
            newRow.className = 'card mb-3 border rounded p-3 license-row';
            newRow.innerHTML = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">資格グループ</label>
                            <select class="form-select license-group" name="licenses[${index}][group_code]">
                                <option value="">選択してください</option>
                                @foreach ($licenseGroups as $group)
                                    <option value="{{ $group->group_code }}">{{ $group->group_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">カテゴリ名</label>
                            <select class="form-select license-category" name="licenses[${index}][category_code]">
                                <option value="">選択してください</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">資格名</label>
                            <select class="form-select license-name" name="licenses[${index}][code]">
                                <option value="">選択してください</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">取得年月日 <span class="text-main-theme">(例: 20240101)</span></label>
                            <input type="text" class="form-control form-control-sm" name="licenses[${index}][get_day]" maxlength="8">
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="button" class="btn btn-danger btn-sm mt-2 remove-license">削除</button>
                        </div>
                    </div>
                `;
            licenseSection.appendChild(newRow);
            setupLicenseSelects(newRow);
        });

        // ✅ 資格行削除ボタン (削除)
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-license')) {
                e.target.closest('.license-row').remove();
            }
        });

        // ✅ 資格グループ→カテゴリ→資格名ロード関数
        function setupLicenseSelects(container) {
            const groupSelect = container.querySelector('.license-group');
            const categorySelect = container.querySelector('.license-category');
            const nameSelect = container.querySelector('.license-name');

            if (groupSelect && categorySelect && nameSelect) {
                groupSelect.addEventListener('change', function() {
                    const groupCode = this.value;
                    categorySelect.innerHTML = '<option value="">選択してください</option>';
                    nameSelect.innerHTML = '<option value="">選択してください</option>';

                    if (groupCode) {
                        fetch(`/get-license-categories?group_code=${groupCode}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.categories) {
                                    data.categories.forEach(item => {
                                        const option = document.createElement('option');
                                        option.value = item.category_code;
                                        option.textContent = item.category_name;
                                        categorySelect.appendChild(option);
                                    });
                                }
                            })
                            .catch(error => console.error('カテゴリロードエラー:', error));
                    }
                });

                categorySelect.addEventListener('change', function() {
                    const groupCode = groupSelect.value;
                    const categoryCode = this.value;
                    nameSelect.innerHTML = '<option value="">選択してください</option>';

                    if (groupCode && categoryCode) {
                        fetch(`/get-licenses?group_code=${groupCode}&category_code=${categoryCode}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.licenses) {
                                    data.licenses.forEach(item => {
                                        const option = document.createElement('option');
                                        option.value = item.code;
                                        option.textContent = item.name;
                                        nameSelect.appendChild(option);
                                    });
                                }
                            })
                            .catch(error => console.error('資格名ロードエラー:', error));
                    }
                });
            }
        }

        // ✅ SweetAlert アラート関数
        function showAlert(type, message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type === 'success' ? 'success' : 'error'
                    , title: type === 'success' ? '成功' : 'エラー'
                    , text: message
                    , confirmButtonText: 'OK'
                    , allowOutsideClick: false
                    , allowEscapeKey: false
                    , timer: type === 'success' ? 3000 : undefined
                    , timerProgressBar: type === 'success'
                }).then(() => {
                    if (type === 'success') {
                        window.location.href = "{{ route('resume.preview') }}";
                    }
                });
            } else {
                const alert = document.createElement('div');
                alert.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 start-50 translate-middle-x mt-3 shadow`;
                alert.style.zIndex = 2000;
                alert.style.maxWidth = '450px';
                alert.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>${message}</div>
                            <button type="button" class="btn-close ms-3" onclick="this.parentElement.parentElement.remove();"></button>
                        </div>
                    `;
                document.body.appendChild(alert);

                setTimeout(() => {
                    alert.remove();
                    if (type === 'success') {
                        window.location.href = "{{ route('resume.preview') }}";
                    }
                }, 3000);
            }
        }
    });

</script>
@endpush
