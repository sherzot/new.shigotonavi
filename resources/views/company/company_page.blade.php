<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h5>御社ページ</h5>
    @if(Auth::check())
        @if(isset(Auth::user()->company_code))
            <h5>ID: {{ Auth::user()->company_code }}</h5>
        @else
            <h5>company_code が存在しません! 新規登録するかログインし直してください。</h5>
        @endif
    @else
        <h5>あなたはログインしていません!</h5>
    @endif
</body>

</html>
