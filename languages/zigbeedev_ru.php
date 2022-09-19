<?php

$dictionary = array(
    'ZIGBEEDEV_PROCESS_TYPE' => 'Обрабатывать',
    'ZIGBEEDEV_PROCESS_TYPE_CHANGED' => 'Только если значение изменилось',
    'ZIGBEEDEV_PROCESS_TYPE_ANY' => 'Любое входящее значение',
);

foreach ($dictionary as $k=>$v)
{
    if (!defined('LANG_' . $k))
    {
        define('LANG_' . $k, $v);
    }
}