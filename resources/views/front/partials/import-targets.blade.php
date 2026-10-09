{{-- Вибір категорії й міста для нових товарів. $prefix — id полів, $selCategory/$selCity — вибрані --}}
@php($ru = app()->getLocale() === 'ru')
<div class="import-targets">
    <label class="import-targets__field">
        <span>{{ $ru ? 'Категория для новых товаров' : 'Категорія для нових товарів' }}</span>
        <select name="category_id" id="{{ $prefix }}category_id" required>
            <option value="">{{ $ru ? '— выберите —' : '— оберіть —' }}</option>
            @foreach(\App\Services\ImportTargets::categories() as $group)
                <optgroup label="{{ $group['label'] }}">
                    @foreach($group['options'] as $id => $name)
                        <option value="{{ $id }}" {{ (int) ($selCategory ?? 0) === (int) $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </label>
    <label class="import-targets__field">
        <span>{{ $ru ? 'Город для новых товаров' : 'Місто для нових товарів' }}</span>
        <select name="city_id" id="{{ $prefix }}city_id" required>
            <option value="">{{ $ru ? '— выберите —' : '— оберіть —' }}</option>
            @foreach(\App\Services\ImportTargets::cities() as $group)
                <optgroup label="{{ $group['label'] }}">
                    @foreach($group['options'] as $id => $name)
                        <option value="{{ $id }}" {{ (int) ($selCity ?? 0) === (int) $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </label>
</div>
