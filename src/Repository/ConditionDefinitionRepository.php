<?php
/**
 *
 *
 */

namespace Bolius\UnomiClient\Repository;

use Bolius\Unomi\Entity\ConditionDefinition;
use Bolius\Unomi\Unomi\Client;

class ConditionDefinitionRepository extends AbstractRepository
{

    /**
     * @param $params
     */
    public function findMany ($params = [])
    {
        $client = new Client();

        if (isset($params['filter'])) {
            $filter = $params['filter'];
        }
        $conditions = $client->get('/definitions/conditions');

        $s = [];
        foreach ($conditions as $c) {
            $client->get('/definitions/conditions/' . $c->id);
            $conditionDefinition = new ConditionDefinition();
            $conditionDefinition
                ->setId($c->id);
            $s[] = $conditionDefinition;
        }

        return $s;
    }
}