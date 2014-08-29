<?php

require_once("inc/functions.inc.php");

$submit = @$_POST["submit"];
$username = @$_POST["username"];
$password = @$_POST["password"];
$url = @$_POST["url"];

if (!empty($submit))
{
	// variables
	$hashed_password = "";

	$username = trim(arabic_number($username));
	$password = trim(arabic_number($password));

	if (empty($username) || empty($password))
	{
		echo website_header();
		echo error_message("الرجاء إدخال اسم المستخدم و كلمة المرور.");
		echo website_footer();
		return;
	}
	else
	{
		$hashed_password = sha1_salt($password);
		
		$select_user_query = $dbh->prepare("SELECT * FROM user WHERE username = :username AND password = :hashed_password");
		
		$select_user_query->bindParam(":username", $username);
		$select_user_query->bindParam(":hashed_password", $hashed_password);

		$select_user_query->execute();

		$user_num_rows = $select_user_query->rowCount();
		
		if ($user_num_rows == 0)
		{
			echo website_header();
			echo error_message("اسم المستخدم أو كلمة المرور غير صحيحة.");
			echo website_footer();
			return;
		}
	}

	$cookie_time = time()+21600;
	$user = $select_user_query->fetch(PDO::FETCH_ASSOC);
	$member = get_member_id($user["member_id"]);

	// Check if the user has no related member.
	if ($member == false)
	{
		echo error_message("لا يُمكن تسجيل الدخول بهذا المستخدم لعدم ارتباطه بفردٍ من الأفراد، الرجاء تسجيل الدخول بمستخدمٍ آخر.");
		return;
	}

	// Save the cookie of the user information.
	setcookie("sidrah_username", $user["username"], $cookie_time);
	setcookie("sidrah_password", $user["password"], $cookie_time);
		
	// Update the last login time.
	$now = time();

	$update_last_login_query = $dbh->prepare("UPDATE user SET last_login_time = :now WHERE id = :user_id");

	$update_last_login_query->bindParam(":now", $now);
	$update_last_login_query->bindParam(":user_id", $user["id"]);

	$update_last_login_query->execute();

	if (!empty($url))
	{
		$redirect = $url;
	}
	else
	{
		if ($user["first_login"] == 1 || $member["gender"] == 0)
		{
			$gender = ($member["gender"] == 1) ? "male" : "female";
			$redirect = "update_profile_{$gender}.php?id=$member[id]";
		}
		else
		{
			//$redirect = "familytree.php?id=$user[member_id]";
			$redirect = "index.php";
		}
	}
	
	// Show message and redirect to it.
	echo success_message(
		"تمت عملية تسجيل الدخول بنجاح.",
		$redirect
	);
	
	return;
}
else
{
	// Get the user information.
	$user = user_information();
	$url = @$_GET["url"];
	
	if ($user["group"] != "visitor")
	{
		echo error_message("لقد قمت بتسجيل الدخول بالفعل.");
		return;
	}

	// Get the header.
	$header = website_header(
		"تسجيل الدخول",
		"صفحة من أجل تسجيل الدخول إلى شجرة العائلة.",
		array(
			"الزغيبي", "عائلة", "شجرة", "تسجيل", "الدخول"
		)
	);

	// Get the login content.
	$content = template(
		"views/login.html",
		array(
			"url" => $url
		)
	);

	// Get the footer.
	$footer = website_footer();

	// Print the page.
	echo $header;
	echo $content;
	echo $footer;
}
