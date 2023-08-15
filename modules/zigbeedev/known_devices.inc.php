<?php

$models = array(
    'lumi.sensor_motion' => array(                    // Xiaomi motion sensor RTCGQ01LM
        'motion' => array(
            'properties' => array(
                'occupancy' => 'status',
                'battery' => 'batteryLevel',
            ),
            'settings' => array(
                'batteryOperated' => 1,
            )
        )
    ),
    'E1525/E1745' => 'lumi.sensor_motion',            // TRADFRI motion sensor
    'lumi.sensor_motion.aq2' => 'lumi.sensor_motion', // Aqara RTCGQ11LM

    'lumi.sensor_magnet' => array(                    // Xiaomi door sensor MCCGQ01LM
        'openclose' => array(
            'properties' => array(
                'contact' => 'status',
                'battery' => 'batteryLevel',
            ),
            'settings' => array(
                'batteryOperated' => 1,
            )
        )
    ),

    'lumi.sensor_wleak.aq1' => array(                 // Aqara SJCGQ11LM
        'leak' => array(
            'properties' => array(
                'water_leak' => 'status',
                'battery' => 'batteryLevel',
            ),
            'settings' => array(
                'batteryOperated' => 1,
            )
        )
    ),

    'lumi.weather' => array(                        // Xiaomi temp sensor WSDCGQ11LM
        'sensor_temphum' => array(
            'properties' => array(
                'temperature' => 'value',
                'humidity' => 'valueHumidity',
                'battery' => 'batteryLevel',
            ),
            'settings' => array(
                'batteryOperated' => 1,
            )
        )

    ),

    'lumi.sen_ill.mgl01' => array(                  // MiJia light sensor GZCGQ01LM
        'sensor_light' => array(
            'properties' => array(
                'illuminance_lux' => 'value',
                'battery' => 'batteryLevel',
            ),
            'settings' => array(
                'batteryOperated' => 1,
                'unit' => 'lux',
            )
        )
    ),

    'TS0001' => array(                              // Sonoff wall switch WHD02
        'relay' => array(
            'properties' => array(
                'state' => 'status',
            ),
        )
    ),

    'LED2002G5' => array(                             // TRADFRI bulb E14 WS globe opal 470lm
        'dimmer' => array(
            'properties' => array(
                'state' => 'status',
                'brightness' => 'levelWork',
            ),
            'settings' => array(
                'minWork' => 0,
                'minWork' => 254,
                'setMaxTurnOn' => 1,
            )
        )
    ),
    'LED1837R5' => 'LED2002G5',                       // TRADFRI bulb GU10 WW 400lm
    'LED1836G9' => 'LED2002G5',                       // TRADFRI bulb E27 WW 806lm

    'E1812' => array(                                 // IKEA button E1812
        'button' => array(
            'properties' => array(
                'battery' => 'batteryLevel',
            ),
            'methods' => array(
                'action:on' => 'pressed',
            ),
            'settings' => array(
                'batteryOperated' => 1,
            )
        )
    )

);