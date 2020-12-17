<?php
/**
 *
 *
 */

namespace Bolius\UnomiClient\Repository;

use Bolius\Unomi\Unomi\Client;
use Bolius\Unomi\Entity\Segment;
use Bolius\UnomiBundle\ClientService;

class SegmentRepository extends AbstractRepository
{

    public function getById($id)
    {
        $client = ClientService::getClient();
        $data = $client->get('/segments/' . $id);
        $segment = new Segment();
        $segment
            ->setId($data->metadata->id)
            ->setName($data->metadata->name);

        return $segment;

    }

    /**
     * @param $params
     */
    public function findMany($params = [])
    {
        $client = ClientService::getClient();
        $segments = $client->get('/segments');

        $s = [];
        foreach ($segments as $segment) {
            $client->get('/segments/' . $segment->id);
            $segmentEntity = new Segment();
            $segmentEntity
                ->setId($segment->id)
                ->setName($segment->name);
            $s[] = $segmentEntity;
        }

        return $s;
    }
}