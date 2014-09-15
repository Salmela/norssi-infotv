<?php

class slideshow_admin_fonts extends slideshow_admin_page
{
	
	function __construct() {
		$this->title       = "Slideshow fonts";
		$this->logo        = "logo";
		$this->name        = "Fonts";
		$this->url         = "slideshow-fonts";
		$this->topmenu     = false;
		$this->topmenu_url = "slideshow-slides";
		$this->permissions = "manage_categories";
		//add_action("wp_ajax_change_slide_order", array($this, "change_order"));
	}
	function page_init() {
		wp_register_style("admin-fonts-style",
				get_bloginfo('stylesheet_directory') ."/admin_fonts.css");
		wp_enqueue_style("admin-fonts-style");
	}
	function slideshow_fonts_get_defaults() {
		$fonts_array = array(
			array("Bangers", "OFL",
				array("normal", "normal", "OFL/Bangers.woff")),
			array("Bowlby One SC", "OFL",
				array("normal", "normal", "OFL/BowlbyOneSC.woff")),
			array("Carme", "OFL",
				array("normal", "normal", "OFL/Carme-Regular.woff")),
			array("Delius", "OFL",
				array("normal", "normal", "OFL/Delius-Regular.woff")),
			array("Fontdiner Swanky", "Apache",
				array("normal", "normal", "Apache/FontdinerSwanky.woff")),
			array("Gentium Basic", "OFL",
				array("normal", "normal", "OFL/GenBasB.woff")),
			array("Gloria Hallelujah", "OFL",
				array("normal", "normal", "OFL/GloriaHallelujah.woff")),
			array("IM Fell Double Pica SC", "OFL",
				array("normal", "normal", "OFL/IMFellDoublePicaSC.woff")),
			array("Josefin Sans", "OFL",
				array("normal", "400", "OFL/JosefinSans-Regular.woff"),
				array("italic", "400", "OFL/JosefinSans-Italic.woff"),
				array("normal", "700", "OFL/JosefinSans-Bold.woff"),
				array("italic", "700", "OFL/JosefinSans-BoldItalic.woff")),
			array("Lobster Two", "OFL",
				array("normal", "400", "OFL/LobsterTwo-Regular.woff"),
				array("italic", "400", "OFL/LobsterTwo-Italic.woff"),
				array("normal", "700", "OFL/LobsterTwo-Bold.woff"),
				array("italic", "700", "OFL/LobsterTwo-BoldItalic.woff")),
			array("OFL Sorts Mill Goudy TT", "OFL",
				array("normal", "normal", "OFL/OFLGoudyStMTT.woff"),
				array("italic", "normal", "OFL/OFLGoudyStMTT-Italic.woff")),
			array("Permanent Marker", "Apache",
				array("normal", "normal", "Apache/PermanentMarker.woff")),
			array("Pompiere", "OFL",
				array("normal", "normal", "OFL/Pompiere-Regular.woff")),
			array("Sniglet", "OFL",
				array("normal", "800", "OFL/Sniglet-Regular.woff")),
			array("Ultra", "Apache",
				array("normal", "normal", "Apache/Ultra.woff")),
			array("Waiting for the Sunrise", "OFL",
				array("normal", "normal", "OFL/WaitingfortheSunrise.woff"))
		);
		update_option("slideshow_fonts", $fonts_array);
		return $fonts_array;
	}
	function create_font_list() {
		$fonts = get_option("slideshow_fonts");
		if(! $fonts) $fonts = $this->slideshow_fonts_get_defaults();

		
		foreach($fonts as $i => $font) {
			echo <<<END
		<tr class="font-tr">
			<td colspan="2" style="font-family: {$font[0]};">{$font[0]}</td>
			<td>{$font[1]}</td>
		</tr>
END;
			for($i = 0; $i < count($font)-2; $i++) {
				echo <<<END
		<tr>
			<td>font-style:{$font[2+$i][0]}</td>
			<td>font-weight:{$font[2+$i][1]}</td>
			<td>url:{$font[2+$i][2]}</td>
		</tr>
END;
			}
		}
	}
	function page_content() {
?>
<div class="wrap">
 	<div id="icon-infotv" class="icon32 icon32-fonts"><br></div>
	<h2>Infotv - Fonts</h2>
	Tällä sivulla on InfoTV:ssä käytettävien fonttien lista <button id="new_font">Lisää fontti</button>
	<table id="font-table" class="widefat">
		<thead>
		<tr>
			<th colspan="2">Name</th>
			<th>Lisence</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th colspan="2">Name</th>
			<th>Lisence</th>
		</tr>
		</tfoot>
		<tbody>
<?php
$this->create_font_list();
?>
		</tbody>
	</table>
</div>
<?php
	}
}

?>
