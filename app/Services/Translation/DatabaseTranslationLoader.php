<?php

namespace App\Services\Translation;

use App\Translation;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Translation\FileLoader;

class DatabaseTranslationLoader implements Loader
{
    protected $fileLoader;

    public function __construct(FileLoader $fileLoader)
    {
        $this->fileLoader = $fileLoader;
    }

    /**
     * @param string $locale
     * @param string $group напр. "front", "validation", "*"
     * @param string|null $namespace
     * @return array
     */
    public function load($locale, $group, $namespace = null)
    {
        // Валідацію та інші стандартні системні групи Laravel лишаємо
        // файловими — БД використовуємо тільки для наших власних груп
        // (front, blog, shop, admin тощо), щоб не зачепити системні
        // повідомлення (validation.php, auth.php).
        $fileTranslations = $this->fileLoader->load($locale, $group, $namespace);

        if ($namespace && $namespace !== '*') {
            return $fileTranslations;
        }

        $dbTranslations = Translation::getGroup($group, $locale);

        // Значення з БД мають пріоритет над файловими (якщо є) —
        // дозволяє тримати частину перекладів у файлах "про запас",
        // а адмін через БД перекриває/доповнює конкретні ключі.
        return array_merge($fileTranslations, $dbTranslations);
    }

    public function addNamespace($namespace, $hint)
    {
        $this->fileLoader->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->fileLoader->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->fileLoader->namespaces();
    }
}