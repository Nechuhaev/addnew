function init_google_map(title, address) {
    (function (t, a) {
        var address = t;
        //var directionDisplay;
        //var directionsService = new google.maps.DirectionsService();
        var map = null;
        var marker = null;
        var infowindow = null;
        var geocoder = null;
        var redFlag = "https://addnew.biz/images/red-flag.png";
        var noLuck = "https://addnew.biz/images/gmaps-no-result.gif";
        var adTitle = "Частный кредит без предоплаты для серьезных людей";
        var contentString = '<div id="mcwrap"><span>' + a + '</span><br />' + address + '</div>';
        function map_init() {
            $(document).ready(function($) {
                $('#map').hide();
                load();
                $('#map').fadeIn(1000);
                codeAddress();
            });
        }
        function load() {
            geocoder = new google.maps.Geocoder();
            //directionsDisplay = new google.maps.DirectionsRenderer();
            var newyork = new google.maps.LatLng(40.69847032728747, -73.9514422416687);
            var myOptions = {
                zoom: 14,
                center: newyork,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                }
            }
            map = new google.maps.Map(document.getElementById('map'), myOptions);
            //directionsDisplay.setMap(map);
        }
        function showMarker(position) {
            marker = new google.maps.Marker({
                map: map,
                icon: redFlag,
                animation: google.maps.Animation.DROP,
                position: position
            });
            map.setCenter(marker.getPosition());
            infowindow = new google.maps.InfoWindow({
                maxWidth: 230,
                content: contentString,
                disableAutoPan: false
            });
            infowindow.open(map, marker);
            google.maps.event.addListener(marker, 'click', function() {
                infowindow.open(map, marker);
            });
        }
        function hideLocationBlock() {
            // Замість статичного "адрес не найден" — тихо ховаємо весь
            // блок локації разом із заголовком "Расположение:", щоб не
            // лякати користувача повідомленням про помилку.
            (function($) {
                $('#map').closest('.adv-location').hide();
            })($);
        }
        function codeAddress() {
            geocoder.geocode( { 'address': address }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    showMarker(results[0].geometry.location);
                } else if (window.addressFallback) {
                    // Перша спроба (з областю) не вдалась — пробуємо
                    // спрощену адресу (тільки країна + місто).
                    var fallbackAddress = window.addressFallback;
                    window.addressFallback = null;
                    geocoder.geocode( { 'address': fallbackAddress }, function(results2, status2) {
                        if (status2 == google.maps.GeocoderStatus.OK) {
                            showMarker(results2[0].geometry.location);
                        } else {
                            hideLocationBlock();
                        }
                    });
                } else {
                    hideLocationBlock();
                }
            });
        }
        map_init();
    })(title, address)
}