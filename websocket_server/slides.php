<?php

class infotv_slide
{
	var $id;
	var $name;
	var $theme;
	var $content;
	var $timestamp;

	function __construct($id, $name, $theme, $content, $timestamp) {
		$this->id = $id;
		$this->update($name, $theme, $content, $timestamp);
	}
	function update($name, $theme, $content, $timestamp) {
		$this->name      = $name;
		$this->theme     = $theme;
		$this->content   = $content;
		$this->timestamp = $timestamp;
	}
}

class infotv_slides_user
{
	var $slides;

	function __construct() {
		$this->slides = array();
	}
}

class infotv_slides_module extends infotv_module
{
	var $slides;
	var $slidelist;
	var $users;
	var $infotv_server;

	function __construct($infotv_server) {
		$this->infotv_server = $infotv_server;
		$this->name = "slides";
		$this->users = array();
		$this->slides = array();
		
		$infotv_server->listen("slide_order", array($this, "get_slide_list"));
		
		$this->update();
		
		echo json_encode($this->slides) ."\n";
	}

	/* upload only differences when client connects */
	function new_user($revision) {
		$this->users = new infotv_slides_user();
	}

	/* broadcast changes to all clients */
	function get_slide_list($user, $data) {
		print("$data\n");
		
		if(! isset($this->users[$user->id])) {
			$this->users[$user->id] = new infotv_slides_user();
		}
		
		echo "p[slide_order]:". json_encode($this->slidelist);
		$this->infotv_server->send($user, "p[slide_order]:". json_encode($this->slidelist));
		
		$this->users[$user->id]->slides = json_decode($data);
		$list = array();
		foreach($this->slidelist as $id) {
			if(! in_array($id, $this->users[$user->id]->slides)) {
				$list[$id] = $this->slides[$id];
			}
		}

		/* send the slide list */
		echo "p[slides]:". json_encode($list);
		$this->infotv_server->send($user, "p[slides]:". json_encode($list));
	}

	function update() {
		global $wpdb;
		global $infotv;
		
		$str = $wpdb->get_row("select * from ". $infotv->slide_list_table);
		if($str == null) return;
		
		echo $str->list_str ."\n";
		$this->slidelist = json_decode("[". $str->list_str ."]");
		
		$args = array(
			"post_type" => "slide"
		);
		$query = new WP_Query($args);
		
		for($i = 0; $i < $query->post_count; $i++) {
			$post = $query->posts[$i];
			$slide = new infotv_slide($post->ID, $post->post_name,
					"", $post->post_content, $post->post_modified);
			$this->slides[$post->ID] = $slide;
		}
	}

	function receved($msg) {
		
	}
}

?>
