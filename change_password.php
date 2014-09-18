<?php

require_once("inc/functions.inc.php");

$user = user_information();

// Check if the user is a visitor.
if ($user["group"] == "visitor")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

$member_id = $user["member_id"];

$submit = @$_POST["submit"];
$member = get_member_id($member_id);

if ($member == false)
{
	echo error_message("لم يتم العثور على صفحة العضو.");
	return;
}

if (!empty($submit))
{
	$old_password = @$_POST["old_password"];
	$new_password1 = @$_POST["new_password1"];
	$new_password2 = @$_POST["new_password2"];

	// Check if there is some empty fields.
	if (empty($old_password) || empty($new_password1) || empty($new_password2))
	{
		echo error_message("الرجاء تعبئة الحقول المطلوبة.");
		return;
	}
	
	// Check if the old password is correct.
	if (sha1_salt($old_password) != $user["password"])
	{
		echo error_message("كلمة المرور الحاليّة لا تتطابق مع كلمة المرور المدخلة.");
		return;
	}
	
	// Check if the new passwords are not the same.
	if ($new_password1 != $new_password2)
	{
		echo error_message("كلمة المرور الجديدة و تأكيدها ليست متطابقة.");
		return;
	}
	
	// OK, update the password of the user.
	$password = sha1_salt($new_password1);
	$update_password_query = $dbh->prepare("UPDATE user SET password = :password WHERE member_id = :member_id");
    $update_password_query->bindParam(":password", $password);
    $update_password_query->bindParam(":member_id", $member_id);
    $update_password_query->execute();

	// Logout after all,
	echo success_message(
		"تم تغيير كلمة المرور بنجاح، قم بتسجيل الدخول مرة أخرى.",
		"logout.php"
	);
	
	return;
}
else
{
	// Get the header.
	$header = website_header(
		"تغيير كلمة المرور",
		"صفحة من أجل تغيير كلمة المرور.",
		array(
			main_tribe_name, "عائلة", "شجرة", "تغيير", "كلمة", "المرور"
		)
	);

	$content = template(
		"views/change_password.html",
		array(
			"username" => $user["username"]
		)
	);
	
	// Get the footer.
	$footer = website_footer();

	// Print the page.
	echo $header;
	echo $content;
	echo $footer;
}
