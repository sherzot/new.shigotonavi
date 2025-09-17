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
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .content p {
            margin: 0 0 10px;
        }
        .content a {
            color: #0066cc;
            text-decoration: none;
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
        <div class="header">しごとナビ会員登録手続きのお知らせ</div>

        <div class="content">
            <p>このたびは、しごとナビ会員登録をお申し込みいただきまして、誠にありがとうございます。</p>
            <p>以下のリンクをクリックして登録を完了してください:</p>
            <p>
                <a href="{{ $verificationUrl }}">会員登録を完了する</a>
            </p>
            {{--  <p>リンクの有効期限は {{ $expirationDate }} までです。</p>  --}}
            <p>もしリンクが機能しない場合は、以下のURLをコピーしてブラウザのアドレスバーに貼り付けてください:</p>
            <p>{{ $verificationUrl }}</p>
        </div>
        <div class="footer">
            このメールは自動配信されました。返信しないでください。
        </div>
    </div>
</body>
</html>
