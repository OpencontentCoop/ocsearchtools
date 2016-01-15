<?php

abstract class OCClassExtraParametersHandlerBase implements OCClassExtraParametersHandlerInterface
{

    /**
     * @var eZContentClass
     */
    protected $class;

    /**
     * @var eZContentClassAttribute[]
     */
    protected $classAttributes;

    /**
     * @var OCClassExtraParameters[]
     */
    protected $parameters;

    public function __construct( eZContentClass $class )
    {
        $this->class = $class;
        $this->classAttributes = $this->class->dataMap();
        $this->loadParameters();
    }

    public function loadParameters( $reload = false )
    {
        if ( $this->parameters === null || $reload )
            $this->parameters = (array)OCClassExtraParameters::fetchByHandlerAndClassIdentifier( $this->getIdentifier(), $this->class->Identifier );
    }

    public function storeParameters( $data )
    {
        if ( OCClassExtraParametersManager::currentUserCanEditHandlers( $this->getIdentifier() ) )
        {
            $classData = isset( $data['class'] ) ? $data['class'] : array();
            $attributesData = isset( $data['class_attribute'] ) ? $data['class_attribute'] : array();

            OCClassExtraParameters::removeByHandlerAndClassIdentifier(
                $this->getIdentifier(),
                $this->class->Identifier
            );

            foreach ( $classData as $classIdentifier => $values )
            {
                foreach ( $values as $key => $value )
                {
                    $row = array(
                        'class_identifier' => $classIdentifier,
                        'attribute_identifier' => '*',
                        'handler' => $this->getIdentifier(),
                        'key' => $key,
                        'value' => $value
                    );
                    $parameter = new OCClassExtraParameters( $row );
                    $parameter->store();
                }
            }

            foreach ( $attributesData as $classIdentifier => $attributesValues )
            {
                foreach ( $attributesValues as $attributeIdentifier => $values )
                {
                    foreach ( $values as $key => $value )
                    {
                        $row = array(
                            'class_identifier' => $classIdentifier,
                            'attribute_identifier' => $attributeIdentifier,
                            'handler' => $this->getIdentifier(),
                            'key' => $key,
                            'value' => $value
                        );
                        $parameter = new OCClassExtraParameters( $row );
                        $parameter->store();
                    }
                }
            }
            $this->loadParameters( true );
        }
    }

    public function attributes()
    {
        return array(
            'identifier',
            'name',
            'enabled',
            'class_edit_template_url',
            'attribute_edit_template_url'
        );
    }

    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }

    public function attribute( $key )
    {
        switch( $key )
        {
            case 'identifier':
                return $this->getIdentifier();

            case 'name':
                return $this->getName();

            case 'enabled':
                return $this->getClassParameter( 'enabled' );

            case 'class_edit_template_url':
                return $this->classEditTemplateUrl();

            case 'attribute_edit_template_url':
                return $this->attributeEditTemplateUrl();

            default:
                eZDebug::writeError( "Attribute $key not found", __METHOD__ );
                return null;
        }
    }

    protected function getName()
    {
        return ucfirst( $this->getIdentifier() );
    }

    protected function classEditTemplateUrl()
    {
        return 'design:classtools/extraparameters/' . $this->getIdentifier() . '/edit_class.tpl';
    }

    protected function attributeEditTemplateUrl()
    {
        return 'design:classtools/extraparameters/' . $this->getIdentifier() . '/edit_attribute.tpl';
    }

    protected function getClassParameter( $key )
    {
        foreach ( $this->parameters as $parameter )
        {
            if ( $parameter->attribute( 'key' ) == $key && $parameter->attribute( 'attribute_identifier' ) == '*' )
            {
                return $parameter->attribute( 'value' );
            }
        }
        return false;
    }

    protected function getAttributeIdentifierListByParameter( $key, $value = 1, $returnAllIfEmpty = true )
    {
        $data = array();

        foreach ( $this->parameters as $parameter )
        {
            if ( $parameter->attribute( 'key' ) == $key && $parameter->attribute( 'value' ) == $value )
            {
                $data[] = $parameter->attribute( 'attribute_identifier' );
            }
        }

        if ( empty( $data ) && $returnAllIfEmpty )
        {
            $data = array_keys( $this->classAttributes );
        }
        return $data;
    }

}