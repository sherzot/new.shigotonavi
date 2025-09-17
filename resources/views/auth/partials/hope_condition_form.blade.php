<form id="hopeForm" method="POST" action="{{ route('matchings.filterJobs') }}">
    @csrf
    <h5 class="mb-0 fw-bold text-start mb-3 pt-0 pb-2 fs-f20">検索条件</h5>
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label for="big_class_code" class="form-label">希望職種 (大分類）</label>
                <select name="big_class_code" id="big_class_code" class="form-control w-100">
                    <option value="">選択してください</option>
                    @foreach ($bigClasses as $bigClass)
                    <option value="{{ $bigClass->big_class_code }}">
                        {{ $bigClass->big_class_name }}</option>
                    @endforeach
                </select>
                @error('big_class_code')
                <div class="alert text-main-theme">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="big_class_code" class="form-label">希望職種 (小分類）</label>
                <select name="job_category" id="middle_class_code" class="form-control w-100">

                </select>
                @error('job_category')
                <div class="alert text-main-theme">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- 希望勤務地 -->
        <div class="col-md-4">
            <label class="form-label">希望勤務地</label>
            <select name="prefecture_code[]" class="form-control">
                <option value="" selected>選択してください</option>
                @foreach ($prefectures as $prefecture)
                <option value="{{ $prefecture->code }}">
                    {{ $prefecture->detail }}
                </option>
                @endforeach
            </select>
            @error('prefecture_code[]')
            <div class="alert" style="color: rgba(255, 0, 0, 0.674);">{{ $message }}</div>
            @enderror
        </div>
        <!-- 希望給与（給料） -->
        <div class="mb-3">
            <label class="form-label fw-normal text-dark">希望給与 <small class="text-main-theme">年収か時給を選んでください！</small></label>
            <div class="row mt-2">
                <div class="col-md-6 col-12">
                    <div class="col-auto">
                        <input type="radio" id="annual" name="salary_type" value="annual" class="form-check-input" onchange="toggleSalarySelects()">
                        <label for="annual" class="form-check-label text-main-theme">年収</label>
                    </div>
                    <select name="salary" id="desired_salary_annual" class="form-select form-select-sm" disabled>
                        <option value="">年収（給料）</option>
                        @foreach (range(100, 1000, 50) as $yen)
                            <option value="{{ $yen }}">{{ $yen }} 万円以上</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-12">
                    <div class="col-auto">
                        <input type="radio" id="hourly" name="salary_type" value="hourly" class="form-check-input" onchange="toggleSalarySelects()">
                        <label for="hourly" class="form-check-label text-main-theme">時給</label>
                    </div>
                    <select name="hourly_wage" id="desired_salary_hourly" class="form-select form-select-sm" disabled>
                        <option value="">時給</option>
                        @foreach (range(800, 4500, 100) as $yen)
                            <option value="{{ $yen }}">時給 {{ $yen }} 円以上</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <!-- 補足フラグ（checkboxOptions）-->
        <div class="mb-4">
            <label class="form-label">こだわり条件（複数選択可）</label>
            <div class="row">
                @foreach ($checkboxOptions as $field => $label)
                    <div class="col-md-4 col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}">
                            <label class="form-check-label fs-f12" for="{{ $field }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const bigClassSelect = document.getElementById("big_class_code");
            const middleClassSelect = document.getElementById("middle_class_code");
            const savedBig = sessionStorage.getItem("big_class_code");
            const savedMid = sessionStorage.getItem("middle_class_code");

            const annualRadio = document.getElementById('annual');
            const hourlyRadio = document.getElementById('hourly');
            const annualSelect = document.getElementById('desired_salary_annual');
            const hourlySelect = document.getElementById('desired_salary_hourly');

            function toggleSalarySelects() {
                const annualSelected = annualRadio.checked;
                const hourlySelected = hourlyRadio.checked;
            
                if (annualSelected) {
                    annualSelect.disabled = false;
                    hourlySelect.disabled = true;
                    hourlySelect.value = '';
                } else if (hourlySelected) {
                    annualSelect.disabled = true;
                    annualSelect.value = '';
                    hourlySelect.disabled = false;
                } else {
                    annualSelect.disabled = true;
                    annualSelect.value = '';
                    hourlySelect.disabled = true;
                    hourlySelect.value = '';
                }
            }

            toggleSalarySelects();
            if (annualRadio) annualRadio.addEventListener("change", toggleSalarySelects);
            if (hourlyRadio) hourlyRadio.addEventListener("change", toggleSalarySelects);

            function resetSelect(selectElement, placeholder = "選択してください") {
                if (!selectElement) return;
                selectElement.innerHTML = "";
                const option = document.createElement("option");
                option.value = "";
                option.textContent = placeholder;
                selectElement.appendChild(option);
            }

            resetSelect(middleClassSelect);
            if (middleClassSelect) {
                middleClassSelect.addEventListener("change", function () {
                    sessionStorage.setItem("middle_class_code", this.value);
                });
            }
            if (bigClassSelect) {
                bigClassSelect.addEventListener("change", function () {
                    const bigClassCode = this.value;
                    resetSelect(middleClassSelect);
                    if (!bigClassCode) return;

                    fetch(`/get-job-types?big_class_code=${bigClassCode}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(item => {
                                const option = document.createElement("option");
                                option.value = item.middle_class_code;
                                option.textContent = item.middle_clas_name;
                                if (item.middle_class_code === savedMid) {
                                    option.selected = true;
                                }
                                middleClassSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error("Error fetching job types:", error);
                            resetSelect(middleClassSelect);
                        });
                });
            }
        });
    </script>
</form>
