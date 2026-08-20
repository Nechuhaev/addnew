<?php

namespace App\Http\Controllers\Admin\Translation;

use App\Http\Controllers\Controller;
use App\Translation;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    /**
     * Список перекладів. Можна фільтрувати за групою й шукати за ключем.
     */
    public function index(Request $request)
    {
        $group = $request->get('group');
        $search = $request->get('search');

        // Групуємо по key+group, беремо uk і ru значення поруч для зручного
        // редагування (щоб не гортати дві окремі сторінки для кожної мови).
        $ukRows = Translation::where('locale', 'uk')
            ->when($group, fn($q) => $q->where('group', $group))
            ->when($search, fn($q) => $q->where('key', 'LIKE', "%{$search}%"))
            ->get()
            ->keyBy(function ($row) {
                return $row->group . '::' . $row->key;
            });

        $ruRows = Translation::where('locale', 'ru')
            ->when($group, fn($q) => $q->where('group', $group))
            ->when($search, fn($q) => $q->where('key', 'LIKE', "%{$search}%"))
            ->get()
            ->keyBy(function ($row) {
                return $row->group . '::' . $row->key;
            });

        $allKeys = $ukRows->keys()->merge($ruRows->keys())->unique()->sort();

        $rows = $allKeys->map(function ($compositeKey) use ($ukRows, $ruRows) {
            [$grp, $key] = explode('::', $compositeKey, 2);
            return [
                'group' => $grp,
                'key' => $key,
                'uk' => optional($ukRows->get($compositeKey))->value ?? '',
                'ru' => optional($ruRows->get($compositeKey))->value ?? '',
            ];
        })->values();

        $groups = Translation::distinct()->pluck('group')->sort()->values();

        return view('admin.translations.index', [
            'rows' => $rows,
            'groups' => $groups,
            'currentGroup' => $group,
            'search' => $search,
        ]);
    }

    /**
     * Додати новий ключ перекладу (обидві мови одразу).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group' => 'required|string|max:100',
            'key' => 'required|string|max:255',
            'uk' => 'nullable|string',
            'ru' => 'nullable|string',
        ], [
            'group.required' => 'Вкажіть групу',
            'key.required' => 'Вкажіть ключ',
        ]);

        Translation::updateOrCreate(
            ['group' => $validated['group'], 'key' => $validated['key'], 'locale' => 'uk'],
            ['value' => $validated['uk'] ?? '']
        );
        Translation::updateOrCreate(
            ['group' => $validated['group'], 'key' => $validated['key'], 'locale' => 'ru'],
            ['value' => $validated['ru'] ?? '']
        );

        return redirect(route('admin.translations', ['group' => $validated['group']]))
            ->with('success', 'Ключ перекладу додано.');
    }

    /**
     * Масове збереження — оновлює всі рядки, показані на поточній сторінці
     * (з урахуванням фільтра), одним сабмітом.
     */
    public function update(Request $request)
    {
        $items = $request->input('items', []);

        foreach ($items as $item) {
            if (empty($item['group']) || empty($item['key'])) {
                continue;
            }

            Translation::updateOrCreate(
                ['group' => $item['group'], 'key' => $item['key'], 'locale' => 'uk'],
                ['value' => $item['uk'] ?? '']
            );
            Translation::updateOrCreate(
                ['group' => $item['group'], 'key' => $item['key'], 'locale' => 'ru'],
                ['value' => $item['ru'] ?? '']
            );
        }

        return redirect()->back()->with('success', 'Переклади збережено.');
    }

    /**
     * Видалити ключ (обидві локалі).
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'group' => 'required|string',
            'key' => 'required|string',
        ]);

        Translation::where('group', $validated['group'])
            ->where('key', $validated['key'])
            ->delete();

        return redirect()->back()->with('success', 'Ключ видалено.');
    }
}