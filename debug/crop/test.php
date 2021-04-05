<?php
function getDomain()
{
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}
$baseurl = getDomain().'/pupilsight_new';
//$baseurl = getDomain();

?>
<html>

<head>
    <script src="<?= $baseurl; ?>/assets/libs/jquery/jquery.js"></script>
    <style>
        .hide {
            display: none;
            visibility: hidden;
        }

        .cropImgPanel {
            background-color: #f3f3f3;
            cursor: pointer;
            height: 50px;
            margin: 10px;
            width: 200px;
            line-height: 50px;
            text-align: center;
        }
    </style>
</head>

<body>

<?php
    if(isset($_POST['submit']))
    {
        //echo 's';exit;
        define('UPLOAD_DIR', 'img');

        //echo '<pre>';print_r($_POST['porpin'][0]);exit;
        $img_str=$_POST['porpin'][0];
        $image_parts = explode(";base64,", $img_str);
        //print_r($image_parts);exit;
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = UPLOAD_DIR . uniqid() . '.jpg';
        file_put_contents($file, $image_base64);
    }
?>    

    <form method="post">
        <?php
        $i   = 0;
        $len = 1;
        while ($i < $len) {
        ?>

            <div id='prophoto_pr<?= $i ?>' class="cropImgPanel" onclick="editPhoto_pr<?= $i ?>();">Upload Image </div>
            <input type="hidden" id="porpin_pr<?= $i ?>" name="porpin[]" value="">
        <?php

            $vwidth = 200;
            $vheight = 258;
            $bwidth = 250;
            $bheight = 308;
            $owidth = 775;
            $oheight = 1000;
            $suffix = '_pr' . $i;
            //isfilebtn= FALSE;
            if ($i == 0) {
                $isimportreq = true;
            } else {
                $isimportreq = false;
            }

            //echo view('partials/croppie', $dt);
            //include $_SERVER['DOCUMENT_ROOT'] . '/debug/crop/croplib.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/pupilsight_new/debug/crop/croplib.php';
            $i++;
        }

        

        ?>
        <input type="submit" name="submit" value="submit" />
    </form>
</body>

</html>