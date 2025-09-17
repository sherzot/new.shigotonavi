document.addEventListener("DOMContentLoaded", function () {
  // Get all elements for multiple job selections
  const bigClassSelects = document.querySelectorAll(".big-class-select");
  const middleClassSelects = document.querySelectorAll(".middle-class-select");

  const groupSelect = document.getElementById("group_code");
  const categorySelect = document.getElementById("category_code");
  const licenseSelect = document.getElementById("license_code");

  const jobTypeSelect = document.getElementById("job_type");
  const salaryLabel = document.getElementById("salary_label");
  const salaryNotice = document.getElementById("salary_notice");
  const notificationMessage = document.getElementById("notification_message");

  // Helper function to reset select dropdown
  function resetSelect(selectElement, placeholder = "選択してください") {
    selectElement.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
  }

  // Function to handle job type selection dynamically
  function handleBigClassChange(event) {
    const bigClassCode = event.target.value;
    const row = event.target.closest(".row");
    const middleClassSelect = row.querySelector(".middle-class-select"); // Faqat shu row ichidagi middle selectni olish

    // Reset the corresponding middle class dropdown
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

  // Attach event listeners for all big class selects
  bigClassSelects.forEach((select) => {
    select.addEventListener("change", handleBigClassChange);
  });

  // 📌 **グループ選択 (group_code) o'zgarganda category_code'larni yuklash**
  // Group selection logic
  if (groupSelect) {
    groupSelect.addEventListener("change", function () {
      const groupCode = this.value;

      // Reset category and license dropdowns
      resetSelect(categorySelect);
      resetSelect(licenseSelect);

      if (!groupCode) return;

      // Fetch categories
      fetch(`/get-license-categories?group_code=${groupCode}`)
        .then((response) => {
          if (!response.ok)
            throw new Error("Failed to fetch license categories");
          return response.json();
        })
        .then((data) => {
          if (data.categories && data.categories.length > 0) {
            data.categories.forEach((category) => {
              const option = document.createElement("option");
              option.value = category.category_code;
              option.textContent = category.category_name;
              categorySelect.appendChild(option);
            });
          } else {
            console.warn("No categories found");
          }
        })
        .catch((error) =>
          console.error("Error fetching license categories:", error)
        );
    });
  }

  // Category selection logic
  if (categorySelect) {
    categorySelect.addEventListener("change", function () {
      const groupCode = groupSelect.value;
      const categoryCode = this.value;

      // Reset the license dropdown
      resetSelect(licenseSelect);

      if (!groupCode || !categoryCode) return;

      // Fetch licenses
      fetch(
        `/get-licenses?group_code=${groupCode}&category_code=${categoryCode}`
      )
        .then((response) => {
          if (!response.ok) throw new Error("Failed to fetch licenses");
          return response.json();
        })
        .then((data) => {
          if (data.licenses && data.licenses.length > 0) {
            data.licenses.forEach((license) => {
              const option = document.createElement("option");
              option.value = license.code;
              option.textContent = license.name;
              licenseSelect.appendChild(option);
            });
          } else {
            console.warn("No licenses found");
          }
        })
        .catch((error) => console.error("Error fetching licenses:", error));
    });
  }

  // Initial validation to reset selects on page load
  if (categorySelect) resetSelect(categorySelect);
  if (licenseSelect) resetSelect(licenseSelect);
  // 📌 Salary Type Selection: Annual vs Hourly
  const yearlyInputs = [
    document.getElementById("desired_salary_annual_min"),
    document.getElementById("desired_salary_annual_max"),
    document.getElementById("desired_salary_monthly_min"),
    document.getElementById("desired_salary_monthly_max"),
  ];

  function toggleSalaryFields() {
    const selectedValue = jobTypeSelect.value;

    if (selectedValue === "1") {
      // "紹介" tanlangan bo‘lsa
      salaryNotice.textContent = "紹介を選択したため、年収のみ入力可能です。";
      notificationMessage.style.display = "none"; // Xabarni yashirish
      salaryLabel.style.display = "block"; // Labelni chiqarish

      // Yillik maosh maydonlarini faollashtirish
      yearlyInputs.forEach((input) => {
        input.disabled = false;
        input.style.backgroundColor = "";
      });
    } else if (selectedValue === "2" || selectedValue === "3") {
      // "派遣" yoki "紹介予定派遣" tanlangan bo‘lsa
      salaryNotice.textContent = "";
      notificationMessage.style.display = "block"; // Xabarni ko‘rsatish
      salaryLabel.style.display = "none"; // Labelni yashirish

      // Yillik maosh maydonlarini bloklash
      yearlyInputs.forEach((input) => {
        input.disabled = true;
        input.value = "";
        input.style.backgroundColor = "#e9ecef";
      });

      // ✅ Agentga notifikatsiya yuborish (backendga API chaqirish)
      sendNotificationToAgent();
    } else {
      salaryNotice.textContent = "";
      notificationMessage.style.display = "none";
      salaryLabel.style.display = "none"; // Labelni yashirish

      // Barcha maydonlarni bloklash
      yearlyInputs.forEach((input) => {
        input.disabled = true;
        input.value = "";
        input.style.backgroundColor = "#e9ecef";
      });
    }
  }

  function sendNotificationToAgent() {
    console.log("エージェントにメールを送信..."); // Bu joyni backend bilan bog‘lash kerak
  }

  // Event listener qo‘shish
  jobTypeSelect.addEventListener("change", toggleSalaryFields);
  toggleSalaryFields();
  // ✅ "解除" (Remove Selected Skill) tugmasi ishlashi uchun
  document.querySelectorAll(".remove-selected").forEach((button) => {
    button.addEventListener("click", function () {
      let select = this.closest(".row").querySelector(".skill-select");

      if (select) {
        for (let i = 0; i < select.options.length; i++) {
          select.options[i].selected = false;
        }
        select.dispatchEvent(new Event("change"));
      }
    });
  });
});
