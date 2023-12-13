<?php

use App\Service\PingPongCheckoutClient;

include './signature.php';

$url = 'https://sandbox-acquirer-payment.pingpongx.com/v4/payment/prePay';

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
        "captureDelayHours" => 0,
        "amount" => 5.23,
        "payResultUrl" => 'https://localhost/ping_pong/success.php',
        "payCancelUrl" => 'https://localhost/ping_pong/failure.php',
        "currency" => "USD",
        "merchantTransactionId" => rand(5,50000),
        'customer' => [
            'firstName' => 'Full',
            'lastName' => 'Tester',
            'email' => 'fullEmail@yopmail.com',
        ],
        'goods' => [
            "imgUrl" => "https://webart.technology/wp-content/themes/webart-technology/assets/images/logo.png",
            'name' => 'Testing Product',
            'unitPrice' => 55,
            'number' => 2
        ]
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

// var_dump($response);exit;

$response = json_decode($response, true);
$bizContent = json_decode($response['bizContent'], true);
// var_dump($bizContent);exit;

header("Location: " . $bizContent['paymentUrl']);exit;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ping Pong</title>
</head>

<body>
    <div>
        <input type="text" id="card-number">
        <input type="text" id="card-cvv">
        <button type="submit"></button>
    </div>
</body>

</html>