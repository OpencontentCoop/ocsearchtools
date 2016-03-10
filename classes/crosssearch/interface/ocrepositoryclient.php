<?php

interface OCRepositoryClientInterface
{
    /**
     * @param $parameters
     *
     * @return mixed
     */
    function init( $parameters );

    /**
     * @return string
     */
    function templateName();

    /**
     * @param string $action
     */
    function setCurrentAction( $action );

    /**
     * @param $parameters
     */
    function setCurrentActionParameters( $parameters );

    /**
     * @param int $remoteReference
     * @param int $localLocation
     *
     * @return eZContentObject
     */
    function import( $remoteReference, $localLocation );

    /**
     * @param eZModule $module
     * @param eZTemplate $tpl
     * @param int $remoteReference
     * @param int $localLocation
     *
     * @return void
     */
    public function handleImport( eZModule $module, eZTemplate $tpl, $remoteReference, $localLocation );
}