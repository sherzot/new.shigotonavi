<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>お問い合わせ</title>
        <!-- Bootstrap CDN -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            .main-theme-color {
                background-color: #FF6347 !important; /* マイページ va logotip rangi */
                color: white !important;
            }
        
            .text-main-theme {
                color: #FF6347 !important; /* Matn uchun asosiy rang */
            }
            th{
                text-align: left;
            }
        
        </style>
    </head>

    <body>
        <div class="container mt-4">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white text-start">
                    <h3 class="mb-0">{{ $data['form_type'] }} からのお問い合わせ</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered text-start">
                        <tbody>
                            <tr>
                                <th class="bg-light w-25 text-start">件名</th>
                                <td>{{ $data['subject'] ?? '未記入' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-start">お名前</th>
                                <td>{{ $data['name'] ?? '未記入' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-start">メールアドレス</th>
                                <td><a href="mailto:{{ $data['email'] }}">{{ $data['email'] ?? '未記入' }}</a></td>
                            </tr>
                            @if ($data['form_type'] == '求人企業お問い合わせ')
                                <tr>
                                    <th class="bg-light text-start">会社名</th>
                                    <td>{{ $data['company'] ?? '未記入' }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th class="bg-light text-start">勤務地</th>
                                <td>{{ $data['prefecture_code'] ?? '未記入' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-start">電話番号</th>
                                <td>{{ $data['phone'] ?? '未記入' }}</td>
                            </tr>
                            @if ($data['form_type'] == '求人企業お問い合わせ')
                                <tr>
                                    <th class="bg-light text-start">人材採用に関するお悩み</th>
                                    <td>{{ $data['trouble'] ?? '未記入' }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th class="bg-light text-start">メッセージ</th>
                                <td>{{ $data['message'] ?? '未記入' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- アンケート結果 (求人企業お問い合わせ のみ) -->
                    @if ($data['form_type'] == '求人企業お問い合わせ')
                        <div class="text-main-theme">
                            <h5><strong>アンケート結果:</strong></h5>
                            <p>{!! $data['survey_q1'] ?? '未記入' !!}</p>
                            <p> {!! $data['survey_q2'] ?? '' !!}</p>
                        </div>
                    @endif

                    <!-- 送信元ブラウザ -->
                    <p class="text-muted"><strong>送信元ブラウザ:</strong> {{ $data['browser'] }}</p>
                </div>
            </div>
        </div>
    </body>

</html>
