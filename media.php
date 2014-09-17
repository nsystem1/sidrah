<?php

// TODO: Replace fetching in here.

require_once("inc/functions.inc.php");

// Get the user information.
$user = user_information();

if ($user["group"] == "visitor")
{
	redirect_to_login();
	return;
}

// Get the member information.
$member = get_member_id($user["member_id"]);
$action = @$_GET["action"];

switch ($action)
{
	default: case "view_media":
	
		// 
		$id = @$_GET["id"];

		// Get the media.
		$get_media_query = $dbh->prepare("SELECT media.*, member.fullname AS member_fullname, member.photo AS member_photo, member.gender AS member_gender, user.username FROM media, member, user WHERE media.author_id = member.id AND member.id = user.member_id AND media.id = :id");
        $get_media_query->bindParam(":id", $id);
        $get_media_query->execute();

		if ($get_media_query->rowCount() == 0)
		{
			echo error_message("لم يتم العثور على الصورة.");
			return;
		}

		// Get the media
		$media = $get_media_query->fetch(PDO::FETCH_ASSOC);

		// Update the views of the media.
		$update_media_views_query = $dbh->prepare("UPDATE media SET views = views+1 WHERE id = :media_id");
        $update_media_views_query->bindParam(":media_id", $media["id"]);
        $update_media_views_query->execute();

		// Get the event.
		$get_event_query = $dbh->prepare("SELECT * FROM event WHERE id = :media_event_id");
        $get_event_query->bindParam(":media_event_id", $media["event_id"]);
        $get_event_query->execute();

		if ($get_event_query->rowCount() == 0)
		{
			$event["id"] = -1;
			$event["title"] = "";
		}
		else
		{
			$event = $get_event_query->fetch(PDO::FETCH_ASSOC);
		}
		
		// Get the media likes.
		$get_media_likes_query = $dbh->prepare("SELECT COUNT(id) as media_likes FROM media_reaction WHERE media_id = :media_id AND reaction = 'like'");
        $get_media_likes_query->bindParam(":media_id", $media["id"]);
        $get_media_likes_query->execute();

		$fetch_media_likes = $get_media_likes_query->fetch(PDO::FETCH_ASSOC);

		
		// Get the tagmembers in media.
		$get_tagmember_query = $dbh->prepare("SELECT member.id AS member_id, member.fullname, tagmember.* FROM member, tagmember WHERE tagmember.member_id = member.id AND tagmember.type = 'media' AND tagmember.content_id = :media_id");
        $get_tagmember_query->bindParam(":media_id", $media["id"]);
        $get_tagmember_query->execute();

		$tagmembers_string = "";

		if ($get_tagmember_query->rowCount() > 0)
		{
			while ($tagmember = mysql_fetch_array($get_tagmember_query))
			{
				$tagmembers_string .= "tagmembers[$tagmember[member_id]] = {name: '$tagmember[fullname]'};\n";
			}
		}
		
		$can_like = media_member_can_like($media["id"], $member["id"]);
		$media_like = "";
		
		if ($can_like == true)
		{
			$media_like = "<a href='media.php?action=like_media&id=$media[id]' title='هل أعجبتك الصورة؟' id='media_like' class='small button'>أعجبتني</a>";
		}
		
		// Check if the media has an event.
		$get_media_event_query = $dbh->prepare("SELECT id, title FROM event WHERE id = :media_event_id");
        $get_media_event_query->bindParam(":media_event_id", $media["event_id"]);
        $get_media_event_query->execute();

		$media_event = "";
		
		if ($get_media_event_query->rowCount() > 0)
		{
			$fetch_media_event = $get_media_event_query->fetch(PDO::FETCH_ASSOC);
			$media_event = "<a href='calendar.php?action=view_event&id=$event[id]'>$event[title]</a> ";
		}
		
		// Get the previous media.
		$get_previous_media_query = $dbh->prepare("SELECT id, title FROM media WHERE event_id = :media_event_id AND id < :media_id ORDER BY id DESC");
        $get_previous_media_query->bindParam(":media_event_id", $media["event_id"]);
        $get_previous_media_query->bindParam(":media_id", $media["id"]);
        $get_previous_media_query->execute();

		$previous_media = "";
		
		if ($get_previous_media_query->rowCount() > 0)
		{
			$fetch_previous_media = $get_previous_media_query->fetch(PDO::FETCH_ASSOC);
			$previous_media = "<a class='small button secondary' href='media.php?id=$fetch_previous_media[id]' title='$fetch_previous_media[title]'>السابق</a>";
		}
		
		// Get the next media.
		$get_next_media_query = $dbh->prepare("SELECT id, title FROM media WHERE event_id = :media_event_id AND id > :media_id");
        $get_next_media_query->bindParam(":media_event_id", $media["event_id"]);
        $get_next_media_query->bindParam(":media_id", $media["id"]);
        $get_next_media_query->execute();

		$next_media = "";
		
		if ($get_next_media_query->rowCount() > 0)
		{
			$fetch_next_media = $get_next_media_query->fetch(PDO::FETCH_ASSOC);
			$next_media = "<a class='small button secondary' href='media.php?id=$fetch_next_media[id]' title='$fetch_next_media[title]'>التالي</a>";
		}
		
		$delete_media = "";
		$rotate_media = "";
		
		if ($user["group"] == "admin" || $user["member_id"] == $media["author_id"])
		{
			$delete_media = "<a href='media.php?action=delete_media&media_id=$media[id]' class='small button alert'>حذف</a>";
			$rotate_media = "<a href='sidrah_ajax.php?action=rotate_image&type=media&id=$media[id]' class='small button success'>تدوير 90º</a>";
		} 
		
		// Get the creatde date for this media.
		$created = arabic_date(date("d M Y, H:i:s", $media["created"]));

		// Get the header.
		$header = website_header(
			$media["title"],
			$media["description"]
		);

		// Get the footer.
		$footer = website_footer();

		// Get the content.
		$content = template(
			"views/single_media.html",
			array(
				"media_id" => $media["id"],
				"media_name" => $media["name"],
				"media_title" => $media["title"],
				"media_description" => $media["description"],
				"media_views" => $media["views"],
				"media_likes" => $fetch_media_likes["media_likes"],
				"media_like" => $media_like,
				"author_username" => $media["username"],
				"author_id" => $media["author_id"],
				"author_fullname" => $media["member_fullname"],
				"author_photo" => rep_photo($media["member_photo"], $media["member_gender"], "avatar"),
				"author_shorten_name" => shorten_name($media["member_fullname"]),
				"media_event" => $media_event,
				"previous_media" => $previous_media,
				"next_media" => $next_media,
				"comments" => get_media_comments($media["id"], $comments_count, $member["id"]),
				"comments_count" => $comments_count,
				"tagmembers" => $tagmembers_string,
				"created" => $created,
				"delete_media" => $delete_media,
				"rotate_media" => $rotate_media
			)
		);

		// Print the page.
		echo $header;
		echo $content;
		echo $footer;

	break;
	
	case "like_media":
	
		// Get the id.
		$id = @$_GET["id"];
		$can_like = media_member_can_like($id, $member["id"]);
		
		if ($can_like == false)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the media.
		$get_media_query = $dbh->prepare("SELECT * FROM media WHERE id = :id");
        $get_media_query->bindParam(":id", $id);
        $get_media_query->execute();

		
		if ($get_media_query->rowCount() == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the media.
		$media = $get_media_query->fetch(PDO::FETCH_ASSOC);
		$now = time();
		
		// Insert the like.
		$insert_media_like_query = $dbh->prepare("INSERT INTO media_reaction (media_id, member_id, reaction, created) VALUES (:media_id, :member_id, 'like', :now)");
        $insert_media_like_query->bindParam(":media_id", $media["id"]);
        $insert_media_like_query->bindParam(":member_id", $member["id"]);
        $insert_media_like_query->bindParam(":now", $now);
        $insert_media_like_query->execute();


		// Get the user of the media.
		$get_media_user_query = $dbh->prepare("SELECT * FROM user WHERE member_id = :media_author_id");
        $get_media_user_query->bindParam(":media_author_id", $media["author_id"]);
        $get_media_user_query->execute();


		if ($get_media_user_query->rowCount() > 0)
		{
			$fetch_media_user = $get_media_user_query->fetch(PDO::FETCH_ASSOC);
			
			if ($user["id"] != $fetch_media_user["id"])
			{
				// Set the notification.
				$desc = "$user[username] أُعجب بالصورة: $media[title].";
				$link = "media.php?action=view_media&id=$media[id]";
		
				// Notify the author of the media.
				notify("media_like", $fetch_media_user["id"], $desc, $link);
			}
		}

		// Done.
		echo success_message(
				"تم تسجيل إعجابك بالصورة، شكراً لك.",
			"media.php?action=view_media&id=$media[id]"
		);
	
	break;
	
	case "add_comment":
	
		$media_id = @$_GET["media_id"];
		
		// Check if the media does exist.
		$get_media_query = $dbh->prepare("SELECT * FROM media WHERE id = :media_id");
        $get_media_query->bindParam(":media_id", $media_id);
        $get_media_query->execute();

		
		if ($get_media_query->rowCount() == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the media.
		$media = $get_media_query->fetch(PDO::FETCH_ASSOC);
		
		// TODO: Check if the commenting on the media is available.
		
		// Post.
		$submit = @$_POST["submit"];
		
		if (!empty($submit))
		{

			// Do some cleaning for the comment (XSS stuff).
			$content = trim(@strip_tags($_POST["content"]));

			// Insert the comment.
			$now = time();
			$insert_media_comment_query = $dbh->prepare("INSERT INTO media_comment (media_id, content, author_id, created) VALUES (:media_id, :content, :member_id, :now)");
            $insert_media_comment_query->bindParam(":media_id", $media["id"]);
            $insert_media_comment_query->bindParam(":content", $content);
            $insert_media_comment_query->bindParam(":member_id", $member["id"]);
            $insert_media_comment_query->bindParam(":now", $now);
            $insert_media_comment_query->execute();

			$inserted_comment_id = $dbh->lastInsertId();
			
			// Get the count of media comments.
			$get_media_comments_query = $dbh->prepare("SELECT COUNT(id) AS comments_count FROM media_comment WHERE media_id = :media_id");
            $get_media_comments_query->bindParam(":media_id", $media["id"]);
            $get_media_comments_query->execute();

			$fetch_media_comments = $get_media_comments_query->fetch(PDO::FETCH_ASSOC);
			
			// Update the media thumb to be with comments count.
			draw_comments_count_thumb($media["name"], $fetch_media_comments["comments_count"]);

			// Set a variable to hold notify/not-notify user ids.
			$notify_user_ids = array();
			$not_notify_user_ids = array();
		
			// Do not notify the author of the comment.
			//$not_notify_user_ids []= $user["id"];
		
			// Get the author id of the media.
			$get_media_author_query = $dbh->prepare("SELECT id FROM user WHERE member_id = :media_author_id");
            $get_media_author_query->bindParam(":media_author_id", $media["author_id"]);
            $get_media_author_query->execute();

			$fetch_media_author = $get_media_author_query->fetch(PDO::FETCH_ASSOC);
			$media_author_user_id = $fetch_media_author["id"];
			
			// Check if the author of the media is not the same with the author of the comment.
			if ($media_author_user_id != $user["id"])
			{
				$notify_user_ids []= $media_author_user_id;
			}
			
			// Set the other condition.
			$not_in_users_condition = "";
			
			// Do not notify these people.
			$not_notify_user_ids = $notify_user_ids;
			$not_notify_user_ids []= $user["id"];
			
			if (count($not_notify_user_ids) > 0)
			{
				$not_in_users = implode(", ", $not_notify_user_ids);
				$not_in_users_condition = "AND user.id NOT IN ($not_in_users)";
			}
			
			// Get the comments before this comment.
			$get_users_before_query = $dbh->prepare("SELECT DISTINCT user.id AS id FROM media_comment, user WHERE media_comment.author_id = user.member_id AND media_comment.media_id = :media_id AND media_comment.created < :now :not_in_users_condition");
            $get_users_before_query->bindParam(":media_id", $media["id"]);
            $get_users_before_query->bindParam(":now", $now);
            $get_users_before_query->bindParam(":not_in_users_condition", $not_in_users_condition);
            $get_users_before_query->execute();

			$users_before_count = $get_users_before_query->rowCount();
			
			if ($users_before_count > 0)
			{
				while ($users_before = mysql_fetch_array($get_users_before_query))
				{
					$notify_user_ids []= $users_before["id"];
				}
			}
			
			// Set the notification.
			$desc = "تعليق جديد على صورة: $media[title]";
			$link = "media.php?action=view_media&id=$media[id]#comment_$inserted_comment_id";
			
			// Notify related users.
			notify_many("media_comment_response", $desc, $link, $notify_user_ids);

			// Done.
			echo success_message(
				"تم إضافة التعليق بنجاح، شكراً لك.",
				"media.php?action=view_media&id=$media[id]"
			);
		}
		else
		{
			// Only post page.
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
	break;
	
	case "delete_comment":
		
		$comment_id = @$_GET["comment_id"];
		
		// Check if the comment exits.
		$get_comment_query = mysql_query("SELECT * FROM media_comment WHERE id = '$comment_id'")or die(mysql_error());
		
		if (mysql_num_rows($get_comment_query) == 0)
		{
			echo error_message("لم يتم العثور على التعليق.");
			return;
		}
		
		$comment = $get_comment_query->fetch(PDO::FETCH_ASSOC);
		
		// Get the media.
		$get_media_query = $dbh->prepare("SELECT * FROM media WHERE id = :comment_media_id");
        $get_media_query->bindParam(":comment_media_id", $comment["media_id"]);
        $get_media_query->execute();

		$media = $get_media_query->fetch(PDO::FETCH_ASSOC);
		
		// Check if the comment author is the logged in user,
		// Or if the user is admin or moderator.
		if ($user["group"] == "admin" || $user["group"] == "moderator" || $user["member_id"] == $comment["author_id"])
		{
			// Delete related likes.
			$delete_likes_query = $dbh->prepare("DELETE FROM media_comment_like WHERE media_comment_id = :comment_id");
            $delete_likes_query->bindParam(":comment_id", $comment["id"]);
            $delete_likes_query->execute();

			
			// Then, delete the comment itsef.
			$delete_comment_query = $dbh->prepare("DELETE FROM media_comment WHERE id = :comment_id");
            $delete_comment_query->bindParam(":comment_id", $comment["id"]);
            $delete_comment_query->execute();

			
			// Count the comments, and update the media.
			$get_media_comments_query = $dbh->prepare("SELECT COUNT(id) AS comments_count FROM media_comment WHERE media_id = :media_id");
            $get_media_comments_query->bindParam(":media_id", $media["id"]);
            $get_media_comments_query->execute();

			$fetch_media_comments = $get_media_comments_query->fetch(PDO::FETCH_ASSOC);

			// Update the comments count written on thumb.
			draw_comments_count_thumb($media["name"], $fetch_media_comments["comments_count"]);
			
			// Done.
			echo success_message(
				"تم حذف التعليق بنجاح.",
				"media.php?action=view_media&id=$comment[media_id]"
			);
		}
		else
		{
			echo error_message("لا يمكنك حذف التعليق.");
			return;
		}
		
	break;
	
	case "like_comment":
	
		$comment_id = @$_GET["comment_id"];
		
		// Check if the comment does exist.
		$get_comment_query = $dbh->prepare("SELECT media_comment.*, media.title AS media_title FROM media_comment, media WHERE media.id = media_comment.media_id AND media_comment.id = :comment_id");
        $get_comment_query->bindParam(":comment_id", $comment_id);
        $get_comment_query->execute();
		
		if ($get_comment_query->rowCount() == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the event.
		$comment = $get_comment_query->fetch(PDO::FETCH_ASSOC);
		
		// Check if the member has liked this comment before.
		$get_member_likes_query = $dbh->prepare("SELECT * FROM media_comment_like WHERE media_comment_id = :comment_id AND member_id = :member_id");
        $get_member_likes_query->bindParam(":comment_id", $comment["id"]);
        $get_member_likes_query->bindParam(":member_id", $member["id"]);
        $get_member_likes_query->execute();

		if ($get_member_likes_query->rowCount() > 0)
		{
			echo error_message("لا يمكنك أن تسجل إعجابك على التعليق مرّة أخرى.");
			return;
		}
		
		// Check if the member is the author of the comment.
		if ($member["id"] == $comment["author_id"])
		{
			echo error_message("لا يمكنك أن تسجل إعجابك بتعليقك.");
			return;
		}
		
		$now = time();

		$like_comment_query = $dbh->prepare("INSERT INTO media_comment_like (media_comment_id, member_id, created) VALUES (:comment_id, :member_id, :now)");
        $like_comment_query->bindParam(":comment_id", $comment["id"]);
        $like_comment_query->bindParam(":member_id", $member["id"]);
        $like_comment_query->bindParam(":now", $now);
        $like_comment_query->execute();
		
		// Set the notification.
		$desc = "$user[username] أُعجب بتعليقك على صورة: $comment[media_title].";
		$link = "media.php?action=view_media&id=$comment[media_id]#comment_$comment[id]";
		
		// Get the user id of the comment author.
		$get_comment_user_query = $dbh->prepare("SELECT id FROM user WHERE member_id = :comment_author_id");
        $get_comment_user_query->bindParam(":comment_author_id", $comment["author_id"]);
        $get_comment_user_query->execute();

		$fetch_comment_user = $get_comment_user_query->fetch(PDO::FETCH_ASSOC);
		
		// Notify the commenter.
		notify("media_comment_like", $fetch_comment_user["id"], $desc, $link);
		
		// Done.
		echo success_message(
			"تم تسجيل إعجابك بالتعليق، شكراً لك.",
			"media.php?action=view_media&id=$comment[media_id]"
		);
	break;
	
	case "delete_media":
	
		$media_id = @$_GET["media_id"];
		
		// Check if the media does exist.
		$get_media_query = $dbh->prepare("SELECT * FROM media WHERE id = :media_id");
        $get_media_query->bindParam(":media_id", $media_id);
        $get_media_query->execute();

		if ($get_media_query->rowCount() == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the media.
		$media = $get_media_query->fetch(PDO::FETCH_ASSOC);
		
		// Check if the user can delete the media.
		if ($user["group"] == "admin" || $user["member_id"] == $media["author_id"])
		{
			// Delete the media.
			$delete_media_query = mysql_query("DELETE FROM media WHERE id = '$media[id]'")or die(mysql_error());
			
			// Delete the reactions of media.
			$delete_media_reactions_query = mysql_query("DELETE FROM media_reaction WHERE media_id = '$media[id]'")or die(mysql_error());
			
			// Delete the likes of comments for media.
			$delete_media_comment_likes_query = mysql_query("DELETE FROM media_comment_like WHERE media_comment_id IN (SELECT id FROM media_comment WHERE media_id  = '$media[id]')")or die(mysql_error());
			
			// Delete the comments of media.
			$delete_media_comments_query = mysql_query("DELETE FROM media_comment WHERE media_id = '$media[id]'")or die(mysql_error());
			
			// Delete the tagmembers related.
			$delete_tagmembers_query = mysql_query("DELETE FROM tagmember WHERE type = 'media' AND content_id = '$media[id]'")or die(mysql_error());
			
			// Delete the files (large/thumb) also.
			unlink("views/medias/photos/large/$media[name]");
			unlink("views/medias/photos/thumb/$media[name]");
			
			// Done.
			echo success_message(
				"تم حذف الصورة بنجاح، شكراً لك.",
				"index.php"
			);
		}
		else
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
	break;
	
	case "update_tagmembers":

		$id = @$_GET["id"];
		$tagmembers = @$_POST["tagmembers"];
		
		// Check if the media does exist.
		$get_media_query = $dbh->prepare("SELECT * FROM media WHERE id = :id");
        $get_media_query->bindParam(":id", $id);
        $get_media_query->execute();
		
		if ($get_media_query->rowCount() == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the media.
		$media = $get_media_query->fetch(PDO::FETCH_ASSOC);

		// Get the already added tagmembers.
		$get_tagmembers_query = $dbh->prepare("SELECT * FROM tagmember WHERE type = 'media' AND content_id = :media_id");
        $get_tagmembers_query->bindParam(":media_id", $media["id"]);
        $get_tagmembers_query->execute();

		$already_tagmembers = array();

		if ($get_tagmembers_query->rowCount() > 0)
		{
			while ($tm = mysql_fetch_array($get_tagmembers_query))
			{
				$already_tagmembers[]= $tm["member_id"];
			}
		}

		if (empty($tagmembers) && count($already_tagmembers) == 0)
		{
			echo error_message("الرجاء إدخال اسم واحد على الأقل.");
			return;
		}
		
		$tagmembers_array = explode(",", $tagmembers);
		
		foreach ($already_tagmembers as $already_tagmember)
		{
			if (!in_array($already_tagmember, $tagmembers_array))
			{
				$delete_member_tagmember_query = $dbh->prepare("DELETE FROM tagmember WHERE type = 'media' AND content_id = :media_id AND member_id = :already_tagmember");
                $delete_member_tagmember_query->bindParam(":media_id", $media["id"]);
                $delete_member_tagmember_query->bindParam(":already_tagmember", $already_tagmember);
                $delete_member_tagmember_query->execute();
			}
		}
		
		$now = time();
		
		foreach ($tagmembers_array as $tagmember)
		{
			if (!in_array($tagmember, $already_tagmembers))
			{
                $insert_member_tagmember_query = $dbh->prepare("INSERT INTO tagmember (type, content_id, member_id, created) VALUES ('media', :media_id, :tagmember, :now)");
                $insert_member_tagmember_query->bindParam(":media_id", $media["id"]);
                $insert_member_tagmember_query->bindParam(":tagmember", $tagmember);
                $insert_member_tagmember_query->bindParam(":now", $now);
                $insert_member_tagmember_query->execute();
			}
		}
		
		// Done.
		echo success_message(
			"تم إضافة الأسماء بنجاح.",
			"media.php?id=$media[id]"
		);
	break;
}
