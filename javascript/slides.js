/* Copyright (C) 2011-2012 Aleksi Salmela
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

function slide() {
	var headerNode;
	var contentNode;
	var ready = false;
}

/* infotv_slides
 *  - modules neaded: core, ui, net
 * 
 * Net handler for slide packets + ui stuff.
 */

var infotv_slides = (function(Core, Ui, Net) {
	var slidesNode, backgroundNode;
	var errorPage     = null;

	var slides        = new Object();
	var slideOrder    = new Array(),
	    slideNewOrder = new Array();
	var slideNro      = 0;

	var netSlides,     cacheSlide;
	var netSlideOrder, cacheSlideOrder;
	var obj = new Object();

	var tittles, tittlesTmp;

	var readyFunc;

	obj.init = function(ready, isNet) {
		slidesNode        = document.createElement("div");
		slidesNode.id     = "slides";
		Ui.wrapperNode.prependChild(slidesNode);
		
		backgroundNode    = document.createElement("div");
		backgroundNode.id = "background";
		Ui.wrapperNode.appendChild(backgroundNode);

		readyFunc = ready;

		netSlideOrder   = Net.listen("slide_order", slideOrderReceave);
		netSlides       = Net.listen("slides", receavedSlide);

		cacheSlideOrder = Core.register("infotv_slide_order");
		cacheSlide      = Core.register("infotv_slide");

		cacheSlideOrder.set("");
		var s_order     = nuller(cacheSlideOrder.get());
		slideOrder      = JSON.parse(s_order);
		slides          = JSON.parse(nuller(cacheSlide.get()));

		netSlideOrder(s_order);
		if(!isNet) {
			makeErrorPage(infotv_config.text.noConnection);
			readyFunc(1.0);
		}
	}

	function slideOrderReceave(data) {
		cacheSlideOrder.set(data);
		slideNewOrder = JSON.parse(data);

		if(slideNewOrder.length == 0) {
			makeErrorPage(infotv_config.text.emptySlidelist);
		}
		readyFunc(1.0);

		/* */slideOrder = slideNewOrder;
		return;
	}

	function makeErrorPage(text) {
		if(errorPage) slidesNode.removeChild(errorPage);
		
		errorPage  = document.createElement("div");
		var box    = document.createElement("div");
		var img    = document.createElement("img");
		var header = document.createElement("h1");

		errorPage.className = "error";
		img.src             = "wp-content/themes/slideshow-new/images/infotv-logo-sad.png";
		header.textContent  = text; // TODO: split header text to main- and small-text

		box.appendChild(img);
		box.appendChild(header);

		errorPage.appendChild(box);
		slidesNode.appendChild(errorPage);
		return;
	}
	function makeFooter() {
		var x = 0;
		
		tittles_tmp.canvas.width  = 400;
		tittles_tmp.canvas.height = 50;

		console.log(slide_new_order);
		console.log(slides);
		for(var i = 0; i < slideNewOrder.length; i++) {
			var slide = slides[slideNewOrder[i]];
			console.log(x);

			/*x += Ui.text(tittlesTmp, slide.name, x, 0, {
				font:		"32px cantarell",
				textBaseline:	"hanging"
			}, {fillStyle:	"#040"}, null);*/
			x += 20;
		}
		return;
	}
	var acceptedNodes = [
		"DIV", "SPAN", "P", "IMG", "OL", "UL",
		"LI", "STRONG", "EM", "B", "I", "U"
	];
	function checkLoadedContent(fragment) {
		var node        = fragment.firstChild;
		
		function checkNode(node) {
			if(!(node.tagName in acceptedNodes)) {
				switch(node.tagName) {
				case "A":
					var link = document.createElemnt("span");
					link.innerHTML = node.innerHTML;
					link.className = "link";
					node.parentNode.replaceChild(node, link);
					break;
				default:
					console.info("Node removed from slide: "+ node.tagName +".");
					node.parentNode.removeChild(node);
					break;
				}
			}
			if(node.firstChild)
				checkNode(node.firstChild);
			
			if(node.nextSibling)
				checkNode(node);
		}
		checkNode(fragment.firstChild);
		return;
	}
	function addScript(script) {
		var node = document.createElement("script");
		
		node.textContent = ""+
				'"use strict";\n'+
				"(function(api) {\n\t"+
					"var window = null"+
					script+
				"}";
		
		document.head.appendChild(node);
		return;
	}
	function addSlide(id) {
		var s         = slides[id];
		var content   = document.createElement("div");
		var frag;

		try {
			frag  = document.createRange().createContextualFragment(s.content);
		} catch(e) {
			console.log(e);
			return;
		}
		checkLoadedContent(frag);
		content.appendChild(frag);

		console.log(content.innerHTML);
		content.classList.add("hide");
		slidesNode.appendChild(content);
		
		if(s.script)
			addScript(s.script);

		s.contentNode = content;
		s.ready       = true;
		slides[id]    = s;
		return;
	}
	function receavedSlide(data) {
		//console.log(data);

		var loadedSlides = JSON.parse(data);
		var i;

		if(!slides) slides = loadedSlides;
		else        slides.extend(loadedSlides);

		var ids = Object.keys(loadedSlides);
		for(i = 0; i < ids.length; i++) {
			addSlide(ids[i]);
		}
		makeFooter();
		//(slides[slide_order[slide_nro]]).contentNode.classList.remove("hide");
		return;
	}
	function change() {
		console.log("change");
		if(slideOrder == null) return;
		var prev = slides[slideOrder[slide_nro]];
		
		for(var i = slideOrder.length; i > 0; i--) {
			slideNro = (slideNro + 1) % slideOrder.length;

			if(slides[slideOrder[slideNro]].ready) break;
		}
		var next = slides[slideOrder[slideNro]];

		if(prev == next) return;

		prev.contentNode.classList.add("hide");
		next.contentNode.classList.remove("hide");
		return;
	}
	obj.loaderNoConnection = function() {
		readyFunc(1.0);
	}
	obj.clean = function() {
		if(!Net.connect) {
			makeErrorPage(infotv_config.text.lostConnection);
		}
		setInterval(change, 5000);
		return;
	}
	return obj;
})(infotv, infotv_gui, infotv_networking);
