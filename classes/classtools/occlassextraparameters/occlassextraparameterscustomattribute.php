<?php

class OCClassExtraParametersCustomAttribute
{
    private $identifier;

    private $name;

    private function __construct($identifier, $name)
    {
        $this->name = $name;
        $this->identifier = $identifier;
    }

    public static function create($identifier, $name)
    {
        return new OCClassExtraParametersCustomAttribute($identifier, $name);
    }

    public function attributes()
    {
        return array(
            'identifier',
            'name'
        );
    }

    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }

    public function attribute( $key )
    {
        switch ($key) {
            case 'identifier':
                return $this->getIdentifier();

            case 'name':
                return $this->getName();

            default:
                eZDebug::writeError("Attribute $key not found", __METHOD__);
                return null;
        }
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

}