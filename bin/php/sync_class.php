<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(array('description' => ("Sync class \n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions(
    '[class:][remote:][install][force][remove_extra][dry-run][override]',
    '',
    array(
        'dry-run' => 'Esegue solo la comparazione',
        'class' => 'Identificatore della classe',
        'remote' => 'Url remoto',
        'install' => 'Installa la classe se non esiste in locale',
        'force' => 'Forza la sincronizzazione',
        'remove_extra' => 'Rimuove attributi locali aggiuntivi',
        'override' => 'Reinstalla la classe',
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
    if (!$class instanceof eZContentClass && !$options['install']) {
        throw new Exception("Classe $classIdentifier non trovata");
    }

    if ($options['remote']) {
        $remoteUrl = $options['remote'];
    } else {
        throw new Exception("Specificare l'url remoto");
    }

    if (file_exists($remoteUrl)) {
        $remoteRequestUrl = $remoteUrl;
    } else {
        if (!eZHTTPTool::getDataByURL($options['remote'])) {
            throw new Exception("Url remoto non raggiungibile");
        }

        $remoteRequestUrl = rtrim($options['remote'], '/') . '/classtools/definition/';
    }

    $toolOptions = $options['override'] && !$options['dry-run'] ? array('override' => true) : array();

    $tools = new OCClassTools($classIdentifier, $options['install'], $toolOptions, $remoteRequestUrl);
    if ($options['dry-run']) {
        $tools->compare();
        $result = $tools->getData();
        if ($result->missingAttributes) {
            $cli->error('Attributi mancanti rispetto al prototipo');
            foreach ($result->missingAttributes as $identifier => $original) {
                $cli->notice(" -> $identifier ({$original->DataTypeString})");
            }
        }
        if ($result->extraAttributes) {
            $cli->error('Attributi aggiuntivi rispetto al prototipo');
            foreach ($result->extraAttributes as $attribute) {
                $detail = $result->extraDetails[$attribute->Identifier];
                $cli->notice(" -> {$attribute->Identifier} ({$attribute->DataTypeString}) ({$detail['count']} oggetti)");
            }
        }
        if ($result->hasDiffAttributes) {
            $identifiers = array_keys($result->diffAttributes);
            $errors = array_intersect(array_keys($result->errors), $identifiers);
            $warnings = array_intersect(array_keys($result->warnings), $identifiers);

            if (count($errors) > 0)
                $cli->error('Attributi che differiscono dal prototipo: ' . count($result->diffAttributes));
            elseif (count($warnings) > 0)
                $cli->warning('Attributi che differiscono dal prototipo: ' . count($result->diffAttributes));
            else
                $cli->notice('Attributi che differiscono dal prototipo: ' . count($result->diffAttributes));

            if (isset($options['verbose'])) {
                foreach ($result->diffAttributes as $identifier => $value) {
                    if (isset($result->errors[$identifier]))
                        $cli->error(" -> $identifier");
                    elseif (isset($result->warnings[$identifier]))
                        $cli->warning(" -> $identifier");
                    else
                        $cli->notice(" -> $identifier");

                    foreach ($value as $diff) {
                        if (isset($result->errors[$identifier][$diff['field_name']]))
                            $cli->error("    {$diff['field_name']}");
                        elseif (isset($result->warnings[$identifier][$diff['field_name']]))
                            $cli->warning("    {$diff['field_name']}");
                        else
                            $cli->notice("    {$diff['field_name']}");
                    }
                }
            }
        }
        if ($result->hasDiffProperties) {
            if (isset($result->errors['properties']))
                $cli->notice('Proprietà che differiscono dal prototipo: ' . count($result->diffProperties));
            elseif (isset($result->warnings['properties']))
                $cli->notice('Proprietà che differiscono dal prototipo: ' . count($result->diffProperties));
            else
                $cli->notice('Proprietà che differiscono dal prototipo: ' . count($result->diffProperties));

            if (isset($options['verbose'])) {
                foreach ($result->diffProperties as $property) {
                    if (isset($result->errors['properties'][$property['field_name']]))
                        $cli->error("    {$property['field_name']}");
                    elseif (isset($result->warnings['properties'][$property['field_name']]))
                        $cli->warning("    {$property['field_name']}");
                    else
                        $cli->notice("    {$property['field_name']}");
                }
            }
        }
    } else {
        $tools->sync($options['force'], $options['remove_extra']);
    }

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
