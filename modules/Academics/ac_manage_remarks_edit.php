<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Curriculum\CurriculamGateway;

// use Pupilsight\Domain\Staff\StaffGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_remarks_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    
    if (isset($_GET['id'])) {
        $remarkid = $_GET['id'];
    }
  
    $page->breadcrumbs
        ->add(__(' Manage Remarks'), 'ac_manage_remarks.php')
        ->add(__('Edit Remarks'));

        if ($remarkid == '') {
            echo "<div class='error'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
               
 //SELECT * FROM `acRemarks` WHERE ,`id`,`remarkcode`,`description`,`pupilsightDepartmentID`,`skill` 
                $data = array('id' => $remarkid);
                $sql = 'SELECT * FROM acRemarks WHERE id=:id';
           


                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='error'>";
                echo __('The specified record does not exist.');
                echo '</div>';
            } else {
                //Let's go!
                $values = $result->fetch();
                $pupilsightSchoolYearID = '';
                if (isset($_GET['pupilsightSchoolYearID'])) {
                    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
                }

                $form = Form::create('remarks', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/ac_remarks_editProcess.php')->addClass('newform');
                $form->setFactory(DatabaseFormFactory::create($pdo));
            
                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('id', $remarkid);
               
            
                $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
                $resultval = $connection2->query($sqlq);
                     $rowdata = $resultval->fetchAll();
                     $academic=array();
                     $ayear = '';
                    if(!empty($rowdata)){
                        $ayear = $rowdata[0]['name'];
                        foreach ($rowdata as $dt) {
                            $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
                        }
                    }


                  
                $form->addHiddenValue('ayear', $ayear);    
                    
        //select subjects from department
        $sqld = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment ';
        $resultd = $connection2->query($sqld);
        $rowdatadept = $resultd->fetchAll();


        $subjects=array();  
        $subject2=array();  
        // $subject1=array(''=>'Select Subjects');
        foreach ($rowdatadept as $dt) {
            $subject2[$dt['pupilsightDepartmentID']] = $dt['name'];
        }
        $subjects=  $subject2;  



        // `ac_manage_skill` WHERE 1, `ID`,`name`,`scode`,`description`,

        $sqlsk = 'SELECT ID, name FROM ac_manage_skill ';
        $resultsk = $connection2->query($sqlsk);
        $skilldata = $resultsk->fetchAll();


        $skills=array();  
        $skills2=array();  

        foreach ($skilldata as $sk) {
            $skills2[$sk['ID']] = $sk['name'];
        }
        $skills=  $skills2;  
                    
                    
                echo '<h2>';
                echo __('Remarks ');
                echo '</h2>';
               
                
                $row = $form->addRow();
                $row->addLabel('rcode', __('Remark Code'));
                $row->addTextField('rcode')->maxLength(30)->required()->setValue($values['remarkcode']);
            $row = $form->addRow();
                $row->addLabel('description', __('Description'));
                $row->addTextArea('description')->setRows(4)->required()->setValue($values['description']);
        
        
                $row = $form->addRow()->setClass('');
                $row->addLabel('pupilsightDepartmentID', 'Subjects');
                $row->addSelect('pupilsightDepartmentID')->fromArray($subjects)->selected($values['pupilsightDepartmentID'])->placeholder();   
        
          $row = $form->addRow()->setClass('');
                $row->addLabel('skill', 'Skills');
                $row->addSelect('skill')->fromArray($skills)->placeholder()->selected($values['skill']); 
               
                    $row = $form->addRow()->setID('');
                    $row->addFooter();
                    $row->addSubmit();
            
                echo $form->getOutput();

            }
        }

   
  
}
