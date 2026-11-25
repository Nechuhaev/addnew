<?php

namespace App\Http\Controllers\Admin\Caller;

use App\Http\Controllers\Controller;
use App\Models\Caller\DoNotCall;
use App\Models\Caller\Lead;
use App\Services\Caller\CallerSettings;
use App\Services\Caller\Dialer;
use App\Services\Caller\LeadImporter;
use App\Services\Caller\Util;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $q = Lead::query();
        if ($status = $request->get('status')) {
            $q->where('status', $status);
        }
        if ($outcome = $request->get('outcome')) {
            $q->where('outcome', $outcome);
        }
        if ($request->get('registered')) {
            $q->whereNotNull('registered_user_id');
        }
        if ($s = trim((string) $request->get('q'))) {
            $phone = Util::normalizeUaPhone($s);
            $q->where(function ($w) use ($s, $phone) {
                $w->where('store_name', 'like', "%{$s}%")
                    ->orWhere('store_site', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', '%' . preg_replace('/\D/', '', $s) . '%');
                if ($phone) {
                    $w->orWhere('phone', $phone);
                }
            });
        }

        $leads = $q->orderByDesc('updated_at')->paginate(50)->appends($request->query());

        return view('admin.caller.leads', [
            'leads' => $leads,
            'statuses' => Lead::STATUSES,
            'outcomes' => Lead::OUTCOMES,
        ]);
    }

    public function show($id)
    {
        $lead = Lead::with(['calls', 'emails', 'registeredUser'])->findOrFail($id);
        return view('admin.caller.lead', [
            'lead' => $lead,
            'dnc' => DoNotCall::where('phone', $lead->phone)->first(),
        ]);
    }

    public function import(Request $request, LeadImporter $importer)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240'], [
            'file.required' => 'Оберіть CSV-файл',
            'file.mimes' => 'Потрібен файл .csv',
        ]);
        $s = $importer->importFile($request->file('file')->getRealPath(), 'admin');
        $msg = "Додано: {$s['inserted']}. Уже були: {$s['exists']}. «Не дзвонити»: {$s['dnc']}. "
            . "Уже зареєстровані на сайті: {$s['registered']}. Невалідні: {$s['invalid']}.";
        if ($s['errors']) {
            $msg .= "\n" . implode("\n", array_slice($s['errors'], 0, 20)) . (count($s['errors']) > 20 ? "\n…" : '');
        }
        return redirect()->route('admin.caller.leads')->with('success', $msg);
    }

    public function store(Request $request, LeadImporter $importer)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:32',
            'store_name' => 'required|string|max:250',
            'store_site' => 'nullable|string|max:250',
            'contact_name' => 'nullable|string|max:250',
            'category' => 'nullable|string|max:250',
        ], ['phone.required' => 'Вкажіть телефон', 'store_name.required' => 'Вкажіть назву магазину']);

        $s = $importer->importRows([$data], 'manual');
        if ($s['inserted']) {
            return back()->with('success', 'Лід додано в чергу.');
        }
        $why = $s['errors'][0] ?? ($s['dnc'] ? 'номер у списку «не дзвонити»' : ($s['registered'] ? 'магазин уже зареєстрований на сайті' : 'такий номер уже є'));
        return back()->withErrors(['phone' => 'Не додано: ' . $why])->withInput();
    }

    /** Подзвонити зараз, не чекаючи черги і робочого часу (для тестів на власний номер). */
    public function callNow($id, Dialer $dialer)
    {
        $lead = Lead::findOrFail($id);
        if (DoNotCall::has($lead->phone)) {
            return back()->withErrors(['lead' => 'Номер у списку «не дзвонити».']);
        }
        if ($lead->status === 'calling') {
            return back()->withErrors(['lead' => 'Дзвінок уже йде.']);
        }
        $log = $dialer->tick(CallerSettings::all(), null, true, $lead);
        return back()->with('success', implode("\n", $log) ?: 'Нічого не зроблено (перевірте ліміти).');
    }

    public function requeue($id)
    {
        $lead = Lead::findOrFail($id);
        if (DoNotCall::has($lead->phone)) {
            return back()->withErrors(['lead' => 'Номер у списку «не дзвонити».']);
        }
        $lead->update(['status' => 'retry', 'attempts' => 0, 'next_call_at' => null]);
        return back()->with('success', 'Лід повернуто в чергу.');
    }

    public function dnc($id)
    {
        $lead = Lead::findOrFail($id);
        DoNotCall::add($lead->phone, 'вручну з адмінки');
        return back()->with('success', 'Номер додано до списку «не дзвонити».');
    }

    public function destroy($id)
    {
        Lead::findOrFail($id)->delete();
        return redirect()->route('admin.caller.leads')->with('success', 'Лід видалено (список «не дзвонити» збережено).');
    }

    /** CSV з результатами для Excel / Google Sheets. */
    public function export(Request $request)
    {
        $filename = 'caller-leads-' . date('Y-m-d') . '.csv';
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['phone', 'store_name', 'store_site', 'contact_name', 'status', 'outcome', 'email',
                'has_merchant_feed', 'attempts', 'last_called_at', 'next_call_at', 'registered_at', 'notes']);
            Lead::orderBy('id')->chunk(500, function ($leads) use ($out) {
                foreach ($leads as $l) {
                    fputcsv($out, [$l->phone, $l->store_name, $l->store_site, $l->contact_name, $l->status, $l->outcome,
                        $l->email, $l->has_merchant_feed, $l->attempts, (string) $l->last_called_at,
                        (string) $l->next_call_at, (string) $l->registered_at, $l->notes]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
