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

var programs = {};

var infotv_tools = (function(ui, Programs) {
	var obj = {};
	var toolbarNode = null;
	var toolStyle, console;

	function ToolButton(id, attr) {
		var rule = {};
		var node = document.createElement("div");
		
		node.id = "tb_"+ id;
		node.innerHTML = "<a href=\"#\"><div>"+ attr.char +"</div>"+
					"<span class=\"menu\">"+ attr.name +"</span></a>";
		
		rule["#tb_"+ id +" div"] = {
			"background-position": (-attr.icon * 32) +"px 0"
		};
		toolStyle.addRule(rule);
		
		node.addEventListener("click", attr.func);
		toolbarNode.appendChild(node);
	}
	
	function CommandLine(api) {
		var user = "user";
		var natives = {};

		natives.exit = function() {
			api.exit();
		}

		function args_handler(str) {
			var i, args = [], prev = 0, quotes = false, ignore = false;

			for(i = 0; i < str.length; i++) {
				var c = str.charAt(i);
				if(c == "\\") {ignore = true; continue;}

				if(prev == i && c == " ") prev++;
				if(prev == i && c == "\t") prev++;
				if(!quotes && prev != i && c == " ") {
					args.push(str.substr(prev, i)); prev = i;
				}
				if(!quotes && prev != i && c == "\t") {
					args.push(str.substr(prev, i)); prev = i;
				}

				if(quotes && !ignore && c == "\"") {
					args.push(str.substr(prev+1, i-1));
					quotes = false; prev = i;
				}
				if(prev == i && !quotes && !ignore && c == "\"") quotes = true;
				ignore = false;
			}
			if(prev != i) args.push(str.substr(prev, i));
			return args;
		}

		var loop;
		function ret_func() {
			loop();
		}
		this.ret_func = ret_func;
		function run_program(str) {
			var args = args_handler(str);

			if(natives[args[0]]) {
				natives[args[0]]();
			} else if(Programs[args[0]]) {
				api.runProgram(Programs[args[0]]);
			} else {
				loop();
			}
		}

		function line_loop() {
			api.print(user +"@infotv: ");
			api.readline(run_program);
		}
		loop = line_loop;
		line_loop();
	}

	function Console() {
		var node, inputNode, outputNode, textNode, cursorNode;
		var cursor = 0;
		var program_stack = [], program_now;
		var readl = false, readl_cb = null;

		node = document.createElement("div");
		node.id = "console";
		node.innerHTML = "<div id=\"display\"><span id=\"input\">"+
			"<span id=\"cursor\">&nbsp;</span></span>"+
			"</div><input type=\"text\" id=\"console-input\">";
		ui.wrapperNode.appendChild(node);

		outputNode = node.querySelector("#display");
		inputNode = node.querySelector("#console-input");

		textNode   = $("#input", node);
		cursorNode = textNode.firstChild;

		window.setInterval(function() {
			cursorNode.className = (cursorNode.className == "") ? "blink" : "";
		}, 500);
		
		this.show = function() {
			setTimeout(function(){node.style.top = "50%";}, 10);
		};
		
		this.hide = function() {
			node.style.top = "-50%";
		};

		function apiRunProgram(program) {
			setTimeout(function() {
				spawnNewProcess(program);
			}, 10);
		}
		function apiReadline(callback) {
			readl = true;
			inputNode.value = "";
			readl_cb = callback;
		}
		function apiPrint(str) {
			outputNode.insertBefore(document.createTextNode(str), textNode);
		}

		var hide_function = this.hide;
		function apiExit() {
			var program_ret = program_stack[program_now].ret;
			if(!program_ret) {
				hide_function();
			}
			if(program_ret > program_now) program_ret--;
			program_stack.splice(program_now, 1);
			program_now = program_ret;
			program_stack[program_now].ret_func();
		}

		function spawnNewProcess(program) {
			var api = {
				runProgram: apiRunProgram,
				readline:   apiReadline,
				print:      apiPrint,
				exit:       apiExit
			};
			var cur_prog = program_now;
			program_now = program_stack.push(new program(api)) - 1;
			program_stack[program_now].ret = cur_prog;
		}
		spawnNewProcess(CommandLine);

		inputNode.addEventListener("keydown", function(event) {
			switch(event.keyCode) {
			case 13://enter
				if(!readl) break;

				var str;
				textNode.normalize();
				str = textNode.textContent;
				readl_cb(str.substr(0, str.length - 1));
				break;
			case 8://backspace
				if(!readl) break;

				textNode.normalize();
				if(cursorNode.previousSibling) {
					var tnode = textNode.firstChild;
					tnode.textContent = tnode.textContent.substr(0, tnode.textContent.length-1);
					cursor--;
				}
				break;
			case 37://arrow left
				if(!readl) break;

				textNode.normalize();
				if(cursorNode.previousSibling) {
					var tnode = cursorNode.previousSibling;
					var char = cursorNode.textContent;

					cursorNode.textContent = tnode.textContent.substr(-1);
					tnode.textContent = tnode.textContent.substr(0, tnode.textContent.length-1);

					if(cursorNode.nextSibling) {
						cursorNode.nextSibling.textContent = char + cursorNode.nextSibling.textContent;
					} else {
						textNode.appendChild(document.createTextNode(char));
					}
					cursor--;
				}
				break;
			case 39://arrow right
				if(!readl) break;

				textNode.normalize();
				if(cursorNode.nextSibling) {
					var tnode = cursorNode.nextSibling;
					var char = cursorNode.textContent;

					cursorNode.textContent = tnode.textContent.substr(0, 1);
					tnode.textContent = tnode.textContent.substr(1);

					if(cursorNode.previousSibling) {
						cursorNode.previousSibling.textContent += char;
					} else {
						textNode.insertBefore(document.createTextNode(char), cursorNode);
					}
					cursor++;
				}
				break;
			default:
				window.setTimeout(function() {
					var char = inputNode.value.charAt(cursor);
					textNode.insertBefore(document.createTextNode(char), cursorNode);
					cursor++;
				}, 50);
				break;
			}
		});
	}

	function login_cb() {
		document.href = "./wp-admin/";
	}

	obj.init = (function() {
		toolbarNode = document.createElement("div");
		toolbarNode.id = "toolbar";
		document.body.appendChild(toolbarNode);
		
		toolStyle = new Stylesheet();
		console   = new Console();
		
		new ToolButton("console",	{char: "C", icon: "6", name: "Open the console", func: console.show});
		new ToolButton("newslide",	{char: "N", icon: "5", name: "Create new slide"});
		new ToolButton("fullscreen",{char: "S", icon: "8", name: "Fullscreen", func: infotv_gui.fullscreen});
		new ToolButton("playpause",	{char: "P", icon: "3", name: "Pause"});
		new ToolButton("backward",	{char: "B", icon: "0", name: "Go previous slide"});
		new ToolButton("forward",	{char: "F", icon: "1", name: "Go next slide"});
		new ToolButton("infotv",	{char: "I", icon: "4", name: "Go to infoTV"});
		new ToolButton("login",		{char: "L", icon: "7", name: "Login", func: login_cb});
	});

	return obj;
})(infotv_gui, programs);
