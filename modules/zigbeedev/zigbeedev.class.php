<?php
/**
 * ZigbeeDev
 * @package project
 * @author Wizard <sergejey@gmail.com>
 * @copyright http://majordomo.smartliving.ru/ (c)
 * @version 0.1 (wizard, 18:09:32 [Sep 17, 2021])
 */
//
//
class zigbeedev extends module
{
    /**
     * zigbeedev
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "zigbeedev";
        $this->title = "ZigbeeDev";
        $this->module_category = "<#LANG_SECTION_DEVICES#>";
        $this->checkInstalled();
    }

    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data = 1)
    {
        $p = array();
        if (isset($this->id)) {
            $p["id"] = $this->id;
        }
        if (isset($this->view_mode)) {
            $p["view_mode"] = $this->view_mode;
        }
        if (isset($this->edit_mode)) {
            $p["edit_mode"] = $this->edit_mode;
        }
        if (isset($this->data_source)) {
            $p["data_source"] = $this->data_source;
        }
        if (isset($this->tab)) {
            $p["tab"] = $this->tab;
        }
        return parent::saveParams($p);
    }

    /**
     * getParams
     *
     * Getting module parameters from query string
     *
     * @access public
     */
    function getParams()
    {
        global $id;
        global $mode;
        global $view_mode;
        global $edit_mode;
        global $data_source;
        global $tab;
        if (isset($id)) {
            $this->id = $id;
        }
        if (isset($mode)) {
            $this->mode = $mode;
        }
        if (isset($view_mode)) {
            $this->view_mode = $view_mode;
        }
        if (isset($edit_mode)) {
            $this->edit_mode = $edit_mode;
        }
        if (isset($data_source)) {
            $this->data_source = $data_source;
        }
        if (isset($tab)) {
            $this->tab = $tab;
        }
    }

    /**
     * Run
     *
     * Description
     *
     * @access public
     */
    function run()
    {
        global $session;
        $out = array();
        if ($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }
        if (isset($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (isset($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        $out['DATA_SOURCE'] = $this->data_source;
        $out['TAB'] = $this->tab;
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
    }

    /**
     * BackEnd
     *
     * Module backend
     *
     * @access public
     */
    function admin(&$out)
    {

        $this->getConfig();
        $out['MQTT_HOST'] = $this->config['MQTT_HOST'];
        $out['MQTT_PORT'] = $this->config['MQTT_PORT'];
        $out['MQTT_QUERY'] = $this->config['MQTT_QUERY'];
        if (!$out['MQTT_HOST']) {
            $out['MQTT_HOST'] = 'localhost';
        }
        if (!$out['MQTT_PORT']) {
            $out['MQTT_PORT'] = '1883';
        }
        if (!$out['MQTT_QUERY']) {
            $out['MQTT_QUERY'] = '/var/now/#';
        }
        $out['MQTT_USERNAME'] = $this->config['MQTT_USERNAME'];
        $out['MQTT_PASSWORD'] = $this->config['MQTT_PASSWORD'];
        $out['MQTT_AUTH'] = $this->config['MQTT_AUTH'];
        $out['DEBUG_MODE'] = $this->config['DEBUG_MODE'];

        if ($this->view_mode == 'update_settings') {
            $this->config['MQTT_HOST'] = gr('mqtt_host','trim');
            $this->config['MQTT_USERNAME'] = gr('mqtt_username','trim');
            $this->config['MQTT_PASSWORD'] = gr('mqtt_password','trim');
            $this->config['MQTT_AUTH'] = gr('mqtt_auth','int');
            $this->config['MQTT_PORT'] = gr('mqtt_port','int');
            $this->config['MQTT_QUERY'] = gr('mqtt_query','trim');
            $this->config['DEBUG_MODE'] = gr('debug_mode','int');
            $this->saveConfig();
            setGlobal('cycle_zigbeedevControl', 'restart');
            $this->redirect("?");
        }

        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'zigbeedevices' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_zigbeedevices') {
                $this->search_zigbeedevices($out);
            }
            if ($this->view_mode == 'edit_zigbeedevices') {
                $this->edit_zigbeedevices($out, $this->id);
            }
            if ($this->view_mode == 'delete_zigbeedevices') {
                $this->delete_zigbeedevices($this->id);
                $this->redirect("?data_source=zigbeedevices");
            }
        }
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'zigbeeproperties') {
            if ($this->view_mode == '' || $this->view_mode == 'search_zigbeeproperties') {
                $this->search_zigbeeproperties($out);
            }
            if ($this->view_mode == 'edit_zigbeeproperties') {
                $this->edit_zigbeeproperties($out, $this->id);
            }
        }

        if ($this->mode=='refresh_devices') {
            $this->refreshDevices();
            $this->redirect("?ok=1");
        }

    }

    function refreshDevices() {
        $this->getConfig();
        $query = $this->config['MQTT_QUERY'];
        $query_list = explode(',', $query);
        $total = count($query_list);
        for($i=0;$i<$total;$i++) {
            $topic = trim($query_list[$i]);
            $topic = preg_replace('/#$/','',$topic);
            $this->mqttPublish($topic.'bridge/config/devices','');
            $this->mqttPublish($topic.'bridge/request/restart','');
        }
    }

    /**
     * FrontEnd
     *
     * Module frontend
     *
     * @access public
     */
    function usual(&$out)
    {
        if ($this->ajax) {
            global $op;
            $result = array();
            if ($op == 'process') {
                $topic = gr('topic');
                $did = gr('did');
                $msg = gr('msg');
                $hub = gr('hub');
                $this->processMessage($topic, $did, $msg, $hub);
                exit;
            }
        }
    }

    function api($params) {
        if ($_REQUEST['topic']) {
            $this->processMessage($_REQUEST['topic'], $_REQUEST['did'], $_REQUEST['msg'], $_REQUEST['hub']);
        }
        if ($params['publish']) {
            $this->mqttPublish($params['publish'],$params['msg']);
        }
    }

    /**
     * zigbeedevices search
     *
     * @access public
     */
    function search_zigbeedevices(&$out)
    {
        require(dirname(__FILE__) . '/zigbeedevices_search.inc.php');
    }

    /**
     * zigbeedevices edit/add
     *
     * @access public
     */
    function edit_zigbeedevices(&$out, $id)
    {
        require(dirname(__FILE__) . '/zigbeedevices_edit.inc.php');
    }

    /**
     * zigbeedevices delete record
     *
     * @access public
     */
    function delete_zigbeedevices($id)
    {
        $rec = SQLSelectOne("SELECT * FROM zigbeedevices WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM zigbeeproperties WHERE DEVICE_ID=".$rec['ID']);
        SQLExec("DELETE FROM zigbeedevices WHERE ID='" . $rec['ID'] . "'");
    }

    /**
     * zigbeeproperties search
     *
     * @access public
     */
    function search_zigbeeproperties(&$out)
    {
        require(dirname(__FILE__) . '/zigbeeproperties_search.inc.php');
    }

    /**
     * zigbeeproperties edit/add
     *
     * @access public
     */
    function edit_zigbeeproperties(&$out, $id)
    {
        require(dirname(__FILE__) . '/zigbeeproperties_edit.inc.php');
    }

    function setDeviceData($device_id, $property, $data) {
        $device_rec=SQLSelectOne("SELECT * FROM zigbeedevices WHERE ID=".(int)$device_id);
        if ($device_rec['FULL_PATH']) {
            if ($data[0]=="{")
                $json = "{\"$property\":$data}";
            else
                $json = json_encode(array($property=>$data));
            $this->mqttPublish($device_rec['FULL_PATH'].'/set',$json);
        }
    }

    function propertySetHandle($object, $property, $value)
    {
        $this->getConfig();
        $properties = SQLSelect("SELECT zigbeeproperties.* FROM zigbeeproperties WHERE LINKED_OBJECT LIKE '" . DBSafe($object) . "' AND LINKED_PROPERTY LIKE '" . DBSafe($property) . "' AND READ_ONLY <> 1");
        $total = count($properties);
        if ($total) {
            for ($i = 0; $i < $total; $i++) {
                $old_value = $properties[$i]['VALUE'];
                $new_value = $value;
                if ($old_value=='true' || $old_value=='false') {
                    if ($value) {
                        $new_value=true;
                    } else {
                        $new_value=false;
                    }
                } elseif ($old_value=='ON' || $old_value=='OFF') {
                    if ($value) {
                        $new_value='ON';
                    } else {
                        $new_value='OFF';
                    }
                } elseif ($old_value=='CLOSE' || $old_value=='OPEN') {
                    if ($value) {
                        $new_value='CLOSE';
                    } else {
                        $new_value='OPEN';
                    }
                } elseif (is_integer($value)) {
                    $new_value=(int)$value;
                } elseif (is_float($value)) {
                    $new_value=(float)$value;
                }
                //if ($properties[$i]['VALUE'])
                $this->setDeviceData($properties[$i]['DEVICE_ID'],$properties[$i]['TITLE'],$new_value);
            }
        }
    }

    function processMessage($path, $did, $value, $hub)
    {

        if (preg_match('/\#$/', $path)) {
            return 0;
        }

        $device=SQLSelectOne("SELECT * FROM zigbeedevices WHERE TITLE='".DBSafe($did)."'");
        if (!$device['ID']) {
            $device=SQLSelectOne("SELECT * FROM zigbeedevices WHERE IEEEADDR='".DBSafe($did)."'");
        }
        if (!$device['ID']) {
            $device=array('TITLE'=>$did,'IEEEADDR'=>$did);
            $device['UPDATED']=date('Y-m-d H:i:s');
            $device['FULL_PATH']=$path;
            $device['FULL_PATH']=preg_replace('/\/bridge.+/','',$device['FULL_PATH']);
            if ($hub) {
                $device['IS_HUB']=1;
            }
            $device['ID']=SQLInsert('zigbeedevices',$device);
        } else {
            $device['UPDATED']=date('Y-m-d H:i:s');
            SQLUpdate('zigbeedevices',$device);
        }

        if (preg_match('/^{/', $value)) {
            $ar = json_decode($value, true);
            if ($hub && $ar['type']=='devices' && is_array($ar['message'])) {
                $path = preg_replace('/bridge.+/','',$path);
                if ($this->config['DEBUG_MODE']) {
                    DebMes($value,'zigbeedev_devices');
                }
                $this->processListOfDevices($path, $ar['message']);
                return;
            }
            if ($hub && is_string($ar['devices']) && !empty($ar['devices'])) {
                $path = preg_replace('/bridge.+/','',$path);
                $devices = json_decode($ar['devices'], true);
                if ($this->config['DEBUG_MODE']) {
                    DebMes($ar['devices'],'zigbeedev_devices');
                }
                $this->processListOfDevices($path, $devices);
                return;
            }
            if ($hub && $ar['type']=='device_announce' && is_array($ar['meta'])) {
                if ($ar['meta']['ieeeAddr']) {
                    $friendly_name=$ar['meta']['friendly_name'];
                    if (!$friendly_name) {
                        $friendly_name=$ar['meta']['ieeeAddr'];
                    }
                    $dev=SQLSelectOne("SELECT * FROM zigbeedevices WHERE IEEEADDR='".$ar['meta']['ieeeAddr']."'");
                    if (!$dev['ID']) {
                        $dev['IEEEADDR']=$ar['meta']['ieeeAddr'];
                        $dev['TITLE']=$friendly_name;
                        $dev['UPDATED']=date('Y-m-d H:i:s');
                        SQLInsert('zigbeedevices',$dev);
                    } elseif ($dev['ID'] && $dev['TITLE']!=$friendly_name) {
                        $dev['TITLE']=$friendly_name;
                        $dev['UPDATED']=date('Y-m-d H:i:s');
                        SQLUpdate('zigbeedevices',$dev);
                    }
                }
            }
            foreach ($ar as $k => $v) {
                if (is_array($v)) $v = json_encode($v);
                if ($k=='action') {
                    $this->processData($device, 'action:'.$v, date('Y-m-d H:i:s'));
                }
                $this->processData($device, $k, $v);
            }
        }
    }

    function processListOfDevices($path, $data) {
        $total_devices = count($data);
        //DebMes(json_encode($data),'zigbeedev_devices');
        for($i=0;$i<$total_devices;$i++) {
            $device_data=$data[$i];
            if ($device_data['friendly_name']) {
                $device_data['path']=$path.$device_data['friendly_name'];
            } else {
                $device_data['path']=$path.$device_data['ieeeAddr'];
            }
            $rec=SQLSelectOne("SELECT * FROM zigbeedevices WHERE IEEEADDR='".$device_data['ieeeAddr']."'");
            if (!$rec['ID'] && $device_data['friendly_name']) {
                $rec=SQLSelectOne("SELECT * FROM zigbeedevices WHERE TITLE='".$device_data['friendly_name']."'");
            }

            $rec['IEEEADDR']=$device_data['ieeeAddr'] ?? $device_data['ieee_address'];
            $rec['TITLE']=$device_data['friendly_name'];
            if (!$rec['TITLE']) {
                $rec['TITLE']=$rec['IEADDR'];
            }
            $rec['FULL_PATH']=$device_data['path'];
            $rec['MANUFACTURER_ID']=''.($device_data['manufacturerID']?$device_data['manufacturerID']:$device_data['manufacturer']);
            $rec['MODEL']=''.($device_data['model']?$device_data['model']:$device_data['definition']['model']);
            $rec['MODEL_NAME']=''.($device_data['modelID']?$device_data['modelID']:$device_data['model_id']);
            $rec['MODEL_DESCRIPTION']=''.($device_data['description']?$device_data['description']:$device_data['definition']['description']);
            $rec['VENDOR']=''.($device_data['vendor']?$device_data['vendor']:$device_data['definition']['vendor']);
            if (!$rec['DESCRIPTION'] || preg_match('/^\-/',trim($rec['DESCRIPTION']))) {
                $rec['DESCRIPTION']=$rec['MODEL_DESCRIPTION'].' - '.$rec['TITLE'];
            }
            if (!$rec['ID']) {
                $rec['UPDATED'] = date('Y-m-d H:i:s');
                $rec['ID'] = SQLInsert('zigbeedevices',$rec);
            } else {
                SQLUpdate('zigbeedevices',$rec);
            }
            //DebMes(json_encode($rec,JSON_PRETTY_PRINT),'zigbeedev_devices');
            //DebMes(json_encode($device_data,JSON_PRETTY_PRINT),'zigbeedev_devices');
        }
    }

    function processData(&$device, $prop, $value) {
        $property=SQLSelectOne("SELECT * FROM zigbeeproperties WHERE TITLE='".DBSafe($prop)."' AND DEVICE_ID=".$device['ID']);
        if (!$property['ID']) {
            $property = array('TITLE'=>$prop,'DEVICE_ID'=>$device['ID']);
        }
        if (is_bool($value)) {
            if ($value===false) $value='false';
            if ($value===true) $value='true';
        } elseif (is_null($value)) {
            $value='';
        } elseif (strlen($value)>255) {
            $value=substr($value,0,255);
        }
        $property['VALUE']=$value;
        $property['UPDATED']=date('Y-m-d H:i:s');
        if (!$property['ID']) {
            $property['ID']=SQLInsert('zigbeeproperties',$property);
        } else {
            SQLUpdate('zigbeeproperties',$property);
        }

        if ($property['LINKED_OBJECT']) {
            $value = strtolower($value);
            if ($value=='false' || $value=='off' || $value=='no' || $value=='open' || $value=='offline') {
                $new_value = 0;
            } elseif ($value=='true' || $value=='on' || $value=='yes' || $value=='close' || $value=='online') {
                $new_value = 1;
            } else {
                $new_value = $value;
            }
            if ($property['LINKED_METHOD']) {
                callMethod($property['LINKED_OBJECT'].'.'.$property['LINKED_METHOD'],array('VALUE'=>$new_value, 'NEW_VALUE'=>$new_value, 'TITLE' => $prop));
            }
            if ($property['LINKED_PROPERTY']) {
                $current_value=gg($property['LINKED_OBJECT'].'.'.$property['LINKED_PROPERTY']);
                if ($current_value!=$new_value || $prop=="action" || $property['ONLY_NEW_VALUE']!=1) {
                    setGlobal($property['LINKED_OBJECT'].'.'.$property['LINKED_PROPERTY'],$new_value, array($this->name => '0'));
                }
            }
        }
        if ($prop=='battery' && $device['BATTERY_LEVEL']!=$value) {
            $device['IS_BATTERY']=1;
            $device['BATTERY_LEVEL']=$value;
            SQLUpdate('zigbeedevices',$device);
        }

    }

    function mqttPublish($topic, $value, $qos = 0, $retain = 0)
    {
        $data = array('v' => $value);
        if ($qos) {
            $data['q'] = $qos;
        }
        if ($retain) {
            $data['r'] = $retain;
        }
        //DebMes("Publishing to $topic: $value",'zigbeedev_publish');
        addToOperationsQueue('zigbeedev_queue', $topic, json_encode($data), true);
        return 1;

        /*
        include_once(ROOT . "3rdparty/phpmqtt/phpMQTT.php");
        if ($this->config['MQTT_CLIENT']) {//
            $client_name = $this->config['MQTT_CLIENT'];
        } else {
            $client_name = "MajorDoMo MQTT";
        }

        if ($this->config['MQTT_AUTH']) {
            $username = $this->config['MQTT_USERNAME'];
            $password = $this->config['MQTT_PASSWORD'];
        }
        if ($this->config['MQTT_HOST']) {
            $host = $this->config['MQTT_HOST'];
        } else {
            $host = 'localhost';
        }
        if ($this->config['MQTT_PORT']) {
            $port = $this->config['MQTT_PORT'];
        } else {
            $port = 1883;
        }

        $mqtt_client = new Bluerhinos\phpMQTT($host, $port, $client_name . ' Client');
        if (!$mqtt_client->connect(true, NULL, $username, $password)) {
            return 0;
        }

        $mqtt_client->publish($topic, $value, $qos, $retain);

        $mqtt_client->close();
        */
    }

    function processCycle()
    {
        $this->getConfig();
        //to-do
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($data = '')
    {
        parent::install();
    }

    /**
     * Uninstall
     *
     * Module uninstall routine
     *
     * @access public
     */
    function uninstall()
    {
        SQLExec('DROP TABLE IF EXISTS zigbeedevices');
        SQLExec('DROP TABLE IF EXISTS zigbeeproperties');
        parent::uninstall();
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data)
    {
        /*
        zigbeedevices -
        zigbeeproperties -
        */
        $data = <<<EOD
 zigbeedevices: ID int(10) unsigned NOT NULL auto_increment
 zigbeedevices: TITLE varchar(100) NOT NULL DEFAULT ''
 zigbeedevices: IEEEADDR varchar(255) NOT NULL DEFAULT ''
 zigbeedevices: DESCRIPTION varchar(255) NOT NULL DEFAULT ''
 zigbeedevices: IS_HUB int(3) unsigned NOT NULL DEFAULT '0'
 zigbeedevices: IS_BATTERY int(3) unsigned NOT NULL DEFAULT '0'
 zigbeedevices: BATTERY_LEVEL int(3) unsigned NOT NULL DEFAULT '0'
 zigbeedevices: FULL_PATH varchar(255) NOT NULL DEFAULT ''
 zigbeedevices: MANUFACTURER_ID varchar(100) NOT NULL DEFAULT ''
 zigbeedevices: MODEL varchar(100) NOT NULL DEFAULT ''
 zigbeedevices: MODEL_NAME varchar(100) NOT NULL DEFAULT ''
 zigbeedevices: MODEL_DESCRIPTION varchar(255) NOT NULL DEFAULT ''
 zigbeedevices: VENDOR varchar(100) NOT NULL DEFAULT ''
 zigbeedevices: UPDATED datetime
 
 zigbeeproperties: ID int(10) unsigned NOT NULL auto_increment
 zigbeeproperties: TITLE varchar(100) NOT NULL DEFAULT ''
 zigbeeproperties: VALUE varchar(255) NOT NULL DEFAULT ''
 zigbeeproperties: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 zigbeeproperties: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 zigbeeproperties: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 zigbeeproperties: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 zigbeeproperties: READ_ONLY varchar(1) NOT NULL DEFAULT ''
 zigbeeproperties: ONLY_NEW_VALUE varchar(1) NOT NULL DEFAULT ''
 zigbeeproperties: UPDATED datetime
EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDE3LCAyMDIxIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
