<?php

class slideshow_admin_clients extends slideshow_admin_page
{
	
	function __construct() {
		$this->title       = "Slideshow clients";
		$this->logo        = "logo";
		$this->name        = "Fonts";
		$this->url         = "slideshow-clients";
		$this->topmenu     = false;
		$this->topmenu_url = "slideshow-slides";
		$this->permissions = "manage_categories";
		//add_action("wp_ajax_change_slide_order", array($this, "change_order"));
	}
	function page_init() {
		//wp_register_style("admin-fonts-style",
		//		get_bloginfo('stylesheet_directory') ."/admin_fonts.css");
		//wp_enqueue_style("admin-fonts-style");
	}
	function create_client_list() {
/*		$fonts = get_option("slideshow_fonts");
		if(! $fonts) $fonts = $this->slideshow_fonts_get_defaults();

		
		foreach($fonts as $i => $font) {
			echo <<<END
		<tr class="font-tr">
			<td>{$font[0]}</td>
			<td>{$font[1]}</td>
		</tr>
END;
			}
		}
*/
	}
	function page_content() {
?>
<div class="wrap">
 	<div id="icon-infotv" class="icon32 icon32-clients"><br></div>
	<h2>Infotv - Clients</h2>
	<table id="client-table" class="widefat">
		<thead>
		<tr>
			<th>Name</th>
			<th>Ping</th>
			<th>State</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th>Name</th>
			<th>Ping</th>
			<th>State</th>
		</tr>
		</tfoot>
		<tbody>
<?php
$this->create_client_list();
?>
		</tbody>
	</table>
</div>
<?php
	}
}

?>
