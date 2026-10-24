<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class StaffNotificationController extends Controller {
    public function read(Request $request,string $notification){$n=$request->user()->notifications()->whereKey($notification)->firstOrFail();$n->markAsRead();$url=$n->data['url']??route('dashboard');return redirect()->to($url);}
    public function readAll(Request $request){$request->user()->unreadNotifications->markAsRead();return back()->with('success','Notifications marked as read.');}
}
