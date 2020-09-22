<?php
    $_SESSION["log"] = "admin";
    $_SESSION["pwd"] = "Admin@123456";
    $_SESSION["callbackurl"] = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Campaign/fluent.php";
?>
<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'];?>/wp/wp-login.php?admin=formaccess" id="loginform" name="loginform" style="display:none">
    <input type="hidden" value="admin" id="user_login" name="log">
    <input type="hidden" value="Admin@123456" id="user_pass" name="pwd">

    <!--
    <p>
        <label for="user_login">Username<br>
        <input type="text" size="20" value="admin" class="input" id="user_login" name="log"></label>
    </p>
    <p>
        <label for="user_pass">Password<br>
        <input type="password" size="20" value="Admin@123456" class="input" id="user_pass" name="pwd"></label>
    </p>
    <p class="forgetmenot"><label for="rememberme"><input type="checkbox" value="forever" id="rememberme" name="rememberme"> Remember Me</label></p>
    <p class="submit">
    --->
        <input id="wp-submit" name="wp-submit" type="submit" value="Log In" class="button button-primary button-large">
        <input type="hidden" value="<?php echo $_SESSION[$guid]['absoluteURL'];?>'/index.php?q=/modules/Campaign/fluent.php" name="redirect_to">
        <input type="hidden" value="1" name="testcookie">
    <!--</p>-->
</form>
<script>
    document.getElementById("wp-submit").click();
</script>