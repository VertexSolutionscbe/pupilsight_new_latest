<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

//Setup variables
$output = '';
$id = isset($_GET['id'])? $_GET['id'] : '';
$id = preg_replace('/[^a-zA-Z0-9-_]/', '', $id);

$output .= "<script type='text/javascript'>";
    $output .= '$(document).ready(function() {';
        $output .= 'var options={';
            $output .= 'success: function(response) {';
                $output .= "tinymce.execCommand(\"mceFocus\",false,\"$id\"); tinyMCE.execCommand(\"mceInsertContent\", 0, response); formReset(); \$(\".".$id.'resourceQuickSlider").slideUp();';
            $output .= '}, ';
            $output .= "url: '".$_SESSION[$guid]['absoluteURL']."/modules/Planner/resources_addQuick_ajaxProcess.php',";
            $output .= "type: 'POST'";
        $output .= '};';

        $output .= "$('#".$id."ajaxForm').submit(function() {";
            $output .= '$(this).ajaxSubmit(options);';
            $output .= '$(".'.$id."resourceQuickSlider\").html(\"<div class='resourceAddSlider'><img style='margin: 10px 0 5px 0' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/loading.gif' alt='".__('Uploading')."' onclick='return false;' /><br/>".__('Loading').'</div>");';
            $output .= 'return false;';
        $output .= '});';
    $output .= '});';

    $output .= 'var formReset=function() {';
        $output .= "$('#".$id."resourceQuick').css('display','none');";
    $output .= '};';
$output .= '</script>';

$form = Form::create($id.'ajaxForm', '')->addClass('resourceQuick');

$form->addHiddenValue('id', $id);
$form->addHiddenValue($id.'address', $_SESSION[$guid]['address']);

$row = $form->addRow();
    $row->addWebLink("<img title='".__('Close')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/>")
        ->onClick("formReset(); \$(\".".$id."resourceQuickSlider\").slideUp();")->addClass('right');

for ($i = 1; $i < 6; ++$i) {
    $row = $form->addRow();
        $row->addLabel($id.'file'.$i, sprintf(__('File %1$s'), $i));
        $row->addFileUpload($id.'file'.$i)->setMaxUpload(false);
}

$row = $form->addRow();
    $row->addLabel('imagesAsLinks', __('Insert Images As'));
    $row->addSelect('imagesAsLinks')->fromArray(array('N' => __('Image'), 'Y' => __('Link')))->required();

$row = $form->addRow();
    $row->addContent(getMaxUpload($guid, true));
    $row->addSubmit(__('Upload'));

$output .= $form->getOutput();

echo $output;
