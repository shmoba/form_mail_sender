<?php
	ob_start();
	header('Content-Type: text/html;');
	error_reporting(E_ALL);
	mb_internal_encoding('utf-8');
	/*
		openserver(smtp = smtp.gmail.com; port = 587; email = name; pass = pass email/name; encr = tls)
		"Форма обратной связи"
		Сверстать форму с полями: ваше имя, ваш емейл, ваш телефон (не обязательно), сообщение, отправить. После отправки пользователь видит сообщение об успешной отправке и письмо приходит на емейл, указанный в настройках, например: drug@yandex.ru со всеми введенными данными.
		!!! 1. сообщения (об ошибке, об отправке, незаполненных полях) выводить ДО формы
		!!! 2. сообщения выводить в красивой рамке (на зеленом фоне успешная отправка, на красном - ошибки)
		!!! 3. если отправлено, то не выводить форму, показать сообщение "ваше сообщение успешно отправлено." и ссылку "отправить еще раз"
		!!! 4. если отправлено делать редирект, чтобы нажав F5 сообщение не отправилось
		!!! 5. проверять введенные данные, даже если атрибут required не поддерживается браузером
		!!! 6. отправлять нужно собранные данные из формы. тема письма стандартная для таких писем: Заявка с сайта www.домен.ру
		!!! 7. помимо php файла нужно прислать скриншот вашего письма http://joxi.ru/KAg40gai4Ldekr
	*/
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="style.css">
	<title>Форма обратной связи</title>
</head>
<body>
	<div id="wrap">
		<h1>Форма обратной связи</h1>
			<?php

				function mailto($to, $subject, $message, $attach=Array(), $from='Робот', $fromAddr='2018krok@gmail.com') { // $from не показывает

					$mb_internal_encoding = mb_internal_encoding();
					mb_internal_encoding('UTF-8');

					$headers = "From: =?UTF-8?b?".base64_encode($from)."?=<".$fromAddr.">\r\n"; // B=b
					$headers .= "Date: ".date("r")."\r\n";
					$headers .= "MIME-Version: 1.0\r\n"; // !!

					$subject = "=?UTF-8?b?".base64_encode($subject)."?=";
					if (strpos($message, '/>')) $msgType = 'text/html'; else $msgType = 'text/plain';
					if (is_string($attach)) $attach = Array($attach);
					$files = Array();
					foreach ($attach as $path) if (file_exists($path)) $files[] = $path;

					if ($files) {
						$boundary = md5(time());
						$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

						$body  = "\r\n--$boundary\r\n"; 
						$body .= "Content-Type: $msgType; charset=UTF-8\r\n";
						$body .= "Content-Transfer-Encoding: 8bit\r\n";
						$body .= "\r\n";
						$body .= $message;
						$body .= $message;

						foreach ($files as $path) {
							$filename = mb_substr($path, mb_strrpos($path, '/'));
							$body .= "\r\n--$boundary\r\n"; 
							$body .= "Content-Type: application/octet-stream\r\n";  
							$body .= "Content-Transfer-Encoding: base64\r\n"; 
							$body .= "Content-Disposition: attachment; filename*=UTF-8''".str_replace('+', '%20', urlencode($filename))."\r\n"; 
							$body .= "\r\n";
							$body .= chunk_split(base64_encode(file_get_contents($path)));
						}
						        
						$body .= "\r\n--$boundary--\r\n";

					} else {
						$headers .= "Content-Type: $msgType; charset=UTF-8\r\n";
						$headers .= "Content-Transfer-Encoding: 8bit\r\n";
						$headers .= "\r\n";
						$body = $message;
					}
					mb_internal_encoding($mb_internal_encoding);
					return mail($to, $subject, $body, $headers);
				}

				function error_style($string){ // Стиль сообщения
					return '<div class="error">'.$string.'</div>';//<script>setTimeout(function(){window.location.href = "index.php";}, 2000);</script>
				}

				function success_style($string){ // Стиль сообщения
					return '<div class="success">'.$string.'</div>';//<script>setTimeout(function(){window.location.href = "index.php";}, 2000);</script>
				}

				$phone_arr =  array('1', '2','3', '4', '5', '6', '7', '8', '9', '0', '+', '-', '(', ')',' ');

				function verif_phone ($str, $arr2){ // проверка номер на недопустимые символы
					if (!empty(array_diff(str_split($str), $arr2))) {return false;}
					else {return true;}
				}

				function verif_email ($email){ // проверка почты на валидность
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {return true;}
					else {return false;}
				}

				if (isset($_GET['success'])) { //Post/Redirect/Get
					die(success_style('Ваше сообщение успешно отправлено!<br><a href="index.php">ОТПРАВИТЬ ЕЩЕ РАЗ</a></div>'."\r\n")."\r\n".'</body>'."\r\n".'</html>');
				}
				
				if(!empty($_POST['submit'])){

					$errors = array(); // массив для ошибок
					
					if(isset($_POST['name']) || isset($_POST['mail']) || isset($_POST['phone']) || isset($_POST['message'])) {

						foreach ($_POST as $key => $value) {
							trim($value);
							strip_tags($value);
						}

					} // trim(strip_tags - убрать теги и пробелы

					$to = 'd4mkl@yandex.ru'; // приходит и даже не в спам
					$subject = 'Заявка с сайта www.домен.ру'; // есть
					$message = $_POST['message'];
					$message .= "\r\n".'От: '.$_POST['name']."\r\n". 'Тел.: '.$_POST['phone']."\r\n".'Адрес: '.$_POST['mail']; // есть

					if (empty($_POST['name']) || empty($_POST['mail']) || empty($_POST['message'])) {
						echo $errors[] = error_style('Сообщение не отправлено!<Br>Заполните обязательные поля!');
					}
					if (!empty($_POST['mail']) && verif_email($_POST['mail']) !== true) {
						echo $errors[] = error_style('Адрес невалиден!');
					}
					if (!empty($_POST['phone']) && verif_phone($_POST['phone'],$phone_arr) !== true) {
						echo $errors[] = error_style('Номер содержит недопустимые символы!');
					}
					
					if (empty($errors)) {
						$send = mailto($to, $subject, $message);
						header("Location: index.php?success");  // редирект с выводом сообщения
					}

					else {}
				} // required
			?>		
		<div id='contact-wrapper'>
				
		<form method='post' id='contactform'>
			<div>
				<label for='name'><strong>Ваше имя: <span>*</span></strong></label>
				<input type='text' name='name' placeholder='Олег' id='name' value="<?if(isset($_POST['name'])){echo $_POST['name'];}?>" />
			</div>
			<div>
				<label for='mail'><strong>E-mail: <span>*</span></strong></label>
				<input type='text' name='mail' placeholder='drug@yandex.ru' id='mail' value="<?if(isset($_POST['mail'])){echo $_POST['mail'];}?>" />
			</div>
			<div>
				<label for='phone'><strong>Телефон:</strong></label>
				<input type='text' name='phone' placeholder='+78005553535' id='phone' value="<?if(isset($_POST['phone'])){echo $_POST['phone'];}?>" />
			</div>
			<div>
				<label for='message'><strong>Ваше сообщение: <span>*</span></strong></label>
				<textarea name='message' placeholder='Текст сообщения...' id='message'><?if(isset($_POST['message'])){echo $_POST['message'];}?></textarea>
			</div>
			<p><span>*</span> - обязательно к заполнению.</p>
			<div id='submit'>
				<input type='submit' value='Отправить сообщение' name='submit' />
			</div>
		</form>
		</div>
	</div>
</body>
</html>