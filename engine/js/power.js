if(typeof $WowheadPower=="undefined")
{
	var $WowheadPower = new function(){
		function v(AW,AV){var AU=document.createElement(AW);if(AV){AO(AU,AV)}return AU}
		function P(AU,AV){return AU.appendChild(AV)}
		function u(AV,AW,AU){if(window.attachEvent){AV.attachEvent("on"+AW,AU)}else{AV.addEventListener(AW,AU,false)}}
		function AO(AW,AU){for(var AV in AU){if(typeof AU[AV]=="object"){if(!AW[AV]){AW[AV]={}}AO(AW[AV],AU[AV])}else{AW[AV]=AU[AV]}}}
		function o(AU){if(!AU){AU=event}if(!AU._button){AU._button=AU.which?AU.which:AU.button;AU._target=AU.target?AU.target:AU.srcElement}return AU}
		function AD(){var AV=0,AU=0;if(typeof window.innerWidth=="number"){AV=window.innerWidth;AU=window.innerHeight}else{if(document.documentElement&&(document.documentElement.clientWidth||document.documentElement.clientHeight)){AV=document.documentElement.clientWidth;AU=document.documentElement.clientHeight}else{if(document.body&&(document.body.clientWidth||document.body.clientHeight)){AV=document.body.clientWidth;AU=document.body.clientHeight}}}return{w:AV,h:AU}}
		function b(){var AU=0,AV=0;if(typeof (window.pageYOffset)=="number"){AU=window.pageXOffset;AV=window.pageYOffset}else{if(document.body&&(document.body.scrollLeft||document.body.scrollTop)){AU=document.body.scrollLeft;AV=document.body.scrollTop}else{if(document.documentElement&&(document.documentElement.scrollLeft||document.documentElement.scrollTop)){AU=document.documentElement.scrollLeft;AV=document.documentElement.scrollTop}}}return{x:AU,y:AV}}
		function AK(AW){var AV,AX;if(window.innerHeight){AV=AW.pageX;AX=AW.pageY}else{var AU=b();AV=AW.clientX+AU.x;AX=AW.clientY+AU.y}return{x:AV,y:AX}}
		function c(AV){var AU=c.L;return(AU[AV]?AU[AV]:0)}
		c.L={fr:2,de:3,es:6,ru:7,wotlk:0,ptr:25};
		function AH(AU){var AV=AH.L;return(AV[AU]?AV[AU]:-1)}
		AH.L={npc:1,object:2,item:3,itemset:4,quest:5,spell:6,zone:7,faction:8,pet:9,achievement:10};
		function q(AZ,AV,AY){var AX={12:1.5,13:12,14:15,15:5,16:10,17:10,18:8,19:14,20:14,21:14,22:10,23:10,24:0,25:0,26:0,27:0,28:10,29:10,30:10,31:10,32:14,33:0,34:0,35:25,36:10,37:2.5,44:4.69512176513672};if(AZ<0){AZ=1}else{if(AZ>80){AZ=80}}if((AV==14||AV==12||AV==15)&&AZ<34){AZ=34}if(AY<0){AY=0}var AW;if(AX[AV]==null){AW=0}else{var AU;if(AZ>70){AU=(82/52)*Math.pow((131/63),((AZ-70)/10))}else{if(AZ>60){AU=(82/(262-3*AZ))}else{if(AZ>10){AU=((AZ-8)/52)}else{AU=2/52}}}AW=AY/AX[AV]/AU}return AW}
		var a={applyto:3},K,AB,AI,t,j,AL,AJ,k,F,y,w,S=document.getElementsByTagName("head")[0],e={},Z={},L={},AS={},z,Y,C,f,AM,G=1,p=0,AA=!!(window.attachEvent&&!window.opera),U=navigator.userAgent.indexOf("MSIE 7.0")!=-1,W=navigator.userAgent.indexOf("MSIE 6.0")!=-1&&!U,i={loading:"Loading...",noresponse:"No response from server :("},AF=0,O=1,M=2,s=3,AE=4,h=3,r=5,X=6,AC=10,R=15,n=15,T={3:[e,"item","Item"],5:[Z,"quest","Quest"],6:[L,"spell","Spell"],10:[AS,"achievement","Achievement"]},I={0:"enus",2:"frfr",3:"dede",6:"eses",7:"ruru",25:"ptr"};
		function AQ(){P(S,v("link",{type:"text/css",href:(typeof(window['PATHROOT']) != "undefined" ? PATHROOT : "") + "engine/js/power/power.css?3",rel:"stylesheet"}));if(AA){P(S,v("link",{type:"text/css",href:"engine/js/power/power_ie.css?3",rel:"stylesheet"}));if(W){P(S,v("link",{type:"text/css",href:"power/js/power/power_ie6.css?3",rel:"stylesheet"}))}}u(document,"mouseover",g)}
		function Q(AU){var AV=AK(AU);y=AV.x;w=AV.y}
		function AR(Ad,Ab){
			if(Ad.nodeName!="A"&&Ad.nodeName!="AREA"){return -2323}if(!Ad.href.length){return }var Aa,AY,AW,AV,AX={};var AU=function(Ae,Ag,Af){if(Ag=="buff"||Ag=="sock"){AX[Ag]=true}else{if(Ag=="rand"||Ag=="ench"||Ag=="lvl"||Ag=="c"){AX[Ag]=parseInt(Af)}else{if(Ag=="gems"||Ag=="pcs"){AX[Ag]=Af.split(":")}else{if(Ag=="who"||Ag=="domain"){AX[Ag]=Af}else{if(Ag=="when"){AX[Ag]=new Date(parseInt(Af))}}}}}};
			if(a.applyto&1){
				Aa=1;AY=5;AW=6;WEBW=2;WEBW2=4;WEBW3=3;
				AV=Ad.href.match(/^http:\/\/(www|dev|fr|es|de|ru|wotlk|ptr)?\.(wowhead|web-wow)?\.(com|net)?\/(\?|power\.php\?)?(item|quest|spell|achievement)=([w0-9]+)/);p=0
			}
			if(AV==null&&(a.applyto&2)&&Ad.rel){Aa=0;AY=1;AW=2;AV=Ad.rel.match(/(item|quest|spell|achievement).?([w0-9]+)/);p=1}
			if(Ad.rel){Ad.rel.replace(/([a-zA-Z]+)=([a-zA-Z0-9:-]*)/g,AU)}
			if(AV){
				var Ac,AZ="www";if(AX.domain){AZ=AX.domain}else{if(Aa&&AV[Aa]){AZ=AV[Aa]}}Ac=c(AZ);
				if(AZ=="wotlk"){AZ="www"}
				t=AZ;webwow=AV[WEBW];webwow2=AV[WEBW2];webwow3=AV[WEBW3];
				if(!Ad.onmousemove){Ad.onmousemove=B;Ad.onmouseout=D}
				Q(Ab);
				l(AH(AV[AY]),AV[AW],Ac,AX)
			}
		}
		function g(AW){AW=o(AW);var AV=AW._target;var AU=0;while(AV!=null&&AU<3&&AR(AV,AW)==-2323){AV=AV.parentNode;++AU}}function B(AU){AU=o(AU);Q(AU);d()}function D(){K=null;j=[];AL=null;AJ=null;k=null;F=null;V()}function H(){if(!z){var AZ=v("div"),Ad=v("table"),AW=v("tbody"),AY=v("tr"),AV=v("tr"),AU=v("td"),Ac=v("th"),Ab=v("th"),Aa=v("th");AZ.className="wowhead-tooltip";Ac.style.backgroundPosition="top right";Ab.style.backgroundPosition="bottom left";Aa.style.backgroundPosition="bottom right";P(AY,AU);P(AY,Ac);P(AW,AY);P(AV,Ab);P(AV,Aa);P(AW,AV);P(Ad,AW);f=v("p");f.style.display="none";P(f,v("div"));P(AZ,f);P(AZ,Ad);P(document.body,AZ);z=AZ;Y=Ad;C=AU;var AX=v("div");AX.className="wowhead-tooltip-powered";P(AZ,AX);AM=AX;V()}}function AT(AW,AX){if(!z){H()}if(!AW){AW=T[K][2]+" not found :(";AX="inv_misc_questionmark"}else{if(j&&j.length){var AY=0;for(var AV=0,AU=j.length;AV<AU;++AV){if(m=AW.match(new RegExp("<span><!--si([w0-9]+:)*"+j[AV]+"(:[w0-9]+)*-->"))){AW=AW.replace(m[0],'<span class="q8"><!--si'+j[AV]+"-->");++AY}}if(AY>0){AW=AW.replace("(0/","("+AY+"/");AW=AW.replace(new RegExp("<span>\\(([0-"+AY+"])\\)","g"),'<span class="q2">($1)')}}if(AJ){AW=AW.replace(/<span class="c([w0-9]+?)">(.+?)<\/span><br \/>/g,'<span class="c$1" style="display: none">$2</span>');AW=AW.replace(new RegExp('<span class="c('+AJ+')" style="display: none">(.+?)</span>',"g"),'<span class="c$1">$2</span><br />')}if(AL){AW=AW.replace(/\(<!--r([w0-9]+):([w0-9]+):([w0-9]+)-->([w0-9.%]+)(.+?)([w0-9]+)\)/g,function(Aa,Aa,Ab,AZ,Aa,Ad,Aa){var Ac=q(AL,Ab,AZ);Ac=(Math.round(Ac*100)/100);if(Ab!=12&&Ab!=37){Ac+="%"}return"(<!--r"+AL+":"+Ab+":"+AZ+"-->"+Ac+Ad+AL+")"})}if(F){AW=AW.replace("<table><tr><td><br />",'<table><tr><td><br /><span class="q2">'+sprintf(i.tooltip_achievementcomplete,F[0],F[1],F[2],F[3])+"</span><br /><br />");AW=AW.replace(/class="q0"/g,'class="r3"')}}if(AM){AM.style.display=(p?"":"none")}if(G&&AX){f.style.backgroundImage="url(http://static.wowhead.com/images/icons/medium/"+AX.toLowerCase()+".jpg)";f.style.display=""}else{f.style.backgroundImage="none";f.style.display="none"}z.style.display="";z.style.width="320px";C.innerHTML=AW;AP();d();z.style.visibility="visible"}function V(){if(!z){return }z.style.display="none";z.style.visibility="hidden"}function AP(){var AV=C.childNodes;if(AV.length>=2&&AV[0].nodeName=="TABLE"&&AV[1].nodeName=="TABLE"){AV[0].style.whiteSpace="nowrap";var AU;if(AV[1].offsetWidth>300){AU=Math.max(300,AV[0].offsetWidth)+20}else{AU=Math.max(AV[0].offsetWidth,AV[1].offsetWidth)+20}if(AU>20){z.style.width=AU+"px";AV[0].style.width=AV[1].style.width="100%"}}else{z.style.width=Y.offsetWidth+"px"}}function d(){if(!z){return }if(y==null){return }var Ad=AD(),Ae=b(),Aa=Ad.w,AX=Ad.h,AZ=Ae.x,AW=Ae.y,AY=Y.offsetWidth,AU=Y.offsetHeight,AV=y+R,Ac=w-AU-n;if(AV+R+AY+4>=AZ+Aa){var Ab=y-AY-R;if(Ab>=0){AV=Ab}else{AV=AZ+Aa-AY-R-4}}if(Ac<AW){Ac=w+n;if(Ac+AU>AW+AX){Ac=AW+AX-AU;if(G){if(y>=AV-48&&y<=AV&&w>=Ac-4&&w<=Ac+48){Ac-=48-(w-Ac)}}}}z.style.left=AV+"px";z.style.top=Ac+"px"}function AN(AU){return(k?"buff_":"tooltip_")+I[AU]}function AG(AW,AX,AV){var AU=T[AW][0];if(AU[AX]==null){AU[AX]={}}if(AU[AX].status==null){AU[AX].status={}}if(AU[AX].status[AV]==null){AU[AX].status[AV]=AF}}function l(AX,AZ,AV,AY){if(!AY){AY={}}var AW=J(AZ,AY);K=AX;AB=AW;AI=AV;j=AY.pcs;AL=AY.lvl;AJ=AY.c;k=AY.buff;F=(AY.who&&AY.when?[AY.who,AY.when.getMonth()+1,AY.when.getDate(),AY.when.getFullYear()]:null);AG(AX,AW,AV);var AU=T[AX][0];if(AU[AW].status[AV]==AE||AU[AW].status[AV]==s){AT(AU[AW][AN(AV)],AU[AW].icon)}else{if(AU[AW].status[AV]==O){AT(i.tooltip_loading)}else{E(AX,AZ,AV,null,AY)}}}
		function E(Aa,AU,Ac,AZ,AW){
			var Ab=J(AU,AW);
			var AY=T[Aa][0];
			if(AY[Ab].status[Ac]!=AF&&AY[Ab].status[Ac]!=M){return }AY[Ab].status[Ac]=O;if(!AZ){AY[Ab].timer=setTimeout(function(){N.apply(this,[Aa,Ab,Ac])},333)}var AV="";
			for(var AX in AW){
				if(AX!="rand"&&AX!="ench"&&AX!="gems"&&AX!="sock"){continue}
				if(typeof AW[AX]=="object"){AV+="&"+AX+"="+AW[AX].join(":")}
				else{
					if(AX=="sock"){	AV+="&sock" }
					else{ AV+="&"+AX+"="+AW[AX] }
				}
			}
			A("http://"+t+"."+webwow+"."+webwow3+"/"+webwow2+T[Aa][1]+"="+AU+AV+"&power&lol")
		}
		function A(AU){P(S,v("script",{type:"text/javascript",src:AU}))}
		function N(AW,AX,AV){if(K==AW&&AB==AX&&AI==AV){AT(i.loading);var AU=T[AW][0];AU[AX].timer=setTimeout(function(){x.apply(this,[AW,AX,AV])},3850)}}
		function x(AW,AX,AV){var AU=T[AW][0];AU[AX].status[AV]=M;if(K==AW&&AB==AX&&AI==AV){AT(i.tooltip_noresponse)}}
		function J(AV,AU){return AV+(AU.rand?"r"+AU.rand:"")+(AU.ench?"e"+AU.ench:"")+(AU.gems?"g"+AU.gems.join(","):"")+(AU.sock?"s":"")}
		this.register=function(AX,AY,AV,AW){var AU=T[AX][0];clearTimeout(AU[AY].timer);AO(AU[AY],AW);if(AU[AY][AN(AV)]){AU[AY].status[AV]=AE}else{AU[AY].status[AV]=s}if(K==AX&&AY==AB&&AI==AV){AT(AU[AY][AN(AV)],AU[AY].icon)}};
		this.registerItem=function(AW,AU,AV){this.register(h,AW,AU,AV)};
		this.registerQuest=function(AW,AU,AV){this.register(r,AW,AU,AV)};
		this.registerSpell=function(AW,AU,AV){this.register(X,AW,AU,AV)};
		this.registerAchievement=function(AW,AU,AV){this.register(AC,AW,AU,AV)};
		this.set=function(AU){AO(a,AU)};
		this.showTooltip=function(AW,AV,AU){Q(AW);AT(AV,AU)};
		this.hideTooltip=function(){V()};
		this.moveTooltip=function(AU){B(AU)};
		AQ()
	}
};