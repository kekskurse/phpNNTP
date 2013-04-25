This Class is Testet With http://www.wendzel.de/software/wendzelnntpd.html

# HowToUse

Connect to a Server:

	$nntp = new nntp();
	$nntp->connect($server); //Return true or false
	$nntp->autentifizierung($user, $pass); //Return true or false


Get the Group List as an Array:

	$nntp->listGroups(); //Return an Array or False

Change Group:

	$nntp->changeGroup($name); //Return true or false

Get a Message Header:

	$nntp->getHeadDetails($id); //Return an Array or false
	$nntp->getHead($id, $group); //Return an Array or false

Get a Message Body:

	$nntp->getBody($id); //Return an String with "\r\n" or fase
	$nntp->getBody($id, $group); //Return an String with "\r\n" or fase

Post a Message:

	$nntp->post($board, $subject, $message, $from); //Return true or false
	$nntp->post($board, $subject, $message, $from, array("MoreHeaderInfo" => "Value", "MuchMoreHeaderInfo" => "Value2")); //Return true or false

