{{-- Автооновлення товарів з фіда за URL. $feed (App\ShopFeed|null), $feedRuns, $defaultCategory, $defaultCity --}}
@php
    $ru = app()->getLocale() === 'ru';
    $t = function ($uk, $r) use ($ru) { return $ru ? $r : $uk; };
    $statusText = [
        'idle' => $t('ще не оновлювався', 'ещё не обновлялся'),
        'running' => $t('оновлюється…', 'обновляется…'),
        'ok' => $t('успішно', 'успешно'),
        'failed' => $t('помилка', 'ошибка'),
    ];
    $runStatus = [
        'completed' => $t('готово', 'готово'),
        'failed' => $t('помилка', 'ошибка'),
        'processing' => $t('триває', 'идёт'),
        'cancelled' => $t('скасовано', 'отменено'),
        'pending' => $t('у черзі', 'в очереди'),
    ];
    $dt = function ($d) { return $d ? $d->format('d.m.Y H:i') : '—'; };
@endphp
<section class="feed-card" id="feed">
    <h2 class="feed-card__h">{{ $t('Автооновлення з фіда за посиланням', 'Автообновление из фида по ссылке') }}</h2>
    <p class="feed-card__intro">{{ $t('Вкажіть посилання на ваш YML (Prom.ua, Rozetka), Google Merchant XML або CSV — ми самі оновлюватимемо ціни, наявність, описи й фото. Товари, яких більше немає у фіді, позначимо «немає в наявності».', 'Укажите ссылку на ваш YML (Prom.ua, Rozetka), Google Merchant XML или CSV — мы сами будем обновлять цены, наличие, описания и фото. Товары, которых больше нет в фиде, отметим «нет в наличии».') }}</p>

    @if(session('feed_success'))
        <div class="alert success">{{ session('feed_success') }}</div>
    @endif
    @if($errors->hasAny(['url', 'frequency_hours', 'category_id', 'city_id']))
        <div class="alert">
            @foreach(['url', 'frequency_hours', 'category_id', 'city_id'] as $f)
                @foreach($errors->get($f) as $msg)<div>{{ $msg }}</div>@endforeach
            @endforeach
        </div>
    @endif

    @if($feed)
        <div class="feed-card__status feed-card__status--{{ $feed->isRunning() ? 'running' : $feed->status }}">
            <div><span>{{ $t('Статус', 'Статус') }}:</span> <strong>{{ $feed->isRunning() ? $statusText['running'] : ($statusText[$feed->status] ?? $feed->status) }}</strong>@if(!$feed->enabled) · {{ $t('вимкнено', 'выключено') }}@endif</div>
            <div><span>{{ $t('Останнє оновлення', 'Последнее обновление') }}:</span> {{ $dt($feed->last_run_at) }}</div>
            <div><span>{{ $t('Наступне', 'Следующее') }}:</span> {{ $feed->enabled ? $dt($feed->next_run_at && $feed->next_run_at->isPast() ? now() : $feed->next_run_at) : '—' }}</div>
            @if($feed->status === 'failed' && $feed->last_error)
                <div class="feed-card__error">{{ $feed->last_error }}@if($feed->fail_count > 1) ({{ $t('невдач поспіль', 'неудач подряд') }}: {{ $feed->fail_count }})@endif</div>
            @endif
        </div>
    @endif

    <form method="post" action="{{ route('profile.shop.feed.save') }}" class="feed-card__form">
        @csrf
        <label class="feed-card__field">
            <span>{{ $t('Посилання на фід', 'Ссылка на фид') }}</span>
            <div class="feed-card__url">
                <input type="url" name="url" id="feed_url" required maxlength="1000" inputmode="url"
                       value="{{ old('url', optional($feed)->url) }}" placeholder="https://example.com/feed.xml">
                <button type="button" class="btn btn-small feed-card__check" id="feed_check">{{ $t('Перевірити', 'Проверить') }}</button>
            </div>
        </label>
        <div class="feed-card__check-result" id="feed_check_result" role="status" aria-live="polite" hidden></div>

        <div class="feed-card__row">
            <label class="feed-card__field feed-card__field--freq">
                <span>{{ $t('Оновлювати', 'Обновлять') }}</span>
                <select name="frequency_hours">
                    @foreach(\App\ShopFeed::FREQUENCIES as $h)
                        <option value="{{ $h }}" {{ (int) old('frequency_hours', optional($feed)->frequency_hours ?? 24) === $h ? 'selected' : '' }}>{{ $t('кожні', 'каждые') }} {{ $h }} {{ $t('год', 'ч') }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        @include('front.partials.import-targets', ['prefix' => 'feed_', 'selCategory' => old('category_id', optional($feed)->category_id ?? $defaultCategory), 'selCity' => old('city_id', optional($feed)->city_id ?? $defaultCity)])

        <label class="feed-card__enabled">
            <input type="hidden" name="enabled" value="0">
            <input type="checkbox" name="enabled" value="1" {{ old('enabled', optional($feed)->enabled ?? true) ? 'checked' : '' }}>
            <span>{{ $t('Автооновлення увімкнено', 'Автообновление включено') }}</span>
        </label>

        <div class="feed-card__actions">
            <button type="submit" class="btn btn-success">{{ $t('Зберегти', 'Сохранить') }}</button>
        </div>
    </form>

    @if($feed)
        <div class="feed-card__actions feed-card__actions--secondary">
            <form method="post" action="{{ route('profile.shop.feed.run') }}">
                @csrf
                <button type="submit" class="btn btn-small" {{ $feed->isRunning() ? 'disabled' : '' }}>{{ $t('Оновити зараз', 'Обновить сейчас') }}</button>
            </form>
            <form method="post" action="{{ route('profile.shop.feed.delete') }}" onsubmit="return confirm('{{ $t('Вимкнути автооновлення і видалити фід? Товари лишаться на сайті.', 'Выключить автообновление и удалить фид? Товары останутся на сайте.') }}');">
                @csrf
                <button type="submit" class="btn btn-small btn-link feed-card__delete">{{ $t('Видалити фід', 'Удалить фид') }}</button>
            </form>
        </div>

        @if($feedRuns->isNotEmpty())
            <h3 class="feed-card__h3">{{ $t('Останні оновлення', 'Последние обновления') }}</h3>
            <div class="feed-runs">
                <div class="feed-runs__head">
                    <span>{{ $t('Коли', 'Когда') }}</span><span>{{ $t('Статус', 'Статус') }}</span><span>{{ $t('У фіді', 'В фиде') }}</span><span>{{ $t('Нових', 'Новых') }}</span><span>{{ $t('Оновлено', 'Обновлено') }}</span><span>{{ $t('Помилок', 'Ошибок') }}</span><span>{{ $t('Зникли', 'Пропали') }}</span>
                </div>
                @foreach($feedRuns as $run)
                    <div class="feed-runs__row feed-runs__row--{{ $run->status }}">
                        <span data-l="{{ $t('Коли', 'Когда') }}">{{ $run->created_at->format('d.m.Y H:i') }}</span>
                        <span data-l="{{ $t('Статус', 'Статус') }}">{{ $runStatus[$run->status] ?? $run->status }}</span>
                        <span data-l="{{ $t('У фіді', 'В фиде') }}">{{ $run->total }}</span>
                        <span data-l="{{ $t('Нових', 'Новых') }}">{{ $run->new_count }}</span>
                        <span data-l="{{ $t('Оновлено', 'Обновлено') }}">{{ $run->update_count }}</span>
                        <span data-l="{{ $t('Помилок', 'Ошибок') }}">{{ $run->error_count }}</span>
                        <span data-l="{{ $t('Зникли', 'Пропали') }}">{{ $run->missing_count }}</span>
                        @if($run->error_message)
                            <div class="feed-runs__error">{{ $run->error_message }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</section>
<script>
(function () {
    var btn = document.getElementById('feed_check'), box = document.getElementById('feed_check_result');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var url = document.getElementById('feed_url').value.trim();
        if (!url) { document.getElementById('feed_url').focus(); return; }
        btn.disabled = true;
        box.hidden = false; box.className = 'feed-card__check-result';
        box.textContent = '{{ $t('Завантажуємо й перевіряємо фід… це може зайняти до хвилини.', 'Загружаем и проверяем фид… это может занять до минуты.') }}';
        var fd = new FormData(); fd.append('url', url);
        fetch('{{ route('profile.shop.feed.check') }}', {
            method: 'POST', body: fd, credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json().then(function (d) { return [r.ok, d]; }); })
          .then(function (res) {
              var ok = res[0], d = res[1];
              box.className = 'feed-card__check-result ' + (ok && d.success ? 'is-ok' : 'is-error');
              box.textContent = '';
              if (ok && d.success) {
                  var head = document.createElement('strong');
                  head.textContent = '✓ ' + d.format + ' — {{ $t('товарів', 'товаров') }}: ' + d.count;
                  box.appendChild(head);
                  (d.sample || []).forEach(function (s) {
                      var row = document.createElement('div');
                      row.textContent = s.title + ' — ' + s.price + ' ' + s.currency;
                      box.appendChild(row);
                  });
              } else {
                  box.textContent = (d && (d.message || (d.errors && d.errors.url && d.errors.url[0]))) || '{{ $t('Не вдалося перевірити фід.', 'Не удалось проверить фид.') }}';
              }
          })
          .catch(function () { box.className = 'feed-card__check-result is-error'; box.textContent = '{{ $t('Помилка мережі. Спробуйте ще раз.', 'Ошибка сети. Попробуйте ещё раз.') }}'; })
          .then(function () { btn.disabled = false; });
    });
})();
</script>
