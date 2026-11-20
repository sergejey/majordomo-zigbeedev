<?php

$dictionary = array(
    'ZIGBEEDEV_PROCESS_TYPE' => 'Process',
    'ZIGBEEDEV_PROCESS_TYPE_CHANGED' => 'Only when value has changed',
    'ZIGBEEDEV_PROCESS_TYPE_ANY' => 'Any incoming value',
    'ZIGBEEDEV_MQTT_TOPICS' => 'MQTT topics',
    'ZIGBEEDEV_MQTT_TOPICS_HINT' => 'Use "," for multiple topics',
    'ZIGBEEDEV_DEBUG_MODE' => 'Debug mode (extra logging)',
    'ZIGBEEDEV_AUTO_CREATE' => 'Create devices automatically (experimental!)',
    'ZIGBEEDEV_CHECK_UNSUPPORTED' => 'check for unsupported devices',
    'ZIGBEEDEV_SET_VALUE' => 'Set value',
    'ZIGBEEDEV_BATTERY' => 'Battery',
    'ZIGBEEDEV_MODEL_NAME' => 'Model name',
    'ZIGBEEDEV_MODEL_ID' => 'Model Id',
    'ZIGBEEDEV_MODEL_DESCRIPTION' => 'Model description',
    'ZIGBEEDEV_MANUFACTURER' => 'Manufacturer Id',
    'ZIGBEEDEV_VENDOR' => 'Vendor',
    'ZIGBEEDEV_TOPIC' => 'Topic',
    'ZIGBEEDEV_UNSUPPORTED_INFO' => 'Support of some devices can be improved and you can help by sending details below to the author',
    'ZIGBEEDEV_UNSUPPORTED_COPY' => 'Hit below to copy data.',
    'ZIGBEEDEV_COPIED' => 'Text copied to clipboard.',
    'ZIGBEEDEV_ALL_SUPPORTED' => 'All devices found should be fully supported.',
);

foreach ($dictionary as $k=>$v)
{
    if (!defined('LANG_' . $k))
    {
        define('LANG_' . $k, $v);
    }
}
