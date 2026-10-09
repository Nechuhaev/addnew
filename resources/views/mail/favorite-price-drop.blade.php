<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.5;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <p style="font-size: 16px;">Добрий день! Товари з вашого обраного на addnew.biz подешевшали:</p>

        <table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            @foreach($drops as $d)
                <tr>
                    <td style="padding: 12px 12px 12px 0; border-bottom: 1px solid #eee; width: 80px; vertical-align: top;">
                        @if(!empty($d['image']))
                            <a href="{{ $d['url'] }}"><img src="{{ $d['image'] }}" alt="" width="80" style="display: block; border: 0; max-width: 80px;"></a>
                        @endif
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #eee; vertical-align: top;">
                        <a href="{{ $d['url'] }}" style="color: #1a73e8; font-weight: bold; text-decoration: none;">{{ $d['name'] }}</a>
                        @if(!empty($d['seller']))
                            <div style="font-size: 13px; color: #777;">{{ $d['seller'] }}</div>
                        @endif
                        <div style="margin-top: 6px;">
                            <span style="font-size: 18px; font-weight: bold; color: #28a745;">{{ number_format($d['new'], 0, '.', ' ') }} грн</span>
                            <span style="text-decoration: line-through; color: #999; margin-left: 8px;">{{ number_format($d['old'], 0, '.', ' ') }} грн</span>
                            <span style="background: #e8f5e9; color: #2e7d32; border-radius: 4px; padding: 1px 6px; font-size: 13px; margin-left: 6px;">−{{ rtrim(rtrim(number_format($d['percent'], 1, '.', ''), '0'), '.') }}%</span>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>

        <p style="margin-top: 20px;">
            <a href="{{ route('profile.favorites') }}"
               style="display: inline-block; background: #28a745; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold;">
                Переглянути обране
            </a>
        </p>

        <hr style="margin-top: 30px; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #999;">
            Ви отримали цей лист, бо додали товари в обране на addnew.biz і ввімкнули сповіщення про зниження ціни.
            <a href="{{ $unsubscribeUrl }}" style="color: #999;">Відписатися від цих листів</a>.
        </p>
    </div>
</body>
</html>
