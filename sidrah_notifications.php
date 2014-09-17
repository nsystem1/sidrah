<?php

require_once("inc/functions.inc.php");

// Get the user information.
$user = user_information();

// Check if the user is not logged.
if ($user["group"] == "visitor")
{
	echo "لا يوجد معلومات.";
	return;
}

// Get the updates notifications for this user.
$notifications_count = get_notifications();

if ($notifications_count > 0)
{
	// Get the updates notifications.
	$get_updates_notifications_query = $dbh->prepare("SELECT * FROM notification WHERE user_id = :user_id AND is_read = '0' ORDER BY created ASC LIMIT 10");
    $get_updates_notifications_query->bindParam(":user_id", $user["id"]);
    $get_updates_notifications_query->execute();
	
	if ($get_updates_notifications_query->rowCount() > 0)
	{
		while ($update_notification = mysql_fetch_array($get_updates_notifications_query))
		{			
			echo "<div data-alert class='alert-box secondary'><a href='$update_notification[link]'>$update_notification[content]</a><a href='#' class='close'>&times;</a></div>";
			
			// Set this row to be read.
			$update_is_read = $dbh->prepare("UPDATE notification SET is_read = '1' WHERE id = :update_notification_id");
            $update_is_read->bindParam(":update_notification_id", $update_notification["id"]);
            $update_is_read->execute();
		}
	}
}
