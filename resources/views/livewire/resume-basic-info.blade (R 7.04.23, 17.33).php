<div class="container bg-white border p-4" style="max-width: 850px;">
    <h4 class="text-center fw-bold">履 歴 書</h4>
    <p class="text-end">{{ now()->format('Y年 m月 d日') }} 現在</p>

    <form wire:submit.prevent="save" class="container px-3 py-4" style="max-width: 850px;">
        @csrf

        <div class="row g-3">
            {{-- 📸 写真 --}}
            <div class="col-md-3 offset-md-9 text-end">
                <label class="form-label d-block">履歴書写真</label>
            
                @if ($existingPhoto)
                    <img src="{{ $existingPhoto }}"
                         alt="履歴書写真"
                         class="rounded shadow border border-secondary mb-2"
                         style="max-width: 100px; max-height: 130px; object-fit: cover;">
                @endif
            
                <label class="btn btn-outline-secondary btn-sm">
                    ファイルを選択
                    <input type="file" wire:model="photo" class="d-none">
                </label>
            
                <div class="mt-1 small text-muted">
                    @if ($photo)
                        選択されたファイル: {{ $photo->getClientOriginalName() }}
                    @else
                        履歴書写真まだありません
                    @endif
                </div>
            
                @error('photo')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>            
            {{-- 氏名・フリガナ --}}
            <div class="col-md-6">
                <label class="form-label">氏名 (漢字)</label>
                <input type="text" name="name" class="form-control form-control-sm" wire:model.lazy="name">
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">氏名 (フリガナ)</label>
                <input type="text" name="name_f" class="form-control form-control-sm" wire:model.lazy="name_f">
                @error('name_f')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            {{-- メール・生年月日 --}}
            <div class="col-md-6">
                <label class="form-label">メールアドレス</label>
                <input type="email" name="mail_address" class="form-control form-control-sm" wire:model.lazy="mail_address">
                @error('mail_address')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">生年月日 <span class="text-main-theme">(例: 19710401)</span></label>
                <input type="text" name="birthday" class="form-control form-control-sm" wire:model.lazy="birthday">
                @error('birthday')<div class="text-danger small">{{ $message }}</div>@enderror
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
                @error('sex')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            {{-- 電話番号 --}}
            <div class="col-md-6">
                <label class="form-label">電話番号 <span class="text-main-theme">(例: 07009090808)</span></label>
                <input type="text" name="portable_telephone_number" class="form-control form-control-sm" wire:model.lazy="portable_telephone_number">
                @error('portable_telephone_number')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            {{-- 郵便番号 --}}
            <div class="col-md-6">
                <label class="form-label">郵便番号</label>
                <div class="row g-2">
                    <div class="col-5">
                        <input type="text" name="post_u" class="form-control form-control-sm" wire:model.lazy="post_u" maxlength="3" placeholder="123">
                        @error('post_u')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-1 d-flex align-items-center justify-content-center">-</div>
                    <div class="col-6">
                        <input type="text" name="post_l" class="form-control form-control-sm" wire:model.lazy="post_l" maxlength="4" placeholder="4567">
                        @error('post_l')<div class="text-danger small">{{ $message }}</div>@enderror
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
                @error('prefecture_code')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            {{-- 住所 --}}
            <div class="col-md-6">
                <label class="form-label">区・市</label>
                <input type="text" name="city" class="form-control form-control-sm mb-1" wire:model.lazy="city">
            </div>
            <div class="col-md-6">
                <label class="form-label">区・市 (フリガナ)</label>
                <input type="text" name="city_f" class="form-control form-control-sm mb-1" wire:model.lazy="city_f">
            </div>
            <div class="col-md-6">
                <label class="form-label">町</label>
                <input type="text" name="town" class="form-control form-control-sm mb-1" wire:model.lazy="town">
            </div>
            <div class="col-md-6">
                <label class="form-label">町 (フリガナ)</label>
                <input type="text" name="town_f" class="form-control form-control-sm" wire:model.lazy="town_f">
            </div>
            <div class="col-md-6">
                <label class="form-label">番地など </label>
                <input type="text" name="address" class="form-control form-control-sm mb-1" wire:model.lazy="address">
                @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">番地など (フリガナ)</label>
                <input type="text" name="address_f" class="form-control form-control-sm mb-1" wire:model.lazy="address_f">
                @error('address_f')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-sm px-4">保存する</button>
        </div>
    </form>
</div>
