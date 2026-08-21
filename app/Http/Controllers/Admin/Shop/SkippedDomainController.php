<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\SkippedDomain;
use Illuminate\Http\Request;

class SkippedDomainController extends Controller
{
    public function index()
    {
        $domains = SkippedDomain::orderBy('created_at', 'desc')->get();

        return view('admin.shops.skipped-domains', ['domains' => $domains]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:monitoring_skipped_domains,domain',
            'note' => 'nullable|string|max:255',
        ], [
            'domain.required' => "Вкажіть домен",
            'domain.unique' => "Цей домен уже в списку",
        ]);

        // Прибираємо протокол/шлях, якщо випадково вставили повний URL
        $domain = preg_replace('#^https?://#', '', $validated['domain']);
        $domain = explode('/', $domain)[0];

        SkippedDomain::create([
            'domain' => $domain,
            'note' => $validated['note'] ?? null,
        ]);

        return redirect(route('admin.shops.skippedDomains'))->with('success', "Домен «{$domain}» додано до списку виключених.");
    }

    public function destroy($id)
    {
        $domain = SkippedDomain::findOrFail($id);
        $domain->delete();

        return redirect(route('admin.shops.skippedDomains'))->with('success', "Домен видалено зі списку.");
    }
}