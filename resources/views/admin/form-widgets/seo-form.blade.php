<div class="form-group">
    <label class="col-12">Meta Title</label>
    <div class="col-12">
        <input type="text"
               placeholder=""
               value="{{ $meta_title ?? '' }}"
               name="meta_title"
               class="form-control form-control-line">
    </div>
</div>

<div class="form-group">
    <label class="col-12">Meta Description</label>
    <div class="col-12">
        <textarea name="meta_description"
                  rows="5"
                  class="form-control form-control-line">{{ $meta_description ?? '' }}</textarea>
    </div>
</div>


<div class="form-group">
    <label class="col-12">Description</label>
    <div class="col-12">
        <textarea name="description"
                  rows="5"
                  class="content form-control form-control-line">{{ $description ?? '' }}</textarea>
    </div>
</div>