<?php
namespace SSP\NNTP;
class NNTP {
	private $conn;
	private $error;
	private $group;
	public function connect($server, $port = 119, $timeout = 5) {
		$this->conn = fsockopen($server, $port, $timeout);
		if(!$this->conn){
			$this->error = $description;
			return false;
		}
		$return = fgets($this->conn);
		if(substr($return, 0, 3)=="200"){
			return true;
		}
		else {
			return false;
		}
	}
	public function disconnect(){
		fclose($this->conn);
	}
	public function autentifizierung($name, $password){
		fputs($this->conn, "AUTHINFO USER ".$name."\r\n");
		$zeile = fgets($this->conn);
		if(substr($zeile, 0, 3 )=="381"){
			fputs($this->conn, "AUTHINFO PASS ".$password."\r\n");
			$zeile = fgets($this->conn);
			if(substr($zeile,0, 3)=="281"){
				return true;
			}
		}
		return false;
	}
	public function listGroups(){
		fputs($this->conn, "LIST \r\n");
		$zeile = fgets($this->conn);
		if(substr($zeile, 0, 3)=="215"){
			$zeile = fgets($this->conn);
			$groups = array();
			while($this->getAsci($zeile)!="461310"){
				$groups[] = explode(" ", $zeile);
				$zeile = fgets($this->conn);
			}
			return $groups;
		}
		return false;
	}
	public function changeGroup($group){
		fputs($this->conn, "GROUP ".$group." \r\n");
		$zeile = fgets($this->conn);
		if(substr($zeile, 0, 3)=="211"){
			return true;
		}
		return false;
	}
	public function getHead($id, $group = NULL){
		trigger_error("Deprecated functioni 'getHead' called.", E_USER_NOTICE);
		if($group!=NULL){
			if(!$this->changeGroup($group)){
				return false;
			}
		}
		fputs($this->conn, "HEAD ".$id." \r\n");
		$zeile = fgets($this->conn);
		if(substr($zeile, 0, 3)!=221){
			return false;
		}
		$zeile = fgets($this->conn);
		while($this->getAsci($zeile)!="461310"){
			$header[] = explode(":", substr($zeile,0,-2), 2);
			$zeile = fgets($this->conn);
		}
		return $header;
	}
	public function getHeadDetails($id, $group = NULL){
                if($group!=NULL){
                        if(!$this->changeGroup($group)){
                                return false;
                        }
                }
                fputs($this->conn, "HEAD ".$id." \r\n");
                $zeile = fgets($this->conn);
                if(substr($zeile, 0, 3)!=221){
                        return false;
                }
                $zeile = fgets($this->conn);
		$headerDetails = array();
                while($this->getAsci($zeile)!="461310"){
                        $header = explode(":", substr($zeile,0,-2), 2);
			$headerDetails[$header[0]]=trim($header[1]);
                        $zeile = fgets($this->conn);
                }
                return $headerDetails;
        }

	public function getBody($id, $group = NULL){
		if($group!=NULL){
			if(!$this->changeGroup($group)){
				return false;
			}
		}
		fputs($this->conn, "BODY ".$id." ".$this->getString("1310"));
		$zeile = fgets($this->conn);
		if(substr($zeile, 0, 3)!=222){
			return false;
		}
		$zeile = fgets($this->conn);
		$body = "";
		while($this->getAsci($zeile)!="461310"){
			$body .= substr($zeile,0,-2)."\r\n";
			$zeile = fgets($this->conn);
		}
		return $body;
	
	}
	public function post($board, $subject, $message, $from, $header = array()){
		fputs($this->conn, "post\r\n");
		$zeile = fgets($this->conn);
		if(!substr($zeile,0,3)=="340"){
			return false;
		}
		fputs($this->conn, "From: ".$from.$this->getString("1310"));
		fputs($this->conn, "Newsgroups: ".$board.$this->getString("1310"));
		fputs($this->conn, "Subject: ".$subject.$this->getString("1310"));
		fputs($this->conn, "User-Agent: phpNNTP by sspssp".$this->getString("1310"));
		foreach($header as $name => $value){
			fputs($this->conn, $name.": ".$value.$this->getString("1310"));
		}
		fputs($this->conn, $this->getString("1310"));
		fputs($this->conn, $message);
		fputs($this->conn, chr(0x0D).chr(0x0A).".".chr(0x0D).chr(0x0A));
		$zeile = fgets($this->conn);
		if(substr($zeile,0,3)==240){
			return true;
		}
		return false;
	}
	
	private function getAsci($zeile){
		$ascii="";
		for($i=0;$i<strlen($zeile);$i++){
			$ascii.= ord(substr($zeile,$i,1));
		}
		return $ascii;
	}
	private function getString($zeile){
		$ascii="";
		for($i=0;$i<strlen($zeile);$i=$i+2){
			$ascii.= chr(substr($zeile,$i,2));
		}
		return $ascii;
	}
}
?>
