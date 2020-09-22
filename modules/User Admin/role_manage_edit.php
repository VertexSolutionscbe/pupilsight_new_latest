<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/role_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Roles'),'role_manage.php')
        ->add(__('Edit Role'));     

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightRoleID = $_GET['pupilsightRoleID'];
    if ($pupilsightRoleID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightRoleID' => $pupilsightRoleID);
            $sql = 'SELECT * FROM pupilsightRole WHERE pupilsightRoleID=:pupilsightRoleID';
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
            //Let's go!
            $role = $result->fetch();
            $isReadOnly = ($role['type'] == 'Core');

            $form = Form::create('addRole', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/role_manage_editProcess.php?pupilsightRoleID='.$pupilsightRoleID);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $categories = array(
                'Staff'   => __('Staff'),
                'Student' => __('Student'),
                'Parent'  => __('Parent'),
                'Other'   => __('Other'),
            );

            $restrictions = array(
                'None'       => __('None'),
                'Same Role'  => __('Users with the same role'),
                'Admin Only' => __('Administrators only'),
            );

            $row = $form->addRow();
                $row->addLabel('category', __('Category'));
            if ($isReadOnly) {
                $row->addTextField('category')->required()->readonly()->setValue($role['category']);
            } else {
                $row->addSelect('category')->fromArray($categories)->required()->placeholder()->selected($role['category']);
            }

            $row = $form->addRow();
                $row->addLabel('name', __('Name'));
                $row->addTextField('name')->required()->maxLength(20)->readonly($isReadOnly)->setValue($role['name']);

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'));
                $row->addTextField('nameShort')->required()->maxLength(4)->readonly($isReadOnly)->setValue($role['nameShort']);

            $row = $form->addRow();
                $row->addLabel('description', __('Description'));
                $row->addTextField('description')->required()->maxLength(60)->readonly($isReadOnly)->setValue($role['description']);

            $row = $form->addRow();
                $row->addLabel('type', __('Type'));
                $row->addTextField('type')->required()->readonly()->setValue($role['type']);

            $row = $form->addRow();
                $row->addLabel('canLoginRole', __('Can Login?'))->description(__('Are users with this primary role able to login?'));
                if ($role['name'] == 'Administrator') {
                    $row->addTextField('canLoginRole')->required()->readonly()->setValue(__('Yes'));
                } else {
                    $row->addYesNo('canLoginRole')->required()->selected($role['canLoginRole']);
                    $form->toggleVisibilityByClass('loginOptions')->onSelect('canLoginRole')->when('Y');
                }

            $row = $form->addRow()->addClass('loginOptions');
                $row->addLabel('pastYearsLogin', __('Login To Past Years'));
                $row->addYesNo('pastYearsLogin')->required()->selected($role['pastYearsLogin']);

            $row = $form->addRow()->addClass('loginOptions');
                $row->addLabel('futureYearsLogin', __('Login To Future Years'));
                $row->addYesNo('futureYearsLogin')->required()->selected($role['futureYearsLogin']);

            $row = $form->addRow();
                $row->addLabel('restriction', __('Restriction'))->description('Determines who can grant or remove this role in Manage Users.');
            if ($role['name'] == 'Administrator') {
                $row->addTextField('restriction')->required()->readonly()->setValue('Admin Only');
            } else {
                $row->addSelect('restriction')->fromArray($restrictions)->required()->selected($role['restriction']);
            }

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
