<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Mail\ShopAdminMessage;
use App\Services\SiteContactEmailFinder;
use App\ShopLead;
use App\ShopLeadMessage;
use App\ShopMessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 * Кандидати в магазини: адмін вручну додає інтернет-магазини (назва, сайт),
 * система шукає контактний email на сайті самого магазину, адмін надсилає
 * персональний лист-запрошення з сайту (по одному, з журналом) і веде статус.
 */
class ShopLeadController extends Controller
{
    /** Шаблон із «Шаблони листів», що підставляється за замовчуванням */
    const INVITATION_TEMPLATE = 'Запрошення для кандидатів';

    public function index(Request $request)
    {
        $status = $request->get('status');

        $leads = ShopLead::with('messages')->when($status && isset(ShopLead::STATUSES[$status]), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderByRaw("FIELD(status, 'replied', 'new', 'contacted', 'joined', 'declined')")
            ->orderByDesc('created_at')
            ->paginate(50)
            ->appends(['status' => $status]);

        return view('admin.shops.leads', [
            'leads' => $leads,
            'status' => $status,
            'counts' => ShopLead::selectRaw('status, COUNT(*) AS n')->groupBy('status')->pluck('n', 'status'),
            'messageTemplates' => ShopMessageTemplate::orderBy('name')->get(),
            'defaultTemplate' => ShopMessageTemplate::where('name', self::INVITATION_TEMPLATE)->first(),
        ]);
    }

    public function store(Request $request, SiteContactEmailFinder $finder)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'site_url' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ], [
            'name.required' => 'Вкажіть назву магазину',
            'site_url.required' => 'Вкажіть сайт магазину',
            'email.email' => 'Некоректний email',
        ]);

        $siteUrl = preg_match('#^https?://#i', $data['site_url']) ? $data['site_url'] : 'https://' . $data['site_url'];
        $domain = ShopLead::domainOf($siteUrl);

        if (!$domain) {
            return back()->withInput()->withErrors(['site_url' => 'Не вдалося розпізнати домен сайту']);
        }
        if ($existing = ShopLead::where('domain', $domain)->first()) {
            return back()->withInput()->withErrors(['site_url' => "Магазин з доменом {$domain} уже в списку: «{$existing->name}»"]);
        }

        $lead = new ShopLead([
            'name' => $data['name'],
            'site_url' => $siteUrl,
            'domain' => $domain,
            'category' => $data['category'] ?? null,
            'status' => 'new',
        ]);

        if (!empty($data['email'])) {
            $lead->email = $data['email'];
            $lead->email_source = 'manual';
        } else {
            $this->lookupEmail($lead, $finder);
        }
        $lead->save();

        $message = "Додано «{$lead->name}».";
        if (!$lead->email) {
            $message .= ' Email на сайті не знайдено — додайте вручну.';
        }
        if ($shop = $lead->existingShop()) {
            $message .= " Увага: магазин з цим доменом уже є на addnew (ID {$shop->id}).";
        }

        return redirect(route('admin.shops.leads'))->with('success', $message);
    }

    public function update(Request $request, $id)
    {
        $lead = ShopLead::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(ShopLead::STATUSES)),
            'email' => 'nullable|email|max:255',
            'note' => 'nullable|string|max:2000',
        ]);

        if (($data['email'] ?? null) !== $lead->email) {
            $lead->email = $data['email'] ?? null;
            $lead->email_source = $lead->email ? 'manual' : null;
        }
        if ($data['status'] === 'contacted' && !$lead->contacted_at) {
            $lead->contacted_at = now();
        }
        $lead->status = $data['status'];
        $lead->note = $data['note'] ?? null;
        $lead->save();

        return redirect(route('admin.shops.leads', ['status' => $request->get('return_status')]))->with('success', "«{$lead->name}» оновлено.");
    }

    public function lookup($id, SiteContactEmailFinder $finder)
    {
        $lead = ShopLead::findOrFail($id);
        $this->lookupEmail($lead, $finder);
        $lead->save();

        $message = $lead->email ? "Знайдено email: {$lead->email}" : ($lead->email_lookup_status === 'unreachable' ? 'Сайт не відповідає' : 'Email на сайті не знайдено');

        return back()->with('success', "«{$lead->name}»: {$message}");
    }

    /**
     * Надіслати лист-запрошення одному кандидату — тим самим механізмом,
     * що й листи магазинам в «Опис магазину» (ShopAdminMessage), із журналом.
     */
    public function send($id, Request $request)
    {
        $lead = ShopLead::findOrFail($id);

        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
        ], [
            'subject.required' => 'Введіть тему листа',
            'body.required' => 'Введіть текст листа',
        ]);

        if (!$lead->email) {
            return back()->with('error', "У «{$lead->name}» немає email — спершу знайдіть або вкажіть його.");
        }
        if (in_array($lead->status, ['joined', 'declined'], true) || $lead->existingShop()) {
            return back()->with('error', "«{$lead->name}» уже підключився або відмовився — лист не надіслано.");
        }

        $sentOk = true;
        $error = null;
        try {
            Mail::to($lead->email)->send(new ShopAdminMessage($data['subject'], $data['body']));
        } catch (\Throwable $e) {
            $sentOk = false;
            $error = $e->getMessage();
        }

        ShopLeadMessage::create([
            'shop_lead_id' => $lead->id,
            'admin_user_id' => Auth::id(),
            'subject' => $data['subject'],
            'body' => $data['body'],
            'sent_to_email' => $lead->email,
            'sent_successfully' => $sentOk,
            'error_message' => $error,
        ]);

        if (!$sentOk) {
            return back()->with('error', "Не вдалося надіслати лист «{$lead->name}»: {$error}");
        }

        if ($lead->status === 'new') {
            $lead->status = 'contacted';
        }
        $lead->contacted_at = $lead->contacted_at ?: now();
        $lead->save();

        return back()->with('success', "Лист надіслано «{$lead->name}» ({$lead->email}).");
    }

    public function destroy($id)
    {
        $lead = ShopLead::findOrFail($id);
        $lead->delete();

        return back()->with('success', "«{$lead->name}» видалено зі списку.");
    }

    protected function lookupEmail(ShopLead $lead, SiteContactEmailFinder $finder): void
    {
        $result = $finder->find($lead->site_url);
        $lead->email_lookup_status = $result['status'];
        if ($result['email']) {
            $lead->email = $result['email'];
            $lead->email_source = 'site';
        }
    }
}
