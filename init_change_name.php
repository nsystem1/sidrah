<?php

require_once("inc/functions.inc.php");

$user = user_information();

$id = @$_GET["id"];
$submit = @$_POST["submit"];
$name = normalize_name(@$_POST["name"]);

// Check if the member does exist.
$member = get_member_id($id);

if ($user["group"] != "admin")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;	
}

if ($member == false)
{
	echo error_message("لا يمكن العثور على العضو المطلوب.");
	return;
}

if (!empty($submit))
{
	if (empty($name))
	{
		echo error_message("الرجاء إدخال الاسم الجديد.");
		return;
	}
	
	if ($name == $member["name"])
	{
		echo error_message("لم يتم تغيير الاسم.");
		return;
	}

	// Start to change the name of the member.
	$update_name_query = $dbh->prepare("UPDATE member SET name = :name WHERE id = :member_id");
$dbh->bindParam(":name", $name);
$dbh->bindParam(":member_id", $member["id"]);
$dbh->execute();

	
	// Update the fullname after that.
	update_fullname($member["id"]);
	
	// Update the user if any.
	// Check if the user does exist.
	$get_user_query = $dbh->prepare("SELECT id FROM user WHERE member_id = :member_id");
$dbh->bindParam(":member_id", $member["id"]);
$dbh->execute();

	
	// Found?
	if (mysql_num_rows($get_user_query) > 0)
	{
		// Get the user information.
		$user_info = mysql_fetch_array($get_user_query);
	
		// Update the username too.
		$update_username_query = $dbh->prepare("UPDATE user SET username = ':name:member_id' WHERE id = :user_info_id");
$dbh->bindParam(":name", $name);
$dbh->bindParam(":member_id", $member["id"]);
$dbh->bindParam(":user_info_id", $user_info["id"]);
$dbh->execute();

	}
	
	echo success_message(
		"تم تحديث الاسم بنجاح.",
		"familytree.php?id=$member[id]"
	);
}
