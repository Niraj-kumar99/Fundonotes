<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class SendEmailRequest
{
function sendMail($email, $token)
{

    $subject = 'Reset-Password';

    $data = 'Token to reset password <br>'.$token;
        
    $mail = new PHPMailer(true);
    try {                                       
        $mail->isSMTP();                                          
        $mail->Host       = env('MAIL_HOST');                       
        $mail->SMTPAuth   = true;                                  
        $mail->Username   = env('MAIL_USERNAME');                  
        $mail->Password   = env('MAIL_PASSWORD');                              
        $mail->SMTPSecure = 'tls'; 
        $mail->Port       = 587;
        $mail->setFrom(env('MAIL_USERNAME'), env('MAIL_FROM_NAME')); 
        $mail->addAddress($email);
        $mail->isHTML(true);  
        $mail->Subject =  $subject;
        $mail->Body    = $data;
        if($mail->send()){
            return true;
        } 
        else {
            return false;
        }
    }
    catch (Exception $e) {
        return back()->with('error','Message could not be sent.');
    }
}

public function sendEmailToCollab($email,$data,$currentUserfirstName,$currentUserlastName,$currentUserEmail)
    {
        $subject = 'Sharing a note to you:';

        $data = $currentUserfirstName.' '.$currentUserlastName.' '.$currentUserEmail.' shared a note to you <br>'.$data;
        //$name = $Touser_namefirstname;  //-------
        $email = $email;
        $subject = 'Sharing a note to you:';

        $mail = new PHPMailer(true);

        try
        {                                       
            $mail->isSMTP();                                          
            $mail->Host       = env('MAIL_HOST');                        
            $mail->SMTPAuth   = true;                                  
            $mail->Username   = env('MAIL_USERNAME');                  
            $mail->Password   = env('MAIL_PASSWORD');                              
            $mail->SMTPSecure = 'tls'; 
            $mail->Port       = 587;
            $mail->setFrom(env('MAIL_USERNAME'),env('MAIL_FROM_NAME')); 
            $mail->addAddress($email);
            $mail->isHTML(true);  
            $mail->Subject =  $subject;
            $mail->Body    = $data;
            $dt = $mail->send();

            if($dt)
                return true;
            else
                return false;

        }
        catch (Exception $e) 
        {
            return back()->with('error','Message could not be sent.');
        }
    }

}