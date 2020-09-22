<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Get URL from calling page, and set returning URL
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/System Admin/theme_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/theme_manage_install.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $themeName = null;
    if (isset($_GET['name'])) {
        $themeName = $_GET['name'];
    }

    if ($themeName == null or $themeName == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        if (!(include $_SESSION[$guid]['absolutePath']."/themes/$themeName/manifest.php")) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            if ($name == '' or $description == '' or $version == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Check for existence of theme
                try {
                    $dataModule = array('name' => $name);
                    $sqlModule = 'SELECT * FROM pupilsightTheme WHERE name=:name';
                    $resultModule = $connection2->prepare($sqlModule);
                    $resultModule->execute($dataModule);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($resultModule->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Insert new theme row
                    try {
                        $dataModule = array('name' => $name, 'description' => $description, 'version' => $version, 'author' => $author, 'url' => $url);
                        $sqlModule = "INSERT INTO pupilsightTheme SET name=:name, description=:description, active='N', version=:version, author=:author, url=:url";
                        $resultModule = $connection2->prepare($sqlModule);
                        $resultModule->execute($dataModule);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success1';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
