// excellent rock solid addEvent by http://www.dustindiaz.com/rock-solid-addevent/
// via http://stackoverflow.com/questions/799981/document-ready-equivalent-without-jquery

function addEvent( obj, type, fn ) {
    if (obj.addEventListener) {
        obj.addEventListener( type, fn, false );
        EventCache.add(obj, type, fn);
    }
    else if (obj.attachEvent) {
        obj["e"+type+fn] = fn;
        obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
        obj.attachEvent( "on"+type, obj[type+fn] );
        EventCache.add(obj, type, fn);
    }
    else {
        obj["on"+type] = obj["e"+type+fn];
    }
}

/*
 * DOM hasClass, addClass and removeClass convenience functions
 * from http://snipplr.com/view/3561/addclass-removeclass-hasclass/
 *
 */
 
function hasClass(ele,cls) {
	return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
}
function addClass(ele,cls) {
	if (!this.hasClass(ele,cls)) ele.className += " "+cls;
}
function removeClass(ele,cls) {
	if (hasClass(ele,cls)) {
		var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
		ele.className=ele.className.replace(reg,' ');
	}
}

var EventCache = function(){
    var listEvents = [];
    return {
        listEvents : listEvents,
        add : function(node, sEventName, fHandler){
            listEvents.push(arguments);
        },
        flush : function(){
            var i, item;
            for(i = listEvents.length - 1; i >= 0; i = i - 1){
                item = listEvents[i];
                if(item[0].removeEventListener){
                    item[0].removeEventListener(item[1], item[2], item[3]);
                };
                if(item[1].substring(0, 2) != "on"){
                    item[1] = "on" + item[1];
                };
                if(item[0].detachEvent){
                    item[0].detachEvent(item[1], item[2]);
                };
                item[0][item[1]] = null;
            };
        }
    };
}();

function deleteProfile() {
	window.device.clear('profile')
	var features = document.getElementById('features');
	features.parentNode.removeChild(features);
}

function update() {
	var dts = document.getElementsByTagName('dt');
	for (var dt in dts) {
		dts[dt].onclick = function() {
			var visible = this.nextSibling.style.display;
			if (this.nextSibling.style.display == 'block') {
				removeClass(this, 'open');
				this.nextSibling.style.display = 'none';
			} else {
				addClass(this, 'open');
				this.nextSibling.style.display = 'block';
			}
			
			// http://stackoverflow.com/questions/868407/hide-an-elements-next-sibling-with-javascript
		}
	}
	for (var feature in window.device.profile) {
		var client = document.getElementById(feature);
		var value = device.profile[feature];
		if (client != null && value) {
			client.innerHTML = value;
		}
	}
}

addEvent(window,'unload',EventCache.flush);
addEvent(window,'load', function(){update();});
addEvent(window,'resize', function() {
	var timer;
	var delay = 150;
	if (!!timer) { clearTimeout(timer); }
	timer = setTimeout(function() {
		window.update();
		clearTimeout(timer);
	}, delay);
});