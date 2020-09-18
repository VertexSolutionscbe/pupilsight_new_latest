<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Students/student_view_details_notes_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $allStudents = $_GET['allStudents'] ?? '';
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? '';

    $enableStudentNotes = getSettingByScope($connection2, 'Students', 'enableStudentNotes');
    if ($enableStudentNotes != 'Y') {
        echo "<div class='alert alert-danger'>";
        echo __('You do not have access to this action.');
        echo '</div>';
    } else {
        $pupilsightPersonID = $_GET['pupilsightPersonID'];
        $subpage = $_GET['subpage'];
        if ($pupilsightPersonID == '' or $subpage == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $student = $result->fetch();

                //Proceed!
                $page->breadcrumbs
                    ->add(__('View Student Profiles'), 'student_view.php')
                    ->add(Format::name('', $student['preferredName'], $student['surname'], 'Student'), 'student_view_details.php', ['pupilsightPersonID' => $pupilsightPersonID, 'subpage' => $subpage, 'allStudents' => $allStudents])
                    ->add(__('Add Student Note'));

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                if ($_GET['search'] != '') {
                    echo "<div class='linkTop'>";
                    echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=$pupilsightPersonID&search=".$_GET['search']."&subpage=$subpage&category=".$_GET['category']."&allStudents=$allStudents'>".__('Back to Search Results').'</a>';
                    echo '</div>';
				}
				
				$form = Form::create('notes', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/student_view_details_notes_addProcess.php?pupilsightPersonID=$pupilsightPersonID&search=".$_GET['search']."&subpage=$subpage&category=".$_GET['category']."&allStudents=$allStudents");

				$form->addHiddenValue('address', $_SESSION[$guid]['address']);

				$row = $form->addRow();
					$row->addLabel('title', __('Title'));
					$row->addTextField('title')->required()->maxLength(100);

				$sql = "SELECT pupilsightStudentNoteCategoryID as value, name FROM pupilsightStudentNoteCategory WHERE active='Y' ORDER BY name";
				$row = $form->addRow();
					$row->addLabel('pupilsightStudentNoteCategoryID', __('Category'));
					$row->addSelect('pupilsightStudentNoteCategoryID')->fromQuery($pdo, $sql)->required()->placeholder();

				$row = $form->addRow();
					$column = $row->addColumn();
					$column->addLabel('note', __('Note'));
					$column->addEditor('note', $guid)->required()->setRows(25)->showMedia();
								
				$row = $form->addRow();
					$row->addFooter();
					$row->addSubmit();
				
				echo $form->getOutput();
				?>

				<script type="text/javascript">
				$("#pupilsightStudentNoteCategoryID").change(function() {
					if ($("#pupilsightStudentNoteCategoryID").val() != "Please select...") {
						$.get('<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/Students/student_view_details_notes_addAjax.php?pupilsightStudentNoteCategoryID=' ?>' + $("#pupilsightStudentNoteCategoryID").val(), function(data){
							if (tinyMCE.activeEditor==null) {
								if ($("textarea#note").val()=="") {
									$("textarea#note").val(data) ;
								}
							} else {
								if (tinyMCE.get('note').getContent()=="") {
									tinyMCE.get('note').setContent(data) ;
								}
							}
						});
					
					}
				});
				</script>

				<?php
            }
        }
    }
}
