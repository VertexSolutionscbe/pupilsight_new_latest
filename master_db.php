<?php
//die();
//main master_db connection
$databaseServer = '127.0.0.1';
$databaseUsername = 'dev_user';
$databasePassword = '5dvd#NvdnRDVP#48mmbs';
$databaseName = 'master_db';
$msg = "";
if ($_POST['db']) {
    $dbid = $_POST['db'];
    $sq = $_POST['query'];
    if ($dbid) {
        //print_r($dbid);
        $len = count($dbid);
        //echo "len " . $len;
        $i = 0;
        $conn = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);
        while ($i < $len) {
            try {
                $sql = "select id, server_name, db_name, user_name, password from db_list where id='" . $dbid[$i] . "'";
                //echo "sql: " . $sql;
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    //echo "result ";
                    //print_r($result);
                    while ($row = $result->fetch_assoc()) {
                        //echo "<tr><td><input type='checkbox' id='db_" . $row["id"] . "' name='db[]' value='" . $row["id"] . "'><label for='db_" . $row["id"] . "'>" . $row["db_name"] . "</label></td></tr>";
                        $con = new mysqli($row["server_name"], $row["user_name"], $row["password"], $row["db_name"]);
                    }
                    //print_r($row);


                    //print_r($con);
                    //print_r($sq);

                    $con->query($sq);
                    $con->close();
                } else {
                    echo "0 results";
                }
            } catch (Exception $ex) {
                print_r($ex);
            }
            $i++;
        }
        $conn->close();
        $msg = "Query executed successfully.";
    }
}
?>
<html>

<body style="margin:16px;">
    <form name="master_db" method="post" action="">
        <div style='text-align:center;font-size:18px;'><?= $msg; ?></div>
        <table style="width:100%;border:1px;">
            <tr>
                <td>Query Window</td>
            </tr>
            <tr>
                <td><textarea name="query" style='width:100%;height:200px;' required></textarea></td>
            </tr>
            <tr>
                <td>Select one or multiple db to execute query</td>
            </tr>
            <tr>
                <td><input type='checkbox' id='selectall' onchange="checkAll(this)"><label for='selectall'>Select All</label></td>
            </tr>

            <?php


            $conn = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);

            // Check connection
            if ($mysqli->connect_errno) {
                echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                exit();
            }

            //$sql = "select id, server_name, db_name, user_name, password from db_list";
            $sql = "select id, db_name from db_list";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td><input type='checkbox' id='db_" . $row["id"] . "' name='db[]' value='" . $row["id"] . "'><label for='db_" . $row["id"] . "'>" . $row["db_name"] . "</label></td></tr>";
                }
            } else {
                echo "No results";
            }
            $conn->close();
            ?>

            <tr>
                <td><button name="submit" type="submit">Submit</button></td>
            </tr>
            <script>
                function checkAll(ele) {
                    var checkboxes = document.getElementsByTagName('input');
                    if (ele.checked) {
                        for (var i = 0; i < checkboxes.length; i++) {
                            if (checkboxes[i].type == 'checkbox') {
                                checkboxes[i].checked = true;
                            }
                        }
                    } else {
                        for (var i = 0; i < checkboxes.length; i++) {
                            console.log(i)
                            if (checkboxes[i].type == 'checkbox') {
                                checkboxes[i].checked = false;
                            }
                        }
                    }
                }
            </script>
        </table>

    </form>
</body>

</html>