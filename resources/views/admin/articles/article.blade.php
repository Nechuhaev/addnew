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

                    <div class="input-group">
   <span class="input-group-btn">
     <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
       <i class="fa fa-picture-o"></i> Choose
     </a>
   </span>
                        <input id="thumbnail" class="form-control" type="text" name="filepath">
                    </div>
                    <img id="holder" class="img-fluid" style="margin-top: 20px" src="http://placehold.it/400x250">
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

    <script src="https://cdn.tiny.cloud/1/acl3zjjcwn2wu5y9ad8741ibtyz1fcoi1iwhsdhpqblv1q2y/tinymce/4/tinymce.min.js"></script>

    <script>

        var editor_config = {
            path_absolute : "/",
            selector: "textarea.content",
            height: 400,
            plugins: [
                "advlist autolink lists link image charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars code fullscreen",
                "insertdatetime media nonbreaking save table contextmenu directionality",
                "emoticons template paste textcolor colorpicker textpattern"
            ],
            toolbar: "styleselect | alignleft aligncenter alignright | bullist numlist | outdent indent | link image media | code",
            relative_urls: false,
            file_browser_callback : function(field_name, url, type, win) {
                var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;

                var cmsURL = editor_config.path_absolute + 'laravel-filemanager?field_name=' + field_name;
                if (type == 'image') {
                    cmsURL = cmsURL + "&type=Images";
                } else {
                    cmsURL = cmsURL + "&type=Files";
                }

                tinyMCE.activeEditor.windowManager.open({
                    file : cmsURL,
                    title : 'Filemanager',
                    width : x * 0.8,
                    height : y * 0.8,
                    resizable : "yes",
                    close_previous : "no"
                });
            }
        };

        tinymce.init(editor_config);


    </script>
@endsection