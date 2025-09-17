document.querySelectorAll(".big-class-select").forEach((select) => {
  select.addEventListener("change", function () {
    const bigClassSelects = document.querySelectorAll(".big-class-select");
    const middleClassSelects = document.querySelectorAll(
      ".middle-class-select"
    );

    // ヘルパー関数: 選択した要素をクリアする
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
  });
});
