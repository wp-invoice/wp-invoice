<?php
class SendPhpMail {
	
	function SendPhpMail () {
	}
	
	function sendMail($fromname, $fromemail, $toname, $toemail, $replytoemail='', $replytoname='', $subject ,$body) {

$mail = new PHPMailer();

//$mail->SetLanguage('en', RootPath.'lib/phpmailer/');		
		
		
$mail->IsSMTP();
$mail->SMTPAuth   = false;                  // enable SMTP authentication
//$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
$mail->Host       = "213.42.18.227";      // sets GMAIL as the SMTP server
$mail->Port       = 8001;                   // set the SMTP port for the GMAIL server

//$mail->Username   = "";  // GMAIL username
//$mail->Password   = "";            // GMAIL password

$mail->AddReplyTo("yourusername@gmail.com","First Last");

$mail->From       = $fromemail;
$mail->FromName   = $fromname;

$mail->Subject    = $subject;

//$mail->Body       = "Hi,<br>This is the HTML BODY<br>";                      //HTML Body
$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
//$mail->WordWrap   = 50; // set word wrap

$mail->MsgHTML($body);

$mail->AddAddress($toemail, $toname);

//$mail->AddAttachment("images/phpmailer.gif"); // attachment

$mail->IsHTML(true); // send as HTML

if(!$mail->Send()) {
	
  //echo "Mailer Error: " . $mail->ErrorInfo; die;
  return 'false';
} else {
	return 'true';
  //$msg= "Activation link has been sent to your Emalid ID, Copy and Paste the link in your web browser.";
  //header("Location:index.php?msg=$msg");
}
	}
}
?>