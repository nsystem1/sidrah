<?php

require_once("inc/functions.inc.php");

$user = user_information();

$id = @$_GET["id"];
$submit = @$_POST["submit"];
$mobile = arabic_number(@$_POST["mobile"]);

// Check if the member does exist.
$member = get_member_id($id);

if ($user["group"] != "admin" && $user["group"] != "moderator")
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
	$update_mobile_query = $dbh->prepare("UPDATE member SET mobile = :mobile WHERE id = :member_id");
    $update_mobile_query->bindParam(":mobile", $mobile);
    $update_mobile_query->bindParam(":member_id", $member["id"]);
    $update_mobile_query->execute();

	echo success_message(
			"تم تحديث الجوّال بنجاح.",
			"familytree.php?id=$member[id]"
	);
}
