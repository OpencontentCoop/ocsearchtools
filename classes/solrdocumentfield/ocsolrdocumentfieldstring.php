<?php

class ocSolrDocumentFieldString extends ezfSolrDocumentFieldBase
{
    
    const DEFAULT_SUBATTRIBUTE_TYPE = 'string';

    function __construct( eZContentObjectAttribute $attribute )
    {
        parent::__construct( $attribute );
    }
    
    public static function getFieldName( eZContentClassAttribute $classAttribute, $subAttribute = null, $context = 'search' )
    { 
        switch ( $classAttribute->attribute( 'data_type_string' ) )
        {            
            case 'ezstring':
            {
                if ( $subAttribute and $subAttribute !== '' )
                {
                    if ($subAttribute == 'start_end'){
                        return parent::generateSubattributeFieldName( $classAttribute,
                                                                      $subAttribute,
                                                                      'text' );
                    }

                    // A subattribute was passed
                    return parent::generateSubattributeFieldName( $classAttribute,
                                                                  $subAttribute,
                                                                  self::DEFAULT_SUBATTRIBUTE_TYPE );
                }
                else
                {
                    // return the default field name here.
                    return parent::generateAttributeFieldName( $classAttribute,
                                                               self::getClassAttributeType( $classAttribute, null, $context ) );
                }
            } break;
        
            default:
            break;
        }
    }

    
    public function getData()
    {
        $contentClassAttribute = $this->ContentObjectAttribute->attribute( 'contentclass_attribute' );
        $fieldNameArray = array();
        foreach ( array_keys( eZSolr::$fieldTypeContexts ) as $context )
        {
            $fieldNameArray[] = self::getFieldName( $contentClassAttribute, null, $context );
        }
        $fieldNameArray = array_unique( $fieldNameArray );

        $metaData = trim( $this->ContentObjectAttribute->metaData() );
        $processedMetaDataArray = array();
        $processedMetaDataArray[] = $this->preProcessValue( $metaData,
                                                            self::getClassAttributeType( $contentClassAttribute ) );
        $fields = array();
        foreach ( $fieldNameArray as $fieldName )
        {
            $fields[$fieldName] = $processedMetaDataArray ;
        }
        
        $fieldNameArray = array();
        foreach ( array_keys( eZSolr::$fieldTypeContexts ) as $context )
        {
            $fieldNameArray[] = self::getFieldName( $contentClassAttribute, 'start_letter', $context );            
        }
        $fieldNameArray = array_unique( $fieldNameArray );
        foreach ( $fieldNameArray as $fieldName )
        {
            $fields[$fieldName] = $this->preProcessValue( $this->getFirstAlpha( $metaData ),
                                                          self::getClassAttributeType( $contentClassAttribute ) );
        }

        $fieldNameArray = array();
        foreach ( array_keys( eZSolr::$fieldTypeContexts ) as $context )
        {
            $fieldNameArray[] = self::getFieldName( $contentClassAttribute, 'start_end', $context );            
        }
        $fieldNameArray = array_unique( $fieldNameArray );
        foreach ( $fieldNameArray as $fieldName )
        {
            $fields[$fieldName] = $this->preProcessValue( $this->getStartEnd( $metaData ),
                                                          self::getClassAttributeType( $contentClassAttribute ) );
        }
        
        return $fields;
    }
    
    protected function getFirstAlpha( $string )
    {
        $first = '';
        $letters = str_split( $string );
        foreach( $letters as $letter )
        {
            if ( ctype_alpha( $letter ) )
            {
                $first = utf8_encode($letter);
                break;
            }
        }
        return $first;
    }

    protected function getStartEnd( $string )
    {
        if (empty($string)){
            return '';
        }
        return '__START__' . trim($string) . '__END__';
    }
}

?>
