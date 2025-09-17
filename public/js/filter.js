document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ JavaScript が読み込まれました!");

    const checkboxes = document.querySelectorAll(".filter-checkbox");

    if (checkboxes.length === 0) {
        console.error("❌ チェックボックスが見つかりません。");
        return;
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", function () {
            let selectedFilters = [];

            checkboxes.forEach(cb => {
                if (cb.checked) {
                    selectedFilters.push(cb.value);
                }
            });

            let currentPage = document.querySelector(".pagination .page-item.active a")?.innerText || 1;

            console.log("📌 選択したフィルター:", selectedFilters);

            fetch(`/matchings/filterJobs?page=${currentPage}`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ filters: selectedFilters })
            })
            .then(response => response.json())
            .then(data => {
                console.log("📌 AJAX Response:", data);

                let jobContainer = document.querySelector(".row.g-4");
                let paginationContainer = document.querySelector(".pagination");
                let totalJobsContainer = document.getElementById("total-jobs");

                if (!jobContainer || !paginationContainer || !totalJobsContainer) {
                    console.error("❌ HTML 要素が見つかりません。");
                    return;
                }

                // 🔥 古いページネーションを削除し、新しいページネーションを追加します。
                paginationContainer.innerHTML = "";
                paginationContainer.innerHTML = data.pagination_html;

                jobContainer.innerHTML = data.jobs_html;
                totalJobsContainer.innerText = `合計${data.total_jobs}件`;

                updatePaginationLinks();
            })
            .catch(error => console.error("❌ エラーが発生しました:", error));
        });
    });

    function updatePaginationLinks() {
        document.querySelectorAll(".pagination a").forEach(link => {
            link.addEventListener("click", function (e) {
                e.preventDefault();
                let page = this.getAttribute("href").split("page=")[1];

                fetch(`/matchings/filterJobs?page=${page}`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ filters: getSelectedFilters() })
                })
                .then(response => response.json())
                .then(data => {
                    let jobContainer = document.querySelector(".row.g-4");
                    let paginationContainer = document.querySelector(".pagination");
                    let totalJobsContainer = document.getElementById("total-jobs");

                    paginationContainer.innerHTML = "";
                    paginationContainer.innerHTML = data.pagination_html;
                    jobContainer.innerHTML = data.jobs_html;
                    totalJobsContainer.innerText = `合計${data.total_jobs}件`;

                    updatePaginationLinks();
                })
                .catch(error => console.error("❌ エラーが発生しました:", error));
            });
        });
    }

    function getSelectedFilters() {
        let filters = [];
        document.querySelectorAll(".filter-checkbox:checked").forEach(cb => filters.push(cb.value));
        return filters;
    }
});
