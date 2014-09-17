<?php

require_once("inc/functions.inc.php");

$user = user_information();

if ($user["group"] != "admin")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

$action = @$_GET["action"];

switch ($action)
{	
	case "add_prepared_relation":
	
		/*
		$submit = @$_POST["submit"];
		$name = trim(@$_POST["name"]);
		$query = trim(@$_POST["query"]);

		if (empty($submit))
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		if (empty($name) || empty($query))
		{
			echo error_message("الرجاء تعبئة الحقول المطلوبة.");
			return;
		}
		
		$moderator_info = get_member_fullname($moderator_name);
		$moderator_root_info = get_member_fullname($moderator_root_name);
		
		if ($moderator_info == false || $moderator_root_info == false)
		{
			echo error_message("لا يمكن العثور على اسم المشرف.");
			return;
		}
		
		// Check if there is a user mapped to the member.
		$get_user_member_query = $dbh->prepare("SELECT * FROM user WHERE member_id = :moderator_info_id");
$dbh->bindParam(":moderator_info_id", $moderator_info["id"]);
$dbh->execute();

		
		if (mysql_num_rows($get_user_member_query) == 0)
		{
			echo error_message("لا يمكن العثور على مستخدم مرتبط باسم المشرف.");
			return;
		}
		
		$user_member = $get_user_member_query->fetch(PDO::FETCH_ASSOC);
		
		// Now, Update information of the user to be a moderator.
		$update_query = $dbh->prepare("UPDATE user SET usergroup = 'moderator', assigned_root_id = :moderator_root_info_id WHERE id = :user_member_id");
$dbh->bindParam(":moderator_root_info_id", $moderator_root_info["id"]);
$dbh->bindParam(":user_member_id", $user_member["id"]);
$dbh->execute();

		
		echo success_message(
			"تم إضافة المشرف بنجاح.",
			"manage_moderators.php"
		);
		*/
	break;
	
	case "update_prepared_relations":

		$submit = @$_POST["submit"];
		$do = @$_POST["do"];
		$check = @$_POST["check"];

		// Array to hold prepared relations.
		$new_prepared_relations = array();
		
		if (empty($submit))
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		if (!isset($check))
		{
			echo error_message("الرجاء اختيار خيار واحد على الأقل.");
			return;
		}
		
		// Okay.
		if (count($check) > 0)
		{	
			if ($do == "delete")
			{
				foreach ($check as $k => $v)
				{
					// Delete the prepared relation.
					$delete_query = $dbh->prepare("DELETE FROM prepared_relation WHERE id = :k");
                    $delete_query->bindParam(":k", $k);
                    $delete_query->execute();
				}
			}
		}

		echo success_message(
			"تم تحديث العلاقات المعدّة بنجاح.",
			"prepared_relations.php"
		);
		
		return;
	
	break;
	
	default: case "view_prepared_relations":
	
		$prepared_relations_html = "";
		$get_prepared_relations_query = $dbh->prepare("SELECT * FROM prepared_relation ORDER BY id DESC");
$dbh->execute();

		
		if (mysql_num_rows($get_prepared_relations_query) == 0)
		{
			$prepared_relations_html = "<tr><td colspan='3' class='error'><i class='icon-exclamation-sign'></i> لم يتم إضافة علاقات معدّة بعد.</td></tr>";
		}
		else
		{
			while ($prepared_relation = mysql_fetch_array($get_prepared_relations_query))
			{	
				$prepared_relations_html .= "<tr><td><input type='checkbox' name='check[$prepared_relation[id]]' /></td><td><b>$prepared_relation[name]</b></td><td>[Edit]</td></tr>";
			}
		}

		// Get the header.
		$header = website_header(
			"إدارة العلاقات المعدّة",
			"صفحة من أجل إدارة العلاقات المعدّة",
			array(
				"عائلة", "الزغيبي", "شجرة", "العائلة", "إدارة", "العلاقات", "المعدّة"
			)
		);

		// Get the template of the page.
		$template = template(
				"views/manage_prepared_relations.html",
				array(
					"prepared_relations" => $prepared_relations_html
				)
		);
		
		// Get the footer.
		$footer = website_footer();

		// Print the page.
		echo $header;
		echo $template;
		echo $footer;
	break;
}

