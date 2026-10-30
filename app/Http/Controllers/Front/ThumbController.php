<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\Thumb;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * /thumbs/{w}/s3/{path} — зменшена копія фото з нашого S3 (App\Support\Thumb).
 * Створює public/thumbs/{w}/s3/{path}; наступні запити вебсервер віддає як статичний файл.
 * Якщо копію зробити не вдалось — тимчасовий редирект на оригінал; невдача пам'ятається на добу.
 */
class ThumbController extends Controller
{
    const MAX_BYTES = 15728640;     // 15 МБ
    const MAX_PIXELS = 40000000;    // 40 Мп — захист від «бомб» у пам'яті
    const QUALITY = 80;

    public function show(int $w, string $path)
    {
        if (!in_array($w, Thumb::WIDTHS, true) || strpos($path, '..') !== false
            || !preg_match('~^[A-Za-z0-9._\-/]+\.jpe?g$~i', $path)) {
            abort(404);
        }

        $source = Thumb::sourceUrl($path);
        // Шлях має відповідати тому, що генерує Thumb::url() — інакше 404 (не плодимо копій)
        if (Thumb::url($source, $w) !== '/thumbs/' . $w . '/s3/' . $path) {
            abort(404);
        }

        // Невдача запам'ятовується на добу: недоступне фото не смикає S3 при кожному показі
        $failKey = 'thumb_fail:' . md5($path);
        if (Cache::has($failKey)) {
            return redirect()->away($source, 302, ['Cache-Control' => 'no-store']);
        }

        try {
            $target = public_path('thumbs/' . $w . '/s3/' . $path);
            $this->make($source, $target, $w);
        } catch (\Throwable $e) {
            Cache::put($failKey, 1, now()->addDay());
            Log::warning('Thumb failed', ['src' => $source, 'w' => $w, 'error' => $e->getMessage()]);

            return redirect()->away($source, 302, ['Cache-Control' => 'no-store']);
        }

        return response()->file($target, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=2592000',
        ]);
    }

    protected function make(string $source, string $target, int $w): void
    {
        $response = (new Client(['timeout' => 15, 'connect_timeout' => 5, 'http_errors' => false]))->get($source);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('source HTTP ' . $response->getStatusCode());
        }
        $body = (string) $response->getBody();
        if ($body === '' || strlen($body) > self::MAX_BYTES) {
            throw new \RuntimeException('source size ' . strlen($body));
        }

        $info = @getimagesizefromstring($body);
        if (!$info || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > self::MAX_PIXELS) {
            throw new \RuntimeException('not an image or too large');
        }
        [$srcW, $srcH] = $info;

        @ini_set('memory_limit', '512M');
        $img = @imagecreatefromstring($body);
        if (!$img) {
            throw new \RuntimeException('decode failed');
        }

        $dstW = min($w, $srcW);
        $dstH = max(1, (int) round($srcH * $dstW / $srcW));
        $out = imagecreatetruecolor($dstW, $dstH);
        // Прозорість (PNG/GIF/WebP) — на білому тлі, як на картках сайту
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
        imagecopyresampled($out, $img, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagedestroy($img);
        imageinterlace($out, true);

        $dir = dirname($target);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            imagedestroy($out);
            throw new \RuntimeException('mkdir failed');
        }
        // Пишемо в тимчасовий файл і перейменовуємо — паралельний запит не отримає половину файлу
        $tmp = $target . '.' . getmypid() . '.tmp';
        $ok = imagejpeg($out, $tmp, self::QUALITY);
        imagedestroy($out);
        if (!$ok || !@rename($tmp, $target)) {
            @unlink($tmp);
            throw new \RuntimeException('write failed');
        }
    }
}
