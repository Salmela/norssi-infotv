"use strict";

var infotv            = new Object(),
    infotv_loader     = new Object(),
    infotv_ui         = new Object(),
    infotv_networking = new Object(),
    infotv_buslists   = new Object();
var wrapperNode = null;

var infotv_config = {
	title:				"Helsingin yliopiston Viikin normaalikoulu",
	fullscreen:			false,
	cache:				false,
	modules:			["slides", "buslist"],
	fonts:				true,
	page: {
		w:			0.90,

		transition:		"fade",
		defaultBG: [{
			type:		"gradient",
			data:		[{c:"#CCE6E1", s:0.0}, {c:"#008167", s:1.0}]
		}]
	},
	slide:	{ w: 1024, h: 768},
	header:	{
		h:			0.08,

		clock_width_ratio:	3,
		shadowCtx: {
			shadowOffsetX:	0,
			shadowOffsetY:	2,
			shadowBlur:	3,
			shadowColor:	"rgba(0, 0, 0, 0.5)"
		},
		gradient: {
			type:		"gradient",
			data:		[{c:"#B3A", s:0.0}, {c:"#726", s:1.0}]
		}
	},
	footer: {
		h:			0.05,

		gradient: {
			type:		"gradient",
			data:		[{c:"#B3A", s:0.0}, {c:"#726", s:1.0}]
		}
	},
	buslist: {
		clock_width_ratio:	0.3,
		ctx: {
			
		}
	},
	clock: {
		font:			"sans-serif",
		gradient: {
			type:		"gradient",
			data:		[{c:"#FD8", s:0.0}, {c:"#FB2", s:1.0}]
		}
	},
	text: {
		lostConnection:		"Palvelimeen ei saada yhteyttä.",
		noConnection:		"InfoTV ei ole tällä hetkellä käytettävissä. (Websocket palvelin ohjelma ei ole kännissä)",
		emptySlidelist:		"InfoTV:seen ei ole laitettu yhtäkään diaa.",
		noContent:		"",
		loaded:			"[Valmis]",
		failed:			"[Virhe]"
	}
};
