<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted' => 'Поле :attribute має бути прийняте.',
    'active_url' => 'Поле :attribute не є коректною URL-адресою.',
    'after' => 'Поле :attribute має містити дату після :date.',
    'after_or_equal' => 'Поле :attribute має містити дату не раніше :date.',
    'alpha' => 'Поле :attribute може містити лише літери.',
    'alpha_dash' => 'Поле :attribute може містити лише літери, цифри, дефіси та підкреслення.',
    'alpha_num' => 'Поле :attribute може містити лише літери та цифри.',
    'array' => 'Поле :attribute має бути масивом.',
    'before' => 'Поле :attribute має містити дату до :date.',
    'before_or_equal' => 'Поле :attribute має містити дату не пізніше :date.',
    'between' => [
        'numeric' => 'Поле :attribute має бути між :min та :max.',
        'file' => 'Розмір :attribute має бути між :min та :max кілобайт.',
        'string' => 'Поле :attribute має містити від :min до :max символів.',
        'array' => 'Поле :attribute має містити від :min до :max елементів.',
    ],
    'boolean' => 'Поле :attribute має бути true або false.',
    'confirmed' => 'Підтвердження поля :attribute не збігається.',
    'date' => 'Поле :attribute не є коректною датою.',
    'date_equals' => 'Поле :attribute має містити дату, що дорівнює :date.',
    'date_format' => 'Поле :attribute не відповідає формату :format.',
    'different' => 'Поля :attribute та :other мають відрізнятись.',
    'digits' => 'Поле :attribute має складатись з :digits цифр.',
    'digits_between' => 'Поле :attribute має складатись від :min до :max цифр.',
    'dimensions' => 'Поле :attribute має неприпустимі розміри зображення.',
    'distinct' => 'Поле :attribute містить дубльоване значення.',
    'email' => 'Поле :attribute має бути коректною email адресою.',
    'ends_with' => 'Поле :attribute має закінчуватись одним із: :values',
    'exists' => 'Обране значення :attribute недійсне.',
    'file' => 'Поле :attribute має бути файлом.',
    'filled' => 'Поле :attribute має містити значення.',
    'gt' => [
        'numeric' => 'Поле :attribute має бути більше за :value.',
        'file' => 'Розмір :attribute має бути більше за :value кілобайт.',
        'string' => 'Поле :attribute має бути більше за :value символів.',
        'array' => 'Поле :attribute має містити більше за :value елементів.',
    ],
    'gte' => [
        'numeric' => 'Поле :attribute має бути більше або дорівнювати :value.',
        'file' => 'Розмір :attribute має бути більше або дорівнювати :value кілобайт.',
        'string' => 'Поле :attribute має бути більше або дорівнювати :value символів.',
        'array' => 'Поле :attribute має містити :value елементів або більше.',
    ],
    'image' => 'Поле :attribute має бути зображенням.',
    'in' => 'Обране значення :attribute недійсне.',
    'in_array' => 'Поле :attribute не існує в :other.',
    'integer' => 'Поле :attribute має бути цілим числом.',
    'ip' => 'Поле :attribute має бути коректною IP-адресою.',
    'ipv4' => 'Поле :attribute має бути коректною IPv4-адресою.',
    'ipv6' => 'Поле :attribute має бути коректною IPv6-адресою.',
    'json' => 'Поле :attribute має бути коректним JSON-рядком.',
    'lt' => [
        'numeric' => 'Поле :attribute має бути менше за :value.',
        'file' => 'Розмір :attribute має бути менше за :value кілобайт.',
        'string' => 'Поле :attribute має бути менше за :value символів.',
        'array' => 'Поле :attribute має містити менше за :value елементів.',
    ],
    'lte' => [
        'numeric' => 'Поле :attribute має бути менше або дорівнювати :value.',
        'file' => 'Розмір :attribute має бути менше або дорівнювати :value кілобайт.',
        'string' => 'Поле :attribute має бути менше або дорівнювати :value символів.',
        'array' => 'Поле :attribute не має містити більше за :value елементів.',
    ],
    'max' => [
        'numeric' => 'Поле :attribute не може бути більше за :max.',
        'file' => 'Розмір :attribute не може бути більше за :max кілобайт.',
        'string' => 'Поле :attribute не може бути довше за :max символів.',
        'array' => 'Поле :attribute не може містити більше за :max елементів.',
    ],
    'mimes' => 'Поле :attribute має бути файлом одного з типів: :values.',
    'mimetypes' => 'Поле :attribute має бути файлом одного з типів: :values.',
    'min' => [
        'numeric' => 'Поле :attribute має бути не менше :min.',
        'file' => 'Розмір :attribute має бути не менше :min кілобайт.',
        'string' => 'Поле :attribute має містити щонайменше :min символів.',
        'array' => 'Поле :attribute має містити щонайменше :min елементів.',
    ],
    'not_in' => 'Обране значення :attribute недійсне.',
    'not_regex' => 'Формат поля :attribute недійсний.',
    'numeric' => 'Поле :attribute має бути числом.',
    'present' => 'Поле :attribute має бути присутнім.',
    'regex' => 'Формат поля :attribute недійсний.',
    'required' => "Поле :attribute обов'язкове.",
    'required_if' => "Поле :attribute обов'язкове, якщо :other дорівнює :value.",
    'required_unless' => "Поле :attribute обов'язкове, якщо :other не входить в :values.",
    'required_with' => "Поле :attribute обов'язкове, якщо присутнє :values.",
    'required_with_all' => "Поле :attribute обов'язкове, якщо присутні :values.",
    'required_without' => "Поле :attribute обов'язкове, якщо відсутнє :values.",
    'required_without_all' => "Поле :attribute обов'язкове, якщо відсутні всі :values.",
    'same' => 'Поля :attribute та :other мають збігатись.',
    'size' => [
        'numeric' => 'Поле :attribute має дорівнювати :size.',
        'file' => 'Розмір :attribute має дорівнювати :size кілобайт.',
        'string' => 'Поле :attribute має містити :size символів.',
        'array' => 'Поле :attribute має містити :size елементів.',
    ],
    'starts_with' => 'Поле :attribute має починатись з одного із: :values',
    'string' => 'Поле :attribute має бути рядком.',
    'timezone' => 'Поле :attribute має бути коректною часовою зоною.',
    'unique' => 'Таке значення :attribute вже зайняте.',
    'uploaded' => 'Не вдалося завантажити :attribute.',
    'url' => 'Формат поля :attribute недійсний.',
    'uuid' => 'Поле :attribute має бути коректним UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [],

];