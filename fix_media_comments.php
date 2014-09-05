<?php

// First is first.
require_once("inc/functions.inc.php");

// Get all medias.
$get_medias_query = $dbh->prepare("SELECT * FROM media");
$dbh->execute();


while ($media = mysql_fetch_array($get_medias_query))
{
	// Get the count of media comments.
	$get_media_comments_query = $dbh->prepare("SELECT COUNT(id) AS comments_count FROM media_comment WHERE media_id = :media_id");
$dbh->bindParam(":media_id", $media["id"]);
$dbh->execute();

	$fetch_media_comments = mysql_fetch_array($get_media_comments_query);
	
	draw_comments_count_thumb($media["name"], $fetch_media_comments["comments_count"]);
	echo "$media[id]<br />";
}
