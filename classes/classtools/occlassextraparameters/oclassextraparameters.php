<?php

class OCClassExtraParameters extends eZPersistentObject
{

    public static function definition()
    {
        return array(
            'fields' => array(
                'class_identifier' => array(
                    'name' => 'ClassIdentifier',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => true
                ),
                'attribute_identifier' => array(
                    'name' => 'AttributeIdentifier',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => true
                ),
                'handler' => array(
                    'name' => 'Handler',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => true
                ),
                'parameter_key' => array(
                    'name' => 'Key',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => false
                ),
                'value' => array(
                    'name' => 'Value',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => false
                ),
                'created_time' => array(
                    'name' => 'CreatedTime',
                    'datatype' => 'integer',
                    'default' => time(),
                    'required' => false
                )
            ),
            'keys' => array( 'class_identifier', 'attribute_identifier', 'handler', 'parameter_key' ),
            'class_name' => 'OCClassExtraParameters',
            'name' => 'occlassextraparameters'
        );
    }

    /**
     * Universal getter
     * @param string $name
     * @return mixed
     */
    public function __get( $name )
    {
        $ret = null;
        if( $this->hasAttribute( $name ) )
            $ret = $this->attribute( $name );

        return $ret;
    }

    public static function fetchByHandler( $handler )
    {
        return parent::fetchObjectList( self::definition(), null, array( 'handler' => $handler ) );
    }

    public static function fetchByHandlerAndClassIdentifier( $handler, $classIdentifier )
    {
        return parent::fetchObjectList( self::definition(), null, array( 'handler' => $handler, 'class_identifier' => $classIdentifier ) );
    }

    public static function removeByHandler( $handler )
    {
        parent::removeObject( self::definition(), array( 'handler' => $handler ) );
    }

    public static function removeByHandlerAndClassIdentifier( $handler, $classIdentifier )
    {
        parent::removeObject( self::definition(), array( 'handler' => $handler, 'class_identifier' => $classIdentifier ) );
    }

    public static function removeByHandlerClassIdentifierAndKey( $handler, $classIdentifier, $key )
    {
        parent::removeObject( self::definition(), array( 'handler' => $handler, 'class_identifier' => $classIdentifier, 'parameter_key' => $key ) );
    }


}
