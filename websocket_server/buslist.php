<?php

class bus {
	var $year;
	var $month;
	var $day;
	var $hour;
	var $min;
	
	function bus($clone_date) {
		if($clone_date instanceof bus) {
			$this->year = $clone_date->year;
			$this->month = $clone_date->month;
			$this->day = $clone_date->day;
			
			$this->hour = $clone_date->hour;
			$this->min = $clone_date->min;
		} else {
			$this->year = date("Y");
			$this->month = date("n");
			$this->day = date("j");
			
			$this->hour = date("H");
			$this->min = date("i");
		}
	}
	function set($hour, $min) {
		//global $gmt_offset;
		$hour -= get_option("gmt_offset");
		//if($this->hour - 18 > $hour) $this->day++;
		
		$this->hour = $hour;
		$this->min = $min;
	}
	function get() {
		return mktime($this->hour, $this->min, 0, $this->month, $this->day, $this->year);
	}
}

class infotv_buslist_module extends infotv_module
{
	var $slidelist;
	var $infotv_server;

	function __construct($infotv_server) {
		
	}

	function get_slide_list($user, $data) {
		
	}

	function update() {

	}

	function download_page() {
		
	}

	function receved($msg) {
		
	}
}

?>
