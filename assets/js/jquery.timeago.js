(function(factory){if(typeof define==='function'&&define.amd){define(['jquery'],factory);}else if(typeof module==='object'&&typeof module.exports==='object'){factory(require('jquery'));}else{factory(jQuery);}}(function($){$.timeago=function(timestamp){if(timestamp instanceof Date){return inWords(timestamp);}else if(typeof timestamp==="string"){return inWords($.timeago.parse(timestamp));}else if(typeof timestamp==="number"){return inWords(new Date(timestamp));}else{return inWords($.timeago.datetime(timestamp));}};var $t=$.timeago;$.extend($.timeago,{settings:{refreshMillis:60000,allowPast:true,allowFuture:false,localeTitle:false,cutoff:0,autoDispose:true,strings:{prefixAgo:null,prefixFromNow:null,suffixAgo:"",suffixFromNow:"from now",inPast:'any moment now',seconds:"Vừa xong",minute:"1 phút",minutes:"%d phút",hour:"1 giờ",hours:"%d giờ",day:"1 ngày",days:"%d ngày",month:"1 tháng",months:"%d tháng",year:"1 năm",years:"%d năm",wordSeparator:" ",numbers:[]}},inWords:function(distanceMillis){if(!this.settings.allowPast&&!this.settings.allowFuture){throw'timeago allowPast and allowFuture settings can not both be set to false.';}var $l=this.settings.strings;var prefix=$l.prefixAgo;var suffix=$l.suffixAgo;if(this.settings.allowFuture){if(distanceMillis<0){prefix=$l.prefixFromNow;suffix=$l.suffixFromNow;}}if(!this.settings.allowPast&&distanceMillis>=0){return this.settings.strings.inPast;}var seconds=Math.abs(distanceMillis)/1000;var minutes=seconds/60;var hours=minutes/60;var days=hours/24;var years=days/365;function substitute(stringOrFunction,number){var string=$.isFunction(stringOrFunction)?stringOrFunction(number,distanceMillis):stringOrFunction;var value=($l.numbers&&$l.numbers[number])||number;return string.replace(/%d/i,value);}var words=seconds<60&&substitute($l.seconds,Math.round(seconds))||minutes<60&&substitute($l.minutes,Math.round(minutes))||hours<24&&substitute($l.hours,Math.round(hours))||days<30&&substitute($l.days,Math.round(days))||days<365&&substitute($l.months,Math.round(days/30))||substitute($l.years,Math.round(years));var separator=$l.wordSeparator||"";if($l.wordSeparator===undefined){separator=" ";}return $.trim([prefix,words,suffix].join(separator));},parse:function(iso8601){var s=$.trim(iso8601);s=s.replace(/\.\d+/,"");s=s.replace(/-/,"/").replace(/-/,"/");s=s.replace(/T/," ").replace(/Z/," UTC");s=s.replace(/([\+\-]\d\d)\:?(\d\d)/," $1$2");s=s.replace(/([\+\-]\d\d)$/," $100");return new Date(s);},datetime:function(elem){var iso8601=$t.isTime(elem)?$(elem).attr("datetime"):$(elem).attr("title");return $t.parse(iso8601);},isTime:function(elem){return $(elem).get(0).tagName.toLowerCase()==="time";}});var functions={init:function(){functions.dispose.call(this);var refresh_el=$.proxy(refresh,this);refresh_el();var $s=$t.settings;if($s.refreshMillis>0){this._timeagoInterval=setInterval(refresh_el,$s.refreshMillis);}},update:function(timestamp){var date=(timestamp instanceof Date)?timestamp:$t.parse(timestamp);$(this).data('timeago',{datetime:date});if($t.settings.localeTitle){$(this).attr("title",date.toLocaleString());}refresh.apply(this);},updateFromDOM:function(){$(this).data('timeago',{datetime:$t.parse($t.isTime(this)?$(this).attr("datetime"):$(this).attr("title"))});refresh.apply(this);},dispose:function(){if(this._timeagoInterval){window.clearInterval(this._timeagoInterval);this._timeagoInterval=null;}}};$.fn.timeago=function(action,options){var fn=action?functions[action]:functions.init;if(!fn){throw new Error("Unknown function name '"+action+"' for timeago");}this.each(function(){fn.call(this,options);});return this;};function refresh(){var $s=$t.settings;if($s.autoDispose&&!$.contains(document.documentElement,this)){$(this).timeago("dispose");return this;}var data=prepareData(this);if(!isNaN(data.datetime)){if($s.cutoff===0||Math.abs(distance(data.datetime))<$s.cutoff){$(this).text(inWords(data.datetime));}else{if($(this).attr('title').length>0){$(this).text($(this).attr('title'));}}}return this;}function prepareData(element){element=$(element);if(!element.data("timeago")){element.data("timeago",{datetime:$t.datetime(element)});var text=$.trim(element.text());if($t.settings.localeTitle){element.attr("title",element.data('timeago').datetime.toLocaleString());}else if(text.length>0&&!($t.isTime(element)&&element.attr("title"))){element.attr("title",text);}}return element.data("timeago");}function inWords(date){return $t.inWords(distance(date));}function distance(date){return(new Date().getTime()-date.getTime());}document.createElement("abbr");document.createElement("time");}));