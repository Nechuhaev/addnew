<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <p>
            <strong>{{ $senderName }}</strong> написав(ла) вам повідомлення@if($adTitle) щодо оголошення «{{ $adTitle }}»@endif:
        </p>

        <blockquote style="margin: 16px 0; padding: 12px 16px; background: #f5f5f5; border-left: 4px solid #28a745; border-radius: 4px;">
            {{ $preview }}
        </blockquote>

        <p>
            <a href="{{ $conversationUrl }}"
               style="display: inline-block; background: #28a745; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold;">
                Відповісти в чаті
            </a>
        </p>

        <hr style="margin-top: 30px; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #999;">
            Ви отримали цей лист, бо вам написали на дошці оголошень addnew.biz.
            Поки ви не прочитаєте діалог, нові повідомлення в ньому не дублюватимуться листами частіше ніж раз на 30 хвилин.
        </p>
    </div>
</body>
</html>
