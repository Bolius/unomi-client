<?php
/**
 *
 *
 */

namespace Bolius\UnomiClient\Unomi;

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

    protected $version = 0;

    protected $host = 'localhost';
    protected $port = '8181';
    protected $username = 'karaf';
    protected $password = 'karaf';

    /**
     * Unomi cURL Timeout in seconds.
     *
     * @var int
     */
    protected $timeout = 5;

    /**
     * @var
     */
    protected $progressFunction;

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

        $urlPrivate, $urlPublic, $version
    )
    {
        $this->urlPrivate = $urlPrivate;
        $this->urlPublic = $urlPublic;
        $this->version = $version;
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

    /**
     * @param array $data
     * @return mixed
     */
    public function storeProfile(array $data)
    {
        return $this->post('/profiles', $data);
    }

    /**
     * @param string $profileId
     * @param array|null $condition
     */
    public function doesProfileMatchCondition(
        string $profileId,
        array  $condition = NULL
    )
    {
        return (bool)$this->getProfileCount([
            'type' => 'booleanCondition',
            'parameterValues' => [
                'operator' => 'and',
                'subConditions' => [
                    [
                        'type' => 'profilePropertyCondition',
                        'parameterValues' => [
                            'propertyName' => 'itemId',
                            'comparisonOperator' => 'equals',
                            'propertyValue' => $profileId,
                        ],
                    ],
                    $condition
                ],
            ]
        ]);
    }

    /**
     * @param string $profileId
     * @param array|null $condition
     */
    public function doesProfileByEmailMatchCondition(
        string $email,
        array  $condition = NULL
    )
    {
        return (bool)$this->getProfileCount([
            'type' => 'booleanCondition',
            'parameterValues' => [
                'operator' => 'and',
                'subConditions' => [
                    [
                        'type' => 'profilePropertyCondition',
                        'parameterValues' => [
                            'propertyName' => 'properties.email',
                            'comparisonOperator' => 'equals',
                            'propertyValue' => $email,
                        ],
                    ],
                    $condition
                ],
            ]
        ]);
    }

    /**
     * Returns a single profile id that has a matching property value
     *
     * @param $propertyName
     * @param $propertyValue
     * @return false
     */
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


    /**
     * @param array|null $condition
     * @param int $offset
     * @param int $limit
     * @return mixed
     */
    public function getSessions(
        array $condition = NULL,
        int   $offset = 0,
        int   $limit = 20
    )
    {
        return $this->post('/profiles/search/sessions/', [
            'offset' => $offset,
            'limit' => $limit,
            'condition' => $condition,
        ])->list;
    }

    /**
     * @param string $profileId
     * @param null $q
     * @param null $offset
     * @param null $size
     * @param string $sort
     * @return mixed
     */
    public function getSessionsByProfile(
        string $profileId,
               $q = NULL,
               $offset = NULL,
               $size = NULL,
               $sort = 'timeStamp:desc'
    )
    {
        $url = sprintf('/profiles/%s/sessions?', $profileId);
        if (!is_null($q)) {
            $url .= '&q=' . $q;
        }
        if (!is_null($offset)) {
            $url .= '&offset=' . (int)$offset;
        }
        if (!is_null($sort)) {
            $url .= '&sort=' . $sort;
        }
        if (!is_null($size)) {
            $url .= '&size=' . (int)$size;
        }

        return $this->get($url)->list;

    }

    public function getEventsByProfile(
        string $profileId
    )
    {
        return $this->post('/events/search', [
            'condition' => [
                'type' => 'eventPropertyCondition',
                'parameterValues' => [
                    'propertyName' => 'profileId',
                    'comparisonOperator' => 'equals',
                    'propertyValue' => $profileId,
                ]
            ],
            'limit' => 5,
        ])->list;
    }

    /**
     * @param string $sessionId
     * @param null $q
     * @param null $offset
     * @param null $size
     * @param string $sort
     * @return mixed
     */
    public function getEventsBySession(
        string $sessionId,
               $q = NULL,
               $offset = NULL,
               $size = NULL,
               $sort = 'timeStamp:desc'
    )
    {
        $url = sprintf('/profiles/sessions/%s/events?', $sessionId);
        if (!is_null($q)) {
            $url .= '&q=' . $q;
        }
        if (!is_null($offset)) {
            $url .= '&offset=' . (int)$offset;
        }
        if (!is_null($sort)) {
            $url .= '&sort=' . $sort;
        }
        if (!is_null($size)) {
            $url .= '&size=' . (int)$size;
        }

        return $this->get($url)->list;
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
     * @param array $session
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
        return $this->get('/rules')->list;
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

    /**
     * @param $segmentId
     * @return mixed
     */
    public function getSegment($segmentId)
    {
        return $this->get(sprintf('/segments/%s', $segmentId));
    }


    /**
     * @param $segmentId
     * @return mixed
     */
    public function getSegmentProfileCount($segmentId)
    {
        return $this->get(sprintf('/segments/%s/count', $segmentId));
    }


    /**
     * @return mixed
     */
    public function getScorings()
    {
        return $this->get('/scoring')->list;
    }

    /**
     * @param $goalId
     * @return mixed
     */
    public function getGoal($goalId)
    {
        return $this->get(sprintf('/goals/%s', $goalId));
    }

    /**
     * @return mixed
     */
    public function getGoals()
    {
        return $this->get('/goals')->list;
    }

    /**
     * @param $scoringId
     * @return mixed
     */
    public function getScoring($scoringId)
    {
        return $this->get('/scoring/' . $scoringId);
    }

    /**
     * @param $campaignId
     * @return mixed
     */
    public function getCampaign($campaignId)
    {
        return $this->get(sprintf('/campaigns/%s', $campaignId));
    }

    /**
     * @param array $segment
     * @return mixed
     */
    public function storeSegment(array $segment)
    {
        return $this->post('/segments', $segment);
    }

    /**
     * @return mixed
     */
    public function getCampaigns()
    {
        return $this->get('/campaigns')->list;
    }

    /**
     * @return mixed
     */
    public function getOpenapiSpec()
    {
        return $this->get('/openapi.json');
    }

    /**
     * @param string $profileId
     * @param array $data
     * @param string|null $sessionId
     * @deprecated Not tested
     *
     */
    public function registerEventOnProfile(
        string $profileId,
        array  $data,
        string $sessionId = NULL
    )
    {

        $body = [
            'events' => [
                $data,
            ],
        ];

        if (!empty($sessionId)) {
            $body['sessionId'] = $sessionId;
        }

        $this->postPublic($body, $profileId);
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
        $this->lastResponse = $result;

        $this->lastRequestTime = microtime(TRUE) - $start;
        $response = json_decode($result);
        if (is_null($this->firstResponse)) {

            $this->firstResponse = $data;
        }

        if (0) {

            print_r([
                'request' => 'POST ' . $url,
                'body' => $data,
                'bodyJson' => json_encode($data),
                'response' => $result,
                'curl' => curl_getinfo($ch),
            ]);
        }

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
