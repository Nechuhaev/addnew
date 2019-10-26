<?php

namespace App\Http\Controllers\Admin\Ad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class City extends Controller
{
    public function showForm() {
        return view('admin.ad.city');
    }

    /**
     * Создать новый город через форму добавления
     */
    public function create() {

    }

    /**
     * Изменить информацию о существующем городе
     */
    public function edit() {

    }

    /**
     * Удалить город.
     * В случае, если к городу привязаны объявления - уведомляем что удаление невозможно
     */
    public function delete() {

    }
}
