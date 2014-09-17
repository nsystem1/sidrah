<?php

// First is first.
require_once("inc/functions.inc.php");

// Get all medias.
$get_medias_query = $dbh->prepare("SELECT * FROM media");
$dbh->execute();


while ($media = $get_medias_query->fetch(PDO::FETCH_ASSOC))
{
	// Get the count of media comments.
	$get_media_comments_query = $dbh->prepare("SELECT COUNT(id) AS comments_count FROM media_comment WHERE media_id = :media_id");
    $get_media_comments_query->bindParam(":media_id", $media["id"]);
    $get_media_comments_query->execute();

	$fetch_media_comments = $get_media_comments_query->fetch(PDO::FETCH_ASSOC);
	
	draw_comments_count_thumb($media["name"], $fetch_media_comments["comments_count"]);
	echo "$media[id]<br />";
}
