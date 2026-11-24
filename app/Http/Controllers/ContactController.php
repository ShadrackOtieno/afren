<?php
namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Services\Notifier;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show() { return view('public.contact'); }

    public function store(Request $request)
    {
        $d = $request->validate(['name' => 'required|string|max:120', 'email' => 'required|email|max:190', 'organization' => 'nullable|string|max:190',
            'subject' => 'required|string|max:190', 'message' => 'required|string|min:10|max:5000', 'hp' => 'prohibited']);
        unset($d['hp']);
        $m = ContactMessage::create($d);
        Notifier::admins('Contact message: '.$m->subject, "From {$m->name} <{$m->email}>".($m->organization ? " ({$m->organization})" : '')."\n\n{$m->message}");
        Notifier::to($m->email, 'We received your message', "Hello {$m->name}, thanks for contacting AFREN. We will reply to this address shortly.");
        return back()->with('status', 'Thank you. Your message has been sent and we will reply by email.');
    }
}
