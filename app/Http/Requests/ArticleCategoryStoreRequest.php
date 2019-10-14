<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Session;

class ArticleCategoryStoreRequest extends FormRequest
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
        if ($this->request->get('category_id')) {
            $update_param = ',id,' . $this->request->get('category_id');
        }
        return [
            'name' => 'required|string',
            'slug' => 'unique:article_categories' . $update_param,
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
            'name.required' => 'Введите название статьи',
            'slug.unique' => 'Категории с таким slug уже добавлены',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов'
        ];
    }



}
