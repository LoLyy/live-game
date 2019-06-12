<?php


namespace App\Services;

use GuzzleHttp\Client;


class HttpClientService
{

    private static $instance = null;

    private $client;
    private $config = [];


    /**
     * HttpService constructor.
     * @param Client $client
     */
    private function __construct(Client $client)
    {
        $this->config = config('services.guzzle', []);

        $this->client = $client;
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @return HttpClientService|null
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self(new Client());
        }
        return self::$instance;
    }


    /**
     * 请求
     * @param $request_type
     * @param $url
     * @param $data
     * @param string $application_type
     * @param array $headers
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function httpGuzzle(string $request_type, string $url, array $data, string $application_type = 'form_params', array $headers = [])
    {
        $request_type = strtoupper($request_type);
        $temp_config = [];

        switch ($request_type) {
            case 'GET':
                $temp_config['query'] = $data;
                break;
            case 'POST':
                switch ($application_type) {
                    case 'json':
                        if ($data) {
                            $temp_config['json'] = $data;
                        }
                        break;
                    default:
                        if ($data) {
                            $temp_config['form_params'] = $data;
                        }
                        break;
                }
                break;
            default:
                break;
        }

        $config = array_merge($this->config, $temp_config, ["headers" => $headers]);
        try {
            $response = $this->client->request($request_type, $url, $config);

            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody(), true);
            } else {
                return [];
            }
        } catch (\Exception $exception) {

            $msg = 'HTTP请求失败，失败信息：' . $exception->getMessage();
            \Log::warning($msg);
            return [];
        }

    }


    /**
     * Get请求
     * @param $url
     * @param array $data
     * @param array $headers
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $url, array $data = [], array $headers = [])
    {

        return $this->httpGuzzle('get', $url, $data, '', $headers);

    }


    /**
     * Post请求
     * @param $url
     * @param array $data
     * @param string $application_type
     * @param array $headers
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post(string $url, array $data = [], string $application_type = 'form_data', array $headers = [])
    {
        return $this->httpGuzzle('post', $url, $data, $application_type, $headers);
    }
}
