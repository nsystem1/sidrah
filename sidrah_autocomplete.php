<?php

/**
 * In Allah We Trust.
 *
 * @author:	Hussam Al-sidrah.
 * @date:	9 Jul 2012.
 */

require_once("inc/functions.inc.php");

// Get user information.
$user = user_information();

// Get variables.
$name = @$_GET["name"];
$type = @$_GET["type"];
$suggested = trim(@$_GET["suggested"]);
$unique_id = @$_GET["unique_id"];
$return = @$_GET["return"];

// Type.
// mother, father, wife, husband.

$first_name = "";
$error = false;
$exact_array = array();
$almost_exact_array = array();
$child_of_array = array();
$suggested_array = array();
$normalized_name = normalize_name($name);

if (!empty($suggested))
{
	$suggested_array = json_decode($suggested, true);
}

if (empty($normalized_name))
{
	echo "<div class='error'><i class='icon-question-sign'></i> الرجاء تعبئة الحقل.</div>";
	$error = true;
}
else
{
	$main_tribe_id = main_tribe_id;
	$names = explode(" ", $normalized_name);
	$first_name = $names[0];

	// If it was just auto complete.
	if ($type == "auto-complete")
	{
		$condition = "";
	
		if ($user["group"] == "visitor" || $user["group"] == "user")
		{
			$main_tribe_id = main_tribe_id;
			$condition = "AND gender = 1 AND tribe_id = '$main_tribe_id'";
		}

		$href = "#autocomplete-names-$unique_id";
		$search_for = escape_confusing("^" . implode(" ", $names) . ".*$");
		$get_related_names_query = $dbh->prepare("SELECT id, fullname FROM member WHERE fullname REGEXP :search_for $condition ORDER BY fullname ASC LIMIT 8");
        $get_related_names_query->bindValue(":search_for", $search_for, PDO::PARAM_STR);
        //$get_related_names_query->bindParam(":condition", $condition);
        $get_related_names_query->execute();

		if ($get_related_names_query->rowCount() > 0)
		{
			echo "<ul class='ul_result'>";
			
			while ($result = $get_related_names_query->fetch(PDO::FETCH_ASSOC))
			{
				echo "<li class='li_result'><a title='$result[fullname]' href='$href' data-id='$result[id]' class='result'>$result[fullname]</a></li>";
			}
			
			echo "</ul>";
		}
		else
		{
			echo "<div class='error'><i class='icon-exclamation-sign'></i> لا يوجد نتائج.</div>";
		}
		
		return;
	}

	if (count($names) < 4)
	{
		echo "<div class='error'><i class='icon-question-sign'></i> الرجاء إدخال 4 أسماء على الأقل.</div>";
		$error = true;
	}
	else
	{
		$gender = null;

		switch ($type)
		{
			case "mother": case "wife":
				$gender = 0;
			break;
	
			case "father": case "husband":
				$gender = 1;
			break;
		}
		
		// Search exact.
		$exact_name = escape_confusing("^" . $normalized_name . "$");
		$get_exact_query = $dbh->prepare("SELECT fullname FROM member WHERE fullname REGEXP :exact_name AND gender = :gender");
        $get_exact_query->bindParam(":exact_name", $exact_name);
        $get_exact_query->bindParam(":gender", $gender);
        $get_exact_query->execute();
		
		if ($get_exact_query->rowCount() > 0)
		{
			while ($ex = $get_exact_query->fetch(PDO::FETCH_ASSOC))
			{
				$exact_array []= $ex["fullname"];
			}
		}
		
		// Search almost exact.
		$family_name = $names[count($names)-1];
		unset($names[count($names)-1]);
		
		$almost_exact_name = escape_confusing("^" . implode(" ", $names) . ".*" . $family_name . "$");
		$get_almost_exact_query = $dbh->prepare("SELECT id, fullname FROM member WHERE fullname REGEXP :almost_exact_name AND gender = :gender");
        $get_almost_exact_query->bindParam(":almost_exact_name", $almost_exact_name);
        $get_almost_exact_query->bindParam(":gender", $gender);
        $get_almost_exact_query->execute();

		if ($get_almost_exact_query->rowCount() > 0)
		{
			while ($almost_ex = $get_almost_exact_query->fetch(PDO::FETCH_ASSOC))
			{
				if (!in_array($almost_ex["fullname"], $exact_array))
				{
					$almost_exact_array []= $almost_ex["fullname"];
				}
			}
		}
		
		// Search child of.
		unset($names[0]);
		
		$father_name = escape_confusing("^" . implode(" ", $names) . ".*" . $family_name . "$");
		$get_childof_query = $dbh->prepare("SELECT fullname FROM member WHERE fullname REGEXP :father_name AND gender = '1'");
        $get_childof_query->bindParam(":father_name", $father_name);
        $get_childof_query->execute();
		
		if ($get_childof_query->rowCount() > 0)
		{
			while ($childof = $get_childof_query->fetch(PDO::FETCH_ASSOC))
			{
				$iname = "$first_name $childof[fullname]";
				
				if (!in_array($iname, $exact_array) && !in_array($iname, $almost_exact_array))
				{
					$child_of_array []= $childof["fullname"];
				}
			}
		}
	}
}

$href = "#autocomplete-names-$unique_id";

if (count($exact_array) > 0)
{
	echo "<div class='results_group'>اسم مطابق تماماً</div><ul class='ul_result'>";
	
	for($i=0; $i<count($exact_array); $i++)
	{
		echo "<li class='li_result'><a title='$exact_array[$i]' href='$href' class='result'>$exact_array[$i]</a></li>";
	}
	
	echo "</ul>";
}

if (count($almost_exact_array) > 0)
{
	echo "<div class='results_group'>أسماء مطابقة إلى حدٍ كبير</div><ul class='ul_result'>";
	
	for($i=0; $i<count($almost_exact_array); $i++)
	{
		echo "<li class='li_result'><a title='$almost_exact_array[$i]' href='$href' class='result'>$almost_exact_array[$i]</a></li>";
	}
	
	echo "</ul>";
}

if (count($child_of_array) > 0)
{
	echo "<div class='results_group'>إضافة <b>$first_name</b> إلى</div><ul class='ul_result'>";
	
	for($i=0; $i<count($child_of_array); $i++)
	{
		echo "<li class='li_result'><a title='$first_name $child_of_array[$i]' href='$href' class='result'>$child_of_array[$i]</a></li>";
	}
	
	echo "</ul>";
}

if ((count($exact_array) == 0) && (count($almost_exact_array) == 0) && (count($child_of_array) == 0) && $error == false)
{
	echo "<div class='results_group'>إضافة اسم جديد</div>";
	echo "<ul class='ul_result'><li class='li_result'><a title='$normalized_name' href='$href' class='result'>$normalized_name</a></li></ul>";
}

if (count($suggested_array) > 0)
{
	switch ($type)
	{
		case "mother": case "wife":
			$wives_husbands = "زوجات الأب";
		break;
			case "father": case "husband":
			$wives_husbands = "أزواج الأم";
		break;
	}

	echo "<div class='results_group'>أسماء مقترحة ($wives_husbands)</div><ul class='ul_result'>";
	
	for($i=0; $i<count($suggested_array); $i++)
	{
		$name = $suggested_array[$i]["name"];
		echo "<li class='li_result'><a title='$name' href='$href' class='result'>$name</a></li>";
	}
	
	echo "</ul>";
}

