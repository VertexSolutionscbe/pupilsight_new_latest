<form id="myForm" action="/thirdparty/payment/airpay/responsefromairpay.php" method="post">
    <?php
    foreach ($_POST as $a => $b) {
        echo '<input type="hidden" name="' . htmlentities($a) . '" value="' . htmlentities($b) . '">';
    }
    ?>
</form>
<script type="text/javascript">
    document.getElementById('myForm').submit();
</script>

<?php
die();
