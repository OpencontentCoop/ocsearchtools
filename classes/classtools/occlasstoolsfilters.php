<?php

use Opencontent\Opendata\Rest\Client\HttpClient;

class OCClassToolsFilters
{
    private static $remoteLocaleTagsMap = array();

    private static $remoteLocaleClassAttributeDataMap = array();

    private static $modifiedAttributeRegistry = array();

    /**
     * @param bool $simpleComparison
     * @param eZContentClass|eZContentClassAttribute $objectContext
     * @param string $propertyIdentifier
     * @param string $remoteProperty
     * @param string $localeProperty
     * @return bool
     */
    public static function propertyIsEqual($simpleComparison, $objectContext, $propertyIdentifier, $remoteProperty, $localeProperty)
    {
        if ($objectContext instanceof eZContentClassAttribute
            && !in_array($objectContext->attribute('identifier'), self::$modifiedAttributeRegistry)) {

            // Filter eztags subtree comparison
            if ($objectContext->attribute('data_type_string') == 'eztags'
                && $propertyIdentifier == eZTagsType::SUBTREE_LIMIT_FIELD) {

                $localeTag = self::getLocaleTagFromRemoteTag($remoteProperty);
                if ($localeTag) {
                    
                    return $localeProperty == $localeTag->attribute('id');
                }
            }

            // Filter ezobjectrelationlist default placement comparison
            if ($objectContext->attribute('data_type_string') == 'ezobjectrelationlist'
                && $propertyIdentifier == 'data_text5') {

                $localeClassAttributeData = self::getLocaleClassAttributeDataFromRemoteClassAttributeData($remoteProperty);
                if ($localeClassAttributeData) {
                    $classAttributeContent = self::parseRelationListXML($localeClassAttributeData);
        			$localeFromRemoteNodeId = (int)$classAttributeContent['default_placement']['node_id'];
                    
                    $classAttributeContent = self::parseRelationListXML($localeProperty);
                    $localeNodeId = (int)$classAttributeContent['default_placement']['node_id'];

                    return $localeFromRemoteNodeId == $localeNodeId;
                }
            }

        }
        return $simpleComparison;
    }

    /**
     * @param stdClass $originalAttribute
     * @param stdClass $remoteClass
     * @return mixed
     */
    public static function filterOriginalAttribute($originalAttribute, $remoteClass)
    {
        // Filter eztags subtree parameter
        if ($originalAttribute->DataTypeString == 'eztags') {
            $localeTag = self::getLocaleTagFromRemoteTag($originalAttribute->DataInt1);
            if ($localeTag) {
                $originalAttribute->DataInt1 = $localeTag->attribute('id');
                self::$modifiedAttributeRegistry[] = $originalAttribute->Identifier;
            }
        }

        // Filter ezobjectrelationlist default placement parameter
        if ($originalAttribute->DataTypeString == 'ezobjectrelationlist') {
            $localeClassAttributeData = self::getLocaleClassAttributeDataFromRemoteClassAttributeData($originalAttribute->DataText5);
            if ($localeClassAttributeData) {
                $originalAttribute->DataText5 = $localeClassAttributeData;
                self::$modifiedAttributeRegistry[] = $originalAttribute->Identifier;
            }
        }
        return $originalAttribute;
    }

    private static function getLocaleTagFromRemoteTag($remoteProperty)
    {
        if (isset(self::$remoteLocaleTagsMap[$remoteProperty])) {
            return self::$remoteLocaleTagsMap[$remoteProperty];
        }

        self::$remoteLocaleTagsMap[$remoteProperty] = false;

        try {
            $client = new OCClassToolsFiltersTagClient(self::getRemoteHost());
            $remoteTag = $client->readTag($remoteProperty);
            if (isset($remoteTag['keyword'])) {
                $tags = eZTagsObject::fetchByKeyword($remoteTag['keyword']);
                if (isset($tags[0]) && $tags[0] instanceof eZTagsObject) {
                    self::$remoteLocaleTagsMap[$remoteProperty] = $tags[0];
                } else {
                    eZDebug::writeError($remoteTag['keyword'], 'Tag locale non trovato');
                }
            }
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), 'Tag remoto non trovato');
        }

        return self::$remoteLocaleTagsMap[$remoteProperty];
    }

    private static function getLocaleClassAttributeDataFromRemoteClassAttributeData($remoteProperty)
    {
        if (isset(self::$remoteLocaleClassAttributeDataMap[$remoteProperty])) {
            return self::$remoteLocaleClassAttributeDataMap[$remoteProperty];
        }

        self::$remoteLocaleClassAttributeDataMap[$remoteProperty] = false;

        $classAttributeContent = self::parseRelationListXML($remoteProperty);
        $remoteNodeId = (int)$classAttributeContent['default_placement']["node_id"];

        if ($remoteNodeId == 0) {
            return false;
        }

        try {
            $client = new HttpClient(self::getRemoteHost());
            $remoteNode = $client->browse($remoteNodeId);
            if (isset($remoteNode['nodeRemoteId'])) {
                $localeNode = eZContentObjectTreeNode::fetchByRemoteID($remoteNode['nodeRemoteId']);
                if ($localeNode instanceof eZContentObjectTreeNode) {
                    $classAttributeContent['default_placement']["node_id"] = $localeNode->attribute('node_id');
                    $doc = eZObjectRelationListType::createClassDOMDocument($classAttributeContent);
                    $docText = eZObjectRelationListType::domString( $doc );
                    self::$remoteLocaleClassAttributeDataMap[$remoteProperty] = $docText;
                } else {
                    eZDebug::writeError($remoteNode['nodeRemoteId'], 'Nodo locale non trovato');
                }
            }
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), 'Tag remoto non trovato');
        }

        return self::$remoteLocaleClassAttributeDataMap[$remoteProperty];
    }

    private static function parseRelationListXML($remoteProperty)
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML($remoteProperty);

        $content = array('object_class' => '',
            'selection_type' => 0,
            'type' => 0,
            'class_constraint_list' => array(),
            'default_placement' => false
        );
        $root = $doc->documentElement;
        $objectPlacement = $root->getElementsByTagName('contentobject-placement')->item(0);

        if ($objectPlacement and $objectPlacement->hasAttributes()) {
            $nodeID = $objectPlacement->getAttribute('node-id');
            $content['default_placement'] = array('node_id' => $nodeID);
        }
        $constraints = $root->getElementsByTagName('constraints')->item(0);
        if ($constraints) {
            $allowedClassList = $constraints->getElementsByTagName('allowed-class');
            /** @var DOMElement $allowedClass */
            foreach ($allowedClassList as $allowedClass) {
                $content['class_constraint_list'][] = $allowedClass->getAttribute('contentclass-identifier');
            }
        }
        $type = $root->getElementsByTagName('type')->item(0);
        if ($type) {
            $content['type'] = $type->getAttribute('value');
        }
        $selectionType = $root->getElementsByTagName('selection_type')->item(0);
        if ($selectionType) {
            $content['selection_type'] = $selectionType->getAttribute('value');
        }
        $objectClass = $root->getElementsByTagName('object_class')->item(0);
        if ($objectClass) {
            $content['object_class'] = $objectClass->getAttribute('value');
        }

        return $content;
    }

    private static function getRemoteHost()
    {
        $remoteUrl = parse_url(OCClassTools::getRemoteUrl());
        return $remoteUrl['host'];
    }
}

class OCClassToolsFiltersTagClient extends HttpClient
{
    public function __construct(
        $server,
        $login = null,
        $password = null,
        $apiEnvironmentPreset = 'content',
        $apiEndPointBase = '/api/opendata/v2'
    )
    {
        parent::__construct($server, $login, $password, $apiEnvironmentPreset, $apiEndPointBase);
        $this->apiEnvironmentPreset = 'tags_tree';
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function readTag($name)
    {
        return $this->request('GET', $this->buildUrl($name));
    }

    protected function buildUrl($path)
    {
        $request = $this->server . $this->apiEndPointBase . '/' . $this->apiEnvironmentPreset . '/' . urlencode($path);

        return $request;
    }
}