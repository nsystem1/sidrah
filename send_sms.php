<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

if ($user["group"] != "admin")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

// Get the header
$header = website_header(
		"إرسال SMS",
		"صفحة من أجل إرسال SMS.",
		array(
			"إرسال", "SMS", "عائلة", main_tribe_name
		)
);

// Get the footer.
$footer = website_footer();

// Set the TOS.
$tos = "";

// Get the prepared relations.
$get_prepared_relations_query = $dbh->prepare("SELECT * FROM prepared_relation ORDER BY id DESC");
$get_prepared_relations_query->execute();

if ($get_prepared_relations_query->rowCount() > 0)
{
	while ($prepared_relation = $get_prepared_relations_query->fetch(PDO::FETCH_ASSOC))
	{
		$query = base64_decode($prepared_relation["relation"]);
		$query = str_replace("relation_table.*", "COUNT(member_id) AS counts", $query);
		
		// Execute the query.
		$execute_query = $dbh->prepare($query);
        $execute_query->execute();

		$fetch_query = $execute_query->fetch(PDO::FETCH_ASSOC);
		$count = $fetch_query["counts"];

		$tos .= "<option value='$prepared_relation[id]'>$prepared_relation[name] ($count)</option>";
	}
}

$balance = check_balance();

// Get the content.
$content = template(
	"views/send_sms.html",
	array(
		"current" => number_format($balance["current"]),
		"total" => number_format($balance["total"]),
		"tos" => $tos
	)
);
 
// Print the page.
echo $header;
echo $content;
echo $footer;

