<?php
/**
 *
 *
 */

namespace Bolius\UnomiClient\EAV;


class Attribute
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     *
     */
    protected $description;


    /**
     * Attribute constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Attribute
     */
    public function setName(string $name): Attribute
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Attribute
     */
    public function setType(string $type): Attribute
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Attribute
     */
    public function setDescription(string $description): Attribute
    {
        $this->description = $description;
        return $this;
    }

    public function getInstance(Entity $entity)
    {
        return
            SchemaService::getInstance()
                ->getAttributeInstance($entity, $this);
    }

    /**
     * @param Entity $model
     * @return array|bool|Entity
     */
    public function getValue(Entity $model)
    {
        return NULL;
    }



}