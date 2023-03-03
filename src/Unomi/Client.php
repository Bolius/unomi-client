<?php
/**
 *
 *
 */

namespace Bolius\UnomiClient\Unomi;

use Bolius\UnomiBundle\Entity\Session;

class Client
{

    /**
     * @var string
     */
    protected $urlPrivate;

    /**
     * @var string
     */
    protected $urlPublic;

    protected $host = 'localhost';
    protected $port = '8181';
    protected $username = 'karaf';
    protected $password = 'karaf';

    /**
     * Unomi cURL Timeout in seconds.
     *
     * @var int
     */
    protected $timeout = 30;

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
    public function __construct(
        $urlPrivate,
        $urlPublic
    )
    {
        $this->urlPrivate = $urlPrivate;
        $this->urlPublic = $urlPublic;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout Timeout in seconds
     * @return Client
     */
    public function setTimeout(int $timeout): Client
    {
        $this->timeout = $timeout;
        return $this;
    }


    /**
     * @param array|NULL $condion
     * @return mixed
     */
    public function getProfiles(
        array $condition = NULL,
        int   $offset = 0,
        int   $limit = 20
    )
    {
        $req = [
            'offset' => $offset,
            'limit' => $limit,
            'condition' => empty($condition) ? NULL : $condition,
        ];
        $resp = $this->post('/profiles/search', $req);
        if (isset($resp->list)) {
            return $resp->list;
        }
        return FALSE;
    }

    /**
     * @param array|NULL $condion
     * @return mixed
     */
    public function getProfileCount(
        array $condition = NULL
    )
    {
        if (empty($condition)) {
            // shortcut to
            return $this->get('/profiles/count');
        }

        $response = $this->post('/query/profile/count', $condition);

        if (is_int($response)) {
            return $response;
        }

        return 0;
    }

    /**
     * @param string $profileId
     * @return mixed
     */
    public function getProfile(string $profileId)
    {
        return $this->get('/profiles/' . $profileId);
    }

    public function getProfileIdByProperty(
        $propertyName, $propertyValue
    )
    {
        $profiles = $this->post('/profiles/search', [
            'condition' => [
                'type' => 'profilePropertyCondition',
                'parameterValues' => [
                    'propertyName' => 'properties.' . $propertyName,
                    'propertyValue' => $propertyValue,
                    'comparisonOperator' => 'equals',
                ]
            ]
        ]);

        if (isset($profiles->list)) {
            if (isset($profiles->list[0])) {
                if (isset($profiles->list[0]->itemId)) {
                    return $profiles->list[0]->itemId;
                }
            }
        }

        return FALSE;
    }


    public function getSessionsByProfile(string $profileId)
    {
        return $this->get('/profiles/' . $profileId . '/sessions?sort=timeStamp:desc');
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
     * @param Session $session
     * @return mixed
     */
    public function storeSession(array $session)
    {
        return $this->post(
            sprintf('/profiles/sessions/%s', $session['itemId']),
            $session
        );
    }

    /**
     * @return mixed
     */
    public function getSessionCount($condition = null)
    {
        return $this->post('/query/session/count', $condition);
    }

    /**
     * @param string $eventId
     * @return mixed
     */
    public function getEvent(string $eventId)
    {
        return $this->get(sprintf('/events/%s', $eventId));
    }


    /**
     * @param null $condition
     * @return mixed
     */
    public function getEventCount($condition = null)
    {
        return $this->post('/query/event/count', $condition);
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->get('/rules');
    }

    /**
     * @param string $ruleId
     * @return mixed
     */
    public function getRule(string $ruleId)
    {
        return $this->get(sprintf('/rules/%s', $ruleId));
    }

    /**
     * @param string $ruleId
     * @return mixed
     */
    public function getRuleStatistics(string $ruleId)
    {
        return $this->get(sprintf('/rules/%s/statistics', $ruleId));
    }

    /**
     * @param string $userListId
     * @return mixed
     */
    public function getUserList(string $userListId)
    {
        return $this->get('/lists/' . $userListId);
    }

    /**
     * @param $userListIds string|array
     * @return mixed
     */
    public function getUserListProfileCount($userListIds)
    {

        if (!is_array($userListIds)) {
            $userListIds = [$userListIds];
        }
        return $this->getProfileCount([
            'type' => 'profileUserListCondition',
            'parameterValues' => [
                'matchType' => 'in',
                'lists' => $userListIds,
            ]
        ]);
    }

    /**
     * @return mixed
     */
    public function getUserLists()
    {
        return $this->get('/lists')->list;
    }

    public function getUse()
    {

    }

    /**
     * @param $segmentId
     * @return mixed
     */
    public function getSegment($segmentId)
    {
        return $this->get('/segments/' . $segmentId);
    }

    /**
     * @param $segmentId
     * @return mixed
     */
    public function getSegmentProfileCount($segmentId)
    {
        return (int) $this->get(sprintf('/segments/%s/count', $segmentId));
    }

    /**
     * @return mixed
     */
    public function getSegments()
    {
        return $this->get('/segments');
    }

    /**
     * @return mixed
     */
    public function getScorings()
    {
        return $this->get('/scoring');
    }

    public function getGoal($goalId)
    {
        return $this->get('/goals/' . $goalId);
    }

    public function getGoals()
    {
        return $this->get('/goals');
    }

    /**
     * @param $scoringId
     * @return mixed
     */
    public function getScoring($scoringId)
    {
        return $this->get('/scoring/' . $scoringId);
    }

    public function getCampaign($campaignId)
    {
        return $this->get('/campaigns/' . $campaignId);
    }

    public function getCampaigns()
    {
        return $this->get('/campaigns');
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
            CURLOPT_URL => $this->urlPrivate . '/cxs' . $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => $this->timeout,
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
        $response = json_decode($result);

        if (is_null($this->firstResponse)) {
            $this->firstResponse = $data;
        }

        if (0) {

            print_r([
                'request' => 'POST ' . $url . "\n" . json_encode($data, JSON_PRETTY_PRINT),
                'response' => $result,
            ]);
            die;
        }

        $this->lastResponse = $response;
        return $response;
    }

    /**
     * @param $data
     * @param null $profileId If it exists.
     * @return mixed
     */
    public function postPublic($data, $profileId = null)
    {
        $ch = curl_init();

        $headers = [
            'Content-type: application/json',
            'Accept: application/json',
            'X-Unomi-Peer: 670c26d1cc413346c3b2fd9ce65dab41',
        ];

        if ($profileId) {
            $headers[] = 'Cookie: context-profile-id=' . $profileId;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->urlPublic . '/context.json',
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
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
            CURLOPT_URL => $this->urlPrivate . '/cxs' . $url,
            CURLOPT_TIMEOUT => $this->timeout,
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
