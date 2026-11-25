<?php

namespace Tests\Unit;

use App\Services\Caller\CallSchedule;
use App\Services\Caller\CallerSettings;
use App\Services\Caller\OutcomeRules;
use App\Services\Caller\Util;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Модуль AI-дзвінків: чиста логіка без БД і мережі.
 * Запуск: php7.4 vendor/bin/phpunit --filter CallerTest
 */
class CallerTest extends TestCase
{
    private function rules(): OutcomeRules
    {
        return new OutcomeRules(new CallSchedule(10, 18, [1, 2, 3, 4, 5]), 3, 180);
    }

    private function analysis(array $p): array
    {
        return array_merge([
            'outcome' => 'not_interested', 'email' => '', 'email_uncertain' => false, 'contact_name' => '',
            'callback_at' => '', 'referred_contact' => '', 'has_merchant_feed' => 'unknown', 'objections' => [],
            'summary' => 's', 'next_action' => 'n', 'quality_flags' => [],
        ], $p);
    }

    public function testNormalizeUaPhone()
    {
        $this->assertSame('+380677654321', Util::normalizeUaPhone('067 765 43 21'));
        $this->assertSame('+380441234567', Util::normalizeUaPhone('(044) 123-45-67'));
        $this->assertSame('+380501112233', Util::normalizeUaPhone('+38 (050) 111-22-33'));
        $this->assertSame('+380501112233', Util::normalizeUaPhone('80501112233'));
        $this->assertSame('+380501112233', Util::normalizeUaPhone('00380501112233'));
        $this->assertNull(Util::normalizeUaPhone('+48 512 345 678'));
        $this->assertNull(Util::normalizeUaPhone('12345'));
    }

    public function testDomain()
    {
        $this->assertSame('shop.ua', Util::domain('https://www.Shop.ua/catalog?x=1'));
        $this->assertSame('shop.ua', Util::domain('shop.ua'));
        $this->assertSame('', Util::domain(null));
    }

    public function testEmailValidation()
    {
        $this->assertTrue(Util::isValidEmail('olena@kvitka.ua'));
        $this->assertTrue(Util::isValidEmail('o.lena+shop@mail.com.ua'));
        $this->assertFalse(Util::isValidEmail('olena собачка kvitka'));
        $this->assertFalse(Util::isValidEmail('olena@kvitka'));
    }

    public function testElevenLabsSignature()
    {
        $body = '{"type":"post_call_transcription"}';
        $t = time();
        $good = "t={$t},v0=" . hash_hmac('sha256', "{$t}.{$body}", 'sec');
        $this->assertTrue(Util::verifyElevenLabsSignature($body, $good, 'sec'));
        $this->assertFalse(Util::verifyElevenLabsSignature($body . ' ', $good, 'sec'));
        $this->assertFalse(Util::verifyElevenLabsSignature($body, $good, 'other'));
        $this->assertFalse(Util::verifyElevenLabsSignature($body, null, 'sec'));
        $this->assertFalse(Util::verifyElevenLabsSignature($body, $good, ''));
        $old = $t - 3600;
        $stale = "t={$old},v0=" . hash_hmac('sha256', "{$old}.{$body}", 'sec');
        $this->assertFalse(Util::verifyElevenLabsSignature($body, $stale, 'sec'));
    }

    public function testCallingHoursInKyiv()
    {
        $s = new CallSchedule(10, 18, [1, 2, 3, 4, 5]);
        // Ср 26.11.2026 08:30 UTC = 10:30 Київ (зима, UTC+2)
        $this->assertTrue($s->isCallingTime(Carbon::parse('2026-11-26 08:30:00', 'UTC')));
        $this->assertFalse($s->isCallingTime(Carbon::parse('2026-11-26 07:30:00', 'UTC')));  // 09:30
        $this->assertFalse($s->isCallingTime(Carbon::parse('2026-11-26 16:00:00', 'UTC')));  // 18:00
        $this->assertFalse($s->isCallingTime(Carbon::parse('2026-11-28 09:00:00', 'UTC')));  // субота
        // Літо, UTC+3: 07:30 UTC = 10:30 Київ
        $this->assertTrue($s->isCallingTime(Carbon::parse('2026-07-15 07:30:00', 'UTC')));

        // Пʼятниця 18:30 Київ → понеділок 10:00
        $next = $s->nextSlot(Carbon::parse('2026-11-27 16:30:00', 'UTC'))->setTimezone('Europe/Kiev');
        $this->assertSame('2026-11-30 10:00', $next->format('Y-m-d H:i'));

        // Субота дозволена — тоді наступний слот у суботу
        $sat = new CallSchedule(10, 18, [1, 2, 3, 4, 5, 6]);
        $this->assertSame('2026-11-28 10:00', $sat->nextSlot(Carbon::parse('2026-11-27 16:30:00', 'UTC'))->setTimezone('Europe/Kiev')->format('Y-m-d H:i'));
    }

    public function testOutcomeRules()
    {
        $r = $this->rules();
        $now = Carbon::parse('2026-11-26 09:00:00', 'UTC'); // чт 11:00 Київ

        $this->assertTrue($r->apply(1, $this->analysis(['outcome' => 'do_not_call']), $now)['dnc']);
        $this->assertSame('done', $r->apply(1, $this->analysis(['outcome' => 'agreed']), $now)['status']);

        $cb = $r->apply(1, $this->analysis(['outcome' => 'callback', 'callback_at' => '2026-11-27T15:00:00+02:00']), $now);
        $this->assertSame('callback', $cb['status']);
        $this->assertSame('2026-11-27 13:00', $cb['next_call_at']->copy()->setTimezone('UTC')->format('Y-m-d H:i'));

        // Невалідний час → завтра в той самий час
        $cb2 = $r->apply(1, $this->analysis(['outcome' => 'callback', 'callback_at' => 'колись']), $now);
        $this->assertSame('2026-11-27 09:00', $cb2['next_call_at']->copy()->setTimezone('UTC')->format('Y-m-d H:i'));

        // Ліміт передзвонів
        $this->assertSame('done', $r->apply(5, $this->analysis(['outcome' => 'callback']), $now)['status']);

        // Недодзвін: +3 год, потім +6 год, після 3 спроб — failed
        $this->assertSame('2026-11-26 12:00', $r->notReached(1, $now)['next_call_at']->copy()->setTimezone('UTC')->format('Y-m-d H:i'));
        $this->assertSame('2026-11-26 15:00', $r->notReached(2, $now)['next_call_at']->copy()->setTimezone('UTC')->format('Y-m-d H:i'));
        $this->assertSame('failed', $r->notReached(3, $now)['status']);
        // О 16:00 Київ +3 год = 19:00 → ранок наступного дня
        $late = $r->notReached(1, Carbon::parse('2026-11-26 14:00:00', 'UTC'));
        $this->assertSame('2026-11-27 08:00', $late['next_call_at']->copy()->setTimezone('UTC')->format('Y-m-d H:i'));
    }

    public function testSettingsCasting()
    {
        $this->assertSame([1, 3, 5], CallerSettings::cast('days', '5,3,1,9,3'));
        $this->assertTrue(CallerSettings::cast('bool', '1'));
        $this->assertFalse(CallerSettings::cast('bool', '0'));
        $this->assertSame(42, CallerSettings::cast('int', '42'));
    }

    public function testInstructionEmailRendersBothVariants()
    {
        $withFeed = (new \App\Mail\CallerInstruction('Олена', 'Квітка', 'yes', ['email_reply_to' => 'r@a.ua']))->render();
        $this->assertStringContainsString('Добрий день, Олена!', $withFeed);
        $this->assertStringContainsString('посилання на ваш фід', $withFeed);
        $this->assertStringNotContainsString('надішліть таблицю', $withFeed);

        $noFeed = (new \App\Mail\CallerInstruction('', '<b>X</b>', 'no', ['email_reply_to' => 'r@a.ua']))->render();
        $this->assertStringContainsString('Добрий день!', $noFeed);
        $this->assertStringContainsString('надішліть таблицю', $noFeed);
        $this->assertStringNotContainsString('<b>X</b>', $noFeed); // назва магазину екранується
    }
}
