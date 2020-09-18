<?php
$date = date('Y-m-d H:i:s');
echo $date;
$commoadPath = "lowriter --convert-to pdf ".$_SERVER['DOCUMENT_ROOT']."/thirdparty/phpword/templates/refund_receipt.docx";
echo "\n<br>\n".$commoadPath;

echo "\n";
$command = escapeshellcmd($commoadPath);
$highlight = shell_exec($command);
print_r($highlight);

