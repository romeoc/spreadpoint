<?php

namespace Base\Entity;

/**
 * Abstract Entity Model
 * 
 * Module: Application
 * Author: Romeo Cozac <romeo_cozac@yahoo.com>
 * 
 */
class AbstractEntity 
{
    protected $parentField;
    
    public function __get($property) 
    {
        $getter = 'get'.ucfirst($property);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value) 
    {
        $setter = 'set'.ucfirst($property);
        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        } elseif (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
    
    public function __isset($property) 
    {
        $value = $this->__get($property);
        return property_exists($this, $property) && !empty($value);
    }
    
    public function beforeSave()
    {
        return $this;
    }
    
    public function beforeCreate()
    {
        return $this;
    }
    
    public function beforeUpdate()
    { 
        return $this;
    }
    
    public function beforeDelete()
    {
        return $this;
    }
    
    public function getParentField()
    {
        return $this->parentField;
    }
    
    public function hasParentField()
    {
        return (!empty($this->parentField));
    }
}
