<?php
session_start(); // for show your session value 
//print_r($_SESSION); // remove this after check
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<form name="myForm" action="/thanks" method="POST">

    <table>
        <tr>
            <td style="width:272px;">
                <select name="select_amount" id="select_amount" onchange="update_session_value(this.value)">
                    <option value="0">select a listing type</option>
                    <option value="10">Premium Listings</option>
                    <option value="20">Premium Blogs</option>
                    <option value="30">1 week sticky</option>
                </select></td>

            <td style="text-align:left;color:gray;">
                <span style="color:crimson;">*
            </span>Select a listing type
            </td>
        </tr>
    </table>
    <input type="submit" name="submit" class="button_add" onsubmit="return validateForm()">

</form>

<script>
    function update_session_value(value) {
        $.ajax({
            type: "POST",
            url: 'http://localhost/pupilsight_new/modules/Dashboard/testsession1.php', // change url as your 
            data: 'select_amount=' + value,
            dataType: 'json',
            success: function (data) {
			alert('called');
            }
        });
    }

</script>