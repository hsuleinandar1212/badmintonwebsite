<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require_once __DIR__ . "/../PHPMailer/src/Exception.php";
require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/src/SMTP.php";


function sendMail($email, $name, $status)
{

    $mail = new PHPMailer(true);


    try {

        // SMTP Settings
        $mail->isSMTP();

        $mail->Host = "smtp.gmail.com";

        $mail->SMTPAuth = true;


        // Your Gmail
        $mail->Username = "mtechnologyuniversity@gmail.com";


        // Gmail App Password
        $mail->Password = "ispd dhqz pdde fccr";


        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;


        // Sender
        $mail->setFrom(
            "mtechnologyuniversity@gmail.com",
            "MTU Badminton Club"
        );


        // Receiver
        $mail->addAddress($email,$name);


        $mail->isHTML(true);



        if($status=="Approved"){


            $mail->Subject =
            "MTU Badminton Club Approved";


            $mail->Body = "

            <h2>Hello $name</h2>

            <p>
            Your registration has been 
            <b style='color:green'>
            Approved
            </b>.
            </p>

            <p>
            Welcome to MTU Badminton Club 🏸
            </p>

            ";


        }
        else{


            $mail->Subject =
            "MTU Badminton Club Update";


            $mail->Body = "

            <h2>Hello $name</h2>

            <p>
            Your registration has been 
            <b style='color:red'>
            Rejected
            </b>.
            </p>

            ";


        }


        $mail->send();

        return true;


    }
    catch(Exception $e){

        return false;

    }

}

?>