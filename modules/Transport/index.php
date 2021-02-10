<?php
/*
Gibbon, Flexible & Open School System
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

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isModuleAccessible($guid, $connection2) == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs->add(__('Transport Page'));

    // This is where you can start writing code for your module.
    // See the developer docs for more info: https://docs.gibbonedu.org/developers/

    $sql = "SELECT * FROM trans_bus_details";
    $result = $connection2->query($sql);
    $rowdatabus = $result->fetchAll();
    $totalbuses=$result->rowCount();
    $bi = 1;

    $sqlroutes = "SELECT trans_routes.*,trans_bus_details.name as busname,COUNT(trans_route_stops.id) as totalstops from trans_routes 
    LEFT JOIN trans_bus_details ON (trans_routes.bus_id=trans_bus_details.id)
    LEFT JOIN trans_route_stops ON (trans_routes.id=trans_route_stops.route_id)
    GROUP BY trans_routes.id";

    $resultroutes = $connection2->query($sqlroutes);
    $rowroutes = $resultroutes->fetchAll();
    $totalroutes=$resultroutes->rowCount();
    $ri = 1;

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqlstudents = "SELECT trans_route_assign.*,pupilsightPerson.pupilsightPersonID AS stuid,pupilsightPerson.officialName AS student_name,pupilsightRollGroup.name AS section,pupilsightYearGroup.name AS class,
                trans_route_stops.stop_name,trans_routes.id as routeid,trans_routes.route_name,pupilsightSchoolYear.name as academic_year,trans_bus_details.name as bus_name,pupilsightRole.category from trans_route_assign
        left Join pupilsightPerson on (trans_route_assign.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
        left Join pupilsightStudentEnrolment on (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
        left Join pupilsightSchoolYear on (pupilsightStudentEnrolment.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID)
        left Join pupilsightRollGroup on (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
        left Join pupilsightYearGroup on (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)
        left Join trans_route_stops on (trans_route_assign.route_stop_id=trans_route_stops.id)
        left Join trans_routes on (trans_route_assign.route_id=trans_routes.id)
        left Join trans_bus_details on (trans_routes.bus_id=trans_bus_details.id)
        left Join pupilsightRole on (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID)
        where pupilsightPerson.canLogin = 'Y' 
        AND trans_route_assign.pupilsightSchoolYearID = '$pupilsightSchoolYearID'
        GROUP BY pupilsightPerson.pupilsightPersonID
        ";

    $resultstudents = $connection2->query($sqlstudents);
    $rowstudents = $resultstudents->fetchAll();
    $totalmembers=$resultstudents->rowCount();
    $si = 1;
    print "<div class='row'>";
    print "<div class='col-md-4 p-1'>";
    print "<div class='card' style='box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);'><span style='text-align: center'>Total no of Buses : $totalbuses<br><a href='#item1'>View</a></span></div>";
    print "</div>";
    print "<div class='col-md-4 p-1'>";
    print "<div class='card' style='box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);'><span style='text-align: center'>Total no of Routes : $totalroutes<br><a href='#item2'>View</a></span></div>";
    print "</div>";
    print "<div class='col-md-4 p-1'>";
    print "<div class='card' style='box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);'><span style='text-align: center'>Total no of Members : $totalmembers<br><a href='#item3'>View</a></span></div>";
    print "</div>";
    print "</div>";


    print "<h3 id='item1'>Bus Data</h3>";
    print"<table class='table' cellspacing='0' style='width: 100%'>";
    print "<tr>";
    print "<th>Sl No</th>";
    print "<th>Vehicle Number</th>";
    print "<th>Vehicle Name</th>";
    print "</tr>";
    foreach ($rowdatabus as $busdata) {
        print "<tr>";
        print "<td> " . $bi . "</td>";
        print "<td> " . $busdata['vehicle_number'] . "</td>";
        print "<td> " . $busdata['name'] . "</td>";
        print "</tr>";
        $bi++;
    }

    print "</table>";



    print "<h3 id='item2'>Route Data</h3>";
    print"<table class='table' cellspacing='0' style='width: 100%'>";
    print "<tr>";
    print "<th>Sl No</th>";
    print "<th>Route Name</th>";
    print "<th>Bus Name</th>";
    print "<th>No Of Stops</th>";
    print "</tr>";
    foreach ($rowroutes as $routedata) {
        print "<tr>";
        print "<td> " . $ri . "</td>";
        print "<td> " . $routedata['route_name'] . "</td>";
        print "<td> " . $routedata['busname'] . "</td>";
        print "<td> " . $routedata['totalstops'] . "</td>";
        print "</tr>";
        $ri++;
    }

    print "</table>";


    print "<h3 id='item3'>Members in Route</h3>";
    print"<table class='table' cellspacing='0' style='width: 100%'>";
    print "<tr>";
    print "<th>Sl No</th>";
    print "<th>Student Name</th>";
    print "<th>Stop Name</th>";
    print "<th>Bus Name</th>";
    print "</tr>";

    foreach ($rowstudents as $assigndata) {
        print "<tr>";
        print "<td> " . $si . "</td>";
        print "<td> " . $assigndata['student_name'] . "</td>";
        print "<td> " . $assigndata['stop_name'] . "</td>";
        print "<td> " . $assigndata['bus_name'] . "</td>";
        print "</tr>";
        $si++;
    }

    print "</table>";
}