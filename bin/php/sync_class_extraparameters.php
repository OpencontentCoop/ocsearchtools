<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(array('description' => ("Sync class extra parameters\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions(
    '[class:][remote:]',
    '',
    array(
        'class' => 'Identificatore della classe',
        'remote' => 'Url remoto'
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

try {
    if ($options['class']) {
        $classIdentifier = $options['class'];
    } else {
        throw new Exception("Specificare la classe");
    }

    $class = eZContentClass::fetchByIdentifier($classIdentifier);
    if (!$class instanceof eZContentClass) {
        throw new Exception("Classe $classIdentifier non trovata");
    }

    if ($options['remote']) {
        $remoteUrl = $options['remote'];
    } else {
        throw new Exception("Specificare l'url remoto");
    }

    $remoteRequestUrl = rtrim( $remoteUrl, '/' ) . '/classtools/extra_definition/' . $class->attribute('identifier');
    $remoteData = json_decode(eZHTTPTool::getDataByURL($remoteRequestUrl), true);

    if($remoteData == false){
        throw new Exception("Dati remoti non trovati", 1);
    }

    $cli->warning("Sincronizzazione extra parameters per la classe $classIdentifier con il remoto $remoteUrl");
    OCClassExtraParametersManager::instance($class)->sync($remoteData);

    if (method_exists('OpenPAINI', 'clearDynamicIniCache')){
        $cli->notice("Svuoto cache dinamica OpenPAINI");
        OpenPAINI::clearDynamicIniCache();
        eZContentCacheManager::clearAllContentCache();
    }

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
