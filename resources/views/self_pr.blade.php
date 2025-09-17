@extends('layouts.top')

@section('title', '自己PR・志望動機')

@section('content')
    <form method="POST" action="{{ route('self_pr.store') }}">
        @csrf
        <div class="container m-auto row">
            @if (session('success'))
                <div class="alert alert-success text-center">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger text-center">{{ session('error') }}</div>
            @endif
            <div class="mt-5 pt-5">
                <h2 class="text-center">資格 (最大3つ)</h2>
            </div>
            @php
                $maxLicenses = 3; //
                $licenseCount = count($licenses); // DBで利用可能なライセンスの数
            @endphp

            {{-- 利用可能なライセンスは 1 つだけ発行されます --}}
            <div class="license-container">
                <div class="license-entry" data-id="{{ $licenses[0]->id ?? '' }}">
                    <!-- group_code -->
                    <div class="col-12 mb-3">
                        <select name="licenses[0][group_code]" class="form-control border-primary group-select">
                            <option value="">選択してください</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->group_code }}"
                                    {{ old('licenses.0.group_code', $licenses[0]->group_code ?? '') == $group->group_code ? 'selected' : '' }}>
                                    {{ $group->group_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('licenses.0.group_code')
                            <div class="text-danger">資格グループを選択してください。</div>
                        @enderror
                    </div>

                    <!-- category_code -->
                    <div class="col-11 offset-1 col-md-10 offset-md-2 col-lg-10 offset-lg-2">
                        <div class="mb-3">
                            <select name="licenses[0][category_code]" class="form-control border-primary category-select">
                                <option value="">選択してください</option>
                                @if (old('licenses.0.category_code'))
                                    <option value="{{ old('licenses.0.category_code') }}" selected>
                                        {{ old('licenses.0.category_code') }}</option>
                                @elseif (!empty($licenses[0]->category_code))
                                    <option value="{{ $licenses[0]->category_code }}" selected>
                                        {{ $licenses[0]->category_name ?? '不明なカテゴリ' }}</option>
                                @endif
                            </select>
                            @error('licenses.0.category_code')
                                <div class="text-danger">資格カテゴリを選択してください。</div>
                            @enderror
                        </div>
                    </div>

                    <!-- code -->
                    <div class="col-10 offset-2 col-md-9 offset-md-3 col-lg-9 offset-lg-3">
                        <div class="mb-3">
                            <select name="licenses[0][code]" class="form-control border-primary license-select">
                                <option value="">選択してください</option>
                                @if (old('licenses.0.code'))
                                    <option value="{{ old('licenses.0.code') }}" selected>{{ old('licenses.0.code') }}
                                    </option>
                                @elseif (!empty($licenses[0]->code))
                                    <option value="{{ $licenses[0]->code }}" selected>
                                        {{ $licenses[0]->name ?? '不明なライセンス' }}
                                    </option>
                                @endif
                            </select>
                            @error('licenses.0.code')
                                <div class="text-danger">資格名を選択してください。</div>
                            @enderror
                        </div>
                    </div>

                    <!-- get_day -->
                    <div class="col-10 offset-2 col-md-9 offset-md-3 col-lg-9 offset-lg-3">
                        <div class="mb-3">
                            <label for="get_day" class="form-label">取得年月日 <span class="text-main-theme">
                                例：20250101</span></label>
                            <input type="text" name="licenses[0][get_day]" class="form-control border-primary datepicker"
                                value="{{ old('licenses.0.get_day', isset($licenses[0]->get_day) ? \Carbon\Carbon::parse($licenses[0]->get_day)->format('Ymd') : '') }}">
                            @error('licenses.0.get_day')
                                <div class="text-danger">取得日は半角数字8桁で入力してください。</div>
                            @enderror
                        </div>
                    </div>

                    <!-- 削除ボタン（DBに保存された場合のみ） -->
                    @if (!empty($licenses[0]->id))
                        <div class="text-end my-2">
                            <button type="button" class="btn btn-danger btn-sm delete-license"
                                data-id="{{ $licenses[0]->id }}">
                                データを削除
                            </button>
                            <button type="button" class="btn btn-outline-white btn-primary btn-sm remove-inline-license">
                                入力欄を削除
                            </button>
                        </div>
                    @endif
                </div>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelectorAll('.datepicker').forEach(function(input) {
                        input.addEventListener("input", function() {
                            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 8);
                        });
                    });
                });
            </script>
            {{-- ボタンは常に使用可能である必要がありますが、ライセンスが 3 つしかない場合は無効にする必要があります。 --}}
            <button type="button" id="add-license" class="btn btn-success"
                style="display: {{ $licenseCount >= $maxLicenses ? 'none' : 'block' }};">＋ 資格を追加
            </button>


            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const maxLicenses = {{ $maxLicenses }};
                    let licenseIndex = 1; // 0行目があるので1から始めます
                    const licenseContainer = document.querySelector(".license-container");

                    function checkButtonVisibility() {
                        const addButton = document.getElementById("add-license");
                        if (!addButton) return;
                        addButton.style.display = (licenseIndex >= maxLicenses) ? "none" : "block";
                    }

                    // ボタンが読み込まれるまで待ちます。
                    let addButton = document.getElementById("add-license");
                    if (!addButton) {
                        setTimeout(() => {
                            addButton = document.getElementById("add-license");
                            if (!addButton) {
                                console.warn("ボタンが見つかりません: 「add-license」が存在するかどうかを確認してください。");
                                return;
                            }
                            initAddLicenseFunctionality(addButton);
                        }, 500); // 0.5秒待つ
                    } else {
                        initAddLicenseFunctionality(addButton);
                    }

                    function initAddLicenseFunctionality(addButton) {
                        checkButtonVisibility();
                        addButton.addEventListener("click", function() {
                            if (licenseIndex >= maxLicenses) return;

                            // 新しい行を作成する
                            const newLicenseEntry = document.querySelector(".license-entry").cloneNode(true);

                            // 内部のすべての選択要素をクリアし、名前を修正します
                            newLicenseEntry.querySelectorAll("select, input").forEach(input => {
                                input.name = input.name.replace(/\[\d+\]/, `[${licenseIndex}]`);
                                input.value = ""; // リセットします。
                            });

                            // フォームに追加
                            licenseContainer.appendChild(newLicenseEntry);
                            licenseIndex++;

                            checkButtonVisibility(); // ボタンを再確認

                            // 新しい選択にイベントを追加する
                            attachEventListenersToSelects(newLicenseEntry);
                        });
                    }

                    function attachEventListenersToSelects(container) {
                        // グループ変更時のカテゴリの動的読み込み
                        container.querySelectorAll(".group-select").forEach(select => {
                            select.addEventListener("change", function() {
                                const categorySelect = this.closest(".license-entry").querySelector(
                                    ".category-select");
                                fetch(`/get-license-categories?group_code=${this.value}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        categorySelect.innerHTML = '<option value="">選択してください</option>';
                                        data.categories.forEach(category => {
                                            categorySelect.innerHTML +=
                                                `<option value="${category.category_code}">${category.category_name}</option>`;
                                        });
                                    });
                            });
                        });

                        // カテゴリ変更時のライセンスの動的読み込み
                        container.querySelectorAll(".category-select").forEach(select => {
                            select.addEventListener("change", function() {
                                const groupCode = this.closest(".license-entry").querySelector(
                                    ".group-select").value;
                                const licenseSelect = this.closest(".license-entry").querySelector(
                                    ".license-select");
                                fetch(`/get-licenses?group_code=${groupCode}&category_code=${this.value}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        licenseSelect.innerHTML = '<option value="">選択してください</option>';
                                        data.licenses.forEach(license => {
                                            licenseSelect.innerHTML +=
                                                `<option value="${license.code}">${license.name}</option>`;
                                        });
                                    });
                            });
                        });
                    }

                    // ページに読み込まれた要素を選択するためのイベントを追加する
                    attachEventListenersToSelects(document);
                });
            </script>

            <div class="pt-5">
                <h2 class="text-center">自己PR</h2>
            </div>
            <!-- 自己PR -->
            <div class="col-12 mb-5">
                <h5 class="text-primary mb-4">自己PR <span class="text-main-theme">
                        必須</span></h5>
                <textarea id="CONF_SelfPR" name="CONF_SelfPR" class="form-control border-dark" rows="10"
                    placeholder="ここに自己PRを入力してください">{{ old('CONF_SelfPR', $selfPR->self_pr ?? '') }}</textarea>
                <small class="text-muted">※履歴書へ表示されるのは全角220文字までです。(出力形式により多少前後します。)</small>
                @error('CONF_SelfPR')
                    <div class="text-danger">自己PRは2000文字以内で入力してください。</div>
                @enderror
            </div>

            <div class="pt-5">
                <h2 class="text-center">スキル</h2>
            </div>
            <div class="mb-4">
                <h5 class="text-primary mb-4">スキルを選択</h5>

                <div class="row g-3">
                    @foreach ($categories as $categoryCode => $categoryName)
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="border p-3" style="background-color: #e6f3d8;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>{{ $categoryCode }}</strong>
                                    <button type="button" class="btn btn-sm btn-danger remove-selected">解除</button>
                                </div>
                                <select name="skills[{{ $categoryCode }}][]" class="form-control skill-select mt-2"
                                    multiple size="10">
                                    @foreach ($skills[$categoryCode] as $skill)
                                        <option value="{{ $skill->code }}"
                                            {{ isset($selectedSkills[$categoryCode]) && in_array($skill->code, (array) $selectedSkills[$categoryCode]) ? 'selected' : '' }}>
                                            {{ $skill->detail }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('skills')
                                    <div class="text-danger">スキルを選択してください。</div>
                                @enderror

                                @error('skills.*.*')
                                    <div class="text-danger">無効なスキルが選択されています。</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <script>
                document.querySelectorAll(".remove-selected").forEach((button) => {
                    button.addEventListener("click", function() {
                        const select = this.closest(".border").querySelector(".skill-select");
                        if (select) {
                            select.value = []; // 選択したスキルをキャンセル
                            select.dispatchEvent(new Event("change"));
                        } else {
                            console.error("Skill select dropdown not found for this button.");
                        }
                    });
                });
            </script>


            @php
                $educations = $educations ?? []; // ページに読み込まれた要素を選択するためのイベントを追加する
            @endphp

            <!-- 志望動機・通勤時間・本人希望欄 -->
            @if (isset($educations) && is_array($educations) && count($educations) == 0)
                <div class="card mb-4 shadow-sm border rounded-3 education-form">
                    <div class="card-header main-theme-color text-white">志望動機 1</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="CONF_Subject" class="form-label">志望動機を選ぶための識別名 <span class="text-main-theme">
                                    必須</span></label>
                            <input id="CONF_Subject" name="educations[0][subject]" type="text"
                                class="form-control border-dark"
                                value="{{ old("educations.0.subject", $education->subject ?? '') }}">
                        </div>

                        <div class="mb-3">
                            <label for="CONF_CommuteTime" class="form-label">希望通勤時間 </label>
                            <div class="input-group">
                                <input id="CONF_CommuteTime" name="educations[0][commute_time]" type="number"
                                    class="form-control border-dark" placeholder="例: 10"
                                    value="{{ old('commute_time', $personDetails->commute_time ?? '') == '0' ? 'selected' : '' }}>">
                                <span class="input-group-text">分</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <!-- 結婚状況 -->
                            <div class="mb-3">
                                <label for="marriage_flag" class="form-label">結婚状況</label>
                                <select id="marriage_flag" name="marriage_flag" class="form-control border-dark">
                                    <option>選択してください</option>
                                    <option value="0"
                                        {{ old('marriage_flag', $personDetails->marriage_flag ?? '') == '0' ? 'selected' : '' }}>
                                        未婚
                                    </option>
                                    <option value="1"
                                        {{ old('marriage_flag', $personDetails->marriage_flag ?? '') == '1' ? 'selected' : '' }}>
                                        既婚
                                    </option>

                                </select>
                                @error('marriage_flag')
                                    <div class="text-danger">結婚状況を正しく選択してください。</div>
                                @enderror
                            </div>

                            <!-- 扶養家族 -->
                            {{--  <div class="mb-3">
                                <label for="dependent_number" class="form-label">扶養家族 (配偶者を除く)</label>
                                <input id="dependent_number" name="dependent_number" type="number"
                                    value="{{ old('dependent_number', isset($personDetails->dependent_number) ? $personDetails->dependent_number : '') }}">
                            </div>  --}}
                            <div class="mb-3">
                                <label for="dependent_number" class="form-label">扶養家族 (配偶者を除く)</label>
                                <input id="dependent_number" name="dependent_number" type="number"
                                    class="form-control border-dark" min="0" placeholder="例: 2"
                                    value="{{ old('dependent_number', $personDetails->dependent_number ?? '') }}">
                                @error('dependent_number')
                                    <div class="text-danger">扶養家族の人数を半角数字で入力してください。</div>
                                @enderror
                                {{--  @error('dependent_number')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror  --}}

                            </div>


                            <!-- 配偶者の扶養義務 -->
                            <div class="mb-3">
                                <label for="dependent_flag" class="form-label">配偶者の扶養義務</label>
                                <select id="dependent_flag" name="dependent_flag" class="form-control border-dark">
                                    <option>選択してください</option>
                                    <option value="0"
                                        {{ old('dependent_flag', $personDetails->dependent_flag ?? '') == '0' ? 'selected' : '' }}>
                                        無
                                    </option>
                                    <option value="1"
                                        {{ old('dependent_flag', $personDetails->dependent_flag ?? '') == '1' ? 'selected' : '' }}>
                                        有
                                    </option>

                                </select>
                                @error('dependent_flag')
                                    <div class="text-danger">配偶者の扶養義務を正しく選択してください。</div>
                                @enderror
                                {{--  @error('dependent_flag')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror  --}}

                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="CONF_WishMotive" class="form-label">志望動機</label>
                            <textarea id="CONF_WishMotive" name="educations[0][wish_motive]" class="form-control border-dark" rows="6"
                                placeholder="志望動機を入力してください"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="CONF_HopeColumn" class="form-label">本人希望欄</label>
                            <textarea id="CONF_HopeColumn" name="educations[0][hope_column]" class="form-control border-dark" rows="6"
                                placeholder="本人希望欄を入力してください"></textarea>
                        </div>
                    </div>
                </div>
            @endif


            @foreach ($educations as $key => $education)
                <div class="card mb-4 shadow-sm border rounded-3 education-form">
                    <div class="card-header main-theme-color text-white">志望動機 {{ $key + 1 }}</div>
                    <div class="card-body">
                        <!-- タイトル -->
                        <div class="mb-3">
                            <label for="CONF_Subject" class="form-label">志望動機を選ぶための識別名 <span class="text-main-theme">
                                    必須</span></label>
                            <input id="CONF_Subject" name="educations[{{ $key }}][subject]" type="text"
                                class="form-control border-dark"
                                value="{{ old("educations.$key.subject", $education->subject ?? '') }}">
                            <small class="text-muted">※履歴書選択時に、志望動機を選ぶための識別名です。</small>
                            @error("educations.$key.subject")
                                <div class="text-danger">識別名を入力してください。</div>
                            @enderror
                        </div>

                        <!-- 希望通勤時間 -->
                        <div class="mb-3">
                            <label for="CONF_CommuteTime" class="form-label">希望通勤時間</label>
                            <div class="input-group">
                                <input id="CONF_CommuteTime" name="educations[{{ $key }}][commute_time]"
                                    type="number" class="form-control border-dark" placeholder="例: 10"
                                    value="{{ old("educations.$key.commute_time", $education->commute_time ?? '') }}">
                                <span class="input-group-text">分</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <!-- 結婚状況 -->
                            <div class="mb-3">
                                <label for="marriage_flag" class="form-label">結婚状況</label>
                                <select id="marriage_flag" name="marriage_flag" class="form-control border-dark">
                                    <option value="">選択してください</option>
                                    <option value="0"
                                        {{ old('marriage_flag', $personDetails->marriage_flag ?? '') === 0 ? 'selected' : '' }}>
                                        未婚</option>
                                    <option value="1"
                                        {{ old('marriage_flag', $personDetails->marriage_flag ?? '') === 1 ? 'selected' : '' }}>
                                        既婚</option>
                                </select>
                            </div>

                            <!-- 扶養家族数（配偶者を除く） -->
                            <div class="mb-3">
                                <label for="dependent_number" class="form-label">扶養家族（配偶者を除く）</label>
                                <input type="number" class="form-control border-dark" id="dependent_number"
                                    name="dependent_number"
                                    value="{{ old('dependent_number', $personDetails->dependent_number ?? '') }}"
                                    placeholder="例: 2">
                            </div>


                            <!-- 配偶者の扶養義務 -->
                            <div class="mb-3">
                                <label for="dependent_flag" class="form-label">配偶者の扶養義務</label>
                                <select id="dependent_flag" name="dependent_flag" class="form-control border-dark">
                                    <option>選択してください</option>
                                    <option value="0"
                                        {{ old('dependent_flag', $personDetails->dependent_flag ?? '') == '0' ? 'selected' : '' }}>
                                        無
                                    </option>
                                    <option value="1"
                                        {{ old('dependent_flag', $personDetails->dependent_flag ?? '') == '1' ? 'selected' : '' }}>
                                        有
                                    </option>

                                </select>
                            </div>
                        </div>


                        <!-- 志望動機 -->
                        <div class="mb-3">
                            <label for="CONF_WishMotive" class="form-label">志望動機</label>
                            <textarea id="CONF_WishMotive" name="educations[{{ $key }}][wish_motive]" class="form-control border-dark"
                                rows="6" placeholder="志望動機を入力してください">{{ old("educations.$key.wish_motive", $education->wish_motive ?? '') }}</textarea>
                            <small class="text-muted">※全角で1000字以内&nbsp;</small>
                            @error("educations.$key.wish_motive")
                                <div class="text-danger">志望動機を入力してください。</div>
                            @enderror
                        </div>

                        <!-- 本人希望欄 -->
                        <div class="mb-3">
                            <label for="CONF_HopeColumn" class="form-label">本人希望欄</label>
                            <textarea id="CONF_HopeColumn" name="educations[{{ $key }}][hope_column]" class="form-control border-dark"
                                rows="6" placeholder="本人希望欄を入力してください">{{ old("educations.$key.hope_column", $education->hope_column ?? '') }}</textarea>
                            <small class="text-muted">※全角で1000字以内&nbsp;</small>
                            @error("educations.$key.hope_column")
                                <div class="text-danger">本人希望欄を入力してください。</div>
                            @enderror
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="container-fluid text-center mt-4">
            <div class="row justify-content-center row-cols-2 row-cols-md-4 g-2">
                <div class="col-6">
                    <a href="{{ route('educate-history') }}" class="btn btn-primary w-100">戻る</a>
                </div>
                <div class="col-6">
                    <button type="submit" class="btn btn-main-theme w-100 m-1">保存</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        let educationIndex = 1;
        document.getElementById('add-education').addEventListener('click', function() {
            const educationForm = document.querySelector('.education-form').cloneNode(true);
            educationForm.querySelectorAll('input, textarea').forEach((input) => {
                input.name = input.name.replace(/\[0\]/, `[${educationIndex}]`);
                input.value = '';
            });
            educationForm.querySelector('.card-header').textContent = `志望動機 ${educationIndex + 1}`;
            document.getElementById('education-forms').appendChild(educationForm);
            educationIndex++;

        });
    </script>
    <script>
        function initDeleteLicenseButtons() {
            document.querySelectorAll(".delete-license").forEach(button => {
                button.addEventListener("click", function() {
                    const licenseId = this.dataset.id;
                    if (!licenseId || !confirm("この資格を削除しますか？")) return;

                    fetch(`/license/${licenseId}`, {
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const entry = this.closest(".license-entry");
                                entry.style.transition = "all 0.3s ease-out";
                                entry.style.opacity = 0;
                                setTimeout(() => entry.remove(), 300);
                            } else {
                                alert("削除に失敗しました。");
                            }
                        })
                        .catch(error => {
                            console.error("削除エラー:", error);
                            alert("通信エラーが発生しました。");
                        });
                });
            });
        }

        // ページが読み込まれたときにトリガーされます
        document.addEventListener("DOMContentLoaded", function() {
            initDeleteLicenseButtons();
        });

        function initInlineRemoveButtons() {
            document.querySelectorAll(".remove-inline-license").forEach(button => {
                button.addEventListener("click", function() {
                    const entry = this.closest(".license-entry");
                    if (entry && confirm("この入力欄を削除しますか？")) {
                        entry.remove();
                    }
                });
            });
        }

        // ページが読み込まれたときにトリガーされます
        document.addEventListener("DOMContentLoaded", function() {
            initDeleteLicenseButtons();
            initInlineRemoveButtons();

            const addBtn = document.getElementById("add-license");
            if (addBtn) {
                addBtn.addEventListener("click", function() {
                    setTimeout(() => {
                        initInlineRemoveButtons(); // 🔄 新しいブロックにも適用されます
                    }, 200);
                });
            }
        });
    </script>
    {{--  <script src="{{ asset('js/signin.js') }}"></script>  --}}
@endsection
