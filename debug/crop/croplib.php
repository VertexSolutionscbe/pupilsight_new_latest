<?php
$vwidth = isset($vwidth) ? $vwidth : 200; //view port width 
$vheight = isset($vheight) ? $vheight : 150; //view port height 
$bwidth = isset($bwidth) ? $bwidth : 400; // boundary width
$divwidth = $bwidth + 40; // boundary width
$bheight = isset($bheight) ? $bheight : 300; //boundary height
$owidth = isset($owidth) ? $owidth : 800; //output width
$oheight = isset($oheight) ? $oheight : 600; //output height
$cropclass = isset($cropclass) ? $cropclass : "cropPopPanel";

$suffix = isset($suffix) ? $suffix : "";
$isimportreq = isset($isimportreq) ? $isimportreq : TRUE;
$isfilebtn = isset($isfilebtn) ? $isfilebtn : TRUE;
if ($isimportreq) {
    echo "\n<link rel=\"stylesheet\" href=\"" . $baseurl . "/assets/libs/croppie/croppie.min.css\">";
    echo "\n<script src=\"" . $baseurl . "/assets/libs/croppie/croppie.min.js\"></script>";
    echo "\n<script src=\"" . $baseurl . "/assets/libs/croppie/CropMaster.js\"></script>";
    echo "\n<script src=\"" . $baseurl . "/assets/libs/croppie/bpopup.min.js\"></script>";
}
echo "\n<script>var cropMaster" . $suffix . " = new CropMaster();</script>";
?>

<!--crop ui start -->
<div id="cropbox<?= $suffix; ?>" style="width:<?= $divwidth . 'px' ?>" class="hide <?= $cropclass ?>">
    <div id="cropdiv<?= $suffix ?>"></div>
    <div>
        <center>
            <button class="btn btn-primary" onclick="cropMaster<?= $suffix; ?>.getCropImg(callBackCropImg<?= $suffix; ?>);">OK</button>
            <button class="btn btn-light ml-2" onclick="closeCropDialog<?= $suffix; ?>();">Cancel</button>
        </center>
    </div>
</div>

<?php if ($isfilebtn) { ?>
    <div id="cropelements<?= $suffix; ?>">
        <input type="file" id="imgfile<?= $suffix; ?>" style='display:none;visibility:hidden;' class="imgFlag">
    </div>
<?php } ?>
<!--crop ui end -->

<script>
    //crop js start
    <?php
    echo "\ncropMaster" . $suffix . ".setup('cropdiv" . $suffix . "', " . $vwidth . ", " . $vheight . ", " . $bwidth . ", " . $bheight . ", " . $owidth . ", " . $oheight . ",'square');";
    echo "\ncropMaster" . $suffix . ".addFileDialog('imgfile" . $suffix . "', openCropDialog" . $suffix . ");";
    ?>

    function editPhoto<?= $suffix; ?>() {
        $("#imgfile<?= $suffix; ?>").click();
    }

    var loadOnceFlag = true;

    function openCamCropDialog<?= $suffix; ?>() {
        $("#fileOrCam<?= $suffix; ?>").addClass("hide").bPopup().close();
        if (loadOnceFlag) {
            $("#cropbox<?= $suffix; ?>").removeClass("hide").bPopup();
            loadOnceFlag = false;
        } else {
            setTimeout(function() {
                $("#cropbox<?= $suffix; ?>").removeClass("hide").bPopup();
            }, 5);
        }
    }

    function openCropDialog<?= $suffix; ?>() {
        $("#fileOrCam<?= $suffix; ?>").addClass("hide").bPopup().close();
        $("#cropbox<?= $suffix; ?>").removeClass("hide").bPopup();
    }

    function closeCropDialog<?= $suffix; ?>() {
        $("#cropbox<?= $suffix; ?>").addClass("hide").bPopup().close();
        cropMaster<?= $suffix; ?>.rawfile = $("#imgfile<?= $suffix; ?>").val();
        <?php
        if ($isfilebtn) {
            echo "$('#imgfile'.$suffix).val('');";
        }
        ?>

    }

    function callBackCropImg<?= $suffix; ?>(imgsrc) {
        $('#prophoto<?= $suffix; ?>').css('backgroundImage', 'url(' + imgsrc + ')'); //main page return photoid
        $("#propHolder<?= $suffix; ?>").hide();
        $("#porpin<?= $suffix; ?>").val(imgsrc);
        closeCropDialog<?= $suffix; ?>();
        $("#porpin<?= $suffix; ?>").trigger("change");
    }

    function loadProfilePhoto<?= $suffix; ?>() {
        var prophoto = $('#prophoto<?= $suffix; ?>').css('background-image');
        if (common.isEmpty(prophoto) || prophoto == "none") {
            $("#profilePhotoPlaceHolder<?= $suffix; ?>").show();
        } else {
            $("#profilePhotoPlaceHolder<?= $suffix; ?>").hide();
        }
    }

    var actionPanelInterval<?= $suffix; ?>;
    var isActionPanelActive<?= $suffix; ?> = false;

    function closeActionPanel<?= $suffix; ?>(id) {
        actionPanelInterval<?= $suffix; ?> = setTimeout(function() {
            isActionPanelActive<?= $suffix; ?> = false;
            $("#" + id).hide(100);
        }, 1000);
    }

    function openActionPanel<?= $suffix; ?>(id) {
        if (isActionPanelActive<?= $suffix; ?>) {
            clearInterval(actionPanelInterval<?= $suffix; ?>);
        } else {
            $("#" + id).show(100);
        }
        isActionPanelActive<?= $suffix; ?> = true;
    }
    $("#cropAction<?= $suffix; ?>").hide();

    //crop js end
</script>