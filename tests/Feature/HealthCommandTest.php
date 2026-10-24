<?php
namespace Tests\Feature;use Illuminate\Foundation\Testing\RefreshDatabase;use Tests\TestCase;
class HealthCommandTest extends TestCase{use RefreshDatabase;public function test_health_command_runs():void{$this->artisan('hotel:health-check')->assertExitCode(0);}}
