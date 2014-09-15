"use strict";

var infotv = (function(Net) {
	var methods = new Object();
	var cache   = new Array();
	var net_configs;
	var net_timeing, net_modules, net_debug;
	var cache_config;

	methods.getConfig = function() {
		cache_config = methods.register("infotv_config");
		var data = cache_config.get();

		if(!data) return;

		var conf = JSON.parse(data);

		Object.freeze(conf);

		infotv_config = conf;
	}
	function setConfig(data) {
		var conf = JSON.parse(data.content);
		cache_config.set(conf);

		Object.freeze(conf);

		infotv_config = conf;
	}
	function getModules(data) {

	}
	methods.register = function(name) {
		if(cache[name]) return false;
		if(!name) {
			throw new Error("infotv-core: duplicated cache registeration for \""+ name +"\"");
		}
		cache[name] = true;

		return {
			get: function() {
				return localStorage.getItem(name);
			},
			set: function(value) {
				localStorage.setItem(name, value);
			}
		};
	}
	methods.init = function() {
		net_configs = Net.listen("config", setConfig);
		net_timeing = Net.listen("timeing", null);
		net_debug = Net.listen("debug", null);
		//setTimeout(function(){net_modules("a");}, 500);
	}
	return methods;
})(infotv_networking);
