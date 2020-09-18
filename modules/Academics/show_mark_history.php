<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$session = $container->get('session');
$stid = $session->get('stid');
$tid = $session->get('tid');
$skil_id = $session->get('skil_id');
if (isActionAccessible($guid, $connection2, '/modules/Academics/entry_marks_byStudent.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage School Years'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $sqla = 'SELECT p.officialName,smarks.*
    FROM pupilsightPerson as p
    LEFT JOIN history_of_students_marks as smarks
    ON p.pupilsightPersonID = smarks.pupilsightPersonIDTaker
    WHERE smarks.test_id= "'.$tid.'" AND smarks.pupilsightPersonID="'.$stid.'" AND smarks.skill_id="'.$skil_id.'" ORDER BY smarks.id DESC';
    $resulta = $connection2->query($sqla);
    $data = $resulta->fetchAll();
  ?>
 <div class="table-responsive dataTables_wrapper ">
    <div id="expore_tbl_wrapper" class="dataTables_wrapper no-footer">
        <table class="table display data-table text-nowrap dataTable no-footer" id="expore_tbl" role="grid">
        <thead>
            <tr role="row">
                <th colspan="5" class="column relative pr-4 cursor-pointer">Marks History</th>
            </tr>
        <tr role="row">
        <th class="column relative pr-4 cursor-pointer">Slno.</th>
        <th class="column relative pr-4 cursor-pointer " style="width: 190px;">
        Staff Name</th>
        <th class="column hidden-1 sm:table-cell relative pr-4 cursor-pointer" style="width: 182px;">
        Marks</th>
        <th class="column hidden-1 md:table-cell relative pr-4 cursor-pointer"  style="width: 253px;">
            Remark
        </th>
        <th class="column " style="width: 140px;">
        Updated date and time</th>
        </tr>
        </thead>
        <tbody>
            <?php if(!empty($data)){
                $i=1;
              foreach ($data as $val) { ?>
               <tr>
                   <td style="padding: 5px !important;"><?php echo $i++;?></td>
                   <td><?php echo $val['officialName'];?></td>
                   <td><?php echo ceil($val['marks_obtained']);?></td>
                   <td><?php echo $val['remark'];?></td>
                   <td><?php echo date('Y/m/d H:s:i',strtotime($val['added_at']));?></td>
               </tr>
              <?php }

            } else { ?>
                <tr>
                    <td colspan="5" style="padding: 5px !important;">No data found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</div>
  <?php
}