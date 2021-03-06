<?php
/*
Pupilsight, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

@session_start() ;

include "./modules/Help Desk/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Help Desk/helpDesk_manageTechnicianGroup.php") == FALSE) {
    //Acess denied
    print "<div class='error'>" ;
        print __($guid, "You do not have access to this action.") ;
    print "</div>" ;
} else {
    //Proceed!
    print "<div class='trail'>" ;
        print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . __($guid, "Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . __($guid, getModuleName($_GET["q"])) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/helpDesk_manageTechnicianGroup.php'>" . __($guid, "Manage Technician Groups") . "</a> > </div><div class='trailEnd'>" . __($guid, 'Delete Technician Group') . "</div>" ;
    print "</div>" ;
    
    $highestAction = getHighestGroupedAction($guid, "/modules/Help Desk/helpDesk_manageTechnicianGroup.php", $connection2) ;
    if ($highestAction == FALSE) {
        print "<div class='error'>" ;
        print __($guid, "The highest grouped action cannot be determined.") ;
        print "</div>" ;
        exit();
    }

    if ($highestAction != "Manage Technician Groups") { 
        print "<div class='error'>" ;
            print __($guid, "You do not have access to this action.") ;
        print "</div>" ;
        exit();
    }

    $groupID = null;
    if (isset($_GET["groupID"])){ 
        $groupID = $_GET["groupID"]; 
    } else {
        print "<div class='error'>" ;
            print __($guid, "No group selected.") ;
        print "</div>" ;
        exit();
    }

    try {
        $data = array();
        $sql = "SELECT * FROM helpDeskTechGroups ORDER BY helpDeskTechGroups.groupID ASC";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowcount() == 1) {
        print "<div class='error'>" ;
            print __($guid, "Cannot delete last technician group.") ;
        print "</div>" ;
        exit();
    }

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    
    ?>
    
    <form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . " /modules/" . $_SESSION[$guid]["module"] . "/helpDesk_technicianGroupDeleteProcess.php?groupID=" . $groupID ?>">
        <table class='smallIntBorder' cellspacing='0' style="width: 100%">  
            <tr>
                <td>
                    <b>
                        <?php print __($guid, 'New Technician Group') . " *"; ?>
                    </b><br/>
                </td>
                <td class="right">
                    <select name='group' id='group' style='width:302px'>
                        <option value=''>Please select...</option>
                            <?php
                                $group = null;
                                if (isset($_GET['group'])) {
                                    $group = $_GET['group'];
                                }
                                while ($option = $result->fetch()) {
                                    if ($groupID != $option["groupID"]) {
                                        $selected = "";
                                        if($option["groupID"] == $group) {
                                            $selected = "selected";
                                        }
                                        print "<option $selected value='" . $option["groupID"] . "'>". $option["groupName"] ."</option>" ;
                                    }
                                }
                            ?>
                    </select>
                    <script type="text/javascript">
                        var name2=new LiveValidation('group');
                        name2.add(Validate.Presence);
                    </script>
                </td>
            </tr>
            <tr>
                <td>
                    <span style="font-size: 90%"><i>* <?php print __($guid, "denotes a required field") ; ?></i></span>
                </td>
                <td class="right">
                    <input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
                    <input type="submit" value="<?php print __($guid, "Submit") ; ?>">
                </td>
            </tr>
        </table>
    </form>
<?php
}
?>