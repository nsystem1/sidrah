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
    $update_name_query->bindParam(":name", $name);
    $update_name_query->bindParam(":member_id", $member["id"]);
    $update_name_query->execute();
	
	// Update the fullname after that.
	update_fullname($member["id"]);
	
	// Update the user if any.
	// Check if the user does exist.
	$get_user_query = $dbh->prepare("SELECT id FROM user WHERE member_id = :member_id");
    $get_user_query->bindParam(":member_id", $member["id"]);
    $get_user_query->execute();
	
	// Found?
	if (mysql_num_rows($get_user_query) > 0)
	{
		// Get the user information.
		$user_info = $get_user_query->fetch(PDO::FETCH_ASSOC);
	
		// Update the username too.
		$update_username_query = $dbh->prepare("UPDATE user SET username = ':name:member_id' WHERE id = :user_info_id");
        $update_username_query->bindParam(":name", $name);
        $update_username_query->bindParam(":member_id", $member["id"]);
        $update_username_query->bindParam(":user_info_id", $user_info["id"]);
        $update_username_query->execute();
	}
	
	echo success_message(
		"تم تحديث الاسم بنجاح.",
		"familytree.php?id=$member[id]"
	);
}
