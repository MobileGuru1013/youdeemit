deemeter=new function() {
	this.setTabId=function(tabId) {
		deemeter.tabId=tabId;
		deemeter.info=null;
	}
	
	this.update=function(callback) {
		
		if( !this.tabId ) return;
		chrome.tabs.get(this.tabId,function(tab) {
			var url=tab.url;
			if( !url ) {
				window.setTimeout(function() { deemeter.update(callback); }, 100);
				return;
			}
			var info=deemeter.urlInfo(url);
			var iconPath='icon.png';   
			var rating=0;
			if( info.rate ) {
				var rating=Math.round(info.rate);
				if( rating ) iconPath='images/icon'+rating+'.png';
			}             
			
			var title=chrome.i18n.getMessage('value'+rating+'_label');
			
			if( info.ratings ) title+=' - '+info.ratings+' '+chrome.i18n.getMessage('votes')
			
			
			chrome.browserAction.setIcon({path:iconPath});
			
			chrome.browserAction.setTitle({
					title:title
			});
			deemeter.info=info;
			if( callback ) callback();
		});
	}
	
	this.getInfo=function(callback) {
		chrome.tabs.get(this.tabId,function(tab) {
			info=deemeter.info;
			info.tab=tab;
			callback(info);
		});
	}
	
	this.urlInfo=function(url) {
		return this.callAjax('dget.php',{"url":url});
	}

	this.callAjax=function(url,params,silent) {
		var req=new XMLHttpRequest();
		if( params ) {
			url+='?';
			for( var i in params ) {
				url+=i+'='+encodeURIComponent(params[i])+'&';
			}
		}
		
		req.open( "GET", 'http://www.youdeemit.com/db/'+url, false );
		req.send();
		if( req.readyState!=4 || req.status!=200 || !req.responseText ) {
			if( !silent ) alert( "Ajax error!" );
			return null;
		}
		var resp=null;
		try {
			resp=JSON.parse(req.responseText);
		} catch( e ) {
			resp={ 'status':'error', 'message':'Unable to parse: '+req.responseText };
		}
		if( resp.status=="error" ) {
			if( !silent ) alert( 'Error: '+resp.message );
			return null;
		}
		return resp;
		
	}
	
	chrome.extension.onRequest.addListener(function(request, sender, sendResponse) {
		switch( request['what'] ) {
			case 'info':
				deemeter.getInfo(sendResponse);
				break;
			case 'mots':
				if( !deemeter.motivations ) deemeter.motivations=deemeter.callAjax('motivations.php',{lang:chrome.i18n.getMessage('lang')}).motivations;
				if( sendResponse ) sendResponse(deemeter.motivations);
				break;
			case 'update':
				deemeter.update(sendResponse);
				break;
		}
	});	
	
	
}

chrome.tabs.onActivated.addListener(function (info ) {
	deemeter.setTabId(info.tabId);
	deemeter.update();
});

chrome.tabs.onUpdated.addListener(function( tabId, changeInfo, tab ) {
	if( changeInfo.url && tabId==deemeter.tabId ) {
		deemeter.update();
	}
});


