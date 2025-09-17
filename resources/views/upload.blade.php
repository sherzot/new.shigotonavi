@extends('layouts.top')

@section('title', '証明写真の登録')
@section('content')
    <div class="container-fluid mt-4">
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-12 col-md-10 col-lg-6 col-xl-5">
                <div class="card p-4 shadow-lg rounded-3 border-0">
                    <h3 class="text-center mb-4 text-dark">証明写真の登録</h3>

                    @if (session('success'))
                        <div class="alert alert-success text-center">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger text-center">{{ session('error') }}</div>
                    @endif

                    <form id="upload-form" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">スタッフコード：</label>
                            <input type="text" id="staff_code" name="staff_code" value="{{ $staffCode }}"
                                class="form-control border-dark w-100 w-lg-75 rounded-2 shadow-sm" required maxlength="8">
                        </div>
                        {{--  <span id="file-error" class="text-main-theme small d-block mt-2"></span>  --}}
                        <div class="mb-3">
                            <label class="form-label">写真画像 取込み：<span class="text-main-theme">
                                JPEG, JPG と PNG 形式のみ</span></label>
                            <div class="input-group w-100 w-lg-75">
                                <input type="file" name="image" class="form-control d-none" id="file-input"
                                    accept="image/jpeg, image/jpg, , image/png" required> <!-- , image/ipg, image/png -->
                                <label for="file-input" class="btn btn-outline-primary shadow-sm ">ファイルを選択</label>
                                <span id="file-name" class="form-control border-dark shadow-sm">選択ください</span>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <img id="preview-image" src="" alt="プレビュー画像" class="rounded shadow-lg border border-2"
                                style="max-width: 100%; height: auto; display: none;">
                        </div>

                        <div class="my-3 text-muted small">
                            <ul class="list-unstyled">
                                <li>※ 画像ファイルのサイズは <strong class="text-main-theme">500KB まで</strong> です。</li>
                                <li>※ 推奨サイズは <strong class="text-primary">240px × 320px</strong> です。</li>
                                <li>※ 利用可能なファイル形式は <strong class="text-main-theme">JPEG 形式のみ</strong> です。</li>
                            </ul>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <a href="{{ route('self_pr') }}" class="btn btn-primary w-100">戻る</a>
                            </div>
                            <div class="col-4">
                                <button type="button" id="skip-upload"
                                    class="btn btn-secondary w-100 shadow-sm rounded-2 col-6">
                                    スキップ
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="button" id="confirm-upload"
                                    class="btn btn-main-theme w-100 shadow-sm rounded-2 mb-3">
                                    登録
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let fileInput = document.getElementById("file-input");
            let previewImage = document.getElementById("preview-image");
            let confirmButton = document.getElementById("confirm-upload");
            let skipButton = document.getElementById("skip-upload");

            // 🖼️ **画像プレビュー**
            fileInput.addEventListener("change", function() {
                let file = fileInput.files[0];

                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                }
            });

            // ✅ **スキップボタンは機能するはずです**
            document.getElementById("skip-upload").addEventListener("click", function() {
                fetch("{{ route('resume.skip') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    window.location.href = data.redirect;
                })
                .catch(error => console.error("エラー:", error));
            });
            

            document.getElementById("confirm-upload").addEventListener("click", function () {
                let staffCode = document.getElementById("staff_code").value;
                let imageFile = document.getElementById("file-input").files[0];
            
                if (!imageFile) {
                    alert("❌ 画像が選択されていません！");
                    return;
                }
            
                // ❗ Fayl turi tekshirish
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(imageFile.type)) {
                    alert("❌ JPEG(もしJPG) または PNG 形式の画像を選択してください！");
                    return;
                }
            
                // ❗ Fayl hajmini tekshirish (500KB = 512000 bytes)
                if (imageFile.size > 512000) {
                    alert("❌ 画像サイズが大きすぎます（500KB 以下）！");
                    return;
                }
            
                let formData = new FormData();
                formData.append("staff_code", staffCode);
                formData.append("picture", imageFile);
                formData.append("_token", document.querySelector('meta[name=\"csrf-token\"]').content);
            
                fetch("{{ route('upload.confirm') }}", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("✔️ " + data.message);
                        window.location.href = data.redirect;
                    } else {
                        alert("❌ エラーが発生しました！");
                    }
                })
                .catch(error => console.error("エラー:", error));
            });            
        });
    </script>
@endsection
