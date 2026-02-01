<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF契約書アップロード - 合宿費用計算</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php require VIEWS_PATH . '/layouts/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="d-flex align-items-center mb-4">
                    <a href="/" class="btn btn-outline-secondary me-3">
                        <i class="bi bi-arrow-left"></i> 戻る
                    </a>
                    <h2 class="mb-0">PDF契約書アップロード</h2>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">契約書PDFから料金を自動入力</h5>
                        <p class="text-muted">
                            旅行代理店からの契約書PDFをアップロードすると、宿泊費やバス料金などの情報を自動抽出します。
                        </p>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>対応している契約書：</strong>
                            <ul class="mb-0 mt-2">
                                <li>毎日コムネット「御予約確認書」（宿泊費、入湯税、施設料金）</li>
                                <li>毎日コムネット「貸切バス契約書」（バス料金、高速代）</li>
                                <li>毎日コムネット「旅行申込書兼確定書」（総合契約）</li>
                            </ul>
                        </div>

                        <!-- アップロードフォーム -->
                        <form id="pdfUploadForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="campSelect" class="form-label">適用する合宿を選択</label>
                                <select class="form-select" id="campSelect" name="camp_id" required>
                                    <option value="">合宿を選択してください</option>
                                    <!-- 合宿リストはJavaScriptで動的に読み込み -->
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="pdfFile" class="form-label">PDFファイル</label>
                                <input class="form-control" type="file" id="pdfFile" name="pdf" accept=".pdf" required>
                                <div class="form-text">最大ファイルサイズ: 10MB</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="uploadBtn">
                                    <i class="bi bi-upload"></i> アップロードして解析
                                </button>
                            </div>
                        </form>

                        <!-- プログレス表示 -->
                        <div id="uploadProgress" class="mt-3" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%">
                                    解析中...
                                </div>
                            </div>
                        </div>

                        <!-- エラー表示 -->
                        <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span id="errorMessage"></span>
                        </div>
                    </div>
                </div>

                <!-- 使い方 -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h5 class="card-title">使い方</h5>
                        <ol>
                            <li>料金を反映したい合宿を選択します</li>
                            <li>旅行代理店から送られてきた契約書PDFをアップロードします</li>
                            <li>自動的にPDFから料金情報が抽出されます</li>
                            <li>抽出結果を確認・修正して、合宿データに反映します</li>
                        </ol>
                        <p class="text-muted mb-0">
                            <i class="bi bi-lightbulb"></i>
                            同じ合宿に対して複数のPDFをアップロードできます（宿泊とバスの契約書が別々の場合など）
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 合宿リストを読み込み
        async function loadCamps() {
            try {
                const response = await fetch('/api/camps');
                const camps = await response.json();

                const select = document.getElementById('campSelect');
                camps.forEach(camp => {
                    const option = document.createElement('option');
                    option.value = camp.id;
                    option.textContent = `${camp.name} (${camp.start_date} 〜 ${camp.end_date})`;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('合宿リストの読み込みに失敗:', error);
            }
        }

        // PDFアップロード処理
        document.getElementById('pdfUploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const uploadBtn = document.getElementById('uploadBtn');
            const uploadProgress = document.getElementById('uploadProgress');
            const errorAlert = document.getElementById('errorAlert');

            // UI更新
            uploadBtn.disabled = true;
            uploadProgress.style.display = 'block';
            errorAlert.style.display = 'none';

            try {
                const response = await fetch('/pdf/upload', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // セッションに保存してレビュー画面へ
                    sessionStorage.setItem('pdfParsedData', JSON.stringify(result.data));
                    sessionStorage.setItem('campId', formData.get('camp_id'));
                    window.location.href = '/pdf/review';
                } else {
                    throw new Error(result.error || 'アップロードに失敗しました');
                }
            } catch (error) {
                errorAlert.style.display = 'block';
                document.getElementById('errorMessage').textContent = error.message;
            } finally {
                uploadBtn.disabled = false;
                uploadProgress.style.display = 'none';
            }
        });

        // ページ読み込み時に合宿リストを取得
        loadCamps();
    </script>
</body>
</html>
