<?php

require_once("inc/functions.inc.php");

$hobbies = array(
	array("القراءة", 98),
	array("الطبح", 70),
	array("الحساب", 35),
	array("التصميم", 44),
	array("الإدارة", 20),
	array("الكوميديا", 10),
	array("الشعر", 5),
	array("التجميع", 34),
	array("الزراعة", 28),
	array("الكتابة", 86),
	array("التاريخ", 77),
	array("كرة القدم", 20),
	array("الصيد", 10),
	array("الأحاجي", 5),
);

foreach ($hobbies as $hobby)
{
	$name = $hobby[0];
	$rank = $hobby[1];

	// Check if the hobby already exists.
	$get_hobby_query = $dbh->prepare("SELECT id FROM hobby WHERE name = :name");
    $get_hobby_query->bindParam(":name", $name);
    $get_hobby_query->execute();
	
	if (mysql_num_rows($get_hobby_query) == 0)
	{
		$insert_hobby = $dbh->prepare("INSERT INTO hobby (name, rank) VALUES (:name, :rank)");
        $insert_hobby->bindParam(":name", $name);
        $insert_hobby->bindParam(":rank", $rank);
        $insert_hobby->execute();
	}
}
