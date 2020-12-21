<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Curriculum\CurriculamGateway;

// use Pupilsight\Domain\Staff\StaffGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_home_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    
    if (isset($_GET['id'])) {
        $testid = $_GET['id'];
    }
  
    $page->breadcrumbs
        ->add(__(' Manage Test'), 'test_home.php')
        ->add(__('Edit Test'));

        if ($testid == '') {
            echo "<div class='error'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
               
  // `examinationTest` WHERE 1,,id,`pupilsightSchoolYearID`,`name`,`code`
                $data = array('id' => $testid);
                $sql = 'SELECT * FROM examinationTestMaster WHERE id=:id';
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
              
                $sqlq = 'SELECT * FROM pupilsightSchoolYear ORDER BY sequenceNumber';
                $resultval = $connection2->query($sqlq);
                $rowdata = $resultval->fetchAll();
                $academic = array();
                $ayear = '';
                if (!empty($rowdata)) {
                    $ayear = $rowdata[0]['name'];
                    foreach ($rowdata as $dt) {
                        $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
                    }
                }
                
                echo ' <a href="'.$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/test_home_general_add.php" class=" btn btn-primary active">General</a>
                <a href="'.$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/test_create.php&tid='.$testid.'" class=" btn btn-primary">Create Tests</a>';
                
           
                $form = Form::create('testhomeManage', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/test_home_editProcess.php')->addClass('newform');
                $form->setFactory(DatabaseFormFactory::create($pdo));
            
                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('id', $testid);
                
                echo '<h2>';
                echo __('TEST ');
                echo '</h2>';
 
                $row = $form->addRow();
                $row->addLabel('testname', __('Name'));
                $row->addTextField('testname')->maxLength(40)->required()->setValue($values['name']);
                $row = $form->addRow();
                $row->addLabel('testcode', __('Test Code'));
                $row->addTextField('testcode')->maxLength(30)->required()->setValue($values['code']);
        
                $row = $form->addRow();
                $row->addLabel('Academic Year', __('Academic Year'));
                $row->addSelect('pupilsightSchoolYearID')->setId('academic_year')->fromArray($academic)->required()->selected($values['pupilsightSchoolYearID'])->placeholder();
        
                    $row = $form->addRow()->setID('');
                    $row->addFooter();
                    $row->addSubmit();
            
                echo $form->getOutput();

            }
        }

   
  
}
