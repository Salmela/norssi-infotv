<?php

function to_ints($n) {
	return (intval($n) <= 0);
}

class slideshow_admin_slides extends slideshow_admin_page
{
	var $slidelist;

	function __construct() {
		$this->title       = "Slide arrangement";
		$this->logo        = "logo";
		$this->topname     = "Slideshow";
		$this->name        = "Arrangement";
		$this->url         = "slideshow-slides";
		$this->topmenu     = true;
		$this->permissions = "manage_categories";
		add_action("wp_ajax_change_slide_order", array($this, "change_order"));

	}

	function page_init() {
		global $wpdb;
		global $infotv;

		wp_register_script("slides-script",
				get_bloginfo('stylesheet_directory') ."/admin_slides.js", array("jquery-ui-sortable"));
		wp_enqueue_script("jquery");
		wp_enqueue_script("jquery-ui-core");
		wp_enqueue_script("jquery-ui-sortable");
		wp_enqueue_script("slides-script");

		/* create the slide list if it doesn't exist */
		if(! $wpdb->query("DESCRIBE ". $infotv->slide_list_table)) {
			if($wpdb->query("CREATE TABLE ". $infotv->slide_list_table ."(Num int, Id int, Duration int);")) {
				echo "Failed to create the slide list table\n";
			}
		}
	}

	function change_order() {
		if(!current_user_can($this->permissions)) return;

		$slides = $_POST["order"];

		/* check the data */
		$array = explode(",", $slides);
		$str = "";
		foreach($array as $value) {
			$num = intval($value);
			if($num > 0) {
				$str .= $num . ",";
			}
		}
		if($str == "") return;

		/* remove last "," from the list */
		$str = substr($str, 0, -1);

		global $wpdb;
		global $infotv;

		echo $wpdb->update($infotv->slide_list_table, array(
			"list_str" => $str
		), array('id' => 1110));
	}

	function get_slidelist() {
		$args = array(
			"post_type"    => "slide",
			"post_status"  => array("publish", "future"),
			"post__in"     => explode(",", $this->slidelist)
		);
		$query = new WP_Query($args);

		for($i = 0; $i < $query->post_count; $i++) {
			$id = $query->posts[$i]->ID;
			$author = $query->posts[$i]->author_name;
			$created = $query->posts[$i]->modified;
			$post_name = $query->posts[$i]->post_name;

			print("<div id=\"slide_$id\" data-author=\"$author\" ".
				"data-interactive=\"false\" data-created=\"$created\">$post_name</div>\n");
		}
	}

	function get_archives() {
		$args = array(
			"post_type"    => "slide",
			"post_status"  => array("publish", "future"),
			"post__not_in" => explode(",", $this->slidelist)
		);
		$query = new WP_Query($args);

		for($i = 0; $i < $query->post_count; $i++) {
			$id = $query->posts[$i]->ID;
			$author = $query->posts[$i]->author;
			$created = $query->posts[$i]->modified;
			$post_name = $query->posts[$i]->post_name;

			print("<div id=\"slide_$id\" data-author=\"$author\" ".
				"data-interactive=\"false\" data-created=\"$created\">$post_name</div>\n");
		}
	}

	function page_content() {
		global $wpdb;
		global $infotv;

		$row = $wpdb->get_row("SELECT * FROM ". $infotv->slide_list_table);

		if($row == null) {
			$this->slidelist = "";
		} else {
			$this->slidelist = $row->list_str;
		}
?>
<div class="wrap">
 	<div id="icon-infotv" class="icon32 icon32-infotv"><br></div>
	<h2>Infotv</h2>

	<div class="hbox">
	<div class="slidelists" id="current_slide_list">
		<?php $this->get_slidelist(); ?>
	</div>
	<div id="slide-panel">
		<h3>Slide info</h3>
		<div id="slide-image"><img /></div>
		<ol id="slide-info">
			<li>Name:</li>
			<li>Date:</li>
			<li>Creator:</li>
			<li>Interactive:</li>
		</ol>
	</div>
	</div>
	<div class="slidelists" id="new_slide_list">
		<?php $this->get_archives(); ?>
	</div>
</div>
<?php
	}
}

?>
