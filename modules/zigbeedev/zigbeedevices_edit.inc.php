<?php
/*
* @version 0.1 (wizard)
*/
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'zigbeedevices';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
if ($this->mode == 'update') {
    $ok = 1;
    // step: default
    if ($this->tab == '') {
        //updating '<%LANG_TITLE%>' (varchar, required)
        //$rec['TITLE']=gr('title');
        if ($rec['TITLE'] == '' && $rec['IEEEADDR']) {
            $rec['TITLE'] = $rec['IEEEADDR'];
        }
        if ($rec['TITLE'] == '') {
            $out['ERR_TITLE'] = 1;
            $ok = 0;
        }

        $rec['DESCRIPTION'] = gr('description');

        //updating 'IEEEADDR' (varchar)
        //$rec['IEEEADDR']=gr('ieeeaddr');
    }
    // step: data
    if ($this->tab == 'data') {
    }
    //UPDATING RECORD
    if ($ok) {
        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update
        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        $out['OK'] = 1;
    } else {
        $out['ERR'] = 1;
    }
}

if (gr('ok')) {
    $out['OK'] = 1;
}

// step: default
// step: data
if ($this->tab == 'data') {
}
if ($this->tab == 'data') {
    //dataset2
    $new_id = 0;
    global $delete_id;
    if ($delete_id) {
        SQLExec("DELETE FROM zigbeeproperties WHERE ID='" . (int)$delete_id . "'");
    }
    $properties = SQLSelect("SELECT * FROM zigbeeproperties WHERE DEVICE_ID='" . $rec['ID'] . "' ORDER BY TITLE");
    $total = count($properties);
    $prop_id = gr('prop_id', 'int');

    if (!$prop_id) {
        if ($this->canCreateDevice($rec['ID'])) {
            if ($this->mode == 'link_device') {
                $this->linkDevice($rec['ID'], gr('device_id', 'int'));
                $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&ok=1");
            }
            if ($this->mode == 'create_device') {
                $this->createDevice($rec['ID']);
                $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&ok=1");
            }
            $out['CAN_CREATE_DEVICE'] = 1;
            $device_type = $this->checkDeviceType($rec['ID']);
            $types = array("'impossible_device_type'");
            foreach ($device_type as $type => $details) {
                $types[] = "'$type'";
            }
            $devices = SQLSelect("SELECT ID, TITLE FROM devices WHERE TYPE IN (" . implode(',', $types) . ")");
            if (isset($devices[0])) {
                $out['DEVICES_TO_LINK'] = $devices;
            }
        } else {
            $seen_objects = array();
            $linked_devices = array();
            foreach ($properties as $prop) {
                if ($prop['LINKED_OBJECT'] != '' && !isset($seen_objects[$prop['LINKED_OBJECT']])) {
                    $seen_objects[$prop['LINKED_OBJECT']] = 1;
                    $sdevice = SQLSelectOne("SELECT ID, LINKED_OBJECT FROM devices WHERE LINKED_OBJECT='" . $prop['LINKED_OBJECT'] . "'");
                    if (isset($sdevice['ID'])) {
                        $linked_devices[] = array('ID' => $sdevice['ID']);
                        //$out['SIMPLE_DEVICE_ID'] = $sdevice['ID'];
                        //$out['SIMPLE_DEVICE_LINKED_OBJECT'] = $sdevice['LINKED_OBJECT'];
                    }
                }
            }
            if (count($linked_devices) > 0) {
                $out['LINKED_DEVICES'] = $linked_devices;
            }
        }
    }

    for ($i = 0; $i < $total; $i++) {
        if ($properties[$i]['ID'] == $new_id) continue;
        if ($properties[$i]['ID'] == $prop_id) {
            if ($this->mode == 'set') {
                $new_value = gr('value');
                $this->setDeviceData($properties[$i]['DEVICE_ID'], $properties[$i]['TITLE'], $new_value);
                $this->processData($rec, $properties[$i]['TITLE'], $new_value);
            }
            if ($this->mode == 'update') {

                $old_linked_object = $properties[$i]['LINKED_OBJECT'];
                $old_linked_property = $properties[$i]['LINKED_PROPERTY'];

                $properties[$i]['LINKED_OBJECT'] = gr('linked_object', 'trim');
                $properties[$i]['LINKED_PROPERTY'] = gr('linked_property', 'trim');
                $properties[$i]['LINKED_METHOD'] = gr('linked_method', 'trim');
                $properties[$i]['READ_ONLY'] = gr('read_only', 'trim');
                $properties[$i]['PROCESS_TYPE'] = gr('process_type', 'int');
                SQLUpdate('zigbeeproperties', $properties[$i]);

                if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
                    addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
                } elseif ($old_linked_object && $old_linked_property && function_exists('removeLinkedPropertyIfNotUsed')) {
                    removeLinkedPropertyIfNotUsed('zigbeeproperties', $old_linked_object, $old_linked_property, $this->name);
                }
                $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&prop_id=" . $prop_id . "&ok=1");
            }
            foreach ($properties[$i] as $k => $v) {
                $out['PROP_' . $k] = $v;
            }
        }
        $properties[$i]['VALUE'] = str_replace('",', '", ', $properties[$i]['VALUE']);

    }
    $out['PROPERTIES'] = $properties;
    if (gr('ajax')) {
        header("Content-type:application/json");
        echo json_encode($properties, JSON_NUMERIC_CHECK);
        exit;
    }
}
if (is_array($rec)) {
    foreach ($rec as $k => $v) {
        if (!is_array($v)) {
            $rec[$k] = htmlspecialchars($v);
        }
    }
}
outHash($rec, $out);
