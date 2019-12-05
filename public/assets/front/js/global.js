// Автокомплит для городов поиск
var options = {
    url: function(phrase) {
        if (phrase.length > 2 && phrase.length < 6 ) {
            return "/api/ad/city/autocomplete/" + phrase;
        }
    },
    getValue: "name",
    listLocation: "data",
    list: {
        onChooseEvent: function () {
            $('#search_city_id').val($("#autocomplete_c").getSelectedItemData().id);
        }
    }
};

var modal = {
    el: '',
    set: function (id) {
        this.el = $('#' + id);
        return this;
    },
    show: function () {
        this.el.addClass('displayed');
    },
    close: function () {
        $('.modal').removeClass('displayed');
    }
}

$("#autocomplete_c").easyAutocomplete(options);

// Подтягиваем категорию при выборе родительской для поиска
$('#search_category').on('change', function () {
    var value = $(this).val();
    console.log(value);

    $('#search_sub_category').prop('disabled', true);

    var sub_categories = '<option value="0">Искать во всей категории</option>';
    $.getJSON("/api/ad/category/children/" + value, function ( data ) {
        console.log(data.data);
        $.each( data.data, function ( key, val ) {
            sub_categories += "<option value='" + val.id + "'>" + val.name + "</option>";
        } )

        $('#search_sub_category').html(sub_categories);
        $('#search_sub_category').prop('disabled', false);
    });
})