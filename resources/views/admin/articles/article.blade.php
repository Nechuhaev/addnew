@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Пользователь </h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.index') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Column -->
        <div class="col-lg-4 col-xlg-3 col-md-5">
            <div class="card">
                <div class="card-body">
                    <img src="http://placehold.it/400x250" class="img-fluid" alt="">
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Параметры SEO</h4>



                    <div class="form-group">
                        <label class="col-md-12">SEO теги</label>
                        <div class="col-md-12">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio1" name="customRadio" class="custom-control-input">
                                <label class="custom-control-label" for="customRadio1">SEO шаблон</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio2" name="customRadio" class="custom-control-input">
                                <label class="custom-control-label" for="customRadio2">Индивидуальные теги</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-12">Meta-тег title</label>
                        <div class="col-md-12">
                            <input type="text"
                                   placeholder="noobmaster69"
                                   class="form-control form-control-line">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-12">Meta-тег description</label>
                        <div class="col-md-12">
                            <textarea rows="5"
                                      class="form-control form-control-line"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-12">Meta-тег keywords</label>
                        <div class="col-md-12">
                            <textarea rows="3"
                                      class="form-control form-control-line"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Категории</h4>
                    <div class="form-group">

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="category-1" name="category[]" value="1">
                            <label for="category-1" class="custom-control-label">Meta-тег title</label>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="category-2" name="category[]" value="1">
                            <label for="category-2" class="custom-control-label">Meta-тег title</label>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="category-3" name="category[]" value="1">
                            <label for="category-3" class="custom-control-label">Meta-тег title</label>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="category-4" name="category[]" value="1">
                            <label for="category-4" class="custom-control-label">Meta-тег title</label>
                        </div>

                    </div>

                </div>
            </div>
        </div>
        <!-- Column -->
        <!-- Column -->
        <div class="col-lg-8 col-xlg-9 col-md-7">
            <div class="card">
                <div class="card-body">
                    <form class="form-horizontal form-material">
                        <div class="form-group">
                            <label class="col-md-12">Название статьи</label>
                            <div class="col-md-12">
                                <input type="text"
                                       placeholder="noobmaster69"
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-12">Контент</label>
                            <div class="col-md-12">
                                <textarea name="content" rows="5"
                                          class="content form-control form-control-line"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <button class="btn btn-success">Сохранить статью</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Column -->
    </div>

    <script src="https://cdn.tiny.cloud/1/acl3zjjcwn2wu5y9ad8741ibtyz1fcoi1iwhsdhpqblv1q2y/tinymce/5/tinymce.min.js"></script>

    <script>
        // document.addEventListener('load', function (ev) {
        //     $('.fileupload-field')
        //         .fileupload({
        //             disableImageResize: false,
        //             previewMaxWidth: 320,
        //             previewMaxHeight: 320
        //         })
        //         .bind('fileuploadprocessalways', function(e, data)
        //         {
        //             var canvas = data.files[0].preview;
        //             var dataURL = canvas.toDataURL();
        //             $("#some-image").css("background-image", 'url(' + dataURL +')');
        //
        //         })
        // })


        tinymce.init({
            selector:'textarea.content',
            height: 700,
            plugins: "image",
            images_upload_url: 'postAcceptor.php',
            automatic_uploads: false
    });</script>
@endsection