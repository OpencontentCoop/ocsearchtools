<?php

class ocSolrDocumentFieldMatrix extends ezfSolrDocumentFieldBase
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
            case 'ezmatrix':
            {
                if ( $subAttribute and $subAttribute !== '' )
                {
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
        }
        return null;
    }

    public function getData()
    {
        $data = array();

        /** @var eZContentClassAttribute $contentClassAttribute */
        $contentClassAttribute = $this->ContentObjectAttribute->contentClassAttribute();

        /** @var \eZMatrix $attributeContents */
        $attributeContents = $this->ContentObjectAttribute->content();

        if ($attributeContents instanceof eZMatrix) {

            $columns = (array)$attributeContents->attribute('columns');
            $rows = (array)$attributeContents->attribute('rows');

            $keys = $values = $contents = $stringList = array();
            foreach ($columns['sequential'] as $column) {
                $keys[] = $column['identifier'];
            }
            foreach ($rows['sequential'] as $row) {
                $contents[] = array_combine($keys, $row['columns']);
            }
            foreach ( array_keys( eZSolr::$fieldTypeContexts ) as $context )
            {
                foreach($contents as $values) {
                    foreach ($values as $key => $value) {
                        if (!empty($value)) {
                            $fieldName = self::getFieldName($contentClassAttribute, $key, $context);
                            $data[$fieldName] = $value;
                            $stringList[] = $value;
                        }
                    }
                }
            }

            foreach ( array_keys( eZSolr::$fieldTypeContexts ) as $context ) {
                $fieldName = self::getFieldName($contentClassAttribute, null, $context);
                $data[$fieldName] = implode(' ', array_unique($stringList));
            }

        }
        return $data;
    }
}

?>
