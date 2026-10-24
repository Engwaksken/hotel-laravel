<?php
namespace App\Providers;
use App\Services\Payments\PaymentManager; use App\Services\Payments\Gateways\{AirtelMoneyGateway,FlutterwaveGateway,MtnMomoGateway,PesapalGateway}; use Illuminate\Support\ServiceProvider;
class PaymentServiceProvider extends ServiceProvider{
 public function register():void{$this->app->singleton(PaymentManager::class,function($app){$m=new PaymentManager;return $m->register('mtn_momo',$app->make(MtnMomoGateway::class))->register('airtel_momo',$app->make(AirtelMoneyGateway::class))->register('pesapal',$app->make(PesapalGateway::class))->register('flutterwave',$app->make(FlutterwaveGateway::class))->register('card',$app->make(FlutterwaveGateway::class));});}
}
