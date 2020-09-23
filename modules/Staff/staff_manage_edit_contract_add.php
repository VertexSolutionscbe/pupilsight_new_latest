<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_edit_contract_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightStaffID = $_GET['pupilsightStaffID'] ?? '';
    $search = $_GET['search'] ?? '';
    $editID = $_GET['editID'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Staff'), 'staff_manage.php')
        ->add(__('Edit Staff'), 'staff_manage_edit.php', ['pupilsightStaffID' => $pupilsightStaffID])
        ->add(__('Add Contract'));

    $editLink = '';
    if (!empty($editID)) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/staff_manage_edit_contract_edit.php&pupilsightStaffContractID='.$editID.'&search='.$search.'&pupilsightStaffID='.$pupilsightStaffID;
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    //Check if school year specified
    if ($pupilsightStaffID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightStaffID' => $pupilsightStaffID);
            $sql = 'SELECT * FROM pupilsightStaff JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStaffID=:pupilsightStaffID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            $values = $result->fetch();

            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Staff/staff_manage_edit.php&pupilsightStaffID=$pupilsightStaffID&search=$search'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/staff_manage_edit_contract_addProcess.php?pupilsightStaffID=$pupilsightStaffID&search=$search");
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('person', __('Person'));
                $row->addTextField('person')->setValue(Format::name('', $values['preferredName'], $values['surname'], 'Student'))->readonly()->required();

            $row = $form->addRow();
                $row->addLabel('title', __('Title'))->description(__('A name to identify this contract.'));
                $row->addTextField('title')->maxlength(100)->required();

            $row = $form->addRow();
                $row->addLabel('status', __('Status'));
                $row->addSelect('status')->fromArray(array('Pending' => __('Pending'), 'Active' => __('Active'), 'Expired' => __('Expired')))->required()->placeholder();

            $row = $form->addRow();
                $row->addLabel('dateStart', __('Start Date'));
                $row->addDate('dateStart')->required();

            $row = $form->addRow();
                $row->addLabel('dateEnd', __('End Date'));
                $row->addDate('dateEnd');

            $scalePositions = getSettingByScope($connection2, 'Staff', 'salaryScalePositions');
            $scalePositions = ($scalePositions != '' ? explode(',', $scalePositions) : '');
            $row = $form->addRow();
                $row->addLabel('salaryScale', __('Salary Scale'));
                $row->addSelect('salaryScale')->fromArray($scalePositions)->placeholder();

            $periods = array(
                "Week" => __('Week'),
                "Month" => __('Month'),
                "Year" => __('Year'),
                "Contract" => __('Contract')
            );
            $row = $form->addRow();
                $row->addLabel('salaryAmount', __('Salary'));
                    $col = $row->addColumn('salaryAmount')->addClass('right inline');
                    $col->addCurrency('salaryAmount')->setClass('shortWidth');
                    $col->addSelect('salaryPeriod')->fromArray($periods)->setClass('shortWidth')->placeholder();

            $responsibilityPosts = getSettingByScope($connection2, 'Staff', 'responsibilityPosts');
            $responsibilityPosts = ($responsibilityPosts != '' ? explode(',', $responsibilityPosts) : '');
            if (is_array($responsibilityPosts)) {
                $row = $form->addRow();
                    $row->addLabel('responsibility', __('Responsibility Level'));
                    $row->addSelect('responsibility')->fromArray($responsibilityPosts)->placeholder();
            }

            $row = $form->addRow();
                $row->addLabel('responsibilityAmount', __('Responsibility'));
                    $col = $row->addColumn('responsibilityAmount')->addClass('right inline');
                    $col->addCurrency('responsibilityAmount')->setClass('shortWidth');
                    $col->addSelect('responsibilityPeriod')->fromArray($periods)->setClass('shortWidth')->placeholder();

            $row = $form->addRow();
                $row->addLabel('housingAmount', __('Housing'));
                    $col = $row->addColumn('housingAmount')->addClass('right inline');
                    $col->addCurrency('housingAmount')->setClass('shortWidth');
                    $col->addSelect('housingPeriod')->fromArray($periods)->setClass('shortWidth')->placeholder();

            $row = $form->addRow();
                $row->addLabel('travelAmount', __('Travel'));
                    $col = $row->addColumn('travelAmount')->addClass('right inline');
                    $col->addCurrency('travelAmount')->setClass('shortWidth');
                    $col->addSelect('travelPeriod')->fromArray($periods)->setClass('shortWidth')->placeholder();

            $row = $form->addRow();
                $row->addLabel('retirementAmount', __('Retirement'));
                    $col = $row->addColumn('retirementAmount')->addClass('right inline');
                    $col->addCurrency('retirementAmount')->setClass('shortWidth');
                    $col->addSelect('retirementPeriod')->fromArray($periods)->setClass('shortWidth')->placeholder();

            $row = $form->addRow();
                $row->addLabel('bonusAmount', __('Bonus/Gratuity'));
                    $col = $row->addColumn('bonusAmount')->addClass('right inline');
                    $col->addCurrency('bonusAmount')->setClass('shortWidth');
                    $col->addSelect('bonusPeriod')->fromArray($periods)->setClass('shortWidth')->placeholder();

            $row = $form->addRow();
                $column = $row->addColumn();
                $column->addLabel('education', __('Education Benefits'));
                $column->addTextArea('education')->setRows(5)->setClass('fullWidth');

            $row = $form->addRow();
                $column = $row->addColumn();
                $column->addLabel('notes', __('Notes'));
                $column->addTextArea('notes')->setRows(5)->setClass('fullWidth');

            $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
            $row = $form->addRow();
                $row->addLabel('file1', __('Contract File'));
                $row->addFileUpload('file1')->accepts($fileUploader->getFileExtensions('Document'));

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
