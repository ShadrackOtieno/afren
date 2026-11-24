<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class MessageController extends Controller
{
    public function index() { return view('admin.messages', ['messages' => ContactMessage::orderByRaw("status = 'new' desc")->latest()->paginate(30)]); }

    public function handle(ContactMessage $message)
    {
        $message->update(['status' => $message->status === 'new' ? 'handled' : 'new']);
        return back()->with('status', 'Message updated.');
    }
}
