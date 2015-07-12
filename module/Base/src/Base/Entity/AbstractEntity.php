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
    public function get($property) 
    {
        $getter = 'get'.ucfirst($property);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function set($property, $value) 
    {
        $setter = 'set'.ucfirst($property);
        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        } elseif (property_exists($this, $property)) {
            return $this->$property = $value;
        }
    }
    
    public function __get($property) 
    {
        return $this->get($property);
    }
    
    public function __set($property, $value)
    {
        return $this->set($property, $value);
    }
    
    public function __isset($property) 
    {
        $value = $this->get($property);
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
    
    public function afterSave()
    {
        return $this;
    }
    
    public function afterCreate()
    {
        return $this;
    }
    
    public function afterUpdate()
    { 
        return $this;
    }
    
    public function afterDelete()
    {
        return $this;
    }
    
    public function setData($data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
        
        return $this;
    }
}
