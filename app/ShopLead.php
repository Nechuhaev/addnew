<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Кандидат у магазини (адмінка → Магазини → Кандидати)
 */
class ShopLead extends Model
{
    const STATUSES = [
        'new' => 'Новий',
        'contacted' => 'Написали',
        'replied' => 'Відповіли',
        'joined' => 'Підключились',
        'declined' => 'Відмовились',
    ];

    const STATUS_BADGES = [
        'new' => 'secondary',
        'contacted' => 'info',
        'replied' => 'warning',
        'joined' => 'success',
        'declined' => 'dark',
    ];

    protected $fillable = [
        'name', 'site_url', 'domain', 'category', 'email', 'email_source',
        'email_lookup_status', 'email_candidates', 'status', 'note', 'contacted_at',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'email_candidates' => 'array',
    ];

    public function messages()
    {
        return $this->hasMany(ShopLeadMessage::class)->orderByDesc('created_at');
    }

    /**
     * Домен без www — для пошуку дублікатів і магазинів, що вже є на сайті
     */
    public static function domainOf(string $url): ?string
    {
        $host = parse_url(preg_match('#^https?://#i', $url) ? $url : 'https://' . $url, PHP_URL_HOST);

        return $host ? preg_replace('/^www\./i', '', strtolower($host)) : null;
    }

    /**
     * Магазин із цим доменом уже зареєстрований на addnew (щоб не писати своїм)
     */
    public function existingShop(): ?User
    {
        return User::where('is_shop_owner', 1)
            ->where('site_url', 'like', '%' . $this->domain . '%')
            ->first();
    }

    /**
     * Змінні для шаблонів листів кандидату ({{shop_name}}, {{goods}}, …)
     */
    public function templateVars(string $senderName): array
    {
        return [
            'shop_name' => $this->name,
            'goods' => $this->category ? "товари в категорії «{$this->category}»" : 'товари',
            'site_url' => $this->site_url,
            'register_url' => route('business-register'),
            'sender_name' => $senderName,
        ];
    }
}
