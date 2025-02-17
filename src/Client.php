<?php

declare(strict_types=1);

namespace FASTJE\EnotIO;

use stdClass;

/**
 * Class Client.
 *
 * @property string $merchantId
 * @property string $secretWord
 * @property string $apiKey
 * @property string $email
 * @property RequestApi $request
 */
class Client
{
    public CONST
        CURRENCY_RUB = 'RUB',
        CURRENCY_USD = 'USD',
        CURRENCY_EUR = 'EUR',
        CURRENCY_UAH = 'UAH';

    public CONST AVAILABLE_CURRENCIES = [
        self::CURRENCY_RUB,
        self::CURRENCY_USD,
        self::CURRENCY_EUR,
        self::CURRENCY_UAH,
    ];

    protected CONST API_HOST = 'https://enot.io';

    /**
     * @var string $merchant_id
     */
    protected $merchantId;

    /**
     * @var string $secret_word
     */
    protected $secretWord;

    /**
     * @var string $api_key
     */
    protected $apiKey;

    /**
     * @var string $email
     */
    protected $email;

    /**
     * @var RequestApi $request
     */
    private $request;

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->$merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getSecretWord(): string
    {
        return $this->secretWord;
    }

    /**
     * @param string $secretWord
     */
    public function setSecretWord(string $secretWord): void
    {
        $this->secretWord = $secretWord;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string  $email): void
    {
        $this->email = $email;
    }

    /**
     * Client constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->loadConfig($data);
        $this->request = new RequestApi(self::API_HOST);
    }

    /**
     * @param array $config
     */
    public function loadConfig(array $config)
    {
        foreach ($config as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * @return stdClass|mixed
     */
    public function getAvailablePaymentServices(): stdClass
    {
        return $this->request->send('request/payment-methods', [
            'merchant_id' => $this->getMerchantId(),
            'secret_key' => $this->getSecretWord(),
        ]);
    }

    /**
     * @return stdClass|mixed
     */
    public function getBalance(): stdClass
    {
        return $this->request->send('request/balance', [
            'api_key' => $this->getApiKey(),
            'email' => $this->getEmail(),
        ]);
    }

    /**
     * @param string $service
     * @param string $wallet
     * @param $amount
     * @return stdClass|mixed
     */
    public function withdraw(string $service, string $wallet, $amount, $transactionId): stdClass
    {
        return $this->request->send('request/payoff', [
            'api_key' => $this->getApiKey(),
            'email' => $this->getEmail(),
            'service' => $service,
            'wallet' => $wallet,
            'amount' => $amount,
            'orderid' => $transactionId
        ]);
    }
    
     /**
     * @param array $info
     * @return stdClass
     */
    public function withdrawInfo(array $info): stdClass
    {
        $data = [
            'api_key' => $this->getApiKey(),
            'email' => $this->getEmail(),
        ];
        $data = array_merge($data, $info);
        return $this->request->send('request/payoff-info', $data);
    }

    /**
     * @param $sum
     * @param $orderId
     * @param string $currency
     * @param $callback
     * @param $defaultPayment
     * @param $redirectTo
     * @param $successUrl
     * @param $failUrl
     * @return string
     */
    public function generatePaymentLink(
        $sum,
        $orderId,
        string $currency = self::CURRENCY_RUB,
        string $callback,
        string $defaultPayment,
        string $redirectTo = '',
        string $successUrl = '',
        string $failUrl = ''
    ) {
        $params = [
            'm' => $this->getMerchantId(),
            'oa' => $sum,
            'o' => $orderId,
            's' => $this->generateSign($sum, $orderId),
            'cr' => $currency,
            'cf' => $callback,
            'p' => $defaultPayment,
            'ap' => $redirectTo,
            'success_url' => $successUrl,
            'fail_url' => $failUrl,
        ];

        return self::API_HOST . '/pay?' . http_build_query($params);
    }

    /**
     * @param $sum
     * @param $orderId
     * @return string
     */
    public function generateSign($sum, $orderId): string
    {
        $sign = [
            $this->getMerchantId(),
            $this->getSecretWord(),
            $sum,
            $orderId,
        ];

        return md5(join(':', $sign));
    }
}
