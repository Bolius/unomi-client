<?php
/**
 *
 *
 */

namespace Bolius\UnomiClient\EAV;

class AttributeInstance
{

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @var
     */
    protected $value;

    /**
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * @param Entity $entity
     * @return AttributeInstance
     */
    public function setEntity(Entity $entity): AttributeInstance
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return Attribute
     */
    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }

    /**
     * @param Attribute $attribute
     * @return AttributeInstance
     */
    public function setAttribute(Attribute $attribute): AttributeInstance
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if (is_null($this->value)) {
            $this->value = $this->getAttribute()->getValue($this->getEntity());
        }
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return AttributeInstance
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get the human readable version of the machine readable value.
     * Does not apply to all attributes. Falls back to value.
     *
     * @return string|null
     */
    public function getLabel ()
    {
        return $this->attribute->getValueLabel($this->getValue());
    }


}