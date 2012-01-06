(function() {
var device = {
	profile:{},
	features: {
		[FEATURE_DETECTION]
	},
	get:function(name) {
		var nameEQ=name+"=";
		var ca=document.cookie.split(';');
		for(var i=0;i<ca.length;i++){
			var c=ca[i];
			while(c.charAt(0)==' ')c=c.substring(1,c.length);
			if(c.indexOf(nameEQ) == 0)return c.substring(nameEQ.length,c.length);
		}
		return null;
	},
	set:function(name,value,days) {
		if (days){
			var date=new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires=";expires="+date.toGMTString();
		}
		else var expires="";
		document.cookie=name+"="+value+expires+";path=/";
	},
	clear:function(name){
		this.set(name,"",-1);
	},
	update:function(init) {
		/* we don't need (or want) to pass the ua through the cookie */
		delete this.profile['ua'];
		/* add features to profile based on defined feature tests */
		for (feature in this.features) {
			this.profile[feature] = this.features[feature]();
			(this.profile[feature] == 0)?delete this.profile[feature]:false;
		}
		/* assemble profile object as string for cookie */
		var data = "%7B";
		for (feature in this.profile){ data += "%22"+feature+"%22:%22"+this.profile[feature]+"%22%2C"; }
		data = data.substring(0, data.length-3);
		data += "%7D";
		/* write profile object as string to cookie */
		this.set('profile', data, 30);
		/* this doesn't need to be the in cookie, and is merely a convenience */
		this.profile['ua'] = navigator.userAgent;
	},
	init:function() {
		/* read profile object from cookie */
		var data = unescape(unescape(this.get('profile'))), prof; 
		this.features['json']?prof=eval('('+data+')'):prof=JSON.parse(data);
		/* copy features from cookie to this.profile */
		for (feature in prof){
			this.profile[feature] = prof[feature];
		}
		window.device = this;
		var update = function() {
			var timer;
			var delay = 100;
			return function() {
				if (!!timer) { clearTimeout(timer);}
					timer = setTimeout(function() {
						window.device.update();
						clearTimeout(timer);
					}, delay);
				};
			}();
		window.addEventListener?window.addEventListener('resize',update,false):window.attachEvent("onresize",update);
		this.update(true);
	}
};
device.init();
})();