<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Пошук оголошень: FULLTEXT (назва, опис, бренд) у BOOLEAN MODE з префіксами
 * й легким відсіканням закінчень (uk/ru), короткі слова — через LIKE,
 * точний збіг артикулу/штрихкоду. Без FULLTEXT-індексу — запасний LIKE.
 */
class AdSearch
{
    const MATCH = 'MATCH(ads.name, ads.content, ads.brand)';
    const MAX_TOKENS = 8;

    /** Закінчення, які відкидаємо, щоб «ноутбуки» знаходили «ноутбук» (довші — першими) */
    const ENDINGS = [
        'ами', 'ями', 'ові', 'еві', 'ого', 'ому', 'ими', 'ыми', 'ний', 'ная', 'ное', 'ные',
        'ів', 'ов', 'ев', 'ей', 'ий', 'ій', 'ый', 'ой', 'ая', 'яя', 'ое', 'ее', 'ые', 'ие', 'ої', 'ам', 'ям', 'ах', 'ях', 'ом', 'ем',
        'а', 'я', 'и', 'і', 'ї', 'ы', 'у', 'ю', 'е', 'є', 'о', 'ь', 'й',
    ];

    /** @var string */
    public $raw;
    /** @var string[] */
    public $tokens;

    public function __construct(string $query)
    {
        $this->raw = trim(preg_replace('/\s+/u', ' ', $query));
        $this->tokens = static::tokenize($this->raw);
    }

    public static function tokenize(string $q): array
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($q), -1, PREG_SPLIT_NO_EMPTY);
        return array_slice(array_values(array_unique($parts)), 0, self::MAX_TOKENS);
    }

    public function isEmpty(): bool
    {
        return !$this->tokens;
    }

    /** Мінімальна довжина слова, яку індексує FULLTEXT цього сервера */
    public static function minTokenLength(): int
    {
        return Cache::remember('search:ft_min_len', 86400, function () {
            try {
                $engine = DB::selectOne("SELECT ENGINE AS e FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ads'");
                $var = ($engine && strtolower($engine->e) === 'innodb') ? 'innodb_ft_min_token_size' : 'ft_min_word_len';
                $row = DB::selectOne('SHOW VARIABLES LIKE ?', [$var]);
                return $row ? max(1, (int) $row->Value) : 4;
            } catch (\Throwable $e) {
                return 4;
            }
        });
    }

    public static function hasFulltext(): bool
    {
        return Cache::remember('search:has_fulltext', 3600, function () {
            try {
                return (bool) DB::select("SHOW INDEX FROM ads WHERE Key_name = 'ads_fulltext'");
            } catch (\Throwable $e) {
                return false;
            }
        });
    }

    /** Основа слова для префіксного пошуку */
    public static function stem(string $token): string
    {
        if (!preg_match('/\p{Cyrillic}/u', $token) || mb_strlen($token) < 6) {
            return $token;
        }
        foreach (self::ENDINGS as $end) {
            $len = mb_strlen($end);
            if (mb_substr($token, -$len) === $end && mb_strlen($token) - $len >= 4) {
                return mb_substr($token, 0, -$len);
            }
        }
        return $token;
    }

    /** Вираз для MATCH … AGAINST (… IN BOOLEAN MODE) або null, якщо всі слова короткі */
    public function booleanExpression(): ?string
    {
        if (!static::hasFulltext()) {
            return null;
        }
        $min = static::minTokenLength();
        $terms = [];
        foreach ($this->tokens as $t) {
            $stem = static::stem($t);
            if (mb_strlen($stem) >= $min) {
                $terms[] = '+' . $stem . '*';
            }
        }
        return $terms ? implode(' ', $terms) : null;
    }

    /** Слова, які не потрапили у FULLTEXT (короткі або індексу немає) */
    protected function likeTokens(): array
    {
        if (!static::hasFulltext()) {
            return $this->tokens;
        }
        $min = static::minTokenLength();
        return array_values(array_filter($this->tokens, function ($t) use ($min) {
            return mb_strlen(static::stem($t)) < $min;
        }));
    }

    protected static function like(string $t): string
    {
        return '%' . addcslashes($t, '%_\\') . '%';
    }

    /**
     * Обмежити запит (Ad::getAds()) результатами пошуку.
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function apply($query)
    {
        if ($this->isEmpty()) {
            return $query;
        }
        $expr = $this->booleanExpression();
        $likes = $this->likeTokens();
        $code = preg_replace('/\s+/', '', $this->raw);

        $query->where(function ($q) use ($expr, $likes, $code) {
            $q->where(function ($q) use ($expr, $likes) {
                if ($expr !== null) {
                    $q->whereRaw(self::MATCH . ' AGAINST (? IN BOOLEAN MODE)', [$expr]);
                }
                foreach ($likes as $t) {
                    $like = static::like($t);
                    $q->where(function ($q) use ($like) {
                        $q->where('ads.name', 'LIKE', $like)
                            ->orWhere('ads.content', 'LIKE', $like)
                            ->orWhere('ads.brand', 'LIKE', $like);
                    });
                }
            });
            // Точний артикул або штрихкод
            if (mb_strlen($code) >= 4 && mb_strlen($code) <= 64) {
                $q->orWhere('ads.code', mb_strtolower($code))->orWhere('ads.gtin', $code);
            }
        });

        return $query;
    }

    /** Сортування за релевантністю (замість «спочатку нові») */
    public function orderByRelevance($query)
    {
        $expr = $this->booleanExpression();
        $query->orders = null;
        if ($expr !== null) {
            // Збіг у назві важить більше, ніж в описі
            $query->orderByRaw('MATCH(ads.name, ads.content, ads.brand) AGAINST (? IN BOOLEAN MODE) + (ads.name LIKE ?) * 2 DESC', [$expr, static::like($this->raw)]);
        } else {
            $query->orderByRaw('(ads.name LIKE ?) DESC', [static::like($this->raw)]);
        }
        return $query->orderBy('ads.date_active', 'desc');
    }
}
