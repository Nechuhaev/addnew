<?php

use App\Ad;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Імпорт товарів створював оголошення без слагу (поле slug не передавалось у Ad::create,
 * тож мутатор Ad::setSlugAttribute не спрацьовував) — посилання вели на /ads/.
 * Генеруємо слаги тим самим мутатором; пишемо через DB, щоб не чіпати updated_at і хуки.
 */
class FillEmptyAdSlugs extends Migration
{
    public function up()
    {
        Ad::where(function ($q) {
            $q->whereNull('slug')->orWhere('slug', '');
        })
            ->select('id', 'name', 'slug')
            ->chunkById(200, function ($ads) {
                foreach ($ads as $ad) {
                    $ad->slug = null;
                    $slug = (string) ($ad->getAttributes()['slug'] ?? '');
                    if ($slug === '') {
                        $slug = 'ad-' . $ad->id;
                    }
                    DB::table('ads')->where('id', $ad->id)->update(['slug' => $slug]);
                }
            });
    }

    public function down()
    {
        // Згенеровані слаги лишаються: порожній слаг — це зламане посилання
    }
}
