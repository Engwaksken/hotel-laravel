<?php
namespace App\Services\FrontDesk;
use App\Enums\{ReservationStatus,RoomStatus};use App\Models\Stay;use App\Services\Folios\FolioService;use App\Services\Operations\HousekeepingService;use Illuminate\Support\Facades\DB;
class CheckOutService{
 public function __construct(private FolioService $folios,private HousekeepingService $housekeeping){}
 public function execute(Stay $stay,int $userId,bool $allowBalance=false):Stay{return DB::transaction(function()use($stay,$userId,$allowBalance){$stay->load('reservation.folio','room');$folio=$stay->reservation?->folio;if($folio){$this->folios->recalculate($folio);if(!$allowBalance&&(float)$folio->fresh()->balance>0.009)abort(422,'Outstanding folio balance must be cleared before checkout.');$folio->update(['status'=>'closed']);}$stay->update(['status'=>'checked_out','checked_out_at'=>now(),'checked_out_by'=>$userId]);$stay->room->update(['status'=>RoomStatus::Dirty,'housekeeping_status'=>'dirty']);$stay->reservation?->update(['status'=>ReservationStatus::CheckedOut]);$this->housekeeping->createCheckoutTask($stay,$userId);return $stay->fresh();});}
}
