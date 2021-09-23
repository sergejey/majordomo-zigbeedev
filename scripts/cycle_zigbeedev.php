<?php
chdir(dirname(__FILE__) . '/../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");
set_time_limit(0);
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();

include_once(ROOT . "3rdparty/phpmqtt/phpMQTT.php");
include_once(DIR_MODULES . 'zigbeedev/zigbeedev.class.php');
$zigbeedev_module = new zigbeedev();
$zigbeedev_module->getConfig();

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

$client_name = "MajorDoMo Zigbeedev";
$client_name = $client_name . ' (#' . uniqid() . ')';

if ($zigbeedev_module->config['MQTT_AUTH']) {
    $username = $zigbeedev_module->config['MQTT_USERNAME'];
    $password = $zigbeedev_module->config['MQTT_PASSWORD'];
}

$host = 'localhost';

if ($zigbeedev_module->config['MQTT_HOST']) {
    $host = $zigbeedev_module->config['MQTT_HOST'];
}

if ($zigbeedev_module->config['MQTT_PORT']) {
    $port = $zigbeedev_module->config['MQTT_PORT'];
} else {
    $port = 1883;
}

if ($zigbeedev_module->config['MQTT_QUERY']) {
    $query = $zigbeedev_module->config['MQTT_QUERY'];
} else {
    $query = '/var/now/#';
}

$mqtt_client = new Bluerhinos\phpMQTT($host, $port, $client_name);

if ($zigbeedev_module->config['MQTT_AUTH']) {
    $connect = $mqtt_client->connect(true, NULL, $username, $password);
    if (!$connect) {
        exit(1);
    }
} else {
    $connect = $mqtt_client->connect();
    if (!$connect) {
        exit(1);
    }
}

$zigbeedev_module->refreshDevices();

$query_list = explode(',', $query);
$total = count($query_list);
echo date('H:i:s') . " Topics to watch: $query (Total: $total)\n";
for ($i = 0; $i < $total; $i++) {
    $path = trim($query_list[$i]);
    echo date('H:i:s') . " Path: $path\n";
    $topics[$path] = array("qos" => 0, "function" => "procmsg");
}
foreach ($topics as $k => $v) {
    echo date('H:i:s') . " Subscribing to: $k  \n";
    $rec = array($k => $v);
    $mqtt_client->subscribe($rec, 0);
}
$previousMillis = 0;

while ($mqtt_client->proc()) {
    $queue = checkOperationsQueue('zigbeedev_queue');
    foreach ($queue as $mqtt_data) {
        //echo "queue: ".json_encode($mqtt_data);
        $topic=$mqtt_data['DATANAME'];
        $data_value=json_decode($mqtt_data['DATAVALUE'],true);
        $value=$data_value['v'];
        $qos=0;
        if (isset($data_value['q'])) {
            $qos=$data_value['q'];
        }
        $retain=0;
        if (isset($data_value['r'])) {
            $retain=$data_value['r'];
        }
        if ($topic!='') {
            $mqtt_client->publish($topic, $value, $qos, $retain);
        }
    }

    $currentMillis = round(microtime(true) * 10000);

    if ($currentMillis - $previousMillis > 10000) {
        $previousMillis = $currentMillis;
        setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
        if (file_exists('./reboot') || IsSet($_GET['onetime'])) {
            $mqtt_client->close();
            $db->Disconnect();
            exit;
        }
    }
}

$mqtt_client->close();

function procmsg($topic, $msg) {

    $from_hub = 0;
    $did = $topic;
    if (!preg_match('/bridge\//',$topic)) {
        global $topics;
        $did = strtolower($did);
        foreach($topics as $t=>$v) {
            $t = strtolower($t);
            $t = preg_replace('/#$/','',$t);
            $did = str_replace($t,'',$did);
        }
        if (!preg_match('/^{/',$msg) && preg_match('/\/(.+)$/',$did,$m)) {
            $prop=$m[1];
            $msg = json_encode(array($prop=>$msg));
        }
        $did = preg_replace('/\/.+/','',$did);
    } else {
        $from_hub = 1;
        if (!preg_match('/^{/',$msg) && preg_match('/\/(\w+)$/',$did,$m)) {
            $prop=$m[1];
            $msg = json_encode(array($prop=>$msg));
        }
        $did = preg_replace('/\/bridge.+/','',$did);
    }
    global $latest_msg;
    $new_msg = time().' '.$topic.': '.$msg;
    if ($latest_msg==$new_msg) return;
    $latest_msg=$new_msg;
    if (!isset($topic) || !isset($msg)) return false;
    echo date("Y-m-d H:i:s") . " Received from {$topic} ($did, $from_hub): $msg\n";
    if (function_exists('callAPI')) {
        callAPI('/api/module/zigbeedev','GET',array('topic'=>$topic,'did'=>$did, 'msg'=>$msg,'hub'=>$from_hub));
    } else {
        global $zigbeedev_module;
        $zigbeedev_module->processMessage($topic, $did, $msg, $from_hub);
    }
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
