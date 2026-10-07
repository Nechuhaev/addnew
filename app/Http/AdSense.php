<?php

namespace App\Http;

class AdSense {
    // Адаптивний блок для мобільних замість фіксованих 728×90 (ті ширші за екран телефону)
    const MOBILE_SLOT = '4313028875';
    const CLIENT = 'ca-pub-1409649052535600';
    const MOBILE_MAX_WIDTH = 767;

    public static function block($position) {
        if (env('APP_ENV') == 'production') {
            echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1409649052535600" crossorigin="anonymous"></script>';
            switch ($position) {
                case 'top':
                    echo self::adaptiveBanner('8755878337');
                    return;
                case 'home-vertical':
                    echo '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-1409649052535600" data-ad-slot="7184113110" data-ad-format="auto" data-full-width-responsive="true"></ins>';
                    break;
                case 'bottom':
                    echo '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-1409649052535600" data-ad-slot="7549602288" data-ad-format="auto" data-full-width-responsive="true"></ins>';
                    break;
                case 'top-listing':
                    echo self::adaptiveBanner('1885708977');
                    return;
                case 'category-right':
                    echo '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-1409649052535600" data-ad-slot="5421684172" data-ad-format="auto" data-full-width-responsive="true"></ins>';
                    break;
                case 'bottom-listing':
                    echo self::adaptiveBanner('3362442171');
                    return;
                case 'ad-left':
                    echo '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-1409649052535600" data-ad-slot="1997102577" data-ad-format="auto" data-full-width-responsive="true"></ins>';
                    break;
                case 'ad-middle':
                    echo '<ins class="adsbygoogle" style="display:block; text-align:center;" data-ad-layout="in-article" data-ad-format="fluid" data-ad-client="ca-pub-1409649052535600" data-ad-slot="2159424362"></ins>';
                    break;
                case 'ad-after-product':
                    echo '<ins class="adsbygoogle" style="display:block; text-align:center;" data-ad-layout="in-article" data-ad-format="fluid" data-ad-client="ca-pub-1409649052535600" data-ad-slot="2159424362"></ins>';
                    break;
            }
            echo '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
        }
    }

    /**
     * Банер 728×90 на десктопі й адаптивний блок на екранах до 768px.
     * Виводимо обидва <ins> без класу adsbygoogle, і скрипт ще до запиту
     * реклами залишає лише потрібний — тож AdSense отримує запит на один слот.
     */
    protected static function adaptiveBanner(string $desktopSlot): string
    {
        return '<div class="adsense-adaptive">'
            . '<ins data-ad-variant="desktop" style="display:none;width:728px;height:90px" data-ad-client="' . self::CLIENT . '" data-ad-slot="' . $desktopSlot . '"></ins>'
            . '<ins data-ad-variant="mobile" style="display:none" data-ad-client="' . self::CLIENT . '" data-ad-slot="' . self::MOBILE_SLOT . '" data-ad-format="auto" data-full-width-responsive="true"></ins>'
            . '</div>'
            . '<script>(function(){var w=document.currentScript.previousElementSibling;'
            . 'var mobile=window.matchMedia("(max-width: ' . self::MOBILE_MAX_WIDTH . 'px)").matches;'
            . 'var keep=w.querySelector("[data-ad-variant=" + (mobile ? "mobile" : "desktop") + "]");'
            . 'var drop=w.querySelector("[data-ad-variant=" + (mobile ? "desktop" : "mobile") + "]");'
            . 'drop.parentNode.removeChild(drop);keep.className="adsbygoogle";'
            . 'keep.style.display=mobile ? "block" : "inline-block";'
            . '(adsbygoogle=window.adsbygoogle||[]).push({});})();</script>';
    }
}
