<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
include($_SERVER['DOCUMENT_ROOT'] . '/config.php');

if (isActionAccessible($guid, $connection2, '/modules/Campaign/transitionsList.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs
        ->add(__('Transition'), 'transitions.php')
        ->add(__('Add Transition'));

    // $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/edit_wf_transition.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }


    $form = Form::create('WorkflowTransition', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/transitionEditProcess.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);


    $sqlq = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE  table_schema='".$databaseName."'";
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();

    $tables = array();
    $tables2 = array();
    $tables1 = array('' => 'Select Table');
    foreach ($rowdata as $dt) {
        $tables2[$dt['TABLE_NAME']] = $dt['TABLE_NAME'];
    }
    $tables = $tables1 + $tables2;


    $sql = 'SELECT id, form_id, name FROM campaign ';
    $result = $connection2->query($sql);
    $rowdatacamp = $result->fetchAll();

    $campaign = array();
    $campaign2 = array();
    $campaign1 = array('' => 'Select Campaign');
    foreach ($rowdatacamp as $dt) {
        $campaign2[$dt['id']] = $dt['name'];
    }
    $campaign = $campaign1 + $campaign2;

    echo '<div style="display: inline-flex;width: 100%;"><h2 style="width:64%;">';
    echo __('Transitions List');
    echo '</h2>';

    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=%2Fmodules%2FCampaign%2Ftransitions.php' class='btn btn-primary'>Add Transitions</a>";
    echo "&nbsp;&nbsp;<a href='index.php?q=/modules/Campaign/applicantDetails.php' class=' btn btn-primary' style='display:none;'>Import Student</a></div><div class='float-none'></div></div></div>";

    $sqlt = "SELECT a.*, b.form_id FROM campaign_transitions_form_map AS a LEFT JOIN campaign AS b ON a.campaign_id = b.id ";
    $resultt = $connection2->query($sqlt);
    $transitionlist = $resultt->fetchAll();

    $k = 1;
    foreach ($transitionlist as $trans) {
        $sqlcol = 'SHOW COLUMNS FROM ' . $trans['table_name'] . ' ';
        $resultcol = $connection2->query($sqlcol);
        $columndata = $resultcol->fetchAll();
        $columns = array();

        foreach ($columndata as $dt) {
            if ($dt['Null'] == 'NO') {
                $notnull = '*';
                $fieldname[] = $dt['Field'];
            } else {
                $notnull = '';
            }
            $columns[$dt['Field']] = $dt['Field'];
        }

        $fform = array();
        if (isset($trans['form_id'])) {
            $sqlf = 'Select form_fields FROM wp_fluentform_forms WHERE id = ' . $trans['form_id'] . '  ';
            $resultf = $connection2->query($sqlf);

            $rowdataf = $resultf->fetch();
            $field = json_decode($rowdataf['form_fields']);
            $fields = array();

            foreach ($field as $fe) {
                foreach ($fe as $f) {
                    if (!empty($f->attributes)) {
                        $fform[$f->attributes->name] = ucwords($f->attributes->name);
                    }
                }
            }
        }


        $row = $form->addRow()->setClass('requiredcss')->setID('requireddiv1');
        $row = $form->addRow()->setID('seatdiv' . $trans['id']);
        $col = $row->addColumn()->setClass('newdes');
        if ($k == 1) {
            $col->addLabel('table_name', __('Tables'));
        }
        $col->addSelect('table_name[' . $trans['id'] . ']')->addData('rid', '' . $trans['id'] . '')->addClass('tableName txtfield')->fromArray($tables)->required()->selected($trans['table_name']);

        $col = $row->addColumn()->setClass('newdes');
        if ($k == 1) {
            $col->addLabel('column', __('Column'));
        }
        $col->addSelect('column[' . $trans['id'] . ']')->setID('columnName' . $trans['id'] . '')->addClass('txtfield')->fromArray($columns)->required()->selected($trans['column_name']);

        $col = $row->addColumn()->setClass('newdes');
        if ($k == 1) {
            $col->addLabel('auto_gen_inv', __('Application'));
        }
        $col->addSelect('campaign[' . $trans['id'] . ']')->addData('rid', '' . $trans['id'] . '')->addClass('campaignName txtfield')->fromArray($campaign)->required()->selected($trans['campaign_id']);

        $col = $row->addColumn()->setClass('newdes');
        if ($k == 1) {
            $col->addLabel('tansition_action', __('Form'));
        }
        $col->addSelect('fluent_form[' . $trans['id'] . ']')->setID('fluentForm' . $trans['id'] . '')->addClass('txtfield')->fromArray($fform)->required()->selected($trans['fluent_form_column_name']);

        $col = $row->addColumn()->setClass('newdes delcolumn');
        if ($k == 1) {
            $col->addLabel('', __(''));
        }
        $col->addContent('<div class="dte mb-1"  style="font-size: 25px; padding:  6px 0 0px 4px"><i style="cursor:pointer" class="far fa-times-circle delTransition " data-id="' . $trans['id'] . '" data-sid="' . $trans['id'] . '"></i></div>');
        $k++;
    }

    if (!empty($transitionlist)) {
        $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit()->addClass('sumit_css submt');
    }


    echo $form->getOutput();
}