// Copyright (c) 2012 The Chromium Authors. All rights reserved.
// Use of this source code is governed by a BSD-style license that can be
// found in the LICENSE file.

$globalStep=1;
$globalValue=0;
$globalURL=null;
$globalMotivations=[];

pageRated=0;
domainRated=0;

function deemeter_popup_reconf(callback,skipControl) {
	chrome.extension.sendRequest({what: "info"}, function(info) {
		if( !skipControl ) {
			pageRated=info.pageAlreadyRated;
			domainRated=info.domainAlreadyRated;
			if( pageRated && domainRated ) {
				$globalStep=4;
				$('.drate_container').hide();
				$('.drate_step'+$globalStep).show();
			} else if( pageRated ) {
				$('input[name=extendDomain]').attr('checked','checked');
				$('input[name=extendDomain]').attr('disabled','disabled');
				$('span[data-i18n=extend_Vote]').attr('title',chrome.i18n.getMessage('canNotUncheckDomain'));
			} else if( domainRated ) {
				$('input[name=extendDomain]').attr('checked',null);
				$('input[name=extendDomain]').attr('disabled','disabled');
				$('span[data-i18n=extend_Vote]').attr('title',chrome.i18n.getMessage('canNotCheckDomain'));
			}
			if( info.userNickname ) {
				var msg=chrome.i18n.getMessage('login_Welcome');
				msg=msg.replace( '%NAME%',info.userNickname );
				//alert( msg );
				$('.drate_Login a').hide();
				$('.drate_Login span.login_GainPoints').hide();
				$('.drate_Login span.login_Welcome').append(msg);
				
			}
		}
		$globalURL=info.tab.url;
		$('.deemeter_title').html(info.tab.title);
		if( callback ) callback();
	});
}

$(document).ready(function() {
	deemeter_popup_reconf(function() {
		chrome.extension.sendRequest({what: "mots"}, function(mots) {
			$globalMotivations=mots;
		});
	});
});

$(document).ready(function() {
    // initializing messages depending on _locales language
	$('.drate_i18n').each(function(){
		var key = $(this).attr('data-i18n');
		if($(this).hasClass('vtip')){
			$(this).attr('title',chrome.i18n.getMessage(key)); 
		} else {
			$(this).append(chrome.i18n.getMessage(key));
		}
	});
	var step1_helpMsg = chrome.i18n.getMessage("step1_helpMsg");
	$('#step1_help').attr('title',step1_helpMsg);

	$('input[name=extendDomain]').click(function(e) {
		if( pageRated ) {
			$(this).attr('checked','checked');
			e.stopPropagation();
			alert( chrome.i18n.getMessage('canNotUncheckDomain') );
			return false;
		} else if( domainRated ) {
			$(this).attr('checked',null);
			e.stopPropagation();
			alert( chrome.i18n.getMessage('canNotCheckDomain') );
			return false;
		}
	});
	
    //UI behaviour
    //click on vote
    $('.drate_container').hide();
    $('.drate_step'+$globalStep).show(150,function(){
        console.log('dopo= '+$('body').width());
        
    });
    
    $('.drate_Rate a','.drate_step1').click(function(){
        var $value = $(this).attr('class').slice(6);
        $('.drate_container').hide();
        $globalStep+=1;
        $('.drate_step'+$globalStep+'[id="'+$value+'"]').show();
		//$('header .drate_i18n.drate_IconMini').attr('data-i18n','step'+$globalStep+'_titleMsg');
        console.log($('body').height());
        $globalValue=$value;
        //alert($globalStep);
        switch( $globalValue ) {
	        case 'Safe': vn=1; break;
	        case 'NotEasy': vn=2; break;
	        case 'Unsafe': vn=3; break;
        }
        var mots=$('#'+$globalValue+' form .dmotivations').get(0);
//        mots.html('');
        html='';
        for( var i=0; i<$globalMotivations.length; ++i ) {
        	var m=$globalMotivations[i];
        	if( parseInt(m.value)==vn ) {
        		html+='<label><input type="radio" name="motivation" value="'+m.name+'">'+m.label+'<span> - '+m.descr+'</span></label>'
        	}
        }
        if(mots){
            mots.innerHTML=html;
        }
        console.log('dopo='+$('body').height());
    });
    
    //go back and change mind
    $('.drate_Back').click(function(){
        var $toHide = $('.drate_container').hide();
        $globalStep-=1;
        $('.drate_step'+$globalStep).show();
		//$('header .drate_i18n.drate_IconMini').attr('data-i18n','step'+$globalStep+'_titleMsg');
        //alert($globalStep);
        return false;
    });
    
    //go confirm vote
    $('.drate_step2 button.drate_Btn').click(function(){
        switch( $globalValue ) {
	        case 'Safe': $value=1; break;
	        case 'NotEasy': $value=2; break;
	        case 'Unsafe': $value=3; break;
        }
    	var extendDomain=$('.drate_step'+$globalStep+'[id="'+$globalValue+'"] input[name="extendDomain"]').get(0).checked;
    	var motivation=null;
    	$('.drate_step'+$globalStep+'[id="'+$globalValue+'"] input[name="motivation"]').each(function() {
			if( this.checked ) motivation=this.value;
    	});
    	var type='page';
    	if( extendDomain ) type='domain';
		var req=new XMLHttpRequest();
		var url='http://www.youdeemit.com/db/rate.php?url='+encodeURIComponent($globalURL)+'&value='+$value+'&type='+type;
		if( motivation ) url+='&motivation='+motivation;
		req.open( "GET", url, false );
		req.send();
		if( req.readyState!=4 || req.status!=200 || !req.responseText ) {
			alert( "Ajax error!" );
			return false;
		}
		var resp=null;
		try {
			resp=JSON.parse(req.responseText);
		} catch( e ) {
			resp={ 'status':'error', 'errorMessage':e.message };
		}
		if( resp.status=="error" ) {
			alert( 'Error: '+resp.errorMessage );
			return false;
		} else if( resp.status=='duplicate' ) {
			alert( 'already rated' );
			return false;
		}
		var nameEQ = "dontShow=";
		var ca = document.cookie.split(';');
		var dontShow=0;
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) dontShow=c.substring(nameEQ.length,c.length);
		}
		chrome.extension.sendRequest({what: "update"}, function() {
			$globalStep=3;
			deemeter_popup_reconf(function() {
				if( dontShow ) {
					window.close();
				} else {
					//alert( 'Moving to step 3' );
					var $toHide = $('.drate_container').hide();
					$('.drate_step'+$globalStep).show();
			//        alert( 'call' );
					return false;
					//alert($globalStep);
				}
			},1);
		});
		return false;
    })
    
    //close popup
    $('a.drate_Btn', '.drate_step3').click(function(){
        window.close();
    });

    $('a.drate_Btn', '.drate_step4').click(function(){
        window.close();
    });
    
    $('input[name=thanksMessageFalse]').click(function() {
    	var days=180;
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
		document.cookie = "dontShow="+this.checked+expires+"; path=/";
    });
    
    $('.drate_sendFeedback').click(function() {
    	 window.open('http://www.youdeemit.com/takePart.php?lang='+chrome.i18n.getMessage('lang')); 
    	 return false;
    });

});




