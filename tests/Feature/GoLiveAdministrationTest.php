<?php
namespace Tests\Feature;use Tests\TestCase;
class GoLiveAdministrationTest extends TestCase{public function test_guest_cannot_open_administration():void{$this->get('/admin')->assertRedirect('/login');}public function test_public_hotel_route_remains_available():void{$this->get('/hotel')->assertStatus(200);}}
