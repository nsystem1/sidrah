<?php

require_once("inc/functions.inc.php");

$iban = "SA0380000000000000000000";
$get_suggested_subsribers_query = $dbh->prepare("SELECT * FROM member WHERE is_alive = 1 AND age >= 21");
$get_suggested_subsribers_query->execute();

while ($suggested = mysql_fetch_array($get_suggested_subsribers_query))
{
	$now = time();
	$insert_subscriber_query = $dbh->prepare("INSERT INTO box_subscriber (member_id, created) VALUES (:suggested_id, :now)");
    $insert_subscriber_query->bindParam(":suggested_id", $suggested["id"]);
    $insert_subscriber_query->bindParam(":now", $now);
    $insert_subscriber_query->execute();

	$subscriber_id = $dbh->lastInsertId();
	$insert_account_query = $dbh->prepare("INSERT INTO box_account (subscriber_id, iban, created) VALUES (:subscriber_id, :iban, :now)");
    $insert_account_query->bindParam(":subscriber_id", $subscriber_id);
    $insert_account_query->bindParam(":iban", $iban);
    $insert_account_query->bindParam(":now", $now);
    $insert_account_query->execute();
}
