<?php
namespace App\Services;
use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
class ShopInfoService
{
    public function update(User $user, array $data, ?UploadedFile $logo = null, ?UploadedFile $banner = null, bool $deleteBanner = false): void
    {
        if ($logo) {
            $this->handleLogoUpload($user, $logo);
            $data['image'] = $this->getLogoPath($user, $logo);
        }
        if ($banner) {
            $this->handleBannerUpload($user, $banner);
            $data['banner'] = $this->getBannerPath($user, $banner);
        } elseif ($deleteBanner && $user->banner) {
            $this->deleteBannerFile($user);
            $data['banner'] = null;
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

    protected function handleBannerUpload(User $user, UploadedFile $banner): void
    {
        if ($user->banner) {
            $oldPath = str_replace('/storage/', '', $user->banner);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        $filename = 'shop-banner-' . $user->id . '.' . $banner->getClientOriginalExtension();
        $banner->storeAs('shop-banners', $filename, 'public');
    }
    protected function getBannerPath(User $user, UploadedFile $banner): string
    {
        return '/storage/shop-banners/shop-banner-' . $user->id . '.' . $banner->getClientOriginalExtension();
    }

    protected function deleteBannerFile(User $user): void
    {
        $oldPath = str_replace('/storage/', '', $user->banner);
        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}