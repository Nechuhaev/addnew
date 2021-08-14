(function( $ ){

    $.fn.addnewfile = function(type, options) {
        type = type || 'file';

        this.on('click', function(e) {
            var route_prefix = (options && options.prefix) ? options.prefix : '/laravel-filemanager';
            localStorage.setItem('target_input', $(this).data('input'));
            localStorage.setItem('target_preview', $(this).data('preview'));
            window.open(route_prefix + '?type=' + type, 'FileManager', 'width=900,height=600');
            window.SetUrl = function (url, file_path) {
                //set the value of the desired input to image url

                var target_holder = $(e.target).closest('.ad-image');

                var target_input = target_holder.find('input');
                target_input.val(file_path).trigger('change');

                //set or change the preview image src
                var target_preview = target_holder.find('img');;
                target_preview.attr('src', url).trigger('change');
            };
            return false;
        });
    }

})(jQuery);
