<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/tc_template_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    //$id = $_GET['id'];
    $page->breadcrumbs
        ->add(__('Manage Template'), 'tc_template_manage.php')
        ->add(__('Template Fields'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    
}
?>

<table class="table">
    <thead>
        <tr>
            <th>Sl No</th>
            <th>Field Name</th>
            <th>Template Field type</th>
        </tr>
    </thead>
    <tbody>
        <?php /* if(!empty($arrHeader)){ 
                $i = 1;
                foreach($arrHeader as $ah){   
                    $headcol = ucwords(str_replace("_", " ", $ah)); 
       */ ?>
        <tr>
            <td>1</td>
            <td>Student Name</td>
            <td>${student_name}</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Academic Year</td>
            <td>${academic}</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Program</td>
            <td>${program}</td>
        </tr>
        <tr>
            <td>4</td>
            <td>Class</td>
            <td>${class}</td>
        </tr>
        <tr>
            <td>5</td>
            <td>Section</td>
            <td>${section}</td>
        </tr>
        <tr>
            <td>6</td>
            <td>Date</td>
            <td>${date}</td>
        </tr>
        <tr>
            <td>7</td>
            <td>TC No</td>
            <td>${tc_no}</td>
        </tr>
        <tr>
            <td>8</td>
            <td>Date of Birth</td>
            <td>${dob}</td>
        </tr>
        <tr>
            <td>9</td>
            <td>Father Name</td>
            <td>${father_name}</td>
        </tr>
        <tr>
            <td>10</td>
            <td>Father Name</td>
            <td>${father_email}</td>
        </tr>
        <tr>
            <td>11</td>
            <td>Father Name</td>
            <td>${father_phone}</td>
        </tr>
        <tr>
            <td>12</td>
            <td>Mother Name</td>
            <td>${mother_name}</td>
        </tr>
        <tr>
            <td>13</td>
            <td>Mother Name</td>
            <td>${mother_email}</td>
        </tr>
        <tr>
            <td>14</td>
            <td>Mother Name</td>
            <td>${mother_phone}</td>
        </tr>
       

        <?php /* $i++; } } else { */ ?>
            <!-- <tr><td colspan="3">No Data</td></tr> -->
        <?php /* } */ ?>
    </tbody>

</table>