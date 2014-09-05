<?php

require_once("inc/functions.inc.php");

$user = user_information();

// Check if the user is not admin
if ($user["group"] != "admin")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

$submit = @$_POST["submit"];
$find_words = @$_POST["find_words"];
$replace_with_word = @$_POST["replace_with_word"];

if (!empty($submit))
{
	$s_words = explode("،", $find_words);
	$clean_words = array();
	
	// Walk up-onto s_words.
	foreach ($s_words as $s_word)
	{
		$s_word = trim($s_word);
		
		if (!empty($s_word))
		{
			$clean_words []= "'$s_word'";	
		}
	}
	
	// Get the company to be replaced with.
	$gcrwithquery = $dbh->prepare("SELECT * FROM company WHERE name = :replace_with_word");
$dbh->bindParam(":replace_with_word", $replace_with_word);
$dbh->execute();

	
	if (mysql_num_rows($gcrwithquery) == 0)
	{
		$gcrfetch["name"] = "";
		$gcrfetch["id"] = -1;
	}
	else
	{
		$gcrfetch = $gcrwithquery->fetch(PDO::FETCH_ASSOC);
	}
	
	// Delete all companies and update members of them.
	foreach ($clean_words as $clean_word)
	{
		$gcw_query = $dbh->prepare("SELECT id, name FROM company WHERE name = :clean_word");
$dbh->bindParam(":clean_word", $clean_word);
$dbh->execute();

		
		if (mysql_num_rows($gcw_query) == 0)
		{
			continue;
		}
		
		$gcw_fetch = $gcw_query->fetch(PDO::FETCH_ASSOC);
		
		// Delete this company.
		$delete_company_query = $dbh->prepare("DELETE FROM company WHERE id = :gcw_fetch_id");
$dbh->bindParam(":gcw_fetch_id", $gcw_fetch["id"]);
$dbh->execute();

		
		// Update all members.
		$update_members_query = $dbh->prepare("UPDATE member SET company_id = :gcrfetch_id WHERE company_id = :gcw_fetch_id");
$dbh->bindParam(":gcrfetch_id", $gcrfetch["id"]);
$dbh->bindParam(":gcw_fetch_id", $gcw_fetch["id"]);
$dbh->execute();

		
	}

	echo success_message(
		"تم تحديث جهات العمل."
	);
	
	return;
}
else
{
	$header = website_header(
		"استبدال جهات العمل",
		"صفحة من أجل استبدال استبدال جهات العمل.",
		array()
	);

	$footer = website_footer();

	echo $header;

	?>
	<form action="fix_companies.php" method="post" class="inputform">

	<p>
		<label>ابحث عن الكلمات</label>
		<input type="text" name="find_words" />
		<div class="clear"></div>
	</p>
	<p>
		<label>استبدلها بالكلمة</label>
		<input type="text" name="replace_with_word" />
		<div class="clear"></div>
	</p>
	<p class="controls">
		<button class="submit" type="submit" name="submit" value="1"><i class="icon-ok icon-white"></i> استبدال</button>
	</p>

	<?php

	// Get companies.
	$gcq = $dbh->prepare("SELECT id, name FROM company ORDER BY LENGTH(name) ASC");
$dbh->execute();


	while ($fc = mysql_fetch_array($gcq))
	{
	/*
		$name = normalize_name($fc["name"]);
	
		// Check if the name is empty.
		if (empty($name)) continue;
	
		// Get similar.
		//$escaped_name = escape_confusing($name);
		$words = explode(" ", $name);
	
		//echo $escaped_name;
		$escaped_words = array();
	
		foreach ($words as $word)
		{
			$escaped_words []= "name REGEXP '" . escape_confusing($word) . "'";
		}
	
		$condition = implode(" AND ", $escaped_words);
	
		$get_similar_query = $dbh->prepare("SELECT id, name FROM company WHERE (:condition) AND name != :fc_name");
$dbh->bindParam(":condition", $condition);
$dbh->bindParam(":fc_name", $fc["name"]);
$dbh->execute();

		$related_rows_count = mysql_num_rows($get_similar_query);

		if ($related_rows_count > 0)
		{
			echo "<h2>$fc[name]</h2>";
		
			while ($rc = mysql_fetch_array($get_similar_query))
			{
				echo "$rc[name], ";
			}
			
			echo "<br />";
		}
	*/
		echo "$fc[name]<br />";
	}
	
	echo "</form>";
	echo $footer;
}
