<div class="card">
    <div class="card-body">
        <h4 class="card-title">Параметры SEO</h4>
        <div class="form-group">
            <label class="col-md-12">Meta-тег title</label>
            <div class="col-md-12">
                <input type="text"
                       placeholder="noobmaster69"
                       name="meta_title"
                       value="{{ $default['meta_title'] }}"
                       class="form-control form-control-line">
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-12">Meta-тег description</label>
            <div class="col-md-12">
                            <textarea rows="5"
                                      name="meta_description"
                                      class="form-control form-control-line">{{ $default['meta_description'] }}</textarea>
            </div>
        </div>
    </div>
</div>