<?php
require_once dirname(__FILE__) . '/../vendor/PHPMailer/class.phpmailer.php';

function sendmail($to,$subject,$content) {
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->IsHTML(true);
	$mail->SMTPSecure = "ssl";
	$mail->CharSet ="utf-8";
	$mail->Encoding = "base64";
	$mail->AddAddress($to, "");
	$mail->Subject = $subject;
	$mail->Body    = $content;
	$host    = 'smtp.126.com';
	$username = 'hb4daemon@126.com';
	$password = 'HB4daemon';
	$from    = 'hb4daemon@126.com';
	$fromname = 'Kantwait Team';
	$mail->Host    = $host;
	$mail->Username = $username;
	$mail->Password = $password;
	$mail->From    = $from;
	$mail->FromName = $fromname;
	$mail->Port = 465;
	//$mail->SMTPDebug = true;
	return $mail->send();
}

function fill_mail($host, $content) {
    $html = "
<html>
<body style='text-align:center;'>
	<div>
		<div style='background: none repeat scroll 0% 0% rgb(37, 16, 42); text-align:center;margin:auto;padding: 15px; width: 670px;'>
		</div>
		<div style='text-align:center;margin:auto;padding-left: 15px;padding-right:15px; width: 670px;font-family:proxima-nova,Proxima Nova, sans-serif;'>
			<p style='font-size:40px;'>Celebrate life everyday!</p>
			<p style='font-size:16px;color:#4f4f4f;'>$content</p>
		</div>
		<div style='background: none repeat scroll 0% 0% rgb(37, 16, 42); text-align:left;margin:auto;padding-left:10px;padding-right:10px;padding-top:5px;padding-bottom: 5px;width:680px;color:white;font-family:proxima;'>
			<p style='margin-left:30px;font-family:proxima-nova,Proxima Nova, sans-serif;'>2015&copy;Kantwait</p>
		</div>
		
	</div>
</body>
</html>
    ";
    return $html;
}


?>
