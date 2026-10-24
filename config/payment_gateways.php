<?php
return [
 'mtn_momo'=>['base_url'=>env('MTN_MOMO_BASE_URL','https://sandbox.momodeveloper.mtn.com'),'subscription_key'=>env('MTN_MOMO_SUBSCRIPTION_KEY'),'api_user'=>env('MTN_MOMO_API_USER'),'api_key'=>env('MTN_MOMO_API_KEY'),'target_environment'=>env('MTN_MOMO_TARGET_ENVIRONMENT','sandbox')],
 'airtel_momo'=>['base_url'=>env('AIRTEL_MONEY_BASE_URL','https://openapiuat.airtel.africa'),'client_id'=>env('AIRTEL_MONEY_CLIENT_ID'),'client_secret'=>env('AIRTEL_MONEY_CLIENT_SECRET'),'country'=>env('AIRTEL_MONEY_COUNTRY','UG'),'currency'=>env('AIRTEL_MONEY_CURRENCY','UGX')],
 'pesapal'=>['base_url'=>env('PESAPAL_BASE_URL','https://cybqa.pesapal.com/pesapalv3/api'),'consumer_key'=>env('PESAPAL_CONSUMER_KEY'),'consumer_secret'=>env('PESAPAL_CONSUMER_SECRET'),'ipn_id'=>env('PESAPAL_IPN_ID')],
 'flutterwave'=>['base_url'=>env('FLUTTERWAVE_BASE_URL','https://api.flutterwave.com/v3'),'secret_key'=>env('FLUTTERWAVE_SECRET_KEY'),'webhook_secret'=>env('FLUTTERWAVE_WEBHOOK_SECRET')],
];
