<?php

use App\Service\PingPongCheckoutClient;

include './signature.php';
$transactionId = $_GET['transactionId'];

$url = 'https://sandbox-acquirer-payment.pingpongx.com/v4/payment/query';

$headers = array(
    'Content-Type: application/json',
);

$data = array(
    'salt' => 'AC09D35CC78CA5E8491B77C5',
    'accId' => '2023120712300910285520',
    'clientId' => '2023120712300910285',
    'signType' => 'SHA256',
    'version' => '1.0'
);

$salt = 'AC09D35CC78CA5E8491B77C5';
$requestBody = [
    'accId' => '2023120712300910285520',
    'clientId' => '2023120712300910285',
    'signType' => 'MD5',
    'version' => '1.0',
    'bizContent' => [
        "transactionId" => $transactionId,
        "currency" => "INR",
        "merchantTransactionId" => rand(5, 50000)
    ],
];

try {
    $pingPongCheckoutClient = new PingPongCheckoutClient();
    $response = $pingPongCheckoutClient->signRequest($salt, $requestBody);
} catch (\Exception $exception) {
    return $this->writeJson(4000, [], $exception->getMessage());
}

$content = json_encode($response);

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
}

curl_close($ch);

$response = json_decode($response, true);
$bizContent = json_decode($response['bizContent'], true);

var_dump($response, $bizContent);
exit;
