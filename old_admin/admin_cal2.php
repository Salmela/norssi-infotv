<?php

function slideshow_option_update() {
  $error = "";
  
  // header and footer gradient colors
  if (is_numeric($_POST["1r"]) && is_numeric($_POST["1g"]) && is_numeric($_POST["1b"]) && is_numeric($_POST["1r2"]) && is_numeric($_POST["1g2"]) && is_numeric($_POST["1b2"])) {
    update_option("gradient_grad", array($_POST["1r"], $_POST["1g"],
      $_POST["1b"], $_POST["1r2"], $_POST["1g2"], $_POST["1b2"]));
  } else {
    $error .= "Header/Footer colors are invalid...<br>";
  }

  // clock gradient colors
  if (is_numeric($_POST["2r"]) && is_numeric($_POST["2g"]) && is_numeric($_POST["2b"]) && is_numeric($_POST["2r2"]) && is_numeric($_POST["2g2"]) && is_numeric($_POST["2b2"])) {
    update_option("gradient_grad2", array($_POST["2r"], $_POST["2g"],
      $_POST["2b"], $_POST["2r2"], $_POST["2g2"], $_POST["2b2"]));
  } else {
    $error .= "Clock colors are invalid...<br>";
  }

  if (is_numeric($_POST["def_slide_time"])) {
    update_option("default_slide_time", $_POST["def_slide_time"]);
  } else {
    $error .= "Default slide time is invalid...<br>";
  }

  if($error == "") return "Settings saved.";
  $error .= "Everything else are saved.";
  return $error;
}

function slideshow_cal_func() {
  return array(slideshow_cal, slideshow_cal_head, slideshow_cal_load);
}

function slideshow_cal_head() {
  $grad = get_option("gradient_grad");
  $grad2 = get_option("gradient_grad2");
	echo <<<END
<style>

#calendar {
	overflow: auto;
	clear: both;
}
.month {
	border: 1px solid #888;
	background: #eee;
	float: left;
	margin: 5px;
	border-collapse:collapse;
}
.month td {
	background: #fff;
	padding: 3px;
	border: 1px solid #888;
	text-align: center;
}
.month td#this_day {
	padding: 2px;
}
.month td#this_day a {
	background: #f88;
	border: 1px solid #000;
}
.month td#today a {
	color: #f00;
}
.month td.month_padding {
	border-right: 0 none;
	background: transparent;
}
.month td.filled_day {
	background: #8f8;
}

.content {
	width: 100%;
	height: 120px;
}
#grad_preview,
#grad2_preview {
	display: inline-block;
	border: 1px solid #fff;
	outline: 1px solid #000;
	margin: 5px 3px 3px 5px;
	height:24px;
	width:24px;
}
#grad_preview + h4,
#grad2_preview + h4 {
	display: inline-block;
  position: relative;
  top: -10px;
}
#grad_preview {
	background: -moz-linear-gradient(top, rgb({$grad[0]}, {$grad[1]}, {$grad[2]}), rgb({$grad[3]}, {$grad[4]}, {$grad[5]}));
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0, rgb({$grad[0]}, {$grad[1]}, {$grad[2]})), color-stop(1, rgb({$grad[3]}, {$grad[4]}, {$grad[5]})));
}

#grad2_preview {
	background: -moz-linear-gradient(top, rgb({$grad2[0]}, {$grad2[1]}, {$grad2[2]}), rgb({$grad2[3]}, {$grad2[4]}, {$grad2[5]}));
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0, rgb({$grad2[0]}, {$grad2[1]}, {$grad2[2]})), color-stop(1, rgb({$grad2[3]}, {$grad2[4]}, {$grad2[5]})));
}
</style>
<script type="text/javascript"> (function($) {
var day, month, thisDay;

function get_day(event) {
	var data, monthReg;
	if(event.target.tagName != "A") return;
	
	monthReg = /month-(\d+)/.exec($(event.target).closest(".month").attr("id"));
	
	data = {
		"action": "slideshow_get_day",
		"month": monthReg[1],
		"day": event.target.textContent
	};

	$.post(ajaxurl, data, function(response) {
		var textarea = $(".content");
		textarea.val(response);
		textarea.closest("form").submit(set_day);
		
		thisDay.attr("id", "");
		day = event.target.textContent;
		month = monthReg[1];
		thisDay = $(event.target.parentNode);
		thisDay.attr("id", "this_day");
	});
	event.preventDefault();
	return false;
}
function set_day(event) {
	data = {
		"action": "slideshow_set_day",
		"month": month,
		"day": day,
		"text": $(".content").val()
	};

	$.post(ajaxurl, data, function(response) {
		if(response != 1) return;
		
		if($(".content").val() == "") {
			thisDay.removeClass("filled_day");
		} else {
			thisDay.addClass("filled_day");
		}
	});
	
	event.preventDefault();
	return false;
}

$(document).ready(function($) {
	day = $("#hidden-day").val();
	month = $("#hidden-month").val();
	thisDay = $("#this_day");
	
	$("#calendar .month").click(get_day);
});

})(jQuery); </script>
END;
}

function slideshow_cal_load() {
	
}

function array_position($array, $num) {
	reset($array);
	while($num != 0) { next($array); $num--; }
	return key($array);
}

function slideshow_cal() {
	$months = array("Tammikuu", "Helmikuu", "Maaliskuu", "Huhtikuu", "Toukokuu", "Kesäkuu", "Heinäkuu", "Elokuu", "Syyskuu", "Lokakuu", "Marraskuu", "Joulukuu");
	
	$show_day = $_GET["day"];
	if(! isset($show_day)) $show_day = (int) date("j");
	$show_month = $_GET["month"];
	if(! isset($show_month)) $show_month = (int) date("n");
	
	$calendar = get_option("calendar");
	if(isset($_POST["change"])) {
		$calendar[$show_month][$show_day] = $_POST["global_content"];
		update_option("calendar", $calendar);
	}
	
	echo <<<END
<div class="wrap">
 	<div id="icon-infotv" class="icon32"><br></div>
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

END;
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
				
				if($day == $show_day && $month_num == $show_month) $css = "id=\"this_day\"";
				if($day == date("j") && $month_num == date("n")) $css = "id=\"today\"";
				if($calendar[$month_num][$day] != "") $css .= " class=\"filled_day\"";
				
				echo "<td $css><a href=\"./admin.php?page=slideshow-calendar&month=$month_num&day=$day\">$day</a></td>";
			}
			echo "</tr><tr>\n";
			$d = 0;
		}
		echo "</tr>\t\t</table>\n";
	}
	$global_content = $calendar[$show_month][$show_day];

	$grad = get_option("gradient_grad");
	$grad2 = get_option("gradient_grad2");

	echo <<<END
	</div>
	<div id="form">
		<form method="post" enctype="application/x-www-form-urlencoded" action="admin.php?page=slideshow-calendar&month=$show_month&day=$show_day">
		<input type="hidden" id="hidden-day" value="$show_day">
		<input type="hidden" id="hidden-month" value="$show_month">
		<input type="hidden" name="change" value="true">
		<textarea class="content" name="global_content">$global_content</textarea><br>
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
END;
}
?>
