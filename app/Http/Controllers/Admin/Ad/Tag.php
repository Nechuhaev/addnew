<?php

namespace App\Http\Controllers\Admin\Ad;

use App\AdTag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Tag extends Controller
{
    private $per_page = 20;

    /**
     * Страница с формой добавления меток и их списком
     * @param null $tag_id
     * @return $this
     */
    public function showForm(Request $request, $tag_id = null)
    {
        $direction = $request->get('direction') ?? 'asc';
        $order = $request->get('order') ?? 'name';

        $tags = AdTag::withCount('ads')->orderBy($order, $direction)
            ->paginate($this->per_page);

        $tag = null;
        if ($tag_id) {
            $tag = AdTag::find($tag_id);
            $action = route('admin.adTags.update');
        } else {
            $action = route('admin.adTags.create');
        }

        return view('admin.ad.tag')->with([
            'action' => $action,
            'direction' => $direction,
            'order' => $order,
            'tag' => $tag,
            'tags' => $tags,
            'action_search' => route('admin.adTags.search')
        ]);

    }

    /**
     * Поиск меток по имени
     * @param Request $request
     * @return $this
     */
    public function search(Request $request) {

        $direction = $request->get('direction') ?? 'asc';
        $order = $request->get('order') ?? 'name';
        $requested_tag = $request->name;

        $tags = AdTag::withCount('ads')->where('name', 'like', '%' . $requested_tag . '%')->orderBy($order, $direction)
            ->paginate($this->per_page);

        $tag = null;

        $action = route('admin.adTags.create');

        return view('admin.ad.tag')->with([
            'action' => $action,
            'direction' => $direction,
            'order' => $order,
            'tag' => $tag,
            'tags' => $tags,
            'action_search' => route('admin.adTags.search'),
            'requested_tag' => $requested_tag
        ]);
    }

    /**
     * Создать новый тег через форму добавления
     * @param Request $request
     * @return $this
     */
    public function create(Request $request)
    {
        $errors = [
            'name.required' => 'Введите название',
            'slug.unique' => 'Такой slug уже существует',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов'
        ];

        $request->validate([
            'name' => 'required|string',
            'slug' => 'unique:ad_tags',
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
        ], $errors);

        AdTag::create($request->all());

        return redirect(route('admin.adTags'))->with('success', 'Тег добавлен');
    }

    public function update(Request $request) {

        $errors = [
            'name.required' => 'Введите название',
            'slug.unique' => 'Такой slug уже используется',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов',
        ];

        $request->validate([
            'name' => 'required|string',
            'slug' => 'unique:ad_tags,slug,' . $request->tag_id,
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
        ], $errors);

        $category = AdTag::find($request->tag_id);

        if ($category) {
            $category->fill($request->all())->save();
        }

        return redirect(route('admin.adTags'))->with('success', 'Данные тега обновлены');

    }

    public function delete($tag_id) {
        #TODO: синхронизация удаленной метки к объявлениям
        if ($tag_id) {
            AdTag::find($tag_id)->delete();
        }
        return redirect(route('admin.adTags'))->with('success', 'Данные тега удалены');
    }

    public function deleteMany($ids) {
        $ids = explode(',', $ids);

        if ($ids) {
            foreach ($ids as $id) {
                AdTag::find($id)->delete();
            }
        }
        return route('admin.adTags');
    }

}
