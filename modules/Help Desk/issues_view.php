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

//Module includes
include "./modules/Help Desk/moduleFunctions.php" ;

if (isModuleAccessible($guid, $connection2) == FALSE) {
    //Acess denied
    print "<div class='error'>" ;
        print "You do not have access to this action." ;
    print "</div>" ;
} else {
    print "<div class='trail'>" ;
        print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . __($guid, "Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . __($guid, getModuleName($_GET["q"])) . "</a> > </div><div class='trailEnd'>" . __($guid, 'Issues') . "</div>" ;
    print "</div>" ;

    if (isset($_GET['return'])) {
        $editLink = null;
        if (isset($_GET['issueID'])) {
            $editLink = $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Help Deskissues_discussView.php&issueID=" . $_GET['issueID'];
        }
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $highestAction = getHighestGroupedAction($guid, $_GET["q"], $connection2) ;
    if ($highestAction == FALSE) {
        print "<div class='error'>" ;
        print __($guid, "The highest grouped action cannot be determined.") ;
        print "</div>" ;
        exit();
    }

    print "<h3>" ;
        print __($guid, "Filter") ;
    print "</h3>" ;


    $filter=null ;
    $filter2=null ;
    $filter3=null ;
    $filter4=null ;
    $yearFilter=null;
    $IDFilter="" ;
    $whereYear="";
    $whereUsed=false;

    if (isset($_POST["filter"])) {
        $filter = $_POST["filter"] ;
    }
    if (isset($_POST["filter2"])) {
        $filter2 = $_POST["filter2"] ;
    }
    if (isset($_POST["filter3"])) {
        $filter3 = $_POST["filter3"] ;
    }
    if (isset($_POST["filter4"])) {
        $filter4 = $_POST["filter4"] ;
    }
    if (isset($_POST["yearFilter"])) {
        $yearFilter = $_POST["yearFilter"] ;
    }

    if (isset($_POST["IDFilter"])) {
        $IDFilter=intval($_POST["IDFilter"]) ;
    }

    $settings = getHelpDeskSettings($connection2);
    $priorityFilters = array("All");
    $priorityName = null;
    $categoryFilters = array("All");

    while ($row = $settings->fetch()) {
        if ($row["name"] == "issuePriority") {
            foreach (explode(",", $row["value"]) as $type) {
                if ($type != "") {
                    array_push($priorityFilters, $type);
                }
            }
        } else if ($row["name"] == "issuePriorityName") {
            $priorityName = $row["value"];
        } else if ($row["name"] == "issueCategory") {
            foreach (explode(",", $row["value"]) as $type) {
                if ($type != "") {
                    array_push($categoryFilters, $type);
                }
            }
        }
    }

    $renderPriority = count($priorityFilters)>1;
    $renderCategory = count($categoryFilters)>1;

    $issueFilters = array();
    if (getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "viewIssue")) {
        array_push($issueFilters, "All");
    }
    if (isTechnician($connection2, $_SESSION[$guid]["pupilsightPersonID"])) {
        array_push($issueFilters, "My Working");
    }
    array_push($issueFilters, "My Issues");
    if (isTechnician($connection2, $_SESSION[$guid]["pupilsightPersonID"])) {   
        $statusFilters = array("Pending");
        if (getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "viewIssueStatus")=="All") {
            $statusFilters = array("All", "Unassigned", "Pending", "Resolved");
        } else if (getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "viewIssueStatus")=="UP") {
            $statusFilters = array("Unassigned and Pending", "Unassigned", "Pending");
        } else if (getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "viewIssueStatus")=="PR") {
            $statusFilters = array("Pending and Resolved", "Pending", "Resolved");
        }
    } else {
        $statusFilters = array("All", "Unassigned", "Pending", "Resolved");
    }

    $dataIssue["helpDeskpupilsightPersonID"] = $_SESSION[$guid]["pupilsightPersonID"];
    $whereIssue = "";
    
    if ($yearFilter == "" || $yearFilter == null) {
        $yearFilter = $_SESSION[$guid]["pupilsightSchoolYearID"];
    }


    if ($yearFilter != "All Years") {
        $dataIssue["pupilsightSchoolYearID"] = $yearFilter;
        $whereYear = " WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID";
        $whereUsed = true;
    }


    if ($filter == "" || $filter == null) {
        $filter = $issueFilters[0];
    }

    if ($filter == "My Issues") {
        if ($whereUsed) {
            $whereIssue .= " AND";
        } else {
            $whereIssue .= " WHERE";
            $whereUsed = true;
        }
        $whereIssue .= " helpDeskIssue.pupilsightPersonID=:helpDeskpupilsightPersonID";
    } else if ($filter == "My Working") {
        if ($whereUsed) {
            $whereIssue .= " AND";
        } else {
            $whereIssue .= " WHERE";
            $whereUsed = true;
        }
        $dataIssue["helpDeskTechnicianID"] = getTechnicianID($connection2, $_SESSION[$guid]["pupilsightPersonID"]);
        $dataIssue["status"] = 'Resolved';
        $whereIssue .= " helpDeskIssue.technicianID=:helpDeskTechnicianID AND NOT helpDeskIssue.status=:status";
    }

    if ($filter2 == "") {
        $filter2 = $statusFilters[0];
    }

    if ($filter2 == "Unassigned") {
        if ($whereUsed) {
            $whereIssue .= " AND";
        } else {
            $whereIssue .= " WHERE";
            $whereUsed = true;
        }
        $dataIssue["helpDeskStatus"] = 'Unassigned';
        $whereIssue .= " helpDeskIssue.status=:helpDeskStatus";
    } else if ($filter2 == "Pending") {
        if ($whereUsed) {
            $whereIssue .= " AND";
        } else {
            $whereIssue .= " WHERE";
            $whereUsed = true;
        }
        $dataIssue["helpDeskStatus"] = 'Pending';
        $whereIssue .= " helpDeskIssue.status=:helpDeskStatus";
    } else if ($filter2 == "Resolved") {
        if ($whereUsed) {
            $whereIssue .= " AND";
        } else {
            $whereIssue .= " WHERE";
            $whereUsed = true;
        }
        $dataIssue["helpDeskStatus"] = 'Resolved';
        $whereIssue .= " helpDeskIssue.status=:helpDeskStatus";
    } else if ($filter2 == "Unassigned and Pending") {
        if ($whereUsed) {
            $whereIssue .= " AND";
        } else {
            $whereIssue .= " WHERE";
            $whereUsed = true;
        }
        $dataIssue["helpDeskStatus1"] = 'Unassigned';
        $dataIssue["helpDeskStatus2"] = 'Pending';
        $whereIssue.= " (helpDeskIssue.status=:helpDeskStatus1 OR helpDeskIssue.status=:helpDeskStatus2)";
    } else if ($filter2 == "Pending and Resolved") {
        if ($whereUsed) {
            $whereIssue .= " AND";
        } else {
            $whereIssue .= " WHERE";
            $whereUsed = true;
        }
        $dataIssue["helpDeskStatus1"] = 'Pending';
        $dataIssue["helpDeskStatus2"] = 'Resolved';
        $whereIssue .= " (helpDeskIssue.status=:helpDeskStatus1 OR helpDeskIssue.status=:helpDeskStatus2)";
    }

    if ($filter3 == "") {
        $filter3 = $categoryFilters[0];
    }

    if ($filter3 != "All") {
        if ($whereUsed) {
            $whereIssue .= " AND";
        } else {
            $whereIssue .= " WHERE";
            $whereUsed = true;
        }
        $dataIssue["helpDeskCategory"] = $filter3;
        $whereIssue .= " helpDeskIssue.category=:helpDeskCategory";
    }

    if ($filter4 == "") {
        $filter4 = $priorityFilters[0];
    }

    if ($filter4 != "All") {
        if ($whereUsed) {
            $whereIssue .= " AND";
        } else {
            $whereIssue .= " WHERE";
            $whereUsed = true;
        }
        $dataIssue["helpDeskPriority"] = $filter4;
        $whereIssue .= " helpDeskIssue.priority=:helpDeskPriority";
    }
    
    if (intval($IDFilter)>0) {
        if ($whereUsed) {
            $whereIssue .= " AND";
        }
        else {
            $whereIssue .= " WHERE";
            $whereUsed = true;
        }
        $dataIssue["issueID"] = $IDFilter;
        $whereIssue .= " issueID=:issueID";
    }
    
    print "<form method='post' action='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=" . $_GET["q"] . "'>" ;
        print"<table class='table' cellspacing='0' style='width: 100%'>" ;
            if (count($issueFilters)>1) {
                print "<tr>";
                    print "<td> ";
                        print "<b>". __($guid, 'Issue Filter') ."</b><br/>";
                    print "</td>";
                    print "<td class=\"right\">";
                        print "<select name='filter' id='filter' style='width:302px'>" ;
                            foreach ($issueFilters as $option) {
                                $selected = "" ;
                                if ($option == $filter) {
                                    $selected = "selected" ;
                                }
                                print "<option $selected value='" . $option . "'>". $option ."</option>" ;
                            }
                        print "</select>" ;
                    print "</td>";
                print "</tr>";
            }
            print "<tr>";
                print "<td> ";
                    print "<b>".  __($guid, 'Status Filter') ."</b><br/>";
                    print "<span style=\"font-size: 90%\"><i></i></span>";
                print "</td>";
                print "<td class=\"right\">";
                    print "<select name='filter2' id='filter2' style='width:302px'>" ;
                        foreach ($statusFilters as $option) {
                            $selected = "" ;
                            if ($option == $filter2) {
                                $selected = "selected" ;
                            }
                            print "<option $selected value='" . $option . "'>". $option ."</option>" ;
                        }
                    print "</select>" ;
                print "</td>";
            print "</tr>";
            if (count($categoryFilters)>1) {
                print "<tr>";
                    print "<td> ";
                        print "<b>". __($guid, 'Category') ."</b><br/>";
                        print "<span style=\"font-size: 90%\"><i></i></span>";
                    print "</td>";
                    print "<td class=\"right\">";
                        print "<select name='filter3' id='filter3' style='width:302px'>" ;
                            foreach ($categoryFilters as $option) {
                                $selected = "" ;
                                if ($option == $filter3) {
                                    $selected = "selected" ;
                                }
                                print "<option $selected value='" . $option . "'>". $option ."</option>" ;
                            }
                        print "</select>" ;
                    print "</td>";
                print "</tr>";
            }
            if ($renderPriority) {
                print "<tr>";
                    print "<td> ";
                        print "<b>". $priorityName ."</b><br/>";
                        print "<span style=\"font-size: 90%\"><i></i></span>";
                    print "</td>";
                    print "<td class=\"right\">";
                        print "<select name='filter4' id='filter4' style='width:302px'>" ;
                            foreach ($priorityFilters as $option) {
                                $selected = "" ;
                                if ($option == $filter4) {
                                    $selected = "selected" ;
                                }
                                print "<option $selected value='" . $option . "'>". $option ."</option>" ;
                            }
                        print "</select>" ;
                    print "</td>";
                print "</tr>";
            }
            print "<tr>";
                print "<td> ";
                    print "<b>Issue ID Filter</b><br/>";
                    print "<span style=\"font-size: 90%\"><i></i></span>";
                print "</td>";
                print "<td class='right'>";
                    print "<input type='text' value='". $IDFilter . "' id='IDFilter' name='IDFilter' style='width: 300px'>";
                print "</td>";
            print "</tr>";
            print "<tr>";
                print "<td> ";
                    print "<b>Year Filter</b><br/>";
                    print "<span style=\"font-size: 90%\"><i></i></span>";
                print "</td>";
                print "<td class=\"right\">";
                    print "<select name='yearFilter' id='yearFilter' style='width:302px'>" ;
                        print "<option value='All Years'>All Years</option>" ;
                        try {
                            $dataYear = array();
                            $sqlYear = "SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear";
                            $resultYear = $connection2->prepare($sqlYear);
                            $resultYear->execute($dataYear);
                        } catch (PDOException $e) {
                        }

                        while($row = $resultYear->fetch()) {
                            $selected = "" ;
                            if ($row['pupilsightSchoolYearID'] == $yearFilter) {
                                $selected = "selected" ;
                            }
                            print "<option $selected value='" . $row['pupilsightSchoolYearID'] . "'>". $row['name'] ."</option>" ;
                        }
                    print "</select>" ;
                print "</td>";
            print "</tr>";
            print "<tr>" ;
                print "<td class='right' colspan=2>" ;
                    print "<input type='submit' value='" . __($guid, 'Go') . "'>" ;
                    ?>
                        <script type="text/javascript">
                            var IDFilter=new LiveValidation('IDFilter');
                            IDFilter.add(Validate.Numericality);
                        </script>
                    <?php
                print "</td>" ;
            print "</tr>" ;
        print"</table>" ;
    print "</form>" ;

    try {
        $sqlIssue = "SELECT helpDeskIssue.* FROM helpDeskIssue" . $whereYear . $whereIssue;
        $sqlIssue .= " UNION ";
        if ($whereYear != "" && $whereYear != null && $whereYear!="All Years") {
            $sqlIssue .= "SELECT helpDeskIssue.* FROM helpDeskIssue" . $whereYear . " AND helpDeskIssue.pupilsightPersonID=:helpDeskpupilsightPersonID";
        } else {
            $sqlIssue .= "SELECT helpDeskIssue.* FROM helpDeskIssue WHERE helpDeskIssue.pupilsightPersonID=:helpDeskpupilsightPersonID";
        }
        $sqlIssue .= " UNION ";
        if ($whereYear != "" && $whereYear != null && $whereYear!="All Years") {
            $sqlIssue .= "SELECT helpDeskIssue.* FROM helpDeskIssue" . $whereYear . " AND helpDeskIssue.privacySetting='Everyone'";
        } else {
            $sqlIssue .= "SELECT helpDeskIssue.* FROM helpDeskIssue WHERE helpDeskIssue.privacySetting='Everyone'";
        }
        $sqlIssue .= " ORDER BY FIELD(status, 'Unassigned', 'Pending', 'Resolved'), ";
        if ($renderPriority) {
            $sqlIssue .= "FIELD(priority";
            foreach ($priorityFilters as $priority) {
                $sqlIssue .= ", '" . $priority . "'";
            }
            $sqlIssue .= ", ''), ";
        }
        $sqlIssue .= "date DESC, issueID DESC;";
        $resultIssue = $connection2->prepare($sqlIssue);
        $resultIssue->execute($dataIssue);
    }
    catch (PDOException $e) {
    }

    print "<h3>" ;
        print __($guid, "Issues") ;
    print "</h3>" ;
    print "<div class='linkTop'>" ;
        // print "<a style='position:relative; bottom:10px;float:right;' >" .  __($guid, 'Create');
      //  print "<img style='margin-left: 2px' title=" . __($guid, 'Create ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/page_new.png'/></a>";
        echo "<div style='height:50px;'><div class='float-right mb-2'>";  
        echo "&nbsp;&nbsp;<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/issues_create.php' class='btn btn-primary'>Create</a></div><div class='float-none'></div></div>"; 
    print "</div>" ;
    print "<table class='table'>";
        print "<tr>";
            print "<th>ID</th>";
            print "<th>Title<br/>";
                print "<span style='font-size: 85%; font-style: italic'>" . __($guid, 'Description') . "</span>" ;
            print "</th>";
            print "<th>Owner";
                if ($renderCategory) {
                    print "<br/><span style='font-size: 85%; font-style: italic'>" . __($guid, 'Category') . "</span>";
                }
            print "</th>";
            if ($renderPriority) {
                print "<th>$priorityName</th>";
            }
            print "<th>Assigned Technician</th>";
            print "<th>Status<br/>";
                print "<span style='font-size: 85%; font-style: italic'>" . __($guid, 'Date') . "</span>";
            print "</th>";
            print "<th>Action</th>";
        print "</tr>";
        if ($resultIssue->rowCount()==0){
            print "<tr>";
                $colspan = 7;
                if (!$renderCategory) {
                    $colspan -= 1;
                }
                if (!$renderPriority) {
                    $colspan -= 1;
                }
                print "<td colspan=$colspan>";
                    print __($guid, "There are no records to display.");
                print "</td>";
            print "</tr>";
        } else {
            $nameLength = 15;
            $descriptionLength = 50;
            foreach ($resultIssue as $row){
                $person = getOwnerOfIssue($connection2, $row['issueID']);
                $class = "error";
                if ($row['status'] == 'Pending') {
                    $class = "warning";
                } else if ($row['status'] == 'Resolved') {
                    $class = "current";
                }
                try {
                    $data = array("issueID"=>$row["issueID"]); 
                    $sql = "SELECT privacySetting FROM helpDeskIssue WHERE issueID=:issueID" ;
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                }
                catch (PDOException $e) {
                }

                $row2 = $result->fetch() ;
                $privacySetting = $row2['privacySetting'];
                print "<tr class='$class'>";
                    print "<td style='text-align: center;'><b>" . intval($row['issueID']) . "</b></td>";
                    $issueName = $row['issueName'];
                    if (strlen($issueName)>$nameLength) {
                        $issueName = substr($issueName, 0, $nameLength) . "...";
                    }
                    print "<td><b>" .$issueName . "</b><br/>";
                    $descriptionText = strip_tags($row['description']);
                    if (strlen($descriptionText)>$descriptionLength) {
                        $descriptionText = substr($descriptionText, 0, $descriptionLength) . "...";
                    }
                    if ($row['status'] == "Resolved") {
                        if ($privacySetting == "Everyone") {
                            print "<span style='font-size: 85%; font-style: italic'>" . $descriptionText . "</span></td>" ;
                            $openCreated = true;
                        } else if ($privacySetting == "Related" && relatedToIssue($connection2, intval($row['issueID']), $_SESSION[$guid]["pupilsightPersonID"])) {
                            print "<span style='font-size: 85%; font-style: italic'>" . $descriptionText . "</span></td>" ;
                            $openCreated = true;
                        } else if ($privacySetting == "Owner" && isPersonsIssue($connection2, intval($row['issueID']), $_SESSION[$guid]["pupilsightPersonID"])) {
                            print "<span style='font-size: 85%; font-style: italic'>" . $descriptionText . "</span></td>" ;
                        } else if (getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "fullAccess")) {
                            print "<span style='font-size: 85%; font-style: italic'>" . $descriptionText . "</span></td>" ;
                        }
                    } else {
                        print "<span style='font-size: 85%; font-style: italic'>" . $descriptionText . "</span></td>" ;
                    }      
                    print "<td><b>" .formatName($person['title'],$person['preferredName'],$person['surname'], "Student", FALSE, FALSE) . "</b>";
                        if ($renderCategory) {
                            print "<br/><span style='font-size: 85%; font-style: italic'>" . $row['category'] . "</span>" ;
                        }
                    print "</td>";
                    if ($renderPriority) {
                        print "<td style='width: 8%'><b>" .$row['priority']. "</b></td>";
                    }
                    $technician = getTechWorkingOnIssue($connection2, $row['issueID']);
                    print "<td style='width: 15%'><b>" . $technician["preferredName"] . " " . $technician["surname"] . "</b></td>";
                    print "<td style='width: 10%'><b>" .$row['status']. "</b><br/>";
                        $date2 = dateConvertBack($guid, $row['date']);
                        if ($date2 == "30/11/-0001") {
                            $date2 = "No date";
                        }
                        print "<span style='font-size: 85%; font-style: italic'>" . $date2 . "</span>" ;
                    print "</td>";
                    print "<td style='width:17%'>";
                        $openCreated = false;
                        $resolveCreated = false; 
              
                        if (relatedToIssue($connection2, intval($row['issueID']), $_SESSION[$guid]["pupilsightPersonID"]) && !($row['status']=="Resolved")) {
                            if (!$openCreated) { 
                                print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/issues_discussView.php&issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Open ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/zoom.png'/></a>"; 
                                $openCreated = true;
                            }
                        }
              
                        if ($row['status'] == "Resolved") {
                            if ($privacySetting == "Everyone" && !$openCreated) {
                                print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/issues_discussView.php&issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Open ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/zoom.png'/></a>";
                                $openCreated = true;
                            } else if ($privacySetting == "Related" && relatedToIssue($connection2, intval($row['issueID']), $_SESSION[$guid]["pupilsightPersonID"]) && !$openCreated) {
                                print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/issues_discussView.php&issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Open ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/zoom.png'/></a>";
                                $openCreated = true;
                            } else if ($privacySetting == "Owner" && isPersonsIssue($connection2, intval($row['issueID']), $_SESSION[$guid]["pupilsightPersonID"]) && !$openCreated) {
                                print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/issues_discussView.php&issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Open ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/zoom.png'/></a>";
                                $openCreated = true;
                            }
                        }
              
                        if (isTechnician($connection2, $_SESSION[$guid]["pupilsightPersonID"]) || getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "fullAccess")) {
                            if ($row['technicianID'] == null && $row['status'] != "Resolved" ) {
                                if (!$openCreated) {
                                    print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/issues_discussView.php&issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Open ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/zoom.png'/></a>";
                                    $openCreated = true;
                                }
                            }
                        }     
              
                        if (getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "fullAccess")) {
                            if (!$openCreated && !($row['status'] == "Resolved")) { 
                                print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/issues_discussView.php&issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Open ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/zoom.png'/></a>"; 
                                $openCreated = true;
                            }
                        }
              
                        if (isPersonsIssue($connection2, $row["issueID"], $_SESSION[$guid]["pupilsightPersonID"]) || getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "fullAccess")) {
                            print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/issues_discussEdit.php&issueID=". $row['issueID'] ."&returnAddress=issues_view.php'><img title=" . __($guid, 'Edit ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/config.png'/></a>";
                        }
              
                        if (isTechnician($connection2, $_SESSION[$guid]["pupilsightPersonID"]) || getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "fullAccess")) {
                            if ($row['technicianID'] == null && $row['status'] != "Resolved" ) {
                                print "<input type='hidden' name='address' value='". $_SESSION[$guid]["address"] . "'>";
                                if (getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "acceptIssue") && !isPersonsIssue($connection2, intval($row['issueID']), $_SESSION[$guid]["pupilsightPersonID"])) {
                                    print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/issues_acceptProcess.php?issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Accept ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/page_new.png'/></a>";
                                }
                            }
                        } 
                          
                        //Not Resolved
                        if ($row['status'] != "Resolved") {
                            if ($row['technicianID'] == null && getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "assignIssue")) {
                                print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/issues_assign.php&issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Assign ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/attendance.png'/></a>";
                            } else if (getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "reassignIssue")) {
                                print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/issues_assign.php&issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Reassign ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/attendance.png'/></a>";
                            }
                        }
              
                        if (relatedToIssue($connection2, intval($row['issueID']), $_SESSION[$guid]["pupilsightPersonID"]) && !($row['status'] == "Resolved")) {
                            if (getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "resolveIssue") && $row['status'] == "Pending") { 
                                print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/issues_resolveProcess.php?issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Resolve ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/iconTick.png'/></a>";
                                $resolveCreated = true;
                            }
                        }
              
                        //Full Access
                        if (getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "fullAccess")) {
                            if (!$resolveCreated && $row['status'] == "Pending") { 
                                print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/issues_resolveProcess.php?issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Resolve ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/iconTick.png'/></a>"; 
                                $resolveCreated = true;
                            }
                        }
              
                        //Resolved
                        if ($row['status'] == "Resolved") {
                            if (getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "reincarnateIssue") || isPersonsIssue($connection2, intval($row['issueID']), $_SESSION[$guid]["pupilsightPersonID"])) {
                                print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/issues_reincarnateProcess.php?issueID=". $row["issueID"] . "'><img style='margin-left: 5px' title=" . __($guid, 'Reincarnate ') . "' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/" . $_SESSION[$guid]["pupilsightThemeName"] . "/img/reincarnate.png'/></a>";
                            }
                        }              
                    print "</td>";
                print "</tr>";
            }
        }
    print "</table>";
}
?>
