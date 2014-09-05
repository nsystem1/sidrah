<?php

session_start();

require_once("inc/functions.inc.php");

$action = @$_GET["action"];

$session_verification_code = @$_SESSION["sidrah_verification_code"];
$session_mobile = @$_SESSION["sidrah_mobile"];

switch ($action)
{
	default: case "enter_mobile":
	
		$submit = @$_POST["submit"];
		
		if (!empty($submit))
		{
			$mobile = arabic_number(@$_POST["mobile"]);
			$captcha = arabic_number(@$_POST["captcha"]);

			if (empty($mobile) || empty($captcha))
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			$sha1_captcha = sha1_salt($captcha);
	
			// Check if the captcha is missed up.
			if ($_SESSION["reset_password"] != $sha1_captcha)
			{
				echo error_message("الرجاء إدخال رمز التحقّق بشكل صحيح.");
				return;
			}
			
			// Check if a user with the giving information does exist.
			$get_user_query = $dbh->prepare("SELECT member.mobile as mobile, user.username as username, user.id as user_id FROM member, user WHERE user.member_id = member.id AND member.mobile = :mobile");
$dbh->bindParam(":mobile", $mobile);
$dbh->execute();

	
			if (mysql_num_rows($get_user_query) > 0)
			{
				$user_info = mysql_fetch_array($get_user_query);
				
				$verification_code = sprintf("%04d", rand(0, 9999));				
				$hashed_verification_code = sha1_salt($verification_code);

				$_SESSION["sidrah_verification_code"] = $hashed_verification_code;
				$_SESSION["sidrah_mobile"] = $user_info["mobile"];

				// Send an sms.
				$content = "رمز التأكيد\n$verification_code";
				$sms_received = send_sms(array("966" . $user_info["mobile"]), $content);
				
				// Redirect to enter code page.
				redirect("reset_password.php?action=enter_code");
				return;
			}
			else
			{
				echo error_message("المعلومات المُدخلة غير صحيحة.");
				return;
			}
		}
		else
		{
			if (!empty($session_verification_code))
			{
				redirect("reset_password.php?action=enter_code");
				return;
			}
			
			// Get the header.
			$header = website_header(
				"نسيت كلمة المرور",
				"صفحة من أجل توليد كلمة مرور جديدة.",
				array(
					"الزغيبي", "عائلة", "نسيت", "كلمة", "المرور"
				)
			);

			// Get the template.
			$content = template(
				"views/reset_password_mobile.html"
			);
	
			// Get the footer.
			$footer = website_footer();
	
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	
	break;
	
	case "enter_code":
	
		if (empty($session_verification_code) || empty($session_mobile))
		{
			redirect("reset_password.php?action=enter_mobile");
			return;
		}

		$submit = @$_POST["submit"];
		
		if (!empty($submit))
		{
			$verification_code = arabic_number(@$_POST["verification_code"]);
			$hashed_verification_code = sha1_salt($verification_code);
			
			if ($hashed_verification_code != $session_verification_code)
			{
				echo error_message("رمز التأكيد غير صحيح.");
				return;
			}

			// Check if a user with the giving information does exist.
			$get_user_query = $dbh->prepare("SELECT member.mobile as mobile, user.username as username, user.id as user_id FROM member, user WHERE user.member_id = member.id AND member.mobile = :session_mobile");
$dbh->bindParam(":session_mobile", $session_mobile);
$dbh->execute();

	
			if (mysql_num_rows($get_user_query) > 0)
			{
				// Get user information.
				$user_info = mysql_fetch_array($get_user_query);
		
				// Generate a new password.
				$password = generate_key();
				$hashed_password = sha1_salt($password);
		
				// Update a password.
				$update_password_query = $dbh->prepare("UPDATE user SET password = :hashed_password WHERE username = :user_info_username");
$dbh->bindParam(":hashed_password", $hashed_password);
$dbh->bindParam(":user_info_username", $user_info["username"]);
$dbh->execute();

		
				// Send an sms.
				$content = "اسم المستخدم\n$user_info[username]\n\nكلمة المرور الجديدة\n$password";
				$sms_received = send_sms(array("966" . $user_info["mobile"]), $content);
	
				// Update the value of sms received.
				$update_sms_received_query = $dbh->prepare("UPDATE user SET sms_received = :sms_received WHERE id = :user_info_user_id");
$dbh->bindParam(":sms_received", $sms_received);
$dbh->bindParam(":user_info_user_id", $user_info["user_id"]);
$dbh->execute();

		
				unset($_SESSION["sidrah_verification_code"]);
				unset($_SESSION["sidrah_mobile"]);
		
				echo success_message(
					"تم توليد كلمة مرور جديدة.",
					"logout.php"
				);
			}
		}
		else
		{
			// Get the header.
			$header = website_header(
				"نسيت كلمة المرور",
				"صفحة من أجل توليد كلمة مرور جديدة.",
				array(
					"الزغيبي", "عائلة", "نسيت", "كلمة", "المرور"
				)
			);

			// Get the template.
			$content = template(
				"views/reset_password_code.html"
			);
	
			// Get the footer.
			$footer = website_footer();
	
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	
	break;
}

/*
$submit = @$_POST["submit"];

if (!empty($submit))
{
	$username = trim(@$_POST["username"]);
	$mobile = (int) trim(arabic_number(@$_POST["mobile"]));
	
	// Check if the username is empty or mobile.
	if (empty($username) || empty($mobile))
	{
		echo error_message("اسم المستخدم أو كلمة المرور فارغة.");
		return;
	}

	// Check if a user with the giving information does exist.
	$get_user_query = $dbh->prepare("SELECT member.mobile as mobile, user.username as username, user.id as user_id FROM member, user WHERE user.member_id = member.id AND member.mobile = :mobile AND user.username = :username");
$dbh->bindParam(":mobile", $mobile);
$dbh->bindParam(":username", $username);
$dbh->execute();

	
	if (mysql_num_rows($get_user_query) > 0)
	{
		// Get user information.
		$user_info = mysql_fetch_array($get_user_query);
		
		// Generate a new password.
		$password = generate_key();
		$hashed_password = sha1_salt($password);
		
		// Update a password.
		$update_password_query = $dbh->prepare("UPDATE user SET password = :hashed_password WHERE username = :user_info_username");
$dbh->bindParam(":hashed_password", $hashed_password);
$dbh->bindParam(":user_info_username", $user_info["username"]);
$dbh->execute();

		
		// Send an sms.
		$content = "كلمة المرور الجديدة\n$password";
		$sms_received = send_sms(array("966" . $user_info["mobile"]), $content);
	
		// Update the value of sms received.
		$update_sms_received_query = $dbh->prepare("UPDATE user SET sms_received = :sms_received WHERE id = :user_info_user_id");
$dbh->bindParam(":sms_received", $sms_received);
$dbh->bindParam(":user_info_user_id", $user_info["user_id"]);
$dbh->execute();

		
		echo success_message(
			"تم توليد كلمة مرور جديدة.",
			"familytree.php"
		);echo error_message("المعلومات المُدخلة غير صحيحة.");
		return;
	}
	else
	{
		echo error_message("المعلومات المُدخلة غير صحيحة.");
		return;
	}
}
else
{
	// Get the header.
	$header = website_header(
		"نسيت كلمة المرور",
		"صفحة من أجل توليد كلمة مرور جديدة.",
		array(
			"الزغيبي", "عائلة", "نسيت", "كلمة", "المرور"
		)
	);

	// Get the template.
	$content = template(
		"views/reset_password.html"
	);
	
	// Get the footer.
	$footer = website_footer();
	
	// Print the page.
	echo $header;
	echo $content;
	echo $footer;
}
*/
