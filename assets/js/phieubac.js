
var checkLogin      = isLogin,
		checkMobile     = isMobile,
		loading         = '<div class="loader">Loading...</div>',
		loadingRED      = '<div class="loader red">Loading...</div>',
		loadingView     = false,
		windowContainer = $('.window-container'),
		base_boxchat    = $('.phieubac-boxchat'),
		base_messenger  = $('.in-messenger #content'),
		base_notice     = $('#pbNotice'),
		base_online     = $('#onlineList'),
		base_taixiu     = $('.TaiXiu-Game'),
		base_baucua     = $('.baucua-Game'),
		notice_message  = base_notice.find('.tin-nhan-moi'),
		notice_other    = base_notice.find('.thong-bao-moi'),
		notice_ipay     = base_notice.find('.notice_ipay');

// chatbox
autoloadChatbox = true;

// messenger
autoloadMessenger = true;

var A_HEADER_HEIGHT = 82;
scrollElementIntoView = function(element, animationDuration, callback){
	animationDuration = animationDuration || 777;
	var destTop = $(element).offset().top - A_HEADER_HEIGHT;
	var callbackCalled = false;
	$('html, body').animate(
		{scrollTop: destTop}, 
		animationDuration, 'easeInOutExpo',
		function(){
			if(!callbackCalled) callback();
			callbackCalled = true;
		}
	);
};





/**
* Functions
*/
function in_array(needle, haystack, argStrict) {
	var key = '';
	var strict = !!argStrict;
	if (strict) {
		for (key in haystack) {
			if (haystack[key] === needle) {
				return true;
			}
		}
	} else {
		for (key in haystack) {
			if (haystack[key] == needle) {
				return true;
			}
		}
	}
	return false
}
function imgError(el) {
	$(el).attr('class', 'max-width-500');
	el.src = "http://i.imgur.com/2oG6XVk.png";
	return true;
}
	function closeWindow() {
	$(".window-container").hide();
	$(document.body).css("overflow", "auto");
}
function phieubac_bind() {
	// like for status
	$("li#likehover").bind({
		mouseenter: function() {
			rel = $(this).attr('map');
				statusID = $(this).attr('data');
				if (rel == 1){
					var el_reply_comments = $('.ps' + statusID).find('.STTReactions');
				} else if (rel == 2){
					var el_reply_comments = $('.comment' + statusID).find('.CMTReactions');
				} else if (rel == 3){
					var el_reply_comments = $('.reply' + statusID).find('.ReplyReactions');
				}

				if(el_reply_comments.css('display') == 'none')
						el_reply_comments.show();
		},
		mouseleave: function() {
			rel = $(this).attr('map');
				statusID = $(this).attr('data');
				if (rel == 1){
					var el_reply_comments = $('.ps' + statusID).find('.STTReactions');
				} else if (rel == 2){
					var el_reply_comments = $('.comment' + statusID).find('.CMTReactions');
				} else if (rel == 3){
					var el_reply_comments = $('.reply' + statusID).find('.ReplyReactions');
				}

				el_reply_comments.hide();
		}
	});

	// like for forum
	$(".new_like").bind({
		mouseenter: function() {
			ForumReactions = $(this).find('.ForumReactions');
			ForumReactions.show();
		},
		mouseleave: function() {
			ForumReactions = $(this).find('.ForumReactions');
			ForumReactions.hide();
		}
	});

	//show users like
	/**
	$('.reaction_wrap-style').bind({
		click: function(e) {
			//e.preventDefault();
			windowContainer.show();
			$(document.body).css('overflow','hidden');
			$('.window-wrapper').css('top', $(document).scrollTop() + ($('.window-background').height()/2-20) - $('.window-wrapper').height() + 'px');
		}
	});
	*/

	$('.components__pages a.pagenav').bind({
		click: function(e) {
			e.preventDefault();
			d__page = $(this).attr('href');
			is__page = d__page.split('=')[1];

			section__id = $('.get-component.is-active').attr('get');
			section = $('#' + section__id);
			section.prepend('<div class="components__load"><div class="loader red"></div></div>');

			components__data   = new Object();
			components__data.t = 'components';
			components__data.a = section__id;
			components__data.page = is__page;

			$.post('/request.php', components__data, function (data) {
				var list_post = '',
						pageData  = data.html;
				for (var i = 0; i < pageData.length; i++) {
					if (data.rate == 'gametop'){
						list_post += components_rate_gameTop(pageData[i]);
					} else if (data.rate == 'forum'){
						list_post += components_rate_forum(pageData[i]);
					}
				}
				listPage = '';
				if (data['page']['status']) {
					listPage = '<div class="list3 text-center">' + data['page']['data'] + '</div>';
				}
				section.html(list_post + listPage).removeAttr('style');

				scrollElementIntoView(section, null, function(){});

				phieubac_unbind();
			});
		}
	});
}

function phieubac_unbind() {
	$("li#likehover").unbind(); // like for status
	$(".new_like").unbind();    // like for forum
	//$('.reaction_wrap-style').unbind(); // show users like
	phieubac_bind();
}

phieubac_bind(); // start bind

// bbcode js hide show
function show_hide(elem) {
	obj = $(elem);
	obj.slideToggle("slow");
}
function rightsColor(rights, name, bold = 1) {
	listColor = [
		'nickname',
		'nickname',
		'nickname',
		'nickname',
		'nickname',
		'nickname',
		'nickname',
		'nickname',
		'nickname',
		'nickadmin'
	];
	return bold ? '<strong class="' + listColor[rights] + '">' + name + '</strong>' : '<span class="' + listColor[rights] + '">' + name + '</span>';
}
function components_rate_forum(data) {
	rdata = '';
	rdata += '<div class="list3 fauthor">';
	rdata += '<img src="' + data['avatar'] + '" class="thumb" alt="' + data['resFrom'] + '" />';
	rdata += '<ul><li>';
	rdata += '<a class="tload" href="' + data['link'] + '">' + data['title'] + '</a>&#160;[' + data['colmes'] + ']';
	if (data['next']) {
		rdata += '&#160;<a class="tload" href="' + data['next'] + '">&gt;&gt;</a>';
	}
	rdata += '</li><li class="sub">';
	rdata += data['resFrom'];
	if (data['namFrom']) {
		rdata += '&#160;/&#160;' + data['namFrom'];
	}
	rdata += ' <span class="gray">(' + data['time'] + ')</span>';
	rdata += '</li></ul>';
	rdata += '</div>';
	return rdata;
}

function components_rate_gameTop(data) {
	rdata = '';
	rdata += '<div class="list3 fauthor">';
	rdata += '<img src="' + data['avatar'] + '" class="avatar" />';
	rdata += '<ul class="finfo"><li><i class="material-icons list__item-icon ' + (data['sex'] == 'm' ? 'mColor' : 'wColor') + '"></i><a href="/profile/?user=' + data['id'] + '">' + rightsColor(data['rights'], data['name']) + '</a></li>';
	rdata += '<li><i class="material-icons list__item-icon red"></i><small>' + data['balans'] + ' VNĐ</small></li></ul>';
	rdata += '</div>';
	return rdata;
}
function reloadChat() {
	request_data = new Object();
	request_data.t = 'chatbox';
	request_data.a = 'filter';
	request_data.view = 'new';
	admin = $('#admin');
	if(admin.length) {
		request_data.admin = '1';
	} else {
		request_data.admin = '0';
	}
		if ($('.phieubac-boxchat .list1').length > 0) {
		 request_data.before_id = $('.phieubac-boxchat .list1:first').attr('data-id');
	}
	$.post('/request.php', request_data, function(data) {
		if (data.status) {
			var boxchat_list = '',
					boxchat_data  = data.chatbox_list;

			for (var i = 0; i < boxchat_data.length; i++) {
				var chatbox_m = boxchat_data[i];
				boxchat_list += '<div id="chat' + chatbox_m['id'] + '" class="list1" data-id="' + chatbox_m['id'] + '">';
				boxchat_list += '<div class="fauthor">';
				boxchat_list += '<img src="' + chatbox_m['avatar'] + '" class="thumb" alt="' + chatbox_m['name'] + '" /><ul><li>';

				// ** Link user */
				boxchat_list += (chatbox_m['link'] ? '<a href="/profile/?user=' + chatbox_m['user_id'] + '" class="tload">' : '') + '<strong class="' + (chatbox_m['rights'] == 9 ? 'nickadmin' : 'nickname') + '">' + chatbox_m['name'] + '</strong>' + (chatbox_m['link'] ? '</a>' : '');

				// ** Time */
				boxchat_list += '</li><li><span class="text--italic gray fsize--12">' + (chatbox_m['timestamp'] != 0 ? '<span class="ajax-time" title="' + chatbox_m['timestamp'] + '">' + chatbox_m['time'] + '</span>' : chatbox_m['time']) + '</span></li></ul>';

				// ** Admin panel */
				boxchat_list += (chatbox_m['panel'] ? '<span class="chatmore"><a class="tload" href="/guestbook/index.php?act=otvet&amp;id=' + chatbox_m.id + '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE15E;</i></a>' + (chatbox_m['panel_more'] ? ' <a class="tload" href="/guestbook/index.php?act=edit&amp;id=' + chatbox_m.id + '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE254;</i></a> <a class="tload" href="/guestbook/index.php?act=delpost&amp;id=' + chatbox_m.id + '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE872;</i></a>' : '') + '</span>' : '');

				/** Post */
				boxchat_list += '</div>' + chatbox_m['text'] + (chatbox_m['reply']['time'] != 0 ? '<div class="reply"><strong class="nickadmin">' + chatbox_m['reply']['name'] + '</strong> <span class="fsize--11-5">' + chatbox_m['reply']['time'] + '</span><br>' + chatbox_m['reply']['text'] + '</div>' : '') + '</div>';
			}
			$('.phieubac-boxchat').prepend(boxchat_list);
		}
		chatArray = $('.phieubac-boxchat .list1');
		danhsach = new Array();
		for (var i = 1, length = chatArray.length; i <= length; i++) {
			idChat = $('.phieubac-boxchat .list1:nth-child(' + i + ')').attr('data-id');
			if(danhsach[idChat] == true) {
				$('.phieubac-boxchat .list1:nth-child(' + i + ')').remove();
			}else{
			danhsach[idChat] = true;
			}
		}
	});
}
function reloadMessenger() {
	request_mdata = new Object();
	request_mdata.t = 'messenger';
	request_mdata.a = 'chat';
	request_mdata.uid = $('.in-messenger #content').attr('data-yid');
	if ($('.content-messenger .message-wrapper').length > 0) {
		request_mdata.before_id = $('.content-messenger .message-wrapper').last().attr('mes-id');
	}
	$.post('/request.php', request_mdata, function(mdata) {
		if (mdata.status == 200) {
			for (var i = 0; i <= mdata.count - 1; i++) {
				var _thisr = mdata.data[i];
				/** Time */
				messenger_time = '<div class="time-wrapper">' + (_thisr['timestamp'] != 0 ? '<span class="ajax-time" title="' + _thisr['timestamp'] + '">' + _thisr['time'] + '</span>' : _thisr['time']) + '</div>';
				var testMrT98 = $('#content .message-group').last();
				if (testMrT98.hasClass('message-group-me') && _thisr.avatar == '0')
				{
					testMrT98.append("\r" + '<div class="message-wrapper me" mes-id="' + _thisr.id + '">' + "\r" + '<div class="text-wrapper">' + "\r" + _thisr.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r");
				} else if (testMrT98.hasClass('message-group-them') && _thisr.avatar != '0')
				{
					testMrT98.append("\r" + '<div class="message-wrapper them" mes-id="' + _thisr.id + '">' + "\r" + '<div class="circle-wrapper animated bounceIn" style="background-image: url(' + _thisr.avatar + '); background-size: 40px 40px;"></div>' + "\r" + '<div class="text-wrapper">' + "\r" + _thisr.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r");
				} else if (testMrT98.hasClass('message-group-me') && _thisr.avatar != '0') {
					$('.content-messenger').append("\r" + '<div class="message-group message-group-them">' + "\r" + '<div class="message-wrapper them" mes-id="' + _thisr.id + '">' + "\r" + '<div class="circle-wrapper animated bounceIn" style="background-image: url(' + _thisr.avatar + '); background-size: 40px 40px;"></div>' + "\r" + '<div class="text-wrapper">' + "\r" +  _thisr.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r" + '</div>' + "\r");
				} else {
					$('.content-messenger').append("\r" + '<div class="message-group message-group-me">' + "\r" + '<div class="message-wrapper me" mes-id="' + _thisr.id + '">' + "\r" + '<div class="text-wrapper">' + "\r" + _thisr.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r" + '</div>' + "\r");
				}
			}
			scrollBottom();
		}
		var messengerArray = $('.message-wrapper');
		var danhsach = new Array();
		var idMessenger = null;
		messengerArray.each(function(){
			idMessenger = $(this).attr('mes-id');
			if (danhsach[idMessenger] == true) {
				$(this).remove();
			} else {
				danhsach[idMessenger] = true;
			}
		});
	});
}
function scrollBottom() {
	$('#inner').animate({scrollTop: $('#content').height() + $('#content').height()});
}

function loadM() {
	data_sc = $('#content').height();
	default_sec = $('.phieubac-message');
	cont = default_sec.attr('data-c');
	if(cont == '1'){
		default_text = default_sec.text();
		out_data = new Object();
		out_data.t = 'messenger';
		out_data.a = 'filter';
		out_data.uid = default_sec.attr('data-mid');
		out_data.after_id = $('.content-messenger .message-wrapper:first').attr('mes-id');
		if ($('.content-messenger .message-wrapper').length > 0) {
			out_data.start_row = $('.content-messenger .message-wrapper').length;
		}
		default_sec.html(loading);
		$.post('/request.php', out_data, function (data) {
			if (data.status == 200) {
				for (var i = data.count - 1; i >= 0; i--) {
					var _this = data.data[i];
					messenger_time = '<div class="time-wrapper">' + (_this['timestamp'] != 0 ? '<span class="ajax-time" title="' + _this['timestamp'] + '">' + _this['time'] + '</span>' : _this['time']) + '</div>';
					var testMrT = $('.content-messenger div').first();
					if (testMrT.hasClass('message-group-me') && _this.avatar == '0')
					{
						testMrT.prepend("\r" + '<div class="message-wrapper me" mes-id="' + _this.id + '">' + "\r" + '<div class="text-wrapper">' + "\r" + _this.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r");
					} else if (testMrT.hasClass('message-group-them') && _this.avatar != '0')
					{
						testMrT.prepend("\r" + '<div class="message-wrapper them" mes-id="' + _this.id + '">' + "\r" + '<div class="circle-wrapper animated bounceIn" style="background-image: url(' + _this.avatar + '); background-size: 40px 40px;"></div>' + "\r" + '<div class="text-wrapper">' + "\r" + _this.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r");
					} else if (testMrT.hasClass('message-group-me') && _this.avatar != '0') {
						$('.content-messenger').prepend("\r" + '<div class="message-group message-group-them">' + "\r" + '<div class="message-wrapper them" mes-id="' + _this.id + '">' + "\r" + '<div class="circle-wrapper animated bounceIn" style="background-image: url(' + _this.avatar + '); background-size: 40px 40px;"></div>' + "\r" + '<div class="text-wrapper">' + "\r" +  _this.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r" + '</div>' + "\r");
					} else {
						$('.content-messenger').prepend("\r" + '<div class="message-group message-group-me">' + "\r" + '<div class="message-wrapper me" mes-id="' + _this.id + '">' + "\r" + '<div class="text-wrapper">' + "\r" + _this.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r" + '</div>' + "\r");
					}
				}
				data_rc = $('#content').height();
				$('#inner').animate({scrollTop: data_rc - data_sc}, 0, 'easeInOutExpo');
				if (data.continue == 0) {
					default_sec.html('Không còn nội dung để xem...');
					default_sec.attr('data-c', '0');
					default_sec.removeAttr('onclick');
				} else {
					default_sec.html(default_text);
				}
			}
		});
	}
}

function viewChatLoad() {
	button_wrapper = $('.phieubacChat').find('.button');
	button_default_text = button_wrapper.find('span').text();
	button_wrapper.html(loading);
	button_wrapper.removeAttr('onclick');
	outgoing_data = new Object();
	outgoing_data.t = 'chatbox';
	outgoing_data.a = 'filter';
	outgoing_data.view = 'log';
	admin = $('#admin');
	if(admin.length) {
		outgoing_data.admin = '1';
	} else {
		outgoing_data.admin = '0';
	}
	if ($('.phieubac-boxchat .list1').length > 0) {
		outgoing_data.start_row = $('.phieubac-boxchat .list1').length;
	}
	$.post('/request.php', outgoing_data, function (data) {
		if (data.status) {
			var boxchat_list = '',
					boxchat_data  = data.chatbox_list;

			for (var i = 0; i < boxchat_data.length; i++) {
				var chatbox_m = boxchat_data[i];
				boxchat_list += '<div id="chat' + chatbox_m['id'] + '" class="list1" data-id="' + chatbox_m['id'] + '">';
				boxchat_list += '<div class="fauthor">';
				boxchat_list += '<img src="' + chatbox_m['avatar'] + '" class="thumb" alt="' + chatbox_m['name'] + '" /><ul><li>';

				// ** Link user */
				boxchat_list += (chatbox_m['link'] ? '<a href="/profile/?user=' + chatbox_m['user_id'] + '" class="tload">' : '') + '<strong class="' + (chatbox_m['rights'] == 9 ? 'nickadmin' : 'nickname') + '">' + chatbox_m['name'] + '</strong>' + (chatbox_m['link'] ? '</a>' : '');

				// ** Time */
				boxchat_list += '</li><li><span class="text--italic gray fsize--12">' + (chatbox_m['timestamp'] != 0 ? '<span class="ajax-time" title="' + chatbox_m['timestamp'] + '">' + chatbox_m['time'] + '</span>' : chatbox_m['time']) + '</span></li></ul>';

				// ** Admin panel */
				boxchat_list += (chatbox_m['panel'] ? '<span class="chatmore"><a class="tload" href="/guestbook/index.php?act=otvet&amp;id=' + chatbox_m.id + '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE15E;</i></a>' + (chatbox_m['panel_more'] ? ' <a class="tload" href="/guestbook/index.php?act=edit&amp;id=' + chatbox_m.id + '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE254;</i></a> <a class="tload" href="/guestbook/index.php?act=delpost&amp;id=' + chatbox_m.id + '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE872;</i></a>' : '') + '</span>' : '');

				/** Post */
				boxchat_list += '</div>' + chatbox_m['text'] + (chatbox_m['reply']['time'] != 0 ? '<div class="reply"><strong class="nickadmin">' + chatbox_m['reply']['name'] + '</strong> <span class="fsize--11-5">' + chatbox_m['reply']['time'] + '</span><br>' + chatbox_m['reply']['text'] + '</div>' : '') + '</div>';
			}
			$('.phieubac-boxchat').append(boxchat_list);
		}

		if (data.rest < 1) {
			button_wrapper.html('<span>Không còn nội dung để xem...</span>');
			loadingView = true;
		} else {
			button_wrapper.html('<span>' + button_default_text + '</span>');
			button_wrapper.attr('onclick', "viewChatLoad();");
			loadingView = false;
		}
	});
}
function searchToggle(obj, evt){
	var container = $(obj).closest('.search-wrapper');
	if(!container.hasClass('active')){
		container.addClass('active');
		evt.preventDefault();
	} else if (container.hasClass('active') && $(obj).closest('.input-holder').length == 0)
	{
		container.removeClass('active');
		// clear input
		container.find('.search-input').val('');
	}
}

// Begin Status
function repositionCover() {
	$('.cover-wrapper').hide();
	$('.cover-resize-wrapper').show();
	$('.cover-resize-buttons').show();
	$('.screen-width').val($('.cover-resize-wrapper').width());
	$('.profile-cover-resize')
		.css('cursor', 's-resize')
		.draggable({
			scroll: false,
			addClasses: false,
			axis: "y",
			cursor: "s-resize",
			drag: function (event, ui) {
				y1 = $('.timeline-header-wrapper').height();
				y2 = $('.cover-resize-wrapper').find('img').height();
				if (ui.position.top >= 0) {
					ui.position.top = 0;
				} else if (ui.position.top <= (y1-y2)) {
					ui.position.top = y1-y2;
				}
			},
			stop: function(event, ui) {
				$('input.cover-position').val(ui.position.top);
			}
		});
}

function saveReposition() {
	if ($('input.cover-position').length == 1) {
		posY = $('input.cover-position').val();
		$('form.cover-position-form').submit();
	}
}
function cancelReposition() {
	$('.cover-wrapper').show();
	$('.cover-resize-wrapper').hide();
	$('.cover-resize-buttons').hide();
	$('input.cover-position').val(0);
	$('.profile-cover-resize').draggable('destroy').css('cursor','default');
}

function statusComment(statusID) {
	var el_status_comments = $('.ps' + statusID).find('.status_comments');
	el_status_comments.slideToggle();
}
function comment_reply (commentID) {
	var el_reply_comments = $('.comment' + commentID).find('.comment_replys');
	el_reply_comments.slideToggle();
}
function re_reply(replyID, nick) {
	var el_reply = $('.comment' + replyID).find('textarea'),
			data_textarea = el_reply.val();
	el_reply.val(data_textarea + '[@' + nick + '] ');
}

function loadStatus(op = false, el = null) {
	stt_data = new Object();
	stt_data.t = 'status';
	stt_data.a = 'loadStatus';
	stt_data.user = $('.list_status').attr('user-id');
	if ($('.list_status .profile_status').length > 0) {
		if (op) {
			button = $(el);
			button_default_text = button.find('span').text();
			button.html(loading);
			button.removeAttr('onclick');
			stt_data.view = 'old';
			stt_data.ss_id = $('.list_status .profile_status').last().attr('stt-id');
			stt_data.start_row = $('.list_status .profile_status').length;
		} else {
			stt_data.ss_id = $('.list_status .profile_status:first').attr('stt-id');
		}
	}
	$.post('/request.php', stt_data, function(data) {
		if (data.status) {
			var list_status = data.data,
					countStt = list_status.length,
					out_stt = '';

			for (var si = 0; si < countStt; si++) {
				out_stt += htmlStatus(list_status[si]);
			}

			if (op){
				$('.list_status').append(out_stt);
				if (data.rest < 1) {
					button.html('Không còn nội dung để xem...');
				}else{
					button.attr('onclick', 'loadStatus(true, this);');
					button.html('<span>' + button_default_text + '</span>');
				}
			}else{
				$('.list_status').prepend(out_stt);
			}

		}

		phieubac_unbind();
	});
}
function htmlStatus(data){
	_this     = data;
	lstyle    = '';
	opLike    = 'Thích';
	lsstyle   = 'display:none;';
	lssicon   = 'icon-like-blf--18';
	reactions = _this.reactions;

	if (!_this.reactions.check) {
		lstyle = 'display:none;';
	}

	if (reactions.like.i || reactions.love.i || reactions.haha.i || reactions.hihi.i || reactions.woww.i || reactions.cry.i || reactions.angry.i || reactions.wtf.i) {
		lsstyle = '';
	}

	if (reactions.like.i) {
		opLike = 'Like';
		lssicon = 'icon-like-new--18';
	}
	if (reactions.love.i) {
		opLike = 'Love';
		lssicon = 'icon-love-new--18';
	}
	if (reactions.haha.i) {
		opLike = 'Haha';
		lssicon = 'icon-haha-new--18';
	}
	if (reactions.hihi.i) {
		opLike = 'Hihi';
		lssicon = 'icon-hihi-new--18';
	}
	if (reactions.woww.i) {
		opLike = 'Woww';
		lssicon = 'icon-woww-new--18';
	}
	if (reactions.cry.i) {
		opLike = 'Cry';
		lssicon = 'icon-cry-new--18';
	}
	if (reactions.angry.i) {
		opLike = 'Angry';
		lssicon = 'icon-angry-new--18';
	}
	if (reactions.wtf.i) {
		opLike = 'WTF';
		lssicon = 'icon-like-blf--18';
	}

	out_stt = '<div class="profile_status ps' + _this['id'] + ' mrt-code card shadow--2dp" stt-id="' + _this['id'] + '">'
		+ '<div class="card__actions">'
		+ '<table border="0" cellspacing="0" cellpadding="0"><tr><td width="48px">'
		+ '<img class="avatar" src="' + _this['avatar'] + '" alt="' + _this['u_name'] + '">'
		+ '</td><td>'
		+ (_this['f_id'] != _this['u_id'] ? '<a href="/profile/?user=' + _this['u_id'] + '" class="tload nickname' + (_this['u_rights'] == 9 ? ' red' : '') + '"><strong>' + _this['u_name'] + '</strong></a> > <a href="/profile/?user=' + _this['f_id'] + '" class="tload nickname' + (_this['f_rights'] == 9 ? ' red' : '') + '"><strong>' + _this['f_name'] + '</strong></a>' : '<a href="/profile/?user=' + _this['u_id'] + '" class="tload nickname' + (_this['u_rights'] == 9 ? ' red' : '') + '"><strong>' + _this['u_name'] + '</strong></a>')
		+ '<div class="status_other_data gray">'
		+ '<i class="material-icons">&#xE192;</i> ' + (_this['timestamp'] != 0 ? '<span class="ajax-time" title="' + _this['timestamp'] + '">' + _this['time'] + '</span>' : _this['time'])
		+ '</div>'
		+ '</td></tr></table>'
		+ '</div>'
		+ '<div class="card__actions post">' + _this['text'] + '</div>'
		+ '<div class="reactions card__actions">'
		+ '<ul class="who-likes-this-post" id="stt_reactions' + _this['id'] + '" style="' + lstyle + '">';

	if(reactions.like.u != "0"){
		out_stt += '<li class="likes reaction_wrap-style icon-newL reaction18px like lpos" id="stt_elike' + _this['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactions.like.d) + '</span></li>';
	}else{
		out_stt += '<li class="likes reaction_wrap-style icon-newL reaction18px like lpos" id="stt_elike' + _this['id'] + '" style="display:none"></li>';
	}

	if(reactions.love.u != "0"){
		out_stt += '<li class="loves reaction_wrap-style icon-newL reaction18px love lpos" id="stt_elove' + _this['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactions.love.d) + '</span></li>';
	}else{
		out_stt += '<li class="loves reaction_wrap-style icon-newL reaction18px love lpos" id="stt_elove' + _this['id'] + '" style="display:none"></li>';
	}

	if(reactions.haha.u != "0"){
		out_stt += '<li class="hahas reaction_wrap-style icon-newL reaction18px haha lpos" id="stt_ehaha' + _this['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactions.haha.d) + '</span></li>';
	}else{
		out_stt += '<li class="hahas reaction_wrap-style icon-newL reaction18px haha lpos" id="stt_ehaha' + _this['id'] + '" style="display:none"></li>';
	}

	if(reactions.hihi.u != "0"){
		out_stt += '<li class="hihis reaction_wrap-style icon-newL icon-hihi-new--18 lpos" id="stt_ehihi' + _this['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactions.hihi.d) + '</span></li>';
	}else{
		out_stt += '<li class="hihis reaction_wrap-style icon-newL icon-hihi-new--18 lpos" id="stt_ehihi' + _this['id'] + '" style="display:none"></li>';
	}

	if(reactions.woww.u != "0"){
		out_stt += '<li class="wowws reaction_wrap-style icon-newL reaction18px woww lpos" id="stt_ewoww' + _this['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactions.woww.d) + '</span></li>';
	}else{
		out_stt += '<li class="wowws reaction_wrap-style icon-newL reaction18px woww lpos" id="stt_ewoww' + _this['id'] + '" style="display:none"></li>';
	}

	if(reactions.cry.u != "0"){
		out_stt += '<li class="crys reaction_wrap-style icon-newL reaction18px cry lpos" id="stt_ecry' + _this['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactions.cry.d) + '</span></li>';
	}else{
		out_stt += '<li class="crys reaction_wrap-style icon-newL reaction18px cry lpos" id="stt_ecry' + _this['id'] + '" style="display:none"></li>';
	}

	if(reactions.angry.u != "0"){
		out_stt += '<li class="angrys reaction_wrap-style icon-newL reaction18px angry lpos" id="stt_eangry' + _this['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactions.angry.d) + '</span></li>';
	}else{
		out_stt += '<li class="angrys reaction_wrap-style icon-newL reaction18px angry lpos" id="stt_eangry' + _this['id'] + '" style="display:none"></li>';
	}

	if(reactions.wtf.u != "0"){
		out_stt += '<li class="wtfs reaction_wrap-style icon-newL icon-like-blf--18 lpos" id="stt_ewtf' + _this['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactions.wtf.d) + '</span></li>';
	}else{
		out_stt += '<li class="wtfs reaction_wrap-style icon-newL icon-like-blf--18 lpos" id="stt_ewtf' + _this['id'] + '" style="display:none"></li>';
	}

	out_stt += '<li class="totalco" id="stt_totalco' + _this['id'] + '">' + reactions['info'] + '</li>'
		+ '</ul>'
		+ '</div>'
		+ '<div class="status_active list1">'
		+ '<ul class="status_ul">'
		+ '<li id="likehover" map="1" data="' + _this['id'] + '">'
		+ '<div class="reactionTrans icon-lpn reaction_grap-style ' + lssicon + '" id="stt_ulk' + _this['id'] + '" style="' + lsstyle + '"></div>'
		+ '<div class="reatext" id="stt_reatext' + _this['id'] + '">' + opLike + '</div>'
		+ '<div id="STTReactions' + _this['id'] + '" class="STTReactions new_like_items">'
		+ '<div class="like_hover op-lw like_button reactionTrans" id="sttlike' + _this['id'] + '" rel="' + (reactions.like.i ? 'UnLike' : 'Like') + '" map="status">'
		+ '<div class="reactionTrans icon-newL icon-like-new"></div>'
		+ '</div>'
		+ '<div class="love_hover op-lw like_button reactionTrans" id="sttlove' + _this['id'] + '" rel="' + (reactions.love.i ? 'UnLove' : 'Love') + '" map="status">'
		+ '<div class="reactionTrans icon-newL icon-love-new"></div>'
		+ '</div>'
		+ '<div class="haha_hover op-lw like_button reactionTrans" id="stthaha' + _this['id'] + '" rel="' + (reactions.haha.i ? 'UnHaha' : 'Haha') + '" map="status">'
		+ '<div class="reactionTrans icon-newL icon-haha-new"></div>'
		+ '</div>'
		+ '<div class="hihi_hover op-lw like_button reactionTrans" id="stthihi' + _this['id'] + '" rel="' + (reactions.hihi.i ? 'UnHihi' : 'Hihi') + '" map="status">'
		+ '<div class="reactionTrans icon-newL icon-hihi-new"></div>'
		+ '</div>'
		+ '<div class="woww_hover op-lw like_button reactionTrans" id="sttwoww' + _this['id'] + '" rel="' + (reactions.woww ? 'Woww' : 'Woww') + '" map="status">'
		+ '<div class="reactionTrans icon-newL icon-woww-new"></div>'
		+ '</div>'
		+ '<div class="cry_hover op-lw like_button reactionTrans" id="sttcry' + _this['id'] + '" rel="' + (reactions.cry.i ? 'UnCry' : 'Cry') + '" map="status">'
		+ '<div class="reactionTrans icon-newL icon-cry-new"></div>'
		+ '</div>'
		+ '<div class="angry_hover op-lw like_button reactionTrans" id="sttangry' + _this['id'] + '" rel="' + (reactions.angry.i ? 'UnAngry' : 'Angry') + '" map="status">'
		+ '<div class="reactionTrans icon-newL icon-angry-new"></div>'
		+ '</div>'
		+ '<div class="wtf_hover op-lw like_button reactionTrans" id="sttwtf' + _this['id'] + '" rel="' + (reactions.wtf.i ? 'UnWTF' : 'WTF') + '" map="status">'
		+ '<div class="reactionTrans icon-newL icon-like-blf"></div>'
		+ '</div>'
		+ '</div>'
		+ '</li>'
		+ '<li onclick="statusComment(' + _this['id'] + ');">Bình Luận<span class="numCMT_' + _this['id'] + ' numCMT' + (_this['num'] > 0 ? '' : ' hidden') + '"> (' + _this['num'] + ')</span></li>'
		+ '<li>Chia sẻ</li>'
		+ '</ul>'
		+ '</div>'
		+ '<div class="status_comments list1" style="display: none;">'
		+ (_this['num'] > 5 ? '<div class="like-it like-pit" onclick="loadComments(' + _this['id'] + ', true, this);" style="margin: 0px 0 10px 29px;">Xem thêm bình luận...</div>' : '')
		+ '<div class="list_comment_' + _this['id'] + '">';

	list_comments = _this['comments'];
	countCMT = list_comments.length;
	for (var j = 0; j < countCMT; j++) {
		var _thisCMT = list_comments[j];
				lstyle    = '',
				opLike    = 'Thích',
				reactionsCmt = _thisCMT.reactions;

		if (!reactionsCmt.check) {
			lstyle = 'display:none;';
		}
		if (reactionsCmt.like.i) {
			opLike = 'Like';
		}
		if (reactionsCmt.love.i) {
			opLike = 'Love';
		}
		if (reactionsCmt.haha.i) {
			opLike = 'Haha';
		}
		if (reactionsCmt.hihi.i) {
			opLike = 'Hihi';
		}
		if (reactionsCmt.woww.i) {
			opLike = 'Woww';
		}
		if (reactionsCmt.cry.i) {
			opLike = 'Cry';
		}
		if (reactionsCmt.angry.i) {
			opLike = 'Angry';
		}
		if (reactionsCmt.wtf.i) {
			opLike = 'WTF';
		}

		out_stt += '<div class="comment' + _thisCMT['id'] + ' comment-wrapper" ss-id="' + _thisCMT['id'] + '">'
			+ '<div>'
			+ '<div class="fleft">'
			+ '<img class="avatar" src="' + _thisCMT['avatar'] + '" alt="' + _thisCMT['name'] + '">'
			+ '</div>'
			+ '<div class="comment_more">'
			+ '<div class="comment_content">'
			+ '<div class="comment">'
			+ '<div>'
			+ '<a href="/profile/?user=' + _thisCMT['uid'] + '" class="tload nickname' + (_thisCMT['rights'] == 9 ? ' red' : '') + '"><strong>' + _thisCMT['name'] + '</strong></a>'
			+ ': ' + _thisCMT['text']
			+ '</div>'
			+ '<div class="cm_time">'
			+ '<span class="status_other_data gray">' + (_thisCMT['timestamp'] != 0 ? '<span class="ajax-time" title="' + _thisCMT['timestamp'] + '">' + _thisCMT['time'] + '</span>' : _thisCMT['time']) + '</span>'
			+ '</div>'
			+ '<div class="comment_reactions">'
			+ '<ul class="who-likes-this-post" id="cmt_reactions' + _thisCMT['id'] + '" style="' + lstyle + '">';

		if(reactionsCmt.like.u != "0"){
			out_stt += '<li class="likes reaction_wrap-style icon-newL reaction16px like lpos" id="cmt_elike' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.like.d) + '</span></li>'; 
		}else{
			out_stt += '<li class="likes reaction_wrap-style icon-newL reaction16px like lpos" id="cmt_elike' + _thisCMT['id'] + '" style="display:none"></li>';
		}

		if(reactionsCmt.love.u != "0"){
			out_stt += '<li class="loves reaction_wrap-style icon-newL reaction16px love lpos" id="cmt_elove' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.love.d) + '</span></li>'; 
		}else{
			out_stt += '<li class="loves reaction_wrap-style icon-newL reaction16px love lpos" id="cmt_elove' + _thisCMT['id'] + '" style="display:none"></li>';
		}

		if(reactionsCmt.haha.u != "0"){
			out_stt += '<li class="hahas reaction_wrap-style icon-newL reaction16px haha lpos" id="cmt_ehaha' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.haha.d) + '</span></li>'; 
		}else{
			out_stt += '<li class="hahas reaction_wrap-style icon-newL reaction16px haha lpos" id="cmt_ehaha' + _thisCMT['id'] + '" style="display:none"></li>';
		}

		if(reactionsCmt.hihi.u != "0"){
			out_stt += '<li class="hihis reaction_wrap-style icon-newL reaction16px hihi lpos" id="cmt_ehihi' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.hihi.d) + '</span></li>'; 
		}else{
			out_stt += '<li class="hihis reaction_wrap-style icon-newL reaction16px hihi lpos" id="cmt_ehihi' + _thisCMT['id'] + '" style="display:none"></li>';
		}

		if(reactionsCmt.woww.u != "0"){
			out_stt += '<li class="wowws reaction_wrap-style icon-newL reaction16px woww lpos" id="cmt_ewoww' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.woww.d) + '</span></li>'; 
		}else{
			out_stt += '<li class="wowws reaction_wrap-style icon-newL reaction16px woww lpos" id="cmt_ewoww' + _thisCMT['id'] + '" style="display:none"></li>';
		}

		if(reactionsCmt.cry.u != "0"){
			out_stt += '<li class="crys reaction_wrap-style icon-newL reaction16px cry lpos" id="cmt_ecry' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.cry.d) + '</span></li>'; 
		}else{
			out_stt += '<li class="crys reaction_wrap-style icon-newL reaction16px cry lpos" id="cmt_ecry' + _thisCMT['id'] + '" style="display:none"></li>';
		}

		if(reactionsCmt.angry.u != "0"){
			out_stt += '<li class="angrys reaction_wrap-style icon-newL reaction16px angry lpos" id="cmt_eangry' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.angry.d) + '</span></li>'; 
		}else{
			out_stt += '<li class="angrys reaction_wrap-style icon-newL reaction16px angry lpos" id="cmt_eangry' + _thisCMT['id'] + '" style="display:none"></li>';
		}

		if(reactionsCmt.wtf.u != "0"){
			out_stt += '<li class="wtfs reaction_wrap-style icon-newL reaction16px wtf lpos" id="cmt_ewtf' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.wtf.d) + '</span></li>'; 
		} else {
			out_stt += '<li class="wtfs reaction_wrap-style icon-newL reaction16px wtf lpos" id="cmt_ewtf' + _thisCMT['id'] + '" style="display:none"></li>';
		}

		out_stt += '<li class="totalco" id="cmt_totalco' + _thisCMT['id'] + '">' + reactionsCmt['info'] + '</li>'
			+ '</ul>'
			+ '</div>'
			+ '</div>'
			+ '<span class="cm_more"><i class="material-icons">&#xE5D3;</i></span>'
			+ '</div>'
			+ '<ul class="comment_active">'
			+ '<li id="likehover" map="2" data="' + _thisCMT['id'] + '">'
			+ '<div class="reatext" id="cmt_reatext' + _thisCMT['id'] + '">' + opLike + '</div>'
			+ '<div id="CMTReactions' + _thisCMT['id'] + '" class="CMTReactions new_like_items">'
			+ '<div class="like_hover op-lw like_button reactionTrans" id="cmtlike' + _thisCMT['id']   + '" rel="' + (reactionsCmt.like.i ? 'UnLike' : 'Like') + '" map="status_comment"><div class="reactionTrans icon-newL icon-like-new"></div></div>'
			+ '<div class="love_hover op-lw like_button reactionTrans" id="cmtlove' + _thisCMT['id']   + '" rel="' + (reactionsCmt.love.i ? 'UnLove' : 'Love') + '" map="status_comment"><div class="reactionTrans icon-newL icon-love-new"></div></div>'
			+ '<div class="haha_hover op-lw like_button reactionTrans" id="cmthaha' + _thisCMT['id']   + '" rel="' + (reactionsCmt.haha.i ? 'UnHaha' : 'Haha') + '" map="status_comment"><div class="reactionTrans icon-newL icon-haha-new"></div></div>'
			+ '<div class="hihi_hover op-lw like_button reactionTrans" id="cmthihi' + _thisCMT['id']   + '" rel="' + (reactionsCmt.hihi.i ? 'UnHihi' : 'Hihi') + '" map="status_comment"><div class="reactionTrans icon-newL icon-hihi-new"></div></div>'
			+ '<div class="woww_hover op-lw like_button reactionTrans" id="cmtwoww' + _thisCMT['id']   + '" rel="' + (reactionsCmt.woww.i ? 'UnWoww' : 'Woww') + '" map="status_comment"><div class="reactionTrans icon-newL icon-woww-new"></div></div>'
			+ '<div class="cry_hover op-lw like_button reactionTrans" id="cmtcry'   + _thisCMT['id']   + '" rel="' + (reactionsCmt.cry.i ? 'UnCry' : 'Cry') + '" map="status_comment"><div class="reactionTrans icon-newL icon-cry-new"></div></div>'
			+ '<div class="angry_hover op-lw like_button reactionTrans" id="cmtangry' + _thisCMT['id'] + '" rel="' + (reactionsCmt.angry.i ? 'UnAngry' : 'Angry') + '" map="status_comment"><div class="reactionTrans icon-newL icon-angry-new"></div></div>'
			+ '<div class="wtf_hover op-lw like_button reactionTrans" id="cmtwtf'   + _thisCMT['id']   + '" rel="' + (reactionsCmt.wtf.i ? 'UnWTF' : 'WTF') + '" map="status_comment"><div class="reactionTrans icon-newL icon-like-blf"></div></div>'
			+ '</div>'
			+ '</li>'
			+ '<li onclick="comment_reply(' + _thisCMT['id'] + ');">Trả lời<span class="numREP_' + _thisCMT['id'] + ' numCMT' + (_thisCMT['num'] > 0 ? '' : ' hidden') + '"> (' + _thisCMT['num'] + ')</span></li>'
			+ '</ul>'
			+ '<div class="comment_replys">'
			+ (_thisCMT['num'] > 5 ? '<div class="like-it like-pit" onclick="loadReplys(' + _thisCMT['id'] + ', true, this);" style="margin: 0px 0 10px 17px;">Xem thêm trả lời...</div>' : '')
			+ '<div class="list_reply_' + _thisCMT['id'] + ' list_reply">';

		var list_reply = _thisCMT['reply'],
				countREPLY = list_reply.length;
		if( countREPLY > 0) {
			for (var ij = 0; ij < countREPLY; ij++) {
				var _thisReply = list_reply[ij];
				out_stt += htmlReplys(_thisReply);
			}
		}

		out_stt += '</div>'
			+ '<div class="comment-write">'
			+ '<div class="fleft">'
			+ '<img class="avatar" src="' + _thisCMT['avatar'] + '">'
			+ '</div>'
			+ '<div class="comment-textarea">'
			+ '<textarea class="js_add_nick" name="text" placeholder="Trả lời.?" onkeyup="commentReply(this.value,' + _thisCMT['id'] + ',event);"></textarea>'
			+ '</div>'
			+ '</div>'
			+ '</div>'
			+ '</div>'
			+ '</div>'
			+ '</div>';
	}

	out_stt += '</div>'
		+ '<div class="comment-write">'
		+ '<div class="fleft">'
		+ '<img class="avatar" src="' + _this['avatar'] + '">'
		+ '</div>'
		+ '<div class="comment-textarea">'
		+ '<textarea name="text" placeholder="Bạn thấy sao.?" onkeyup="postComment(this.value,' + _this['id'] + ',event);"></textarea>'
		+ '</div>'
		+ '</div>'
		+ '</div>'
		+ '</div>';

	return out_stt;

}

function htmlComents(data){
	var _thisCMT = data;
			lstyle    = '',
			opLike    = 'Thích',
			reactionsCmt = _thisCMT.reactions;

	if (!reactionsCmt.check) {
		lstyle = 'display:none;';
	}
	if (reactionsCmt.like.i) {
		opLike = 'Like';
	}
	if (reactionsCmt.love.i) {
		opLike = 'Love';
	}
	if (reactionsCmt.haha.i) {
		opLike = 'Haha';
	}
	if (reactionsCmt.hihi.i) {
		opLike = 'Hihi';
	}
	if (reactionsCmt.woww.i) {
		opLike = 'Woww';
	}
	if (reactionsCmt.cry.i) {
		opLike = 'Cry';
	}
	if (reactionsCmt.angry.i) {
		opLike = 'Angry';
	}
	if (reactionsCmt.wtf.i) {
		opLike = 'WTF';
	}

	out = '<div class="comment' + _thisCMT['id'] + ' comment-wrapper" ss-id="' + _thisCMT['id'] + '">'
		+ '<div>'
		+ '<div class="fleft">'
		+ '<img class="avatar" src="' + _thisCMT['avatar'] + '" alt="' + _thisCMT['name'] + '">'
		+ '</div>'
		+ '<div class="comment_more">'
		+ '<div class="comment_content">'
		+ '<div class="comment">'
		+ '<div>'
		+ '<a href="/profile/?user=' + _thisCMT['uid'] + '" class="tload nickname' + (_thisCMT['rights'] == 9 ? ' red' : '') + '"><strong>' + _thisCMT['name'] + '</strong></a>'
		+ ': ' + _thisCMT['text']
		+ '</div>'
		+ '<div class="cm_time">'
		+ '<span class="status_other_data gray">' + (_thisCMT['timestamp'] != 0 ? '<span class="ajax-time" title="' + _thisCMT['timestamp'] + '">' + _thisCMT['time'] + '</span>' : _thisCMT['time']) + '</span>'
		+ '</div>'
		+ '<div class="comment_reactions">'
		+ '<ul class="who-likes-this-post" id="cmt_reactions' + _thisCMT['id'] + '" style="' + lstyle + '">';

	if(reactionsCmt.like.u != "0"){
		out += '<li class="likes reaction_wrap-style icon-newL reaction16px like lpos" id="cmt_elike' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.like.d) + '</span></li>'; 
	}else{
		out += '<li class="likes reaction_wrap-style icon-newL reaction16px like lpos" id="cmt_elike' + _thisCMT['id'] + '" style="display:none"></li>';
	}

	if(reactionsCmt.love.u != "0"){
		out += '<li class="loves reaction_wrap-style icon-newL reaction16px love lpos" id="cmt_elove' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.love.d) + '</span></li>'; 
	}else{
		out += '<li class="loves reaction_wrap-style icon-newL reaction16px love lpos" id="cmt_elove' + _thisCMT['id'] + '" style="display:none"></li>';
	}

	if(reactionsCmt.haha.u != "0"){
		out += '<li class="hahas reaction_wrap-style icon-newL reaction16px haha lpos" id="cmt_ehaha' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.haha.d) + '</span></li>'; 
	}else{
		out += '<li class="hahas reaction_wrap-style icon-newL reaction16px haha lpos" id="cmt_ehaha' + _thisCMT['id'] + '" style="display:none"></li>';
	}

	if(reactionsCmt.hihi.u != "0"){
		out += '<li class="hihis reaction_wrap-style icon-newL reaction16px hihi lpos" id="cmt_ehihi' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.hihi.d) + '</span></li>'; 
	}else{
		out += '<li class="hihis reaction_wrap-style icon-newL reaction16px hihi lpos" id="cmt_ehihi' + _thisCMT['id'] + '" style="display:none"></li>';
	}

	if(reactionsCmt.woww.u != "0"){
		out += '<li class="wowws reaction_wrap-style icon-newL reaction16px woww lpos" id="cmt_ewoww' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.woww.d) + '</span></li>'; 
	}else{
		out += '<li class="wowws reaction_wrap-style icon-newL reaction16px woww lpos" id="cmt_ewoww' + _thisCMT['id'] + '" style="display:none"></li>';
	}

	if(reactionsCmt.cry.u != "0"){
		out += '<li class="crys reaction_wrap-style icon-newL reaction16px cry lpos" id="cmt_ecry' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.cry.d) + '</span></li>'; 
	}else{
		out += '<li class="crys reaction_wrap-style icon-newL reaction16px cry lpos" id="cmt_ecry' + _thisCMT['id'] + '" style="display:none"></li>';
	}

	if(reactionsCmt.angry.u != "0"){
		out += '<li class="angrys reaction_wrap-style icon-newL reaction16px angry lpos" id="cmt_eangry' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.angry.d) + '</span></li>'; 
	}else{
		out += '<li class="angrys reaction_wrap-style icon-newL reaction16px angry lpos" id="cmt_eangry' + _thisCMT['id'] + '" style="display:none"></li>';
	}

	if(reactionsCmt.wtf.u != "0"){
		out += '<li class="wtfs reaction_wrap-style icon-newL reaction16px wtf lpos" id="cmt_ewtf' + _thisCMT['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsCmt.wtf.d) + '</span></li>'; 
	}else{
		out += '<li class="wtfs reaction_wrap-style icon-newL reaction16px wtf lpos" id="cmt_ewtf' + _thisCMT['id'] + '" style="display:none"></li>';
	}

	out += '<li class="totalco" id="cmt_totalco' + _thisCMT['id'] + '">' + reactionsCmt['info'] + '</li>'
		+ '</ul>'
		+ '</div>'
		+ '</div>'
		+ '<span class="cm_more"><i class="material-icons">&#xE5D3;</i></span>'
		+ '</div>'
		+ '<ul class="comment_active">'
		+ '<li id="likehover" map="2" data="' + _thisCMT['id'] + '">'
		+ '<div class="reatext" id="cmt_reatext' + _thisCMT['id'] + '">' + opLike + '</div>'
		+ '<div id="CMTReactions' + _thisCMT['id'] + '" class="CMTReactions new_like_items">'
		+ '<div class="like_hover op-lw like_button reactionTrans" id="cmtlike' + _thisCMT['id']   + '" rel="' + (reactionsCmt.like.i ? 'UnLike' : 'Like') + '" map="status_comment"><div class="reactionTrans icon-newL icon-like-new"></div></div>'
		+ '<div class="love_hover op-lw like_button reactionTrans" id="cmtlove' + _thisCMT['id']   + '" rel="' + (reactionsCmt.love.i ? 'UnLove' : 'Love') + '" map="status_comment"><div class="reactionTrans icon-newL icon-love-new"></div></div>'
		+ '<div class="haha_hover op-lw like_button reactionTrans" id="cmthaha' + _thisCMT['id']   + '" rel="' + (reactionsCmt.haha.i ? 'UnHaha' : 'Haha') + '" map="status_comment"><div class="reactionTrans icon-newL icon-haha-new"></div></div>'
		+ '<div class="hihi_hover op-lw like_button reactionTrans" id="cmthihi' + _thisCMT['id']   + '" rel="' + (reactionsCmt.hihi.i ? 'UnHihi' : 'Hihi') + '" map="status_comment"><div class="reactionTrans icon-newL icon-hihi-new"></div></div>'
		+ '<div class="woww_hover op-lw like_button reactionTrans" id="cmtwoww' + _thisCMT['id']   + '" rel="' + (reactionsCmt.woww.i ? 'UnWoww' : 'Woww') + '" map="status_comment"><div class="reactionTrans icon-newL icon-woww-new"></div></div>'
		+ '<div class="cry_hover op-lw like_button reactionTrans" id="cmtcry'   + _thisCMT['id']   + '" rel="' + (reactionsCmt.cry.i ? 'UnCry' : 'Cry') + '" map="status_comment"><div class="reactionTrans icon-newL icon-cry-new"></div></div>'
		+ '<div class="angry_hover op-lw like_button reactionTrans" id="cmtangry' + _thisCMT['id'] + '" rel="' + (reactionsCmt.angry.i ? 'UnAngry' : 'Angry') + '" map="status_comment"><div class="reactionTrans icon-newL icon-angry-new"></div></div>'
		+ '<div class="wtf_hover op-lw like_button reactionTrans" id="cmtwtf'   + _thisCMT['id']   + '" rel="' + (reactionsCmt.wtf.i ? 'UnWTF' : 'WTF') + '" map="status_comment"><div class="reactionTrans icon-newL icon-like-blf"></div></div>'
		+ '</div>'
		+ '</li>'
		+ '<li onclick="comment_reply(' + _thisCMT['id'] + ');">Trả lời</li>'
		+ '</ul>'
		+ '<div class="comment_replys">'
		+ '<div class="list_reply_' + _thisCMT['id'] + ' list_reply">';

	var list_reply = _thisCMT['reply'],
			countREPLY = list_reply.length;
	if( countREPLY > 0) {
		for (var ij = 0; ij < countREPLY; ij++) {
			var _thisReply = list_reply[ij];
			out += htmlReplys(_thisReply);
		}
	}

	out += '</div>'
		+ '<div class="comment-write">'
		+ '<div class="fleft">'
		+ '<img class="avatar" src="' + _thisCMT['avatar'] + '">'
		+ '</div>'
		+ '<div class="comment-textarea">'
		+ '<textarea class="js_add_nick" name="text" placeholder="Trả lời.?" onkeyup="commentReply(this.value,' + _thisCMT['id'] + ',event);"></textarea>'
		+ '</div>'
		+ '</div>'
		+ '</div>'
		+ '</div>'
		+ '</div>'
		+ '</div>';

	return out;
}
function htmlReplys(data) {
	var _thisReply = data,
			lstyle = '',
			opLike = 'Thích',
			reactionsRep = _thisReply.reactions;

	if (!reactionsRep.check) {
		lstyle = 'display:none;';
	}

	if (reactionsRep.like.i) {
		opLike = 'Like';
	}
	if (reactionsRep.love.i) {
		opLike = 'Love';
	}
	if (reactionsRep.haha.i) {
		opLike = 'Haha';
	}
	if (reactionsRep.hihi.i) {
		opLike = 'Hihi';
	}
	if (reactionsRep.woww.i) {
		opLike = 'Woww';
	}
	if (reactionsRep.cry.i) {
		opLike = 'Cry';
	}
	if (reactionsRep.angry.i) {
		opLike = 'Angry';
	}
	if (reactionsRep.wtf.i) {
		opLike = 'WTF';
	}

	outRep = '<div class="reply' + _thisReply['id'] + ' reply-wrapper" ss-id="' + _thisReply['id'] + '">'
		+ '<div>'
		+ '<div class="fleft">'
		+ '<img class="avatar" src="' + _thisReply['avatar'] + '" alt="' + _thisReply['name'] + '">'
		+ '</div>'
		+ '<div class="comment_more">'
		+ '<div class="comment_content">'
		+ '<div class="comment">'
		+ '<div>'
		+ '<a href="/profile/?user=' + _thisReply['uid'] + '" class="tload nickname' + (_thisReply['rights'] == 9 ? ' red' : '') + '"><strong>' + _thisReply['name'] + '</strong></a>'
		+ ': ' + _thisReply['text']
		+ '</div>'
		+ '<div class="cm_time">'
		+ '<span class="status_other_data gray">' + (_thisReply['timestamp'] != 0 ? '<span class="ajax-time" title="' + _thisReply['timestamp'] + '">' + _thisReply['time'] + '</span>' : _thisReply['time']) + '</span>'
		+ '</div>'
		+ '<div class="comment_reactions">'
		+ '<ul class="who-likes-this-post" id="rep_reactions' + _thisReply['id'] + '" style="' + lstyle + '">';

	if(reactionsRep.like.u != "0"){
		outRep += '<li class="likes reaction_wrap-style icon-newL reaction16px like lpos" id="rep_elike' + _thisReply['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsRep.like.d) + '</span></li>'; 
	}else{
		outRep += '<li class="likes reaction_wrap-style icon-newL reaction16px like lpos" id="rep_elike' + _thisReply['id'] + '" style="display:none"></li>';
	}


	if(reactionsRep.love.u != "0"){
		outRep += '<li class="loves reaction_wrap-style icon-newL reaction16px love lpos" id="rep_elove' + _thisReply['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsRep.love.d) + '</span></li>'; 
	}else{
		outRep += '<li class="loves reaction_wrap-style icon-newL reaction16px love lpos" id="rep_elove' + _thisReply['id'] + '" style="display:none"></li>';
	}


	if(reactionsRep.haha.u != "0"){
		outRep += '<li class="hahas reaction_wrap-style icon-newL reaction16px haha lpos" id="rep_ehaha' + _thisReply['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsRep.haha.d) + '</span></li>'; 
	}else{
		outRep += '<li class="hahas reaction_wrap-style icon-newL reaction16px haha lpos" id="rep_ehaha' + _thisReply['id'] + '" style="display:none"></li>';
	}


	if(reactionsRep.hihi.u != "0"){
		outRep += '<li class="hihis reaction_wrap-style icon-newL reaction16px hihi lpos" id="rep_ehihi' + _thisReply['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsRep.hihi.d) + '</span></li>'; 
	}else{
		outRep += '<li class="hihis reaction_wrap-style icon-newL reaction16px hihi lpos" id="rep_ehihi' + _thisReply['id'] + '" style="display:none"></li>';
	}


	if(reactionsRep.woww.u != "0"){
		outRep += '<li class="wowws reaction_wrap-style icon-newL reaction16px woww lpos" id="rep_ewoww' + _thisReply['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsRep.woww.d) + '</span></li>'; 
	}else{
		outRep += '<li class="wowws reaction_wrap-style icon-newL reaction16px woww lpos" id="rep_ewoww' + _thisReply['id'] + '" style="display:none"></li>';
	}


	if(reactionsRep.cry.u != "0"){
		outRep += '<li class="crys reaction_wrap-style icon-newL reaction16px cry lpos" id="rep_ecry' + _thisReply['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsRep.cry.d) + '</span></li>'; 
	}else{
		outRep += '<li class="crys reaction_wrap-style icon-newL reaction16px cry lpos" id="rep_ecry' + _thisReply['id'] + '" style="display:none"></li>';
	}


	if(reactionsRep.angry.u != "0"){
		outRep += '<li class="angrys reaction_wrap-style icon-newL reaction16px angry lpos" id="rep_eangry' + _thisReply['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsRep.angry.d) + '</span></li>'; 
	}else{
		outRep += '<li class="angrys reaction_wrap-style icon-newL reaction16px angry lpos" id="rep_eangry' + _thisReply['id'] + '" style="display:none"></li>';
	}


	if(reactionsRep.wtf.u != "0"){
		outRep += '<li class="wtfs reaction_wrap-style icon-newL reaction16px wtf lpos" id="rep_ewtf' + _thisReply['id'] + '"><span class="reaction_use">' + htmlReactionMore(reactionsRep.wtf.d) + '</span></li>'; 
	}else{
		outRep += '<li class="wtfs reaction_wrap-style icon-newL reaction16px wtf lpos" id="rep_ewtf' + _thisReply['id'] + '" style="display:none"></li>';
	}

	outRep += '<li class="totalco" id="rep_totalco' + _thisReply['id'] + '">' + reactionsRep['info'] + '</li>'
		+ '</ul>'
		+ '</div>'
		+ '</div>'
		+ '<span class="cm_more"><i class="material-icons">&#xE5D3;</i></span>'
		+ '</div>'
		+ '<ul class="comment_active">'
		+ '<li id="likehover" map="3" data="' + _thisReply['id'] + '">'
		+ '<div class="reatext" id="rep_reatext' + _thisReply['id'] + '">' + opLike + '</div>'
		+ '<div id="ReplyReactions' + _thisReply['id'] + '" class="ReplyReactions new_like_items">'
		+ '<div class="like_hover op-lw like_button reactionTrans" id="replike' + _thisReply['id'] + '" rel="' + (reactionsRep.like.i ? 'UnLike' : 'Like') + '" map="status_reply"><div class="reactionTrans icon-newL icon-like-new"></div></div>'
		+ '<div class="love_hover op-lw like_button reactionTrans" id="replove' + _thisReply['id'] + '" rel="' + (reactionsRep.love.i ? 'UnLove' : 'Love') + '" map="status_reply"><div class="reactionTrans icon-newL icon-love-new"></div></div>'
		+ '<div class="haha_hover op-lw like_button reactionTrans" id="rephaha' + _thisReply['id'] + '" rel="' + (reactionsRep.haha.i ? 'UnHaha' : 'Haha') + '" map="status_reply"><div class="reactionTrans icon-newL icon-haha-new"></div></div>'
		+ '<div class="hihi_hover op-lw like_button reactionTrans" id="rephihi' + _thisReply['id'] + '" rel="' + (reactionsRep.hihi.i ? 'UnHihi' : 'Hihi') + '" map="status_reply"><div class="reactionTrans icon-newL icon-hihi-new"></div></div>'
		+ '<div class="woww_hover op-lw like_button reactionTrans" id="repwoww' + _thisReply['id'] + '" rel="' + (reactionsRep.woww.i ? 'UnWoww' : 'Woww') + '" map="status_reply"><div class="reactionTrans icon-newL icon-woww-new"></div></div>'
		+ '<div class="cry_hover op-lw like_button reactionTrans" id="repcry' + _thisReply['id'] + '" rel="' + (reactionsRep.cry.i ? 'UnCry' : 'Cry') + '" map="status_reply"><div class="reactionTrans icon-newL icon-cry-new"></div></div>'
		+ '<div class="angry_hover op-lw like_button reactionTrans" id="repangry' + _thisReply['id'] + '" rel="' + (reactionsRep.angry.i ? 'UnAngry' : 'Angry') + '" map="status_reply"><div class="reactionTrans icon-newL icon-angry-new"></div></div>'
		+ '<div class="wtf_hover op-lw like_button reactionTrans" id="repwtf' + _thisReply['id'] + '" rel="' + (reactionsRep.wtf.i ? 'UnWTF' : 'WTF') + '" map="status_reply"><div class="reactionTrans icon-newL icon-like-blf"></div></div>'
		+ '</div>'
		+ '</li>'
		+ '<li onclick="re_reply(' + _thisReply['reid'] + ', \'' + _thisReply['name'] + '\');">'
		+ 'Trả lời'
		+ '</li>'
		+ '</ul>'
		+ '</div>'
		+ '</div>'
		+ '</div>';

	return outRep;
}

function htmlReactionMore(data) {
	var list_u = data.data,
			countU = list_u.length,
			out = '';
	if( countU > 0) {
		for (var u = 0; u < countU; u++) {
			var _thisU = list_u[u];
			out +='<a href="/profile/?user=' + _thisU['id'] + '">' +
				'<span class="m-chip m-chip--contact m-chip--deletable">' +
					'<img class="m-chip__contact" src="' + _thisU['avatar'] + '" alt="' + _thisU['name'] + '">' +
					'<span class="nickname">' + _thisU['name'] + '</span>' +
				'</span>' +
			'</a>';
		}
		if (data.rest > 0) {
			 out += '<span class="reaction_total-style">' + data.info + '</span>';
		}
	}
	return out;
}

// Post comment status
function postComment(text = '', post_id = 0, event){
	if (event.keyCode == 13 && event.shiftKey == 0)
	{
		main_wrapper = $('.ps' + post_id);
		comment_textarea = main_wrapper.find('.comment-textarea');
		textarea_wrapper = comment_textarea.find('textarea');
		textarea_wrapper.val('');

		in_data         = new Object();
		in_data.t       = 'status';
		in_data.a       = 'addComment';
		in_data.post_id = post_id;
		in_data.text    = text;

		$.post('/request.php', in_data, function (data) {
			if (data.status) {
				loadComments(post_id);
			}
		});
	}
}
function loadComments(post_id = 0, op = false, el = null) {
	cmt_el = $('.list_comment_' + post_id + ' .comment-wrapper');
	out = '';
	cmt_data = new Object();
	cmt_data.t = 'status';
	cmt_data.a = 'loadComments';
	cmt_data.post_id = post_id;
	if (cmt_el.length > 0) {
		if (op) {
			button = $(el);
			button_default_text = button.text();
			button.html(loadingRED);
			cmt_data.view = 'old';
			cmt_data.ss_id = cmt_el.first().attr('ss-id');
			cmt_data.start_row = cmt_el.length;
		} else {
			cmt_data.ss_id = cmt_el.last().attr('ss-id');
		}
	}
	$.post('/request.php', cmt_data, function(data) {
		if (data.status) {
			if (data.num > 0)
				$('.numCMT_' + post_id).show().text(' (' + data.num + ')');

			list_comments = data.data;
			out = '';
			countCMT = list_comments.length;
			for (var j = 0; j < countCMT; j++) {
				out += htmlComents(list_comments[j]);
			}
			if (op){
				$('.list_comment_' + post_id).prepend(out);
			}else{
				$('.list_comment_' + post_id).append(out);
			}
		}

		if (op) {
			if (data.rest < 1) {
				button.remove();
			}else{
				button.text(button_default_text);
			}
		}

		phieubac_unbind();
	});
}

function commentReply(text = '', post_id = 0, event){
	if (event.keyCode == 13 && event.shiftKey == 0)
	{
		main_wrapper = $('.comment' + post_id);
		comment_textarea = main_wrapper.find('.comment-textarea');
		textarea_wrapper = comment_textarea.find('textarea');
		textarea_wrapper.val('');

		in_data            = new Object();
		in_data.t          = 'status';
		in_data.a          = 'addReply';
		in_data.post_id    = post_id;
		in_data.text       = text;

		$.post('/request.php', in_data, function (data) {
			if (data.status) {
				loadReplys(post_id);
			}
		});
	}
}

function loadReplys(post_id = 0, op = false, el = null) {
	rep_el = $('.list_reply_' + post_id + ' .reply-wrapper');
	out = '';
	rep_data = new Object();
	rep_data.t = 'status';
	rep_data.a = 'loadReplys';
	rep_data.post_id = post_id;
	if (rep_el.length > 0) {
		if (op) {
			button = $(el);
			button_default_text = button.text();
			button.html(loadingRED);
			rep_data.view = 'old';
			rep_data.ss_id = rep_el.first().attr('ss-id');
			rep_data.start_row = rep_el.length;
		} else {
			rep_data.ss_id = rep_el.last().attr('ss-id');
		}
	}

	$.post('/request.php', rep_data, function(data) {
		if (data.status) {
			var list_reply = data.data,
					countREPLY = list_reply.length,
					out = '';
			for (var ij = 0; ij < countREPLY; ij++) {
				out += htmlReplys(list_reply[ij]);
			}
			if (op){
				$('.list_reply_' + post_id).prepend(out);
			}else{
				$('.list_reply_' + post_id).append(out);
			}

			if (data.num > 0){
				$('.numREP_' + post_id).show().text(' (' + data.num + ')');
			}

		}    

		if (op) {
			if (data.rest < 1) {
				button.remove();
			}else{
				button.text(button_default_text);
			}
		}

		phieubac_unbind();
	});
}
// End Status




// doccument for phieubac
$(document).ready(function() {
	/**
	$('[data-fancybox="images"]').fancybox();
	$('body').on("click", '.tload', function(e) {
		e.preventDefault();
		url = $(this).attr('href');
		loadPage(url, false);
	});

	var state = {name: location.href, page: document.title};
	history.pushState(state, document.title, location.href);

	$(window).on("popstate", function(){
		if(history.state){
			loadPage(history.state.name, true);
		}
	});
	function loadPage(link, popped){
		var originalUrl = link;
		$.ajax({
			url: originalUrl,
			cache: true,
			beforeSend: function() {
				startLoadingBar();
			},
			success: function(data) {
				var titlePart = data.split("<title>");
				titlePart = titlePart[1].split("</title>");
				var title = titlePart[0];
				if(popped != true){
					var state = {name: originalUrl, page: title};
					history.pushState(state, title, originalUrl);
				}
				$('#container').html($(data).find('#containerPage'));
				document.title = title;
				stopLoadingBar();
				targetElm = location.hash ? document.getElementById(location.hash.substr(1)) : null;
				if(targetElm){
					var wasID = targetElm.id;
					targetElm.id = "";
					setTimeout(function(){
						scrollElementIntoView(targetElm, null, function(){
							// targetElm.id = wasID;
						});
					}, 200);
				} else {
					if(popped != true){
						$('html, body').stop().animate({
							scrollTop: ($('html, body').offset().top)
						}, 500, 'easeInOutExpo');
					}
				}
			},
			error: function(xhr) {
				stopLoadingBar();
			}
		});
	}
	*/

	// Begin Status
	if ($('.cover-resize-wrapper').length) {
		$('.cover-resize-wrapper').height($('.cover-resize-wrapper').width()*0.39);
	}
	if ($('.cover-wrapper').length) {
		$('.cover-wrapper').height($('.cover-resize-wrapper').width()*0.39);
	}

	$('form.change-avatar-form').ajaxForm({
		url: '/request.php',
		beforeSend: function() {
		$('.avatar-progress-wrapper').text('0%' + 'Tải lên').fadeIn('fast').removeClass('hidden');
		$('.avatar-change-wrapper').addClass('hidden');
		},
		uploadProgress: function(event, position, total, percentComplete) {
			var percentVal = percentComplete+'%';
			$('.avatar-progress-wrapper').text(percentVal + ' Tải lên');
			if (percentComplete == 100) {
				setTimeout(function () {
					$('.avatar-progress-wrapper').text('Đang xử lý...');
					setTimeout(function () {
						$('.avatar-progress-wrapper').text('Xin chờ...');
					}, 2000);
				}, 500);
			}
		},
		success: function(responseText) {
			if (responseText.status) {
				$('.profile-avatar').attr('src', responseText.avatar_url + '?' + new Date().getTime());
				$('.avatar-progress-wrapper').fadeOut('fast').addClass('hidden').text('');
				$('.avatar-change-wrapper').removeClass('hidden');
			} else {
				$('.avatar-progress-wrapper').fadeOut('fast').addClass('hidden').text('');
				$('.avatar-change-wrapper').removeClass('hidden');
			}
		}
	});

	$('form.cover-form').ajaxForm({
		url: '/request.php',
		beforeSend: function() {
		$('.cover-progress')
			.css('line-height', $('.cover-resize-wrapper').height() + 'px')
			.text('0% Tải lên')
			.fadeIn('fast')
			.removeClass('hidden');
		},
		uploadProgress: function(event, position, total, percentComplete) {
			var percentVal = percentComplete+'%';
			$('.cover-progress').text(percentVal+' Tải lên');
			if (percentComplete == 100) {
				setTimeout(function () {
					$('.cover-progress').text('Đang xử lý...');
					setTimeout(function () {
						$('.cover-progress').text('Xin chờ...');
					}, 2000);
				}, 500);
			}
		},
		success: function(responseText) {
			if (responseText.status) {
				$('.profile-cover').attr('src', responseText.cover_url + '?' + new Date().getTime());
				$('.cover-progress').fadeOut('fast', function(){
					$(this).addClass('hidden').text('');
				});
			$('.profile-cover-resize').attr('src', responseText.actual_cover_url + '?' + new Date().getTime()).css('top', 0);
			} else {
				$('.cover-progress').fadeOut('fast', function(){
					$(this).addClass('hidden').text('');
				});
				$('.profile-cover-resize').css('top', 0);
			}
		}
	});

	$('form.cover-position-form').ajaxForm({
		url: '/request.php',
		beforeSend: function() {
		$('.cover-progress')
			.css('line-height', $('.cover-resize-wrapper').height() + 'px')
			.text('Đang định vị...').fadeIn('fast').removeClass('hidden');
		},
		success: function(responseText) {
			if (responseText.status) {
				$('.profile-cover').attr('src', responseText.url + '?' + new Date().getTime());
				$('.cover-progress').fadeOut('fast').addClass('hidden').text('');
				$('.cover-wrapper').show();
				$('.cover-resize-wrapper').hide().find('img').css('top', 0);
				$('.cover-resize-buttons').hide();
				$('input.cover-position').val(0);
				$('.profile-cover-resize').draggable('destroy').css('cursor','default');
			}
		}
	});

	$(document).on("submit", 'form#ajaxStatus', function(e) {
		e.preventDefault();
		var sttform  = $(this),
				sttValue = sttform.serialize(),
				button   = sttform.find('button.button'),
				button_default_text = button.find('span').text();

		$.ajax({
			type: 'POST',
			url: '/request.php',
			data: sttValue,
			cache: false,
			beforeSend: function() {
				fillStatus = false;
				button.attr('disabled', true);
				button.html(loading);
				$('.jserror').hide('slow');
			},
			success: function (data) {
				if (data.status) {
					loadStatus();
					fillStatus = true;
				} else {
					$('.jserror').text(data.error);
					$('.jserror').show('slow');
				}
				sttform.resetForm();
				button.removeAttr('disabled');
				button.html('<span>' + button_default_text + '</span>');
			}
		});
	});
	// End Status




	// Head && scroll to Top
	var mainHeader  = $('.cd-auto-hide-header');
	var menuHeader  = $('#cd-navigation');
	var scrolling  = false,
			previousTop  = 0,
			currentTop   = 0,
			scrollDelta  = 10,
			scrollOffset = 150;

	$('.nav-trigger').on('click', function(e){
		e.preventDefault();
		mainHeader.toggleClass('nav-open');
		menuHeader.slideToggle(450);
	});
	$(window).scroll(function(){
		// hide/show head
		if( !scrolling ) {
			scrolling = true;
			(!window.requestAnimationFrame)
				? setTimeout(autoHideHeader, 200)
				: requestAnimationFrame(autoHideHeader);
		}
		if($(window).scrollTop() > 0) {
			if(!$('.cd-auto-hide-header').hasClass('is-hidden') ){
				$('.navbar').addClass("shadow--2dp");
			}
		} else  {
			if($('.cd-auto-hide-header').hasClass('is-hidden') ){
				mainHeader.removeClass("is-hidden");
			}
			$('.navbar').removeClass("shadow--2dp");
		}

		// scroll to top
		if($(window).scrollTop() > 250) {
			$('.toTop').fadeIn(500);
		} else  {
			$('.toTop').fadeOut(500);
		}
	});
	$(window).resize(function () {
		if($(window).width() >= '1024') {
			if ($('#cd-navigation').css('display') != 'flex') {
				$('#cd-navigation').css({'display' : 'flex'});
			}
		} else {
			if ($('#cd-navigation').css('display') == 'flex') {
				mainHeader.removeClass('nav-open');
				$('#cd-navigation').css({'display' : 'none'});
			}
		}


		if ($('.cover-resize-wrapper').length) {
			$('.cover-resize-wrapper').height($('.cover-resize-wrapper').width() * 0.39);
		}
		if ($('.cover-wrapper').length) {
			$('.cover-wrapper').height($('.cover-resize-wrapper').width() * 0.39);
		}
		if ($('.profile-cover-resize').length) {
			$('.profile-cover-resize').css('top', 0);
		}
		if ($('.cover-progress').length) {
			$('.cover-progress').css('line-height', $('.cover-resize-wrapper').height() + 'px');
		}
		if ($('.screen-width').length) {
			$('.screen-width').val($('.cover-resize-wrapper').width());
		}

	});

	scrollBottom();
	if($('.phieubac-message').length) {
		$('#inner').scroll(function(){
			if($('#inner').scrollTop() == 0) {
				loadM();
			}
		});
	}

	// scroll to top
	$('.toTop').on('click', function(e) {
		e.preventDefault();
		$('html, body').stop().animate({
			scrollTop: ($('html, body').offset().top)
		}, 1750, 'easeInOutExpo');
	});

	/**
	* up load
	*/
	// avatar
	$(document).on("submit", 'form#ajaxAvatar', function(e) {
		e.preventDefault();
		var frm = $(this);
		var formData = new FormData(this);
		var button = $(this).find('button.button');
		var button_default_text = button.find('span').text();
		button.prepend(loading + '&#160;');
		button.attr('disabled', true);
		$.ajax({
			type: 'POST',
			url: '/request.php',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			xhr: function() {
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt) {
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						percentComplete = parseInt(percentComplete * 100);
						button.find('span').html(percentComplete + '%');
						if (percentComplete === 100) {
							button.find('span').html('Đang xử lý...');
						}
					}
				}, false);
				return xhr;
			},
			success: function (data) {
				$('.jserror').html(data);
				$('.jserror').show('slow');
				frm.resetForm();
				button.removeAttr('disabled');
				button.html('<span>' + button_default_text + '</span>');
			}
		});
	});

	// imgur
	$('.imgur form').ajaxForm({
		url: '/request.php',
		beforeSend: function() {
				main_chat = $('.imgur');
				button = main_chat.find('button.button');
				button_default_text = button.find('span').text();
				button.attr('disabled', true);
				button.prepend(loading + '&#160;');
				$('.jserror').hide('slow');
		},
		uploadProgress: function(event, position, total, percentComplete) {
			var percentVal = percentComplete+'%';
			button.find('span').html(percentVal);

			if (percentComplete == 100) {
					button.find('span').html('Đang xử lý...');
			}
		},
		success: function(resUp) {
				$('.jserror').html(resUp);
				$('.jserror').show('slow');
			$('.imgur form').resetForm();
				button.removeAttr('disabled');
				button.html('<span>' + button_default_text + '</span>');
		}
	});
	
	/**
	 * Form ajax
	*/
	$('.formjs form').ajaxForm({
		url: '/request.php',
		beforeSend: function() {
				main_js = $('.formjs');
				button = main_js.find('button.button');
				button_default_text = button.find('span').text();
				button.attr('disabled', true);
				button.prepend(loading + '&#160;');
				$('.jserror').hide('slow');
		},
		uploadProgress: function(event, position, total, percentComplete) {
			var percentVal = percentComplete+'%';
			button.find('span').html(percentVal);

			if (percentComplete == 100) {
					button.find('span').html('Đang xử lý...');
			}
		},
		success: function(data) {
				$('.jserror').html(data);
				$('.jserror').show('slow');
			$('.formjs form').resetForm();
				button.removeAttr('disabled');
				button.html('<span>' + button_default_text + '</span>');
		}
	});

	$(window).scroll(function() {
		guestbook = $('.phieubacChat').attr('data');
		if(guestbook == 1 && $('.phieubac-boxchat .list1').length >= 10) {
	
			if (loadingView == false) {
				checkonc = $('.phieubacChat').find('.button').attr('onclick');
				dOut = $(document).height() - $('#guestbook').height() - 50;
				if (checkonc && ($(window).scrollTop() >= $(document).height() - $(window).height() - dOut)) {
					loadingView = true;
					viewChatLoad();
				}
			}
		}
	});
	
	$(document).on("submit", 'form#ajaxChat', function(e) {
		e.preventDefault();
		var frm = $(this);
		var frmValue = frm.serialize();
		$.ajax({
			type: 'POST',
			url: '/request.php',
			data: frmValue,
			cache: false,
			beforeSend: function() {
				autoloadChatbox = false;
				button = frm.find('button.button');
				button_default_text = button.find('span').text();
				button.attr('disabled', true);
				button.html(loading);
				$('.jserror').hide('slow');
			},
			success: function (data) {
				if (data.status == 200) {
					reloadChat();
					autoloadChatbox = true;
				} else if (data.status == 300) {
					$('.jserror').html(data.html);
					$('.jserror').show('slow');
				}
				frm.resetForm();
				button.removeAttr('disabled');
				button.html('<span>' + button_default_text + '</span>');
			}
		});
	});
	$(document).on("submit", 'form#ajaxMessenger', function(e) {
		e.preventDefault();
		var frm = $(this);
		var frmValue = frm.serialize();
		$.ajax({
			type: 'POST',
			url: '/request.php',
			data: frmValue,
			cache: false,
			beforeSend: function() {
				autoloadMessenger = false;
				button = frm.find('button.button');
				button_default_text = button.find('span').text();
				button.attr('disabled', true);
				button.html(loading);
				$('.jserror').hide('slow');
			},
			success: function (data) {
				if (data.status == 200) {
					reloadMessenger();
					autoloadMessenger = true;
				} else if (data.status == 300) {
					$('.jserror').html(data.html);
					$('.jserror').show('slow');
				}
				frm.resetForm();
				button.removeAttr('disabled');
				button.html('<span>' + button_default_text + '</span>');
			}
		});
	});
	
	$('body').on("click", '#gchap', function() {
		var gcData = $(this).attr('data');
		var gcID = $(this).attr('data-id');
		$('.gchap' + gcData).attr('class', 'pagenav').attr('id', 'gchap');
		$(this).attr('class', 'gchap' + gcData + ' currentpage').attr('id', 'test');
		$('#gchap' + gcData).attr('src', 'https://drive.google.com/file/d/' + gcID + '/preview');
	});

	// stt like
	$('body').on("click", '.like_button', function() {
		var icon_name = $(this).find(".icon-newL").attr("class").replace(/icon\-newL\s+/gi, "");
		var location  = $(this).attr("id");
		var REL       = $(this).attr("rel");
		var MAP       = $(this).attr("map");
		var URL       = '/like_post.php';

		$(this).parent().hide();

		if (MAP == 'status') {

			/** status */
			var ID = location.split(/sttlike|sttlove|stthaha|stthihi|sttwoww|sttcry|sttangry|sttwtf/)[1],
					el_reactions = '#stt_reactions' + ID,
					el_reatext   = '#stt_reatext' + ID,
					el_this      = '#stt_e' + REL.toLowerCase() + ID,
					el_ulk       = '#stt_ulk' + ID,
					el_info      = '#stt_totalco' + ID,

					el_like      = '#sttlike'  + ID,
					el_love      = '#sttlove'  + ID,
					el_haha      = '#stthaha'  + ID,
					el_hihi      = '#stthihi'  + ID,
					el_woww      = '#sttwoww'  + ID,
					el_cry       = '#sttcry'   + ID,
					el_angry     = '#sttangry' + ID,
					el_wtf       = '#sttwtf'   + ID,

					el_elike     = '#stt_elike'  + ID,
					el_elove     = '#stt_elove'  + ID,
					el_ehaha     = '#stt_ehaha'  + ID,
					el_ehihi     = '#stt_ehihi'  + ID,
					el_ewoww     = '#stt_ewoww'  + ID,
					el_ecry      = '#stt_ecry'   + ID,
					el_eangry    = '#stt_eangry' + ID,
					el_ewtf      = '#stt_ewtf'   + ID;
		} else if (MAP == 'status_comment') {

			/** status cmt */
			var ID = location.split(/cmtlike|cmtlove|cmthaha|cmthihi|cmtwoww|cmtcry|cmtangry|cmtwtf/)[1],
					el_reactions = '#cmt_reactions' + ID,
					el_reatext   = '#cmt_reatext' + ID,
					el_this      = '#cmt_e' + REL.toLowerCase() + ID,
					el_ulk       = '#cmt_ulk' + ID,
					el_info      = '#cmt_totalco' + ID,

					el_like      = '#cmtlike'  + ID,
					el_love      = '#cmtlove'  + ID,
					el_haha      = '#cmthaha'  + ID,
					el_hihi      = '#cmthihi'  + ID,
					el_woww      = '#cmtwoww'  + ID,
					el_cry       = '#cmtcry'   + ID,
					el_angry     = '#cmtangry' + ID,
					el_wtf       = '#cmtwtf'   + ID,

					el_elike     = '#cmt_elike'  + ID,
					el_elove     = '#cmt_elove'  + ID,
					el_ehaha     = '#cmt_ehaha'  + ID,
					el_ehihi     = '#cmt_ehihi'  + ID,
					el_ewoww     = '#cmt_ewoww'  + ID,
					el_ecry      = '#cmt_ecry'   + ID,
					el_eangry    = '#cmt_eangry' + ID,
					el_ewtf      = '#cmt_ewtf'   + ID;
		} else if (MAP == 'status_reply') {

			/** status reply */
			var ID = location.split(/replike|replove|rephaha|rephihi|repwoww|repcry|repangry|repwtf/)[1],
					el_reactions = '#rep_reactions' + ID,
					el_reatext   = '#rep_reatext' + ID,
					el_this      = '#rep_e' + REL.toLowerCase() + ID,
					el_ulk       = '#rep_ulk' + ID,
					el_info      = '#rep_totalco' + ID,

					el_like      = '#replike'  + ID,
					el_love      = '#replove'  + ID,
					el_haha      = '#rephaha'  + ID,
					el_hihi      = '#rephihi'  + ID,
					el_woww      = '#repwoww'  + ID,
					el_cry       = '#repcry'   + ID,
					el_angry     = '#repangry' + ID,
					el_wtf       = '#repwtf'   + ID,

					el_elike     = '#rep_elike'  + ID,
					el_elove     = '#rep_elove'  + ID,
					el_ehaha     = '#rep_ehaha'  + ID,
					el_ehihi     = '#rep_ehihi'  + ID,
					el_ewoww     = '#rep_ewoww'  + ID,
					el_ecry      = '#rep_ecry'   + ID,
					el_eangry    = '#rep_eangry' + ID,
					el_ewtf      = '#rep_ewtf'   + ID;
		} else {

			/** forum */
			var ID = location.split(/like|love|haha|hihi|woww|cry|angry|wtf/)[1],
					el_reactions = '#reactions' + ID,
					el_reatext   = '#reatext' + ID,
					el_this      = '#e' + REL.toLowerCase() + ID,
					el_ulk       = '#ulk' + ID,
					el_info      = '#totalco' + ID,

					el_like      = '#like'  + ID,
					el_love      = '#love'  + ID,
					el_haha      = '#haha'  + ID,
					el_hihi      = '#hihi'  + ID,
					el_woww      = '#woww'  + ID,
					el_cry       = '#cry'   + ID,
					el_angry     = '#angry' + ID,
					el_wtf       = '#wtf'   + ID,

					el_elike     = '#elike'  + ID,
					el_elove     = '#elove'  + ID,
					el_ehaha     = '#ehaha'  + ID,
					el_ehihi     = '#ehihi'  + ID,
					el_ewoww     = '#ewoww'  + ID,
					el_ecry      = '#ecry'   + ID,
					el_eangry    = '#eangry' + ID,
					el_ewtf      = '#ewtf'   + ID;
		}

		var indata    = 'msg_id=' + ID + '&rel=' + REL + '&map=' + MAP;

		//$(this).closest(".new_like").find(".icon-lpn").removeClass().addClass("reactionTrans icon-lpn " + icon_name + "--18 reaction_grap-style");
		if ($(el_ulk).length) {
			$(el_ulk).fadeIn('slow').removeClass().addClass("reactionTrans icon-lpn " + icon_name + "--18 reaction_grap-style");
		}
		//$(el_ulk).removeClass('icon-' + too + '-new--18').addClass('icon-like-blf--18');

		$.ajax({
			type: "POST",
			url: URL,
			data: indata,
			cache: false,
			success: function(data) {
				if (in_array(REL, ['Like', 'Love', 'Haha', 'Hihi', 'Woww', 'Cry', 'Angry', 'WTF'])) {

					if ($(el_reactions).css('display') == 'none') {
						$(el_reactions).fadeIn('slow');
					}

					$('#' + location).attr('rel', 'Un' + REL);

					$(el_reatext).text(REL);
					$(el_this).fadeIn('slow');

					if (REL != 'Like') {
						if ($(el_like).attr('rel') == 'UnLike') {
							$(el_like).attr('rel', 'Like');
							if (data.count.Like <= 0) {
								$(el_elike).fadeOut('slow');
							}
						}
					}
					if (REL != 'Love') {
						if ($(el_love).attr('rel') == 'UnLove') {
							$(el_love).attr('rel', 'Love');
							if (data.count.Love <= 0) {
								$(el_elove).fadeOut('slow');
							}
						}
					}
					if (REL != 'Haha') {
						if ($(el_haha).attr('rel') == 'UnHaha') {
							$(el_haha).attr('rel', 'Haha');
							if (data.count.Haha <= 0) {
								$(el_ehaha).fadeOut('slow');
							}
						}
					}
					if (REL != 'Hihi') {
						if ($(el_hihi).attr('rel') == 'UnHihi') {
							$(el_hihi).attr('rel', 'Hihi');
							if (data.count.Hihi <= 0) {
								$(el_ehihi).fadeOut('slow');
							}
						}
					}
					if (REL != 'Woww') {
						if ($(el_woww).attr('rel') == 'UnWoww') {
							$(el_woww).attr('rel', 'Woww');
							if (data.count.Woww <= 0) {
								$(el_ewoww).fadeOut('slow');
							}
						}
					}
					if (REL != 'Cry') {
						if ($(el_cry).attr('rel') == 'UnCry') {
							$(el_cry).attr('rel', 'Cry');
							if (data.count.Cry <= 0) {
								$(el_ecry).fadeOut('slow');
							}
						}
					}
					if (REL != 'Angry') {
						if ($(el_angry).attr('rel') == 'UnAngry') {
							$(el_angry).attr('rel', 'Angry');
							if (data.count.Angry <= 0) {
								$(el_eangry).fadeOut('slow');
							}
						}
					}
					if (REL != 'WTF') {
						if ($(el_wtf).attr('rel') == 'UnWTF') {
							$(el_wtf).attr('rel', 'WTF');
							if (data.count.WTF <= 0) {
								 $(el_ewtf).fadeOut('slow');
							}
						}
					}

				} else if (in_array(REL, ['UnLike', 'UnLove', 'UnHaha', 'UnHihi', 'UnWoww', 'UnCry', 'UnAngry', 'UnWTF'])) {
					var str = REL.split("Un")[1];
					var too = str.toLowerCase();
					if (MAP == 'status') {
						el_this = '#stt_e' + too + ID;
					} else if (MAP == 'status_comment') {
						el_this = '#cmt_e' + too + ID;
					} else if (MAP == 'status_reply') {
						el_this = '#rep_e' + too + ID;
					} else {
						el_this = '#e' + too + ID;
					}

					if ($(el_ulk).length) {
						$(el_ulk).fadeOut().removeClass('icon-' + too + '-new--18').addClass('icon-like-blf--18');
					}
					$(el_reatext).text('Thích');
					if (data.count[str] <= 0) {
						$(el_this).fadeOut('slow');
					}

					var checkreact = false;

					var elike  = $(el_elike).css('display')  == 'none' ? true : false;
					var elove  = $(el_elove).css('display')  == 'none' ? true : false;
					var ehaha  = $(el_ehaha).css('display')  == 'none' ? true : false;
					var ehihi  = $(el_ehihi).css('display')  == 'none' ? true : false;
					var ewoww  = $(el_ewoww).css('display')  == 'none' ? true : false;
					var ecry   = $(el_ecry).css('display')   == 'none' ? true : false;
					var eangry = $(el_eangry).css('display') == 'none' ? true : false;
					var ewtf   = $(el_ewtf).css('display')   == 'none' ? true : false;

					if (REL == 'UnLike') {
						if (elove && ehaha && ehihi && ewoww && ecry && eangry && ewtf) {
							checkreact = true;
						}
					} else if (REL == 'UnLove') {
						if (elike && ehaha && ehihi && ewoww && ecry && eangry && ewtf) {
							checkreact = true;
						}
					} else if (REL == 'UnHaha') {
						if (elike && elove && ehihi && ewoww && ecry && eangry && ewtf) {
							checkreact = true;
						}
					} else if (REL == 'UnHihi') {
						if (elike && elove && ehaha && ewoww && ecry && eangry && ewtf) {
							checkreact = true;
						}
					} else if (REL == 'UnWoww') {
						if (elike && elove && ehaha && ehihi && ecry && eangry && ewtf) {
							checkreact = true;
						}
					} else if (REL == 'UnCry') {
						if (elike && elove && ehaha && ehihi && ewoww && eangry && ewtf) {
							checkreact = true;
						}
					} else if (REL == 'UnAngry') {
						if (elike && elove && ehaha && ehihi && ewoww && ecry && ewtf) {
							checkreact = true;
						}
					} else if (REL == 'UnWTF') {
						if (elike && elove && ehaha && ehihi && ewoww && ecry && eangry) {
							checkreact = true;
						}
					}

					if (checkreact) {
						if (data.count[str] <= 0) {
							$(el_reactions).fadeOut('slow');
						}
					}

					$('#' + location).attr('rel', str);

				}

				if (data.text){
					$(el_info).text(data.text);
				}else{
					$(el_info).text('');
				}
			}
		});
		return false;
	});


	// input file 
	var fileInputTextDiv = document.getElementById('file_input_text_div');
	var fileInput = document.getElementById('file_input_file');
	var fileInputText = document.getElementById('file_input_text');
	if (fileInputTextDiv) {
		fileInput.addEventListener('change', changeInputText);
		fileInput.addEventListener('change', changeState);

		function changeInputText() {
			var str = fileInput.value;
			var i;
			if (str.lastIndexOf('\\')) {
				i = str.lastIndexOf('\\') + 1;
			} else if (str.lastIndexOf('/')) {
				i = str.lastIndexOf('/') + 1;
			}
			fileInputText.value = str.slice(i, str.length);
		}

		function changeState() {
			if (fileInputText.value.length != 0) {
				if (!fileInputTextDiv.classList.contains("is-focused")) {
					fileInputTextDiv.classList.add('is-focused');
				}
			} else {
				if (fileInputTextDiv.classList.contains("is-focused")) {
					fileInputTextDiv.classList.remove('is-focused');
				}
			}
		}
	}

	if (isLogin) {
		setInterval(userRequests, 7000);
		setInterval(function () {
			if ( $('.ajax-time').length > 0) {
				$('.ajax-time').timeago()
					.removeClass('.ajax-time');
			}
		},
		10000);
	}


		/**
	* Select
	*/
	$('select').each(function(){
		var $this = $(this), numberOfOptions = $(this).children('option').length;
		$this.addClass('select-hidden'); 
		$this.wrap('<div class="select"></div>');
		$this.after('<div class="select-styled"></div>');
		var $styledSelect = $this.next('div.select-styled'),
				$selectedSelect = $this.children('option');
		$selectedSelect.each(function(){
			if ($(this).attr('selected')) {
				$styledSelect.text($(this).text());
			}
		});
		var $list = $('<ul />', {
			'class': 'select-options'
		}).insertAfter($styledSelect);
		for (var i = 0; i < numberOfOptions; i++) {
			$('<li />', {
				text: $this.children('option').eq(i).text(),
				rel: $this.children('option').eq(i).val()
			}).appendTo($list);
		}
		var $listItems = $list.children('li');
		$styledSelect.click(function(e) {
			e.stopPropagation();
			$('div.select-styled.active').not(this).each(function(){
				$(this).removeClass('active').next('ul.select-options').hide();
			});
			$(this).toggleClass('active').next('ul.select-options').toggle();
		});
		$listItems.click(function(e) {
			e.stopPropagation();
			$styledSelect.text($(this).text()).removeClass('active');
			$this.val($(this).attr('rel'));
			$list.hide();
		});
		$(document).click(function() {
			$styledSelect.removeClass('active');
			$list.hide();
		});
	});

	/**
	* Component
	*/
	$('.get-component').click(function(e){
		e.preventDefault();
		var components__pages = $('.components__pages').children(),
				components__parent  = $(this).parent(),
				components__list    = components__parent.children();
				sectionH = components__pages.height();

		if (!$(this).hasClass("is-active")) {
			components__list.removeClass('is-active');
			components__pages.removeClass('is-active');

			$(this).addClass('is-active');
			section__id = $(this).attr('get');
			section = $('#' + section__id);
			section.addClass('is-active');

			components__data   = new Object();
			components__data.t = 'components';
			components__data.a = section__id;

			if (section.attr('load') == 0) {
				section.attr('load', '1').css({'height':sectionH + 'px'});
				$.post('/request.php', components__data, function (data) {
					var list_post = '',
							pageData  = data.html;
					for (var i = 0; i < pageData.length; i++) {
						if (data.rate == 'gametop')
						{
							list_post += components_rate_gameTop(pageData[i]);
						} else if (data.rate == 'forum')
						{
							list_post += components_rate_forum(pageData[i]);
						}
					}
					listPage = '';
					if (data['page']['status']) {
						listPage = '<div class="list3 text-center">' + data['page']['data'] + '</div>';
					}
					section.html(list_post + listPage).removeAttr('style');
					phieubac_unbind();
				});
			}
		}
	});

	/**
	* Mini game
	*/

	var active1 = false;
	var active2 = false;
	var active3 = false;
	var active4 = false;

	$('#miniGame').click(function(e) {
		var bugg = $(this).attr('bugg'),
				buggt1 = '',
				buggt2 = '',
				buggt3 = '',
				buggt4 = '',
				offsetbugg = $(this).attr('offsetbugg');

		if (bugg == 0) {
			$(this).attr('bugg', '1');
		} else $(this).attr('bugg', '0');

		if (offsetbugg == 1){
			buggt1 = 'translate(0px,70px)';
			buggt2 = 'translate(40px,55px)';
			buggt3 = 'translate(64px,20px)';
			buggt4 = 'translate(60px,-24px)';
		} else if (offsetbugg == 2){
			buggt1 = 'translate(0px,70px)';
			buggt2 = 'translate(-40px,55px)';
			buggt3 = 'translate(-64px,20px)';
			buggt4 = 'translate(-60px,-24px)';
		} else if (offsetbugg == 3){
			buggt1 = 'translate(-60px,20px)';
			buggt2 = 'translate(-64px,-20px)';
			buggt3 = 'translate(-40px,-55px)';
			buggt4 = 'translate(0px,-70px)';
		} else if (offsetbugg == 4){
			buggt1 = 'translate(60px,20px)';
			buggt2 = 'translate(64px,-20px)';
			buggt3 = 'translate(40px,-55px)';
			buggt4 = 'translate(0px,-70px)';
		}

		if (!active1) $(this).find('.miniTaixiu').css({'transform': buggt1});
		else $(this).find('.miniTaixiu').css({'transform': 'none'}); 
		 if (!active2) $(this).find('.miniBaucua').css({'transform': buggt2});
		else $(this).find('.miniBaucua').css({'transform': 'none'});
			if (!active3) $(this).find('.test3').css({'transform': buggt3});
		else $(this).find('.test3').css({'transform': 'none'});
			if (!active4) $(this).find('.test4').css({'transform': buggt4});
		else $(this).find('.test4').css({'transform': 'none'});
		active1 = !active1;
		active2 = !active2;
		active3 = !active3;
		active4 = !active4;
	});

	$('#miniGame').draggable({
		scroll: false,
		addClasses: false,
		start: function( event, ui ) {
			var bugg = $(this).attr('bugg');
			if (bugg == 1) {
				openMinigame($(this));
			}
		},
		drag: function( event, ui ) {
			if($(this).hasClass('transition')){
				$(this).removeClass('transition');
			}
			ui.position.left = Math.min( $(window).width() - $(this).width() - 15, ui.position.left );
			ui.position.top  = Math.min( $(window).height()- $(this).height() - 20, ui.position.top );
			if (ui.position.left < 15) {
				ui.position.left = 15;
			}
			if (ui.position.top < 20) {
				ui.position.top = 20;
			}

		},
		stop: function( event, ui ) {
			var dragW = Math.floor($(window).width() / 2),
					dragH = Math.floor($(window).height() - 200),
					toW   = $(window).width() - $(this).width() - 15;

			if(!$(this).hasClass('transition')){
				$(this).addClass('transition');
			}
			if (ui.position.left >= dragW) {
				$(this).css({'left' : toW + 'px'});
				if (ui.position.top > dragH) {
					$(this).attr('offsetbugg', '3');
				} else $(this).attr('offsetbugg', '2');
			} else if (ui.position.left < dragW) {
				$(this).css({'left' : '15px'});
				if (ui.position.top > dragH) {
					$(this).attr('offsetbugg', '4');
				} else $(this).attr('offsetbugg', '1');
			}
		}
	});
	function openMinigame(e){
		var bugg = e.attr('bugg'),
				buggt1 = '',
				buggt2 = '',
				buggt3 = '',
				buggt4 = '',
				offsetbugg = e.attr('offsetbugg');

		if (bugg == 0) {
			e.attr('bugg', '1');
		} else e.attr('bugg', '0');

		if (offsetbugg == 1){
			buggt1 = 'translate(0px,70px)';
			buggt2 = 'translate(40px,55px)';
			buggt3 = 'translate(64px,20px)';
			buggt4 = 'translate(60px,-24px)';
		} else if (offsetbugg == 2){
			buggt1 = 'translate(0px,70px)';
			buggt2 = 'translate(-40px,55px)';
			buggt3 = 'translate(-64px,20px)';
			buggt4 = 'translate(-60px,-24px)';
		} else if (offsetbugg == 3){
			buggt1 = 'translate(-60px,20px)';
			buggt2 = 'translate(-64px,-20px)';
			buggt3 = 'translate(-40px,-55px)';
			buggt4 = 'translate(0px,-70px)';
		} else if (offsetbugg == 4){
			buggt1 = 'translate(60px,20px)';
			buggt2 = 'translate(64px,-20px)';
			buggt3 = 'translate(40px,-55px)';
			buggt4 = 'translate(0px,-70px)';
		}

		if (!active1) e.find('.miniTaixiu').css({'transform': buggt1});
		else e.find('.miniTaixiu').css({'transform': 'none'}); 
		 if (!active2) e.find('.miniBaucua').css({'transform': buggt2});
		else e.find('.miniBaucua').css({'transform': 'none'});
			if (!active3) e.find('.test3').css({'transform': buggt3});
		else e.find('.test3').css({'transform': 'none'});
			if (!active4) e.find('.test4').css({'transform': buggt4});
		else e.find('.test4').css({'transform': 'none'});
		active1 = !active1;
		active2 = !active2;
		active3 = !active3;
		active4 = !active4;
	}

	// Game Tài Xỉu
	$('.miniTaixiu').on('click', function(e) {
		e.preventDefault();
		if (!checkMobile) {
			var clicktaixiumini = $('.TaiXiu-miniGame').css('display');
			if (clicktaixiumini == 'none') {
				$('.TaiXiu-miniGame').show().css({'display' : 'block', 'left' : $(window).width()/2 - 250 + 'px', 'top' : $(window).height()/2 - $('.TaiXiu-miniGame').height()/2 + 'px'});
			} else $('.TaiXiu-miniGame').hide();
		} else {
			window.location.href = $(this).attr("href");
		}
	});

	$('.TaiXiu-miniGame').draggable({
		scroll: false,
		addClasses: false,
		drag: function( event, ui ) {
			ui.position.left = Math.min($(window).width() - $(this).width()/2, ui.position.left);
			ui.position.top  = Math.min($(window).height()- $(this).height()/2, ui.position.top);
			if (ui.position.left < -$(this).width()/2) {
				ui.position.left = -$(this).width()/2;
			}
			if (ui.position.top < -$(this).height()/2) {
				ui.position.top = -$(this).height()/2;
			}
		}
	});

	// Game Bầu cua
	$('.miniBaucua').on('click', function(e) {
		e.preventDefault();
		if (!checkMobile) {
			var clicktaixiumini = $('.baucua-miniGame').css('display');
			if (clicktaixiumini == 'none') {
				$('.baucua-miniGame').show().css({'display' : 'block', 'left' : $(window).width()/2 - 250 + 'px', 'top' : $(window).height()/2 - $('.baucua-miniGame').height()/2 + 'px'});
			} else $('.baucua-miniGame').hide();
		} else {
			window.location.href = $(this).attr("href");
		}
	});

	$('.baucua-miniGame').draggable({
		scroll: false,
		addClasses: false,
		drag: function( event, ui ) {
			ui.position.left = Math.min($(window).width() - $(this).width()/2, ui.position.left);
			ui.position.top  = Math.min($(window).height()- $(this).height()/2, ui.position.top);
			if (ui.position.left < -$(this).width()/2) {
				ui.position.left = -$(this).width()/2;
			}
			if (ui.position.top < -$(this).height()/2) {
				ui.position.top = -$(this).height()/2;
			}
		}
	});


/**
	$('body').on('click', '#miniGame', function(e) {
		var miniGame = $(this).parent();
		openMinigame(miniGame);
	});


		$('body').on('click', '.mask2', function(e) {
		var miniGame = $(this).parent();
		openMinigame(miniGame);
	});
	*/

	//$(".tin-nhan-moi").draggable();

	/**
	* Enables "smooth scrolling to page anchor" for page <a> links.
	*/
	$('a[href*=#]').click(function(e){
		var urlTester = document.createElement("a");
		urlTester.href = this.href;
		urlTester.hash = location.hash;
		var targetElement = document.getElementById(this.hash.substr(1));
		if(urlTester.href == location.href && targetElement){
			e.preventDefault();
			var wasID = targetElement.id;
			targetElement.id = "";
			scrollElementIntoView(targetElement, null, function(){
				targetElement.id = wasID;
			});
		}
	});

	var targetElm = location.hash ? document.getElementById(location.hash.substr(1)) : null;
	if(targetElm){
		var wasID = targetElm.id;
		targetElm.id = "";
		$(window).load(function(){
			setTimeout(function(){
				scrollElementIntoView(targetElm, null, function(){
					targetElm.id = wasID;
				});
			}, 300);
		});
	}


	/** Functions Phieubac*/
	// request
	function userRequests (){
		requests_data = new Object();
		requests_data.t = 'app';
		requests_data.a = 'requests';

		if(base_boxchat.length)
		{ // check chat box
			requests_data.chatbox = 1;
			admin = $('#admin');
			if(admin.length) {
				requests_data.admin = '1';
			} else {
				requests_data.admin = '0';
			}
			if ($('.phieubac-boxchat .list1').length > 0) {
				requests_data.before_id = $('.phieubac-boxchat .list1:first').attr('data-id');
			}
		}

		if(base_messenger.length)
		{ // check messenger
			requests_data.messenger = 1;
			requests_data.messuid   = $('.in-messenger #content').attr('data-yid');
			if ($('.content-messenger .message-wrapper').length > 0) {
				requests_data.messbefore = $('.content-messenger .message-wrapper').last().attr('mes-id');
			}
		}

		if(base_taixiu.length)
			{ // check game taixiu
			requests_data.taixiu = 1;
		}
		if(base_baucua.length)
			{ // check game baucua
			requests_data.baucua = 1;
		}

		$.post('/request.php', requests_data, function(data){
			console.log(data);
			if (data.status) {
				/** Game */
				if (data.game.status) {
					// tai xiu
					if ($('#adSetGame').length) {
						if (data.game.listGame.taixiuSet.status) {
							$('#adSetGame').css({'color' : '#009688'}).text(data.game.listGame.taixiuSet.s1 + ' - ' + data.game.listGame.taixiuSet.s2 + ' - ' + data.game.listGame.taixiuSet.s3);
						} else $('#adSetGame').css({'color' : 'red'}).text('Ngẫu nhiên.');

					}
					if(base_taixiu.length) {
						var taixiu = data.game.listGame.taixiu;
						if (taixiu.cuoctai != "0") {
							$.TaiXiu.openload.uset(1,1);
						} else if(taixiu.cuocxiu != "0") {
							$.TaiXiu.openload.uset(1,2);
						}
						$.TaiXiu.settings.balans = taixiu.balans;
						$('.mebet').text(taixiu.tien + ' VNĐ');
						$('#stai').text(taixiu.tai);
						$('#sxiu').text(taixiu.xiu);
						$('#ibetstai').text(taixiu.cuoctai);
						$('#ibetsxiu').text(taixiu.cuocxiu);
						$('input#betsxiu').attr('max', taixiu.balans);
						$('input#betstai').attr('max', taixiu.balans);
					}
				}
				// Game Bầu cua
				if(base_baucua.length) {
					var baucua = data.game.listGame.baucua;
					$.miniGame.baucua.openload.sttUpdate(baucua);
					$('#baucua_input').attr('max', data.users.balans);
				}

				/** Messenger */
				if (data.messenger.status) {
					var dMess = data.messenger.data;
					for (var i = 0; i < dMess.length; i++) {
						var _thisr = dMess[i];
						messenger_time = '<div class="time-wrapper">' + (_thisr['timestamp'] != 0 ? '<span class="ajax-time" title="' + _thisr['timestamp'] + '">' + _thisr['time'] + '</span>' : _thisr['time']) + '</div>';
						var testMrT98 = $('#content .message-group').last();
						if (testMrT98.hasClass('message-group-me') && _thisr.avatar == '0')
						{
							testMrT98.append("\r" + '<div class="message-wrapper me" mes-id="' + _thisr.id + '">' + "\r" + '<div class="text-wrapper">' + "\r" + _thisr.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r");
						} else if (testMrT98.hasClass('message-group-them') && _thisr.avatar != '0')
						{
							testMrT98.append("\r" + '<div class="message-wrapper them" mes-id="' + _thisr.id + '">' + "\r" + '<div class="circle-wrapper animated bounceIn" style="background-image: url(' + _thisr.avatar + '); background-size: 40px 40px;"></div>' + "\r" + '<div class="text-wrapper">' + "\r" + _thisr.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r");
						} else if (testMrT98.hasClass('message-group-me') && _thisr.avatar != '0') {
							$('.content-messenger').append("\r" + '<div class="message-group message-group-them">' + "\r" + '<div class="message-wrapper them" mes-id="' + _thisr.id + '">' + "\r" + '<div class="circle-wrapper animated bounceIn" style="background-image: url(' + _thisr.avatar + '); background-size: 40px 40px;"></div>' + "\r" + '<div class="text-wrapper">' + "\r" +  _thisr.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r" + '</div>' + "\r");
						} else {
							$('.content-messenger').append("\r" + '<div class="message-group message-group-me">' + "\r" + '<div class="message-wrapper me" mes-id="' + _thisr.id + '">' + "\r" + '<div class="text-wrapper">' + "\r" + _thisr.text + "\r" + '</div>' + "\r" + messenger_time + "\r" + '</div>' + "\r" + '</div>' + "\r");
						}
					}

					scrollBottom();
				}
				var messengerArray = $('.message-wrapper');
				var danhsachMessenger = new Array();
				var idMessenger = null;
				messengerArray.each(function(){
					idMessenger = $(this).attr('mes-id');
					if (danhsachMessenger[idMessenger] == true) {
						$(this).remove();
					} else {
						danhsachMessenger[idMessenger] = true;
					}
				});
				/** End Messenger */

				/** Chat box */
				if (data.chatbox.chatbox) {
					var boxchat_list = '',
							boxchat_data  = data.chatbox.chatbox_list;

					for (var i = 0; i < boxchat_data.length; i++) {
						var chatbox_m = boxchat_data[i];
						boxchat_list += '<div id="chat' + chatbox_m['id'] + '" class="list1" data-id="' + chatbox_m['id'] + '">';
						boxchat_list += '<div class="fauthor">';
						boxchat_list += '<img src="' + chatbox_m['avatar'] + '" class="thumb" alt="' + chatbox_m['name'] + '" /><ul><li>';

						/** Link user */
						boxchat_list += (chatbox_m['link'] ? '<a href="/profile/?user=' + chatbox_m['user_id'] + '" class="tload">' : '') + '<strong class="' + (chatbox_m['rights'] == 9 ? 'nickadmin' : 'nickname') + '">' + chatbox_m['name'] + '</strong>' + (chatbox_m['link'] ? '</a>' : '');


						/** Time */
						boxchat_list += '</li><li><span class="text--italic gray fsize--12">' + (chatbox_m['timestamp'] != 0 ? '<span class="ajax-time" title="' + chatbox_m['timestamp'] + '">' + chatbox_m['time'] + '</span>' : chatbox_m['time']) + '</span></li></ul>';

						/** Admin panel */
						boxchat_list += (chatbox_m['panel'] ? '<span class="chatmore"><a class="tload" href="/guestbook/index.php?act=otvet&amp;id=' + chatbox_m.id + '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE15E;</i></a>' + (chatbox_m['panel_more'] ? ' <a class="tload" href="/guestbook/index.php?act=edit&amp;id=' + chatbox_m.id + '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE254;</i></a> <a class="tload" href="/guestbook/index.php?act=delpost&amp;id=' + chatbox_m.id + '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE872;</i></a>' : '') + '</span>' : '');

						/** Post */
						boxchat_list += '</div>' + chatbox_m['text'] + (chatbox_m['reply']['time'] != 0 ? '<div class="reply"><strong class="nickadmin">' + chatbox_m['reply']['name'] + '</strong> <span class="fsize--11-5">' + chatbox_m['reply']['time'] + '</span><br>' + chatbox_m['reply']['text'] + '</div>' : '') + '</div>';
					}
					$('.phieubac-boxchat').prepend(boxchat_list);
				}

				chatArray = $('.phieubac-boxchat .list1');
				danhsach = new Array();
				for (var i = 1, lengthChat = chatArray.length; i <= lengthChat; i++) {
					idChat = $('.phieubac-boxchat .list1:nth-child(' + i + ')').attr('data-id');
					if(danhsach[idChat] == true) {
						$('.phieubac-boxchat .list1:nth-child(' + i + ')').remove();
					}else{
						danhsach[idChat] = true;
					}
				}
				/** End Chat box */

				/** Notice */
				// thông báo mới
				if(data.notice.notice != "0"){
					$('#so-thong-bao-moi').attr('data-badge', data.notice.notice);
					notice_other.fadeIn(500);
				} else {
					notice_other.fadeOut(500);
				}

				// tin nhắn mới
				if(data.notice.notice_messenger != "0"){
					$('#notice').attr('data-badge', data.notice.notice_messenger);
					$('#notice-img').css({'background-image':'url(' + data.notice.data_messenger + ')'});
					notice_message.fadeIn(500);
				} else {
					notice_message.fadeOut(500);
				}

				// ipay
				if (data.ipay) {
					$('#thanhtoanmoi').attr('data-badge', data.ipay);
					notice_ipay.fadeIn(500);
				}  else {
					notice_ipay.fadeOut(500);
				}

				// thành viên online
				var list_online = '',
						onlines     = data.notice.data_online;

				for (var i = 0; i < onlines.length; i++) {
					var fonline = onlines[i];

					list_online += (!fonline['my'] ? '<a href="/profile/?user=' + fonline['id'] + '">' : '') + '<span class="' + (fonline['rights'] == 9 ? 'nickadmin' : '') + (fonline['ban'] ? ' text--through' : '') + '">' + fonline.name + '</span>' + (!fonline['my'] ? '</a>' : '');

					if (i < onlines.length - 1) {  
						list_online += ', ';
					}
				}
				base_online.html(list_online);
				/** end Notice */
			}
		});
	}

	function autoHideHeader() {
		var currentTop = $(window).scrollTop();
		checkSimpleNavigation(currentTop);
		previousTop = currentTop;
		scrolling = false;
	}

	function checkSimpleNavigation(currentTop) {
		if (previousTop - currentTop > scrollDelta) {
			mainHeader.removeClass('is-hidden');
		} else if( currentTop - previousTop > scrollDelta && currentTop > scrollOffset) {
			mainHeader.addClass('is-hidden');
			mainHeader.removeClass('shadow--2dp');
			if ($(window).width() < '1024') {
				if ($('#cd-navigation').css('display') != "none") {
					mainHeader.removeClass('nav-open');
					menuHeader.slideUp();
				}
			}
		}
	}

	function startLoadingBar(){
		if ($(window).width() < '1024') {
			mainHeader.removeClass('nav-open');
			menuHeader.slideUp();
		}
		$(".page-loading-bar").show().width(50+30*Math.random()+"%");
	}
	function stopLoadingBar(){
		$(".page-loading-bar").width("101%").delay(200).fadeOut(400, function(){
			$(this).width("0");
		});
	}
});
