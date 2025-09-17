@section('content')
<div class="accordion" id="resumeAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingBasic">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBasic" aria-expanded="true" aria-controls="collapseBasic">
                Step 1: 基本情報 (Basic Information)
            </button>
        </h2>
        <div id="collapseBasic" class="accordion-collapse collapse show" aria-labelledby="headingBasic" data-bs-parent="#resumeAccordion">
            <div class="accordion-body">
                <div class="row g-3">
                    <div class="col-md-3 offset-md-9 text-end">
                        <label class="form-label d-block">履歴書写真</label>
                        @if ($existingPhoto)
                            <img src="{{ $existingPhoto }}" alt="履歴書写真" class="rounded shadow border border-secondary mb-2" style="max-width: 100px; max-height: 130px; object-fit: cover;">
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
                    <div class="col-md-6">
                        <label class="form-label">電話番号 <span class="text-main-theme">(例: 07009090808)</span></label>
                        <input type="text" name="portable_telephone_number" class="form-control form-control-sm" wire:model.lazy="portable_telephone_number">
                        @error('portable_telephone_number')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection