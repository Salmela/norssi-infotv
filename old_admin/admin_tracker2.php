<?php

function slideshow_tracker_func() {
  return array(slideshow_tracker, slideshow_tracker_head, slideshow_tracker_load);
}

function slideshow_tracker_head() {

}

function slideshow_tracker_load() {
	
}

function slideshow_tracker() {
	echo <<<END
<div class="wrap">
 	<div id="icon-infotv" class="icon32"><br></div>
	<h2>Infotv - Fonts</h2>
	Tällä sivulla näet kaikki käyttäjät, jotka katsovat InfoTV:tä.
	<table id="font-table" class="widefat">
		<thead>
		<tr>
			<th>Client</th>
			<th>State</th>
			<th>Actions</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th>Client</th>
			<th>State</th>
			<th>Actions</th>
		</tr>
		</tfoot>
		<tbody>
		<tr>
			<td>InfoTV</td>
			<td>test</td>
			<td><a href="javascript: alert('hei');">block</a></td>
		</tr>
		
		</tbody>
	</table>
</div>
END;
}

?>
