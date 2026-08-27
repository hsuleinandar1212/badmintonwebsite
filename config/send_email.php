<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';


function sendMemberEmail($email, $name, $status)
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'mtechnologyuniversity@gmail.com';
        $mail->Password = 'ispd dhqz pdde fccr';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;


        $mail->setFrom(
            'mtechnologyuniversity@gmail.com',
            'MTU Badminton Club'
        );

        $mail->addAddress($email, $name);


        $mail->isHTML(true);

        if($status == "Approved"){

            $mail->Subject = "MTU Badminton Club Registration Approved 🏸";

            $mail->Body = "
            <h2>Congratulations $name! 🎉</h2>

            <p>Your registration for MTU Badminton Club has been 
            <b>Approved</b>.</p>

            <p>Welcome to our badminton family 🏸</p>

            <p>Thank you.</p>
            ";

        }else{

            $mail->Subject = "MTU Badminton Club Registration Update";

            $mail->Body = "
            <h2>Hello $name</h2>

            <p>Your registration request has been 
            <b>Rejected</b>.</p>

            <p>Thank you for your interest.</p>
            ";
        }


        $mail->send();

        return true;


    } catch(Exception $e){

        return false;
    }
}

?>