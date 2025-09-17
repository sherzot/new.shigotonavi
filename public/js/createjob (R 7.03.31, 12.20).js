document.addEventListener("DOMContentLoaded", function () {
  // 複数のジョブ選択のすべての要素を取得する
  const bigClassSelects = document.querySelectorAll(".big-class-select");
  const middleClassSelects = document.querySelectorAll(".middle-class-select");

  const groupSelect = document.getElementById("group_code");
  const categorySelect = document.getElementById("category_code");
  const licenseSelect = document.getElementById("license_code");

  const orderTypeSelect = document.getElementById("order_type");
  const salaryLabel = document.getElementById("salary_label");
  const salaryLabel2 = document.getElementById("salary_label2");
  const salaryNotice = document.getElementById("salary_notice");
  const employment_start_day = document.getElementById("employment_start_day");
  const employment_start_date2 = document.getElementById(
    "employment_start_date2"
  );
  const notificationMessage = document.getElementById("notification_message");
  const notificationMessage2 = document.getElementById("notification_message2");

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
  // 年収入力要素を取得する
  const yearlyInputs = [
    document.getElementById("desired_salary_annual_min"),
    document.getElementById("desired_salary_annual_max"),
    document.getElementById("desired_salary_monthly_min"),
    document.getElementById("desired_salary_monthly_max"),
    document.getElementById("employment_start_date"),
    document.getElementById("employment_start_date2"),
  ];

  // 収益現場管理機能
  function toggleSalaryFields() {
    const selectedValue = orderTypeSelect.value;

    if (selectedValue === "2") {
      // "紹介" を選択した場合
      // order_type 2 bo‘lganda
      salaryNotice.textContent = "紹介を選択したため、年収のみ入力可能です。";
      notificationMessage.style.display = "none";
      notificationMessage2.style.display = "none";
      salaryLabel.style.display = "block";
      salaryLabel2.style.display = "block";
      employment_start_day.style.display = "block";
      employment_start_date2.style.display = "none";

      // フィールドをオプションにする
      document.getElementById("workStartDay").required = false;
      document.getElementById("workStartDay").value = "";　// クリア
      document.getElementById("workEndDay").required = false;
      document.getElementById("workEndDay").value = "";

      // 年収欄を有効にする
      yearlyInputs.forEach((input) => {
        input.disabled = false;
        input.style.backgroundColor = "";
      });
    } else if (selectedValue === "1" || selectedValue === "3") {
      // 「派遣」または「紹介予定派遣」を選択した場合、つまりorder_typeが1または3の場合
      salaryNotice.textContent = "";
      notificationMessage.style.display = "block";
      notificationMessage2.style.display = "block";
      employment_start_date2.style.display = "block";
      salaryLabel.style.display = "none";
      salaryLabel2.style.display = "none";
      employment_start_day.style.display = "none";

      // フィールドを必須にする
      document.getElementById("workStartDay").required = true;
      document.getElementById("workEndDay").required = true;

      // エージェントにメッセージを送信
      sendNotificationToAgent();
    } else {
      // 何も選択されていない場合
      salaryNotice.textContent = "";
      notificationMessage.style.display = "none";
      notificationMessage2.style.display = "none";
      salaryLabel.style.display = "none";
      salaryLabel2.style.display = "none";
      employment_start_day.style.display = "none";

      // 清掃とボランティア活動エリア
      document.getElementById("workStartDay").required = false;
      document.getElementById("workStartDay").value = "";
      document.getElementById("workEndDay").required = false;
      document.getElementById("workEndDay").value = "";

      // すべての収入フィールドをブロック
      // yearlyInputs.forEach((input) => {
      //   input.disabled = true;
      //   input.value = ""; // エリアの清掃
      //   input.style.backgroundColor = "#fff";
      // });
    }
  }

  function sendNotificationToAgent() {
    console.log("エージェントにメールを送信..."); // ここでバックエンドに接続する必要があります。
  }

  // 「order_type」が選択されると関数が呼び出されます
  if (orderTypeSelect) {
    orderTypeSelect.addEventListener("change", toggleSalaryFields);
    toggleSalaryFields(); // ページが読み込まれたときにトリガーされます
} else {
    console.error("Order_type 選択要素が見つかりません。");
  }

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
