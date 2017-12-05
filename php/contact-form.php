<?php
if(isset($_POST["action"])) {
	$name = $_POST['name'];        // Sender's name
	$email = $_POST['email'];      // Sender's email address
	$phone  = $_POST['phone'];     // Sender's email address
	$message = nl2br($_POST['message']);  // Sender's message
	$headers = 'Content-Type: text/html; charset=utf-8' . "\r\n";
	$headers .= 'Content-Transfer-Encoding: 8bit' . "\r\n";

	$to = 'nabla@mail.bg, office@nabla.bg';     // Recipient's email address
	$subject = 'Message from the website contact form'; // Message title

	$body = " From: $name <br> E-Mail: $email <br> Phone : $phone <br><br> Message :<br> $message"  ;
	
	// init error message
	$errmsg='';
	// Check if name has been entered
	if (isset($_POST['name']) && $_POST['name'] == '') {
		$errmsg .= '<p>Моля, попълнете вашето име.</p>';
	}
	// Check if email has been entered and is valid
	if (!$_POST['email'] || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$errmsg .= '<p>Моля, попълнете валиден имейл адрес.</p>';
	}
	//Check if phone number has been entered
	if ( isset($_POST['phone']) && $_POST['phone'] == '') {
		$errmsg .= '<p>Моля, попълнете вашия телефонен номер.</p>';
	}
	
	//Check if message has been entered
	if ( isset($_POST['message']) && $_POST['message'] == '') {
		$errmsg .= '<p>Моля, попълнете вашето съобщение.</p>';
	}

	/* Check Google captch validation */
	if( isset( $_POST['g-recaptcha-response'] ) ){
		$error_message = validation_google_captcha( $_POST['g-recaptcha-response'] );
		if($error_message!=''){
			$errmsg .= $error_message;
		}
	}	
	
	$result='';
	// If there are no errors, send the email
	if (!$errmsg) {
		if (mail ($to, $subject, $body, $headers)) {
			$result='<div class="alert alert-success">Благодаря, че се свързахте с нас. Вашето съобщение беше изпратено успешно. Ще се свържем с Вас в най-кратки срокове!</div>';
		}
		else {
		  $result='<div class="alert alert-danger">Съжаляваме, но имаше проблем с изпращането на съобщението Ви. Моля, опитайте по-късно.</div>';
		}
	}
	else{
		$result='<div class="alert alert-danger">'.$errmsg.'</div>';
	}
	echo $result;
 }

/*
 * Validate google captch
 */
function validation_google_captcha( $captch_response){

	/* Replace google captcha secret key*/
	$captch_secret_key = '6LcCPDoUAAAAAGXE9rlFqbarpDmw5KtmNvGMLk1J';
	
	$data = array(
            'secret'   => $captch_secret_key,
            'response' => $captch_response,
			'remoteip' => $_SERVER['REMOTE_ADDR']
        );
	$verify = curl_init();
	curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
	curl_setopt($verify, CURLOPT_POST, true);
	curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($verify);
	$response = json_decode( $response, true );
	$error_message='';
	if( isset($response['error-codes']) && !empty($response['error-codes'])){
		if( $response['error-codes'][0] == 'missing-input-secret' ){
			
			$error_message = '<p>The recaptcha secret parameter is missing.</p>';
			
		}elseif( $response['error-codes'][0] == 'invalid-input-secret' ){
			
			$error_message = '<p>The recaptcha secret parameter is invalid or malformed.</p>';
			
		}elseif( $response['error-codes'][0] == 'missing-input-response' ){
			
			$error_message = '<p>The recaptcha response parameter is missing.</p>';
			
		}elseif( $response['error-codes'][0] == 'invalid-input-response' ){
			
			$error_message = '<p>The recaptcha response parameter is invalid or malformed.</p>';
			
		}elseif( $response['error-codes'][0] == 'bad-request' ){
			
			$error_message = '<p>The recaptcha request is invalid or malformed.</p>';
		}
	}	
	if( $error_message !=''){
		return $error_message;
	}else{
		return '';
	}
  }
