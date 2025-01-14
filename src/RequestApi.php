<?php

declare(strict_types=1);

namespace FASTJE\EnotIO;

use GuzzleHttp\Client;
use stdClass;

/**
 * Class RequestApi.
 *
 * @property string $host
 * @property Client $http_client
 */
class RequestApi
{
    protected CONST CONNECTION_TIMEOUT = 2;
    protected CONST TIMEOUT = 4;
    protected CONST HTTP_STATUS_CODE = 200;

    /**
     * @var string $host
     */
    private $host;

    /**
     * @var Client $http_client
     */
    private $http_client;

    /**
     * RequestApi constructor.
     * @param string $host
     */
    public function __construct(string $host)
    {
        $this->http_client = new Client([
            'base_uri' => $host,
            'timeout' => static::TIMEOUT,
            'connect_timeout' => static::CONNECTION_TIMEOUT,
        ]);

        $this->host = $host;
    }

    /**
     * @param array $params
     * @param string $path
     * @param string $method
     * @return stdClass
     */
    public function send(string $path, array $params = [], string $method = 'get'): stdClass
    {
        $rowData = http_build_query($params);
        $response = $this->http_client->request($method, $path, [
            'query' => $rowData
        ]);

        return json_decode((string) $response->getBody());
    }
}
