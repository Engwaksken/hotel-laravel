<?php
namespace Tests\Feature;use App\Models\{Property,Reservation,User};use Illuminate\Foundation\Testing\RefreshDatabase;use Tests\TestCase;
class PropertyIsolationTest extends TestCase{use RefreshDatabase;public function test_reservation_api_does_not_expose_another_property():void{$a=Property::factory()->create();$b=Property::factory()->create();$u=User::factory()->create(['property_id'=>$a->id,'is_active'=>true]);Reservation::factory()->create(['property_id'=>$b->id]);$this->actingAs($u)->getJson('/api/v1/reservations')->assertOk()->assertJsonMissing(['property_id'=>$b->id]);}}
