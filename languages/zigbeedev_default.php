<?php

$dictionary = array(
    'ZIGBEEDEV_PROCESS_TYPE' => 'Process',
    'ZIGBEEDEV_PROCESS_TYPE_CHANGED' => 'Only when value has changed',
    'ZIGBEEDEV_PROCESS_TYPE_ANY' => 'Any incoming value',
);

foreach ($dictionary as $k=>$v)
{
    if (!defined('LANG_' . $k))
    {
        define('LANG_' . $k, $v);
    }
}