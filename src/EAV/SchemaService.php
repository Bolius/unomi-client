<?php
/**
 *
 *
 */

namespace Bolius\UnomiClient\EAV;

use Symfony\Component\Yaml\Yaml;

class SchemaService
{

    /**
     * https://dawa.aws.dk/replikering/datamodel
     *
     */

    protected static $instance;

    /**
     * @var array
     */
    protected $entities = [];

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct()
    {
        $this->load();
    }

    public function load()
    {

        // @TODO : change !
        $rootDir = '/var/www/virtual/datahub-lko/docs/bolius/unomi/config/schema.yaml';
        $data = Yaml::parseFile($rootDir);

        // instantiate all attributes that are not relations
        foreach ($data as $entityId => $entityConf) {
            if (isset($entityConf['attributes'])) {

                foreach ($entityConf['attributes'] as $attribute) {

                    $attributeObject = new Attribute($attribute['name']);
                    $attributeObject->setType($attribute['type']);

                    $this->entities[$entityId]['attributes'][] = $attributeObject;
                }
            }

        }

    }

    public function getAttribute ($entityType, $attributeName)
    {
        if (isset($this->entities[$entityType])) {
            /** @var Attribute $attribute */
            foreach ($this->entities[$entityType]['attributes'] as $attribute ) {
                if ($attribute->getName() == $attributeName) {
                    return $attribute;
                }
            }
        }
        return false;
    }

    public function getAttributes ($type)
    {
        $e = $this->entities[$type];
        return $e['attributes'];
    }

    public function getAttributeInstance(Entity $entity, Attribute $attribute)
    {
        $instance = new AttributeInstance();
        $instance
            ->setEntity($entity)
            ->setAttribute($attribute);
        return $instance;
    }

}