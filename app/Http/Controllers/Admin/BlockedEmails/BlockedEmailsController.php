<?php

namespace App\Http\Controllers\Admin\BlockedEmails;

use App\Ad;
use App\Http\Controllers\Controller;
use App\BlockedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BlockedEmailsController extends Controller
{
    public function showEmailInformation(Request $request) {
        $email = BlockedEmail::where('id', (int)$request->id)->first();
        return view('admin.blocked-emails.blocked-email', ['email' => $email]);
    }

    public function showEmailsList(Request $request) {

        $emails = BlockedEmail::all();

        return view('admin.blocked-emails.list', [
            'emails' => $emails,
        ]);
    }

    public function create(Request $request)
    {

        $errors = [
            'mailbox.required' => "Введите почтовый ящик",
        ];
        $request->validate([
            'mailbox' => 'required',
        ], $errors);
        
        $data = $request->all();

        $mailbox = new BlockedEmail;
        $mailbox->mailbox = $data['mailbox'];
        $mailbox->save();

        return redirect(route('admin.blocked-emails'))
            ->with('success', 'Ящик добавлен');
    }

    public function new()
    {
        return view('admin.blocked-emails.new-blocked-email');
    }

    public function delete($id)
    {
        BlockedEmail::find($id)->delete();
        return redirect(route('admin.blocked-emails'))->with('success', 'Ящик удален. Надеюсь, Вам полегчало!');
    }

    public function deleteMany($ids)
    {
        $ids = explode(',', $ids);

        if ($ids) {
            foreach ($ids as $id) {
                BlockedEmail::find($id)->delete();
            }
            return route('admin.blocked-emails');
        }
    }
}
