"use strict";

function bus(time) {
	this.ctx = null;
	this.time = time;
}

var infotv_buslists = (function(ui, net, tables) {

	var obj = new Object();
	var priv = new Object();

	priv.timetables = [null];
	var busid = 0;
	var netBus;
	
	//var tmp = ui.newLayer("tmp", 0, 0);
	obj.init = function(ready, isNet) {
		netBus = net.listen("buslist", receave);
		if(!isNet) {
			ready(1);
		}
	}
	function addBus(name, time, t_nro) {
		var bus = new bus();
		bus.ctx = ui.newLayer("header", 0, 0);

		bus.ctx.extend(infotv_config.buslist.ctx);
		ui.drawText(bus);

		priv.timetables[t_nro][busid] = bus;
		busid++;
	}
	function receave(msg) {
		var list = msg.split("\x1F");
		for(i = 0; i < list.length; i++) {
			
		}
	}
	return obj;
})(infotv_gui, infotv_networking, 3);

