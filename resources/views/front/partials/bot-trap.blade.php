{{-- Пастка для ботів (App\Services\RegistrationGuard): поле приховане від людей, мітка часу підписана --}}
<div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true">
    <label>Website <input type="text" name="{{ \App\Services\RegistrationGuard::TRAP_FIELD }}" value="" tabindex="-1" autocomplete="off"></label>
</div>
<input type="hidden" name="{{ \App\Services\RegistrationGuard::TS_FIELD }}" value="{{ \App\Services\RegistrationGuard::token() }}">
