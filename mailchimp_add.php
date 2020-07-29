<?php

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  require 'vendor/phpmailer/phpmailer/src/Exception.php';
  require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
  require 'vendor/phpmailer/phpmailer/src/SMTP.php';

  // Load credentials file
  require_once '/home/freeirvl/keys.php';

  // Load Composer's autoloader
  require 'vendor/autoload.php';

  // ************************************ LOAD MAILCHIMP LIBRARY************************************
  include(dirname(__FILE__) . '/MailChimp.php');
  use \DrewM\MailChimp\MailChimp;
  // ************************************ END LOAD MAILCHIMP ************************************

  // Get form data
   $email = $_REQUEST['email'];
   $first_name = $_REQUEST['first_name'];
   $last_name = $_REQUEST['last_name'];
   $phone_number = $_REQUEST['phone_number'];
   $linkedin = $_REQUEST['linkedin'];
   $profession = $_REQUEST['profession'];
   $education = $_REQUEST['education'];
   $question_1 = $_REQUEST['question_1'];
   $question_2 = $_REQUEST['question_2'];
   $question_3 = $_REQUEST['question_3'];
   $question_4 = $_REQUEST['question_4'];

  // Instantiation and passing `true` enables exceptions
  $mail = new PHPMailer(true);

  try {
    //Server settings
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
    $mail->isSMTP();                   // Send using SMTP
    $mail->Host = 'smtp.gmail.com';   // Set the SMTP server to send through
    $mail->SMTPAuth   = true;        // Enable SMTP authentication
    $mail->Username   = 'freecodingbootcamp';  // SMTP username
    $mail->Password   = $gmail_password;            // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom('YOUREMAIL@DOMAIN.com', 'Mailer');
    $mail->addAddress("{$email}", "{$first_name} {$last_name}");
    $mail->addCC('YOUREMAIL@DOMAIN.com', 'Avan Sardar');

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = "Thank you {$first_name} for Applying to the Free Coding Bootcamp";
    $body = "You provided us with the following answers <br>";
    $body .= "Name: {$first_name} {$last_name} <br>";
    $body .= "Email: {$email} <br>";
    $body .= "Phone Number: {$phone_number} <br>";
    $body .= "LinkedIn: {$linkedin} <br>";
    $body .= "Profession: {$profession} <br>";
    $body .= "Education: {$education} <br>";
    $body .= "Question 1: {$question_1} <br>";
    $body .= "Question 2: {$question_2} <br>";
    $body .= "Question 3: {$question_3} <br>";
    $body .= "Question 4: {$question_4} <br><br>";
    $body .= "<b>We will let you know once a new co-hort is being formed. Thanks again!</b> <br>";
    $mail->Body = $body;
    $mail->AltBody = "This is the body in plain text for non-HTML mail clients";
    $mail->send();
    subscribe_add_tags(strtolower($email));
    exit();
  } catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }

// ************************************ MAILCHIMP ************************************
  function subscribe_add_tags($email)  {
    // *************** LOADING API KEY AND LIST ID
    global  $mailchimp_api_key, $mailchimp_list_id;

    $MailChimp = new MailChimp($mailchimp_api_key);
    $list_id = $mailchimp_list_id;  //  <--- PLAYGROUND LIST_ID
    $hashed= md5(strtolower($email));
    // *************** CHANGE TAG NAME HERE
    $payload = '{ "tags": [ { "name": "Bootcamp", "status": "active" }  ] }';

    $payload_json = json_decode($payload, true);
    $result_subscribe = $MailChimp->post("lists/$list_id/members", [
				'email_address' => $email,
				'status'        => 'subscribed',
			]);
    $result_tag = $MailChimp->post("lists/$list_id/members/$hashed/tags", $payload_json);



    //   this error success produces NOTHING in the Response from MailChimp
    // because it doesn't seem to send anything, but if not error then success I guess
    // so you can still capture its status
    if ($MailChimp->success()) {
        print_r($result_subscribe);
        print_r($result_tag);
    } else {
        echo 'Error Subscribing and or Tagging Mailchimp<br>';
        print_r($result_subscribe);
        print_r($result_tag);
        echo $MailChimp->getLastError();
    }
  }  // end function
// ************************************ END MAILCHIMP ************************************


?>
