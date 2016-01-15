<?php

class OCTableViewClassExtraParameters extends OCClassExtraParametersHandlerBase
{

    public function getIdentifier()
    {
        return 'table_view';
    }

    protected function getName()
    {
        return "Visualizzazione tabellare";
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $attributes[] = 'show';
        $attributes[] = 'show_label';
        $attributes[] = 'show_empty';
        $attributes[] = 'collapse_label';

        return $attributes;
    }

    public function attribute( $key )
    {
        switch( $key )
        {
            case 'show':
                return $this->getAttributeIdentifierListByParameter( 'show' );

            case 'show_label':
                return $this->getAttributeIdentifierListByParameter( 'show_label' );

            case 'show_empty':
                return $this->getAttributeIdentifierListByParameter( 'show_empty', 1, false );

            case 'collapse_label':
                return $this->getAttributeIdentifierListByParameter( 'collapse_label', 1, false );
        }

        return parent::attribute( $key );
    }

}