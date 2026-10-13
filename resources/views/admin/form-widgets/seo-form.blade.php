{{--
    Форма SEO-шаблону (адмінка → «SEO тексти» → тип сторінки).
    Змінні з контролерів Admin\Seo\*: $meta_title, $meta_description, $description
    та *_uk. Українська — основна мова сайту, російська — для /ru/.
    Плейсхолдери на кшталт ---city_name--- підставляються на сторінці (список праворуч).
--}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@foreach ([['uk', 'Українська версія (основна)', '_uk'], ['ru', 'Російська версія (/ru/)', '']] as [$seoLang, $seoLabel, $seoSuffix])
    @php
        $seoTitle = old('meta_title' . $seoSuffix, ${'meta_title' . $seoSuffix} ?? '');
        $seoDesc = old('meta_description' . $seoSuffix, ${'meta_description' . $seoSuffix} ?? '');
        $seoText = old('description' . $seoSuffix, ${'description' . $seoSuffix} ?? '');
    @endphp
    <h4 class="card-title {{ $loop->first ? '' : 'mt-4' }}">{{ $seoLabel }}</h4>

    <div class="form-group">
        <label for="meta_title{{ $seoSuffix }}">Meta title
            <small class="text-muted">— до 60 символів видно в Google; зараз <span class="seo-count" data-for="meta_title{{ $seoSuffix }}">{{ mb_strlen((string) $seoTitle) }}</span></small>
        </label>
        <input type="text" maxlength="255" id="meta_title{{ $seoSuffix }}" name="meta_title{{ $seoSuffix }}"
               value="{{ $seoTitle }}" class="form-control form-control-line">
    </div>

    <div class="form-group">
        <label for="meta_description{{ $seoSuffix }}">Meta description
            <small class="text-muted">— 140–160 символів; зараз <span class="seo-count" data-for="meta_description{{ $seoSuffix }}">{{ mb_strlen((string) $seoDesc) }}</span></small>
        </label>
        <textarea rows="3" maxlength="255" id="meta_description{{ $seoSuffix }}" name="meta_description{{ $seoSuffix }}"
                  class="form-control form-control-line">{{ $seoDesc }}</textarea>
    </div>

    <div class="form-group">
        <label for="description{{ $seoSuffix }}">SEO-текст на сторінці</label>
        <textarea rows="10" id="description{{ $seoSuffix }}" name="description{{ $seoSuffix }}"
                  class="content form-control form-control-line">{{ $seoText }}</textarea>
    </div>
@endforeach

<script>
    document.querySelectorAll('.seo-count').forEach(function (counter) {
        var field = document.getElementById(counter.getAttribute('data-for'));
        if (field) {
            field.addEventListener('input', function () { counter.textContent = field.value.length; });
        }
    });
</script>
