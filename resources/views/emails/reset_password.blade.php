<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワードリセット</title>
</head>
<body>
    {{--  パスワードリセットの手続き  --}}
    <h1>パスワードリセットのご案内</h1>
    <p>以下のリンクをクリックしてパスワードをリセットしてください:</p>
    <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
    <p>このメールに心当たりがない場合は、何も行わないでください。</p>
    <p>よろしくお願いいたします。</p>
</body>
</html>
