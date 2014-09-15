<?php

function slideshow_fonts_update() {
  $error = "";
  
  
  
  if($error == "") return "Settings saved.";
  $error .= "Everything else are saved.";
  return $error;
}

function slideshow_fonts_func() {
  return array(slideshow_fonts, slideshow_fonts_head, slideshow_fonts_load);
}

function slideshow_fonts_head() {
?>
<style>
#font-table .font-tr td {
	font-size: 24px;
	line-height: 32px;
}
</style>
<style id="style-font"></style>
<script type="text/javascript"> (function($) {

function save_new_font(event) {
	var send_data, tr, i;
	
	send_data = {
		"action": "slideshow_set_font",
		"data": ""
	};
	send_data.data += "{name:'"+ $("#font-name").val();
	send_data.data += "',source:'"+ $("#font-lisence").val();
	send_data.data += "',variants:[";
	
	tr = $(".font-url").closest("tr");
	for (i=0; i < tr.length; i++) {
		if($(".font-url", tr[i]).val() == "") break;
		send_data.data += "["+ $(".font-style", tr[i]).val() +","+ $(".font-weight", tr[i]).val() +","+ $(".font-url", tr[i]).val() +"],";
	}
	send_data.data += "null]}";
	alert(send_data.data);

	$.post(ajaxurl, send_data, function(response) {
		$(event.target).text("Lisää fontti");
		$(event.target).unbind("click", save_new_font);
		$(event.target).click(new_font);
	});
}
function new_font_do(event) {
	var text = event.target.value,
	    filename = text.match(/(.+\/)?([^\.]+)(\..*)?/),
	    len = filename.length-1,
	    fontTR = $(event.target).closest("tr"),
	    fontFamilyNode = $("#font-name");
	
	if(filename[len-1] != "") {
		$("#font-lisence").val(filename[1].substr(0, filename[1].length-1));
		var name = filename[len-1],
			italic = /italic/i.test(name),
			bold = /bold/i.test(name),
			format = filename[len].substring(1, filename[len].length);
		
		name = name.replace(/[A-Z]/, " $&").replace(/^\s/, "");
		
		if(italic) {
			$(".font-style", fontTR).val("italic");
			name = name.replace(/[-+_]?italic/i, "");
			fontFamilyNode.css({fontStyle: "italic"});
		} else fontFamilyNode.css({fontStyle: "normal"});
		
		if(bold) {
			$(".font-weight", fontTR).val("bold");
			name = name.replace(/[-+_]?bold/i, "");
			fontFamilyNode.css({fontWeight: "bold"});
			
		} else fontFamilyNode.css({fontWeight: "normal"});
		
		if(!italic && !bold && /regular/i.test(name)) {
			$(".font-style", fontTR).val("normal");
			$(".font-weight", fontTR).val("normal");
			name = name.replace(/[-+_]?regular/i, "");
		}
		fontFamilyNode.val(name);
		fontFamilyNode.css({fontFamily: name});
		
		if(format != "ttf" && format != "woff") {
			alert("Vain ttf- ja woff-fonttit toimivat");
			return;
		}
		
		if(filename[len] != "") {
			var style;
			style = $("#style-font").val()+"@font-face{font-family:'"+ name+
				"';font-style:"+ $("#font-style").val() +";font-weight:"+
				$("#font-weight").val() +";src:url('../../fonts/"+ text+
				"') format('"+ format +"');}\n";
			
			$("#style-font").val(style);
			new_font_style(fontTR);
		}
	}
}

function new_font_style(before) {
	before.after("<tr><td>font-style: <input class=\"font-style\" value=\"normal\"/></td><td>font-weight: <input class=\"font-weight\" value=\"normal\"/></td><td>url:<input class=\"font-url\"/></td></tr>");
	$(".font-url", before.next()).change(new_font_do);
}

function new_font(event) {
	var table = $("#font-table tbody");
	table.prepend("<tr class=\"font-tr\"><td colspan=\"2\"><input id=\"font-name\"/></td><td><input id=\"font-lisence\"/></td></tr>");
	new_font_style(table.children().first());
	$(event.target).text("Tallenna fontti");
	$(event.target).unbind("click", new_font);
	$(event.target).click(save_new_font);
}

$(document).ready(function($) {
	var new_font_button = $("#new_font");
	new_font_button.click(new_font);
});

})(jQuery); </script>
<?php
}

function slideshow_fonts_load() {
	
}
function slideshow_fonts_default() {
	$fonts_array = array(
		array("Bangers", "OFL", array("normal", "normal", "OFL/Bangers.woff")),
		array("Bowlby One SC", "OFL", array("normal", "normal", "OFL/BowlbyOneSC.woff")),
		array("Carme", "OFL", array("normal", "normal", "OFL/Carme-Regular.woff")),
		array("Delius", "OFL", array("normal", "normal", "OFL/Delius-Regular.woff")),
		array("Fontdiner Swanky", "Apache", array("normal", "normal", "Apache/FontdinerSwanky.woff")),
		array("Gentium Basic", "OFL", array("normal", "normal", "OFL/GenBasB.woff")),
		array("Gloria Hallelujah", "OFL", array("normal", "normal", "OFL/GloriaHallelujah.woff")),
		array("IM Fell Double Pica SC", "OFL", array("normal", "normal", "OFL/IMFellDoublePicaSC.woff")),
		array("Josefin Sans", "OFL", array("normal", "400", "OFL/JosefinSans-Regular.woff"), array("italic", "400", "OFL/JosefinSans-Italic.woff"), array("normal", "700", "OFL/JosefinSans-Bold.woff"), array("italic", "700", "OFL/JosefinSans-BoldItalic.woff")),
		array("Lobster Two", "OFL", array("normal", "400", "OFL/LobsterTwo-Regular.woff"), array("italic", "400", "OFL/LobsterTwo-Italic.woff"), array("normal", "700", "OFL/LobsterTwo-Bold.woff"), array("italic", "700", "OFL/LobsterTwo-BoldItalic.woff")),
		array("OFL Sorts Mill Goudy TT", "OFL", array("normal", "normal", "OFL/OFLGoudyStMTT.woff"), array("italic", "normal", "OFL/OFLGoudyStMTT-Italic.woff")),
		array("Permanent Marker", "Apache", array("normal", "normal", "Apache/PermanentMarker.woff")),
		array("Pompiere", "OFL", array("normal", "normal", "OFL/Pompiere-Regular.woff")),
		array("Sniglet", "OFL", array("normal", "800", "OFL/Sniglet-Regular.woff")),
		array("Ultra", "Apache", array("normal", "normal", "Apache/Ultra.woff")),
		array("Waiting for the Sunrise", "OFL", array("normal", "normal", "OFL/WaitingfortheSunrise.woff"))
	);
	update_option("slideshow_fonts", $fonts_array);
	return $fonts_array;
}
function slideshow_fonts() {
	$fonts = get_option("slideshow_fonts");
	if(! $fonts) $fonts = slideshow_fonts_default;
	
	echo <<<END
<div class="wrap">
 	<div id="icon-infotv" class="icon32"><br></div>
	<h2>Infotv - Fonts</h2>
	Tällä sivulla on InfoTV:ssä käytettävien fonttien lista <button id="new_font">Lisää fontti</button>
	<table id="font-table" class="widefat">
		<thead>
		<tr>
			<th colspan=\"2\">Name</th>
			<th>Lisence</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th colspan=\"2\">Name</th>
			<th>Lisence</th>
		</tr>
		</tfoot>
		<tbody>
END;
	foreach($fonts as $i => $font) {
		echo "\t\t<tr class=\"font-tr\">";
		echo "\t\t\t<td colspan=\"2\" style=\"font-family: {$font[0]};\">{$font[0]}</td>"; // name
		echo "\t\t\t<td>{$font[1]}</td>"; // license
		echo "\t\t</tr>";
		for($i = 0; $i < count($font)-2; $i++) {
			echo "\t\t<tr>";
			echo "\t\t\t<td>font-style:". $font[2+$i][0] ."</td>"; // italic/normal
			echo "\t\t\t<td>font-weight:". $font[2+$i][1] ."</td>"; // boldness
			echo "\t\t\t<td>url:". $font[2+$i][2] ."</td>"; // url
			echo "\t\t</tr>";
		}
	}
	echo <<<END
		</tbody>
	</table>
</div>
END;
}
?>
