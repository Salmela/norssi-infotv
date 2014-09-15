"use strict";

/* General object utils */
Object.prototype._extend = function(obj) {
	if(!(obj instanceof Object)) {
		if(obj == null) return;
		console.warn("extend: not a object");
		console.info(obj);
		return;
	}
	var keys = Object.keys(obj);
	var prop;
	for(var i = 0; i < keys.length; i++) {
		prop = keys[i];
		if(obj[prop] instanceof Object) {
			if(! this[prop]) continue;
			else (this[prop])._extend(obj[prop]);
		} else {
			this[prop] = obj[prop];
		}
	}
}
RegExp.prototype.find = function(obj, recursive) {
	recursive = (recursive === true) ? true : false;		
	var keys = Object.keys(obj);
	var prop, result;

	for(prop in obj) {
		if(recursive && obj[prop] instanceof Object) {
			result = this.find(obj[prop]);
			if(result !== null) return result;
		}
		result = this.exec(prop);
		if(result !== null) return result;
	}
}

/* String utils */
String.prototype.capitalize = function() {
	return this[0].toUpperCase() + this.substr(1);
}

/* DOM utils */
function $(selector, node) {
	return ((node !== undefined) ? node : document).querySelector(selector);
}
function $s(selector, node) {
	return ((node !== undefined) ? node : document).querySelectorAll(selector);
}
HTMLElement.prototype.prependChild = function(node) {
	this.insertBefore(node, this.firstChild);
}
HTMLElement.prototype.insertAfter = function(node, reference) {
	this.insertBefore(node, reference.nextSibling);
}

/* Math utils */
function min(a, b) {
	return (a < b) ? a : b;
}
function max(a, b) {
	return (a > b) ? a : b;
}

/* JSON utils */
function nuller(str) {
	return (str) ? str : "null";
}

