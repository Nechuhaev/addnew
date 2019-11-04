var city = {
    'loadRegions': function (el) {

        var regions_text = "<option value='0'>Выберите область</option>";

        var value = $(el).val();
        $('[name=region_id]').prop('disabled', true);
        $.getJSON("/api/ad/country/" + value, function ( data ) {

            $.each( data.data.regions, function ( key, val ) {
                regions_text += "<option value='" + val.id + "'>" + val.name + "</option>";
            } )

            $('[name=region_id]').html(regions_text);
            $('[name=region_id]').prop('disabled', false);
        });
    }
}

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

jQuery(function () {

    // File managers
    jQuery('#lfm').filemanager('image');
    jQuery('.filepicker').addnewfile('image');

    // Datepicker
    jQuery(".datepicker").datepicker({
        dateFormat: 'yy-mm-dd',
        beforeShow: function(input, inst)
        {
            var txtBoxOffset = $(this).offset();
            var top = txtBoxOffset.top;
            setTimeout(function () {
                inst.dpDiv.css({
                    top: top + 35,
                    left: 'initial',//show at the end of textBox
                    right: 40//show at the end of textBox
                });
            }, 0);

            //inst.dpDiv.css({marginTop: -input.offsetHeight + 'px', marginRight: input.offsetWidth - 20 + 'px'});
        }
    });

    // Autocompletes
    $( "#ad_category" ).autocomplete({
        minLength: 0,
        source: function (request, response) {
            $.getJSON("/api/ad/category/autocomplete/" + encodeURIComponent(request.term), function (json) {

                response($.map(json.data, function(item){
                    return {
                        id: item.id,
                        label: item.path,
                        value: item.name
                    }
                }));
                console.log(json.data);
            });

        },
        select: function (elem, item) {

            $( "#ad_category" ).val(item.item['label']);
            $( "#ad_category_id" ).val(item.item['id']);
        }
    }).focus(function () {
        $(this).autocomplete('search', '');
    });

    $( "#ad_invalid" ).autocomplete({
        minLength: 0,
        source: function (request, response) {
            $.getJSON("/api/ad/city/autocomplete/" + encodeURIComponent(request.term), function (json) {

                response($.map(json.data, function(item){
                    return {
                        id: item.id,
                        label: item.path,
                        value: item.name
                    }
                }));
                console.log(json.data);
            });

        },
        select: function (elem, item) {

            $( "#ad_invalid" ).val(item.item['label']);
            $( "#ad_city_id" ).val(item.item['id']);
        }
    }).focus(function () {
        $(this).autocomplete('search', '');
    });

    $( "#ad_user" ).autocomplete({
        minLength: 0,
        source: function (request, response) {
            $.getJSON("/api/user/autocomplete/" + encodeURIComponent(request.term), function (json) {

                response($.map(json.data, function(item){
                    return {
                        id: item.id,
                        label: item.email,
                        value: item.email
                    }
                }));
                console.log(json.data);
            });

        },
        select: function (elem, item) {

            $( "#ad_user" ).val(item.item['label']);
            $( "#ad_user_id" ).val(item.item['id']);
        }
    }).focus(function () {
        $(this).autocomplete('search', '');
    });


    $("#tags").tagEditor({
        initialTags: $("#tags").data('json'),
        placeholder: 'Добавить теги...',
        autocomplete: {
            minLength: 0,
            source: function (request, response) {
                $.getJSON("/api/ad/tag/autocomplete/" + encodeURIComponent(request.term), function (json) {
                    response($.map(json.data, function(item){
                        return {

                            value: item.name
                        }
                    }));
                });
            },
        }
    });
});

