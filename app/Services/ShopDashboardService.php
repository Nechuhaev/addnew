<?php

namespace App\Services;

use App\Ad;
use App\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShopDashboardService
{
    public function getShopData(User $user): array
    {
        return [
            'name' => $user->firstname,
            'email' => $user->email,
            'telephone' => $user->telephone,
            'description' => $user->info,
            'logo_url' => $this->getLogoUrl($user),
        ];
    }

    public function getProducts(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Ad::where('user_id', $user->id)
            ->where('is_product', 1)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getProductsStats(User $user): array
    {
        $query = Ad::where('user_id', $user->id)->where('is_product', 1);

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 1)->count(),
            'suspended' => (clone $query)->where('status', 0)->count(),
            'archived' => (clone $query)->where('status', 2)->count(),
        ];
    }

    protected function getLogoUrl(User $user): string
    {
        $logoUrl = $user->image;

        if ($logoUrl && strpos($logoUrl, 'http') !== 0) {
            return asset($logoUrl);
        }

        return asset('assets/front/img/placeholder.png');
    }
}
