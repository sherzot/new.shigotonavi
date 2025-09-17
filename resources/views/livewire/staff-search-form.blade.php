<div class="container">
    <div class="card shadow p-4">
        <h4 class="text-primary fw-bold text-center mb-4">スタッフ検索</h4>

        <form wire:submit.prevent="submitSearch">
            <div class="row g-4">

                {{-- 🔍 基本情報 --}}
                <div class="col-md-6">
                    <label class="form-label fw-bold">スタッフコード / メールアドレス / 氏名</label>
                    <input type="text" class="form-control" wire:model="keyword">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">年齢</label>
                    <input type="number" class="form-control" wire:model="age">
                </div>
                {{-- 💼 居住地（都道府県） --}}
                <fieldset class="col-12 border rounded p-3 my-2">
                    <legend class="w-auto px-2 text-primary fs-6">居住地（都道府県）</legend>
                    <div class="col-12">
                        <label class="form-label fw-bold">居住地（都道府県）</label>
                        <select class="form-select" wire:model="prefecture_code">
                            @foreach (DB::table('master_code')->where('category_code', 'Prefecture')->get() as $pref)
                            <option value="{{ $pref->code }}">{{ $pref->detail }}</option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>
                {{-- 💼 希望勤務地（都道府県）） --}}
                <fieldset class="col-12 border rounded p-3 my-2">
                    <legend class="w-auto px-2 text-primary fs-6">希望勤務地（都道府県）</legend>
                    <div class="col-12">
                        <label class="form-label fw-bold">希望勤務地（都道府県）</label>
                        <select class="form-select" wire:model="prefecture_code2">
                            <option value="">選択してください</option>
                            @foreach (DB::table('master_code')->where('category_code', 'Prefecture')->get() as $pref)
                            <option value="{{ $pref->code }}">{{ $pref->detail }}</option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>

                {{-- 💼 希望職種 --}}
                <fieldset class="col-12 border rounded p-3 my-2">
                    <legend class="w-auto px-2 text-primary fs-6">希望職種</legend>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">希望職種</label>
                            <select class="form-select" wire:model="big_class_code">
                                <option value="">選択してください</option>
                                @foreach ($bigClasses as $big)
                                <option value="{{ $big->big_class_code }}">{{ $big->big_class_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">職種タイプ</label>
                            <select class="form-select" wire:model="job_category">
                                <option value="">選択してください</option>
                                @foreach ($jobCategories as $cat)
                                <option value="{{ $cat->middle_class_code }}">{{ $cat->middle_class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>
                {{-- 💼 経験職種 --}}
                <fieldset class="col-12 border rounded p-3 my-2">
                    <legend class="w-auto px-2 text-primary fs-6">経験職種</legend>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">経験職種</label>
                            <select class="form-select" wire:model="big_class_code2">
                                <option value="">選択してください</option>
                                @foreach ($bigClasses as $big)
                                <option value="{{ $big->big_class_code }}">{{ $big->big_class_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">職種タイプ</label>
                            <select class="form-select" wire:model="job_category2">
                                <option value="">選択してください</option>
                                @foreach ($jobCategories2 as $cat)
                                <option value="{{ $cat->middle_class_code }}">{{ $cat->middle_clas_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                {{-- 🎓 資格フィルター --}}
                <fieldset class="col-12 border rounded p-3 my-2">
                    <legend class="w-auto px-2 text-primary fs-6">資格</legend>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">資格グループ</label>
                            <select class="form-select" wire:model="group_code">
                                <option value="">選択してください</option>
                                @foreach ($groups as $group)
                                <option value="{{ $group->group_code }}">{{ $group->group_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">資格カテゴリ</label>
                            <select class="form-select" wire:model="category_code">
                                <option value="">選択してください</option>
                                @foreach ($categories as $cat)
                                <option value="{{ $cat->category_code }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">資格名</label>
                            <select class="form-select" wire:model="license_code">
                                <option value="">選択してください</option>
                                @foreach ($licenses as $lic)
                                <option value="{{ $lic->code }}">{{ $lic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                {{-- 🛠 スキルフィルター --}}
                <fieldset class="col-12 border rounded p-3 my-2">
                    <legend class="w-auto px-2 text-primary fs-6">スキル（各分類から1つ選択）</legend>
                    <div class="row g-3">
                        @foreach ($skillCategories as $categoryCode => $label)
                        <div class="col-md-6 col-xl-4">
                            <label class="form-label">{{ $label }}</label>
                            <select class="form-select" wire:model="skills.{{ $categoryCode }}">
                                <option value="">選択してください</option>
                                @foreach ($allSkills[$categoryCode] ?? [] as $skill)
                                <option value="{{ $skill->code }}">{{ $skill->detail }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endforeach
                    </div>
                </fieldset>

                {{-- 🔍 検索 --}}
                <div class="col-12 d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">検索</button>
                </div>

            </div>
        </form>

        {{-- 🔎 検索結果 --}}
        <hr class="mt-5">
        <h5 class="fw-bold">検索結果: {{ count($results) }} 件</h5>

        @foreach ($results as $staff)
        <div class="card mt-3 p-3 border-start border-4 border-primary">
            <div class="fw-bold fs-5">{{ $staff->name }} ({{ $staff->staff_code }})</div>
            <div>希望職種: {{ $staff->jobType }}</div>
            <div>希望勤務地: {{ implode(', ', $staff->location) }}</div>
            <div>希望年収: {{ $staff->salary ?? '-' }} 万円〜</div>
            <div>資格: {{ implode(', ', $staff->licenses) }}</div>
            <div>スキル: {{ implode(', ', $staff->skills) }}</div>
            <div>担当エージェント: {{ $staff->agent ?? '未担当' }}</div>
        </div>
        @endforeach
    </div>
</div>
