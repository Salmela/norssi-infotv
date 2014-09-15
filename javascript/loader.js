/* Copyright (C) 2011-2014 Aleksi Salmela
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Author: Aleksi Salmela <aleksi.salmela@helsinki.fi>
 */
"use strict";

var infotv_loader = (function(Core, Net) {
	var loader = {
		state: 0,
		node:  null
	};
	
	/* new progress_bar()
	 *  - progress bar creator.
	 */
	function ProgressBar(parentNode){
		var progressNode, node = document.createElement("div");
		
		node.id = "progress";
		node.innerHTML += "<div></div>\n";
		parentNode.appendChild(node);

		progressNode = node.firstChild;
		this.update = (function(percent) {
			progressNode.style.width = Math.floor(percent * 100)+"%";
		});
		this.update(0);
	}

	/* new loading_list()
	 *  - loading list creator.
	 */
	function LoadingList(parentNode){
		var listNode, node, items = new Array();
		
		node = document.createElement("div");
		node.id = "textarea";
		node.innerHTML += "<ul></ul>\n";
		parentNode.appendChild(node);
		listNode = node.firstChild;
		
		this.add = (function(text) {
			var listItem = document.createElement("li");
			listItem.innerHTML = text +"<span>...</span>";
			listNode.appendChild(listItem);
			items.push(listItem);
			
			return items.length;
		});
		this.update = (function(index, p) {
			var text = "";
			
			if(p == 1.0) {
				text = infotv_config.text.loaded;
			} else if(p == -1.0) {
				text = infotv_config.text.failed;
				items[index].lastChild.style.color = "red";
			} else {
				text = Math.floor(p * 100) +"%";
			}
			items[index].lastChild.textContent = text;
		});
	}

	/** makeLoadingScreen()
	 *  @desc Create html nodes for loading screen.
	 */
	function makeLoadingScreen() {
		var doc = document;
		
		var pageNode = doc.querySelector("#placeholder");
		pageNode.parentNode.removeChild(pageNode);
		
		loader.node = doc.createElement("div");
		loader.node.id = "loader";
		doc.body.appendChild(loader.node);
		
		loader.node.innerHTML += "<img src=\"wp-content/themes/slideshow-new/images/infotv-login.png\">";
		loader.progress = new ProgressBar(loader.node);
		loader.list     = new LoadingList(loader.node);
	}

	/** loadingUiUpdate()
	 *  @desc Update the ui and check if all neaded modules are loaded so that
	 *        the loader can move to next stage.
	 */
	var state = 0;
	function loadingUiUpdate() {
		state++;
		loader.progress.update(state / 6);
		console.log("state: "+ state);
		
		if(state == 5) {
			loadStage3();
		}
	}

	/** module_ready_func_get(i)
	 *  @desc Get callback function that is called when
	 *        the initialization of module progresses.
	 */
	function module_ready_func_get(i) {
		/* function(progress)
		 * param progress The ammount of initialization is ready. Value must be
		 *                at range [0,1] or -1 if the initialization failed.
		 */
		return function(progress) {
			loader.list.update(i, progress);
			
			if(progress == 1 || progress == -1)
				loadingUiUpdate();
		}
	}
	
	/** load()
	 *  @desc Runs all init functions.
	 */
	function loadStage1() {
		var r = 0;
		
		makeLoadingScreen();
		infotv.init();
		
		loader.list.add("Tarkistetaan selainta");
		r = browserCheck();
		loader.list.update(0, r);
		loadingUiUpdate();	
		
		loader.list.add("Yhdistetään palvelimeen");
		Net.init(loadStage2);
		Core.getConfig();
		
		loader.list.add("Ladataan ulkoasu");
		infotv_gui.init(module_ready(2));
		infotv_tools.init();
	}

	/** stage2()
	 *  @desc Run when connection is ready.
	 */
	function loadStage2(isNet) {
		if(isNet) loader.list.update(1, 1);
		else      loader.list.update(1, -1);
		loadingUiUpdate();
		
		loader.list.add("Ladataan info sivut");
		infotv_slides.init(module_ready(3));
		
		loader.list.add("Ladataan bussi aikataulut");
		infotv_buslists.init(module_ready(4));
	}

	/** stage3()
	 *  @desc Run after all data is loaded.
	 */
	function loadStage3() {
		loader.list.add("Viimeistellään infoTV:tä");
		loadingUiUpdate();

		infotv_gui.show();
		console.log("valmis");
		/* remove the loader gui */
		document.body.removeChild(loader.node);
		//delete infotv_loader;
	}

	/** feature_check(name, object, property)
	 *  @desc Checks if object has property or it's variation.
	 * 
	 *  @param name       name for debuging
	 *  @param object     object that should have the property
	 *  @param property   property to be checked
	 */
	function feature_check(name, object, property) {
		var support = false;
		
		if(object[property]) {
			support = true;
		} else {
			var check = new RegExp(property + "$", "i");
			
			for(var prop in object) {
				if(check.test(prop)) {
					object[property] = object[prop];
					support = true;
				}
			}
		}
		console.info(name + ": " + ((support) ? "yes" : "no"));

		return support;
	}

	/** browser_check()
	 * @desc Checks if browser is checked already.
	 *       Checks if browser supports all features.
	 */
	function browserCheck() {
		var support = true;

		/* test localStorage support */
		support &= feature_check("localStorage", window, "localStorage");

		/* */localStorage.setItem('infotv_supported', "");/* */
		if(localStorage.getItem('infotv_supported')) {
			console.info("Browser is supported.");
			return 1;
		}
		
		/* test Ecmascript 5 support */
		support &= feature_check("Ecmascript 5", Object, "create");

		/* test WebSockets support */
		support &= feature_check("WebSocket", window, "WebSocket");

		/* test JSON support */
		support &= feature_check("JSON", JSON, "parse");

		/* test animation support */
		support &= feature_check("\"request frame\"", window, "requestAnimationFrame");

		/* test fullscreen support */
		support &= feature_check("Fullscreen", document, "fullScreenEnabled");
		support &= feature_check("Fullscreen", HTMLElement.prototype, "requestFullScreen");

		/* save the fact that the browser supports infotv */
		if(support) {
			console.info("Browser is supported.");
			localStorage.setItem('infotv_supported', true);
			return 1;
		}
		console.info("Browser isn't supported.");
		return -1;
	}

	/* start the loader when document is loaded */
	window.addEventListener("DOMContentLoaded", loadStage1);
})(infotv, infotv_networking);

