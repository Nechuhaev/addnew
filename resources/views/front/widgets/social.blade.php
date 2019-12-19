{{-- Social buttons--}}

<div class="text-center margin-bottom-20" id="uLogin"
     data-ulogin="display=panel;theme=flat;fields=email;
                             providers=facebook,google,vkontakte,twitter,odnoklassniki,mailru;
                             redirect_uri={{ urlencode('http://' . $_SERVER['HTTP_HOST']) }}/ulogin;mobilebuttons=0;">
</div>

@section('script')
    <script src="//ulogin.ru/js/ulogin.js"></script>
@endsection