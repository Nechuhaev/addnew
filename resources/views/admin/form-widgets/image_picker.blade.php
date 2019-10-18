<div class="card">
    <div class="card-body">

        <div class="input-group">
            <span class="input-group-btn">
                <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary select-image">
                <i class="fa fa-picture-o"></i> Выбрать
                </a>
            </span>
            <input id="thumbnail" value="{{ $default['image'] }}" class="form-control" type="text" name="image">
        </div>
        <img id="holder" class="img-fluid" style="margin-top: 20px" src="{{ $default['image'] ?? 'http://placehold.it/400x250'}}">
    </div>
</div>