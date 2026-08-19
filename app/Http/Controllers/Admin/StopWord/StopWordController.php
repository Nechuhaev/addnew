<?php
namespace App\Http\Controllers\Admin\StopWord;
use App\Ad;
use App\StopWord;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StopWordController extends Controller
{
    /**
     * Список постійних стоп-слів + форма старого bulk-delete інструменту.
     */
    public function index(Request $request) {
        $data = [];
        $data['stopWords'] = StopWord::orderBy('word')->get();
        $data['action'] = route('admin.stop-word.check');
        $data['words'] = $request->input('words');
        return view('admin.stop-word.index')->with($data);
    }

    /**
     * Додає одне або кілька (через кому) слів у постійний список.
     * Дублі (незалежно від регістру) ігноруються. Для КОЖНОГО реально
     * нового слова одразу сканує й видаляє вже існуючі оголошення,
     * що його містять (name/content) — без окремого підтвердження.
     */
    public function store(Request $request)
    {
        $words = collect(explode(',', $request->input('new_words', '')))
            ->map(function ($word) {
                return trim($word);
            })
            ->filter()
            ->filter(function ($word) {
                return Str::length($word) >= 3;
            });

        $existingLower = StopWord::pluck('word')->map(function ($w) {
            return mb_strtolower(trim($w));
        })->all();

        $newlyAdded = [];
        foreach ($words as $word) {
            if (!in_array(mb_strtolower($word), $existingLower, true)) {
                StopWord::create(['word' => $word]);
                $existingLower[] = mb_strtolower($word);
                $newlyAdded[] = $word;
            }
        }

        $deletedCount = 0;
        if (!empty($newlyAdded)) {
            $query = Ad::query();
            foreach ($newlyAdded as $word) {
                $query->orWhere('content', 'LIKE', '%' . $word . '%')
                    ->orWhere('name', 'LIKE', '%' . $word . '%');
            }
            // Подвійна перевірка (додатково до !empty($newlyAdded) вище) —
            // ніколи не викликати delete() без хоча б однієї умови WHERE.
            if (count($newlyAdded) > 0) {
                $deletedCount = $query->delete();
            }
        }

        $message = 'Стоп-слова додано.';
        if ($deletedCount > 0) {
            $message .= " Автоматично видалено оголошень: {$deletedCount} (містили ці слова).";
        }

        return redirect(route('admin.stop-word'))->with('success', $message);
    }

    /**
     * Видаляє одне слово з постійного списку.
     */
    public function destroy($id)
    {
        StopWord::where('id', $id)->delete();
        return redirect(route('admin.stop-word'));
    }

    // -----------------------------------------------------------------
    // Старий інструмент масового видалення вже опублікованих оголошень
    // (незалежний від постійного списку вище, працює як і раніше).
    // -----------------------------------------------------------------

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

        // КРИТИЧНО: якщо після фільтрації слів не лишилось — жодних orWhere
        // не додасться, і delete()/count() виконались би БЕЗ УМОВ, тобто по
        // ВСІЙ таблиці ads. Цей запобіжник обов'язковий.
        if ($words->isEmpty()) {
            $data = [];
            $data['stopWords'] = StopWord::orderBy('word')->get();
            $data['warning'] = 'Список слів порожній або всі слова коротші за 3 символи — пошук не виконано.';
            $data['action'] = route('admin.stop-word.check');
            $data['words'] = $request->input('words');
            return view('admin.stop-word.index')->with($data);
        }

        $query = Ad::query();
        foreach ($words as $word) {
            $query->orWhere('content', 'LIKE', '%'.$word.'%')->orWhere('name', 'LIKE', '%'.$word.'%');
        }
        $count = $query->count();

        $data = [];
        $data['stopWords'] = StopWord::orderBy('word')->get();
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

        // Той самий критичний запобіжник — НІКОЛИ не виконувати delete()
        // без хоча б однієї умови WHERE.
        if ($words->isEmpty()) {
            return redirect(route('admin.stop-word'))->with('success', 'Список слів порожній — нічого не видалено.');
        }

        $query = Ad::query();
        foreach ($words as $word) {
            $query->orWhere('content', 'LIKE', '%'.$word.'%')->orWhere('name', 'LIKE', '%'.$word.'%');
        }
        $query->delete();
        return redirect(route('admin.stop-word'));
    }
}