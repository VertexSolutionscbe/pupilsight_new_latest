<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/bus_manage_delete.php&id='.$id;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/bus_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Transport/bus_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM trans_bus_details WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);

           
        } catch (PDOException $e) {
            $URLDelete .= '&return=error2';
            header("Location: {$URLDelete}");
            exit();
        }

       
       
        if ($result->rowCount() != 1 ) {
           $URLDelete .= '&return=error3';
            header("Location: {$URLDelete}");
        } else {
            //Write to database
            try {
                $data = array('id' => $id);

                $sql1 = 'SELECT id FROM trans_routes WHERE bus_id=:id';
                $result1 = $connection2->prepare($sql1);
                $result1->execute($data);

                $bus_routes = $result1->fetchAll();
                //print_r($bus_routes);
                if($result1->rowCount() > 0) {
                    for ($i = 0; $i < count($bus_routes); $i++) {
                        $p = $bus_routes[$i];
                        $pp = $p['id'];
                        //print_r($p);
                        //print_r($pp);
                        $data1 = array('rid' => $pp);
                        $sql = 'SELECT id FROM trans_route_price WHERE route_id=:rid';
                        $resultrp = $connection2->prepare($sql);
                        $resultrp->execute($data1);
                        $bus_routes_price = $result->fetchAll();
                        if($resultrp->rowCount() > 0) {
                            for ($ii = 0; $ii < count($bus_routes_price); $ii++) {
                                $a = $bus_routes_price[$ii];
                                $aa = $a['id'];
                                //print_r($a);
                                //print_r($aa);
                                $data2 = array('rpid' => $aa);
                                $sqlprice = 'DELETE FROM trans_route_price WHERE id=:rpid';
                        $resultprice = $connection2->prepare($sqlprice);
                        $resultprice->execute($data2);
                            }
                        }

                        $sqlrouteassign = 'SELECT id FROM trans_route_assign WHERE route_id=:rid';
                        $resultassign = $connection2->prepare($sqlrouteassign);
                        $resultassign->execute($data1);
                        $bus_routes_assign = $resultassign->fetchAll();
                        if($resultassign->rowCount() > 0) {
                            for ($iii = 0; $iii < count($bus_routes_assign); $iii++) {
                                $b = $bus_routes_assign[$iii];
                                $bb = $b['id'];
                                //print_r($b);
                                //print_r($bb);
                                $data3 = array('rasid' => $bb);
                                $sql10 = 'DELETE FROM trans_route_assign WHERE id=:rasid';
                                $result10 = $connection2->prepare($sql10);
                                $result10->execute($data3);
                            }
                        }


                    }

                    $sql3 = 'DELETE FROM trans_routes WHERE bus_id=:id';
                    $result3 = $connection2->prepare($sql3);
                    $result3->execute($data);

                    $sql4 = 'DELETE FROM trans_route_stops WHERE bus_id=:id';
                    $result4 = $connection2->prepare($sql4);
                    $result4->execute($data);
                }
                //die();
                $sql5 = 'DELETE FROM trans_bus_details WHERE id=:id';
                $result5 = $connection2->prepare($sql5);
                $result5->execute($data);

            } catch (PDOException $e) {
                $URLDelete .= '&return=error2';
                header("Location: {$URLDelete}");
                exit();
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
