<?php
/**
 *
 *
 */
namespace Bolius\UnomiClient\Unomi;

class Client
{

    protected $host = 'localhost';
    protected $port = '8181';
    protected $username = 'karaf';
    protected $password = 'karaf';

    protected $firstRequestMethod;
    protected $firstRequestPath;

    protected $firstResponse;
    protected $lastResponse;

    protected $firstRequest;

    protected $lastRequest = '';

    protected $lastRequestTime;


    /**
     * Client constructor.
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     */
    public function __construct($host, $port, $username, $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }


    public function post($url, $data)
    {
        $ch = curl_init();

        $auth = base64_encode($this->username . ':' . $this->password);

        curl_setopt_array($ch, [
            CURLOPT_URL => 'http://' . $this->host . ':' . $this->port . '/cxs' . $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . $auth,
            ],
        ]);

        if (is_null($this->firstRequest)) {
            $this->firstRequestMethod = 'POST';
            $this->firstRequestPath = '/cxs' . $url;
            $this->firstRequest = $data;
        }
        $this->lastRequest = $data;

        $start = microtime(TRUE);
        $result = curl_exec($ch);
        $this->lastRequestTime = microtime(TRUE) - $start;

        $data = json_decode($result);

        if (is_null($this->firstResponse)) {
            $this->firstResponse = $data;
        }

        $this->lastResponse = $data;
        return $data;
    }

    public function postPublic ($data)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => 'http://' . $this->host . ':' . $this->port . '/context.json',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-type: application/json',
                'Accept: application/json',
            ],
        ]);

        $this->lastRequest = $data;

        $start = microtime(TRUE);
        $result = curl_exec($ch);
        $this->lastRequestTime = microtime(TRUE) - $start;

        $data = json_decode($result);

        $this->lastResponse = $data;
        return $data;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function get($url)
    {
        $auth = base64_encode($this->username . ':' . $this->password);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'http://' . $this->host . ':' . $this->port . '/cxs' . $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Basic ' . $auth,
            ],
        ]);


        $start = microtime(TRUE);
        $result = curl_exec($ch);
        $this->lastRequestTime = microtime(TRUE) - $start;

        $data = json_decode($result);
        if (is_null($this->firstResponse)) {
            $this->firstResponse = $data;
        }
        $this->lastResponse = $data;
        return $data;

    }

    /**
     * @return mixed
     */
    public function getFirstResponse()
    {
        return $this->firstResponse;
    }

    /**
     * @return mixed
     */
    public function getFirstRequestMethod()
    {
        return $this->firstRequestMethod;
    }

    /**
     * @param mixed $firstRequestMethod
     */
    public function setFirstRequestMethod($firstRequestMethod): void
    {
        $this->firstRequestMethod = $firstRequestMethod;
    }

    /**
     * @return mixed
     */
    public function getFirstRequestPath()
    {
        return $this->firstRequestPath;
    }

    /**
     * @param mixed $firstRequestPath
     */
    public function setFirstRequestPath($firstRequestPath): void
    {
        $this->firstRequestPath = $firstRequestPath;
    }

    /**
     * @return mixed
     */
    public function getFirstRequest()
    {
        return $this->firstRequest;
    }

    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    public function getLastRequestTime()
    {
        return $this->lastRequestTime;
    }
}
