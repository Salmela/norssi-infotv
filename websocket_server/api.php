<?php	
/**
 * Simple implementation of HTML5 WebSocket server-side.
 *
 * PHP versions 5
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.	If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		WebSocket
 * @author		George Nava <georgenava@gmail.com>
 * @author		Vincenzo Ferrari <wilk3ert@gmail.com>
 * @author		Aleksi Salmela
 * @copyright		2010-2011
 * @license		http://www.gnu.org/licenses/gpl.txt GNU GPLv3
 * @version		1.1.0
 * @link		http://code.google.com/p/phpwebsocket/
 */

class WebSocket {
	var $master;
	var $sockets = array();
	var $clients = array();
	var $debug   = true;

	function __construct($address, $port) {
		error_reporting(E_ALL);
		set_time_limit(0);
		ob_implicit_flush();
		date_default_timezone_set("Europe/Helsinki");
		
		$this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or $this->socket_error("socket_create()");
		socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1)	or $this->socket_error("socket_option()");
		socket_bind($this->master, $address, $port) or $this->socket_error("socket_bind()");
		socket_listen($this->master, 20)            or $this->socket_error("socket_listen()");
		$this->sockets[] = $this->master;
		$this->say("Server Started:	" . date('Y-m-d H:i:s'));
		$this->say("Listening on:	" . $address . " port " . $port);
		$this->say("Master socket:	" . $this->master . "\n");
		
		while(true) {
			$changed = $this->sockets; // wait until some socket have data
			$write	 = NULL;
			$except	= NULL;
			socket_select($changed, $write, $except, NULL);
			
			foreach($changed as $socket) {
				if($socket == $this->master) {// if it is new client
					$client = socket_accept($this->master);
					if($client < 0) {
						$this->log("socket_accept() failed: ". socket_strerror(socket_last_error()));
						continue;
					}
					$this->connect($client);
					continue;
				}
				$bytes = @socket_recv($socket, $buffer, 2048, 0); // get received message
				if($bytes < 0) {
					$this->log("socket_recv() failed: ". socket_strerror(socket_last_error()));
					$this->disconnect($socket);
					continue;
				}
				$user = $this->getuserbysocket($socket);
				
				if($user->version != "hybi" && $bytes == 0) {
					$this->disconnect($socket);
					continue;
				}
				if(! $user->handshake){
					$this->dohandshake($user, $buffer);
					continue;
				}

				if($user->version == "hybi") {
					$decoded = $this->hybi10Decode($buffer);
					$msg = false;
					switch($decoded["type"]) {
						case "ping":
							socket_write($user->socket, $this->hybi10Encode($msg, "pong"));
							break;
						case "pong":
							$this->log("pong");
							break;
						case "close":
							$this->disconnect($user->socket);
							break;
						default:
							$msg = $decoded["payload"];
					}
					if($decoded["type"] == "close") continue;

				} else {
					$msg = $this->unwrap($buffer);
				}

				if($msg) {
					$this->receved($user, $msg);
				} elseif($decoded) {
					//$this->notify($user, $decoded);
				}
			}
		}
	}

	function new_user($user) {

	}

	function receved($user, $msg){
		/* Extend and modify this method to suit your needs */
		/* Basic usage is to echo incoming messages back to client */
		if($msg == "ping") socket_write($user->socket, $this->hybi10Encode("", "pong"));
		else $this->send($user, $msg);
	}

	function send($user, $msg) {
		$data = ($user->version == "hybi") ? $this->hybi10Encode($msg) : $this->wrap($msg);
		socket_write($user->socket, $data);
	}

	function send_to_all($msg) {
		foreach($this->clients as $user) {
			send($user, $msg);
		}
	}

	function user_removed($user) {
		
	}

	function connect($socket){
		$user = new User();
		$user->id = uniqid();
		$user->socket = $socket;
		array_push($this->clients, $user);
		array_push($this->sockets, $socket);
		$this->log($socket . " new client connected");
		$this->log(date("d/n/Y ") . "at " . date("H:i:s T"));
	}

	function close($socket, $statusCode) {
		$this->say("disconnecting client\n");
		$data = pack("n", $statusCode);

		switch($statusCode) {
		case 0x1000:
			$data .= 'normal closure';
			break;
		case 0x1001:
			$data .= 'going away';
			break;
		case 0x1002:
			$data .= 'protocol error';
			break;
		case 0x1003:
			$data .= 'datatype not supported (opcode)';
			break;
		case 0x1004:
		case 0x1005:
		case 0x1006:
			$this->say('reserved');
			return;
		case 0x1007:
			$data .= 'utf8 expected';
			break;
		case 0x1008:
			$data .= 'message violates server policy';
			break;
		case 0x1009:
			$data .= 'too big message';
			break;
		}
		socket_write($socket, $this->hybi10Encode($data, "close"));
		disconnect($socket);
	}

	function disconnect($socket){
		$found = null;
		$n = count($this->clients);

		for($i = 0; $i < $n; $i++){
			if($this->clients[$i]->socket == $socket){ 
				$found = $i;
				break;
			}
		}
		$this->user_removed($this->clients[$found]);
		if($found != null){
			array_splice($this->clients, $found, 1);
		}
		$index = array_search($socket, $this->sockets);
		socket_close($socket);
		$this->log($socket." client is disconnected");

		if($index >= 0){
			array_splice($this->sockets, $index, 1);
		}
	}
	function shutdown(){
		$found = null;
		$n = count($this->clients);

		foreach($this->clients as $client){
			$this->user_removed($client);
			socket_close($client->socket);
		}
		socket_close($this->master);
	}

	function dohandshake($user, $buffer){
		$this->log("\nRequesting handshake...");
		$this->log($buffer);
		list($version, $resource, $host, $origin, $key, $key1, $key2, $l8b) = $this->getheaders($buffer);
		$this->log("Handshaking... (version: ". $version .")");
		//$port = explode(":", $host);
		//$port = $port[1];
		//$this->log($origin."\r\n".$host);

		if(empty($origin)) {
			$this->log("No origin provided.");
			$header = "HTTP/1.1 401 Unauthorized\r\n" . chr(0);
			socket_write($user->socket, $header);
			return false;
		}

		if($version >= 6) {
			$upgrade  = "HTTP/1.1 101 Switching Protocols\r\n";
			$upgrade .= "Upgrade: WebSocket\r\n";
			$upgrade .= "Connection: Upgrade\r\n";
			$upgrade .= "Sec-WebSocket-Accept: " . $this->hybi_accept($key) . "\r\n\r\n";

			socket_write($user->socket, $upgrade);
			$user->version = "hybi";

		} elseif(!isset($version)) {
			$upgrade  = "HTTP/1.1 101 WebSocket Protocol Handshake\r\n";
			$upgrade .= "Upgrade: WebSocket\r\n";
			$upgrade .= "Connection: Upgrade\r\n";
			$upgrade .= "Sec-WebSocket-Origin: " . $origin . "\r\n";
			$upgrade .= "Sec-WebSocket-Location: ws://" . $host . $resource . "\r\n\r\n";
			$upgrade .= $this->calcKey($key1, $key2, $l8b) . "\r\n";

			socket_write($user->socket, $upgrade.chr(0));
			$user->version = "old";
		}
		$user->handshake = true;
		$this->log($upgrade);
		$this->log("Done handshaking...");
		$this->new_user($user);
		return true;
	}
	
	private function calcKey($key1, $key2, $l8b) {
		//Get the numbers
		preg_match_all('/([\d]+)/', $key1, $key1_num);
		preg_match_all('/([\d]+)/', $key2, $key2_num);
		//Number crunching [/bad pun]
		$this->log("Key1: " . $key1_num = implode($key1_num[0]) );
		$this->log("Key2: " . $key2_num = implode($key2_num[0]) );
		//Count spaces
		preg_match_all('/([ ]+)/', $key1, $key1_spc);
		preg_match_all('/([ ]+)/', $key2, $key2_spc);
		//How many spaces did it find?
		$this->log("Key1 Spaces: " . $key1_spc = strlen(implode($key1_spc[0])) );
		$this->log("Key2 Spaces: " . $key2_spc = strlen(implode($key2_spc[0])) );
		if($key1_spc==0|$key2_spc==0){ $this->log("Invalid key");return; }
		//Some math
		$key1_sec = pack("N", $key1_num / $key1_spc); //Get the 32bit secret key, minus the other thing
		$key2_sec = pack("N", $key2_num / $key2_spc);
		//This needs checking, I'm not completely sure it should be a binary string
		return md5($key1_sec.$key2_sec.$l8b,1); //The result, I think
	}
	private function hybi10Encode($data, $type = "text") {
		$length = strlen($data);
		$masked = 1;
		$encoded = 0;

		$this->log("\n");
		$this->log("encoding...");
		$this->log("length: ". $length);
		$this->log("type: ". $type);

		switch($type) {
			case 'text':
				$encoded = chr(129);// Text-Frame (10000001)
				break;
			case 'binary':
				$encoded = chr(130);// Text-Frame (10000010)
				break;
			case 'close':
				$encoded = chr(136);// Close Frame (10001000)
				break;
			case 'ping':
				$encoded = chr(137);// Ping frame (10001001)
				break;
			case 'pong':
				$encoded = chr(138);// Pong frame (10001010)
				break;
		}
		if($length > 65535) {
			$encoded .= chr($masked * 128 + 127);
			$encoded .= pack("N", $length & 0xffff);
			$encoded .= pack("N", $length >> 32);

			if($encoded[2] > 127) {
				return false;// too big
			}
		} elseif($length > 125) {
			$encoded .= chr($masked * 128 + 126);
			$encoded .= pack("n", $length);
		} else {
			$encoded .= chr($masked * 128 + $length);
		}

		if($masked) {
			$mask = pack("N", rand(0, (255 * 255 * 255 * 255) - 1));
			$encoded .= $mask;

			$this->log("mask:	 ". bin2hex($mask));
			$this->log("header: ". bin2hex($encoded));
			$this->log("data:	 ". bin2hex($data));

			for($i = 0; $i < $length; $i++) {
				$encoded .= $data[$i] ^ $mask[$i % 4];
			}
		} else {
			$this->log("header: ". bin2hex($encoded));
			$this->log("data:	 ". $data);
			$encoded .= $data;
		}

		return $encoded;
	}
	private function hybi10Decode($data) {
		$firstByte	= $data[0];
		$secondByte = $data[1];
		$opcode		 = $firstByte & chr(0x0F); //0x0F = 0000 1111
		$masked		 = ($secondByte & chr(128) == 0) ? false : true;	//128 = 1000 0000
		$decoded		= array();

		$this->log("\ndecoding...");

		$decoded["fin"]	= ($firstByte & (1 << 7)) ? true : false;
		if($firstByte & 0x70 != 0) return false;

		switch(ord($opcode)) {
			case 1:// Text-Frame (0001)
				$decoded["type"] = "text";
				break;
			case 2:// Text-Frame (0010)
				$decoded["type"] = "binary";
				break;
			case 8:// Close Frame (1000)
				$decoded["type"] = "close";
				break;
			case 9:// Ping frame (1001)
				$decoded["type"] = "ping";
				break;
			case 10:// Pong frame (1010)
				$decoded["type"] = "pong";
				break;
			default:
				$decoded["type"] = "unknown";
				break;
		}
		$this->log("type: ". $decoded["type"]);
		$len = ord($secondByte & chr(0x7F)); //0x7F = 0111 1111

		if($len == 127) { //read next 64bit unsigned int
			list($h, $l)	= unpack("N2", substr($data, 2, 8));
			$length = ($h<<32) + $l;
			$offset = 10;
		} elseif($len == 126) { //read next 16bit unsigned int
			$length = unpack("n", substr($data, 2, 2));
			$offset = 4;
		} else {
			$length = $len;
			$offset = 2;
		}

		if($masked) { // get masking key
			$mask = substr($data, $offset, 4);
			$offset += 4;
			$this->log("mask: ". bin2hex($mask));
		}
		$this->log("offset: ". $offset .", length:". $length .", masked:". ($masked ? "true" : "false"));

		// have we received all data?
		if(strlen($data) < $length + $offset) {
			return false;
		}

		if($masked) {
			$minLength = min($length + $offset, strlen($data));
			$decoded["payload"] = null;

			for($i = $offset; $i < $minLength; $i++) {
				$decoded["payload"] .= $data[$i] ^ $mask[($i-$offset) % 4];
			}
		} else {
			$decoded["payload"] = substr($data, $offset);
		}
		$this->say("data is decoded");
		return $decoded;
	}
	function getheaders($req) {
		$version = $r = $h = $o = null;
		$sk = $sk1 = $sk2 = $l8b = null;
		if(preg_match("/GET (.*) HTTP/",    $req, $match)){ $r = $match[1]; }
		if(preg_match("/Host: (.*)\r\n/",   $req, $match)){ $h = $match[1]; }

		if(preg_match("/Origin: (.*)\r\n/", $req, $match)){ $o = $match[1]; }
		if(preg_match("/Sec-WebSocket-Origin: (.*)\r\n/", $req, $match) && $o != null){ $o = $match[1]; }

		if(preg_match("/Sec-WebSocket-Version: (.*)\r\n/", $req, $match)){ $version = $match[1]; }
		if($version >= 6) {
			if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $req, $match)){ $this->log("Sec Key: ". $sk = $match[1]); }
		} else {
			if(preg_match("/Sec-WebSocket-Key1: (.*)\r\n/", $req, $match)){ $this->log("Sec Key1: ". $sk1 = $match[1]); }
			if(preg_match("/Sec-WebSocket-Key2: (.*)\r\n/", $req, $match)){ $this->log("Sec Key2: ". $sk2 = $match[1]); }
			if($match=substr($req,-8))																	{ $this->log("Last 8 bytes: ".$l8b = $match); }
		}
		return array($version, $r, $h, $o, $sk, $sk1, $sk2, $l8b);
	}

	function getuserbysocket($socket){
		foreach($this->clients as $user) {
			if($user->socket == $socket) {
				return $user;
			}
		}
		return null;
	}

	function socket_error($func) {
		die($func. " failed: ". socket_strerror(socket_last_error()));
	}
	function hybi_accept($key) {
		$master_key = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
		return base64_encode(pack('H*', sha1($key . $master_key)));
	}
	function say($msg="")    { echo $msg."\n"; }
	function log($msg="")    { if($this->debug){ echo $msg."\n"; } }
	function wrap($msg="")   { return chr(0) . $msg . chr(255); }
	function unwrap($msg="") { return substr($msg,1,strlen($msg)-2); }
}

class User {
	var $id;
	var $socket;
	var $handshake;
	var $version;
}

?>

