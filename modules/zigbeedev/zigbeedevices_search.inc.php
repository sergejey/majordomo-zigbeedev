<?php
/*
* @version 0.1 (wizard)
*/

$go_linked_object = gr('go_linked_object');
$go_linked_property = gr('go_linked_property');
if ($go_linked_object && $go_linked_property) {
    $tmp = SQLSelectOne("SELECT ID, DEVICE_ID FROM zigbeeproperties WHERE LINKED_OBJECT = '" . DBSafe($go_linked_object) . "' AND LINKED_PROPERTY='" . DBSafe($go_linked_property) . "'");
    if ($tmp['ID']) {
        $this->redirect("?id=" . $tmp['ID'] . "&view_mode=edit_zigbeedevices&id=" . $tmp['DEVICE_ID'] . "&tab=data&prop_id=".$tmp['ID']);
    }
}

global $session;
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$qry = "1";
// search filters
// QUERY READY
global $save_qry;
if ($save_qry) {
    $qry = $session->data['zigbeedevices_qry'];
} else {
    $session->data['zigbeedevices_qry'] = $qry;
}
if (!$qry) $qry = "1";
$sortby_zigbeedevices = "TITLE, ID DESC";
$out['SORTBY'] = $sortby_zigbeedevices;
// SEARCH RESULTS
$res = SQLSelect("SELECT *, (SELECT VALUE FROM zigbeeproperties WHERE zigbeedevices.ID = zigbeeproperties.DEVICE_ID AND title = 'availability') as availability FROM zigbeedevices WHERE $qry ORDER BY " . $sortby_zigbeedevices);
if ($res[0]['ID']) {
    //paging($res, 100, $out); // search result paging
    $total = count($res);
    for ($i = 0; $i < $total; $i++) {
        // some action for every record if required
        $data = SQLSelect("SELECT * FROM zigbeeproperties WHERE DEVICE_ID=" . $res[$i]['ID'] . " AND LINKED_OBJECT!=''");
        foreach ($data as $d) {
            $res[$i]['DATA'] .= '<b>' . $d['TITLE'] . '</b>';
            if ($d['LINKED_OBJECT']) {
                $dev_rec = SQLSelectOne("SELECT ID, TITLE FROM devices WHERE LINKED_OBJECT='" . $d['LINKED_OBJECT'] . "'");
                if ($dev_rec['TITLE']) {
                    $res[$i]['DATA'] .= ' - <a href="/panel/devices/'.$dev_rec['ID'].'.html">' . $dev_rec['TITLE']."</a>";
                }
                $res[$i]['DATA'] .= ' (' . $d['LINKED_OBJECT'];
                if ($d['LINKED_PROPERTY']) $res[$i]['DATA'] .= '.' . $d['LINKED_PROPERTY'];
                if ($d['LINKED_METHOD']) $res[$i]['DATA'] .= ' &gt; ' . $d['LINKED_METHOD'];
                if ($d['READ_ONLY']) $res[$i]['DATA'] .= ' [r]';
                if ($d['ONLY_NEW_VALUE']) $res[$i]['DATA'] .= ' [nv]';
                $res[$i]['DATA'] .= ')';
            }
            $res[$i]['DATA'] .= ' = <b>' . $d['VALUE'] . '</b>;<br/>';
        }
        if ($res[$i]['IS_BATTERY']) {
            if ($res[$i]['BATTERY_LEVEL']<30) {
                $res[$i]['BATTERY_WARN']='text-danger';
            } elseif ($res[$i]['BATTERY_LEVEL']<60) {
                $res[$i]['BATTERY_WARN']='text-warning';
            } else {
                $res[$i]['BATTERY_WARN']='text-success';
            }
        }
    }
    if (gr('ajax')) {
        header("Content-type:application/json");
        echo json_encode($res);
        exit;
    }
    $out['RESULT'] = $res;
}
