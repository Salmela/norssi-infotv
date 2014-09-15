<?php

require_once(TEMPLATEPATH . "/admin_slides.php");
require_once(TEMPLATEPATH . "/admin_cal.php");
require_once(TEMPLATEPATH . "/admin_fonts.php");
//require_once(TEMPLATEPATH . "/install.php");

/*
 websocket_status:
  0. off
  1. starting
  2. ready
  3. running
  4. closeing
*/

class slideshow_websocket
{
	function start() {
		global $infotv;
		$i = get_option($infotv->prefix ."websocket_status", 0);
		
		if($i == 0) {
			//$srv = popen("php '". get_theme_root() ."/slideshow-new/websocket_server/websocket.php' &", "r");
			$pid = shell_exec("cd '". get_theme_root()
				."/slideshow-new/websocket_server/'; nohup php ./websocket.php > /dev/null & echo $!");
			update_option($infotv->prefix ."websocket_pid", $pid);
			update_option($infotv->prefix ."websocket_status", 1);
		} else {
			//allready running
		}
	}
	function end() {
		global $infotv;
		if($i == 2 || $i == 3) {
			$i = get_option($infotv->prefix ."websocket_status", 0);
			update_option($infotv->prefix ."websocket_status", 4);
			update_option($infotv->prefix ."websocket_pid", -1);
		}
	}
	function get_status() {
		global $infotv;

		$pid = get_option($infotv->prefix ."websocket_pid", -1);
		print("pid $pid\n");
		if($pid == -1 || !file_exists("/proc/$pid")) {
			update_option($infotv->prefix ."websocket_status", 0);
		}
		$i = get_option($infotv->prefix ."websocket_status", 0);

		switch($i) {
		case 0:
			return "off";
		case 1:
			return "starting";
		case 2:
			return "ready";
		case 3:
			return "running";
		case 4:
			return "closeing";
		}
	}
	function stop() {
		set_option($infotv->prefix ."websocket_status", 4);
	}
}

class slideshow_admin_page
{
	var $title;
	var $name, $topname;
	var $url;
	var $topmenu;
	var $topmenu_url;
	var $permissions;
	var $logo;

	function page_init() {
	}
	function page_content() {
	}
	function page_header() {
	}

	function menu_show() {
		if($this->topmenu) {
			$menu = add_menu_page($this->title, $this->topname,
				$this->permissions, $this->url, array(&$this, "page_content"),
				get_theme_root_uri() ."/slideshow-new/images/menu-{$this->logo}.png");
			$menu = add_submenu_page($this->url, $this->title, $this->name,
				$this->permissions, $this->url, array(&$this, "page_content"));
		} elseif($this->topmenu_url) {
			$menu = add_submenu_page($this->topmenu_url, $this->title, $this->name,
				$this->permissions, $this->url, array(&$this, "page_content"));
		} else return;

		add_action("admin_head-$menu",    array(&$this, "page_header"));
		add_action("load-$menu",          array(&$this, "page_init"));
	}
}

class slideshow 
{
	var $page_fonts;
	var $page_slides;

	var $prefix;
	var $slide_client_table;
	var $slide_list_table;

	var $websocket;

	function __construct() {
		global $wpdb;
		$this->page_fonts = new slideshow_admin_fonts();
		$this->page_slides = new slideshow_admin_slides();
		$this->page_cal  = new slideshow_admin_calendar();
		$this->websocket = new slideshow_websocket();
		
		$this->prefix = $wpdb->prefix ."slides_";
		$this->slide_client_table = $this->prefix ."clients";
		$this->slide_list_table   = $this->prefix ."list";
		
		add_action("init", array(&$this, "init"));

		/* re-branding */
		add_filter("admin_footer_text",  array(&$this, "admin_footer"));
		add_action("admin_head",         array(&$this, "admin_head"));
		add_action("admin_menu",         array(&$this, "admin_navigation"));
		add_action("wp_dashboard_setup", array(&$this, "admin_dashboard"));
		
		add_action("login_head",         array(&$this, "login_logo"));
		
		/* remove support to Micro$oft junk */
		remove_action("wp_head",         "wlwmanifest_link");
		remove_action("wp_head",         "rsd_link");
	}

	function init() {
		$this->slide_init();
		$mce = new slideshow_mce();
	}

	function slide_init() {
		$args = array(
			"labels" => array(
				"menu_name"          => "Slide",
				"add_new_item"       => "Add new slide",
				"edit_item"          => "Edit slide",
				"new_item"           => "New slide",
				"view_item"          => "View slide",
				"search_items"       => "Search slides",
				"not_found"          => "No slides found",
				"not_found_in_trash" => "No slides found in Trash",
				""  => ""),
			"public"             => true,
			"publicly_queryable" => true,
			"show_ui"            => true, 
			"show_in_menu"       => true,
			"menu_icon"          => get_bloginfo('template_directory') . "/images/portfolio-icon.png",
			"capability_type"    => "post",
			"menu_position"      => 4,
			"supports" => array("title", "editor", "custom-fields",
			                    "revisions", "author")
		); 
		register_post_type("slide", $args);
		/*register_taxonomy("slideshow-state", "slide", array(
			"hierarchical" => true,
			"show_ui" => false,
			"update_count_callback" => "_update_post_term_count",
			"query_var" => true
		));*/
	}

	//re-branding
	function admin_head() {
		$dir = get_bloginfo('template_directory');

		echo "\t<link rel=\"shortcut icon\" href=\"$dir/images/favicon.png\" />\n";
		echo "\t<link rel=\"stylesheet\" type=\"text/css\" href=\"$dir/style-admin.css\" />\n";
		echo "\t<link rel=\"stylesheet\" type=\"text/css\" href=\"$dir/fonts.css\" />\n";
		echo "\t<style type=\"text/css\">\n";
		echo "\t\t#header-logo { background-image: url($dir/images/infotv-icon.png) !important; }\n";
		echo "\t</style>\n";

		//calendar->admin_notice();
	}
	function admin_footer() {
		return "InfoTV developed by Aleksi Salmela. Powered by <a href=\"http://www.wordpress.org\">Wordpress</a>";
	}
	function admin_navigation() {
		global $menu;

		/* remove menuitems: posts, links, pages, comments */
		unset($menu[5],  $menu[15], $menu[20], $menu[25]);
		/* remove menuitems: appearance, plugins, tools, separator */
		unset($menu[60], $menu[65], $menu[75], $menu[99]);

		/* remove settings menu if the user is not adminstrator */
		if(! current_user_can("administrator")) unset($menu[80]);

		remove_submenu_page("index.php", "update-core.php");

		if(current_user_can("manage_categories")) {
			$this->page_slides->menu_show();
			$this->page_cal->menu_show();
			$this->page_fonts->menu_show();
		}
	}

	function login_logo() {
		echo "<style type=\"text/css\">\n";
		echo "\th1 a { background-image:url(" . get_bloginfo("template_directory") .
		       "/images/infotv-login.png) !important; }\n";
		echo "</style>";
	}

	function admin_dashboard() {
		global $wp_meta_boxes;

		/* remove all widgets */
		$wp_meta_boxes["dashboard"]["normal"]["core"] = array();
		$wp_meta_boxes["dashboard"][ "side" ]["core"] = array();

		add_meta_box("infotv_help_widget",  "Help",
			array(&$this, "help_dashboard_widget"), "dashboard", "normal");
		add_meta_box("infotv_status_widget", "Status",
			array(&$this, "status_dashboard_widget"), "dashboard", "side");

		/* remove dashboard widgets */
/*		$w_n = $wp_meta_boxes["dashboard"]["normal"]["core"];
		$w_s = $wp_meta_boxes["dashboard"][ "side" ]["core"];
		$w_s["infotv_status_widget"] = $w_n["infotv_status_widget"];
		
		unset($w_n["infotv_status_widget"],     $w_n["dashboard_plugins"],
		      $w_n["dashboard_right_now"],      $w_n["dashboard_recent_comments"],
		      $w_n["dashboard_incoming_links"],	$w_s["dashboard_quick_press"],
		      $w_s["dashboard_recent_drafts"],  $w_s["dashboard_primary"],
		      $w_s["dashboard_secondary"]);
		
		$wp_meta_boxes["dashboard"]["normal"]["core"] = $w_n;
		$wp_meta_boxes["dashboard"][ "side" ]["core"] = $w_s;
*/
	}

	function help_dashboard_widget() {
?>
	<p>Tämä on infotv:n muokkaus sivu. Voit tehdä uuden slide:n/dia:n vasemalla olevan Posts alla olevasta Add New linkistä. Kun sivu on avautunut, ensimmäinen tekstikenttä on slide:n/dia:n nimi. Se näkyy infotv:n ala reunassa. Seuraava tekstikenttä on itse slide/dia.</p>
	<h4>Kuvat</h4><p>Voit lisätä kuvia otsikko tekstikentän alapuolelta löytyvän Upload/Insert: <img src="images/media-button-image.gif" alt="Add an Image"> kuvaketta painamalla. Sivulle ilmestyy uusi "ikkuna", jonka keskellä lukee Select Files. Kun painat siitä voit valita kuvan tietokoneeltasi.</p>
	<p>Jos tiedät kuvan osoitteen internetissä voit mennä saman ikkunan From URL välilehteen ja kirjoittamalla Image URL:in edeltävään tekstikenttään osoiteriviltä kopioiuitu teksti.</p>
	<p>Kun olet saanut kuvan haettua voit valita Alignment edeltävästä listasta mihin kohtaan haluat kuvan. Title:ssä lukevaa tekstiä käytetään jos kuvaa ei löydy tai sitä ei pystytä näyttämään. Ja lopuksi paina Insert into Post.</p>
	<h4>Viimeistely</h4><p>Kun olet saanut slide:stä/dia:sta mieleisesi paina oikealla lukevaa Preview nappia ja mieti kuinka kauan haluat slide:n/dia:n näkyvän. Sulje uusi välilehti tai ikkuna ja etsi Custom Field laatikko, kirjoita Name:n alapuolella olevaan tekstikenttää "kesto" (ilman lainaus merkkejä). Ja kirjoita Value:n alapuolella olevaan tekstikenttään miettimäsi aika sekunneissa pelkillä numeroilla ja erota desimaalit <strong>pisteellä</strong>. Kun olet valmis paina Publish painiketta Preview painikkeen alapuolella.</p>
	<h4>Huomioita</h4><p>Sliden leveys on noin 1000 kuvapistettä (engl. pixels)</p>
<?php
	}

	function status_dashboard_widget() {
		if(isset($_GET["infotv_state"]) && $_GET["infotv_state"] == "start") {
			$this->websocket->start();
		}
		$websocket_status = $this->websocket->get_status();
		$s = get_option($this->prefix ."websocket_status", 0);

		switch($s) {
		case 0:
			$color   = "red";
			$options = array("start");
			break;
		case 2:
		case 3:
			$color   = "green";
			$options = array("restart", "stop");
			break;
		default:
			$color   = "blue";
			$options = array();
		}

		$option = "";
		if($options)
			$option = "<option>". implode("</option><option>", $options) ."</option>";

		echo <<<END
	<form>
	<select name="infotv_state" style="color:$color" onchange="this.form.submit();">
		<option selected="selected">$websocket_status</option>
		$option
	</select>
	<div style="padding: 2px 0;">Websockets:</div>
	<hr />
	<div class="right">?</div><div>Televisiot:</div>
	<div class="right">?</div><div>Auditorio:</div>
	<input class="right" type="submit" value="Päivitä">
	<div style="clear:both;"></div>
	</form>
END;
	}
}

class slideshow_mce
{
	function __construct() {
		/*if(current_user_can('edit_posts') && get_user_option('rich_editing') == 'true') {
			add_filter("mce_buttons", "slideshow_mce_buttons", 0);
			add_filter("mce_buttons_2", "slideshow_mce_buttons2", 0);
			add_filter("mce_css", "slideshow_mce_css");
			add_filter("mce_external_plugins", "slideshow_mce_plugin");

			remove_filter("the_content", "wpautop");
			add_filter("tiny_mce_before_init", "slideshow_mce");
		}*/
	}
}

$infotv = new slideshow();


?>
