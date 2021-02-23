<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$pupilsightLibraryItemID = trim($_GET['pupilsightLibraryItemID']) ?? '';

$page->breadcrumbs
    ->add(__('Lending & Activity Log'), 'library_lending.php')
    ->add(__('View Item'), 'library_lending_item.php', ['pupilsightLibraryItemID' => $pupilsightLibraryItemID])
    ->add(__('Sign Out'));

if (isActionAccessible($guid, $connection2, '/modules/Library/library_lending_item_signOut.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (empty($pupilsightLibraryItemID)) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {

        $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        $program = array();
        $program2 = array();
        $program1 = array('' => 'Select Program');
        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program = $program1 + $program2;

        try {
            $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID);
            $sql = 'SELECT * FROM pupilsightLibraryItem WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID';
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
            $values = $result->fetch();

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            if ($values['returnAction'] != '') {
                if ($values['pupilsightPersonIDReturnAction'] != '') {
                    try {
                        $dataPerson = array('pupilsightPersonID' => $values['pupilsightPersonIDReturnAction']);
                        $sqlPerson = 'SELECT surname, preferredName FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
                        $resultPerson = $connection2->prepare($sqlPerson);
                        $resultPerson->execute($dataPerson);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($resultPerson->rowCount() == 1) {
                        $rowPerson = $resultPerson->fetch();
                        $person = formatName('', htmlPrep($rowPerson['preferredName']), htmlPrep($rowPerson['surname']), 'Student');
                    }
                }

                echo "<div class='alert alert-warning'>";
                if ($values['returnAction'] == 'Make Available') {
                    echo __('This item has been marked to be <u>made available</u> for loan on return.');
                }
                if ($values['returnAction'] == 'Reserve' and $values['pupilsightPersonIDReturnAction'] != '') {
                    echo __("This item has been marked to be <u>reserved</u> for <u>$person</u> on return.");
                }
                if ($values['returnAction'] == 'Decommission' and $values['pupilsightPersonIDReturnAction'] != '') {
                    echo __("This item has been marked to be <u>decommissioned</u> by <u>$person</u> on return.");
                }
                if ($values['returnAction'] == 'Repair' and $values['pupilsightPersonIDReturnAction'] != '') {
                    echo __("This item has been marked to be <u>repaired</u> by <u>$person</u> on return.");
                }
                echo ' '.__('You can change this below if you wish.');
                echo '</div>';
            }

            if ($_GET['name'] != '' or $_GET['pupilsightLibraryTypeID'] != '' or $_GET['pupilsightSpaceID'] != '' or $_GET['status'] != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Library/library_lending_item.php&name='.$_GET['name']."&pupilsightLibraryItemID=$pupilsightLibraryItemID&pupilsightLibraryTypeID=".$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status']."'>".__('Back').'</a>';
                echo '</div>';
            }

            $form = Form::create('libraryLendingSignout', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/library_lending_item_signoutProcess.php?name=".$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status']);
            $form->setFactory(DatabaseFormFactory::create($pdo));
            echo '<input type="hidden" id="pupilsightSchoolYearID" value="'.$_SESSION[$guid]['pupilsightSchoolYearID'].'">';

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightLibraryItemID', $pupilsightLibraryItemID);

            $form->addRow()->addHeading(__('Item Details'));

            $row = $form->addRow();
                $row->addLabel('idLabel', __('ID'));
                $row->addTextField('idLabel')->setValue($values['id'])->readonly()->required();

            $row = $form->addRow();
                $row->addLabel('name', __('Name'));
                $row->addTextField('name')->setValue($values['name'])->readonly()->required();

            $row = $form->addRow();
                $row->addLabel('statusCurrent', __('Current Status'));
                $row->addTextField('statusCurrent')->setValue($values['status'])->readonly()->required();

            //$form->addRow()->addHeading(__('This Event'));
            $row = $form->addRow();
                 $row->addLabel('', __('<h3>This Event</h3>'));
                // $row->addTextField('statusCurrent')->setValue($values['status'])->readonly()->required();
            
            $statuses = array(
                'On Loan' => __('On Loan'),
                'Reserved' => __('Reserved'),
                'Decommissioned' => __('Decommissioned'),
                'Lost' => __('Lost'),
                'Repair' => __('Repair')
            );
            $row = $form->addRow();
                $row->addLabel('status', __('New Status'));
                $row->addSelect('status')->fromArray($statuses)->required()->selected('On Loan')->placeholder();

            $usertype = array('' => 'Select User Type', '002' => 'Staff', '004' => 'Student');
            $row = $form->addRow();
                $row->addLabel('pupilsightRoleIDPrimary', __('User Type'));
                $row->addSelect('pupilsightRoleIDPrimary')->setId('userType')->fromArray($usertype)->required()->selected('On Loan')->required();    

            $row = $form->addRow()->addClass('studentType hiddencol');
                $row->addLabel('pupilsightProgramID', __('Program'));
                $row->addSelect('pupilsightProgramID')->setId('pupilsightProgram')->fromArray($program)->placeholder('Select Program');
        
        
            // $row = $form->addRow()->addClass('studentType hiddencol');
            //     $row->addLabel('pupilsightYearGroupID', __('Class'));
            //     $row->addSelect('pupilsightYearGroupID')->placeholder('Select Class');
        
                
            // $row = $form->addRow()->addClass('studentType hiddencol');
            //     $row->addLabel('pupilsightRollGroupID', __('Section'));
            //     $row->addSelect('pupilsightRollGroupID')->placeholder('Select Section'); 
            
            $row = $form->addRow();
                $row->addLabel('pupilsightPersonIDStatusResponsible', __('Responsible User'))->description(__('Who is responsible for this new status?'));
                $row->addSelect('pupilsightPersonIDStatusResponsible')->setId('pupilsightPersonID')->placeholder('Please Select')->required(); 

            // $people = array();

            // $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => date('Y-m-d'));
            // $sql = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname, username, pupilsightRollGroup.name AS rollGroupName
            //     FROM pupilsightPerson
            //         JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            //         JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
            //     WHERE status='Full'
            //         AND (dateStart IS NULL OR dateStart<=:date)
            //         AND (dateEnd IS NULL  OR dateEnd>=:date)
            //         AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID
            //     ORDER BY name, surname, preferredName";
            // $result = $pdo->executeQuery($data, $sql);

            // if ($result->rowCount() > 0) {
            //     $people['--'.__('Students By Roll Group').'--'] = array_reduce($result->fetchAll(), function ($group, $item) {
            //         $group[$item['pupilsightPersonID']] = $item['rollGroupName'].' - '.formatName('', htmlPrep($item['preferredName']), htmlPrep($item['surname']), 'Student', true).' ('.$item['username'].')';
            //         return $group;
            //     }, array());
            // }

            // $sql = "SELECT pupilsightPersonID, surname, preferredName, status, username FROM pupilsightPerson WHERE status='Full' OR status='Expected' ORDER BY surname, preferredName";
            // $result = $pdo->executeQuery(array(), $sql);

            // if ($result->rowCount() > 0) {
            //     $people['--'.__('All Users').'--'] = array_reduce($result->fetchAll(), function($group, $item) {
            //         $expected = ($item['status'] == 'Expected')? '('.__('Expected').')' : '';
            //         $group[$item['pupilsightPersonID']] = formatName('', htmlPrep($item['preferredName']), htmlPrep($item['surname']), 'Student', true).' ('.$item['username'].')'.$expected;
            //         return $group;
            //     }, array());
            // }

            // $row = $form->addRow();
            //     $row->addLabel('pupilsightPersonIDStatusResponsible', __('Responsible User'))->description(__('Who is responsible for this new status?'));
            //     $row->addSelect('pupilsightPersonIDStatusResponsible')->fromArray($people)->placeholder()->required();

            $loanLength = getSettingByScope($connection2, 'Library', 'defaultLoanLength');
            $loanLength = (is_numeric($loanLength) == false or $loanLength < 0) ? 7 : $loanLength ;
            $row = $form->addRow();
                $row->addLabel('returnExpected', __('Expected Return Date'))->description(sprintf(__('Default renew length is today plus %1$s day(s)'), $loanLength));
                $row->addDate('returnExpected')->setValue(date($_SESSION[$guid]['i18n']['dateFormatPHP'], time() + ($loanLength * 60 * 60 * 24)))->required();

            //$row = $form->addRow()->addHeading(__('On Return'));
            $row = $form->addRow();
                 $row->addLabel('', __('<h3>On Return</h3>'));

            $actions = array(
                'Reserve' => __('Reserve'),
                'Decommission' => __('Decommission'),
                'Repair' => __('Repair')
            );
            $row = $form->addRow();
                $row->addLabel('returnAction', __('Action'))->description(__('What to do when item is next returned.'));
                $row->addSelect('returnAction')->fromArray($actions)->selected($values['returnAction'])->placeholder();
            

                    
            $sql = 'SELECT a.type, b.pupilsightPersonID, b.officialName FROM pupilsightStaff AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE b.officialName != "" ';
            $result = $connection2->query($sql);
            $staffs = $result->fetchAll();
            $owner1 = array('' => 'Please Select ');
        
            foreach ($staffs as $dt) {
                $owner2[$dt['pupilsightPersonID']] = $dt['officialName'];
            }
            $owner = $owner1 + $owner2;
            $row = $form->addRow();
                $row->addLabel('pupilsightPersonIDReturnAction', __('Responsible User'))->description(__('Who will be responsible for the future status?'));
                //$row->addSelect('pupilsightPersonIDReturnAction')->fromArray($people)->placeholder();
                $row->addSelect('pupilsightPersonIDReturnAction')->fromArray($owner)->placeholder();



            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}

?>

<script>

    $(document).ready(function(){
        $("#pupilsightPersonID").select2();
    });
    
    $(document).on('change','#userType', function(){
        var val = $(this).val();
        $("#pupilsightPersonID").html('<option value="">Please Select</option>');
        if(val != ''){
            if(val == '004'){
                $(".studentType").removeClass('hiddencol');
            } else {
                $(".studentType").addClass('hiddencol');
                var type = 'getAllSchoolStaff';
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function(response) {
                        $("#pupilsightPersonID").html();
                        $("#pupilsightPersonID").html(response);
                        $("#pupilsightProgram").val('');
                    }
                });
            }
        }
    });


    $(document).on('change','#pupilsightProgram',function(){

        var pupilsightProgramID=$('#pupilsightProgram').val();
        //alert(pupilsightProgramID);

        var type = 'getAllStudentsByProgram';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { type: type, pupilsightProgramID: pupilsightProgramID  },
            async: true,
            success: function(response) {

                
                $("#pupilsightPersonID").html();
                $("#pupilsightPersonID").html(response);
                
            },
            error: function(response) {
                console.log(response);
            }
        });

});
</script>    