<?php

require_once("inc/functions.inc.php");

// Get the user information.
$user = user_information();

// Get variables.
$action = @$_GET["action"];

switch ($action)
{
	case "check_user_availability":

		// Check if the user is a visitor.
		if ($user["group"] == "visitor")
		{
			echo "MustLogin";
			return;
		}
	
		// Get variables.
		$username = trim(@$_GET["name"]);
		$new_username = trim(@$_GET["new_username"]);
		$name = trim(@$_GET["name"]);
		
		if (check_user_availability($username, $new_username, $name))
		{
			echo "Available";
			return;
		}
		else
		{
			echo "NotAvailable";
			return;
		}
	break;
	
	case "send_sms_message":

		$to = @$_POST["to"];
		$message = @$_POST["message"];
		$method = @$_POST["method"];
		$offset = (int) @$_POST["offset"];

		// Get the prepared relation depending on the id (to).
		$get_prepared_relation_query = $dbh->prepare("SELECT * FROM prepared_relation WHERE id = :to");
        $get_prepared_relation_query->bindParam(":to", $to);
        $get_prepared_relation_query->execute();

		if ($get_prepared_relation_query->rowCount() == 0)
		{
			return;
		}
		
		// Get the prepared relation information.
		$prepared_relation = $get_prepared_relation_query->fetch(PDO::FETCH_ASSOC);
		$query = base64_decode($prepared_relation["relation"]);

		if ($method == "count")
		{
			$mysql_query = mysql_query($query);
			echo $mysql_query->rowCount();
			return;
		}
		else if ($method == "offset")
		{
			// Send an SMS message.
			$mysql_query = $dbh->prepare(":query LIMIT 1 OFFSET :offset");
            $mysql_query->bindParam(":query", $query);
            $mysql_query->bindParam(":offset", $offset);
            $mysql_query->execute();

			$member = $mysql_query->fetch(PDO::FETCH_ASSOC);

			$message = preg_replace('/\{(.*)\}/e', '$member["$1"]', $message);

			$mobile = "966" . $member["member_mobile"];

			$status = send_sms(
				array($mobile), arabic_number($message)
			);

			echo $status;
			return;
		}

	break;
	
	case "get_request_update_code":
	
		$key = @$_GET["key"];
		
		// Check if the request update does exist.
		$get_request_query = $dbh->prepare("SELECT phpscript FROM request WHERE random_key = :key");
        $get_request_query->bindParam(":key", $key);
        $get_request_query->execute();
		
		if ($get_request_query->rowCount() == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		else
		{
			$fetch_request = $get_request_query->fetch(PDO::FETCH_ASSOC);
			
			echo "<!DOCTYPE><html><head><meta charset='utf8' /></head><body><pre>";
			echo $fetch_request["phpscript"];
			echo "</pre></body></html>";
		}
	
	break;
	
	case "upload_media":
		
		if ($user["group"] == "visitor")
		{
			$data = array(
				"status" => 0,
				"message" => "Not logged in."
			);
		}
		else
		{
			$media_title = @$_POST["media_title"];
			$media_is_event = @$_GET["media_is_event"];
			$event_id = @$_GET["event_id"];
			$media = @$_FILES["media_file"];

			// Check if the event does exists.
			$get_event_query = $dbh->prepare("SELECT * FROM event WHERE id = :event_id");
            $get_event_query->bindParam(":event_id", $event_id);
            $get_event_query->execute();

			$event_exist = $get_event_query->rowCount();

			if ($media_is_event == 1 && $event_exist == 0)
			{
				$data = array(
					"status" => 0,
					"message" => "Event id not found."
				);
			}
			else
			{
				if (!empty($media) && !empty($media_title))
				{
					$error = $media["error"];
			
					if ($error == UPLOAD_ERR_OK)
					{
						$tmp_name = $media["tmp_name"];
						$name = $media["name"];
						$size = filesize($tmp_name);
						$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
						$uniqename = uniqid() . ".$extension";
				
						// Check media extension.
						if (!in_array($extension, array("jpg", "jpeg", "png", "gif")))
						{
							$data = array(
								"status" => 0,
								"message" => "Media is not an image."
							);
						}
						else
						{
							// Check media size.
							if ($size > media_max_size * 1024)
							{
								$data = array(
									"status" => 0,
									"message" => "Media size is huge."
								);
							}
							else
							{
								$hash_file = hash_file("sha1", $tmp_name);
						
								// TODO: Check if the file already been uploaded.
					
								// Chcek if the file already uploaded.
								if (false)
								{
							
								}
								else
								{
									if ($extension == "jpg" || $extension == "jpeg")
									{
										$src = imagecreatefromjpeg($tmp_name);
									}
									else if ($extension == "png")
									{
										$src = imagecreatefrompng($tmp_name);
									}
									else
									{
										$src = imagecreatefromgif($tmp_name);
									}
						
									list($width, $height) = getimagesize($tmp_name);
						
									// Large media fixing.
									if ($width > media_large_width)
									{
										$ratio = media_large_width/$width;
										$new_width = media_large_width;
										$new_height = $height * $ratio;
									}
									else
									{
										$new_width = $width;
										$new_height = $height;
									}
						
									$large = imagecreatetruecolor($new_width, $new_height);
									$thumb = imagecreatetruecolor(media_thumb_width, media_thumb_height);
			
									// Resample large and thumb medias.
									imagecopyresampled($large, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
									imagecopyresampled($thumb, $large, 0, 0, 0, 0, media_thumb_width, media_thumb_height, $new_width, $new_height);
						
									// Create medias.
									imagejpeg($large, "views/medias/photos/large/{$uniqename}", 100);
									imagejpeg($thumb, "views/medias/photos/thumb/{$uniqename}", 100);
						
									// Get the file size.
									$filesize = filesize("views/medias/photos/large/{$uniqename}");
						
									// Destroy some.
									imagedestroy($src);
									imagedestroy($large);
									imagedestroy($thumb);
									
									// Get member information.
									$member = get_member_id($user["member_id"]);
						
									$now = time();
									
									// Insert media into media table.
									$insert_media_query = $dbh->prepare("INSERT INTO media (event_id, type, name, size, width, height, hash, title, author_id, created) VALUES (:event_id, 'photo', :uniqename, :filesize, :new_width, :new_height, :hash_file, :media_title, :user_member_id, :now)");
                                    $insert_media_query->bindParam(":event_id", $event_id);
                                    $insert_media_query->bindParam(":uniqename", $uniqename);
                                    $insert_media_query->bindParam(":filesize", $filesize);
                                    $insert_media_query->bindParam(":new_width", $new_width);
                                    $insert_media_query->bindParam(":new_height", $new_height);
                                    $insert_media_query->bindParam(":hash_file", $hash_file);
                                    $insert_media_query->bindParam(":media_title", $media_title);
                                    $insert_media_query->bindParam(":user_member_id", $user["member_id"]);
                                    $insert_media_query->bindParam(":now", $now);
                                    $insert_media_query->execute();

									$media_id = $dbh->lastInsertId();
						
									$data = array(
										"status" => 1,
										"message" => "Done.",
										"media" => array(
											"id" => $media_id,
											"name" => $name,
											"extension" => $extension,
											"uniqename" => $uniqename,
											"size" => $filesize,
											"width" => $new_width,
											"height" => $new_height,
											"author_id" => $user["member_id"],
											"author_username" => $user["username"],
											"author_fullname" => $member["fullname"],
										)
									);
									
									$media_link = "media.php?action=view_media&id=$media_id";
									$media_desc = "تم إضافة صورة جديدة: $media_title.";
									
									// Notify all users of inserting the media.
									notify_all("media_add", $media_desc, $media_link);
								}
							}
						}
					}
					else
					{
						$data = array(
							"status" => 0,
							"message" => "Media cannot be uploaded."
						);
					}
				}
				else
				{
					$data = array(
						"status" => 0,
						"message" => "No media selected."
					);
				}
			}
		}
		
		// Print the output.
		header("Content-Type: application/json");
		echo json_encode($data);
	break;
	
	case "get_node":
	
		$tribe_id = @$_GET["tribe_id"];
		$id = @$_GET["id"];

		// Get the member information from database.
		$get_member_query = $dbh->prepare("SELECT * FROM member WHERE tribe_id = :tribe_id AND id = :id");
        $get_member_query->bindParam(":tribe_id", $tribe_id);
        $get_member_query->bindParam(":id", $id);
        $get_member_query->execute();

		if ($get_member_query->rowCount() == 0)
		{
			$data = array(
				"status" => "failure"
			);
		}
		else
		{
			$member = $get_member_query->fetch(PDO::FETCH_ASSOC);

			// Get parent information.
			$parent = array(
				"id" => -1,
				"name" => ""
			);

			if ($member["father_id"] != -1)
			{
				$get_parent_query = $dbh->prepare("SELECT * FROM member WHERE id = :member_father_id");
                $get_parent_query->bindParam(":member_father_id", $member["father_id"]);
                $get_parent_query->execute();
				
				if ($get_parent_query->rowCount() > 0)
				{
					$parent_fetch = $get_parent_query->fetch(PDO::FETCH_ASSOC);
					
					$parent = array(
						"id" => $member["father_id"],
						"name" => $parent_fetch["name"],
						"photo" => $parent_fetch["photo"],
						"nickname" => $parent_fetch["nickname"],
					);
				}
			}
			
			$related_fullname = "";

			// Get the related name.
			if ($user["group"] == "moderator")
			{
				$user_info = get_user_id($user["id"]);
				$assigned_root_info = get_member_id($user_info["assigned_root_id"]);
	
				if ($assigned_root_info)
				{
					$related_fullname = $assigned_root_info["fullname"];
				}
			}

			$conditions = array("father_id = $member[id] ");
	
			if ($user["group"] == "visitor")
			{
				$is_admin = $is_me = $is_accepted_moderator = $is_relative_user = false;
			}
			else
			{
				// Check if the user is admin
				$is_admin = ($user["group"] == "admin");

				// Check if the user is seeing his/her profile.
				$is_me = ($member["id"] == $user["member_id"]);

				// Check if the moderator is accepted (if any).
				$is_accepted_moderator = is_accepted_moderator($member["id"]);

				// Check if the user is relative to the member.
				$is_relative_user = is_relative_user($member["id"]);
			}
			
			// Get the privacy.
			$display_daughters = privacy_display($member["id"], "daughters", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	
			switch ($user["group"])
			{
				case "visitor": case "user":
				{
					if ($display_daughters == false)
					{
						$conditions []= "gender = 1";
					}
				}
				break;

				case "moderator":
				{
					$conditions []= "(gender = 1 OR (gender = 0 AND fullname LIKE '%$related_fullname'))";
				}
				break;
			}

			$condition = implode("AND ", $conditions);

			// Get the children of the member.
			$get_children_query = $dbh->prepare("SELECT * FROM member WHERE $condition");
            $get_children_query->execute();

			$children = array();

			if ($get_children_query->rowCount() > 0)
			{
				while ($child = $get_children_query->fetch(PDO::FETCH_ASSOC))
				{
					// Get the number of children for this child.
					$get_child_children_query = $dbh->prepare("SELECT id, name FROM member WHERE father_id = :child_id");
                    $get_child_children_query->bindParam(":child_id", $child["id"]);
                    $get_child_children_query->execute();
				
					$children[$child["id"]] = array(
						"id" => $child["id"],
						"name" => $child["name"],
						"children_number" => $get_child_children_query->rowCount(),
						"photo" => $child["photo"],
						"nickname" => $child["nickname"],
					);
				}
			}

			$data = array(
		
				"parent" => $parent,
				"name" => $member["name"],
				"children" => $children,
				"status" => "success",
				"photo" => $member["photo"],
				"nickname" => $member["nickname"],
			);
		}
	
		// Print the output.
		header("Content-Type: application/json");
		echo json_encode($data);
	
	break;
	
	case "answer_ramadan_question":
	
		$submit = @$_POST["submit"];
		
		if (!empty($submit))
		{
			$question_id = @$_POST["question_id"];
			$answer = @$_POST["answer"];
			
			// Check if the question id does exist.
			$check_question_query = $dbh->prepare("SELECT id FROM ramadan_question WHERE id = :question_id");
            $check_question_query->bindParam(":question_id", $question_id);
            $check_question_query->execute();
			
			if ($check_question_query->rowCount() == 0)
			{
				echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
				return;
			}
			
			// Check if the answer is correct.
			if (!in_array($answer, range(1, 4)))
			{
				echo error_message("الرجاء اختيار إجابة من قائمة الاختيارات.");
				return;
			}
			
			// Check if the user already answered this question.
			$check_answered_query = $dbh->prepare("SELECT id FROM member_question WHERE member_id = :user_member_id AND question_id = :question_id");
            $check_answered_query->bindParam(":user_member_id", $user["member_id"]);
            $check_answered_query->bindParam(":question_id", $question_id);
            $check_answered_query->execute();
			
			if ($check_answered_query->rowCount() > 0)
			{
				echo error_message("لقد قمت بالإجابة على هذا السؤال مسبقاً.");
				return;
			}
			
			// Everything is alright.
			$now = time();
			$insert_answer_query = $dbh->prepare("INSERT INTO member_question (member_id, question_id, answer, created) VALUES (:user_member_id, :question_id, :answer, :now)");
            $insert_answer_query->bindParam(":user_member_id", $user["member_id"]);
            $insert_answer_query->bindParam(":question_id", $question_id);
            $insert_answer_query->bindParam(":answer", $answer);
            $insert_answer_query->bindParam(":now", $now);
            $insert_answer_query->execute();
			
			// Awesome.
			echo success_message(
				"شكراً لك، تم حفظ إجابتك في النظام.",
				"index.php"
			);
		}
	
	break;
	
	case "rotate_image":
		
		$type = @$_GET["type"];
		
		switch ($type)
		{
			case "media":
			
				$id = @$_GET["id"];
				
				// Check if the media exists.
				$get_media_query = $dbh->prepare("SELECT * FROM media WHERE id = :id");
                $get_media_query->bindParam(":id", $id);
                $get_media_query->execute();
				
				if ($get_media_query->rowCount() == 0)
				{
					echo error_message("لم يتم العثور على الصورة.");
					return;
				}
				
				$media = $get_media_query->fetch(PDO::FETCH_ASSOC);
				
				$large_file = "views/medias/photos/large/$media[name]";
				$thumb_file = "views/medias/photos/thumb/$media[name]";
				
				// Get the source of the media.
				$filearray = explode(".", $media["name"]);
				$extension = $filearray[1];
				
				if ($extension == "jpg" || $extension == "jpeg")
				{
					$thumb_src = imagecreatefromjpeg($thumb_file);
					$large_src = imagecreatefromjpeg($large_file);
				}
				else if ($extension == "png")
				{
					$thumb_src = imagecreatefrompng($thumb_file);
					$large_src = imagecreatefrompng($large_file);
				}
				else
				{
					$thumb_src = imagecreatefromgif($thumb_file);
					$large_src = imagecreatefromgif($large_file);
				}
				
				$angle = 90;
				
				$thumb_rotate = imagerotate($thumb_src, $angle, 0);
				$large_rotate = imagerotate($large_src, $angle, 0);
				
				if ($extension == "jpg" || $extension == "jpeg")
				{
					imagejpeg($thumb_rotate, $thumb_file, 100);
					imagejpeg($large_rotate, $large_file, 100);
				}
				else if ($extension == "png")
				{
					imagepng($thumb_rotate, $thumb_file, 9);
					imagepng($large_rotate, $large_file, 9);
				}
				else
				{
					imagegif($thumb_rotate, $thumb_file);
					imagegif($large_rotate, $large_file);
				}
				
				imagedestroy($thumb_rotate);
				imagedestroy($large_rotate);
				imagedestroy($thumb_src);
				imagedestroy($large_src);
				
				echo success_message(
					"تم تدوير الصورة بزاوية $angle.",
					"media.php?id=$id"
				);
				return;

			break;
			
			case "avatar":
			
			break;
		}
		
	break;
	
	default:
		echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
		return;
	break;
}
