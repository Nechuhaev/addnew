<?php

namespace App\Console\Commands\Concerns;

use App\DailyReport;

/**
 * Дає artisan-командам метод appendDailyReportStat() — додає (не
 * перезаписує) статистику в рядок daily_reports за СЬОГОДНІШНЮ дату.
 * "Додає", бо за один день команда може відпрацювати кілька разів
 * (наприклад, вручну для тесту, а потім ще й за розкладом) — лічильник
 * накопичується, а текстовий підсумок дописується через роздільник.
 */
trait UpdatesDailyReport
{
    protected function appendDailyReportStat(
        string $countField,
        int $increment,
        ?string $summaryField = null,
        ?string $summaryLine = null,
        string $summarySeparator = '; '
    ): void {
        $today = now()->toDateString();

        $report = DailyReport::firstOrCreate(['date' => $today]);

        $report->{$countField} = ($report->{$countField} ?? 0) + $increment;

        if ($summaryField && $summaryLine) {
            $existing = $report->{$summaryField};
            $report->{$summaryField} = $existing
                ? ($existing . $summarySeparator . $summaryLine)
                : $summaryLine;
        }

        $report->save();
    }
}
