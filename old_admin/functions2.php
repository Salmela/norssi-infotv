<?php

require_once (TEMPLATEPATH . "/admin_page.php");
require_once (TEMPLATEPATH . "/admin_cal.php");
require_once (TEMPLATEPATH . "/admin_fonts.php");
require_once (TEMPLATEPATH . "/admin_tracker.php");

update_option("tag_history", "Helsingin suomalainen tyttökoulu 1869-1919\n".
           "Helsingin suomalainen tyttölyseo 1919-1934\n".
           "Tyttönormaalilyseo 1934-1969\n".
           "Helsingin yhteisnormaalikoulu 1969-1974\n".
           "Helsingin II normaalikoulu 1974-2003\n".
           "Helsingin yliopiston Viikin normaalikoulu 2003-");

//init
function slideshow_init() {
	global $wp_taxonomies;
	unset($wp_taxonomies["category"]);
	unset($wp_taxonomies["post_tag"]);
	
	$args = array(
		"labels" => array(
			"menu_name" => "Slide",
			"add_new_item" => "Add new slide",
			"edit_item" => "Edit slide",
			"new_item" => "New slide",
			"view_item" => "View slide",
			"search_items" => "Search slides",
			"not_found" => "No slides found",
			"not_found_in_trash" => "No slides found in Trash",
			"" => ""),
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true, 
		"show_in_menu" => true,
		"capability_type" => "post",
		"menu_position" => 4,
		"supports" => array("title", "editor", "custom-fields", "revisions", "author")
	); 
	register_post_type("slide", $args);
	/*register_taxonomy("slideshow-state", "slide", array(
		"hierarchical" => true,
		"show_ui" => false,
		"update_count_callback" => "_update_post_term_count",
		"query_var" => true
	));*/
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
		return;

	if ( get_user_option('rich_editing') == 'true') {
		add_filter("mce_buttons", "slideshow_mce_buttons", 0);
		add_filter("mce_buttons_2", "slideshow_mce_buttons2", 0);
		add_filter("mce_css", "slideshow_mce_css");
		add_filter("mce_external_plugins", "slideshow_mce_plugin");

		remove_filter("the_content", "wpautop");
		add_filter("tiny_mce_before_init", "slideshow_mce");
	}
}
add_action("init", "slideshow_init");

// modify text editor
function slideshow_mce_buttons() {
	return array('bold', 'italic', 'strikethrough', 'underline', '|', 'bullist', 'numlist', 'blockquote', '|', 'justifyleft', 'justifycenter', 'justifyright', 'justifyfull', '|', 'pastetext', 'pasteword', 'removeformat', '|', 'spellchecker', 'fullscreen', 'wp_adv', 's_windowselector');
}

function slideshow_mce_buttons2() {
	return array('formatselect', 'fontselect', 'fontsizeselect', 'styleselect', 'forecolor', '|', 'charmap', '|', 'outdent', 'indent', '|', 'undo', 'redo', 'wp_help');
}

function slideshow_mce_css($mce_css) {
	if (! empty($mce_css)) $mce_css .= ",";
	
	$mce_css .= get_stylesheet_directory_uri() . "/tinymce.css, ".
		get_stylesheet_directory_uri() . "/fonts.css";

	return $mce_css; 
}

function slideshow_mce_plugin($plugin_array) {
	$plugin_array['slideshow'] = get_stylesheet_directory_uri() . '/tinymce.js';
	return $plugin_array; 
}

function slideshow_mce( $init ) {
  $init["theme_advanced_fonts"] =
        'Monospace=Monospace,Andale Mono;'.
        'Sans=Sans;'.
        'Serif=Serif,Sans-serif;'.
        '------------------=-;'.
        'Bangers=Bangers;'.
        'Bowlby One SC=Bowlby One SC;'.
        'Carme=Carme;'.
        'Delius=Delius;'.
        'Fontdiner Swanky=Fontdiner Swanky;'.
        'Gentium Basic=Gentium Basic;'.
        'Gloria Hallelujah=Gloria Hallelujah;'.
        'IM Fell Double Pica SC=IM Fell Double Pica SC;'.
        'Josefin Sans=Josefin Sans;'.
        'Lobster Two=Lobster Two;'.
        'OFL Sorts Mill Goudy TT=OFL Sorts Mill Goudy TT;'.
        'Permanent Marker=Permanent Marker;'.
        'Pompiere=Pompiere;'.
        'Sniglet=Sniglet;'.
        'Ultra=Ultra;'.
        'Waiting for the Sunrise=Waiting for the Sunrise';
  //$init['apply_source_formatting'] = false;
  $init["force_br_newlines"] = false;
  $init["force_p_newlines"] = false;
  $init["forced_root_block"] = '';
  $init["font_size_style_values"] = "50%,100%,150%,200%,250%,300%,350%,400%";
  $init["theme_advanced_font_sizes"] = "50%,100%,150%,200%,250%,300%,350%,400%";
  $init["theme_advanced_blockformats"] = "p,h1,h2,h3,h4,h5,h6";
  $init["theme_advanced_styles"] = "ei viela kaytossa=a";
  return $init;
}


// basic theme setup
function slideshow_show_setup() {
	add_option("gradient_grad", array(176, 48, 160, 112, 32, 96));
	add_option("gradient_grad2", array(240, 208, 128, 240, 176, 32));
  
	add_option("default_slide_time", 5);
	/* REMOVE */add_option("new_slide", "False"); // for ajax update
  
	add_option("timetable");
	add_option("tt-time");
	add_option("tt-bus-amount", 10);
}

add_action("after_setup_theme", "slideshow_show_setup");

// remove support to Micro$oft junk
remove_action("wp_head", "wlwmanifest_link");
remove_action("wp_head", "rsd_link");

function slideshow_footer_text() {
  return "InfoTV developed by Aleksi Salmela. Powered by <a href=\"http://www.wordpress.org\">Wordpress</a>";
}

add_filter("admin_footer_text", "slideshow_footer_text");

// dashboard

function infotv_help_widget_function() {
  echo <<<END
  <p>Tämä on infotv:n muokkaus sivu. Voit tehdä uuden slide:n/dia:n vasemalla olevan Posts alla olevasta Add New linkistä. Kun sivu on avautunut, ensimmäinen tekstikenttä on slide:n/dia:n nimi. Se näkyy infotv:n ala reunassa. Seuraava tekstikenttä on itse slide/dia.</p>
  <h4>Kuvat</h4><p>Voit lisätä kuvia otsikko tekstikentän alapuolelta löytyvän Upload/Insert: <img src="images/media-button-image.gif" alt="Add an Image"> kuvaketta painamalla. Sivulle ilmestyy uusi "ikkuna", jonka keskellä lukee Select Files. Kun painat siitä voit valita kuvan tietokoneeltasi.</p>
  <p>Jos tiedät kuvan osoitteen internetissä voit mennä saman ikkunan From URL välilehteen ja kirjoittamalla Image URL:in edeltävään tekstikenttään osoiteriviltä kopioiuitu teksti.</p>
  <p>Kun olet saanut kuvan haettua voit valita Alignment edeltävästä listasta mihin kohtaan haluat kuvan. Title:ssä lukevaa tekstiä käytetään jos kuvaa ei löydy tai sitä ei pystytä näyttämään. Ja lopuksi paina Insert into Post.</p>
  <h4>Viimeistely</h4><p>Kun olet saanut slide:stä/dia:sta mieleisesi paina oikealla lukevaa Preview nappia ja mieti kuinka kauan haluat slide:n/dia:n näkyvän. Sulje uusi välilehti tai ikkuna ja etsi Custom Field laatikko, kirjoita Name:n alapuolella olevaan tekstikenttää "kesto" (ilman lainaus merkkejä). Ja kirjoita Value:n alapuolella olevaan tekstikenttään miettimäsi aika sekunneissa pelkillä numeroilla ja erota desimaalit <strong>pisteellä</strong>. Kun olet valmis paina Publish painiketta Preview painikkeen alapuolella.</p>
  <h4>Huomioita</h4><p>Sliden leveys on noin 1000 kuvapistettä (engl. pixels)</p>
END;
}

function infotv_help_widget_add() {
  global $wp_meta_boxes;
	
  unset($wp_meta_boxes["dashboard"]["normal"]["core"]["dashboard_plugins"]);
  unset($wp_meta_boxes["dashboard"]["normal"]["core"]["dashboard_right_now"]);
  unset($wp_meta_boxes["dashboard"]["normal"]["core"]["dashboard_recent_comments"]);
  unset($wp_meta_boxes["dashboard"]["normal"]["core"]["dashboard_incoming_links"]);
  unset($wp_meta_boxes["dashboard"]["side"]["core"]["dashboard_quick_press"]);
  unset($wp_meta_boxes["dashboard"]["side"]["core"]["dashboard_recent_drafts"]);
  unset($wp_meta_boxes["dashboard"]["side"]["core"]["dashboard_primary"]);
  unset($wp_meta_boxes["dashboard"]["side"]["core"]["dashboard_secondary"]);
  
  wp_add_dashboard_widget("infotv_help_widget", "Infotv", "infotv_help_widget_function");	
}

add_action("wp_dashboard_setup", "infotv_help_widget_add" );

function global_content_of_today_is_empty(){
	echo "<div class=\"error\"><p>Et ole kirjoittanut tämän päivän maailman tapahtumat tekstiä. <a href=\"admin.php?page=slideshow-calendar&month=". date("n") ."&day=". date("j") ."\">Tee se tästä</a></p></div>";
}
function global_content_of_tomorrow_is_empty(){
	global $slideshow_content_of_tomorrow_date;
	$stamp = $slideshow_content_of_tomorrow_date;
	echo "<div class=\"error\"><p>Et ole kirjoittanut huomisen maailman tapahtumat tekstiä. <a href=\"admin.php?page=slideshow-calendar&month=". date("n", $stamp) ."&day=". date("j", $stamp) ."\">Tee se tästä</a></p></div>";
}
function slideshow_admin_head() {
	global $slideshow_content_of_tomorrow_date;
	
	$dir = get_bloginfo('template_directory'); // get_stylesheet_directory_uri()
	echo<<<END
	<link rel="shortcut icon" href="$dir/images/favicon.png" />
	<link rel="stylesheet" type="text/css" href="$dir/style-admin.css" />
	<link rel="stylesheet" type="text/css" href="$dir/fonts.css" />
	<style type="text/css">
		#header-logo { background-image: url($dir/images/infotv-icon.png) !important; }\n";
	</style>
END;
	
	$calendar = get_option("calendar");
	$stamp = mktime(0, 0, 0, date("n"), date("j")+1, date("Y"));
	
	if($calendar[date("n")][date("j")] == "" &&
	   date("N") != 6 && date("N") != 7) {
		add_action("admin_notices", "global_content_of_today_is_empty");
	}
	if($calendar[date("n", $stamp)][date("j", $stamp)] == "" &&
	   date("N", $stamp) != 6 && date("N", $stamp) != 7) {
		$slideshow_content_of_tomorrow_date = $stamp;
		add_action("admin_notices", "global_content_of_tomorrow_is_empty");
	}
}

add_action("admin_head", "slideshow_admin_head");

function slideshow_cal_get_ajax() {
	$month = intval($_POST["month"]);
	$day = intval($_POST["day"]);

	$calendar = get_option("calendar");
	echo $calendar[$month][$day];

	die();
}
add_action("wp_ajax_slideshow_get_day", "slideshow_cal_get_ajax");

function slideshow_cal_set_ajax() {
	$month = intval($_POST["month"]);
	$day = intval($_POST["day"]);
	if (get_magic_quotes_gpc()) {
		$text = stripslashes($_POST["text"]);
	} else {
		$text = $_POST["text"];
	}

	$calendar = get_option("calendar");
	$calendar[$month][$day] = $text;
	update_option("calendar", $calendar);
	
	echo 1;
	die();
}
add_action("wp_ajax_slideshow_set_day", "slideshow_cal_set_ajax");

// customize icons

function custom_login_logo() {
  echo "<style type=\"text/css\">";
  echo "\th1 a { background-image:url(" . get_bloginfo("template_directory") . "/images/infotv-login.png) !important; }";
  echo "</style>";
}

add_action("login_head", "custom_login_logo");

function slideshow_menu() {
  global $slideshow_op;

  global $menu;
  unset($menu[5]); // posts
  unset($menu[15]); // Links
  unset($menu[20]); // Pages
  unset($menu[25]); // Comments
  unset($menu[60]); // Appearance
  unset($menu[65]); // Plugins
  unset($menu[75]); // Tools
  unset($menu[99]); // separator
  
  remove_submenu_page("index.php", "update-core.php");
  remove_submenu_page("edit.php", "edit-tags.php?taxonomy=post_tag");
  remove_submenu_page("edit.php", "edit-tags.php?taxonomy=category");
  if(! current_user_can("administrator")) {
	unset($menu[80]);
  }
  if(current_user_can("manage_categories")) {

  list($main, $head, $load) = slideshow_page_func();

  $slideshow_op = add_menu_page("Slideshow Options", "Slideshow", "manage_categories", "slideshow-options", $main, get_theme_root_uri() . "/slideshow/images/menu-logo.png");
  add_action("admin_head-$slideshow_op", $head);
  add_action("load-$slideshow_op", $load);

  list($main, $head, $load) = slideshow_cal_func();
  $slideshow_cal = add_submenu_page("slideshow-options", "Slideshow Calendar", "Calendar", "manage_categories", "slideshow-calendar", $main);
  add_action("admin_head-$slideshow_cal", $head);
  add_action("load-$slideshow_cal", $load);

  list($main, $head, $load) = slideshow_fonts_func();
  $slideshow_fonts = add_submenu_page("slideshow-options", "Slideshow Fonts", "Fonts", "manage_categories", "slideshow-fonts", $main);
  add_action("admin_head-$slideshow_fonts", $head);
  add_action("load-$slideshow_fonts", $load);

  list($main, $head, $load) = slideshow_tracker_func();
  $slideshow_tracker = add_submenu_page("slideshow-options", "Track InfoTV clients", "Tracker", "manage_categories", "slideshow-tracker", $main);
  add_action("admin_head-$slideshow_tracker", $head);
  add_action("load-$slideshow_tracker", $load);
  }
}

add_action("admin_menu", "slideshow_menu");

?>
