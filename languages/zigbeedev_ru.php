<?php

$dictionary = array(
    'ZIGBEEDEV_PROCESS_TYPE' => 'Обрабатывать',
    'ZIGBEEDEV_PROCESS_TYPE_CHANGED' => 'Только если значение изменилось',
    'ZIGBEEDEV_PROCESS_TYPE_ANY' => 'Любое входящее значение',
    'ZIGBEEDEV_MQTT_TOPICS' => 'MQTT-топики',
    'ZIGBEEDEV_MQTT_TOPICS_HINT' => 'Несколько топиков указываются через ","',
    'ZIGBEEDEV_DEBUG_MODE' => 'Режим отладки (расширенное логирование)',
    'ZIGBEEDEV_AUTO_CREATE' => 'Создавать устройства автоматически (экспериментально!)',
    'ZIGBEEDEV_CHECK_UNSUPPORTED' => 'проверить неподдерживаемые устройства',
    'ZIGBEEDEV_SET_VALUE' => 'Задать значение',
    'ZIGBEEDEV_BATTERY' => 'Батарея',
    'ZIGBEEDEV_MODEL_NAME' => 'Модель',
    'ZIGBEEDEV_MODEL_ID' => 'Идентификатор модели',
    'ZIGBEEDEV_MODEL_DESCRIPTION' => 'Описание модели',
    'ZIGBEEDEV_MANUFACTURER' => 'Идентификатор производителя',
    'ZIGBEEDEV_VENDOR' => 'Производитель',
    'ZIGBEEDEV_TOPIC' => 'Топик',
    'ZIGBEEDEV_UNSUPPORTED_INFO' => 'Поддержку некоторых устройств можно улучшить. Вы можете помочь, отправив автору данные ниже',
    'ZIGBEEDEV_UNSUPPORTED_COPY' => 'Нажмите на поле ниже, чтобы скопировать данные.',
    'ZIGBEEDEV_COPIED' => 'Текст скопирован в буфер обмена.',
    'ZIGBEEDEV_ALL_SUPPORTED' => 'Все найденные устройства должны полностью поддерживаться.',
);

foreach ($dictionary as $k=>$v)
{
    if (!defined('LANG_' . $k))
    {
        define('LANG_' . $k, $v);
    }
}
