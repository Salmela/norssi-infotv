<?php
$slideshow_op = NULL;

function slideshow_page_func() {
  return Array("slideshow_options", "slideshow_head", "slideshow_page_load");
}
function slideshow_slides_update() {
	global $wpdb;
	
	$data = json_decode(stripslashes($_POST["slide_data"]), true);

	foreach($data as $i => $slide) {
		if(! is_array($slide) || !is_int($i)) continue;
		$title = $slide["title"];

		$wpdb->update($wpdb->posts, array("post_title" => $title), array('ID' => $slide["id"]));
	}
	
	add_option("slidelists", $data["slidelists"], true) or
		update_option("slidelists", $data["slidelists"]);
	
	return "Slides are saved";
}

function slideshow_today_update() {
  add_option("today_content", trim($_POST["today_content"]), true) or
        update_option("today_content", trim($_POST["today_content"]));
  return "Today's text is saved";
}

function slideshow_head() {
  $default_slide_time = get_option("default_slide_time");
?>
<link rel="stylesheet" href="../wp-content/themes/slideshow/admin_page.css">
<script type="text/javascript">
//<![CDATA[
var slideSort,
	$ = jQuery;

var slides_ui = {
	slide_to_slideshow: (function(where, id) {
		if(! where || where.length == 0) {
			where = slideSort.children().last();
		}
		var i = slideSort.children().index(where);
		
		where.after("<tr id='"+ id +"'>"+
			'<td><input type="text" onchange="slide_changed(event, 0);" placeholder="ei mitään" value="'+ slideshow_data[id].title +'"></td>'+
			'<td><input type="text" onchange="slide_changed(event, 1);" placeholder="'+ slideshow_data.default_dur +'" value="" size="3"></td>'+
			'<td><input type="checkbox" onchange="slide_changed(event, 2);" name="slidesort-use-'+id+'" class="slide-use"></td>'+
			'<td><div class="slide-icon up" onclick="slide_move_up(event)"> </div>'+
			'<div class="slide-icon down" onclick="slide_move_down(event)"> </div></td>'+
		"</tr>");
		slideshow_data["slidelists"][0].splice(i, 0, {id:where.attr("id"),state:0,index:i});
		if($("#slideshow-empty", slideSort).length != 0) {
			$("#slideshow-empty", slideSort).remove();
		}
	}),
	slide_to_archives: (function(where, id) {
		
		if(! where || where.parent().attr("class") != "archiveList") {
			where = $(".archiveList").not(".ui-tabs-hide").children().last();
		}
		where.after("<div class='slide' id='"+ id +"'>"+
			"<input type='text' onchange='slide_changed(event, 0);'class='slide-title' placeholder='ei mitään' value='"+ slideshow_data[id].title +"'>"+
			"<div class='slide-icon' onclick='slide_move_to(event)'></div>"+
			"<div class='slide-content'>Ei esikatselua tällä hetkellä</div>"+
		"</div>");

		var remove = -1;
		slideshow_data["slidelists"][0].map(function(slide) {
			remove++;
			if(slide.id != id) return;
			slideshow_data["slidelists"][0].splice(remove, 1);
			return;
		});

		if(slideSort.children().length == 0) {
			slideSort.append("<tr id='slideshow-empty'>"+
				"<td colspan='6' style='text-align: center;'>There isn't any slide yet.</td>"+
			"</tr>");
		}
	}),
	reorder: (function() {
		var id;
		
		for(var i=0; i < slideshow_data.length; i++) {
			id = node.id;
			slideshow_data["slidelists"][0].order = i;
		}
	})
}

function send_slides(event) {
	slides_ui.reorder();
	$("form #slide_data").attr("value", JSON.stringify(slideshow_data));
}
function slide_move_to(event) {
	var n = $(event.target).parent();
	
	slides_ui.slide_to_slideshow(null, n.attr("id"));
	n.remove();
	
	slides_ui.reorder();
	event.preventDefault();
	return 0;
}
function slide_move_up(event) {
	var n = $(event.target).parents("tr");
	
	n.prev().before(n);
	
	slides_ui.reorder();
	event.preventDefault();
	return 0;
}
function slide_move_down(event) {
	var n = $(event.target).parents("tr");
	
	n.next().after(n);
	
	event.preventDefault();
	return 0;
}
function slide_changed(event, action) {
	var node = $(event.target),
		id = node.parents("tr, div").attr("id");
	
	switch(action) {
		case 0:
			slideshow_data[id].title = node.val();
			break;
		case 1:
			slideshow_data["slidelists"][0].map(function(slide) {
				alert( slide);
				if(slide.id != id) return;
				slide.dur = node.val();
			});
			break;
		case 2:
			slideshow_data["slidelists"][0].map(function(slide) {
				alert( slide);
				if(slide.id != id) return;
				slide.state = node.val() ? 1 : 2;
			});
			break;
	}
}

jQuery(document).ready( function($) {
	slideSort = $("#slidesort");
	
	$(".connectedsorts").sortable({
		connectWith: ".connectedsorts",
		helper: function(event, item) {
			return '<div id="slide-helper"><div>'+ slideshow_data[item.attr("id")].title +'</div></div>';
		},
		stop: function(event, ui) {
			if(ui.item.parent().attr("id") == "slidesort") {
				slides_ui.slide_to_slideshow(ui.item, ui.item.attr("id"));
				slides_ui.reorder();
				ui.item.remove();
			} else {
				slides_ui.slide_to_archives(ui.item, ui.item.attr("id"));
				ui.item.remove();
			}
		}
	});
	slideSort.sortable({
		cancel: "#slideshow-empty"
	});
	$("#slideArchives").tabs({
		select: (function(event, ui) {
			$(ui.tab).parents("ul").children().removeClass("nav-tab-active");
			$(ui.tab).addClass("nav-tab-active");
		})
	});

	/*$(".grad1").change(function(){
		$("#grad_preview").css("background", "-moz-linear-gradient(top, rgb("+ $(".grad1[name=1r]").val() +", "+ $(".grad1[name=1g]").val() +", "+ $(".grad1[name=1b]").val() +"), rgb("+ $(".grad1[name=1r2]").val() +", "+ $(".grad1[name=1g2]").val() +", "+ $(".grad1[name=1b2]").val() +"))")
	});
	$(".grad2").change(function(){
		$("#grad2_preview").css("background", "-moz-linear-gradient(top, rgb("+ $(".grad2[name=2r]").val() +", "+ $(".grad2[name=2g]").val() +", "+ $(".grad2[name=2b]").val() +"), rgb("+ $(".grad2[name=2r2]").val() +", "+ $(".grad2[name=2g2]").val() +", "+ $(".grad2[name=2b2]").val() +"))")
	});*/
});
//]]>
</script>
<?php
}

function slideshow_page_load() {
  global $slideshow_op;
  $default_time = get_option("default_slide_time");
  
  if (isset($_POST["submit_slides"])) {
    $errors_str .= slideshow_slides_update();
  } elseif (isset($_POST["submit_today"])) {
    $errors_str .= slideshow_today_update();
  }
  if ($_POST["changed"])
	wp_redirect("admin.php?page=slideshow-options&str=". urlencode($errors_str));

  wp_enqueue_style("thickbox");

  wp_enqueue_script("jquery");
  wp_enqueue_script("jquery-ui-core");
  wp_enqueue_script("jquery-ui-sortable");
  wp_enqueue_script("jquery-ui-tabs");

  wp_enqueue_script("postbox");
  wp_enqueue_script("thickbox");
}

function slideshow_make_archive_slides($args) {
  $query = new WP_Query($args);

  while($query->have_posts()) {
    $query->the_post();
    $id = get_the_ID();

    $slidelists = get_option("slidelists");
    $in_slideshow = false;
    foreach($slidelists[0] as $slide) {
	if(empty($slide)) continue;
	if($id == $slide["id"])
	    $in_slideshow = true;
    }
    if($in_slideshow)
	continue;

    $title = htmlentities(get_the_title(), ENT_COMPAT, "UTF-8");
    $content = get_the_content();
    $author = get_the_author();
    $date = get_the_date();
    echo <<<END

				<div id="$id" class="slide">
					<input type="text" class="slide-title" placeholder="ei mitään" name="slide-title-$id" value="$title">
					<div class="slide-icon" onclick="slide_move_to(event)"> </div>
					<!--<div class="slide-icon" onclick="slide_remove(event)"> </div>-->
					<div class="slide-content">$content</div>
					<span class="slide-author">$author</span>
					<span class="slide-date">$date</span>
				</div>
END;

  }
}

function slideshow_options() {
  global $slideshow_op, $slide_errors_str;
  if (!current_user_can("manage_categories"))  {
    wp_die( __("You do not have sufficient permissions to access this page.") );
  }
  $dir = get_template_directory_uri();
  $default_slide_time = get_option("default_slide_time");
?>
<div class="wrap">
 	<div id="icon-infotv" class="icon32"><br></div>
	<h2>Infotv</h2>
	<script>
var slideshow_data = {<?php
	$args = array("post_type" => "slide", "post_status" => array("publish", "future"), 'posts_per_page' => -1);
	$query = new WP_Query($args);
	
	while($query->have_posts()) {
		$query->the_post();
		$id = get_the_ID();

		echo $id. ": {title:'". htmlentities(get_the_title(), ENT_COMPAT, "UTF-8").
			"',author:'". get_the_author() ."',date:'". get_the_date() ."'},";
	}
	//$slidelists = update_option("slidelists", array(array()));
	$slidelists = get_option("slidelists");
	echo "slidelists:[";
	foreach($slidelists as $list) {
		if(empty($list)) continue;
		echo "[";
		$a = false;
		foreach($list as $slide) {
			if($a == true) echo ",";
			echo "{id:". $slide["id"] .",state:". $slide["state"] .",index:". $slide["index"] ."}";
			$a = true;
		}
		echo "],";
	}
	
	echo "null],default_dur:'". $default_slide_time ."'"
?>};
	</script>
<?php
  if(isset($_GET["str"])) {
    $errors_str = urldecode($_GET["str"]);
  }
  if(isset($errors_str)) {
    echo "<div id=\"setting-error-settings_updated\" class=\"updated settings-error\"><p><strong>$errors_str</strong></p></div>";
  }
  $today_content = get_option("today_content");
?>
	<form method="post" enctype="application/x-www-form-urlencoded" action="admin.php?page=slideshow-options">
		<input type="hidden" name="changed" value="true">
		<h2>Content of today</h2>
		<textarea id="today_content" name="today_content"><?php echo $today_content; ?></textarea>
		<div id="slidesort-update"><input name="submit_today" id="submit" class="button-primary" value="Save Changes" type="submit"></div>
	</form>
	<form method="post" enctype="application/x-www-form-urlencoded" onsubmit="send_slides(Event);" action="admin.php?page=slideshow-options">
		<input type="hidden" name="slide_data" id="slide_data" value="" />
		<h2>Slide sorting</h2>
		<table class="widefat">
		<thead>
			<tr>
				<th>Title</th>
				<th>Duration</th>
				<th>Hide</th>
				<th>Action</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Title</th>
				<th>Duration</th>
				<th>Hide</th>
				<th>Action</th>
			</tr>
		</tfoot>
		<tbody id="slidesort" class="connectedsorts">
<?php

  $args = array("post_type" => "slide", "post_status" => array("publish", "future"), 'posts_per_page' => -1);
  $query = new WP_Query($args);
  $none = true;
  
  $default_time = get_option("default_slide_time");
  foreach($slidelists[0] as $slide) {
  //while($query->have_posts()) { $query->the_post();
	$id = $slide["id"];
	
	/*list($category) = get_the_category();
	if($category->cat_ID == 5) { // category "hide"
		$hide = true;
	} elseif($category->cat_ID == 4) { // category "use"
		$hide = false;
	} else {
		continue;
	}*/
	$title = htmlentities(get_the_title($id), ENT_COMPAT, "UTF-8");
	$author = get_the_author($id);
	$date = get_the_date($id);
	$checked = ($hide) ? "checked" : "";
	list($dur) = get_post_custom_values("kesto");
	$dur = is_numeric($dur) ? $dur : "";
	$none = false;
	echo <<<END

			<tr id="$id">
				<td><input type="text" onchange="slide_changed(event, 0);" name="slide-title-$id" value="$title" placeholder="ei mitään"></td>
				<td><input type="text" onchange="slide_changed(event, 1);" placeholder="$default_time" name="slide-duration-$id" value="$dur" size="3"></td>
				<td><input type="checkbox" onchange="slide_changed(event, 2);" class="slide-use" $checked></td>
				<td><div class="slide-icon up" onclick="slide_move_up(event)"> </div>
					<div class="slide-icon down" onclick="slide_move_down(event)"> </div></td>
			</tr>
END;
  }
  if($none) {
	  echo <<<END
			<tr id="slideshow-empty">
				<td colspan="6" style="text-align: center;">There isn't any slide yet.</td>
			</tr>
END;
  }
?>
		</tbody>
		</table>
		<div id="slidesort-update"><input name="submit_slides" id="submit" class="button-primary" value="Save Changes" type="submit"></div>
		<h3>Slide archives</h3>
		<div id="slideArchives">
			<ul class="nav-tabs">
				<li><a href="#archiveListNew" class="nav-tab">New Slides</a></li>
				<li><a href="#archiveListOld" class="nav-tab">Old slides</a></li>
				<!--<span class="nav-tab nav-tab-active"><abbr>+</abbr></span>-->
			</ul>
			<div class="archiveList connectedsorts" id="archiveListNew"><?php
	$args = array("post_type" => "slide", "post_status" => array("publish", "future"), 'posts_per_page' => -1);
	slideshow_make_archive_slides($args);
?>

			</div>
			<div class="archiveList connectedsorts" id="archiveListOld"><?php
	//$args = array("post_type" => "slide", "post_status" => array("publish", "future"), 'posts_per_page' => -1);
	//slideshow_make_archive_slides($args);
?>
			</div>
		</div>
	</form>
</div>
<?php
}
?>
