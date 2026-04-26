<?php

namespace App\Http\Controllers\Front\User\Shop;

use App\Http\Controllers\Controller;
use App\Services\ProductEditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductEditController extends Controller
{
    protected $service;

    public function __construct(ProductEditService $service)
    {
        $this->service = $service;
    }

    public function edit($id)
    {
        $product = $this->service->findProductForEdit($id, Auth::user());

        if (!$product) {
            return redirect()->back()->with('error', 'Товар не знайдено або у вас немає прав для редагування.');
        }

        $data = [
            'product' => $product,
            'currencies' => $this->service->getCurrencies(),
        ];

        return view('front.user.profile.shop.product-edit')->with($data);
    }

    public function update($id, Request $request)
    {
        $product = $this->service->findProductForEdit($id, Auth::user());

        if (!$product) {
            return redirect()->back()->with('error', 'Товар не знайдено або у вас немає прав для редагування.');
        }

        $validated = $request->validate(
            $this->service->validationRules(),
            $this->service->validationMessages()
        );

        $imageSlots = [];
        if ($request->hasFile('image_slot')) {
            foreach ($request->file('image_slot') as $slotIndex => $files) {
                if (is_array($files)) {
                    $imageSlots[$slotIndex] = $files;
                } elseif ($files instanceof \Illuminate\Http\UploadedFile && $files->isValid()) {
                    $imageSlots[$slotIndex] = [$files];
                }
            }
        }

        $validated['delete_image_slots'] = $request->input('delete_image_slot', []);

        $this->service->updateProduct(
            $product,
            $validated,
            $imageSlots
        );

        return redirect(route('profile.shop.dashboard'))->with('success', 'Товар успішно оновлено!');
    }
}
