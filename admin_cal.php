<?php

$months = array("Tammikuu", "Helmikuu", "Maaliskuu",
		"Huhtikuu", "Toukokuu", "Kesäkuu", "Heinäkuu", "Elokuu",
		"Syyskuu", "Lokakuu", "Marraskuu", "Joulukuu");

class slideshow_admin_calendar extends slideshow_admin_page
{
	
	function __construct() {
		$this->title       = "Slideshow calendar";
		$this->logo        = "logo";
		$this->name        = "Calendar";
		$this->url         = "slideshow-calendar";
		$this->topmenu     = false;
		$this->topmenu_url = "slideshow-slides";
		$this->permissions = "manage_categories";
		//add_action("wp_ajax_change_slide_order", array($this, "change_order"));
	}
	function page_init() {
		wp_register_style("admin-cal-style",
				get_bloginfo('stylesheet_directory') ."/admin_cal.css");
		wp_enqueue_style("admin-cal-style");
	}
	function create_calendar() {
		global $months;
	
		if(!isset($_GET["day"]) || !isset($_GET["month"])) {
			$show_month = (int) date("n");
			$show_day = (int) date("j");
		} else {
			$show_month = $_GET["month"];
			$show_day = $_GET["day"];
		}
	
		$calendar = get_option("infotv_calendar");
		if(isset($_POST["change"])) {
			$calendar[$show_month][$show_day] = $_POST["global_content"];
			update_option("infotv_calendar", $calendar);
		}

		for($month_num = 1; $month_num <= 12; $month_num++) {
			echo "\t\t<table class=\"month\" id=\"month-$month_num\"><caption>{$months[$month_num-1]}</caption>\n";
		
			$start_day = date("w", mktime(0, 0, 0, $month_num, 0, date("Y")));
			$days = date("d", mktime(0, 0, 0, $month_num+1, -1, date("Y")));
		
			echo "\t\t\t<tr>";
		
			if($start_day != 0) {
				echo "<td class=\"month_padding\" colspan=\"$start_day\"> </td>";
			}
		
			$d = $start_day;
			for ($v = 0; $v < 6; $v++) {
				if(($v * 7 + $d - $start_day) > $days) {
					echo "<td class=\"month_padding\">&nbsp;</td>";
				}
				for(; $d < 7 && ($v * 7 + $d - $start_day) <= $days; $d++) {
					$css = "";
					$day = $v * 7 + $d - $start_day + 1;
				
					if($day == $show_day && $month_num == $show_month)
						$css .= "id=\"this_day\"";
					
					if($day == date("j") && $month_num == date("n"))
						$css .= "id=\"today\"";
					
					if($calendar[$month_num][$day] != "")
						$css .= " class=\"filled_day\"";
				
					echo "<td $css><a href=\"./admin.php?page=slideshow-calendar&month=$month_num&day=$day\">$day</a></td>";
				}
			
				echo "</tr><tr>\n";
				$d = 0;
			}
			echo "</tr>\t\t</table>\n";
		}
		return $calendar[$show_month][$show_day];
	}
	function page_content() {
?>
<div class="wrap">
 	<div id="icon-infotv" class="icon32 icon32-calendar"><br></div>
	<table class="month" style="float:right">
		<tr>
			<td><a href="#" style="color:red;">1</a></td>
			<td>Tänään</td>
		</tr>
		<tr>
			<td style="padding:2px"><a href="#" style="background:#f88;border: 1px solid #000;">2</a></td>
			<td>Tällä hetkellä näkyy</td>
		</tr>
		<tr>
			<td style="background:#8f8;"><a href="#">3</a></td>
			<td>Täytetty</td>
		</tr>
		<tr>
			<td><a href="#">4</a></td>
			<td>Tyhjä</td>
		</tr>
	</table>
	<h2>Infotv - Calendar</h2>
	Tämä sivu näyttää tämän vuoden päivät ja niihin liitetyt tekstit.
	<div id="calendar">
<?php
$global_content = $this->create_calendar();
?>
	</div>
	<div id="form">
		<form method="post" enctype="application/x-www-form-urlencoded" action="admin.php?page=slideshow-calendar&month=$show_month&day=$show_day">
		<input type="hidden" id="hidden-day" value="$show_day">
		<input type="hidden" id="hidden-month" value="$show_month">
		<input type="hidden" name="change" value="true">
		<textarea class="content" name="global_content"><?php echo $global_content; ?></textarea><br>
		<input name="submit" id="submit" class="button-primary" value="Save Changes" type="submit">
		</form>
	</div>
	<h2>Options</h2>
	<div id="grad_preview"> </div><h4>Header/Footer gradient</h4>
	<table class="form-table">
	<tr valign="top">
		<th scope="row">Top:</th>
		<td>R=<input type="text" name="1r" size="3" class="grad1" value="{$grad[0]}">,
			G=<input type="text" name="1g" size="3" class="grad1" value="{$grad[1]}">,
			B=<input type="text" name="1b" size="3" class="grad1" value="{$grad[2]}"></td>
	</tr>
	<tr valign="top">
		<th scope="row">Bottom:</th>
		<td>R=<input type="text" name="1r2" size="3" class="grad1" value="{$grad[3]}">,
			G=<input type="text" name="1g2" size="3" class="grad1" value="{$grad[4]}">,
			B=<input type="text" name="1b2" size="3" class="grad1" value="{$grad[5]}"></td>
	</tr>
	</table>
	
	<div id="grad2_preview"> </div><h4>Clock gradient</h4>
	<table class="form-table">
	<tr valign="top">
		<th scope="row">Top:</th>
		<td>R=<input type="text" name="2r" size="3" class="grad2" value="{$grad2[0]}">,
			G=<input type="text" name="2g" size="3" class="grad2" value="{$grad2[1]}">,
			B=<input type="text" name="2b" size="3" class="grad2" value="{$grad2[2]}"></td>
	</tr>
	<tr valign="top">
		<th scope="row">Bottom:</th>
		<td>R=<input type="text" name="2r2" size="3" class="grad2" value="{$grad2[3]}">,
			G=<input type="text" name="2g2" size="3" class="grad2" value="{$grad2[4]}">,
			B=<input type="text" name="2b2" size="3" class="grad2" value="{$grad2[5]}"></td>
	</tr>
	</table>
	
	<h4>Slide options</h4>
	<table class="form-table">
	<tr valign="top">
		<th scope="row">Default slide time:</th>
		<td><input type="text" name="def_slide_time" value="{$slidetime}" style="text-align:right;"> seconds</td>
	</tr>
	<tr valign="top">
		<th scope="row">Disable subject bar:</th>
		<td><input type="checkbox" name="disable_footer"></td>
	</tr>
	</table>
	</div>
<?php
	}
}

?>
