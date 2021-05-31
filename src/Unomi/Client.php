<?php
/**
 *
 *
 */

namespace Bolius\UnomiClient\Unomi;

class Client
{

    protected $url;

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
    public function __construct($url)
    {
        $this->url = $url;
    }


    /**
     * @param array|NULL $condion
     * @return mixed
     */
    public function getProfiles(
        array $condition = NULL,
        int $offset = 0,
        int $limit = 20
    )
    {
        return $this->post('/profiles/search', [
            'offset' => $offset,
            'limit' => $limit,
            'condition' => empty($condition) ? NULL : $condition,
        ]);
    }

    /**
     * @param string $profileId
     * @return mixed
     */
    public function getProfile(string $profileId)
    {
        return $this->get('/profiles/' . $profileId);
    }

    public function getSessionsByProfile(string $profileId)
    {
        return $this->get('/profiles/' . $profileId . '/sessions');
    }

    /**
     * @param string $sessionId
     * @return mixed
     */
    public function getSession(string $sessionId)
    {
        return $this->get('/profiles/sessions/' . $sessionId);
    }

    /**
     * @param string $eventId
     * @return mixed
     */
    public function getEvent(string $eventId)
    {
        return $this->get('/events/' . $eventId);
    }

    /**
     * @param string $ruleId
     * @return mixed
     */
    public function getRule (string $ruleId)
    {
        return $this->get('/rules/' . $ruleId);
    }

    public function getSegment($segmentId)
    {
        return $this->get('/segments/' . $segmentId);
    }

    /**
     * @param $segmentId
     * @return mixed
     */
    public function getSegmentCount($segmentId)
    {
        return $this->get('/segments/' . $segmentId . '/count');
    }

    /**
     * @return mixed
     */
    public function getSegments()
    {
        return $this->get('/segments');
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function post($url, $data)
    {
        $ch = curl_init();


        curl_setopt_array($ch, [
            CURLOPT_URL => $this->url . '/cxs' . $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-type: application/json',
                'Accept: application/json',
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

    public function postPublic($data)
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

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->url . '/cxs' . $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
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
