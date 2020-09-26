<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\FormFactory;

//Pupilsight system-wide include
include '../../pupilsight.php';

//Module includes
include '../../modules/Library/moduleFunctions.php';

//Setup variables
$pupilsightLibraryTypeID = isset($_POST['pupilsightLibraryTypeID'])? $_POST['pupilsightLibraryTypeID'] : '';
$pupilsightLibraryItemID = isset($_POST['pupilsightLibraryItemID'])? $_POST['pupilsightLibraryItemID'] : '';

if (isActionAccessible($guid, $connection2, '/modules/Library/library_manage_catalog_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    $data = array('pupilsightLibraryTypeID' => $pupilsightLibraryTypeID);
    $sql = "SELECT * FROM pupilsightLibraryType
            WHERE pupilsightLibraryTypeID=:pupilsightLibraryTypeID AND active='Y' ORDER BY name";
    $result = $pdo->executeQuery($data, $sql);

    $factory = FormFactory::create();
    $table = $factory->createTable('detailsTable')->setClass('fullWidth');

    if ($result->rowCount() != 1) {
        $table->addRow()->addAlert(__('The specified record cannot be found.'), 'error');
    } else {
        $values = $result->fetch();
        $fieldsValues = array();

        // Load any data for an existing library item
        if (!empty($pupilsightLibraryItemID)) {
            $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID);
            $sql = "SELECT fields FROM pupilsightLibraryItem WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID";
            $result = $pdo->executeQuery($data, $sql);
            $fieldsValues = ($result->rowCount() == 1)? unserialize($result->fetchColumn(0)) : array();
        }

        // Transform the library field types to CustomField compatable types
        $fields = array_map(function($item){
            switch($item['type']) {
                case 'Text':        $item['type'] = 'varchar'; break;
                case 'Textarea':    $item['type'] = 'text'; break;
                default:            $item['type'] = strtolower($item['type']); break;
            }
            return $item;
        }, unserialize($values['fields']));

        foreach ($fields as $field) {
            $fieldName = 'field'.preg_replace('/ |\(|\)/', '', $field['name']);
            $fieldValue = isset($fieldsValues[$field['name']])? $fieldsValues[$field['name']] : '';

            $row = $table->addRow()->addClass('col sm-1');
                $row->addLabel($fieldName, __($field['name']))->description(__($field['description']))->addClass('');
                $row->addCustomField($fieldName, $field)->setValue($fieldValue)->addClass('mb-1');
        }

        // Add Google Books data grabber
        if ($values['name'] == 'Print Publication') {
            echo '<script type="text/javascript">';
                echo 'document.onkeypress = stopRKey;';
                echo '$(".gbooks").loadGoogleBookData({
                    "notFound": "'.__('The specified record cannot be found.').'",
                    "dataRequired": "'.__('Please enter an ISBN13 or ISBN10 value before trying to get data from Google Books.').'",
                });';
            echo '</script>';
            echo '<div style="text-align: right">';
            echo '<a class="gbooks" onclick="return false" href="#">'.__('Get Book Data From Google').'</a>';
            echo '</div>';
        }
    }

    echo $table->getOutput();
    echo '<script type="text/javascript">'.$table->getValidationOutput().'</script>';
}
