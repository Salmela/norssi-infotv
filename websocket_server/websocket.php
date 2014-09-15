<?php
require_once("api.php");
include("slides.php");

require_once("../../../../wp-load.php");
require_once("../functions.php");

// check if the port is open
//echo exec("netstat -lnp | grep :12346");
class infotv_module
{
	var $name;

	function startup() {

	}
	function update() {

	}
	function receved($msg) {

	}
}

class infotv_server extends WebSocket
{
	var $listened;
	var $modules;

	function __construct($address, $port) {
		global $infotv;
		$this->listened = array();

		$this->modules = array();
		$this->modules["slides"] = new infotv_slides_module($this);
		add_option($infotv->prefix ."websocket_status", 2);

		parent::__construct($address, $port);
	}
	function new_user($user) {
		
	}

	function get_config($id) {
		
	}

	function update() {
		foreach($this->modules as $name => $mod) {
			//$mod = $this->modules[$name];
			$mod->update();
		}
	}

	function listen($name, $cb) {
		if(isset($this->listened[$name])) return false;
		$this->listened[$name] = $cb;
	}

	function receved($user, $msg) {
		preg_match("/^p\[([a-z\-_]*)\]:/", $msg, $matches, PREG_OFFSET_CAPTURE, 0);

		print($msg ."\n");
		print_r($matches);

		if(! $matches) return;
		$data = substr($msg, strlen($matches[0][0]));

		if(isset($this->listened[$matches[1][0]])) {
			print("listener\n");
			$f = $this->listened[$matches[1][0]];
			$f($user, $data);
		}
		switch($matches[1][0]) {
		case "config":
			
			break;
		}
	}
}

/*$modules = array();
$modules["slides"] = new infotv_slides_module();*/
$infotv = new infotv_server("localhost", 12345);
//$socket->modules["slides"] = new slides();
?>
