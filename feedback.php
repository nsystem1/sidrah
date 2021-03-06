<?php

// Start session for this captcha.
session_start();

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

// Submit
$submit = @$_POST["submit"];

if (!empty($submit))
{
	$type = trim(@$_POST["type"]);
	$page = trim(@$_POST["page"]);
	$content = trim(@$_POST["content"]);
	$captcha = trim(@$_POST["captcha"]);
	
	if (empty($type) || empty($page) || empty($content) || empty($captcha))
	{
		echo error_message("الرجاء إدخال الحقول المطلوبة.");
		return;
	}
	
	$sha1_captcha = sha1_salt($captcha);
	
	// Check if the captcha is missed up.
	if ($_SESSION["feedback"] != $sha1_captcha)
	{
		echo error_message("الرجاء إدخال رمز التحقّق بشكل صحيح.");
		return;
	}
	
	$user_agent = @$_SERVER["HTTP_USER_AGENT"];
	$http_referer = @$_SERVER["HTTP_REFERER"];
	$now = time();
	
	if ($user["group"] != "visitor")
	{
		$user_agent .= "; $user[username];";
	}
	
	// Insert a new feedback.
	$insert_feedback_query = $dbh->prepare("INSERT INTO feedback (type, page, content, user_agent, http_referer, created) VALUES (:type, :page, :content, :user_agent, :http_referer, :now)");
    $insert_feedback_query->bindParam(":type", $type);
    $insert_feedback_query->bindParam(":page", $page);
    $insert_feedback_query->bindParam(":content", $content);
    $insert_feedback_query->bindParam(":user_agent", $user_agent);
    $insert_feedback_query->bindParam(":http_referer", $http_referer);
    $insert_feedback_query->bindParam(":now", $now);
    $insert_feedback_query->execute();
	
	echo success_message(
		"شكراً لك على إخبارنا برأيك حول الموقع.",
		"index.php"
	);
	
	return;
}
else
{
	// Get page.
	$page = @$_GET["page"];

	// Get the header.
	$header = website_header(
		"أخبرنا برأيك",
		"صفحة من أجل الحصول على آراء الزوّار حول الموقع.",
		array(
			"عائلة", main_tribe_name, "شجرة", "أخبرنا", "برأيك"
		)
	);

	// Get the content.
	$content = template(
		"views/feedback.html",
		array(
			"page" => $page
		)
	);

	// Get the footer.
	$footer = website_footer();

	// Print the page.
	echo $header;
	echo $content;
	echo $footer;
}
