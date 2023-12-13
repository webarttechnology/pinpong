<?php

namespace App\Service;

/**
 * This class is used to sign the request content to ensure the security of the request.
 */
class PingPongCheckoutClient
{
    const SignAlgorithm = ['MD5', 'SHA256']; // Signature encryption methods

    /**
     * Sign the request content and add the signature result to the request parameters.
     *
     * @param string $salt
     * @param array $requestBody
     *
     * @return array
     */
    public function signRequest(string $salt, array $requestBody)
    {
        // Parameter validation
        $this->validate($requestBody);
        // Concatenate request parameters
        $signContent = $this->signContent($salt, $requestBody);
        $sign = $this->getSign($signContent, $requestBody);
        $requestBody['sign'] = strtoupper($sign);
        if (is_array($requestBody['bizContent'])) {
            $requestBody['bizContent'] = json_encode($requestBody['bizContent'], JSON_UNESCAPED_SLASHES);
        }
        return $requestBody;
    }

    /**
     * Concatenate request parameters.
     *
     * @param string $salt
     * @param array $requestBody
     * @return array|string
     */
    public function signContent(string $salt, array $requestBody)
    {
        unset($requestBody['sign']);
        if (is_array($requestBody['bizContent'])) {
            $requestBody['bizContent'] = json_encode($requestBody['bizContent']);
        }
        // Sort by key
        ksort($requestBody);
        $signContent = stripslashes(urldecode(http_build_query($requestBody)));
        $signContent = $salt . $signContent;
        return $signContent;
    }

    /**
     * Get the signature result of the request content.
     *
     * @param string $signContent
     * @param array $requestBody
     * @return array|string
     */
    public function getSign(string $signContent, array $requestBody)
    {
        switch ($requestBody['signType']) {
            case "MD5":
                $sign = $this->md5Sign($signContent);
                break;
            case "SHA256":
                $sign = $this->sha256($signContent);
                break;
        }
        return $sign;
    }

    /**
     * MD5 encryption.
     *
     * @param string $signContent
     * @return string
     */
    public function md5Sign(string $signContent)
    {
        return md5($signContent);
    }

    /**
     * SHA256 encryption.
     *
     * @param string $signContent
     * @return string
     */
    public function sha256(string $signContent)
    {
        return hash("sha256", $signContent);
    }

    /**
     * Validate request parameters.
     *
     * @param array $requestBody
     * @return true
     * @throws \Exception
     */
    public function validate(array $requestBody)
    {
        $keys = ['accId', 'clientId', 'signType', 'version', 'bizContent'];
        foreach ($keys as $key) {
            if (!isset($requestBody[$key]) || empty($requestBody[$key])) {
                throw new \Exception('invalid -' . $key);
            }
        }
        if (!in_array($requestBody['signType'], self::SignAlgorithm)) {
            throw new \Exception('invalid - signType');
        }
        if (!is_array($requestBody['bizContent']) && !is_string($requestBody['bizContent'])) {
            throw new \Exception('invalid - bizContent');
        }
        return true;
    }

    /*
     * Example of usage
     * */
    public function usage()
    {
        $salt = 'BCE27A281029F5EAF3CC0E82';
        $requestBody = [
            'accId' => '2018092714313010016289',
            'clientId' => '2018092714313010016',
            'signType' => 'MD5',
            'version' => '1.0',
            'bizContent' => [
                "merchantTransactionId" => "10019",
                "trackingNumber" => "423431241",
                "logisticsCompany" => "sf",
                "logisticsCompanyUrl" => "https://www.baidu.com"
            ],
        ];
        try {
            $pingPongCheckoutClient = new \App\Service\PingPongCheckoutClient();
            $response = $pingPongCheckoutClient->signRequest($salt, $requestBody);
        } catch (\Exception $exception) {
            return $this->writeJson(4000, [], $exception->getMessage());
        }
        return json_encode($response);
    }

    /**
     * Write JSON response.
     *
     * @param int $statusCode
     * @param array $data
     * @param string $message
     *
     * @return string
     */
    public function writeJson(int $statusCode, array $data, string $message)
    {
        $response = [
            'status' => $statusCode,
            'data' => $data,
            'message' => $message,
        ];

        return json_encode($response);
    }

    /*
     * Constructing a cURL request demo
     * */
    public function curl()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sandbox-acquirer-payment.pingpongx.com/v4/logistics/upload',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "accId": "2018092714313010016289",
                "clientId": "2018092714313010016",
                "signType": "MD5",
                "sign": "055AD0B08E3A92A91475E24E1F0C005F",
                "version": "1.0",
                "bizContent": "{\"merchantTransactionId\":\"10019\",\"trackingNumber\":\"423431241\",\"logisticsCompany\":\"sf\",\"logisticsCompanyUrl\":\"https://www.baidu.com\"}"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: SESSION=MWZiMjU3OTMtNzhjZi00MmJhLThhMmQtODUwMzk3OTkzZDIz'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
    }
}
