<?php
/*
Pupilsight, Flexible & Open School System
*/

//Takes a number, and returns an Excel-style column reference for it
function num2alpha($n)
{
    for ($r = ''; $n >= 0; $n = intval($n / 26) - 1) {
        $r = chr($n % 26 + 0x41).$r;
    }

    return $r;
}

function getColourArray()
{
    $return = array();

    $return[0] = '54, 175, 56';
    $return[1] = '192, 89, 203';
    $return[2] = '30, 30, 200';
    $return[3] = '65, 83, 84';
    $return[4] = '206, 169, 83';
    $return[5] = '30, 255, 30';
    $return[6] = '255, 40, 40';
    $return[7] = '146, 156, 163';
    $return[8] = '121, 126, 203';
    $return[9] = '86, 117, 57';
    $return[10] = '114, 66, 47';
    $return[11] = '93, 55, 98';

    return $return;
}
