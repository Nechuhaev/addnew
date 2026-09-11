<?php

namespace App\Http\Controllers\Admin\Prompt;

use App\Http\Controllers\Controller;
use App\PromptTemplate;
use Illuminate\Http\Request;

class PromptController extends Controller
{
    /**
     * Список усіх промптів. Групуємо за командою (command_class), щоб
     * було видно, до якої функції сайту належить кожен промпт.
     */
    public function index()
    {
        $prompts = PromptTemplate::orderBy('command_class')->orderBy('label')->get();
        $grouped = $prompts->groupBy('command_class');

        return view('admin.prompts.index', ['grouped' => $grouped]);
    }

    public function edit($id)
    {
        $prompt = PromptTemplate::findOrFail($id);
        return view('admin.prompts.edit', ['prompt' => $prompt]);
    }

    public function update($id, Request $request)
    {
        $prompt = PromptTemplate::findOrFail($id);

        $validated = $request->validate([
            'template' => 'required|string',
        ], [
            'template.required' => 'Текст промпту не може бути порожнім.',
        ]);

        $prompt->update(['template' => $validated['template']]);

        return redirect(route('admin.prompts.edit', $id))->with('success', 'Промпт оновлено!');
    }

    /**
     * Повертає промпт до оригінального тексту з коду (default_template),
     * якщо адмін щось зіпсував чи хоче почати редагування заново.
     */
    public function reset($id)
    {
        $prompt = PromptTemplate::findOrFail($id);
        $prompt->update(['template' => $prompt->default_template]);

        return redirect(route('admin.prompts.edit', $id))->with('success', 'Промпт скинуто до значення за замовчуванням.');
    }
}
