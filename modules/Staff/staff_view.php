<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffGateway;

if (
    isActionAccessible($guid, $connection2, "/modules/Staff/staff_view.php") ==
    false
) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __("You do not have access to this action.");
    echo "</div>";
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET["q"], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __("The highest grouped action cannot be determined.");
        echo "</div>";
    } else {
        //Proceed!

        $pupilsightPersonID = $_SESSION[$guid]["pupilsightPersonID"];

        $page->breadcrumbs->add(__("View Staff Profiles"));
        $pupilsightSchoolYearID = $_SESSION[$guid]["pupilsightSchoolYearID"];
        $search = isset($_GET["search"]) ? $_GET["search"] : "";
        $allStaff = isset($_GET["allStaff"]) ? $_GET["allStaff"] : "";

        if ($_POST) {
            $pupilsightProgramID = $_POST["pupilsightProgramID"];
            $pupilsightDepartmentID = $_POST["pupilsightDepartmentID"];
            $search = $_POST["search"];
        } else {
            $pupilsightProgramID = "";
            $pupilsightDepartmentID = "";
            $search = "";
        }

        $staffGateway = $container->get(StaffGateway::class);

        // QUERY
        $criteria = $staffGateway
            ->newQueryCriteria()
            ->searchBy($staffGateway->getSearchableColumns(), $search)
            ->filterBy("all", $allStaff)
            ->pageSize(5000)
            ->sortBy(["surname", "preferredName"])
            ->fromPOST();

        echo "<h2>";
        echo __("Search");
        echo "</h2>";
        $classes = ["" => "Select Class"];

        $sqlp = "SELECT pupilsightProgramID, name FROM pupilsightProgram ";
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        $program = [];
        $program2 = [];
        $program1 = ["" => "Select Program"];
        foreach ($rowdataprog as $dt) {
            $program2[$dt["pupilsightProgramID"]] = $dt["name"];
        }
        $program = $program1 + $program2;
        $form = Form::create("filter", "");

        //select subjects from department
        $sqld =
            "SELECT pupilsightDepartmentID, name FROM pupilsightDepartment ";
        $resultd = $connection2->query($sqld);
        $rowdatadept = $resultd->fetchAll();
        $subjects = ["" => __("Select Subject")];
        $subject2 = [];
        // $subject1=array(''=>'Select Subjects');
        foreach ($rowdatadept as $dt) {
            $subject2[$dt["pupilsightDepartmentID"]] = $dt["name"];
        }
        $subjects += $subject2;

        $form->setClass("noIntBorder fullWidth");
        $form->addHiddenValue("address", $_SESSION[$guid]["address"]);
        $form->addHiddenValue(
            "q",
            "/modules/" . $_SESSION[$guid]["module"] . "/staff_view.php"
        );
        $row = $form->addRow();

        $col = $row->addColumn()->setClass("newdes");
        $col->addLabel("pupilsightProgramID", __("Program"));
        $col->addSelect("pupilsightProgramID")
            ->setId("pupilsightProgramIDbyPP")
            ->fromArray($program)
            ->selected($pupilsightProgramID)
            ->placeholder("Select Program");

        $col = $row->addColumn()->setClass("newdes");
        $col->addLabel("pupilsightDepartmentID", __("Subjects"));
        $col->addSelect("pupilsightDepartmentID")
            ->fromArray($subjects)
            ->selected($pupilsightDepartmentID)
            ->placeholder();

        $col = $row->addColumn()->setClass("newdes");
        $col->addLabel("search", __("Search By Name, Email, Type, Phone"));
        $col->addTextField("search")
            ->setValue($criteria->getSearchText())
            ->maxLength(20);

        $col = $row->addColumn()->setClass("newdes");
        $col->addLabel("", __(""));
        $col->addSearchSubmit($pupilsight->session)->setClass(
            "submit_align submt"
        );

        echo $form->getOutput();

        echo "<h2>";
        echo __("Choose A Staff Member");
        echo "</h2>";
        echo "<a style='display:none' id='clickchnagestatus' href='fullscreen.php?q=/modules/Staff/change_staff_status.php'  class='thickbox '>Change Route</a>";
        echo "<div style='height:50px;'><div class='float-left mb-2'><a  id=''  data-toggle='modal' data-target='#large-modal-new_staff' data-noti='2'  class='sendButton_staff btn btn-white'>Send SMS</a>&nbsp;&nbsp;";
        echo "<a  id='' data-toggle='modal' data-noti='1' data-target='#large-modal-new_staff' class='sendButton_staff btn btn-white'>Send Email</a>&nbsp;&nbsp;<a  id='change_status' data-type='staff'  data-noti='1'  class=' btn btn-white'>Change Status</a>";
        echo "&nbsp;&nbsp;<a style='' href='index.php?q=/modules/Staff/message_history.php' class='btn btn-white' id='sendEmail'>SMS/EMAIL - SENT ITEMS</a>";
        echo "&nbsp;&nbsp;<a style='' href='index.php?q=/modules/Staff/staff_manage_add.php' class='btn btn-white' id='sendEmail'>ADD</a>";
        echo "&nbsp;&nbsp;<a style=' ' class=' btn btn-white' href='index.php?q=/modules/Staff/field_to_show.php'  >Field to Show</a>";
        echo "&nbsp;&nbsp;<a data-hrf='index.php?q=/modules/Staff/feedback_manage.php' href='' class='btn btn-white' id='addFeedback'>Feedback</a>";

        echo " </div><div class='float-none'></div></div>";

        $staff = $staffGateway->queryAllStaff(
            $criteria,
            $pupilsightSchoolYearID,
            $pupilsightProgramID,
            $pupilsightDepartmentID
        );

        $sqlf =
            "SELECT field_name FROM staff_field_show WHERE pupilsightPersonID = " .
            $pupilsightPersonID .
            " ";
        $resultf = $connection2->query($sqlf);
        $showfield = $resultf->fetchAll();

        $sql =
            'SELECT field_name, field_title FROM custom_field WHERE FIND_IN_SET("staff",modules) ';
        $result = $connection2->query($sql);
        $customFields = $result->fetchAll();

        // DATA TABLE
        $table = DataTable::createPaginated("staffManage", $criteria);

        // $table->addHeaderAction('add', __('Add'))
        //     ->setURL('/modules/Staff/staff_manage_add.php')
        //     ->addParam('search', $search)
        //     ->displayLabel();

        $table->modifyRows(function ($person, $row) {
            if (!empty($person["status"]) && $person["status"] != "Full") {
                $row->addClass("error");
            }
            return $row;
        });

        // echo $butt = '<i id="expore_xl_all" title="Export Excel" data-title="staf_details" class="far fa-file-excel download_icon"></i> ';
        if ($highestAction == "View Staff Profile_full") {
            $table->addMetaData("filterOptions", [
                "all:on" => __("All Staff"),
                "type:teaching" => __("Staff Type") . ": " . __("Teaching"),
                "type:support" => __("Staff Type") . ": " . __("Support"),
                "type:other" => __("Staff Type") . ": " . __("Other"),
            ]);
        }

        // COLUMNS

        $table
            ->addCheckboxColumn("stuid", __(""))
            ->setClass("chkbox")
            ->notSortable()
            ->format(function ($staff) {
                return "<input id='stuid' name='stuid[]' type='checkbox' value='" .
                    $staff["stuid"] .
                    "' class='enrollstuid' data-del='1' data-name='" .
                    $staff["officialName"] .
                    "'>";
            });

        if (!empty($showfield)) {
            foreach ($showfield as $sf) {
                if ($sf["field_name"] == "staff_name") {
                    $table->addColumn("officialName", __("Name"));
                }
                if ($sf["field_name"] == "email") {
                    $table->addColumn("email", __("Email"));
                }
                if ($sf["field_name"] == "phone1") {
                    $table->addColumn("phone1", __("Phone 1"));
                }
                if ($sf["field_name"] == "phone2") {
                    $table->addColumn("phone2", __("Phone 2"));
                }
                if ($sf["field_name"] == "phone3") {
                    $table->addColumn("phone3", __("Phone 3"));
                }
                if ($sf["field_name"] == "phone4") {
                    $table->addColumn("phone4", __("Phone 4"));
                }
                if ($sf["field_name"] == "type") {
                    $table->addColumn("type", __("Type"));
                }
                if ($sf["field_name"] == "jobTitle") {
                    $table->addColumn("jobTitle", __("Job Title"));
                }
                if ($sf["field_name"] == "username") {
                    $table->addColumn("username", __("Username"));
                }
                if ($sf["field_name"] == "dob") {
                    $table->addColumn("dob", __("Date of Birth"));
                }
                if ($sf["field_name"] == "gender") {
                    $table->addColumn("gender", __("Gender"));
                }
                if ($sf["field_name"] == "stat") {
                    $table->addColumn("stat", __("Status"));
                }

                if (!empty($customFields)) {
                    foreach ($customFields as $cf) {
                        if ($sf["field_name"] == $cf["field_name"]) {
                            $table->addColumn(
                                $cf["field_name"],
                                __($cf["field_title"])
                            );
                        }
                    }
                }
            }
        } else {
            $table->addColumn("officialName", __("Name"));
            $table
                ->addColumn("email", __("Email"))
                ->width("25%")
                ->translatable();
            $table
                ->addColumn("phone1", __("Phone"))
                ->width("25%")
                ->translatable();
            $table
                ->addColumn("type", __("Type"))
                ->width("25%")
                ->translatable();
            $table->addColumn("jobTitle", __("Job Title"))->width("25%");
            $table
                ->addColumn("username", __("UserName"))
                ->width("25%")
                ->translatable();
            $table
                ->addColumn("dob", __("Date of Birth"))
                ->width("25%")
                ->translatable();
            $table
                ->addColumn("gender", __("Gender"))
                ->width("25%")
                ->translatable();
            $table
                ->addColumn("stat", __("Status"))
                ->width("25%")
                ->translatable();
        }
        // ACTIONS
        $table
            ->addActionColumn()
            ->addParam("pupilsightPersonID")
            ->addParam("search", $criteria->getSearchText(true))

            ->format(function ($person, $actions) use ($guid) {
                $actions
                    ->addAction("edit", __("Edit"))
                    ->setURL("/modules/Staff/staff_manage_edit.php");
                //     $actions->addAction('delete', __('Delete'))
                //    ->setURL('/modules/Transport/transport_route_delete.php');
                $actions
                    ->addAction("view", __("View Details"))
                    ->setURL("/modules/Staff/staff_view_details.php");
            });

        echo $table->render($staff);
    }
}
?>
<style>
    .download_icon {
        font-size: 30px;
        color: green;
        margin: 4px;
        cursor: pointer;
    }
</style>


<script>
    $(document).on('click', '#addFeedback', function() {
        if ($("input[name='stuid[]']").is(':checked')) {
            var checked = $("input[name='stuid[]']:checked").length;
            if (checked > 1) {
                alert("Please Select One Staff!");
                return false;
            } else {
                var hrf = $(this).attr('data-hrf');
                var id = $("input[name='stuid[]']:checked").val();
                if (id != '') {
                    var newhrf = hrf + '&stid=' + id;
                    $("#addFeedback").attr('href', newhrf);
                    $("#addFeedback")[0].click();
                } else {
                    alert("Please Select Staff!");
                    return false;
                }
            }
        } else {
            alert("Please Select Staff!");
            return false;
        }
    });
</script>
