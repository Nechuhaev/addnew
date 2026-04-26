<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ShopInfoService
{
    public function update(User $user, array $data, ?UploadedFile $logo = null): void
    {
        if ($logo) {
            $this->handleLogoUpload($user, $logo);
            $data['image'] = $this->getLogoPath($user, $logo);
        }

        $user->update($data);
    }

    protected function handleLogoUpload(User $user, UploadedFile $logo): void
    {
        if ($user->image) {
            $oldPath = str_replace('/storage/', '', $user->image);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $filename = 'shop-logo-' . $user->id . '.' . $logo->getClientOriginalExtension();
        $logo->storeAs('shop-logos', $filename, 'public');
    }

    protected function getLogoPath(User $user, UploadedFile $logo): string
    {
        return '/storage/shop-logos/shop-logo-' . $user->id . '.' . $logo->getClientOriginalExtension();
    }
}
