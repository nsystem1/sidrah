<?php

require_once("inc/functions.inc.php");

// Get all members.
$get_members_query = $dbh->prepare("SELECT id, father_id FROM member");
$dbh->execute();


// Start updating fullnames of them.
while ($member = $get_members_query->fetch(PDO::FETCH_ASSOC))
{
	$id = $member["id"];
	update_fullname($id);
}

// Redirect to init_members.
//header("location: init_users.php");

?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="refresh" content="0; url=init_users.php" />
</head>
<body>
	<h2>3/4</h2>
	<b>Members fullnames have been updated,</b><br />
	Hold on a second...
</body>
</html>
