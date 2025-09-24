/*
 debug.js
  update  : 2006-01-16
  license : same as Perl
  http://la.ma.la/misc/js/debugscreen/
*/
var DebugScreen;
(function(){
DebugScreen = (function(){
	DS.prototype = {
		id : "debugScreen",
		lines : 11,
		element : null,
		plugins : {},
		register : function(name,plugin){
			this.plugins[name] = plugin
		},
		createbox : function(){
			var id = this.id;
			var el = gid(id);
			if(!el){
				el = document.createElement("div");
				el.id = id;
			}
			this.element = el;
			document.body.appendChild(el);
			this.element.ondblclick = bind(this.hide,this);
		},
		dumper : function(obj){ return "" + obj },
		print : function(str){ this.element.innerHTML = str },
		show : function(){ this.element.style.display = "block" },
		hide : function(){ this.element.style.display = "none" },
		stop : function(){ window.onerror = function(){} },
		init : function(){
			window.onerror = this.onerror;
		}
	};
	function DS(){
		var self = this;
		this.register = bind(this.register,this);
		this.onerror = function(mes,file,num){
			var buf = [];
			self.element || self.createbox();
			var caller = arguments.callee.caller;
			if(caller) caller = caller.caller;
			var args = {
				message : mes,
				file    : file,
				line    : num,
				caller  : caller
			};
			buf.push("<h1>Error</h1>", "<p>", mes, "</p>");
			each(self.plugins, function(plugin,name){
				var res = (typeof plugin == "function") ? plugin.call(self,args) : self.dumper(plugin);
				if(res){
					buf.push("<div class='info'><h2>",name ,"</h2>");
					buf.push("<div>", res, "</div></div>");
				}
			});
			self.show();
			self.print(buf.join(""));
			return true;
		};
	};
	return new DS;
})();
DebugScreen.init();

/*
 plugins
  - Source : highlight source
  - Caller : trace caller (ie only)
*/
DebugScreen.register("Source",function(e){
	var src = GET(e.file);
	var start = Math.max(0,e.line - Math.ceil(this.lines/2));
	var stackTrace = src.split(/\n/).slice(start, start + this.lines);
	stackTrace = map(stackTrace,function(line,i){
		var ln = start + i+1;
		var t = mul(" ", 5-(""+ln).length) + ln + ": " + escapeHTML(line);
		return (ln == e.line) ? t.bold() : t + "\n"
	}).join("");
	return [
		"<table><tr><th>Line<th>File</tr>",
		"<tr><td>", e.line, "</td><td>", e.file, "</td></tr>",
		"<tr><td colspan=2><pre>", stackTrace, "</pre>",
		"</td></tr></table>"
	].join("")
});
DebugScreen.register("Caller",function(e){
	return e.caller ? "<pre>" + escapeHTML(e.caller) + "</pre>" : null
});

/*
 functions to use
*/
function gid(id){
	return document.getElementById(id);
}
function bind(f,thisObject){
	return function(){
		return f.apply(thisObject,arguments)
	}
}
function escapeHTML(str){
	return (""+str).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;")
}
function mul(str,num){
	var buf = [];
	for(var i=0;i<num;buf[i++]=str);
	return buf.join("")
}
function each(obj,callback,thisObject){
	for(var key in obj){
		if(obj.hasOwnProperty(key))
			callback.call(thisObject,obj[key],key,obj)
	}
}
function map(ary,callback,thisObject){
	var res = [];
	var len = ary.length;
	for(var i=0;i<len;i++)
		res.push(callback.call(thisObject,ary[i],i,ary));
	return res;
}
function GET(url){
	var req = new XMLHttpRequest;
	var res;
	req.open("GET", url, false);
	req.onload = function(){ res = req.responseText };
	req.send(null);
	return res;
}
})();