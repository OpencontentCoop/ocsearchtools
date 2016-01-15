<?php

class OCClassExtraParametersManager
{
    /**
     * @var OCClassExtraParametersManager[]
     */
    private static $instances = array();

    /**
     * @var eZContentClass
     */
    protected $class;

    /**
     * @var OCClassExtraParametersHandlerInterface[]
     */
    protected $handlers = array();

    /**
     * @var eZINI
     */
    protected $extraParametersIni;

    public static function instance( eZContentClass $class )
    {
        if ( !$class instanceof eZContentClass )
        {
            throw new Exception( "Class not found (" . __METHOD__ . ")" );
        }

        if ( !isset( self::$instances[$class->attribute( 'identifier' )] ) )
        {
            self::$instances[$class->attribute( 'identifier' )] = new OCClassExtraParametersManager( $class );
        }
        return self::$instances[$class->attribute( 'identifier' )];
    }

    public static function currentUserCanManageHandlers()
    {
        $access = eZUser::currentUser()->hasAccessTo( 'class' );
        return $access['accessWord'] == 'yes';
    }

    public static function issetHandlers()
    {
        return self::currentUserCanManageHandlers() ? count( eZINI::instance( 'occlassextraparameters.ini' )->variable( 'AvailableHandlers', 'Handlers' ) ) > 0 : false;
    }

    /**
     * @return OCClassExtraParametersHandlerInterface[]
     */
    public function getHandlers()
    {
        return self::currentUserCanManageHandlers() ? $this->handlers : array();
    }

    /**
     * @param $identifier
     *
     * @return null|OCClassExtraParametersHandlerInterface
     */
    public function getHandler( $identifier )
    {
        return isset( $this->handlers[$identifier] ) ? $this->handlers[$identifier] : null;
    }

    protected function __construct( eZContentClass $class )
    {
        $this->extraParametersIni = eZINI::instance( 'occlassextraparameters.ini' );
        $this->class = $class;
        $this->loadHandlers();
    }

    protected function loadHandlers()
    {
        $handlers = (array) $this->extraParametersIni->variable( 'AvailableHandlers', 'Handlers' );
        foreach( $handlers as $identifier => $className )
        {
            if ( class_exists( $className ) )
            {
                $interfaces = class_implements( $className );
                if ( in_array( 'OCClassExtraParametersHandlerInterface', $interfaces ) )
                {
                    $this->handlers[$identifier] = new $className( $this->class );
                }
                else
                {
                    eZDebug::writeError( "$className not implements OCClassExtraParametersHandlerInterface", __METHOD__ );
                }
            }
            else
            {
                eZDebug::writeError( "$className not found", __METHOD__ );
            }
        }
    }
}