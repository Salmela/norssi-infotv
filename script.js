"use strict";
var infotv = (function() {
	var loader = {
		funcs: [
			[test, "Tarkistetaan selainta"],
			[test, "Ladataan info sivut"],
			[test, "Ladataan kuvat"],
			[test, "Ladataan fontit"],
			[test, "Ladataan bussi aikataulut"],
			[test, "Viimeistellään infoTV:tä"],
		],
		state: 0
	};
	var obj = {};

	function make_loading_screen() {
		var doc = document;
		loader.main = doc.querySelector("#loading");
		var loading = loader.main.querySelector("div");
		var html = "<div id=\"progress\"><div></div></div>\n";
		html += "<div id=\"textarea\"><ul><li>Yhdistetään palvelimeen<span>...</span></li></ul></div>\n";
		loading.innerHTML = html;

		loader.progressBar = loader.main.querySelector("#progress > div");
		loader.textarea    = loader.main.querySelector("#textarea > ul");
	}
	function update_loading_screen(i) {
		loader.textarea.lastElementChild.lastChild.textContent = "[valmis]";
		if(loader.funcs[i])
			loader.textarea.innerHTML += "<li>"+ loader.funcs[i][1] +"<span>...</span></li>";
		loader.progressBar.style.width = (i / loader.funcs.length * 100)+"%";
	}
	function next_state() {
		if(loader.state == loader.funcs.length) {
			update_loading_screen(loader.funcs.length);
			setup_ready();
			return;
		}
		update_loading_screen(loader.state);
		var func = loader.funcs[loader.state][0];
		func(next_state);
		loader.state++;
	};
	function setup() {
		make_loading_screen();
		next_state();				
	}
	function setup_ready() {
		//remove_loading_screen();
		alert("valmis");
	}

	window.addEventListener("load", setup);
	return obj;
})();

function test(callback) {
	setTimeout(callback, 500);
}
