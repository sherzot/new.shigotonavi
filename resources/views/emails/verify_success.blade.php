<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>確認メール</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
            }

            .container {
                max-width: 700px;
                width: 100%;
                height: 100%;
                margin: 0 auto;
                padding: 20px;
                background-color: #f9f9f9;
                border: 1px solid #ddd;
            }

            .header {
                font-size: 24px;
                font-weight: bold;
                text-align: left;
                margin-bottom: 10px;
            }

            .sender-info {
                font-size: 14px;
                color: #666;
                margin-bottom: 20px;
            }

            .content {
                font-size: 16px;
                margin-bottom: 20px;
            }

            .content p {
                margin: 0 0 10px;
            }

            .content a {
                color: #0066cc;
                text-decoration: none;
            }

            .content strong {
                color: #ea544a;
            }

            .footer {
                font-size: 12px;
                color: #999;
                border-top: 1px solid #ddd;
                padding-top: 10px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <img src="{{ asset('img/logo02.png') }}" alt="logo" class="w-sm-25 w-50 mx-auto">
            <div class="sender-info">発信元: <a href="https://www.shigotonavi.co.jp/">shigotonabi.co.jp</a></div>

            <div class="content">
                <p>しごとナビ会員登録手続きのお知らせ</p>
                <p>このたびは、しごとナビ会員登録をお申し込みいただきまして、誠にありがとうございます。</p>

                <p style="color: #508bfc"><strong>【ご注意】</strong><br>
                    ログインする際には、この <strong>ID</strong> と <strong>パスワード</strong>を使用してください。</p>
                <hr class="my-4 color-red">
                <div class="notification">
                    <p>会員登録が完了しました。以下の <strong>ID</strong> を使ってログインしてください:</p>
                    <p>メール: {{ $person->mail_address }}</p>
                    <p>ID: {{ $person->staff_code }}</p>
                    <p>パスワード: 入力したパスワードを使用してください。</p>
                </div>
                <div class="notification">
                    <p><strong>メールアドレス</strong>または <strong>ID</strong> とパスワードでログイン後、、ご希望の条件に合った求人を選び、「オファー」ボタンを押してください。</p>
                </div>
            </div>

            <div class="footer">
                このメールに心当たりがない場合は、誠に恐れ入りますが、破棄していただきますようお願い申し上げます。<br><br>
                このメールは自動配信専用のアドレスから送信しています。<br>
                このメールに返信されても、返答内容のご対応ができませんので、あらかじめご了承ください。
            </div>
        </div>
    </body>

</html>
