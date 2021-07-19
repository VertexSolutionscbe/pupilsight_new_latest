<?php
include 'core.php';
//ad post paramater 1 extra as link or url call for service layer
$parm = $_POST;
$link = $parm["link"];
if (strpos($link, 'http') === false) {
    $link = getDomain() . "/" . $link;
}
//echo $link;
unset($parm["link"]);
print_r($parm);
curl_post_nowait($link, $parm);
echo "success";
