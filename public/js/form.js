document.addEventListener('DOMContentLoaded', function() {
    // チェックボックスを取得する
    const checkbox = document.getElementById('flexCheckChecked');
    const submitButton = document.getElementById('submitButton');

    checkbox.addEventListener('change', function() {
        submitButton.disabled = !checkbox.checked;
    });

});

function validateForm() {
    let valid = true;
    const surname = document.getElementById('surname').value.trim();
    const name = document.getElementById('name').value.trim();
    const katakanaSurname = document.getElementById('katakana_surname').value.trim();
    const katakanaName = document.getElementById('katakana_name').value.trim();

    // Clear previous error messages
    document.getElementById('kanjiError').innerText = '';
    document.getElementById('katakanaError').innerText = '';

    if (!surname || !name) {
        valid = false;
        document.getElementById('kanjiError').innerText = '漢字が必要です。';
    }

    if (!katakanaSurname || !katakanaName) {
        valid = false;
        document.getElementById('katakanaError').innerText = 'フリガナが必要です。';
    }

    return valid;
};
function needcount() {
    var selectedPrefecture = document.getElementById("prefecture_code").value; // To'g'ri nom
    var selectedCity = document.getElementById("city").value;

    // Tanlangan qiymatni konsolga chiqarish
    console.log("選択した都道府県: " + selectedPrefecture); // To'g'ri o'zgaruvchini ishlatish
    console.log("選択した市: " + selectedCity);

    if (selectedPrefecture === "090" && selectedCity === "001") {
        alert("全国と東京都が選択されました。");
    } else if (selectedPrefecture === "090") {
        alert("全日本（全国）を選択しました。");
    } else if (selectedCity === "001") {
        alert("東京都が選択されました。");
    } else if (selectedPrefecture && selectedCity) {
        alert(selectedPrefecture + " と " + selectedCity + " が選択されました。");
    } else {
        alert("地域と市を正しく選択してください。");
    }
}
document.addEventListener('DOMContentLoaded', function () {
    const bigClassSelect = document.getElementById('big_class_code');
    const middleClassSelect = document.getElementById('middle_class_code');

    // 初期状態: old() orqali tiklash
    const selectedBigClass = "{{ old('big_class_code') }}";
    const selectedMiddleClass = "{{ old('job_category') }}";

    if (selectedBigClass) {
        fetch(`/get-job-types?big_class_code=${selectedBigClass}`)
            .then(response => response.json())
            .then(data => {
                middleClassSelect.innerHTML = '<option value="" disabled>選択してください</option>';
                data.forEach(jobType => {
                    const option = document.createElement('option');
                    option.value = jobType.middle_class_code;
                    option.textContent = jobType.middle_clas_name;
                    middleClassSelect.appendChild(option);
                });
                // Eski middle_class_code qiymatini tiklash
                middleClassSelect.value = selectedMiddleClass;
            })
            .catch(error => console.error('Error loading job types:', error));
    }

    // big_class_code o‘zgarganda middle_class_code ni yuklash
    bigClassSelect.addEventListener('change', function () {
        const bigClassCode = this.value;

        fetch(`/get-job-types?big_class_code=${bigClassCode}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('職種タイプのデータを読み込めませんでした。');
                }
                return response.json();
            })
            .then(data => {
                middleClassSelect.innerHTML = '<option value="" disabled selected>選択してください</option>'; // Reset
                data.forEach(jobType => {
                    const option = document.createElement('option');
                    option.value = jobType.middle_class_code;
                    option.textContent = jobType.middle_clas_name;
                    middleClassSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading job types:', error));
    });

    // ラジオボタンの機能を追加する
    const annualRadio = document.getElementById('annual');
    const hourlyRadio = document.getElementById('hourly');
    const annualInput = document.getElementById('desired_salary_annual');
    const hourlyInput = document.getElementById('desired_salary_hourly');

    function toggleSalaryFields() {
        if (annualRadio.checked) {
            annualInput.disabled = false;
            hourlyInput.disabled = true;
            hourlyInput.value = '';
        } else if (hourlyRadio.checked) {
            hourlyInput.disabled = false;
            annualInput.disabled = true;
            annualInput.value = '';
        }
    }

    // Radiobuttonlarni tekshirish va funksiyani ulash
    annualRadio.addEventListener('change', toggleSalaryFields);
    hourlyRadio.addEventListener('change', toggleSalaryFields);

    // Sahifa yuklanganda boshlang'ich holatni o'rnatish
    toggleSalaryFields();
});







