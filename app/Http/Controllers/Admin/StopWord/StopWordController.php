<?php

namespace App\Http\Controllers\Admin\StopWord;

use App\Ad;
use App\Models\Stats\Ad as AdStat;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * TODO: Прежде чем добавлять новые чартлисты - рефакторим этот код,
 * продумываем логику вывода чартлистов и разгребаем бардак в моделях stats
 * Class AdStatController
 * @package App\Http\Controllers\Admin\Stat
 */
class StopWordController extends Controller
{
    public function index(Request $request) {
        $data = [];

        $data['action'] = route('admin.stop-word.check');
        $data['words'] = $request->input('words');
        return view('admin.stop-word.index')->with($data);
    }

    public function check(Request $request)
    {
        $words = collect(explode(',', $request->words))
            ->map(function ($word) {
                return trim($word);
            })
            ->filter()
            ->filter(function ($word) {
                return Str::length($word) >= 3;
            });

        $query = Ad::query();
        foreach ($words as $word) {
            $query->orWhere('content', 'LIKE', '%'.$word.'%')->orWhere('name', 'LIKE', '%'.$word.'%');
        }
        $count = $query->count();

        $data['warning'] = sprintf('Найдено %s обхявлений. Удаляем?', $count);
        $data['action'] = route('admin.stop-word.delete');
        $data['words'] = $request->input('words');
        return view('admin.stop-word.index')->with($data);

    }

    public function delete(Request $request)
    {
        $words = collect(explode(',', $request->words))
            ->map(function ($word) {
                return trim($word);
            })
            ->filter()
            ->filter(function ($word) {
                return Str::length($word) >= 3;
            });

        $query = Ad::query();
        foreach ($words as $word) {
            $query->orWhere('content', 'LIKE', '%'.$word.'%')->orWhere('name', 'LIKE', '%'.$word.'%');
        }
        $count = $query->delete();

        return redirect(route('admin.stop-word'));
    }

}
