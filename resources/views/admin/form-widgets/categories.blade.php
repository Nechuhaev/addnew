<div class="card">
    <div class="card-body">
        <h4 class="card-title">Категории</h4>
        <div class="form-group">
            @foreach($categories as $category)
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="category-{{ $category['id'] }}" name="categories[]" {{ (in_array($category['id'], $default['categories'])) ? 'checked' : '' }} value="{{ $category['id'] }}">
                    <label for="category-{{ $category['id'] }}" class="custom-control-label">{{ $category['name'] }}</label>
                </div>
            @endforeach
        </div>
    </div>
</div>