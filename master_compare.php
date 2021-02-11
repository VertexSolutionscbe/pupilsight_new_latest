<?php
//die();
//main master_db connection
$databaseServer = '192.168.1.5';
$databaseUsername = 'dbimport';
$databasePassword = '5dvd#NvdnRDVP#48mmbs';
$databaseName = 'master_db';
$msg = "";
$currentDB = "";

if (isset($_POST['db'])) {
    $currentDB = $_POST['db'];
}
?>
<html>

<body style="margin:16px;">
    <div style='text-align:center;font-size:18px;'><?= $msg; ?></div>
    <table style="width:100%;" border="1" cellspacing="0" cellpadding="2">
        <tr>
            <td>SRNO</td>
            <td>Master Tables</td>
            <td>Column Count</td>
            <td>
                <form id="masterForm" name="master_compare" method="post" action="">
                    <select name="db" onchange="submitForm();">
                        <option value="">Select DB</option>
                        <?php
                        try {
                            $conn = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);

                            // Check connection
                            if ($conn->connect_errno) {
                                echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                                exit();
                            }

                            //$sql = "select id, server_name, db_name, user_name, password from db_list";
                            $sql = "select id, server_name, db_name, user_name, password from db_list order by db_name asc";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                // output data of each row
                                while ($row = $result->fetch_assoc()) {

                                    if ($row["db_name"] == "mastercopy_erp") {
                                        $mdatabaseServer = $row["server_name"];
                                        $mdatabaseUsername = $row["user_name"];
                                        $mdatabasePassword = $row["password"];
                                        $mdatabaseName = $row["db_name"];
                                    }
                                    if ($row["id"] == $currentDB) {
                                        $cdatabaseServer = $row["server_name"];
                                        $cdatabaseUsername = $row["user_name"];
                                        $cdatabasePassword = $row["password"];
                                        $cdatabaseName = $row["db_name"];
                                        echo "\n<option value='" . $row["id"] . "' selected>" . trim($row["db_name"]) . "</option>";
                                    } else {
                                        echo "\n<option value='" . $row["id"] . "'>" . trim($row["db_name"]) . "</option>";
                                    }
                                }
                            } else {
                                echo "No results";
                            }
                            $conn->close();
                        } catch (Exception $ex) {
                            print_r($ex);
                        }
                        ?>
                    </select>
                </form>
            </td>
            <td>Column Count</td>
        </tr>

        <?php
        if ($_POST['db']) {
            function getDBTableAndCol($mdbServer, $mdbUsername, $mdbPassword, $mdbName, $selectdb)
            {
                $master = array();
                try {
                    $conn1 = new mysqli($mdbServer, $mdbUsername, $mdbPassword, $mdbName);
                    //echo "\n<br>server: " . $mdbServer . " user: " . $mdbUsername . " pass: " . $mdbPassword . " dbname " . $mdbName;
                    if ($conn1->connect_errno) {
                        echo "Failed to connect to MySQL: " . $conn1->connect_error;
                    }
                    $sql = "SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $selectdb . "'";
                    $result = $conn1->query($sql);

                    if ($result->num_rows > 0) {
                        // output data of each row
                        //print_r($result);
                        $mc = 0;
                        while ($row = $result->fetch_assoc()) {
                            $conn2 = new mysqli($mdbServer, $mdbUsername, $mdbPassword, $mdbName);
                            $sq = "SELECT count(column_name) as cols FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '" . $selectdb  . "' AND table_name = '" . $row["table_name"] . "'";

                            $res = $conn2->query($sq);
                            $cols = 0;
                            if ($res->num_rows > 0) {
                                while ($row1 = $res->fetch_assoc()) {
                                    $cols = $row1["cols"];
                                }
                            }
                            $tm = array("table" => $row["table_name"], "cols" => $cols);
                            $master[$mc] = $tm;
                            $mc++;
                            $conn2->close();
                        }
                    } else {
                        echo "No results";
                    }
                    $conn1->close();
                } catch (Exception $ex) {
                    print_r($ex);
                }
                return $master;
            }

            $mres = getDBTableAndCol($databaseServer, $databaseUsername, $databasePassword, $databaseName, $mdatabaseName);
            $cres = getDBTableAndCol($databaseServer, $databaseUsername, $databasePassword, $databaseName, $cdatabaseName);
            $len = count($mres);
            //echo "len : " . $len;
            $i = 0;
            $cnt = 1;
            while ($i < $len) {
                $st = $mres[$i];
                $bgcolor = "";
                if ($mres[$i]["cols"] != $cres[$i]["cols"]) {
                    $bgcolor = "style='background-color:red;'";
                }
                echo "\n<tr " . $bgcolor . ">";
                echo "\n<td>" . $cnt . "</td>";
                echo "\n<td>" . $mres[$i]["table"] . "</td>";
                echo "\n<td>" . $mres[$i]["cols"] . "</td>";
                echo "\n<td>" . $cres[$i]["table"] . "</td>";
                echo "\n<td>" . $cres[$i]["cols"] . "</td>";
                echo "\n</tr>";
                $i++;
                $cnt++;
            }
        }
        ?>

    </table>

    </form>
    <script>
        function submitForm() {
            document.getElementById("masterForm").submit();
        }
    </script>
</body>

</html>