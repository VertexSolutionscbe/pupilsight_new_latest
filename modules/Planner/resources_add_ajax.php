<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

//Setup variables
$output = '';
$id = $_GET['id'];
$action = null;
if (isset($_GET['action'])) {
    $action = $_GET['action'];
}
$category = null;
if (isset($_GET['category'])) {
    $category = $_GET['category'];
}
$purpose = null;
if (isset($_GET['purpose'])) {
    $purpose = $_GET['purpose'];
}
$tag = null;
if (isset($_GET['tag'.$id])) {
    $tag = $_GET['tag'.$id];
}
$pupilsightYearGroupID = null;
if (isset($_GET['pupilsightYearGroupID'])) {
    $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
}
$allowUpload = $_GET['allowUpload'];
$alpha = null;
if (isset($_GET['alpha'])) {
    $alpha = $_GET['alpha'];
}

if (isActionAccessible($guid, $connection2, '/modules/Planner/resources_manage_add.php') == false) {
    //Acess denied
    $output .= "<div class='alert alert-danger'>";
    $output .= __('Your request failed because you do not have access to this action.');
    $output .= '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, '/modules/Planner/resources_manage.php', $connection2);
    if ($highestAction == false) {
        $output .= "<div class='alert alert-danger'>";
        $output .= __('The highest grouped action cannot be determined.');
        $output .= '</div>';
    } else {
        $output .= "<script type='text/javascript'>";
        $output .= '$(document).ready(function() {';

        $output .= "$('.checkall').click(function () {";
        $output .= "$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);";
        $output .= "});";
        $output .= 'var options={';
        $output .= 'success: function(response) {';
        $output .= "tinymce.execCommand(\"mceFocus\",false,\"$id\"); tinyMCE.execCommand(\"mceInsertContent\", 0, response); formReset(); \$(\".".$id.'resourceAddSlider").slideUp();';
        $output .= '}, ';
        $output .= "url: '".$_SESSION[$guid]['absoluteURL']."/modules/Planner/resources_add_ajaxProcess.php',";
        $output .= "type: 'POST'";
        $output .= '};';

        $output .= "$('#".$id."ajaxForm').submit(function() {";
        $output .= '$(this).ajaxSubmit(options);';
        $output .= '$(".'.$id."resourceAddSlider\").html(\"<div class='resourceAddSlider'><img style='margin: 10px 0 5px 0' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/loading.gif' alt='".__('Uploading')."' onclick='return false;' /><br/>".__('Loading').'</div>");';
        $output .= 'return false;';
        $output .= '});';
        $output .= '});';

        $output .= 'var formReset=function() {';
        $output .= "$('#".$id."resourceAdd').css('display','none');";
        $output .= '};';
        $output .= '</script>';

        $form = Form::create($id.'ajaxForm', '')->addClass('resourceQuick');
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->addHiddenValue('id', $id);
        $form->addHiddenValue($id.'address', $_SESSION[$guid]['address']);

        $col = $form->addRow()->addColumn();
            $col->addWebLink("<img title='".__('Close')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/>")
                ->onClick("formReset(); \$(\".".$id."resourceAddSlider\").slideUp();")->addClass('right');
            $col->addContent(__('Add & Insert A New Resource'))->wrap('<h3 style="margin-top: 0;">', '</h3>');
            $col->addContent(__('Use the form below to add a new resource to Pupilsight. If the addition is successful, then it will be automatically inserted into your work above. Note that you  cannot create HTML resources here (you have to go to the Planner module for that).'))->wrap('<p>', '</p>');
        
        $form->addRow()->addSubheading(__('Resource Contents'));

        $types = array('File' => __('File'), 'Link' => __('Link'));
        $row = $form->addRow();
            $row->addLabel($id.'type', __('Type'));
            $row->addSelect($id.'type')->fromArray($types)->required()->placeholder();

        // File
        $form->toggleVisibilityByClass('resourceFile')->onSelect($id.'type')->when('File');
        $row = $form->addRow()->addClass('resourceFile');
            $row->addLabel($id.'file', __('File'));
            $row->addFileUpload($id.'file')->required();

        // Link
        $form->toggleVisibilityByClass('resourceLink')->onSelect($id.'type')->when('Link');
        $row = $form->addRow()->addClass('resourceLink');
            $row->addLabel($id.'link', __('Link'));
            $row->addURL($id.'link')->maxLength(255)->required();

        $form->addRow()->addSubheading(__('Resource Details'));

        $row = $form->addRow();
            $row->addLabel($id.'name', __('Name'));
            $row->addTextField($id.'name')->required()->maxLength(60);

        $categories = getSettingByScope($connection2, 'Resources', 'categories');
        $row = $form->addRow();
            $row->addLabel($id.'category', __('Category'));
            $row->addSelect($id.'category')->fromString($categories)->required()->placeholder();

        $purposesGeneral = getSettingByScope($connection2, 'Resources', 'purposesGeneral');
        $purposesRestricted = ($highestAction == 'Manage Resources_all')? getSettingByScope($connection2, 'Resources', 'purposesRestricted') : '';
        $row = $form->addRow();
            $row->addLabel($id.'purpose', __('Purpose'));
            $row->addSelect($id.'purpose')->fromString($purposesGeneral)->fromString($purposesRestricted)->placeholder();

        $sql = "SELECT tag as value, CONCAT(tag, ' <i>(', count, ')</i>') as name FROM pupilsightResourceTag WHERE count>0 ORDER BY tag";
        $row = $form->addRow()->addClass('tags');
            $row->addLabel($id.'tags', __('Tags'))->description(__('Use lots of tags!'));
            $row->addFinder($id.'tags')
                ->fromQuery($pdo, $sql)
                ->setParameter('hintText', __('Type a tag...'))
                ->setParameter('allowFreeTagging', true);

        $row = $form->addRow();
            $row->addLabel($id.'pupilsightYearGroupID', __('Year Groups'))->description(__('Students year groups which may participate'));
            $row->addCheckboxYearGroup($id.'pupilsightYearGroupID')->checkAll()->addCheckAllNone();

        $row = $form->addRow();
            $row->addLabel($id.'description', __('Description'));
            $row->addTextArea($id.'description')->setRows(8);

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();
        
        $output .= $form->getOutput();
    }
}

echo $output;
