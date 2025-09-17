document.addEventListener("DOMContentLoaded", function () {
  // 複数のジョブ選択のすべての要素を取得する
  const bigClassSelects = document.querySelectorAll(".big-class-select");
  const middleClassSelects = document.querySelectorAll(".middle-class-select");

  const groupSelect = document.getElementById("group_code");
  const categorySelect = document.getElementById("category_code");
  const licenseSelect = document.getElementById("license_code");

  // 選択ドロップダウンをリセットするヘルパー関数
  function resetSelect(selectElement, placeholder = "選択してください") {
    selectElement.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
  }

  // ジョブタイプの選択を動的に処理する機能
  function handleBigClassChange(event) {
    const bigClassCode = event.target.value;
    const row = event.target.closest(".row");
    const middleClassSelect = row.querySelector(".middle-class-select"); // この行の中央の選択のみを取得します

    // 対応する中流階級のドロップダウンをリセットする
    resetSelect(middleClassSelect);

    if (!bigClassCode) return;

    fetch(`/get-job-types?big_class_code=${bigClassCode}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.length > 0) {
          data.forEach((jobType) => {
            const option = document.createElement("option");
            option.value = jobType.middle_class_code;
            option.textContent = jobType.middle_clas_name;
            middleClassSelect.appendChild(option);
          });
        } else {
          console.warn("No middle classes found");
        }
      })
      .catch((error) => console.error("Error fetching job types:", error));
  }

  // すべてのbig_class_code選択にイベントリスナーをアタッチする
  bigClassSelects.forEach((select) => {
    select.addEventListener("change", handleBigClassChange);
  });

  // 📌 **グループ選択 (group_code) が変更されたときに category_codes をロードする**
  document.querySelectorAll(".group-select").forEach((groupSelect) => {
    groupSelect.addEventListener("change", function () {
      const row = this.dataset.row;
      const categorySelect = document.getElementById(`category_code_${row}`);
      const licenseSelect = document.getElementById(`license_code_${row}`);

      resetSelect(categorySelect);
      resetSelect(licenseSelect);

      if (!this.value) return;

      fetch(`/get-license-categories?group_code=${this.value}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.categories && data.categories.length > 0) {
            data.categories.forEach((category) => {
              const option = document.createElement("option");
              option.value = category.category_code;
              option.textContent = category.category_name;
              categorySelect.appendChild(option);
            });
          }
        })
        .catch((error) => console.error("Error fetching categories:", error));
    });
  });
  document.querySelectorAll(".group-select").forEach((groupSelect) => {
    if (groupSelect.value) {
      groupSelect.dispatchEvent(new Event("change"));
    }
  });

  // カテゴリ選択にイベントリスナーをアタッチする
  document.querySelectorAll(".category-select").forEach((categorySelect) => {
    categorySelect.addEventListener("change", function () {
      const row = this.dataset.row;
      const groupCode = document.getElementById(`group_code_${row}`).value;
      const licenseSelect = document.getElementById(`license_code_${row}`);

      resetSelect(licenseSelect);

      if (!groupCode || !this.value) return;

      fetch(`/get-licenses?group_code=${groupCode}&category_code=${this.value}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.licenses && data.licenses.length > 0) {
            data.licenses.forEach((license) => {
              const option = document.createElement("option");
              option.value = license.code;
              option.textContent = license.name;
              licenseSelect.appendChild(option);
            });
          }
        })
        .catch((error) => console.error("Error fetching licenses:", error));
    });
  });
  document.querySelectorAll(".category-select").forEach((categorySelect) => {
    if (categorySelect.value) {
      categorySelect.dispatchEvent(new Event("change"));
    }
  });

  // ✅ 「選択したスキルを削除」ボタンの機能
  document.querySelectorAll(".remove-selected").forEach((button) => {
    button.addEventListener("click", function () {
      const select = this.closest(".border").querySelector(".skill-select");

      if (select) {
        for (let i = 0; i < select.options.length; i++) {
          select.options[i].selected = false;
        }
        select.dispatchEvent(new Event("change"));
      } else {
        console.error("Skill select dropdown not found for this button.");
      }
    });
  });
});
