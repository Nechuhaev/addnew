<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ModerateAds extends Command
{
    protected $signature = 'ads:moderate {--apply : Застосувати (без цього — лише перегляд)} {--no-block : Не блокувати авторів} {--force-block= : ID авторів через кому, яких блокувати попри ratio-запобіжник} {--rollback= : Шлях до backup-файлу для відкату}';
    protected $description = 'Деактивація заборонених оголошень (наркотики, ескорт/інтим, акаунти, документи, категорія знайомств) + блок авторів';

    const PROTECTED_USER_IDS = [2];

    public function handle()
    {
        if ($this->option('rollback')) {
            return $this->rollback($this->option('rollback'));
        }

        $rules = $this->rules();
        $cand = [];
        $checked = 0;

        DB::table('ads')->where('status', 1)->select('id', 'user_id', 'total_views', 'name')->orderBy('id')
            ->chunk(3000, function ($rows) use ($rules, &$cand, &$checked) {
                foreach ($rows as $r) {
                    $checked++;
                    $t = mb_strtolower($r->name);
                    foreach ($rules as $g => $p) {
                        if (preg_match($p[0], $t) && !preg_match($p[1], $t)) {
                            $cand[$r->id] = ['g' => $g, 'u' => $r->user_id, 'n' => $r->name, 'v' => $r->total_views, 'rule' => true];
                            break;
                        }
                    }
                }
            });

        $catAds = 0;
        $catId = (int) DB::table('ad_categories')->where('slug', 'znakomstva-i-kontaktyi')->value('id');
        if ($catId) {
            $ids = [$catId]; $fr = [$catId];
            while ($fr) {
                $fr = array_values(array_diff(DB::table('ad_categories')->whereIn('parent_id', $fr)->pluck('id')->all(), $ids));
                $ids = array_merge($ids, $fr);
            }
            foreach (DB::table('ads')->whereIn('category_id', $ids)->where('status', 1)->get(['id', 'user_id', 'total_views', 'name']) as $r) {
                if (!isset($cand[$r->id])) {
                    $cand[$r->id] = ['g' => 'категорія «Знайомства і контакти»', 'u' => $r->user_id, 'n' => $r->name, 'v' => $r->total_views, 'rule' => false];
                    $catAds++;
                }
            }
        }

        $this->info("Перевірено активних: {$checked}; до деактивації: " . count($cand) . " (з них з категорії знайомств: {$catAds})");

        $byGroup = [];
        foreach ($cand as $id => $c) { $byGroup[$c['g']][] = $id; }
        foreach ($byGroup as $g => $list) {
            usort($list, function ($a, $b) use ($cand) { return $cand[$b]['v'] <=> $cand[$a]['v']; });
            $this->line("\n=== {$g}: " . count($list) . ' ===');
            foreach (array_slice($list, 0, 8) as $id) {
                $this->line("  #{$id} u{$cand[$id]['u']} | " . mb_substr($cand[$id]['n'], 0, 70));
            }
        }

        $authors = [];
        foreach ($cand as $c) { if ($c['rule']) { $authors[$c['u']] = ($authors[$c['u']] ?? 0) + 1; } }
        $users = $authors ? DB::table('users')->whereIn('id', array_keys($authors))->get(['id', 'email', 'is_admin', 'is_shop_owner'])->keyBy('id') : collect();
        $toBlock = []; $skipped = [];
        foreach ($authors as $uid => $cnt) {
            $u = $users->get($uid);
            if (!$u || !$u->email) { $skipped[] = "u{$uid}: немає email"; continue; }
            if ($u->is_admin || $u->is_shop_owner || in_array($uid, self::PROTECTED_USER_IDS)) {
                $skipped[] = "u{$uid} {$u->email}: " . ($u->is_admin ? 'АДМІН ' : '') . ($u->is_shop_owner ? 'МАГАЗИН ' : '') . (in_array($uid, self::PROTECTED_USER_IDS) ? 'захищений' : '') . " — НЕ блокується";
                continue;
            }
            $act = DB::table('ads')->where('user_id', $uid)->where('status', 1)->count();
            if ($act > 3 && $cnt / $act < 0.5 && !in_array((string) $uid, array_map('trim', explode(',', (string) $this->option('force-block'))), true)) {
                $names = [];
                foreach ($cand as $cid => $cc) { if ($cc['u'] == $uid && $cc['rule']) { $names[] = '#' . $cid . ' ' . mb_substr($cc['n'], 0, 60); } }
                $skipped[] = "u{$uid} {$u->email}: {$cnt} з {$act} активних — схоже на звичайного продавця, НЕ блокується; спіймано: " . implode(' | ', $names);
                continue;
            }
            $toBlock[$uid] = strtolower(trim($u->email));
        }
        $this->line("\n=== автори до блокування (blocked_emails): " . count($toBlock) . ' ===');
        foreach ($toBlock as $uid => $em) {
            $active = DB::table('ads')->where('user_id', $uid)->where('status', 1)->count();
            $this->line("  u{$uid} {$em} | кандидатів {$authors[$uid]} / активних {$active}");
        }
        if ($skipped) {
            $this->warn("\nПропущені (не блокуються, оголошення все одно деактивуються):");
            foreach ($skipped as $s) { $this->line('  ' . $s); }
        }

        if (!$this->option('apply')) {
            $this->warn("\nЦе ПЕРЕГЛЯД. Нічого не змінено. Для застосування: ads:moderate --apply");
            return 0;
        }

        $stamp = date('Ymd_His');
        $adsFile = storage_path("app/moderation_{$stamp}_ads.csv");
        $fh = fopen($adsFile, 'w');
        fputcsv($fh, ['id', 'prev_status', 'group']);
        foreach ($cand as $id => $c) { fputcsv($fh, [$id, 1, $c['g']]); }
        fclose($fh);

        $n = 0;
        foreach (array_chunk(array_keys($cand), 500) as $chunk) {
            $n += DB::table('ads')->whereIn('id', $chunk)->where('status', 1)->update(['status' => 2]);
        }

        $blocked = [];
        if (!$this->option('no-block')) {
            foreach ($toBlock as $em) {
                if (!DB::table('blocked_emails')->where('mailbox', $em)->exists()) {
                    $blocked[] = DB::table('blocked_emails')->insertGetId(['mailbox' => $em]);
                }
            }
        }
        $blkFile = storage_path("app/moderation_{$stamp}_blocked.csv");
        $fh = fopen($blkFile, 'w'); fputcsv($fh, ['id']);
        foreach ($blocked as $b) { fputcsv($fh, [$b]); }
        fclose($fh);

        $this->info("\nДеактивовано (status=2): {$n}; додано до blocked_emails: " . count($blocked));
        $this->line("Відкат: php7.4 artisan ads:moderate --rollback={$adsFile}");
        return 0;
    }

    protected function rollback($file)
    {
        if (!is_file($file)) { $this->error('Файл не знайдено'); return 1; }
        $fh = fopen($file, 'r'); fgetcsv($fh);
        $ids = [];
        while (($row = fgetcsv($fh)) !== false) { if (isset($row[0]) && ctype_digit($row[0])) { $ids[] = (int) $row[0]; } }
        fclose($fh);
        $n = 0;
        foreach (array_chunk($ids, 500) as $chunk) {
            $n += DB::table('ads')->whereIn('id', $chunk)->where('status', 2)->update(['status' => 1]);
        }
        $blk = str_replace('_ads.csv', '_blocked.csv', $file);
        $b = 0;
        if (is_file($blk)) {
            $fh = fopen($blk, 'r'); fgetcsv($fh);
            while (($row = fgetcsv($fh)) !== false) { if (isset($row[0]) && ctype_digit($row[0])) { $b += DB::table('blocked_emails')->where('id', (int) $row[0])->delete(); } }
            fclose($fh);
        }
        $this->info("Відновлено оголошень: {$n}; видалено блокувань: {$b}");
        return 0;
    }

    protected function rules()
    {
        return [
            'наркотики/анаболіки/рецептурні' => [
                '/(?<!\p{L})(стероид|туринабол|оксандролон|нандролон|оксиметолон|метилтестостерон|тренболон|станозолол|анаболик|p2np|нитропропен|нітропропен|амфетамин|амфетамін|мефедрон|кокаин|кокаїн|героин|героїн|гашиш|трамадол|трамал|золпідем|золпидем|ксанакс|нембутал|nembutal|пентобарбітал|пентобарбитал|ціанід калію|цианид калия|mdma|мдма|mdpv|4-mec|ібогаїн|ибогаин|фентаніл|фентанил|альфа[ -]?пвп|кладмен|кладоотправ|кладыотправ|надежные клад|caluanie|crystal meth)/iu',
                '/(носк|футболк|принт|стикер|кружк|матрас|потолк|профил|кондиционер)/iu'],
            'ескорт/інтим-послуги' => [
                '/(?<!\p{L})(эскорт|ескорт|интим[ -]?услуг|інтим[ -]?послуг|проститут|секс[ -]?знаком|секс[ -]?услуг|секс[ -]?послуг|стриптиз|релакс[ -]?масс?аж|релакс[ -]?масаж|эротическ\p{L}* масс?аж|еротичн\p{L}* масаж|тантр\p{L}* масс?аж|лингам|массаж простат|индивидуалк|вип[ -]?девуш|девушк[аиу] (по )?вызов|досуг для мужчин)/iu',
                '/(белье|білизн|костюм|игрушк|іграшк|магазин|купальник|одежд|одяг)/iu'],
            'продаж акаунтів' => [
                '/((?<!\p{L})(аккаунт|акаунт)\p{L}*[^\n]{0,30}(продаж|продам|продаю|куп|раздач|монетизир))|((продаж|продам|продаю|куп|монетизир)\p{L}*[^\n]{0,30}(?<!\p{L})(аккаунт|акаунт))|^(аккаунт|акаунт)ы? /iu',
                '/(реклам|продвижен|smm|настройк|ведение)/iu'],
            'документи' => [
                '/((?<!\p{L})(паспорт|загранпаспорт|id[ -]?карт|водительск\p{L}* удостоверен|водійськ\p{L}* посвідчен|техпаспорт|автодокумент\p{L}*|диплом|справк[аиу]|права (любых|на авто))[^\n]{0,60}(куп|оформ|срочно|без |любых категор|быстро|гарант))|(паспорт[^\n]{0,20}(ес|евро)[^\n]{0,30}(id|водител))|(?<!\p{L})(автодокумент|водительские права)/iu',
                '/(обложк|чехол|автошкол|спецтехник|обучен|курсы|нотариус|юрист|апостил|легализац|страхов|перевод|ксерокс|сканер|внж)/iu'],
        ];
    }
}
