# AI-дзвінки інтернет-магазинам: деплой і налаштування

Модуль у складі сайту: голосова асистентка «Марія» (ElevenLabs Agents + Claude) дзвонить інтернет-магазинам, пропонує безкоштовне розміщення на Addnew.biz, а після розмови автоматично надсилає інструкцію тим, хто погодився. Керування — в адмінці: **/admin/caller** (меню «AI-дзвінки»).

## Що входить

| Частина | Файли |
|---|---|
| Таблиці | `database/migrations/2026_11_25_100000_create_caller_tables.php` (`caller_leads`, `caller_calls`, `caller_do_not_call`, `caller_emails`, `caller_settings`) |
| Конфіг | `config/caller.php` |
| Логіка | `app/Services/Caller/*`, моделі `app/Models/Caller/*` |
| Команди | `caller:run` (щохв), `caller:send-emails` (кожні 5 хв), `caller:track-conversions` (щодня), `caller:import {file}`, `caller:unsubscribe {email/телефон}` — розклад у `app/Console/Kernel.php` |
| Вебхук | `POST /webhooks/elevenlabs` → `app/Http/Controllers/API/ElevenLabsWebhookController.php` (HMAC-підпис, без CSRF) |
| Адмінка | `app/Http/Controllers/Admin/Caller/*`, `resources/views/admin/caller/*`, пункт меню в `resources/views/admin/menu.blade.php` |
| Лист | `app/Mail/CallerInstruction.php`, `resources/views/mail/caller-instruction*.blade.php` |
| Промпт агента | `resources/caller/system_prompt.md`, `first_message.txt`, сценарій для людей `call_script.md` |
| Тести | `tests/Unit/CallerTest.php` |

## Як це працює на shared-хостингу

Системного cron немає, тому все йде через наявний `/system/cron-runner/{token}` → `schedule:run` (щохвилини). Довгих процесів і воркерів черг модуль не потребує:

1. `caller:run` — спершу аналізує прийняті розмови (Claude), потім ініціює нові дзвінки в межах лімітів і робочого часу.
2. Вебхук ElevenLabs лише перевіряє підпис і зберігає транскрипт (відповідь за мілісекунди), аналіз робить наступний `caller:run`.
3. `caller:send-emails` відправляє листи через звичайну пошту сайту (`MAIL_*`).

## Деплой

1. Залити файли з гілки `feature/ai-caller` (структура = шляхи на сервері в `/home/addnew/addnew.biz/www/`).
2. Додати в `.env` (секрети — лише тут):

```
# Claude — ключ спільний із рештою сайту, модель для дзвінків окрема
ANTHROPIC_API_KEY=...            # уже є
CALLER_CLAUDE_MODEL=claude-opus-5-5

# ElevenLabs Agents
ELEVENLABS_API_KEY=
ELEVENLABS_AGENT_ID=
ELEVENLABS_PHONE_NUMBER_ID=      # номер, імпортований із SIP-транку
ELEVENLABS_WEBHOOK_SECRET=       # видається при створенні post-call вебхука

# Необов'язково: стартові значення налаштувань (далі все міняється в адмінці)
CALLER_ENABLED=false
CALLER_DRY_RUN=true
CALLER_EMAIL_ENABLED=false
CALLER_EMAIL_FROM_ADDRESS=       # порожньо = MAIL_FROM_ADDRESS
CALLER_EMAIL_REPLY_TO=addnew.feedback@gmail.com
```

3. Міграція, автозавантажувач, кеші:

```
php7.4 artisan migrate
php7.4 /usr/local/bin/composer dump-autoload
php7.4 artisan config:clear
php7.4 artisan route:clear
php7.4 artisan view:clear
```

4. Перевірка: `/admin/caller` → блок «Не задано в .env» має зникнути. Тести (якщо на сервері встановлено dev-залежності):

```
php7.4 vendor/bin/phpunit --filter CallerTest
```

## Налаштування зовнішніх сервісів

**Zadarma (або інший український SIP-оператор):** український номер, SIP-транк, Caller ID — цей номер.

**ElevenLabs Agents:**
1. Створити агента: мова Ukrainian, український голос, LLM — Claude Sonnet 5.5 (для порівняння протестуйте Claude Opus 5.5).
2. System prompt і First message — скопіювати з **/admin/caller/settings** (кнопки «Копіювати»).
3. Dynamic variables: `store_name`, `store_site`, `contact_name`, `category`.
4. System tools: End call, Voicemail detection (якщо є у тарифі). Максимальна тривалість розмови — 4 хв.
5. Phone Numbers → Import → From SIP trunk: адреса оператора, TLS або TCP, Digest auth. `phone_number_id` → `.env`.
6. Webhooks → Post-call (подія transcript): URL з **/admin/caller/settings** (`https://addnew.biz/webhooks/elevenlabs`), секрет → `ELEVENLABS_WEBHOOK_SECRET`.

## Запуск по кроках

1. **Тренажер** (/admin/caller/simulator) — прогнати складні сценарії текстом, поправити промпт.
2. **Ліди** — імпорт CSV (`phone, store_name, store_site, contact_name, category`, приклад: `resources/caller/leads.example.csv`). Магазини, чий сайт уже зареєстрований на addnew.biz, пропускаються автоматично.
3. **Налаштування:** увімкнути автонабір, лишити «Тестовий режим» — у `storage/logs/caller.log` видно, кому подзвонив би.
4. Додати лід зі своїм номером → «Подзвонити зараз» (з вимкненим тестовим режимом) → перевірити розмову, транскрипт і аналіз на сторінці ліда.
5. Вимкнути тестовий режим. Розсилку увімкнути, коли перевірите кілька листів через «Перегляд» на сторінці «Листи».

## Запобіжники

- Бот одразу каже, що він віртуальна асистентка; «не дзвоніть» → номер назавжди в `caller_do_not_call` (і листи скасовуються).
- Робочі дні й години, денний ліміт, кількість ліній і спроб — в адмінці; поза робочим часом `caller:run` нічого не набирає.
- Один лист на магазин; сумнівні/невалідні адреси і домени зі списку «Запрещенные email» (`blocked_emails`) — на ручну перевірку; адреси вже зареєстрованих користувачів — пропускаються.
- Ліміт листів на годину; 3 спроби при помилці SMTP; відписка — на сторінці «Листи» або `caller:unsubscribe`.
- Claude позначає порушення сценарію ботом (`quality_flags`) — видно на дашборді.

## Логи

`storage/logs/caller.log`, `storage/logs/caller-emails.log`, вебхук — у `storage/logs/laravel*.log` з префіксом `[caller-webhook]`.
