<?php
$continue = false;
$processed = false;
if ($systemUser->isValid()) {
	if ($_POST['timeline_id'] == $user_id)
	{
	    $timelineId = $user_id;
	    $continue = true;
	}

	if ($_FILES['image']['size'] > 0 && $continue == true)
	{
	    $image = $_FILES['image'];
	    $avatar = $tools->registerMedia($image);
	    
	    if (isset($avatar['id']))
	    {
	        $query = mysql_query("UPDATE `users` SET avatar_extension='" . $avatar['extension'] . "' WHERE id=$timelineId");
	    }
	}
}
