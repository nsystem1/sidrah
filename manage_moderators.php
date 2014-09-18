<?php

// MANAGE_MODERATORS.PHP
//

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
	case "add_moderator":
	
		$submit = @$_POST["submit"];
		$moderator_name = trim(@$_POST["moderator_name"]);
		$moderator_root_name = trim(@$_POST["moderator_root_name"]);

		if (empty($submit))
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		if (empty($moderator_name) || empty($moderator_root_name))
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
        $get_user_member_query->bindParam(":moderator_info_id", $moderator_info["id"]);
        $get_user_member_query->execute();
		
		if ($get_user_member_query->rowCount() == 0)
		{
			echo error_message("لا يمكن العثور على مستخدم مرتبط باسم المشرف.");
			return;
		}
		
		$user_member = $get_user_member_query->fetch(PDO::FETCH_ASSOC);
		
		// Now, Update information of the user to be a moderator.
		$update_query = $dbh->prepare("UPDATE user SET usergroup = 'moderator', assigned_root_id = :moderator_root_info_id WHERE id = :user_member_id");
        $update_query->bindParam(":moderator_root_info_id", $moderator_root_info["id"]);
        $update_query->bindParam(":user_member_id", $user_member["id"]);
        $update_query->execute();
		
		echo success_message(
			"تم إضافة المشرف بنجاح.",
			"manage_moderators.php"
		);
	break;
	
	case "update_moderators":

		$submit = @$_POST["submit"];
		$do = @$_POST["do"];
		$check = @$_POST["check"];
		$moderator_root = @$_POST["moderator_root"];

		// Array to hold new moderators ids.
		$new_moderators = array();
		
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
		
		// Everything is good, almost.
		// Check if there is a moderator name that is empty.
		if (count($check) > 0)
		{
			foreach ($check as $k => $v)
			{
				$one_moderator_root = trim($moderator_root[$k]);
				
				if (empty($one_moderator_root) && $do != "delete")
				{
					echo error_message("أحد أسماء جذور المشرفين فارغ.");
					return;
				}
				else
				{
					$tmp_member_info = get_member_fullname($one_moderator_root);
					
					if ($tmp_member_info || $do == "delete")
					{
						$new_moderators []= array(
							"moderator_id" => $k, // moderator id
							"assigned_root_id" => @$tmp_member_info["id"], // assigned root id
						);
					}
					else
					{
						echo error_message("أحد أسماء المشرفين غير صحيح.");
						return;
					}
				}
			}
			
			
		}

		if ($do == "update")
		{			
			foreach ($new_moderators as $new_moderator)
			{
				$update_query = $dbh->prepare("UPDATE user SET usergroup = 'moderator', assigned_root_id = :new_moderator_assigned_root_id WHERE id = :new_moderator_moderator_id");
                $update_query->bindParam(":new_moderator_assigned_root_id", $new_moderator["assigned_root_id"]);
                $update_query->bindParam(":new_moderator_moderator_id", $new_moderator["moderator_id"]);
                $update_query->execute();
			}
		}
		else if ($do == "delete")
		{
			foreach ($new_moderators as $new_moderator)
			{
				// Make the moderator to be a user.
				$update_query = $dbh->prepare("UPDATE user SET usergroup = 'user', assigned_root_id = '0' WHERE id = :new_moderator_moderator_id");
                $update_query->bindParam(":new_moderator_moderator_id", $new_moderator["moderator_id"]);
                $update_query->execute();

				// Set all related requests to someone close.
				auto_reassign_requests($new_moderator["moderator_id"]);
			}
		}

		echo success_message(
			"تم تحديث المشرفين بنجاح.",
			"manage_moderators.php"
		);
		
		return;
	
	break;
	
	default: case "view_moderators":
	
		$moderators_html = "";
		$get_moderators_query = $dbh->prepare("SELECT * FROM user WHERE usergroup = 'moderator'");
$dbh->execute();

		
		if ($get_moderators_query->rowCount() == 0)
		{
			$moderators_html = "<tr><td colspan='5' class='error'>لم يتم إضافة مشرفين بعد.</td></tr>";
		}
		else
		{
			while ($moderator = $get_moderators_query->fetch(PDO::FETCH_ASSOC))
			{
				// Get the member information.
				$moderator_info = get_member_id($moderator["member_id"]);
				
				// Get the root information (if any).
				$root_info = get_member_id($moderator["assigned_root_id"]);
				$root_fullname = ($root_info == false) ? "" : $root_info["fullname"];
				
				// Get number of members from the same root (and the number of the requests).
				$root_members_query = mysql_query("SELECT COUNT(id) as members_count FROM member WHERE fullname LIKE '%$root_fullname'");
				$root_members_fetch = @$root_members_query->fetch(PDO::FETCH_ASSOC);
				$root_members_count = @$root_members_fetch["members_count"];
				
				// Get number of pending requests for this moderator.
				$get_pending_requests_query = $dbh->prepare("SELECT COUNT(id) as requests_count FROM request WHERE status = 'pending' AND assigned_to = :moderator_id");
                $get_pending_requests_query->bindParam(":moderator_id", $moderator["id"]);
                $get_pending_requests_query->execute();

				$pendong_requests_fetch = @$get_pending_requests_query->fetch(PDO::FETCH_ASSOC);
				$pending_requests_count = @$pendong_requests_fetch["requests_count"];
				$pending_requests = ($pending_requests_count == 0) ? "" : "<i class='icon-warning-sign'></i> $pending_requests_count";

				// Get the inactive members.
				$get_inactive_users_query = mysql_query("SELECT count(user.id) as users_count FROM user, member WHERE user.member_id = member.id AND user.first_login = '1' AND member.mobile != '0' AND member.fullname LIKE '%$root_fullname'");
				$inative_users_fetch = $get_inactive_users_query->fetch(PDO::FETCH_ASSOC);
				$inactive_users_count = $inative_users_fetch["users_count"];
				
				$moderators_html .= "<tr data-id='$moderator[id]' id='table_$moderator[id]' class='onerow'><td><input type='checkbox' name='check[$moderator[id]]' id='check_$moderator[id]' /></td><td><b><a href='familytree.php?id=$moderator[member_id]'>$moderator[username]</a></b><p class='hide-for-small'>($moderator_info[fullname])</p></td><td><div class='row'><div class='large-12 columns'><input type='text' name='moderator_root[$moderator[id]]' value='$root_fullname' class='sidrah-name-autocomplete' /></div></div></td><td class='hide-for-small'><center>$root_members_count</center></td><td class='hide-for-small'><center>$pending_requests</center></td><td class='hide-for-small'><center>$inactive_users_count</center></td></tr>";
			}
		}

		// Get the header.
		$header = website_header(
			"إدارة المشرفين",
			"صفحة من أجل إدارة المشرفين",
			array(
				"عائلة", main_tribe_name, "شجرة", "العائلة", "إدارة", "المشرفين"
			)
		);

		// Get the template of the page.
		$template = template(
				"views/manage_moderators.html",
				array(
					"moderators" => $moderators_html
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

