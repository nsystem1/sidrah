<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

// Set the values of Ramadan question.
$ramadan_question = "";

if ($user["group"] == "visitor")
{
	
	$inside = template("views/inside_not_logged.html");
}
else
{	
	// Get the media
	$media = media();

	// Get the current day.
	$time = time();
	$events_limit = 10;
			
	$miladi_day = date("d", $time);
	$miladi_month = date("m", $time);
	$miladi_year = date("Y", $time);

	$hijri_date = date_info($miladi_day, $miladi_month, $miladi_year);

	$current_hijri_day = $hijri_date["hijri_day"];
	$current_hijri_month = $hijri_date["hijri_month"];
	$current_hijri_year = $hijri_date["hijri_year"];
	
	// Set the date of today as an int.
	$now_date_int = $current_hijri_day + ($current_hijri_month*29) + ($current_hijri_year*355);
		
	// Get the current events.
	$get_current_events_query = $dbh->prepare("SELECT * FROM event WHERE (day + month*29 + year*355) = :now_date_int ORDER BY id DESC LIMIT :events_limit");
	$get_current_events_query->bindParam(":now_date_int", $now_date_int);
	$get_current_events_query->bindParam(":events_limit", $events_limit);
	$get_current_events_query->execute();

	$current_events_count = $get_current_events_query->rowCount();
		
	if ($current_events_count == 0)
	{
		$current_events = "<li>لا يوجد مناسبات حاليّة.</li>";
	}	
	else
	{
		$current_events = "<ul>";
		
		while ($current_event = $get_current_events_query->fetch(PDO::FETCH_ASSOC))
		{
			$current_events .= "<li><a href='calendar.php?action=view_event&id=$current_event[id]' class='whitelink'>$current_event[title]</a> <span class='datetime'>(في $current_event[day]/$current_event[month]/$current_event[year])</span></li>";
		}
		
		$current_events .= "</ul>";
	}
	
	// Get the future events.
	$get_future_events_query = $dbh->prepare("SELECT * FROM event WHERE (day + month*29 + year*355) > :now_date_int ORDER BY id DESC LIMIT :events_limit");
	
	$get_future_events_query->bindParam(":now_date_int", $now_date_int);
	$get_future_events_query->bindParam(":events_limit", $events_limit);
	
	$get_future_events_query->execute();

	$future_events_count = $get_future_events_query->rowCount();
	
	if ($future_events_count == 0)
	{
		$future_events = "<li>لا يوجد مناسبات مستقبليّة.</li>";
	}	
	else
	{
		$future_events = "<ul>";
		
		while ($future_event = $get_future_events_query->fetch(PDO::FETCH_ASSOC))
		{
			$future_events .= "<li><a href='calendar.php?action=view_event&id=$future_event[id]' class='whitelink'>$future_event[title]</a> <span class='datetime'>(في $future_event[day]/$future_event[month]/$future_event[year])</span></li>";
		}
		
		$future_events .= "</ul>";
	}

	// Get the past events.
	$get_past_events_query = $dbh->prepare("SELECT * FROM event WHERE (day + month*29 + year*355) < :now_date_int ORDER BY id DESC LIMIT :events_limit");

	$get_past_events_query->bindParam(":now_date_int", $now_date_int);
	$get_past_events_query->bindParam(":events_limit", $events_limit);
	
	$get_past_events_query->execute();

	$past_events_count = $get_past_events_query->rowCount();
		
	if ($past_events_count == 0)
	{
		$past_events = "<li>لا يوجد مناسبات ماضية.</li>";
	}	
	else
	{
		$past_events = "<ul>";
		
		while ($past_event = $get_past_events_query->fetch(PDO::FETCH_ASSOC))
		{
			$past_events .= "<li><a href='calendar.php?action=view_event&id=$past_event[id]' class='whitelink'>$past_event[title]</a> <span class='datetime'>(في $past_event[day]/$past_event[month]/$past_event[year])</span></li>";
		}
			
		$past_events .= "</ul>";
	}
	
	$inside = template(
		"views/dashboard.html",
		array(
			"media" => $media,
			"current_events" => $current_events,
			"future_events" => $future_events,
			"past_events" => $past_events
		)
	);
	
	$congratulations = "";
}

$ramadan_question = "";
$logged = logged_in_box();
$media = media();

// Get the template of the main page.
$content = template(
	"views/main_page.html",
	array(
		"logged" => $logged,
		"inside" => $inside,
		"ramadan_question" => $ramadan_question,
		"version" => version
	)
);

echo $content;

