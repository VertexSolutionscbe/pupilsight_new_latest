<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Get URL from calling page, and set returning URL
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/System Admin/module_manage.php';
$_SESSION[$guid]['moduleInstallError'] = '';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/module_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $moduleName = null;
    if (isset($_GET['name'])) {
        $moduleName = $_GET['name'];
    }

    if ($moduleName == null or $moduleName == '') {
        $URL .= '&return=error5';
        header("Location: {$URL}");
    } else {
        if (!(include $_SESSION[$guid]['absolutePath']."/modules/$moduleName/manifest.php")) {
            $URL .= '&return=error5';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            if ($name == '' or $description == '' or $type == '' or $type != 'Additional' or $version == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Lock module table
                try {
                    $sql = 'LOCK TABLES pupilsightModule WRITE';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Check for existence of module
                try {
                    $dataModule = array('name' => $name);
                    $sqlModule = 'SELECT * FROM pupilsightModule WHERE name=:name';
                    $resultModule = $connection2->prepare($sqlModule);
                    $resultModule->execute($dataModule);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($resultModule->rowCount() > 0) {
                    $URL .= '&return=error6';
                    header("Location: {$URL}");
                } else {
                    //Insert new module row
                    try {
                        $dataModule = array('name' => $name, 'description' => $description, 'entryURL' => $entryURL, 'type' => $type, 'category' => $category, 'version' => $version, 'author' => $author, 'url' => $url);
                        $sqlModule = "INSERT INTO pupilsightModule SET name=:name, description=:description, entryURL=:entryURL, type=:type, category=:category, active='N', version=:version, author=:author, url=:url";
                        $resultModule = $connection2->prepare($sqlModule);
                        $resultModule->execute($dataModule);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $pupilsightModuleID = $connection2->lastInsertID();

                    //Unlock module table
                    try {
                        $sql = 'UNLOCK TABLES';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                        $URL .= '&return=warning1';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Create module tables
                    //Whilst this area is intended for use setting up module tables, arbitrary sql can be run at the wish of the module developer. However, such actions are not cleaned up by the uninstaller.
                    $partialFail = false;
                    if (isset($moduleTables)) {
                        for ($i = 0;$i < count($moduleTables);++$i) {
                            try {
                                $sql = $moduleTables[$i];
                                $result = $connection2->query($sql);
                            } catch (PDOException $e) {
                                $_SESSION[$guid]['moduleInstallError'] .= htmlPrep($sql).'<br/><b>'.$e->getMessage().'</b><br/><br/>';
                                $partialFail = true;
                            }
                        }
                    }
                    //Create pupilsightSetting entries
                    //Whilst this area is intended for use setting up pupilsightSetting entries, arbitrary sql can be run at the wish of the module developer. However, such actions are not cleaned up by the uninstaller.
                    $partialFail = false;
                    if (isset($pupilsightSetting)) {
                        for ($i = 0;$i < count($pupilsightSetting);++$i) {
                            try {
                                $sql = $pupilsightSetting[$i];
                                $result = $connection2->query($sql);
                            } catch (PDOException $e) {
                                $_SESSION[$guid]['moduleInstallError'] .= htmlPrep($sql).'<br/><b>'.$e->getMessage().'</b><br/><br/>';
                                $partialFail = true;
                            }
                        }
                    }
                    //Create module actions
                    if (is_null(@$actionRows) == false) {
                        for ($i = 0;$i < count($actionRows);++$i) {
                            $categoryPermissionStaff = 'Y';
                            $categoryPermissionStudent = 'Y';
                            $categoryPermissionParent = 'Y';
                            $categoryPermissionOther = 'Y';
                            if (isset($actionRows[$i]['categoryPermissionStaff'])) {
                                if ($actionRows[$i]['categoryPermissionStaff'] == 'N') {
                                    $categoryPermissionStaff = 'N';
                                }
                            }
                            if (isset($actionRows[$i]['categoryPermissionStudent'])) {
                                if ($actionRows[$i]['categoryPermissionStudent'] == 'N') {
                                    $categoryPermissionStudent = 'N';
                                }
                            }
                            if (isset($actionRows[$i]['categoryPermissionParent'])) {
                                if ($actionRows[$i]['categoryPermissionParent'] == 'N') {
                                    $categoryPermissionParent = 'N';
                                }
                            }
                            if (isset($actionRows[$i]['categoryPermissionOther'])) {
                                if ($actionRows[$i]['categoryPermissionOther'] == 'N') {
                                    $categoryPermissionOther = 'N';
                                }
                            }
                            $entrySidebar = 'Y';
                            if (isset($actionRows[$i]['entrySidebar'])) {
                                if ($actionRows[$i]['entrySidebar'] == 'N') {
                                    $entrySidebar = 'N';
                                }
                            }
                            $menuShow = 'Y';
                            if (isset($actionRows[$i]['menuShow'])) {
                                if ($actionRows[$i]['menuShow'] == 'N') {
                                    $menuShow = 'N';
                                }
                            }

                            try {
                                $dataModule = array('pupilsightModuleID' => $pupilsightModuleID, 'name' => $actionRows[$i]['name'], 'precedence' => $actionRows[$i]['precedence'], 'category' => $actionRows[$i]['category'], 'description' => $actionRows[$i]['description'], 'URLList' => $actionRows[$i]['URLList'], 'entryURL' => $actionRows[$i]['entryURL'], 'entrySidebar' => $entrySidebar, 'menuShow' => $menuShow, 'defaultPermissionAdmin' => $actionRows[$i]['defaultPermissionAdmin'], 'defaultPermissionTeacher' => $actionRows[$i]['defaultPermissionTeacher'], 'defaultPermissionStudent' => $actionRows[$i]['defaultPermissionStudent'], 'defaultPermissionParent' => $actionRows[$i]['defaultPermissionParent'], 'defaultPermissionSupport' => $actionRows[$i]['defaultPermissionSupport'], 'categoryPermissionStaff' => $categoryPermissionStaff, 'categoryPermissionStudent' => $categoryPermissionStudent, 'categoryPermissionParent' => $categoryPermissionParent, 'categoryPermissionOther' => $categoryPermissionOther);
                                $sqlModule = 'INSERT INTO pupilsightAction SET pupilsightModuleID=:pupilsightModuleID, name=:name, precedence=:precedence, category=:category, description=:description, URLList=:URLList, entryURL=:entryURL, entrySidebar=:entrySidebar, menuShow=:menuShow, defaultPermissionAdmin=:defaultPermissionAdmin, defaultPermissionTeacher=:defaultPermissionTeacher, defaultPermissionStudent=:defaultPermissionStudent, defaultPermissionParent=:defaultPermissionParent, defaultPermissionSupport=:defaultPermissionSupport, categoryPermissionStaff=:categoryPermissionStaff, categoryPermissionStudent=:categoryPermissionStudent, categoryPermissionParent=:categoryPermissionParent, categoryPermissionOther=:categoryPermissionOther';
                                $resultModule = $connection2->prepare($sqlModule);
                                $resultModule->execute($dataModule);
                            } catch (PDOException $e) {
                                $_SESSION[$guid]['moduleInstallError'] .= $sqlModule.'<br/><b>'.$e->getMessage().'</b></br><br/>';
                                $partialFail = true;
                            }
                        }
                    }

                    try {
                        $dataActions = array('pupilsightModuleID' => $pupilsightModuleID);
                        $sqlActions = 'SELECT * FROM pupilsightAction WHERE pupilsightModuleID=:pupilsightModuleID';
                        $resultActions = $connection2->prepare($sqlActions);
                        $resultActions->execute($dataActions);
                    } catch (PDOException $e) {
                        $URL .= '&return=warning1';
                        header("Location: {$URL}");
                        exit();
                    }

                    while ($rowActions = $resultActions->fetch()) {
                        if ($rowActions['defaultPermissionAdmin'] == 'Y') {
                            try {
                                $dataPermissions = array('pupilsightActionID' => $rowActions['pupilsightActionID']);
                                $sqlPermissions = 'INSERT INTO pupilsightPermission SET pupilsightActionID=:pupilsightActionID, pupilsightRoleID=001';
                                $resultPermissions = $connection2->prepare($sqlPermissions);
                                $resultPermissions->execute($dataPermissions);
                            } catch (PDOException $e) {
                                $_SESSION[$guid]['moduleInstallError'] .= $sqlPermissions.'<br/><b>'.$e->getMessage().'</b></br><br/>';
                                $partialFail = true;
                            }
                        }
                        if ($rowActions['defaultPermissionTeacher'] == 'Y') {
                            try {
                                $dataPermissions = array('pupilsightActionID' => $rowActions['pupilsightActionID']);
                                $sqlPermissions = 'INSERT INTO pupilsightPermission SET pupilsightActionID=:pupilsightActionID, pupilsightRoleID=002';
                                $resultPermissions = $connection2->prepare($sqlPermissions);
                                $resultPermissions->execute($dataPermissions);
                            } catch (PDOException $e) {
                                $_SESSION[$guid]['moduleInstallError'] .= $sqlPermissions.'<br/><b>'.$e->getMessage().'</b></br><br/>';
                                $partialFail = true;
                            }
                        }
                        if ($rowActions['defaultPermissionStudent'] == 'Y') {
                            try {
                                $dataPermissions = array('pupilsightActionID' => $rowActions['pupilsightActionID']);
                                $sqlPermissions = 'INSERT INTO pupilsightPermission SET pupilsightActionID=:pupilsightActionID, pupilsightRoleID=003';
                                $resultPermissions = $connection2->prepare($sqlPermissions);
                                $resultPermissions->execute($dataPermissions);
                            } catch (PDOException $e) {
                                $_SESSION[$guid]['moduleInstallError'] .= $sqlPermissions.'<br/><b>'.$e->getMessage().'</b></br><br/>';
                                $partialFail = true;
                            }
                        }
                        if ($rowActions['defaultPermissionParent'] == 'Y') {
                            try {
                                $dataPermissions = array('pupilsightActionID' => $rowActions['pupilsightActionID']);
                                $sqlPermissions = 'INSERT INTO pupilsightPermission SET pupilsightActionID=:pupilsightActionID, pupilsightRoleID=004';
                                $resultPermissions = $connection2->prepare($sqlPermissions);
                                $resultPermissions->execute($dataPermissions);
                            } catch (PDOException $e) {
                                $_SESSION[$guid]['moduleInstallError'] .= $sqlPermissions.'<br/><b>'.$e->getMessage().'</b></br><br/>';
                                $partialFail = true;
                            }
                        }
                        if ($rowActions['defaultPermissionSupport'] == 'Y') {
                            try {
                                $dataPermissions = array('pupilsightActionID' => $rowActions['pupilsightActionID']);
                                $sqlPermissions = 'INSERT INTO pupilsightPermission SET pupilsightActionID=:pupilsightActionID, pupilsightRoleID=006';
                                $resultPermissions = $connection2->prepare($sqlPermissions);
                                $resultPermissions->execute($dataPermissions);
                            } catch (PDOException $e) {
                                $_SESSION[$guid]['moduleInstallError'] .= $sqlPermissions.'<br/><b>'.$e->getMessage().'</b></br><br/>';
                                $partialFail = true;
                            }
                        }
                    }

                    //Create hook entries
                    if (isset($hooks)) {
                        for ($i = 0;$i < count($hooks);++$i) {
                            try {
                                $sql = $hooks[$i];
                                $result = $connection2->query($sql);
                            } catch (PDOException $e) {
                                $_SESSION[$guid]['moduleInstallError'] .= htmlPrep($sql).'<br/><b>'.$e->getMessage().'</b><br/><br/>';
                                $partialFail = true;
                            }
                        }
                    }

                    //The reckoning!
                    if ($partialFail == true) {
                        $URL .= '&return=warning1';
                        header("Location: {$URL}");
                    } else {
                        //Set module to active
                        try {
                            $data = array('pupilsightModuleID' => $pupilsightModuleID);
                            $sql = "UPDATE pupilsightModule SET active='Y' WHERE pupilsightModuleID=:pupilsightModuleID";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=warning2';
                            header("Location: {$URL}");
                            exit();
                        }

                        // Clear the main menu from session cache
                        $pupilsight->session->forget('menuMainItems');

                        //We made it!
                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}
