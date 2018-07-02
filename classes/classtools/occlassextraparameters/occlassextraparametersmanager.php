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

    public static function currentUserCanEditHandlers( $handlerIdentifier = null )
    {
        $access = eZUser::currentUser()->hasAccessTo( 'class' );
        return $access['accessWord'] == 'yes';
    }

    public static function issetHandlers()
    {
        return count( (array)eZINI::instance( 'occlassextraparameters.ini' )->variable( 'AvailableHandlers', 'Handlers' ) ) > 0;
    }

    /**
     * @return OCClassExtraParametersHandlerInterface[]
     */
    public function getHandlers()
    {
        return $this->handlers;
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

    public function getAllParameters()
    {
        $result = array();
        $handlers = $this->getHandlers();
        foreach ($handlers as $handelrIdentifier => $handler) {
            $parameters = $handler->getParameters();
            $data = array();
            foreach ($parameters as $parameter) {
                $data[$parameter->attribute(OCClassExtraParameters::getKeyDefinitionName())][$parameter->attribute('attribute_identifier')] = $parameter->attribute('value');                    
            }
            if (!empty($data)){
                $result[$handelrIdentifier] = $data;
            }
        }
        $this->ksortRecursive($result);

        return $result;
    }

    public function compare($externalData)
    {
        $localData = $this->getAllParameters();

        return $this->diffRecursive($externalData, $localData);
    }

    public function sync($externalData)
    {        
        $db = eZDB::instance();
        $db->setErrorHandling( eZDB::ERROR_HANDLING_EXCEPTIONS );
        foreach ($externalData as $handler => $handlerValues) {
            foreach ($handlerValues as $key => $values) {
                foreach ($values as $attribute => $value) {
                    $row = array(
                        'class_identifier' => $this->class->attribute('identifier'),
                        'attribute_identifier' => $attribute,
                        'handler' => $handler,
                        OCClassExtraParameters::getKeyDefinitionName() => $key,
                        'value' => $value
                    );
                    $parameter = new OCClassExtraParameters($row);
                    $parameter->store();
                }
            }
        }

        $handlers = $this->getHandlers();
        foreach ($handlers as $handelrIdentifier => $handler) {
            $parameters = $handler->loadParameters(true);
        }
    }

    private function diffRecursive($array1, $array2)
    {
        $difference = array();
        foreach ($array1 as $key => $value) {
            if (is_array($value) && isset($array2[$key])) { // it's an array and both have the key
                $new_diff = $this->diffRecursive($value, $array2[$key]);
                if (!empty($new_diff))
                    $difference[$key] = $new_diff;
            } else if (is_string($value) && !in_array($value, $array2)) { // the value is a string and it's not in array B
                $difference[$key] = $value . " is missing in local data";
            } else if (!is_numeric($key) && !array_key_exists($key, $array2)) { // the key is not numberic and is missing from array B
                $difference[$key] = "Missing in local data";
            }
        }
        return $difference;
    }

    private function ksortRecursive(&$array, $sort_flags = SORT_REGULAR) {
        if (!is_array($array)) return false;
        ksort($array, $sort_flags);
        foreach ($array as &$arr) {
            $this->ksortRecursive($arr, $sort_flags);
        }
        return true;
    }
}
