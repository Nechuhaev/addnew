<div class="card">
    <div class="card-body">

        <div class="form-group">
            <label class="col-md-12">Название статьи</label>
            <div class="col-md-12">
                <input type="text"
                       name="name"
                       value="{{ $default['name'] ?? null }}"
                       placeholder="noobmaster69"
                       class="form-control form-control-line">
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-12">Slug</label>
            <div class="col-md-12">
                <input type="text"
                       name="slug"
                       value="{{ $default['slug'] ?? null }}"
                       placeholder="noobmaster69"
                       class="form-control form-control-line">
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-12">Короткое описание</label>
            <div class="col-md-12">
                <textarea name="excerpt" rows="5"
                          class="form-control form-control-line">{{ $default['excerpt'] ?? null }}</textarea>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-12">Контент</label>
            <div class="col-md-12">
                                <textarea name="content" rows="5"
                                          class="content form-control form-control-line">{{ $default['content'] ?? null }}</textarea>
            </div>
        </div>

    </div>
</div>