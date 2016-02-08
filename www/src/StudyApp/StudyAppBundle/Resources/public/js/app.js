(
        function() {
		var WEB_ROOT = '',
			paginationCreated = false;
		$(init);
		
		function init() {
			initCommentList();
			initFbLoginPage();
		}
		function initFbLoginPage() {
			var s = window.location.hash,
				varname = '#access_token=',
				hash = s.substring( s.indexOf(varname) + varname.length, s.indexOf('&') );
			if(!hash) {
				return;
			}
			_post({'access_token':hash}, function(data) {
				setTimeout(
					function () {
						window.location.href = data.redirectUrl;
					}, 1000
				);
			}, window.location.href);
		}
		function initCommentList() {
			function _clearForm() {
				if (document.forms.commentForm) {
					document.forms.commentForm.reset();
				}
				$('#parent_id').val(0);
				$('#comment_id').val(null);
			}
			function _lockForm() {
				$('#body').prop('disabled', true);
				$('#addComment').prop('disabled', true);
			}
			function _unlockForm() {
				$('#body').prop('disabled', false);
				$('#addComment').prop('disabled', false);
			}
			_clearForm();
			_unlockForm();
			//add comment
			$('#menu-add-comment').click(_onAdd);
			function _onAdd(evt) {
				$('#comment_id').val(0);
				$('#parent_id').val(0);
				$('#body').val('');
				$('#body')[0].focus();
				return false;
			}
			//edit comment
			$('.j-edit-comment').click(_onEdit);
			function _onEdit(evt) {
				var n = evt.target;
				if (n.tagName == 'SPAN') {
					n = n.parentNode;
				}
				_get(
					function(data) {
						_clearForm();
						_map(data);
						$('#comment_id').val(data.id);
						$('#body')[0].focus();
					},
					WEB_ROOT + '/comments/' + n.getAttribute('data-id')
				);
				return false;
			}
			//reply commment
			$('.j-reply-comment').click(_onReplyTo);
			function _onReplyTo() {
				var obj = this;
				if ($('#body')[0]) {
					_clearForm();
					$('#parent_id').val($(obj).data('id'));
					$('#body')[0].focus();
				}
				return false;
			}
			//save comment
			window.requestSended = 0;
			function _renderComment(data, place){
				place = place ? place : 'Before';
				var oTpl = $('.commentListWide .j-template').first(), 
					tpl = oTpl.html(), i, s = tpl, nLi;
				for (i in data) {
					s = s.replace( new RegExp("\\{" + i + "\\}", "mg"), data[i] );
				}
				nLi = $( '<li data-id="' + data.id + '">' + s + '</li>');
				nLi.find('.j-edit-comment').click(_onEdit);
				nLi.find('.j-reply-comment').click(_onReplyTo);
				nLi.find('.j-load-childs').click(_onLoadChild);
				if (parseInt(data.parent_id)) {
					var ul = $('.commentListWide').find('li[data-id=' + data.parent_id + '] ul.j-children').first();
					//console.log(ul);
					ul.prepend(nLi);
					if (int(data.count_child)) {
						nLi.find('.j-count_child').text(data.count_child);
						nLi.find('.j-answer-info').removeClass('hide');
					}
					nLi.hide();
					nLi.show(400);
				} else {
					if (int(data.count_child)) {
						nLi.find('.j-count_child').text(data.count_child);
						nLi.find('.j-answer-info').removeClass('hide');
					}
					if (place == 'Before') {
						nLi.insertBefore( oTpl );
					} else {
						var ul = $('.commentListWide').first();
						ul.prepend(nLi);
					}
					nLi.hide();
					nLi.show(400);
				}
			}
			function _onSave(data) {
				requestSended = 0;
				_unlockForm();
				if (data.errors) {
					$('#errorsarea').html( data.errors.join('<br>') ).removeClass('hide');
					return;
				}
				$('#errorsarea').html('').removeClass('hide').addClass('hide');
				_renderComment(data, 'After');
				_clearForm();
			}
			function _onUpdate(data) {
				requestSended = 0;
				_unlockForm();
				if (data.errors) {
					$('#errorsarea').html( data.errors.join('<br>') ).removeClass('hide');
					return;
				}
				$('li[data-id=' + data.id + '] .j-body').first().text(data.body);
				_clearForm();
			}
			if (document.forms.commentForm) {
				document.forms.commentForm.onsubmit = _onSubmit;
			}
			function _onSubmit() {
				if (requestSended) {
					return false;
				}
				var data = {
							'comment_type': {
								'_token': $('#comment_type__token').val(),
								'id': $('#comment_id').val(),
								'body': $('#body').val(),
								'parent_id': $('#parent_id').val()
							 }
							};
				requestSended = 1;
				_lockForm();
				if (parseInt(data.comment_type.id)) {
					_patch(data, _onUpdate, WEB_ROOT + '/comments');
				} else {
					_post(data, _onSave, WEB_ROOT + '/comments');
				}
				return false;
			}
			//load childs
			$('.j-load-childs').click(_onLoadChild);
			function _onLoadChild(evt) {
				var n = evt.target, span;
				if (n.tagName == 'SPAN') {
					span = $(n);
					n = n.parentNode;
				} else {
					span = $(n).find('span').first();
				}
				_get(
					function(data) {
						for (var i in data.list) {
							_renderComment(data.list[i]);
						}
					},
					WEB_ROOT + '/comments/' +  + n.getAttribute('data-id') + '/firstchilds'
				);
				$(n).unbind('click', _onLoadChild);
				$(n).bind('click', _onHideChild);
				span.removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
				return false;
			}
			function _onHideChild(evt) {
				var n = evt.target, span;
				if (n.tagName == 'SPAN') {
					span = $(n);
					n = n.parentNode;
				} else {
					span = $(n).find('span').first();
				}
				//TODO hide and clear
				var childsUl = span.parents('li').first().find('ul');
				childsUl.hide(400);
				setTimeout(
					function() {
						childsUl.html('');
						childsUl[0].style = '';
					}, 410);
				
				$(n).unbind('click', _onHideChild);
				$(n).bind('click', _onLoadChild);
				span.removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
				return false;
			}
			//pagination
			_setPagination();
			function _setPagination(ajax) {
				if (window.totalPages > 1 && !paginationCreated) {
					var currentPage = parseInt(window.location.href.replace(/.+\/comments\/page\/([0-9]+)/, '$1'));
					currentPage = currentPage ? currentPage : 1;
					$('#commentPaging').bootstrapPaginator({
						currentPage: currentPage,
						totalPages: window.totalPages,
						onPageClicked: function(e, originalEvent, type, page){
							if (!ajax) {
								if (page == 1) {
									window.location.href = WEB_ROOT + '/comments';
								} else {
									window.location.href = WEB_ROOT + '/comments/page/' + page;
								}
							} else {
								_loadComments(page);
							}
						}
					});
					paginationCreated = true;
				}
			}
			//load comments for  main page
			function _loadComments(page) {
				if (!page) {
					page = '1';
				}
				_get(
					function(data) {
						$('.commentListWide li').each(
							function(i, item) {
								item = $(item);
								if (!item.hasClass('j-template')) {
									item.remove();
								}
							}
						);
						for (var i in data.comments) {
							_renderComment(data.comments[i]);
						}
						if (data.pages_total > 1) {
							window.totalPages = data.pages_total;
							_setPagination(true);
						}
					},
					WEB_ROOT + '/comments/page/' + page + '.json',
					function(){
						;
					}
				);
			}
			var hUrl = window.location.href.split('?')[0],
				url = hUrl.split('#')[0],
				host = url.replace(new RegExp('https?://([^/]+).*'), '$1'),
				path =  url.replace(/\/$/, '').replace(new RegExp('https?://' + host), '');
			if (path == WEB_ROOT) {
				_loadComments();
			}
		}
		
		//ajax helpers
		function  _map(data) {
			var $obj, obj, i;
			for (i in data) {
				$obj = $('#' + i);
				obj = $obj[0];
				if (obj) {
					if (obj.tagName == 'INPUT' || obj.tagName == 'TEXTAREA') {
						$obj.val(data[i]);
					} else {
						$obj.text(data[i]);
					}
				}
			}
		}
		function _get(onSuccess, url, onFail) {
			_restreq('get', {}, onSuccess, url, onFail)
		}
		function _delete(onSuccess, url, onFail) {
			_restreq('post', {}, onSuccess, url, onFail)
		}
		function _post(data, onSuccess, url, onFail) {
			_restreq('post', data, onSuccess, url, onFail)
		}
		function _patch(data, onSuccess, url, onFail) {
			_restreq('patch', data, onSuccess, url, onFail)
		}
		function _put(data, onSuccess, url, onFail) {
			_restreq('put', data, onSuccess, url, onFail)
		}
		function _restreq(method, data, onSuccess, url, onFail) {
			if (!url) {
				url = window.location.href;
			}
			if (!onFail) {
				onFail = defaultFail;
			}
			data.xhr = 1;
			switch (method) {
				case 'put':
				case 'patch':
				case 'delete':
					break;
			}
			$.ajax({
				method: method,
				data:data,
				url:url,
				dataType:'json',
				success:onSuccess,
				error:onFail
			});
		}
		
		function defaultFail(data) {
			window.requestSended = 0;
			alert('Произошла какая-то ошибка при выполнении запроса');
		}
		function int(n){
			return parseInt(n);
		}
	}
)()
