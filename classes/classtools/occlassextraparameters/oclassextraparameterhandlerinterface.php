<?php

interface OCClassExtraParametersHandlerInterface
{
    public function __construct( eZContentClass $class );

    public function getIdentifier();

    public function getName();

    public function loadParameters();

    public function storeParameters( $data );

    /**
     * @return string[]
     */
    public function attributes();

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasAttribute( $key );

    /**
     * @param $key
     *
     * @return mixed
     */
    public function attribute( $key );

}