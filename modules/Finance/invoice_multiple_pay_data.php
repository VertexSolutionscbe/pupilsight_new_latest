<?php

$newpost = array();
foreach($_POST as $pst){
    foreach($pst as $k=>$v){
        foreach($v as $nv){
            $newpost[$k][$nv['name']] = $nv['value'];
        }
    }
}

echo json_encode($newpost);

?>