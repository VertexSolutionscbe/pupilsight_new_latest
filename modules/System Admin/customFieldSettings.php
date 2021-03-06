<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Domain\System\CustomField;

//$URL = '/modules/System Admin/customFieldSettings.php';
$URL =
    $_SESSION[$guid]["absoluteURL"] .
    "/index.php?q=/modules/System Admin/customFieldSettings.php";

if (
    isActionAccessible(
        $guid,
        $connection2,
        "/modules/System Admin/customFieldSettings.php"
    ) == false
) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __("You do not have access to this action.");
    echo "</div>";
} else {

    //Proceed!
    $page->breadcrumbs->add(__("Custom Field Settings"));

    if (isset($_GET["return"])) {
        returnProcess($guid, $_GET["return"], null, null);
    }

    $customField = $container->get(CustomField::class);
    $tables = $customField->getModelTables();
    $len = count($tables);
    $i = 0;
    $dt = [];
    while ($i < $len) {
        $dt[$tables[$i]["table_name"]] = $tables[$i]["table_tag"];
        $i++;
    }

    //custom field data added

    if (isset($_POST["table_name"])) {
        $newPostFlag = true;
        $customFieldKey = md5(json_encode($_POST));
        if (isset($_SESSION["customFieldKey"])) {
            if ($customFieldKey == $_SESSION["customFieldKey"]) {
                $newPostFlag = false;
            }
        }

        if ($newPostFlag) {
            $_SESSION["customFieldKey"] = $customFieldKey;
            $customAction = $_POST["customAction"];
            if ($customAction == "add") {
                $_POST["modules"] = implode(",", $_POST["modules"]);
                if ($_POST["field_type"] == "tab") {
                    $flag = $customField->addCustomTab($_POST);
                } else {
                    $flag = $customField->addCustomField($_POST);
                }
            } elseif ($customAction == "edit") {
                $flag = $customField->editCustomField($_POST);
            }

            if ($flag) {
                $_SESSION[$guid]["pageLoads"] = null;
                $URL .= "&return=success0";
                header("Location: {$URL}");
                die();
            } else {
                getSystemSettings($guid, $connection2);
                $URL .= "&return=error2";
                header("Location: {$URL}");
                die();
            }
        }
    }

    //INSERT INTO table_name (column1, column2, column3, ...)
    //VALUES (value1, value2, value3, ...);

    /*
    print_r($tables);
    die();

    $tables = $customField->getAllTables();
    $keys = array_keys($tables[0]);
    $colname = $keys[0];
    $len = count($tables);
    $i = 0;
    
    while($i<$len){
        $kv = $tables[$i][$colname];
        $dt[$kv] = $kv;
        $i++;
    }*/

    $tableID = "pupilsightPerson";
    if (isset($_POST["tableID"])) {
        $tableID = isset($_POST["tableID"])
            ? $_POST["tableID"]
            : "pupilsightPerson";
    }

    //if ($_POST["dbColumn"] && $_POST["dbTable"]) {
    //isset($_SESSION["dbTable"])
    //}

    $form = Form::create("customFieldSearchForm", "");

    $row = $form->addRow();

    $col = $row->addColumn()->setClass("newdes");
    $col->addLabel("tableID", __("Select Table"));
    $col->addSelect("tableID")
        ->fromArray($dt)
        ->selected($tableID)
        ->required();

    $col = $row->addColumn()->setClass("newdes");
    $col->addLabel("submitInvoice", __(""));
    $col->addContent(
        '<button type=\'submit\' id="submitInvoice"  class=" btn btn-white">Go</button>'
    );

    $col = $row->addColumn()->setClass("newdes text-right");
    $col->addLabel("addCustomField", __(""));
    $col->addContent(
        '<button type=\'button\' id="addCustomField"  class=" btn btn-primary" onclick=\'loadCustomFieldModal();\'><i class="mdi mdi-plus-thick mr-2"></i>Custom Field</button>'
    );

    $row = $form->addRow();

    //$col = $row->addColumn()->setClass('newdes');
    //$col->addLabel('sortableBtn', __(''));
    //$col->addContent('<button type=\'button\' class="btn btn-primary" onclick=\'tabSortPanel(true);\'>Sort Tile / Tab</button>');

    $col = $row->addColumn()->setClass("newdes customHandleCol");
    $col->addLabel("addCustomField", __(""));
    $col->addContent(
        '<button type=\'button\' class="btn btn-white" onclick=\'deactivateField();\'>Hide Field</button>'
    );

    $col = $row->addColumn()->setClass("newdes customHandleCol");
    $col->addLabel("addCustomField", __(""));
    $col->addContent(
        '<button type=\'button\' class="btn btn-white" onclick=\'activateField();\'>Show Field</button>'
    );

    $col = $row->addColumn()->setClass("newdes customHandleCol");
    $col->addLabel("addCustomField", __(""));
    $col->addContent(
        '<button type=\'button\' id="listCustomField"  class="btn btn-white" onclick=\'loadCustomFieldList();\'>Manage Custom Field List</button>'
    );

    /*
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();
    */

    echo $form->getOutput();
    $cd = $customField->getAllColumn($tableID);

    $inactiveCol = $customField->getAllInactiveColumn($tableID);
    $customModel = $customField->loadCustomFieldModal($tableID);
    $cuModules = $customModel[0]["modules"];
    $cuTabs = $customModel[0]["tabs"];
    ?>

    <form method="post" id="customFieldFormHideShow" class="mt-4">
        <div class="table-responsive dataTables_wrapper">
            <table class="table display text-nowrap filterTable" id='customFieldTable'>
                <thead>
                    <tr>
                        <th class='column' style='width:80px;'>Serial No</th>
                        <th class='column'>Status</th>
                        <th class='column'>Field Name</th>
                        <th class='column' style='max-width:200px;'>Field Type</th>
                        <th class='column' style='max-width:200px;'>Field Key</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $len = count($cd);
                    $i = 0;
                    $cnt = 1;
                    $cls = "odd";
                    while ($i < $len) {
                        $isFieldActive = true;
                        $fieldStr = "Active"; //show or active
                        if (in_array($cd[$i]["Field"], $inactiveCol)) {
                            $isFieldActive = false;
                            $fieldStr = "Hidden";
                        }
                        $cls = "odd";
                        if ($i % 2 == 0) {
                            $cls = "even";
                        }

                        echo "\n<tr class='" . $cls . "'>";
                        echo "\n<td>" . $cnt . "</td>";
                        echo "\n<td>" . $fieldStr . "</td>";

                        if ($cd[$i]["Key"]) {
                            echo "\n<td><i class=\"mdi mdi-lock\"></i>&nbsp;&nbsp;" .
                                strtoupper($cd[$i]["Field"]) .
                                "</td>";
                            //echo "\n<td>" . strtoupper($cd[$i]["Field"]) . "</td>";
                            //} else if ($isFieldActive == FALSE) {
                            //echo "\n<td><i class=\"mdi mdi-eye-off\"></i>&nbsp;&nbsp;" . strtoupper($cd[$i]["Field"]) . "</td>";
                        } else {
                            echo "\n<td><input type='checkbox' name='fields[]' value='" .
                                $cd[$i]["Field"] .
                                "' id='" .
                                $cd[$i]["Field"] .
                                "'><label for='" .
                                $cd[$i]["Field"] .
                                "'>&nbsp;&nbsp;" .
                                strtoupper($cd[$i]["Field"]) .
                                "</label></td>";
                            //echo "\n<td>" . strtoupper($cd[$i]["Field"]) . "</td>";
                        }

                        echo "\n<td>" . $cd[$i]["Type"] . "</td>";
                        echo "\n<td>" . $cd[$i]["Key"] . "</td>";
                        //echo "\n<td>" . $cd[$i]["Default"] . "</td>";
                        echo "\n</tr>";
                        $i++;
                        $cnt++;
                    }
                    ?>
                </tbody>
            </table>
            <input type="hidden" name="type" value="hideCustomControl" id="ajaxCustomFieldType">
            <input type="hidden" name="val" value="" id='hideCustomControlTable'>
        </div>
    </form>

    <!-- Custom Field Panel -->
    <form method="post" autocomplete="on" enctype="multipart/form-data" class="smallIntBorder fullWidth standardForm" id="customFieldForm" onsubmit="pupilsightFormSubmitted(this)">
        <div class="container" id='customFieldPanel' style='font-size:14px;'>
            <h3 class='mt-4'>General Information</h3>
            <input type='hidden' name='table_name' id='dbTable' value='<?= $tableID ?>'>
            <input type='hidden' name='customAction' value='add'>

            <div class="row mt-4">
                <div class="col-sm">Modules</div>
                <div class="col-sm">
                    <?php
                    $cust = explode(",", $cuModules);
                    $clen = count($cust);
                    $ci = 0;
                    $strChecked = "checked";
                    while ($ci < $clen) {
                        $moduleName = trim($cust[$ci]);
                        echo "\n<input type='checkbox' name='modules[]' id='cuh_" .
                            $ci .
                            "' value='" .
                            $moduleName .
                            "' " .
                            $strChecked .
                            ">";
                        echo "<label for='cuh_" .
                            $ci .
                            "' class='ml-2 mr-2'>" .
                            ucwords($moduleName) .
                            "</label>";
                        $strChecked = "";
                        $ci++;
                    }
                    ?>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm" id='tabId'>Tab / Section / Tile</div>
                <div class="col-sm">
                    <?php
                    $cust = explode(",", $cuTabs);
                    $clen = count($cust);
                    $ci = 0;
                    $strChecked = "checked";
                    while ($ci < $clen) {
                        $tabName = trim($cust[$ci]);
                        $tabTitle = str_replace("_", " ", $tabName);
                        echo "\n<div class='float-left mr-2'><input type='radio' name='tab' id='rdh_" .
                            $ci .
                            "' value='" .
                            $tabName .
                            "' " .
                            $strChecked .
                            ">";
                        echo "<label for='rdh_" .
                            $ci .
                            "' class='ml-2 mr-2'>" .
                            ucwords($tabTitle) .
                            "</label></div>";
                        $ci++;
                        $strChecked = "";
                    }
                    ?>
                    <div class='float-none'></div>
                </div>
            </div>

            <div class="row mt-2 notab" style='display:none;visibility:hidden;'>
                <div class="col-sm">After DB Column</div>
                <div class="col-sm">
                    <select class='w-full' name='table_column_after'>
                        <?php
                        $len = count($cd);
                        $i = 0;
                        $cols = [];
                        $len1 = $len - 1;
                        $selected = "";
                        while ($i < $len) {
                            if ($i == $len1) {
                                $selected = "selected";
                            }
                            echo "\n<option value='" .
                                $cd[$i]["Field"] .
                                "' " .
                                $selected .
                                ">" .
                                $cd[$i]["Field"] .
                                "</option>";
                            $cols[$i] = strtolower($cd[$i]["Field"]);
                            $i++;
                            $cnt++;
                        }
                        ?>
                    </select>
                </div>
            </div>


            <div class="row mt-2">
                <div class="col-sm">Field Type</div>
                <div class="col-sm">
                    <select class='w-full' id='fieldTypeSelect' name='field_type' onchange="activateOption();">
                        <option value='tinytext'>Text Field</option>
                        <option value='text'>Text Area</option>
                        <option value='dropdown'>Dropdown</option>
                        <option value='email'>EMAIL</option>
                        <option value='mobile'>MOBILE</option>
                        <option value='number'>NUMBER</option>
                        <option value='date'>Date</option>
                        <option value='image'>Image</option>
                        <option value='file'>File Upload</option>
                        <option value='checkboxes'>Checkboxes</option>
                        <option value='radioboxes'>Radioboxes</option>
                        <option value='tab'>Tab / Section / Tile</option>
                    </select>
                </div>
            </div>

            <div class="row mt-2" id='optionPanel'>
                <div class="col-sm">Dropdown, Radio, Checkbox (comma separated values eg. male,female.)</div>
                <div class="col-sm">
                    <input type='text' class='w-full txtfield' name='options'>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm">Element Field ID* (must be unique, no special char and no space)</div>
                <div class="col-sm">
                    <input type='text' class='w-full txtfield' name='field_name' id='dbFieldName' onkeypress="return allowAlphaNumeric(event)">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm">Element Display Name*</div>
                <div class="col-sm">
                    <input type='text' class='w-full txtfield' name='field_title' id='displayName'>
                </div>
            </div>

            <div class="row mt-2 notab">
                <div class="col-sm">Element Label Description</div>
                <div class="col-sm">
                    <textarea rows='2' name='field_description' id='description' class='smarteditor'></textarea>
                </div>
            </div>

            <div class="row mt-2" id="fieldLengthPanel" style='display:none;'>
                <div class="col-sm">Element Length</div>
                <div class="col-sm">
                    <input type='text' maxlength="4" class='w-full txtfield' onkeyup="this.value=this.value.replace(/[^\d]/,'')" name='field_length' id='fieldLength'>
                </div>
            </div>

            <div class="row mt-2 notab">
                <div class="col-sm">Default Value</div>
                <div class="col-sm">
                    <input type='text' class='w-full txtfield' name='default_value' id='defaultValue'>
                </div>
            </div>

            <div class='notab'>
                <br />
                <div class="row mt-2">
                    <div class="col-sm">Should this field unique</div>
                    <div class="col-sm">
                        <input type='radio' name='isunique' id='rdUYes' value='Y'>
                        <label for='rdUYes' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='isunique' id='rdUNo' value='N' checked>
                        <label for='rdUNo' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm">Should this field searchable</div>
                    <div class="col-sm">
                        <input type='radio' name='search' id='rdSYes' value='Y'>
                        <label for='rdSYes' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='search' id='rdSNo' value='N' checked>
                        <label for='rdSNo' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm">Should this field required</div>
                    <div class="col-sm">
                        <input type='radio' name='required' id='rdRYes' value='Y'>
                        <label for='rdRYes' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='required' id='rdRNo' value='N' checked>
                        <label for='rdRNo' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm">Should this field active</div>
                    <div class="col-sm">
                        <input type='radio' name='active' id='rdAYes' value='Y' checked>
                        <label for='rdAYes' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='active' id='rdANo' value='N'>
                        <label for='rdANo' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <br />
                <h3>In Manage Console</h3>
                <div class="row mt-2">
                    <div class="col-sm">Should this field visible</div>
                    <div class="col-sm">
                        <input type='radio' name='visibility' id='rdVYes' value='Y' checked>
                        <label for='rdVYes' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='visibility' id='rdVNo' value='N'>
                        <label for='rdVNo' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm">Should this field editable</div>
                    <div class="col-sm">
                        <input type='radio' name='editable' id='rdEYes' value='Y' checked>
                        <label for='rdEYes' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='editable' id='rdENo' value='N'>
                        <label for='rdENo' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <br />
                <h3>End User Console <span class='ml-2' style='font-size:12px;'>(for parents and students)</span></h3>
                <div class="row mt-2">
                    <div class="col-sm">Should this field visible</div>
                    <div class="col-sm">
                        <input type='radio' name='parent_visible' id='rdVYes2' value='Y' checked>
                        <label for='rdVYes2' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='parent_visible' id='rdVNo2' value='N'>
                        <label for='rdVNo2' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm">Should this field editable</div>
                    <div class="col-sm">
                        <input type='radio' name='parent_editable' id='rdEYes2' value='Y' checked>
                        <label for='rdEYes2' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='parent_editable' id='rdENo2' value='N'>
                        <label for='rdENo2' class='ml-2 mr-2'>No</label>
                    </div>
                </div>
            </div>

            <br />
            <hr />
            <div class="row mb-2 mt-4">
                <div class="col-sm-auto">
                    <button type="button" class="btn btn-secondary ml-4" onclick="cancelCustomField();">Cancel</button>
                </div>
                <div class="col-sm-auto ml-auto">
                    <button type="button" class="btn btn-primary ml-2" onclick="validate();">Save</button>
                    <button type="submit" id='btnSubmit' style="display:none;visibility:hidden;"></button>
                </div>
            </div>
            <br />
        </div>
    </form>


    <!-- Custom Field Edit Panel -->
    <form method="post" autocomplete="on" enctype="multipart/form-data" class="smallIntBorder fullWidth standardForm" id="customFieldEditForm" onsubmit="pupilsightFormEditSubmitted(this)">
        <div class="container" id='customFieldEditPanel' style='font-size:14px;'>
            <h3 class='mt-4'>Edit Custom Field</h3>
            <hr style='margin:5px 0px !important;'>
            <input type='hidden' name='table_name' id='edit_dbTable' value=''>
            <input type='hidden' name='id' id='edit_id'>
            <input type='hidden' name='field_type_org' id='edit_field_type_org'>
            <input type='hidden' name='customAction' value='edit'>

            <div class="row mt-4">
                <div class="col-sm" id='tabId'>Tab / Section / Tile</div>
                <div class="col-sm">
                    <?php
                    $cust = explode(",", $cuTabs);
                    $clen = count($cust);
                    $ci = 0;
                    $strChecked = "checked";
                    while ($ci < $clen) {
                        $tabName = trim($cust[$ci]);
                        $tabTitle = str_replace("_", " ", $tabName);
                        echo "\n<div class='float-left mr-2'><input type='radio' name='edit_tab' id='edit_rdh_" .
                            $ci .
                            "' value='" .
                            $tabName .
                            "' " .
                            $strChecked .
                            ">";
                        echo "<label for='edit_rdh_" .
                            $ci .
                            "' class='ml-2 mr-2'>" .
                            ucwords($tabTitle) .
                            "</label></div>";
                        $ci++;
                        $strChecked = "";
                    }
                    ?>
                    <div class='float-none'></div>
                </div>
            </div>


            <div class="row mt-2">
                <div class="col-sm">Field Type</div>
                <div class="col-sm">
                    <select class='w-full' id='edit_fieldTypeSelect' name='field_type' onchange="editActivateOption();">
                        <option value='tinytext' class='editType'>Text Field</option>
                        <option value='text' class='editType'>Text Area</option>
                        <option value='dropdown' class='editType'>Dropdown</option>
                        <option value='email' class='editType'>EMAIL</option>
                        <option value='mobile' class='editType'>MOBILE</option>
                        <option value='number' class='editType'>NUMBER</option>
                        <option value='date' class='editType'>Date</option>
                        <option value='image' class='editType'>Image</option>
                        <option value='file' class='editType'>File Upload</option>
                        <option value='tab' id='tabFieldType'>Tab / Section / Tile</option>
                        <option value='checkboxes' class='editType'>Checkboxes</option>
                        <option value='radioboxes' class='editType'>Radio Buttons</option>
                    </select>
                </div>
            </div>

            <div class="row mt-2" id='edit_optionPanel'>
                <div class="col-sm">Dropdown, Radio, Checkbox (comma separated values eg. male,female.)</div>
                <div class="col-sm">
                    <input type='text' class='w-full txtfield' name='options' id='edit_options'>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm">Element Display Name*</div>
                <div class="col-sm">
                    <input type='text' class='w-full txtfield' name='field_title' id='edit_displayName'>
                </div>
            </div>

            <div class="row mt-2 notab">
                <div class="col-sm">Element Label Description</div>
                <div class="col-sm">
                    <textarea rows='2' name='field_description' id='edit_description' class="smarteditor"></textarea>
                </div>
            </div>

            <div class="row mt-2 notab">
                <div class="col-sm">Default Value</div>
                <div class="col-sm">
                    <input type='text' class='w-full txtfield' name='default_value' id='edit_defaultValue'>
                </div>
            </div>

            <div class='notab'>
                <div class="row mt-2">
                    <div class="col-sm">Should this field unique</div>
                    <div class="col-sm">
                        <input type='radio' name='edit_isunique' id='edit_rdUYes' value='Y'>
                        <label for='edit_rdUYes' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='edit_isunique' id='edit_rdUNo' value='N' checked>
                        <label for='edit_rdUNo' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm">Should this field required</div>
                    <div class="col-sm">
                        <input type='radio' name='edit_required' id='edit_rdRYes' value='Y' checked>
                        <label for='edit_rdRYes' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='edit_required' id='edit_rdRNo' value='N'>
                        <label for='edit_rdRNo' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm">Should this field active</div>
                    <div class="col-sm">
                        <input type='radio' name='edit_active' id='edit_rdAYes' value='Y' checked>
                        <label for='edit_rdAYes' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='edit_active' id='edit_rdANo' value='N'>
                        <label for='edit_rdANo' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <br />
                <h3>In Manage Console</h3>
                <div class="row mb-2">
                    <div class="col-sm">Should this field visible</div>
                    <div class="col-sm">
                        <input type='radio' name='edit_visibility' id='edit_rdVYes' value='Y' checked>
                        <label for='edit_rdVYes' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='edit_visibility' id='edit_rdVNo' value='N'>
                        <label for='edit_rdVNo' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-sm">Should this field editable</div>
                    <div class="col-sm">
                        <input type='radio' name='edit_editable' id='edit_rdEYes' value='Y' checked>
                        <label for='edit_rdEYes' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='edit_editable' id='edit_rdENo' value='N'>
                        <label for='edit_rdENo' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <br />
                <h3>End User Console <span class='ml-2' style='font-size:12px;'>(for parents and students)</span></h3>
                <div class="row mb-2">
                    <div class="col-sm">Should this field visible</div>
                    <div class="col-sm">
                        <input type='radio' name='edit_parent_visible' id='edit_rdVYes2' value='Y' checked>
                        <label for='edit_rdVYes2' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='edit_parent_visible' id='edit_rdVNo2' value='N'>
                        <label for='edit_rdVNo2' class='ml-2 mr-2'>No</label>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-sm">Should this field editable</div>
                    <div class="col-sm">
                        <input type='radio' name='edit_parent_editable' id='edit_rdEYes2' value='Y' checked>
                        <label for='edit_rdEYes2' class='ml-2 mr-2'>Yes</label>

                        <input type='radio' name='edit_parent_editable' id='edit_rdENo2' value='N'>
                        <label for='edit_rdENo2' class='ml-2 mr-2'>No</label>
                    </div>
                </div>
            </div>

            <br />
            <hr />
            <div class="row mb-2 mt-4">
                <div class="col-sm-auto">
                    <button type="button" class="btn btn-secondary ml-4" onclick="cancelCustomFieldEditModal();">Cancel</button>
                </div>
                <div class="col-sm-auto ml-auto">
                    <button type="button" class="btn btn-primary ml-2" onclick="validateEdit();">Save</button>
                    <button type="submit" id='btnEditSubmit' style="display:none;visibility:hidden;"></button>
                </div>
            </div>
            <br />
        </div>
    </form>

    <style>
        .sortDiv {
            margin-bottom: 4px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #f3f3f3;
            cursor: move;
        }
    </style>
    <div id='tabSortPanel'>
        <div class="w-100 mb-2 mt-2">
            <div class='float-left h2'>Tab / Tile Sorting</div>
            <div class='float-right'>
                <i class="mdi mdi-close-thick-circle" style="font-size:24px;cursor:pointer;" onclick="tabSortPanel(false);"></i>
            </div>
            <div class='clearfix'></div>
        </div>
        <form id='sortForm' method='post'>
            <div class="row mb-2" id='tabSortId' style='margin:0;'>
                <?php
                $cust = explode(",", $cuTabs);
                $clen = count($cust);
                $ci = 0;
                while ($ci < $clen) {
                    $tabName = trim($cust[$ci]);
                    $tabTitle = str_replace("_", " ", $tabName);
                    echo "\n<div class='sortDiv w-100'><input type='hidden' name='tabs[]' value='" .
                        $tabName .
                        "' >" .
                        ucwords($tabTitle) .
                        "</div>";
                    $ci++;
                }
                ?>
            </div>
            <input type="hidden" name="type" value="sortTab">
            <input type="hidden" id='sortCustomControlTable' name="val" value="">
        </form>
        <div class='w-100 mt-2'>
            <button type="button" class="btn btn-primary ml-2" onclick="saveSorting();">Save</button>
        </div>
    </div>

    <!-- Sotable Column -->
    <script>
        function sortableTile() {
            alert("Sortbale ");
        }
        $(function() {
            new Sortable(tabSortId, {
                animation: 150,
                ghostClass: 'blue-background-class'
            });
            //$("#fieldLengthPanel").hide();
            //
            $(".customHandleCol").addClass("col-auto").removeClass("col-sm");
            $('.filterTable').DataTable({
                "pageLength": 25,
                "lengthMenu": [
                    [10, 25, 50, 250, -1],
                    [10, 25, 50, 250, "All"]
                ],
                "sDom": '<"top"lpf>rt<"bottom"ipf><"clear">',
                "orderable": false,
                "targets": [3, 4]
            });
            $(".dataTables_length").find("select").css("width", "90px");
            $(".dataTables_length").find("select").css("display", "inline-block");
        });

        function saveSorting() {
            try {
                $("#sortCustomControlTable").val($("#tableID").val());
                var frmData = $('#sortForm').serialize();
                var link = "ajax_custom_data.php";
                $.ajax({
                    type: "POST",
                    url: link,
                    data: frmData,
                }).done(function(msg) {
                    console.log(msg);
                    if (msg) {
                        var obj = jQuery.parseJSON(msg);
                        if (obj.status == 1) {
                            alert("Your request has been successfully executed");
                        } else {
                            if (obj.message) {
                                alert(obj.message);
                            }
                        }
                    }
                });
            } catch (ex) {
                console.log(ex);
            }
        }
    </script>
    <!-- Custom Field Panel Close -->
    <script>
        //tab example
        function isTabSelectActive(flag) {
            if (flag) {
                $(".notab").hide();
            } else {
                $(".notab").show();
            }
        }
    </script>

    <script>
        //hide and show form
        //ajaxCustomFieldType
        function activateField() {
            var len = document.querySelectorAll('input[name="fields[]"]:checked').length;
            if (len > 0) {
                $("#ajaxCustomFieldType").val("showCustomControl");
                hideShowFormSubmit();
            } else {
                alert("You have not selected any field to hide.");
            }
        }

        function deactivateField() {
            var len = document.querySelectorAll('input[name="fields[]"]:checked').length;
            if (len > 0) {
                $("#ajaxCustomFieldType").val("hideCustomControl");
                hideShowFormSubmit();
            } else {
                alert("You have not selected any field to hide.");
            }
        }

        function hideShowFormSubmit() {
            try {
                $("#hideCustomControlTable").val($("#tableID").val());
                var frmData = $('#customFieldFormHideShow').serialize();
                var link = "ajax_custom_data.php";
                $.ajax({
                    type: "POST",
                    url: link,
                    data: frmData,
                }).done(function(msg) {
                    console.log(msg);
                    if (msg) {
                        var obj = jQuery.parseJSON(msg);
                        if (obj.status == 1) {
                            alert("Your request has been successfully executed");
                            /*$("input:checkbox[name='fields[]']:checked").each(function() {
                                $(this).remove();
                            });*/
                            location.reload();
                        } else {
                            if (obj.message) {
                                alert(obj.message);
                            }
                        }
                    }
                });
            } catch (ex) {
                console.log(ex);
            }
        }
    </script>

    <script>
        $(function() {
            $("#customFieldPanel, #customFieldEditPanel, #optionPanel, #tabSortPanel").hide();
            // loadCustomFieldModal();
        });

        function uniqueDbCol() {
            var newcol = $("#dbFieldName").val();
            if (newcol) {
                var flag = true;
                var dbcols = <?php echo json_encode($cols); ?>;
                var len = dbcols.length;
                var fieldType = $("#fieldTypeSelect").val();
                if (fieldType == "tab") {
                    flag = isUniqueTab();
                } else {
                    var i = 0;
                    newcol = newcol.toLowerCase();
                    while (i < len) {
                        if (newcol == dbcols[i]) {
                            $("#dbFieldName").focus();
                            flag = false;
                            break;
                        }
                        i++;
                    }
                }

                if (!flag) {
                    alert("Element Field ID must be unique.");
                }
                return flag;
            } else {
                alert("Element Field ID can't left blank.");
            }
            return false;
        }

        function validate() {

            var flag = true;
            if (!uniqueDbCol()) {
                flag = false;
            } else if (!isUnique('dbFieldName')) {
                alert("Element Field ID can't left blank");
                flag = false;
            } else if (!isUnique('displayName')) {
                alert("Element Display Name can't left blank");
                flag = false;
            }

            if (flag) {
                $("#btnSubmit").click();
            }

        }

        function isUnique(elementId) {
            var val = $("#" + elementId).val();
            if (val == "" || val.length == 0) {
                return false;
            }
            return true;
        }

        function isUniqueTab() {
            var newTab = $("#dbFieldName").val();
            var tabs = "<?php echo str_replace(",", " ", $cuTabs); ?>";
            if (tabs.search(newTab) == -1) {
                return true;
            } else {
                return false;
            }
        }


        function loadCustomFieldModal() {
            $("#customFieldSearchForm, #customFieldFormHideShow").hide();
            $("#customFieldPanel").show();
        }

        function cancelCustomField() {
            $("#customFieldSearchForm, #customFieldFormHideShow").show();
            $("#customFieldPanel").hide();
        }

        function saveCustomField() {
            alert("save custom field");
        }

        function activateOption() {
            //'varchar','text','date','url','select','checkboxes','radioboxes'
            var element = $("#fieldTypeSelect").val();
            $("#fieldLengthPanel").hide();
            if (element == "dropdown" || element == "checkboxes" || element == "radioboxes") {
                $("#optionPanel").show();
                isTabSelectActive(false);
                $("#tabId").html("Tab / Section / Tile");
            } else if (element == "tab") {
                $("#optionPanel").hide();
                isTabSelectActive(true);
                $("#tabId").html("<b>After</b> Tab / Section / Tile ");
            } else if (element == "number") {
                $("#optionPanel").hide();
                $("#tabId").html("Tab / Section / Tile");
                isTabSelectActive(false);
                $("#fieldLengthPanel").show();
            } else {
                $("#optionPanel").hide();
                $("#tabId").html("Tab / Section / Tile");
                isTabSelectActive(false);
            }
        }

        function allowAlphaNumeric(event) {
            var charCode = event.keyCode;
            if ((charCode >= 48 && charCode <= 57) || (charCode > 64 && charCode < 91) || (charCode > 94 && charCode < 123) || charCode == 8) {
                return true;
            }
            return false;
        }
    </script>

    <script>
        //custom field Load
        /*
        function _getParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        function _getPageNames() {
            var tm = _getParameterByName("q");
            var st = tm.split("/");
            var len = st.length;
            var i = 0;
            var pageName = "";
            while (i < len) {
                if (st[i]) {
                    var n = st[i].indexOf(".php");
                    if (n > 0) {
                        pageName = st[i];
                        break;
                    }
                }
                i++;
            }
            return pageName;
        }

        function loadCustomField() {
            try {
                var link = "ajax_custom_data.php";
                var val = _getPageNames();
                if (val) {
                    var type = "getCustomControl";
                    $.ajax({
                        type: "POST",
                        url: link,
                        data: {
                            val: val,
                            type: type
                        },
                    }).done(function(msg) {
                        console.log(msg);
                        if (msg != "") {
                            var obj = jQuery.parseJSON(msg);
                        }
                    });
                }
            } catch (ex) {
                console.log(ex);
            }
        }*/
    </script>
    <?php
    $customFieldList = $customField->getCustomFieldList();
    //print_r($customFieldList);
    //die();
    $len = count($customFieldList);
    ?>
    <!----Custom Field List----->
    <div id="customFieldListPanel">
        <table class="table display text-nowrap" cellspacing="0">
            <thead>
                <tr>

                    <th colspan='4' style='line-height: 28px;'>Total Custom Fields : <b><?= $len ?></b></th>
                    <th colspan='3' style='text-align:right;line-height: 28px;'>
                        To change tab or section use dropdown
                    </th>
                    <th colspan='2' style='text-align:right;line-height: 28px;'>
                        <span onclick="cancelCustomFieldList();" style='width:40px;cursor:pointer;margin-left:20px;'><i class="mdi mdi-close" style='font-size:24px;'></i></span>
                    </th>

                </tr>
            </thead>
        </table>
        <table class="table table-responsive display filterTable " cellspacing="0" id='customFieldList'>
            <thead>
                <tr>
                    <th class='column'>Table</th>
                    <th class='column'>Field Name</th>
                    <th class='column'>Field ID</th>
                    <th class='column'>Field Type</th>
                    <th class='column'>Modules</th>
                    <th class='column'>Tab / Section</th>
                    <th class='column'>Active</th>
                    <th class='column'>Page View</th>
                    <th class='column'>Page Edit</th>
                    <th class='column'>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 0;
            $str = "";
            //print_r($customFieldList);
            while ($i < $len) {
                $field_type = $customFieldList[$i]["field_type"];
                //if ($field_type) {
                $isColAvl = $customField->isColumnAvailable(
                    $customFieldList[$i]["table_name"],
                    $customFieldList[$i]["field_name"]
                );
                $id = $customFieldList[$i]["id"];
                if ($isColAvl == false) {
                    $customField->removeUnusedColumn($id);
                } else {
                    $fieldTitle = $customFieldList[$i]["field_title"];
                    $fieldName = $customFieldList[$i]["field_name"];
                    if (empty($fieldTitle)) {
                        $fieldTitle = $fieldName;
                    }

                    $str .= "\n<tr id='custom_row_" . $id . "' >";
                    $str .=
                        "<td>" . $customFieldList[$i]["table_tag"] . "</td>";
                    $str .= "<td>" . $fieldTitle . "</td>";
                    $str .= "<td>" . $fieldName . "</td>";
                    $str .=
                        "<td>" .
                        $customField->getInputTag(
                            $customFieldList[$i]["field_type"]
                        ) .
                        "</td>";
                    $mod =
                        "<div>" .
                        str_replace(
                            ",",
                            "</div><div>",
                            $customFieldList[$i]["modules"]
                        ) .
                        "</div>";
                    $str .= "<td>" . $mod . "</td>";

                    $tabs = explode(",", $customFieldList[$i]["tabs"]);
                    $jlen = count($tabs);
                    $j = 0;
                    $opt =
                        "<input type='hidden' id='fieldid_" .
                        $fieldName .
                        "' value='" .
                        $customFieldList[$i]["id"] .
                        "'>";
                    $opt .=
                        "<input type='hidden' id='tabSelect_" .
                        $fieldName .
                        "' value='" .
                        $customFieldList[$i]["tab"] .
                        "'>";
                    $opt .=
                        "<select id='switchTab_" .
                        $fieldName .
                        "' onchange=\"changeTab('" .
                        $fieldName .
                        "');\">";
                    $opt .= "\n<option value=''>Select</option>";
                    $optse = "";
                    while ($j < $jlen) {
                        $optse = "";
                        if ($tabs[$j] == $customFieldList[$i]["tab"]) {
                            $optse = " selected";
                        }
                        $opt .=
                            "\n<option value='" .
                            $tabs[$j] .
                            "' " .
                            $optse .
                            ">" .
                            $tabs[$j] .
                            "</option>";
                        $j++;
                    }
                    $opt .= "</select>";

                    $str .= "<td>" . $opt . "</td>";

                    $str .= "<td>" . $customFieldList[$i]["active"] . "</td>";
                    $pv =
                        "<div>" .
                        str_replace(
                            ",",
                            "</div><div>",
                            $customFieldList[$i]["page_view"]
                        ) .
                        "</div>";
                    $str .= "<td>" . $pv . "</td>";
                    $pe =
                        "<div>" .
                        str_replace(
                            ",",
                            "</div><div>",
                            $customFieldList[$i]["page_edit"]
                        ) .
                        "</div>";
                    $str .= "<td>" . $pe . "</td>";

                    $str .= "<td>";
                    if($customFieldList[$i]["active"]=="Y"){
                    $str .=
                        "<button class='btn btn-danger' title='Delete' onclick=\"deleteCustomField('" .
                        $id .
                        "','" .
                        $customFieldList[$i]["table_name"] .
                        "','" .
                        $fieldName .
                        "');\"><i class='mdi mdi-delete mdi-18px'></i></button>";
                    $str .=
                        "<button class='btn btn-info ml-1' title='Edit' onclick=\"loadCustomFieldEditModal('" .
                        $id .
                        "');\"><i class='mdi mdi-pencil mdi-18px'></i></button>";
                    }else{
                        $str .="NA";
                    }

                    $str .= "</td>";
                    $str .= "</tr>";
                }
                //}
                $i++;
            }

            echo $str;

}
?>
            </tbody>
        </table>
    </div>
    <script>
        var objCustomFieldList = <?php echo json_encode($customFieldList); ?>;

        function loadCustomFieldEditModal(id) {
            hideAllPanel();

            $("#customFieldEditPanel").show();
            var len = objCustomFieldList.length;
            var i = 0;
            var obj = null;
            while (i < len) {
                if (objCustomFieldList[i]["id"] == id) {
                    obj = objCustomFieldList[i];
                    break;
                }
                i++;
            }
            console.log(obj);
            if (obj) {
                //set edit Value
                setEditValue(obj);
                editActivateOption();
            }
        }

        function setEditValue(obj) {
            $("#edit_dbTable").val(obj["table_name"]);
            $("#edit_id").val(obj["id"]);


            $("input[name=edit_tab][value=" + obj["tab"] + "]").attr('checked', 'checked');
            $("#tabFieldType").hide();
            $(".editType").show();
            if (obj["field_type"] == "varchar") {
                obj["field_type"] = "tinytext";
            } else if (obj["field_type"] == "tab") {
                $("#tabFieldType").show();
                $(".editType").hide();
            }

            $("#edit_field_type_org").val(obj["field_type"]);
            $("#edit_fieldTypeSelect").val(obj["field_type"]);
            $("#edit_options").val(obj["options"]);
            $("#edit_displayName").val(obj["field_title"]);
            $('#edit_description').trumbowyg('html', obj["field_description"]);
            //$("#edit_description").val(obj["field_description"]);
            $("#edit_defaultValue").val(obj["default_value"]);

            $("input[name=edit_isunique][value=" + obj["isunique"] + "]").attr('checked', 'checked');
            $("input[name=edit_required][value=" + obj["required"] + "]").attr('checked', 'checked');
            $("input[name=edit_active][value=" + obj["active"] + "]").attr('checked', 'checked');
            $("input[name=edit_visibility][value=" + obj["visibility"] + "]").attr('checked', 'checked');
            $("input[name=edit_editable][value=" + obj["editable"] + "]").attr('checked', 'checked');
            $("input[name=edit_parent_visible][value=" + obj["parent_visible"] + "]").attr('checked', 'checked');
            $("input[name=edit_parent_editable][value=" + obj["parent_editable"] + "]").attr('checked', 'checked');
        }

        function editActivateOption() {
            //'varchar','text','date','url','select','checkboxes','radioboxes'
            var element = $("#edit_fieldTypeSelect").val();
            $("#fieldLengthPanel, #edit_optionPanel").hide();
            $("#tabId").html("Tab / Section / Tile");
            isTabSelectActive(false);
            if (element == "dropdown" || element == "checkboxes" || element == "radioboxes") {
                $("#edit_optionPanel").show();
            } else if (element == "tab") {
                isTabSelectActive(true);
                $("#tabId").html("<b>After</b> Tab / Section / Tile ");
            }
        }


        function cancelCustomFieldEditModal() {
            // hideAllPanel();
            loadCustomFieldList();
            //$("#customFieldSearchForm, #customFieldFormHideShow").show();
        }

        function validateEdit() {

            var flag = true;
            if (!isUnique('edit_displayName')) {
                alert("Element Display Name can't left blank");
                flag = false;
            }

            var fieldType = $("#edit_fieldTypeSelect").val();
            if (fieldType == "dropdown" || fieldType == "checkboxes" || fieldType == "radioboxes") {
                if (!isUnique('edit_options')) {
                    alert("Dropdown, Radio, Checkbox (comma separated values required).");
                    flag = false;
                }
            }

            if (flag) {
                $("#btnEditSubmit").click();
            }
        }
    </script>
    <script>
        $(function() {
            $("#customFieldListPanel").hide();
        });


        function deleteCustomField(id, tableName, fieldName) {
            if (confirm("Are you sure you want to delete this field ")) {
                try {
                    var link = "ajax_custom_data.php";
                    var type = "deleteField";
                    $.ajax({
                        type: "POST",
                        url: link,
                        data: {
                            val: fieldName,
                            type: type,
                            id: id,
                            table_name: tableName
                        },
                    }).done(function(msg) {
                        console.log(msg);
                        if (msg != "") {
                            var obj = jQuery.parseJSON(msg);
                            if (obj.status == 1) {
                                alert("Your request has been successfully executed");
                                $("#custom_row_" + id).remove();
                            } else {
                                alert("Error : " + obj.message);
                            }
                        }
                    });
                } catch (ex) {
                    console.log(ex);
                }
            } else {
                var fieldNameVal = $("#tabSelect_" + fieldName).val();
                $("#switchTab_" + fieldName).val(fieldNameVal);
            }
        }

        function tabSortPanel(flag) {
            hideAllPanel();
            if (flag) {
                $("#tabSortPanel").show();
            } else {
                cancelCustomFieldList();
            }
        }

        function hideAllPanel() {
            $("#tabSortPanel, #customFieldListPanel, #customFieldSearchForm, #customFieldFormHideShow, #customFieldPanel, #customFieldEditPanel").hide();
        }

        function loadCustomFieldList() {
            hideAllPanel();
            $("#customFieldListPanel").show();
        }

        function cancelCustomFieldList() {
            hideAllPanel();
            $("#customFieldSearchForm").show();
            $('#customFieldFormHideShow').show();
        }

        function changeTab(fieldName) {
            var sv = $("#tabSelect_" + fieldName).val();
            var nv = $("#switchTab_" + fieldName).val();
            var fieldid = $("#fieldid_" + fieldName).val();
            if (confirm("Are you sure you want to switch tab from " + sv + " to other tab " + nv)) {
                try {
                    var link = "ajax_custom_data.php";
                    var type = "switchCustomControlTab";
                    $.ajax({
                        type: "POST",
                        url: link,
                        data: {
                            val: nv,
                            type: type,
                            fieldid: fieldid
                        },
                    }).done(function(msg) {
                        console.log(msg);
                        if (msg != "") {
                            var obj = jQuery.parseJSON(msg);
                            if (obj.status == 1) {
                                $("#tabSelect_" + fieldName).val(nv);
                                alert("Your request has been successfully executed");
                            } else {
                                alert("Error : " + obj.message);
                            }
                        }
                    });
                } catch (ex) {
                    console.log(ex);
                }
            } else {
                var fieldNameVal = $("#tabSelect_" + fieldName).val();
                $("#switchTab_" + fieldName).val(fieldNameVal);
            }
        }
    </script>
    <script>
        $('.smarteditor').trumbowyg({
            autogrow: true,
            btns: [

                ['viewHTML'],
                ['undo', 'redo'], // Only supported in Blink browsers
                ['formatting'],
                ['strong', 'em', 'del'],
                ['superscript', 'subscript'],
                ['link'],
                ['foreColor', 'backColor'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['horizontalRule'],
                ['removeformat'],
                ['fullscreen']
            ],
        });
        //['insertImage'], ignore for now
    </script>
    