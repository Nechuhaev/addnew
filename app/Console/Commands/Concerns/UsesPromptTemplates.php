<?php

namespace App\Console\Commands\Concerns;

use App\PromptTemplate;

/**
 * Дає artisan-командам метод prompt() — повертає текст промпту з БД
 * (адмінка "Промпти"), якщо він там уже є, інакше використовує
 * $default і одразу зберігає його в БД, щоб промпт з'явився в
 * адмінці для редагування вже після першого ж запуску команди.
 *
 * Плейсхолдери в тексті промпту — у форматі {{ключ}}, підставляються
 * зі значень масиву $vars. Якщо адмін відредагує текст в адмінці,
 * АЛЕ прибере якийсь плейсхолдер — команда просто підставить менше
 * даних, це не зламає роботу (str_replace на відсутній ключ — no-op).
 */
trait UsesPromptTemplates
{
    protected function prompt(string $key, string $label, string $default, array $vars = [], ?string $description = null): string
    {
        $record = PromptTemplate::firstOrCreate(
            ['key' => $key],
            [
                'command_class' => static::class,
                'label' => $label,
                'description' => $description,
                'default_template' => $default,
                'template' => $default,
            ]
        );

        $text = ($record->template !== null && $record->template !== '')
            ? $record->template
            : $default;

        foreach ($vars as $name => $value) {
            $text = str_replace('{{' . $name . '}}', (string) $value, $text);
        }

        return $text;
    }
}
