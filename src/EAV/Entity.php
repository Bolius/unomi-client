<?php
/**
 *
 *
 */

namespace Bolius\UnomiClient\EAV;

abstract class Entity
{

    /**
     * @var
     */
    protected $id;

    /**
     * @var
     */
    protected $typestring;

    /**
     * @var AttributeInstance[]
     */
    protected $attributes;


    /**
     * Model constructor.
     */
    public function __construct()
    {

    }
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Entity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTypestring()
    {
        return $this->typestring;
    }

    /**
     * @param $key
     * @return AttributeInstance
     */
    public function getAttribute($name)
    {
        if (!isset($this->attributes[$name])) {
            if ($attribute = SchemaService::getInstance()->getAttribute($this->typestring, $name)) {
                $this->attributes[$name] = $attribute->getInstance($this);
            }
        }

        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return FALSE;
    }

    /**
     * @param $key
     * @param $value AttributeInstance
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setValue($name, $value)
    {
        if ($attribute = $this->getAttribute($name)) {
            $attribute->setValue($value);
        }
        return $this;
    }

    /**
     * @param array $array
     * @return $this
     */
    public function setArray(array $array)
    {
        foreach ($array as $name => $value) {
            $this->setValue($name, $value);
        }
        return $this;
    }

    /**
     * @param $property
     * @return mixed|null
     */
    public function __get($property)
    {
        // called from AbstractSerializer::getId($model)
        if ($property == 'id') {
            return $this->getId();
        }
        if ($attribute = $this->getAttribute($property)) {
            return $attribute->getValue();
        }
        return null;
    }

    /**
     * @param $method
     * @param $args
     * @return bool|\stdClass
     */
    public function __call($method, $args)
    {
        $propertyName = lcfirst($method);
        if ($attribute = $this->getAttribute($propertyName)) {
            return $attribute->getValue();
        }

        if (substr($method, 0, 3) == 'set') {
            $propertyName = lcfirst(substr($method, 3));
            if ($attribute = $this->getAttribute($propertyName)) {
                return $attribute->setValue($args[0]);
            }
        }
        return null;
    }
}