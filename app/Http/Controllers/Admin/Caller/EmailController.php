<?php

namespace App\Http\Controllers\Admin\Caller;

use App\Http\Controllers\Controller;
use App\Models\Caller\InstructionEmail;
use App\Services\Caller\CallerSettings;
use App\Services\Caller\InstructionMailer;
use App\Services\Caller\Util;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'review');
        $q = InstructionEmail::with('lead')->orderByDesc('id');
        if ($status !== 'all') {
            $q->where('status', $status);
        }
        $counts = InstructionEmail::selectRaw('status, COUNT(*) n')->groupBy('status')->pluck('n', 'status')->toArray();

        return view('admin.caller.emails', [
            'emails' => $q->paginate(50)->appends($request->query()),
            'status' => $status,
            'counts' => $counts,
            'statuses' => InstructionEmail::STATUSES,
            'settings' => CallerSettings::all(),
        ]);
    }

    /** Попередній перегляд листа так, як його побачить магазин. */
    public function preview($id, InstructionMailer $mailer)
    {
        $row = InstructionEmail::with('lead')->findOrFail($id);
        $mail = $mailer->mailable($row, $row->lead, CallerSettings::all());
        return $mail->render();
    }

    public function approve($id, Request $request)
    {
        $row = InstructionEmail::findOrFail($id);
        $to = mb_strtolower(trim((string) $request->get('to_email', $row->to_email)));
        if (!Util::isValidEmail($to)) {
            return back()->withErrors(['to_email' => "Невалідна адреса: {$to}"]);
        }
        if (!in_array($row->status, ['review', 'failed'], true)) {
            return back()->withErrors(['to_email' => 'Цей лист уже не на перевірці.']);
        }
        $row->update(['to_email' => $to, 'status' => 'pending', 'reason' => null, 'attempts' => 0, 'last_error' => null]);
        if ($row->lead && $row->lead->email !== $to) {
            $row->lead->update(['email' => $to]);
        }
        return back()->with('success', "Лист #{$row->id} схвалено → {$to}. Піде протягом 5 хв.");
    }

    public function skip($id)
    {
        InstructionEmail::whereIn('status', ['review', 'pending', 'failed'])->findOrFail($id)->update(['status' => 'skipped']);
        return back()->with('success', 'Лист скасовано.');
    }

    public function sendNow(InstructionMailer $mailer)
    {
        $settings = CallerSettings::all();
        if (empty($settings['email_enabled'])) {
            return back()->withErrors(['email' => 'Розсилку вимкнено в налаштуваннях.']);
        }
        $n = $mailer->sendPending($settings);
        return back()->with('success', "Надіслано: {$n}");
    }

    public function unsubscribe(Request $request, InstructionMailer $mailer)
    {
        $request->validate(['key' => 'required|string|max:255'], ['key.required' => 'Вкажіть email або телефон']);
        $n = $mailer->unsubscribe($request->get('key'));
        return $n
            ? back()->with('success', "Відписано: {$n}. Більше ні листів, ні дзвінків.")
            : back()->withErrors(['key' => 'Не знайдено ліда з таким email чи телефоном.']);
    }
}
