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

var PREFIX_CSS = new Array();

function Stylesheet() {
	var node;
	
	node = document.createElement("style");
	document.head.appendChild(node);
	
	function css2js(property) {
		function lowerToUpper(match, letter) {
			return letter.toUpperCase();
		}
		return property.replace(/-([a-z])/g, lowerToUpper);
	}
	function js2css(property) {
		function upperToLower(match, letter) {
			return "-"+ letter.toLowerCase();
		}
		return property.replace(/([A-Z])/g, upperToLower);
	}

	this.addRule = function(styles) {
		var text = "";
		var selector, rule;
		var selectors = Object.keys(styles);

		for(var i = 0; i < selectors.length; i++) {
			var rules = styles[selectors[i]];
			var props = Object.keys(rules);

			text += selectors[i] +"{";
			for(var j = 0; j < props.length; j++) {
				var jsProperty = css2js(props[j]);

				if(document.body.style[jsProperty] === undefined) {
					var capitalized = jsProperty.capitalize();
					var k = 0, cont = 0;
					do {
						if(document.body.style[PREFIX_CSS[k] + capitalized] !== undefined) {
							text += js2css(PREFIX_CSS[k].capitalize() + capitalized) +
								":"+ rules[props[j]] +";";
							cont++;
							break;
						}
						k++;
					} while(k < PREFIX_CSS.length);
					if(cont) continue;

					var reg = new RegExp("^([A-Z]?[a-z]{0,6}"+ capitalized +")");
					var result = reg.find(document.body.style);
					if(result) {
						PREFIX_CSS.push(result[0].slice(0, -jsProperty.length));
						var k = PREFIX_CSS.length - 1;
						text += js2css(PREFIX_CSS[k].capitalize() + capitalized) +
							":"+ rules[props[j]] +";";
					}
					continue;
				}
				console.log(rules[props[j]]);
				text += props[j] +":"+ rules[props[j]] +";";
			}
			text += "}";
		}

		node.sheet.insertRule(text, 0);
		//node.textContent = text;
	}
}

var infotv_gui = (function(conf) {

	var obj = new Object();
	var priv = {
		page:   {w: 0, h: 0},
		screen: {w: 0, h: 0}
	};
	obj.priv = priv;

	var isAnimating = true;
	var frame = 0;
	var dynamicCSS;
	var headerCanvas, clockCanvas, footerCanvas, fontCanvas,
	    tittleCanvas1, tittleCanvas2;

	function Layer(name, attr) {
		this.canvas  = document.createElement("canvas");
		this.ctx     = this.canvas.getContext("2d");

		this.canvas._extend({
			className:	"infotv-layers"+ (attr.hidden ? " hidden" : ""),
			width:		attr.w,
			height:		attr.h,
			id:			name
		});
		this.canvas.style.zIndex = attr.depth;
		priv.layout.appendChild(this.canvas);

		this.clear  = function() { this.canvas.width = this.canvas.width; }
		this.remove = function() { this.canvas.parentNode.removeChild(this.canvas); }
	}

	/** public init(ready_cb)
	 *  @desc Initialize the gui. Executed by the loader when infotv is started.
	 *  @param ready_cb Callback for notifying the loader to update the progress bar and
	 *                  go to next stage
	 */
	obj.init = (function(ready_cb) {
		dynamicCSS = new Stylesheet();
		
		obj.wrapperNode    = document.createElement("div");
		obj.wrapperNode.style.visibility = "hidden";
		document.body.prependChild(obj.wrapperNode);
		
		priv.layout = document.createElement("div");
		priv.layout.id   = "layout";
		obj.wrapperNode.prependChild(priv.layout);

		/* initialize the layers */
		headerCanvas  = new Layer("header",     {w:0, h:0, depth:20});
		clockCanvas   = new Layer("clock_text", {w:0, h:0, depth:25});
		footerCanvas  = new Layer("footer",     {w:0, h:0, depth:20});
		tittleCanvas1 = new Layer("tittles",    {w:0, h:0});
		tittleCanvas2 = new Layer("tittles2",   {w:0, h:0});

		fontCanvas = new Layer("font", {w:128, h:128, hidden:true});
		fontCanvas.ctx._extend({
			font:         128 +"px "+ conf.fontCanvas,
			textBaseline: "hanging"
		});
		conf.clock.font_height = measureTextBaseline(fontCanvas.ctx, 128) / 128;

		redrawPanels();
		window.addEventListener("resize", redrawPanels);

		var head = headerCanvas.canvas,
		    foot = footerCanvas.canvas;

		/* add these to own functions */
		clockCanvas.canvas.style.top = -head.height +"px";
		head.style.top    = -head.height +"px";
		foot.style.bottom = -foot.height +"px";
		ready_cb(1.0);
	});
	/** public newGradient(ctx, height)
	 *  @desc Show the infotv after the loader is ready.
	 *  @param finished_cb Executed when the animation is finished.
	 */
	obj.show = (function(finished_cb) {
		obj.wrapperNode.id = "wrapper";
		obj.wrapperNode.removeAttribute("style");

		/* slide the header bars from outside of window to borders */
		obj.animate(clockCanvas.canvas,  {top:    "0"}, "linear", "1s", finished_cb);
		obj.animate(headerCanvas.canvas, {top:    "0"}, "linear", "1s", null);
		obj.animate(footerCanvas.canvas, {bottom: "0"}, "linear", "1s", null);
	});
	/** public newGradient(ctx, height)
	 *  @desc Create new canvas gradient.
	 *  @param ctx canvas context.
	 *  @param height the height of gradient in pixels
	 */
	obj.newGradient = (function(ctx, height) {
		var gradient = ctx.createLinearGradient(0, 0, 0, height);
		
		var len = arguments.length / 2;
		for(var i = 1; i < len; i++) {
			gradient.addColorStop(arguments[2*i], arguments[2*i+1]);
		}
		return gradient;
	});
	/** public newGradient(ctx, height)
	 *  @desc Create new canvas gradient.
	 *  @param ctx    canvas context.
	 *  @param text   The string to be rendered.
	 *  @param x      X coordinate of the top-left corner of the text.
	 *  @param y      Y coordinate of the top-left corner of the text.
	 *  @param pre    The font information.
	 *  @param fill   The font information.
	 *  @param stroke If the stroke is rendered
	 *  @param stroke_type Draw the edges of text above(up) or below(down) of the text.
	 */
	obj.text = function(ctx, text, x, y, pre, fill, stroke, stroke_type) {
		ctx._extend(pre);

		if(stroke && stroke_type == "down") {
			ctx._extend(stroke);
			ctx.strokeText(text, x, y);
		}
		if(fill) {
			ctx._extend(fill);
			ctx.fillText(text, x, y);
		}
		var length = ctx.measureText(text);
		if(stroke && stroke_type == "up") {
			ctx._extend(stroke);
			ctx.strokeText(text, x, y);
		}
		return length.width;
	}
	obj.playAnimation = function(node, properties, name, duration, end_cb) {
		node.style._extend({
			animationProperty:	properties.join(", "),
			animationName:		name,
			animationDuration:	duration
		});
		node.addEventListener("animationend", end_cb, false);  
	}
	obj.animate = function(node, to, type, duration, end_cb) {
		node.style._extend({
			MozTransitionProperty:		Object.keys(to).join(", "),
			MozTransitionTimingFunction:	type,
			MozTransitionDuration:		duration
		});
		node.style._extend(to);
		//node.addEventListener("transitionend", end_cb, false);  
	}
	/** public fullscreen()
	 *  @desc Open the infotv on fullscreen.
	 */
	obj.fullscreen = (function() {
		document.documentElement.requestFullScreen();
	});
	obj.lock = (function() {
		obj.freeze();
	});

	/** redrawPanels()
	 *  @desc The window was resized or the infotv was started.
	 */
	function redrawPanels() {
		priv.screen.w = document.body.clientWidth;
		priv.screen.h = document.body.clientHeight;

		var page = priv.screen;

		//document.lastChild.style.minWidth = 0;
		if(page.w / 3.6 > priv.screen.h) {
			page.w = priv.screen.h * 3.6;
		}

		var hratio = conf.header.clock_width_ratio,
		    tratio = conf.buslist.clock_width_ratio;
		if(page.h * conf.header.h * hratio > tratio * page.w) {
			page.h = (tratio * page.w) / conf.header.h / hratio;
		}
		priv.page.w = page.w * conf.page.w;
		priv.page.h = page.h;

		var slideHeight = (priv.page.h * (1 - conf.header.h - conf.footer.h));
		var ratio = min(priv.page.w / conf.slide.w, slideHeight / conf.slide.h);

		dynamicCSS.addRule({
			"#slides > div": {
				"position":   "absolute",
				"width":      1024 +"px",
				"height":     768 +"px",
				"transform-origin": "0 0",
				"transform":  "translate("+ ((priv.page.w - conf.slide.w * ratio) / 2 + (priv.screen.w - priv.page.w) / 2) +"px,"+
						((slideHeight - 768 * ratio) / 2 + page.h * conf.header.h) +"px) "+
						"scale("+ ratio +")"
			}
		});

		DrawHeader(headerCanvas.ctx, page.h * 0.02);
		DrawFooter(footerCanvas.ctx, page.h * 0.02);
	}

	/* code from: http://stackoverflow.com/questions/1134586/how-can-you-find-the-height-of-text-on-an-html-canvas */
	function measureTextBaseline(ctx, fontSize) {
		ctx.textBaseline = "top";
		var letterWidth = Math.floor(ctx.measureText("1", 0, 0).width);
		ctx.fillText("1", 0, 0);

		var data = ctx.getImageData(0, 0, letterWidth, fontSize).data;

		for(var r = data.length - 1; r > 0; r--) {
			if(data[r * 4 + 3]) {
				return Math.floor(r / letterWidth);
			}
		}
	}
	function DrawHeader(ctx, radius) {
		var width  = priv.page.w,
		    height = priv.page.h * conf.header.h;
		var shadow_rad = conf.header.shadowCtx.shadowBlur;

		ctx.canvas._extend({
			width:  width + 2 * shadow_rad,
			height: height + shadow_rad + Math.max(0, conf.header.shadowCtx.shadowOffsetY),
			style: {
				left: (priv.screen.w - width) / 2 - shadow_rad +"px",
				top:  "0"
			}
		});

		var clock_width = height * conf.header.clock_width_ratio;

		ctx.translate(conf.header.shadowCtx.shadowBlur, 0);
		ctx._extend(conf.header.shadowCtx);

		function clockPath(ctx) {
			ctx.beginPath();
			ctx.moveTo(0, -2 * shadow_rad);
			ctx.lineTo(0, height - radius);
			ctx.quadraticCurveTo(0, height, radius, height);
			ctx.lineTo(clock_width + 2 * shadow_rad, height);
			ctx.lineTo(clock_width + 2 * shadow_rad, -2 * shadow_rad);
			ctx.closePath();
		}
		//add conf
		function drawTimetable(ctx) {
			var tmpCanvas = new Layer("tmp", {
				w:		ctx.canvas.width - clock_width,
				h:		ctx.canvas.height,
				hidden:	true
			});
			var tmp = tmpCanvas.ctx;

			tmp._extend(conf.header.shadowCtx);
			tmp.fillStyle = obj.newGradient(ctx, height, 0, "#BB33AA", 1, "#772266");//get colors from conf

			tmp.translate(shadow_rad, 0);

			tmp.beginPath();
			tmp.moveTo(-shadow_rad, -shadow_rad);
			tmp.lineTo(-shadow_rad, height);
			tmp.lineTo(width - clock_width - radius, height);
			tmp.quadraticCurveTo(width - clock_width, height, width - clock_width, height - radius);
			tmp.lineTo(width - clock_width, -shadow_rad);
			tmp.closePath();

			tmp.fill();

			return tmpCanvas;
		}


		ctx.fillStyle = obj.newGradient(ctx, height, 0, "#FFDD88", 1, "#FFBB22");
		ctx.save();
		//make rect witch fills the clock
		ctx.rect(-shadow_rad, 0, clock_width + shadow_rad, height + 2 * shadow_rad);
		//clip everything else except clock
		ctx.clip();

		clockPath(ctx);
		ctx.fill();
		//remove clipping
		ctx.restore();

		var tmp_ctx = drawTimetable(ctx);
		ctx.shadowColor   = "transparent";
		ctx.drawImage(tmp_ctx.canvas, shadow_rad,  0, tmp_ctx.canvas.width - shadow_rad, tmp_ctx.canvas.height,
		                              clock_width, 0, tmp_ctx.canvas.width - shadow_rad, tmp_ctx.canvas.height);
		tmp_ctx.remove();

		obj.drawTime = function(ctx, time) {
			ctx.canvas.width      = clock_width;
			ctx.canvas.height     = height;
			ctx.canvas.style.left = (priv.screen.w - width) / 2 +"px";
			ctx.canvas.style.top  = "0px";

			var font_height = conf.clock.font_height * height*0.9;

			obj.text(ctx, time, clock_width * 0.5, (height - font_height) * 0.5, {
				font:			height*0.9 +"px "+ conf.clock.font,
				textBaseline:	"hanging",
				textAlign:		"center",

				strokeStyle:	"#fff",
				lineWidth:		height * 0.1,
				lineJoin:		"round",
			}, {
				shadowColor:	"transparent"
			}, {
				shadowOffsetX:	0,
				shadowOffsetY:	0,
				shadowBlur:		5,
				shadowColor:	"#000"
			}, "down");
		}
		clockCanvas.clear();
		var time = new Date();
		obj.drawTime(clockCanvas.ctx, time.getHours() +":"+ time.getMinutes());
	}

	function DrawFooter(ctx, radius) {
		var width  = priv.page.w,
		    height = priv.page.h * conf.footer.h;
		var shadow_rad = conf.header.shadowCtx.shadowBlur;

		ctx.canvas.width        = width  + 2 * shadow_rad;
		ctx.canvas.height       = height + shadow_rad;
		ctx.canvas.style.left   = (priv.screen.w - width) / 2 - shadow_rad + "px";
		ctx.canvas.style.bottom = "0px";

		ctx._extend(conf.header.shadowCtx);

		ctx.fillStyle = obj.newGradient(ctx, height, 0, "#BB33AA", 1, "#772266");

		var sRadius = conf.header.shadowCtx.shadowBlur;
		ctx.translate(sRadius, sRadius);

		ctx.beginPath();
		ctx.moveTo(0, height + sRadius);
		ctx.lineTo(0, radius);
		ctx.quadraticCurveTo(0, 0, radius, 0);
		ctx.lineTo(width - radius, 0);
		ctx.quadraticCurveTo(width, 0, width, radius);
		ctx.lineTo(width, height + sRadius);
		ctx.closePath();
		ctx.fill();
	}

	return obj;
})(infotv_config);
