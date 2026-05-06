<?php
$apitoken           = 'ENTER_API_TOKEN_FROM_CLOUDFLARE_HERE';
$cf_account_email   = 'ENTER_YOUR_CLOUDFLARE_EMAIL_HERE';


ini_set("log_errors", 1);
ini_set("error_log", __DIR__."/error.log");


if (php_sapi_name() !== "cli") {
    error_log( "Attempt to access file via web... blocked" );
    die("invalid request");
}

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.cloudflare.com/client/v4/zones/?per_page=200',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer '.$apitoken
  ),
));

$response = curl_exec($curl);
$response = json_decode($response);

$success = $response->success ?: false;
curl_close($curl);

if($success && isset($response->result)){
    foreach($response->result as $zone){
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.cloudflare.com/client/v4/zones/'.$zone->id.'/dns_records/export',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => false,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'X-Auth-Email: ' . $cf_account_email,
            'Authorization: Bearer ' . $apitoken 
            ),
        ));
        
        $response = curl_exec($curl);
        
        if($response){
            // Save the response to a file in the exports directory, named after the zone
            file_put_contents(__DIR__."/exports/".$zone->name.".txt", $response);
        }

    }
    
    curl_close($curl);
}