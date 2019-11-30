function init_google_map(title, address) {
    (function (t, a) {
        var address = t;



        //var directionDisplay;
        //var directionsService = new google.maps.DirectionsService();
        var map = null;
        var marker = null;
        var infowindow = null;
        var geocoder = null;
        var redFlag = "https://addnew.biz/wp-content/themes/classipress-child/images/red-flag.png";
        var noLuck = "https://addnew.biz/wp-content/themes/classipress-child/images/gmaps-no-result.gif";
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


        function codeAddress() {
            geocoder.geocode( { 'address': address }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    marker = new google.maps.Marker({
                        map: map,
                        icon: redFlag,
                        //title: title,
                        animation: google.maps.Animation.DROP,
                        position: results[0].geometry.location            });

                    map.setCenter(marker.getPosition());

                    infowindow = new google.maps.InfoWindow({
                        maxWidth: 230,
                        content: contentString,
                        disableAutoPan: false
                    });

                    infowindow.open(map, marker);

                    google.maps.event.addListener(marker, 'click', function() {
                        infowindow.open(map,marker);
                    });

                } else {
                    (function($) {
                        $('#map').html('<div style="height:400px;background: url(' + noLuck + ') no-repeat center center;"><p style="padding:50px 0;text-align:center;">Извините, адрес не найден.</p></div>');
                        return false;
                    })($);
                }
            });
        }


        map_init();
    })(title, address)

}