<?php

require_once("inc/functions.inc.php");

$iban = "SA0380000000000000000000";
$get_suggested_subsribers_query = $dbh->prepare("SELECT * FROM member WHERE is_alive = 1 AND age >= 21");
$dbh->execute();


while ($suggested = mysql_fetch_array($get_suggested_subsribers_query))
{
	$now = time();
	$insert_subscriber_query = $dbh->prepare("INSERT INTO box_subscriber (member_id, created) VALUES (:suggested_id, :now)");
$dbh->bindParam(":suggested_id", $suggested["id"]);
$dbh->bindParam(":now", $now);
$dbh->execute();

	$subscriber_id = mysql_insert_id();
	$insert_account_query = $dbh->prepare("INSERT INTO box_account (subscriber_id, iban, created) VALUES (:subscriber_id, :iban, :now)");
$dbh->bindParam(":subscriber_id", $subscriber_id);
$dbh->bindParam(":iban", $iban);
$dbh->bindParam(":now", $now);
$dbh->execute();

}
