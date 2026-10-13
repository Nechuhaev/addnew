{{-- «Часті запитання» + FAQPage JSON-LD. $faq = [[питання, відповідь], ...] (App\Services\GeoFaq) --}}
@if(!empty($faq))
    <section class="faq-block" style="margin: 30px 0;">
        <h2 style="margin-bottom: 12px;">{{ app()->getLocale() === 'ru' ? 'Частые вопросы' : 'Часті запитання' }}</h2>
        @foreach($faq as [$q, $a])
            <div class="faq-item" style="padding: 12px 0; border-top: 1px solid #e5e5e5;">
                <h3 style="font-size: 16px; font-weight: 700; margin: 0 0 6px;">{{ $q }}</h3>
                <p style="margin: 0; line-height: 1.5;">{{ $a }}</p>
            </div>
        @endforeach
    </section>
    <script type="application/ld+json">{!! json_encode(\App\Services\GeoFaq::schema($faq), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endif
