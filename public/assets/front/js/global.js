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




// Автокомплит для основного ввода
var optionsMain = {
    url: function(phrase) {
        if (phrase.length > 2 && phrase.length < 9 ) {
            return "/api/ad/item/autocomplete/" + phrase;
        }
    },
    getValue: "name",
    list: {
        match: {
            enabled: true
        }
    },
    template: {
		type: "iconLeft",
		fields: {
			iconSrc: "image"
		}
	}
};

$("#autocomplete_i").easyAutocomplete(optionsMain);

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

$('.btn-subscribe-trigger').on('click', function () {
    var btn = $(this);
    btn.css('z-index', '-1');
    btn.css('opacity', '0.7');
    $('.error-holder').text("");
    $.getJSON("https://addnew.biz/subscribe?email=" + $('#subscriber_email').val(), function ( data ) {
        if (data.error) {
            $('.error-holder').text(data.error);
            btn.css('z-index', 'initial');
            btn.css('opacity', 'initial');
        }
        if (data.success) {
            $('.form-subscribe').html("<div>\n" +
                "            Спасибо!<br><br>\n" +
                "            Вы успешно подписались на нашу email рассылку новостей.\n" +
                "        </div>");
        }

        console.log(data);
    });
});

$('.form-control').on("keypress", function() {
    if (this.value.match(/[^а-яА-Яа-яЁёЇїІіЄєҐґa-zA-Z0-9.]/s)) {
        this.value = this.value.replace(/[^а-яА-Яа-яЁёЇїІіЄєҐґa-zA-Z0-9.,]/s, '');
        return false;
    }
});