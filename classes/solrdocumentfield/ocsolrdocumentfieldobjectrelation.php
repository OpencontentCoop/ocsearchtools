<?php

class ocSolrDocumentFieldObjectRelation extends ezfSolrDocumentFieldBase
{
    /**
     * Contains the definition of subattributes for this given datatype.
     * This associative array takes as key the name of the field, and as value
     * the type. The type must be picked amongst the value present as keys in the
     * following array :
     * ezfSolrDocumentFieldName::$FieldTypeMap
     *
     * WARNING : this definition *must* contain the default attribute's one as well.
     *
     * @see ezfSolrDocumentFieldName::$FieldTypeMap
     * @var array
     */
    public static $subattributesDefinition = array( self::DEFAULT_SUBATTRIBUTE => 'text',
                                                    self::DEFAULT_SUBATTRIBUTE_TYPE => 'string');

    /**
     * The name of the default subattribute.
     * Will contain the textual representation of all of the related object(s)
     * fields.
     *
     * @var string
     */
    const DEFAULT_SUBATTRIBUTE = 'full_text_field';

    const DEFAULT_SUBATTRIBUTE_TYPE = 'string';


    /**
     * @see ezfSolrDocumentFieldBase::getFieldName()
     * @param eZContentClassAttribute $classAttribute
     * @param null $subAttribute
     * @param string $context
     *
     * @return bool|string
     */
    public static function getFieldName( eZContentClassAttribute $classAttribute, $subAttribute = null, $context = 'search' )
    {
        switch ( $classAttribute->attribute( 'data_type_string' ) )
        {
            case 'ezobjectrelation' :
            {
                if ( $subAttribute
                     && $subAttribute !== ''
                     && $subAttribute != self::DEFAULT_SUBATTRIBUTE
                     && ( $type = self::getTypeForSubattribute( $classAttribute, $subAttribute, $context ) ) )
                {
                    if ( in_array( $subAttribute, array_keys( eZSolr::metaAttributes() ) ) )
                    {                        
                        return parent::generateSubmetaFieldName( $subAttribute, $classAttribute );
                    }
                    else
                    {
                        return parent::generateSubattributeFieldName( $classAttribute,
                                                                      $subAttribute,
                                                                      $type );
                    }
                }
                else
                {
                    return parent::generateAttributeFieldName( $classAttribute,
                                                               self::$subattributesDefinition[self::DEFAULT_SUBATTRIBUTE_TYPE] );
                }
            } break;

            case 'ezobjectrelationlist' :
            {
                  
                if ( $subAttribute and
                     $subAttribute !== '' and
                     $subAttribute != self::DEFAULT_SUBATTRIBUTE and
                     ( $type = self::getTypeForSubattribute( $classAttribute, $subAttribute, $context ) ) )
                {
                    if ( in_array( $subAttribute, array_keys( eZSolr::metaAttributes() ) ) )
                    {                        
                        return parent::generateSubmetaFieldName( $subAttribute, $classAttribute );
                    }
                    else
                    {                        
                        return parent::generateSubattributeFieldName( $classAttribute,
                                                                      $subAttribute,
                                                                      $type );
                    }
                }
                else
                {
                    // return the default field name here.
                    return parent::generateAttributeFieldName( $classAttribute,
                                                               self::$subattributesDefinition[self::DEFAULT_SUBATTRIBUTE_TYPE] );
                }
            } break;

            default:
            break;
        }
        return false;
    }

    /**
     * Identifies, based on the existing object relations, the type of the subattribute.
     *
     * @param eZContentClassAttribute $classAttribute
     * @param $subAttribute
     * @param $context
     *
     * @return bool|string
     */
    protected static function getTypeForSubattribute( eZContentClassAttribute $classAttribute, $subAttribute, $context  )
    {
        $q = "SELECT DISTINCT( ezcoa.data_type_string )
                FROM   ezcontentobject_link AS ezcol,
                       ezcontentobject_attribute AS ezcoa,
                       ezcontentclass_attribute AS ezcca,
                       ezcontentclass_attribute AS ezcca_target
                WHERE  ezcol.contentclassattribute_id={$classAttribute->attribute( 'id' )}
                  AND  ezcca_target.identifier='{$subAttribute}'
                  AND  ezcca.data_type_string='{$classAttribute->attribute( 'data_type_string' )}'
                  AND  ezcca.id=ezcol.contentclassattribute_id
                  AND  ezcol.to_contentobject_id = ezcoa.contentobject_id
                  AND  ezcoa.contentclassattribute_id = ezcca_target.id;
        ";
        $rows = eZDB::instance()->arrayQuery( $q );
        
        if ( count( $rows ) == 0 ) return self::DEFAULT_SUBATTRIBUTE_TYPE;
        
        if ( $rows and count( $rows ) > 0 )
        {
            if ( count( $rows ) > 1 )
            {
                $msg = "Multiple types were found for subattribute '{$subAttribute}' of class attribute #{$classAttribute->attribute( 'id' )} [{$classAttribute->attribute( 'data_type_string' )}]. This means that objects of different content classes were related through class attribute #{$classAttribute->attribute( 'id' )} and had attributes named '{$subAttribute}' of different datatypes : \n" . print_r( $rows , true ) . " Picking the first one here : {$rows[0]['data_type_string']}";
                eZDebug::writeWarning( $msg,  __METHOD__ );
            }
            return ezfSolrDocumentFieldBase::getClassAttributeType( new eZContentClassAttribute( $rows[0] ), null, $context );
        }
        return false;
    }

    /**
     * @see ezfSolrDocumentFieldBase::getFieldNameList()
     *
     * @todo Implement this
     */
    public static function getFieldNameList( eZContentClassAttribute $classAttribute, $exclusiveTypeFilter = array() )
    {
        return false;
    }

    /**
     * Extracts textual representation of a related content object. Used to populate a
     * default, full-text search field for an ezobjectrelation/ezobjectrelationlist
     * content object attribute.
     *
     * @return string The string representation of the related eZContentObject(s),
     *                then indexed in Solr.
     * @param eZContentObjectAttribute $contentObjectAttribute The ezobjectrelation/ezobjectrelationlist
     *                                                         textual representation shall be extracted from.
     */
    protected function getPlainTextRepresentation( eZContentObjectAttribute $contentObjectAttribute = null )
    {
        if ( $contentObjectAttribute === null )
        {
            $contentObjectAttribute = $this->ContentObjectAttribute;
        }

        $metaData = '';

        if ( $contentObjectAttribute )
        {
            $metaDataArray = $contentObjectAttribute->metaData();

            if( !is_array( $metaDataArray ) )
            {
                $metaDataArray = array( $metaDataArray );
            }

            foreach( $metaDataArray as $item )
            {
                $metaData .= $item['text'] . ' ';
            }
        }
        return trim( $metaData, "\t\r\n " );
    }
    

    // Get an Array of all sub Attributes
    protected function getArrayrelatedObject( eZContentObject $relatedObject, $contentClassAttribute, $metaData = null )
    {
        if ( $metaData === null )
        {
            $metaData = array();
        }
        
        if ( $relatedObject instanceof eZContentObject && $relatedObject->attribute( 'main_node_id' ) > 0 )
        {
            $objectName = $relatedObject->Name;
            $fieldName = parent::generateSubattributeFieldName( $contentClassAttribute,
                                                                'name',
                                                                self::DEFAULT_SUBATTRIBUTE_TYPE );
        
            if ( isset( $metaData[$fieldName] ) )
            {
                $metaData[$fieldName] = array_merge( $metaData[$fieldName], array( $objectName ) );
            }
            else
            {
                $metaData[$fieldName] = array( $objectName );
            }

            $baseList = $this->getBaseList( $relatedObject->attribute( 'current' ) );
            foreach( $baseList as $field )
            {
                /** @var eZContentClassAttribute $tmpClassAttribute */
                $tmpClassAttribute = $field->ContentObjectAttribute->attribute( 'contentclass_attribute' );
                $fieldName = $field->ContentObjectAttribute->attribute( 'contentclass_attribute_identifier' );
        
                $fieldNameArray = array();
                
                foreach( array_keys( eZSolr::$fieldTypeContexts ) as $context )
                {
                    $fieldNameArray[] = parent::generateSubattributeFieldName( $contentClassAttribute,
                                                                               $fieldName,
                                                                               ezfSolrDocumentFieldBase::getClassAttributeType( $tmpClassAttribute, null, $context ) );
                }
                $fieldNameArray = array_unique( $fieldNameArray );
                if ( $tmpClassAttribute->attribute( 'data_type_string' ) == 'ezobjectrelation' or
                     $tmpClassAttribute->attribute( 'data_type_string' ) == 'ezobjectrelationlist' )
                {
                    $finalValue = $field->getPlainTextRepresentation();
                }
                else
                {
                    $finalValue = $this->preProcessValue( $field->ContentObjectAttribute->metaData(),
                                                          parent::getClassAttributeType( $tmpClassAttribute ) );
        
                }
                
                foreach ( $fieldNameArray as $fieldNameValue )
                {
                    //eZCLI::instance()->output(var_dump($metaData));
                    if ( isset( $metaData[$fieldNameValue] ) )
                    {
                        $metaData[$fieldNameValue] = array_merge( $metaData[$fieldNameValue], array( trim( $finalValue, "\t\r\n " ) ) );
                    }
                    else
                    {
                        $metaData[$fieldNameValue] = array( trim( $finalValue, "\t\r\n " ) );
                    }
                    
                }
        
            }
        
            $metaAttributeValues = eZSolr::getMetaAttributesForObject( $relatedObject );

            foreach ( $metaAttributeValues as $metaInfo )
            {
                $value = ezfSolrDocumentFieldBase::preProcessValue( $metaInfo['value'], $metaInfo['fieldType'] );
                if ( !is_array( $value ) )
                {
                    $value = array( $value );
                }
                $metaData[ezfSolrDocumentFieldBase::generateSubmetaFieldName( $metaInfo['name'], $contentClassAttribute )] = $value;
            }
        }
        return $metaData;
    }

    /**
     * @see ezfSolrDocumentFieldBase::getData()
     */
    public function getData()
    {
        $contentClassAttribute = $this->ContentObjectAttribute->attribute( 'contentclass_attribute' );

        switch ( $contentClassAttribute->attribute( 'data_type_string' ) )
        {
            case 'ezobjectrelation':
            {
                $returnArray = array();
                
                $relatedObject = $this->ContentObjectAttribute->content();

                if ( $relatedObject )
                {
                    $returnArray = $this->getArrayrelatedObject($relatedObject, $contentClassAttribute);			
                }
                return $returnArray;
		
            } break;
                
            case 'ezobjectrelationlist' :
            {
                $returnArray = array();
                $content = $this->ContentObjectAttribute->content();

                foreach( $content['relation_list'] as $relationItem )
                {
                    $subObjectID = $relationItem['contentobject_id'];
                    
                    if ( !$subObjectID )
                    {
                        continue;
                    }
                    
                    $subObject = eZContentObjectVersion::fetchVersion( $relationItem['contentobject_version'], $subObjectID );
                    
                    if ( !$subObject )
                    {
                        continue;
                    }                       
                    
                    $metaAttributeValues = eZSolr::getMetaAttributesForObject( $subObject->attribute( 'contentobject' ) );
                    
                    foreach ( $metaAttributeValues as $metaInfo )
                    {
                      
                        $submetaFieldName = ezfSolrDocumentFieldBase::generateSubmetaFieldName( $metaInfo['name'], $contentClassAttribute );

                        if ( isset( $returnArray[$submetaFieldName] ) )
                        {
                            $returnArray[$submetaFieldName] = array_merge( $returnArray[$submetaFieldName], array( ezfSolrDocumentFieldBase::preProcessValue( $metaInfo['value'], $metaInfo['fieldType'] ) ) );
                        }
                        else
                        {
                            $returnArray[$submetaFieldName] = array( ezfSolrDocumentFieldBase::preProcessValue( $metaInfo['value'], $metaInfo['fieldType'] ) );
                        }
                    }
                }
                
                $contentClassAttribute = $this->ContentObjectAttribute->attribute( 'contentclass_attribute' );
	   
                $content = $this->ContentObjectAttribute->content();

                $returnArrayRelatedObject = array();
                
                foreach( $content['relation_list'] as $relationItem )
                {
                    $relatedObject = eZContentObject::fetch( $relationItem['contentobject_id'] );
            
                    if ( $relatedObject )
                    {
                        $returnArrayRelatedObject = $this->getArrayrelatedObject( $relatedObject,
                                                                                  $contentClassAttribute,
                                                                                  $returnArrayRelatedObject);			
                    }
                    $returnArray = array_merge_recursive( $returnArray, $returnArrayRelatedObject);
                }
		
                $result = array();
                foreach ( $returnArray as $key => $value )
                {
                    if ( is_array( $value ) )
                    {
                        $value = array_unique( $value );
                    }
                    $result[$key] = $value;
                }
                return $result;

            } break;
        }
        return array();
    }

    /**
     * Get ezfSolrDocumentFieldBase instances for all attributes of specified eZContentObjectVersion
     *
     * @param eZContentObjectVersion $objectVersion Instance of eZContentObjectVersion to fetch attributes from.
     * @return ezfSolrDocumentFieldBase[]|ocSolrDocumentFieldObjectRelation[] List of ezfSolrDocumentFieldBase instances.
     */
    function getBaseList( eZContentObjectVersion $objectVersion )
    {
        $returnList = array();
        // Get ezfSolrDocumentFieldBase instance for all attributes in related object
        foreach( $objectVersion->contentObjectAttributes( $this->ContentObjectAttribute->attribute( 'language_code' ) ) as $attribute )
        {
            /** @var eZContentObjectAttribute $attribute */
            if ( $attribute->attribute( 'contentclass_attribute' )->attribute( 'is_searchable' ) )
            {
                $returnList[] = ezfSolrDocumentFieldBase::getInstance( $attribute );
            }
        }
        return $returnList;
    }
}
