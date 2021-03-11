<?php
/**
 *
 *
 */

namespace Bolius\UnomiClient\Repository;


use Bolius\UnomiClient\EAV\Attribute;
use Bolius\UnomiClient\EAV\SchemaService;
use Bolius\UnomiClient\Entity\Segment;
use Bolius\UnomiClient\Unomi\Client;
use Bolius\UnomiClient\Entity\Profile;

class ProfileRepository extends AbstractRepository
{


    /**
     * @param $id
     * @return Segment
     */
    public function getById($id)
    {
        $schemaService = SchemaService::getInstance();
        $client = new Client();
        $data = $client->get('/profiles/' . $id);

        $entity = new Profile();
        $entity
            ->setId($data->itemId);

        $data = $data->properties;

        /** @var Attribute $attribute */
        foreach ($schemaService->getAttributes('profile') as $attribute) {
            if (isset($data->{$attribute->getName()})) {
                $entity->getAttribute($attribute->getName())->setValue($data->{$attribute->getName()});
            }
        }

        return $entity;

    }

    /**
     * params :
     * [
     *    'condition' => Condition
     * ]
     * @param $params
     */
    public function findMany($params = [])
    {
        $client = new Client();
        $items = $client->post('/profiles/search', [
            'offset' => 0,
            'limit' => 20,
            'condition' => null,
        ]);

        $s = [];
        if (is_array($items->list)) {

            foreach ($items->list as $item) {
                $profile = $this->getById($item->itemId);
                $s[] = $profile;
            }
        }

        return $s;
    }
}