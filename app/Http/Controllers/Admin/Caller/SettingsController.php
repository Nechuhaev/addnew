<?php

namespace App\Http\Controllers\Admin\Caller;

use App\Http\Controllers\Controller;
use App\Services\Caller\CallerSettings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.caller.settings', [
            'schema' => CallerSettings::schema(),
            'values' => CallerSettings::all(),
            'health' => CallerSettings::health(),
            'model' => config('caller.claude.model'),
            'webhookUrl' => route('webhooks.elevenlabs'),
            'systemPrompt' => @file_get_contents(resource_path('caller/system_prompt.md')) ?: '',
            'firstMessage' => trim(@file_get_contents(resource_path('caller/first_message.txt')) ?: ''),
        ]);
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach (CallerSettings::schema() as $fields) {
            foreach ($fields as $key => $def) {
                switch ($def[0]) {
                    case 'int':
                        $rules[$key] = "required|integer|min:{$def[3]}|max:{$def[4]}";
                        break;
                    case 'email':
                        $rules[$key] = 'nullable|email|max:255';
                        break;
                    case 'days':
                        $rules[$key] = 'required|array|min:1';
                        $rules[$key . '.*'] = 'integer|between:1,7';
                        break;
                    case 'string':
                        $rules[$key] = 'nullable|string|max:255';
                        break;
                }
            }
        }
        $data = $request->validate($rules, [
            'call_days.required' => 'Оберіть хоча б один день',
            '*.email' => 'Невалідна email-адреса',
        ]);
        if ((int) $data['call_hours_start'] >= (int) $data['call_hours_end']) {
            return back()->withErrors(['call_hours_end' => 'Кінець робочого дня має бути пізніше за початок'])->withInput();
        }

        // Чекбокси: не передані = вимкнено
        foreach (CallerSettings::types() as $key => $type) {
            if ($type === 'bool') {
                $data[$key] = filter_var($request->input($key), FILTER_VALIDATE_BOOLEAN);
            }
        }
        CallerSettings::save($data);
        return redirect()->route('admin.caller.settings')->with('success', 'Налаштування збережено.');
    }
}
