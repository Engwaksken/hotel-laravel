<?php
namespace App\Http\Controllers\Api\V1\Guest;
use App\Http\Controllers\Controller; use App\Models\{Folio,Payment}; use Illuminate\Http\Request;
class GuestDocumentsController extends Controller{public function invoice(Request $r,Folio $folio){abort_unless($r->user()->role==='guest'&&$r->user()->guest_id===$folio->guest_id,403);return response()->view('guest.invoice',['folio'=>$folio->load(['items','payments','guest','property'])]);}public function receipt(Request $r,Payment $payment){abort_unless($r->user()->role==='guest'&&$r->user()->guest_id===$payment->guest_id,403);return response()->view('guest.receipt',['payment'=>$payment]);}}
