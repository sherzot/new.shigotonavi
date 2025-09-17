<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール設定</title>
</head>
<body>
    <h1>メール設定</h1>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

        <table>
        <thead>
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mailConfig as $key => $value)
                <tr>
                    <td>{{ $key }}</td>
                    <td>{{ $value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <a href="{{ route('mail.config.refresh') }}"
        style="display: inline-block; padding: 10px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px;">
        構成を更新する
    </a>

</body>
</html>
