<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs
    ->add(__('Manage Catalog'), 'library_manage_catalog.php')
    ->add(__('Duplicate Item'));

if (isActionAccessible($guid, $connection2, '/modules/Library/library_manage_catalog_duplicate.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightLibraryItemID = $_GET['pupilsightLibraryItemID'];
    if ($pupilsightLibraryItemID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID);
            $sql = 'SELECT pupilsightLibraryItem.*, pupilsightLibraryType.name AS type FROM pupilsightLibraryItem JOIN pupilsightLibraryType ON (pupilsightLibraryItem.pupilsightLibraryTypeID=pupilsightLibraryType.pupilsightLibraryTypeID) WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            $step = null;
            if (isset($_GET['step'])) {
                $step = $_GET['step'];
            }
            if ($step != 1 and $step != 2) {
                $step = 1;
            }

            //Step 1
            if ($step == 1) {
                if ($_GET['name'] != '' or $_GET['pupilsightLibraryTypeID'] != '' or $_GET['pupilsightSpaceID'] != '' or $_GET['status'] != '' or $_GET['pupilsightPersonIDOwnership'] != '' or $_GET['typeSpecificFields'] != '') {
                    echo "<div class='linkTop'>";
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Library/library_manage_catalog.php&name='.$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status'].'&pupilsightPersonIDOwnership='.$_GET['pupilsightPersonIDOwnership'].'&typeSpecificFields='.$_GET['typeSpecificFields']."'>".__('Back to Search Results').'</a>';
                    echo '</div>';
                }

                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/library_manage_catalog_duplicate.php&step=2&pupilsightLibraryItemID='.$values['pupilsightLibraryItemID'].'&name='.$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status'].'&pupilsightPersonIDOwnership='.$_GET['pupilsightPersonIDOwnership'].'&typeSpecificFields='.$_GET['typeSpecificFields']);

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                $form->addRow()->addHeading(__('Step 1 - Quantity'));

                $form->addHiddenValue('pupilsightLibraryTypeID', $values['pupilsightLibraryTypeID']);
                $row = $form->addRow();
                    $row->addLabel('type', __('Type'));
                    $row->addTextField('type')->setValue($values['type'])->readonly()->required();

                $row = $form->addRow();
                    $row->addLabel('name', __('Name'));
                    $row->addTextField('name')->setValue($values['name'])->readonly()->required();

                $row = $form->addRow();
                    $row->addLabel('id', __('ID'));
                    $row->addTextField('id')->setValue($values['id'])->readonly()->required();

                $row = $form->addRow();
                    $row->addLabel('producer', __('Author/Brand'));
                    $row->addTextField('producer')->setValue($values['producer'])->readonly()->required();

                $options = array();
                for ($i = 1; $i < 21; ++$i) {
                    $options[$i] = $i;
                }
                $row = $form->addRow();
                    $row->addLabel('number', __('Number of Copies'))->description('How many copies do you want to make of this item?');
                    $row->addSelect('number')->fromArray($options)->required();

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

                echo $form->getOutput();
            }
            //Step 1
            elseif ($step == 2) {
                if ($_GET['name'] != '' or $_GET['pupilsightLibraryTypeID'] != '' or $_GET['pupilsightSpaceID'] != '' or $_GET['status'] != '' or $_GET['pupilsightPersonIDOwnership'] != '' or $_GET['typeSpecificFields'] != '') {
                    echo "<div class='linkTop'>";
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Library/library_manage_catalog.php&name='.$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status'].'&pupilsightPersonIDOwnership='.$_GET['pupilsightPersonIDOwnership'].'&typeSpecificFields='.$_GET['typeSpecificFields']."'>".__('Back to Search Results').'</a>';
                    echo '</div>';
                }

                $number = $_POST['number'];

                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/library_manage_catalog_duplicateProcess.php?pupilsightLibraryItemID='.$values['pupilsightLibraryItemID'].'&name='.$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status'].'&pupilsightPersonIDOwnership='.$_GET['pupilsightPersonIDOwnership'].'&typeSpecificFields='.$_GET['typeSpecificFields']);

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('count', $number);
                $form->addHiddenValue('pupilsightLibraryTypeID', $_POST['pupilsightLibraryTypeID']);
                $form->addHiddenValue('pupilsightLibraryItemID', $values['pupilsightLibraryItemID']);

                $form->addRow()->addHeading(__('Step 2 - Details'));

                for ($i = 1; $i <= $number; ++$i) {
                    $row = $form->addRow();
                        $row->addLabel('id'.$i, sprintf(__('Copy %1$s ID'), $i));
                        $row->addTextField('id'.$i)
                            ->uniqueField('./modules/Library/library_manage_catalog_idCheckAjax.php', array('fieldName' => 'id'))
                            ->required()
                            ->maxLength(255);
                }

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

                echo $form->getOutput();
            }
        }
    }
}
?>
