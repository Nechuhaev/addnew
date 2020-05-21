<?php

namespace App\Http;

class AdSense {
    public static function block($position) {
        if (env('APP_ENV') == 'production') {
            echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>';
            switch ($position) {
                case 'top':
                    //echo '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-1409649052535600" data-ad-slot="8755878337" data-ad-format="auto" data-full-width-responsive="true"></ins>';
                    echo '<ins class="adsbygoogle" style="display:inline-block;width:728px;height:90px" data-ad-client="ca-pub-1409649052535600" data-ad-slot="8755878337"></ins>';
                    break;
                case 'home-vertical':
                    echo '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-1409649052535600" data-ad-slot="7184113110" data-ad-format="auto" data-full-width-responsive="true"></ins>';
                    break;
                case 'bottom':
                    echo '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-1409649052535600" data-ad-slot="7549602288" data-ad-format="auto" data-full-width-responsive="true"></ins>';
                    break;
                case 'top-listing':
                    echo '<ins class="adsbygoogle" style="display:inline-block;width:728px;height:90px" data-ad-client="ca-pub-1409649052535600" data-ad-slot="1885708977"></ins>';
                    break;
                case 'category-right':
                    echo '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-1409649052535600" data-ad-slot="5421684172" data-ad-format="auto" data-full-width-responsive="true"></ins>';
                    break;
                case 'bottom-listing':
                    echo '<ins class="adsbygoogle" style="display:inline-block;width:728px;height:90px" data-ad-client="ca-pub-1409649052535600" data-ad-slot="3362442171"></ins>';
                    break;
                case 'ad-left':
                    echo '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-1409649052535600" data-ad-slot="1997102577" data-ad-format="auto" data-full-width-responsive="true"></ins>';
                    break;
                case 'ad-middle':
                    echo '<ins class="adsbygoogle" style="display:block; text-align:center;" data-ad-layout="in-article" data-ad-format="fluid" data-ad-client="ca-pub-1409649052535600" data-ad-slot="2159424362"></ins>';
                    break;
            }
            echo '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
        }
    }
}