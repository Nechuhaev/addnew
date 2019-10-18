<?php

namespace App\Http\Requests\Article;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Session;

class ArticleStoreRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $update_param = '';
        if ($this->request->get('article_id')) {
            $update_param = ',id,' . $this->request->get('article_id');
        }
        return [
            'name' => 'required|string',
            'slug' => 'unique:articles' . $update_param,
            'content' => 'required',
            'categories' => 'required',
            'excerpt' => 'required|max:2000',
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Введите название',
            'slug.unique' => 'Такой slug уже существует',
            'content.required' => 'Текст статьи не должен быть пустым',
            'excerpt.required' => 'Введите короткое описание статьи',
            'excerpt.max' => 'Максимальная длина короткого описания - :max символов',
            'categories.required' => 'Выберите категорию, к которой будет отнесена статья',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов'
        ];
    }



}
