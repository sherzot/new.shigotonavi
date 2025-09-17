<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>【しごとナビ求人票】のオファーが完了しました</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px; color: #333; line-height: 1.6; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #e0e0e0; padding: 20px;">
        <div style="text-align: right; font-size: 13px; color: #999;">{{ now()->format('Y年n月j日 H:i') }}</div>

        <div style="margin: 20px 0;">
            <img src="{{ asset('img/shigotonavi-logoA.svg') }}" alt="しごとナビ" style="height: 50px;">
        </div>

        <h2 style="font-size: 18px; color: #333; border-left: 5px solid #e60033; padding-left: 10px;">
            【しごとナビ求人票】のオファーが完了しました
        </h2>

        <p>
            {{ $userName }} さん<br><br>
            この度は、オファー申し込みありがとうございます。<br>
            専任の担当エージェントがあなたの就職活動をサポートさせていただきます。
        </p>

        {{--  <p>
            <strong style="color: #005bac;">お申し込み求人票：</strong><br>
            <a href="{{ $jobUrl }}" style="color: #005bac; text-decoration: underline;">{{ $jobDetail }}</a>
        </p>  --}}
        <p>
            <strong style="color: #005bac;">お申し込み求人票：</strong><br>
            <span style="font-weight: bold;">【{{ $orderCode }}】</span>
            <a href="{{ $jobUrl }}" style="color: #005bac; text-decoration: underline;">
                {{ $jobDetail }}
            </a>
        </p>        

        <p style="color: #888; font-size: 13px;">
            このメールに心当たりがない場合は、誠に恐れ入りますが、破棄していただきますようお願い申し上げます。<br>
            このメールは自動配信専用のアドレスから送信しています。<br>
            このメールに返信されても、返答内容のご対応ができませんので、あらかじめご了承ください。
        </p>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #ccc;">

        <p style="font-size: 13px;">
            —— しごとナビ ————<br>
            全国の正社員、派遣、アルバイトの求人情報が満載！<br>
            <a href="https://mch.shigotonavi.co.jp/" style="color: #005bac;">https://mch.shigotonavi.co.jp/</a><br><br>
            リス株式会社
        </p>
    </div>
</body>
</html>
