<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\ShopMessageTemplate;
use Illuminate\Http\Request;

class ShopMessageTemplateController extends Controller
{
    /**
     * Список шаблонів + форма створення/редагування на одній сторінці
     * (той самий підхід, що й в адмінці тегів оголошень).
     */
    public function showForm($id = null)
    {
        $templates = ShopMessageTemplate::orderBy('name')->get();

        $template = null;
        if ($id) {
            $template = ShopMessageTemplate::find($id);
            $action = route('admin.shopMessageTemplates.update');
        } else {
            $action = route('admin.shopMessageTemplates.create');
        }

        return view('admin.shops.message-templates', [
            'templates' => $templates,
            'template' => $template,
            'action' => $action,
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ], [
            'name.required' => 'Введіть назву шаблону',
            'subject.required' => 'Введіть тему листа',
            'body.required' => 'Введіть текст листа',
        ]);

        ShopMessageTemplate::create($request->only('name', 'subject', 'body'));

        return redirect(route('admin.shopMessageTemplates'))->with('success', 'Шаблон додано');
    }

    public function update(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:shop_message_templates,id',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ], [
            'name.required' => 'Введіть назву шаблону',
            'subject.required' => 'Введіть тему листа',
            'body.required' => 'Введіть текст листа',
        ]);

        $template = ShopMessageTemplate::find($request->template_id);
        if ($template) {
            $template->fill($request->only('name', 'subject', 'body'))->save();
        }

        return redirect(route('admin.shopMessageTemplates'))->with('success', 'Шаблон оновлено');
    }

    public function delete($id)
    {
        $template = ShopMessageTemplate::find($id);
        if ($template) {
            $template->delete();
        }

        return redirect(route('admin.shopMessageTemplates'))->with('success', 'Шаблон видалено');
    }
}
