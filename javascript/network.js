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

var infotv_networking = (function(ui, config) {

	var methods = new Object();
	var buffer  = new Object();

	var serverURI = "ws://localhost:12345/wordpress/wp-content/themes/slideshow-new/websocket_server/websocket.php";
	var websocket = null;
	var clients   = [];

	var connected = false;
	var connectChangeF = null;

	if(window.MozWebSocket) {
		window.WebSocket = window.MozWebSocket;
	}

	function connect() {
		console.info("Connection");
		try {
			websocket = new WebSocket(serverURI);
		} catch(e) {
			console.info("exception");
			if(e instanceof ReferenceError) {
				console.info("browser doesn't support websockets");
			} else if(e instanceof URIError) {
				console.info("server address isn't correct");
			} else {
				console.info("error from websocket");
			}
			return -1;
		}
		websocket.onopen = ready;
		websocket.onclose = closed;
		websocket.onmessage = receved;
		websocket.onerror = error;
		
		methods.connect = false;
	}

	function ready(evt) {
		console.info("connection is ready");
		connected = true;
		
		var names = Object.keys(buffer);
		console.log(names);
		for(var i = 0; i < names.length; i++) {
			var msgs = buffer[names[i]];
			for(var j = 0; j < msgs.length; j++) {
				console.info("send to server from buffer: \""+ msgs[j] +"\"");
				websocket.send("p["+ names[i] +"]:"+ msgs[j]);
			}
		}
		connectChangeF(true);
	}

	function closed(evt) {
		connected = false;
		console.warn("connection closed");
		console.info(evt);
		switch(evt.code) {
			case 1000:
				console.info("closed normally");
				break;
			case 1001:
			case 1002:
				console.warn("Internal server error");
				break;
			case 1003:
			case 1004:
				console.info("browser not supported");
				break;
			case 1005:
			case 1006:
				console.info("NetSocket server is offline");
				break;
			default:
				console.warn("error");
				break;
		}
		connectChangeF(false);
		
		//ui.showWarning(infotv_config.text.lostConnection);
		//window.setTimeout(connect, 1000);
	}

	function error(error) {
		connected = false;
		console.warn("connection error: ");
		console.log(error);
		websocket.close();
		//ui.showWarning(infotv_config.text.lostConnection);
		//window.setTimeout(connect, 2000);
	}

	function receved(evt) {
		console.log("data receved from server: \""+ evt.data +"\"");
		var header = /^p\[([a-z\-_]*)\]:/.exec(evt.data);
		
		var nextHeader;
		var reg = /^#p\[([a-z\-_]*)\]:/;
		do {
			var nextHeader = reg.exec(evt.data);
			if(header && header[1]) {
				if(nextHeader) var end = nextHeader.index;
				console.log(header[1]);
				if(clients[header[1]]) {
					console.log("run");
					(clients[header[1]])(evt.data.substr(header[0].length, end));
				}
			}
			header = nextHeader;
		} while(nextHeader);
	}

	function send(name, data) {
		if(!data) return false;
		if(!connected) {
			console.log(name);
			buffer[name] = new Array();
			buffer[name].push(data);
			return true;
		}
		console.info("send to server: \""+ data +"\"");
		websocket.send("p["+ name +"]:"+ data);
		return true;
	}

	methods.init = function(func) {
		connectChangeF = func;
		connect();
	}

	methods.listen = function(name, callback) {
		if(clients[name] != undefined) {
			throw new Error("infotv-networking: duplicated listener for \""+ name +"\"");
		}
		if(!name) return false;
		clients[name] = callback;
		
		return (function(data) {
			send(name, data);
		});
	}

	methods.clean = function() {
		connectChangeF = null; //ui.connectChanged
		delete methods.init;
		delete methods.connect;
	}

	return methods;
})(infotv_ui);
