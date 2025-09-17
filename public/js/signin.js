document.addEventListener("DOMContentLoaded", function () {
  // Get elements from the DOM
  const bigClassSelect = document.getElementById("big_class_code");
  const middleClassSelect = document.getElementById("middle_class_code");
  const groupSelect = document.getElementById("group_code");
  const categorySelect = document.getElementById("category_code");
  const licenseSelect = document.getElementById("license_code");

  // Helper function to reset select dropdown
  function resetSelect(selectElement, placeholder = "選択してください") {
    while (selectElement.firstChild) {
        selectElement.removeChild(selectElement.firstChild);
    }
    const option = document.createElement("option");
    option.value = "";
    option.textContent = placeholder;
    option.selected = true;
    selectElement.appendChild(option);
}


  // Big class selection logic
  if (bigClassSelect) {
    bigClassSelect.addEventListener("change", function () {
        const bigClassCode = this.value;
        resetSelect(middleClassSelect); // ✅ Avval select bo‘shashtiriladi
      
        if (!bigClassCode) return;
      
        fetch(`/get-job-types?big_class_code=${bigClassCode}`)
          .then((response) => {
            if (!response.ok) throw new Error("Failed to fetch job types");
            return response.json();
          })
          .then((data) => {
            if (data.length > 0) {
              data.forEach((jobType) => {
                const option = document.createElement("option");
                option.value = jobType.middle_class_code;
                option.textContent = jobType.middle_clas_name;
                middleClassSelect.appendChild(option);
              });
            }
          })
          .catch((error) => {
            console.error("Error fetching job types:", error);
            resetSelect(middleClassSelect); // ✅ Xatolik bo‘lsa ham, "選択してください" chiqariladi
          });
      });      
  }
  resetSelect(middleClassSelect);

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
  
  if (middleClassSelect) resetSelect(middleClassSelect);
  if (categorySelect) resetSelect(categorySelect);
  if (licenseSelect) resetSelect(licenseSelect);
  
});
