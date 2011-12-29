<?php
class nntp
{
	private $conn;
	private $error;
	private $group;
	public function connect($server, $port = 119, $timeout = 5)
	{
		$this->conn = fsockopen($server, $port, &$error, &$description, $timeout);
		if(!$this->conn)
		{
			$this->error = $description;
			return false;
		}
		$return = fgets($this->conn);
		if(substr($return, 0, 3)=="200")
		{
			return true;
		}
		else {
			return false;
		}
	}
	public function disconnect()
	{
		fclose($this->conn);
	}
	public function getGroups()
	{
		fputs($this->conn, "LIST\r\n");
		$zeile = fgets($this->conn);
		$groups = array();
		do
		{
			$zeile = fgets($this->conn);
			if(substr($zeile,0,1)!=".")
			{
				$groups[] = explode(" ", $zeile);
			}
		} while(substr($zeile,0,1)!=".");
		return $groups;
	}
	public function selectGroup($group)
	{
		fputs($this->conn, "GROUP ".$group."\r\n");
		$zeile = fgets($this->conn);
		//211 2 1 2 alt.wendzelnntpd.test group selected
		if(substr($zeile, 0, 3)=="211")
		{
			return true;
		}
		return false;
	}
	public function autentifizierung($name, $password)
	{
		fputs($this->conn, "AUTHINFO USER ".$name."\r\n");
		$zeile = fgets($this->conn);
		if(substr($zeile, 0, 3 )=="381")
		{
			fputs($this->conn, "AUTHINFO PASS ".$password."\r\n");
			$zeile = fgets($this->conn);
			if(substr($zeile,0, 3)=="281")
			{
				return true;
			}
		}
		return false;
	}
	public function getArticle($board, $start = 1, $ende = NULL)
	{
		$this->selectGroup($board);
		if($ende == NULL)
		{
			$ende = $this->getHighst($board);
		}
		$messages = array();
		echo ">ENDE:".$ende."\r\n";
		for($i=$start; $i <= $ende; $i++)
		{
			fputs($this->conn, "article ".$i."\r\n");
			$zeile = fgets($this->conn);
			echo $zeile."\r\n";
			if(substr($zeile, 0, 3)=="220")
			{
				$message="";
				while(true)
				{
					$zeile = fgets($this->conn);
					if(substr($zeile, 0, 1)==".")
					{
						break;
					}
					$message .= $zeile;
				}
				$messages[]=$message;
			}
		}
		return $messages;
	}
	private function getHighst($board)
	{
		$groups = $this->getGroups();
		foreach($groups as $group)
		{
			if($group[0]==$board)
			{
				return $group[1];
			}
		}
		return false;
	}
	public function post($board, $subject, $message, $from = "anonymus@nobody.de")
	{
		//User-Agent
		define('CRLF', chr(0x0D).chr(0x0A)); 
		fputs($this->conn, "post\r\n");
		$zeile = fgets($this->conn);
		if(substr($zeile,0,3)=="340")
		{
			fputs($this->conn, "From: ".$from."\r\n");
			fputs($this->conn, "Newsgroups: ".$board."\r\n");
			fputs($this->conn, "Subject: ".$subject."\r\n");
			fputs($this->conn, "User-Agent: phpNNTP by sspssp"."\r\n");
			fputs($this->conn, "\r\n");
			fputs($this->conn, $message."\r\n");
			fputs($this->conn, CRLF.'.'.CRLF);
			$zeile = fgets($this->conn);
			if(substr($zeile, 0, 3)=="240")
			{
				return true;
			}
		}
		return false;
	}
}
?>