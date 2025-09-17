<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせ</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white text-center">
                <h3 class="mb-0">{{ $data['form_type'] }} からのお問い合わせ</h3>
            </div>
            <div class="card-body">

                <!-- 件名 -->
                <div class="mb-3">
                    <h5><strong>件名:</strong> {{ $data['subject'] ?? '未記入' }}</h5>
                </div>

                <!-- ユーザー情報 -->
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>お名前:</strong> {{ $data['name'] ?? '未記入' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>メールアドレス:</strong> <a href="mailto:{{ $data['email'] }}">{{ $data['email'] ?? '未記入' }}</a></p>
                    </div>
                </div>

                <!-- 会社名 (求人企業お問い合わせ のみ) -->
                @if ($data['form_type'] == '求人企業お問い合わせ')
                    <div class="mb-3">
                        <p><strong>会社名:</strong> {{ $data['company'] ?? '未記入' }}</p>
                    </div>
                @endif

                <!-- 勤務地 & 電話番号 -->
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>勤務地:</strong> {{ $data['prefecture_code'] ?? '未記入' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>電話番号:</strong> {{ $data['phone'] ?? '未記入' }}</p>
                    </div>
                </div>

                <!-- 人材採用に関するお悩み (求人企業お問い合わせ のみ) -->
                @if ($data['form_type'] == '求人企業お問い合わせ')
                    <div class="mb-3">
                        <p><strong>人材採用に関するお悩み:</strong> {{ $data['trouble'] ?? '未記入' }}</p>
                    </div>
                @endif

                <!-- メッセージ -->
                <div class="mb-3">
                    <h5 class="text-dark"><strong>メッセージ:</strong></h5>
                    <p class="border rounded p-3 bg-light">{{ $data['message'] ?? '未記入' }}</p>
                </div>

                <!-- アンケート結果 (求人企業お問い合わせ のみ) -->
                @if ($data['form_type'] == '求人企業お問い合わせ')
                    <div class="alert alert-danger">
                        <h5><strong>アンケート結果:</strong></h5>
                        <p><strong>Q1:</strong> {{ $data['survey_q1'] ?? '未記入' }}</p>
                        @if (!empty($data['survey_q2']))
                            <p><strong>Q2:</strong> {{ $data['survey_q2'] }}</p>
                        @endif
                    </div>
                @endif

                <!-- 送信元ブラウザ -->
                <p><strong>送信元ブラウザ:</strong> {{ $data['browser'] }}</p>
            </div>
        </div>
    </div>
</body>
</html>
