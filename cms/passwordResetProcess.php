<?php

// Pupilsight system-wide include
require_once '../pupilsight.php'; 

    // Get primary role info
    $data = array('username' => 'admin');
    $sql = "SELECT * FROM pupilsightPerson WHERE username=:username";
    $res = $pdo->selectOne($sql, $data);
    $email=$res['email'];
    echo '<pre>';print_r($email);exit;

    

    //Mail Functionality


    if ($email) {
        //Set up email

        $subject='<h1>Reset</h1>';
    
        $mail= $container->get(Mailer::class);
        $mail->SMTPKeepAlive = true;
        $mail->CharSet="UTF-8";
        $mail->Encoding="base64" ;
        $mail->IsHTML(true);
        $mail->Subject=$subject ;
        $mail->To=$email;
        $mail->renderBody('mail/email.twig.html', [
            'title'  => $subject,
            'body'   => $body
        ]);


        $mail->Send();
        if(!$mail->Send())
            echo 'Not send';
        else
            echo 'Send';
        $mail->smtpClose();
    }


    



    