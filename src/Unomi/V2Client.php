<?php

namespace Bolius\UnomiClient\Unomi;

class V2Client extends Client
{

    /**
     * @param string $profileId
     * @return mixed
     */
    public function getProfile(string $profileId)
    {
        $profile = $this->get('/profiles/' . $profileId);
        $profile->aliases = $this->getProfileAliases($profileId);
        return $profile;
    }

    /**
     * @param $profileId
     * @return mixed
     */
    public function getProfileAliases(
        $profileId
    )
    {
        return $this->get(sprintf('/profiles/%s/aliases', $profileId))->list;
    }

    /**
     * @param $profileId
     * @param $aliasId
     * @return mixed
     */
    public function getProfileAlias(
        $profileId,
        $aliasId
    )
    {
        return $this->get(sprintf('/profiles/%s/aliases/%s', $profileId, $aliasId));
    }

    /**
     * @param $profileId
     * @param $aliasId
     * @return mixed
     */
    public function addProfileAlias(
        $profileId,
        $aliasId
    )
    {
        return $this->post(sprintf('/profiles/%s/aliases/%s', $profileId, $aliasId));
    }

    public function getSchema(
        $id
    )
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->urlPrivate . '/cxs/jsonSchema/query',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $id,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);
        $result = curl_exec($ch);

        return json_decode($result);
    }

    public function storeSchema(array $schema)
    {
        return $this->post('/jsonSchema', $schema);
    }

    /**
     * @return mixed
     */
    public function getSegments()
    {
        return $this->get('/segments');
    }

    public function getScopes()
    {
        return $this->get('/scopes');
    }

    public function storeScope(
        array $scope
    )
    {
        $this->post('/scopes', $scope);
    }
}