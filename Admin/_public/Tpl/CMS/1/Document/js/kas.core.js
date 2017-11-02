
Core = {
	ajaxPath : window.location.href.replace(/\/admin[^ ]+/, '/admin/ajax/')
};

Core.FileManager = 
{	
	conteiner: 'section.kas-file-manager-content',
	path: 'input[data-path]',
	
	getContent: function(fn, dst) 
	{
		var ob 			= {};
			ob.type 	= 'KAS_FILE_MANAGER',
			ob.action 	= 'get';
			ob.path		= dst || '';
			
		$.post(Core.ajaxPath, ob)
			.done(function(data){
				return fn(data);
			});
	},
	
	run: function() 
	{
		Core.FileManager.getContent(function(data){
			console.log(data);
		});
	},
};
Core.terminal = 
{ 
	run: function() 
	{
		var term = 'section.terminal';
		Core.onAltKey(false, 67, function()
		{
			switch($(term + ':visible').length == 0) 
			{				
				case true:
					$(term).show();
					$(term).find('textarea').focus();
					return;
				break;
				
				
				case false:
					$(term).hide();
					$(term).find('textarea').val('');
					return;
				break;				
			}
			
			return;
			
		});
		
		Core.onEnter(function(e)
		{
			var cmd, ob;
				cmd = $.trim($(e).val());
				
			ob = {
				type: 'terminal'
			};
			
			if (cmd.length < 2) {
				return;
			}
			
			ob.command = cmd;
			
			return Core.terminal.req(ob);
			
			
		}, term + ' textarea');
		
	},
	  
	req: function(ob) 
	{
		$.post(Core.ajaxPath, ob)
			.done(function(data) 
			{	
				var resp = Core.json(data);
				console.log(data);
				if (typeof resp !== 'object') {
					return;
				}
				
				
				resp.html 	? $('body').append(resp.html) : false;
				resp.terminal ? $('section.terminal textarea')
					.val($.trim($('section.terminal textarea').val()) + "\n" + $.trim(resp.terminal)) : false;
				
				/*OnContinue*/				
				if (typeof resp.cont !== 'undefined') {
					return Core.terminal.req(ob);
				}
				
			});
	},
};

Core.modal = 
{
	txt: {
		0 : {t : 'Внимание!', d: 'Нет выбранных элементов для выполнения данного действия.'},
		1 : {t : 'Внимание!', d: 'Создан новый процесс для выполнения'},
		2 : {t : 'Внимание!', d: 'Вы действительно хотите выполнить данный процесс ?'},
		3 : {t : 'Внимание!', d: 'Чтобы убрать синхронизацию необходимо выбрать группу или элемент'},
		4 : {t : 'Внимание!', d: 'Вы действительно хотите убрать синхронизацию с данных элементов'},
	}
};
Core.modal.html = function(ob) {
	var html = '<section class="m-wrap transition"><section class="modal" data-type="'+ob.type+'"><span class="m-title">'+ob.t+'</span><span class="m-desc">'+ob.d+'</span><div class="btn-set"><button data-action="true" data-btn="true">Да</button><button data-action="false" data-btn="false">Нет</button></div></section></section>';
	return html;
};
Core.modal.htmlInfo = function(ob) {
	var html = '<section class="m-wrap transition"><section class="modal" data-type="'+ob.type+'"><span class="m-title">'+ob.t+'</span><span class="m-desc">'+ob.d+'</span><div class="btn-set"><button data-action="true" data-btn="true">Да</button></div></section></section>';
	return html;
};

Core.modal.close = function() {
	$('section.modal').closest('section.m-wrap').remove();	
	return;
};

Core.modal.inf = function(ob, onTrue) 
{
	typeof ob === 'number' ?
		ob = Core.modal.txt[ob] : false;
		
	if (typeof ob != 'object') {
		ob = {t: 'Внимание!', d: 'Данные были изменены. Для продолжения нажмите "Продолжить"'};
	}
	
	ob.type = 'inf';
	
	Core.modal.close();
	$('body').append(Core.modal.htmlInfo(ob));	
	
	$('section.modal').find('button[data-action="true"]')
		.on('click', function()
		{			
			typeof onTrue  === 'function' ?
				onTrue() : false;
				
			Core.modal.close();
			return;
		});
		
	return;
	
};

Core.modal.e = function() {
	Core.modal.inf({t: 'Внимание!',
		d: 'Произошла системная ошибка. Для решения данной проблемы обратитесь в службу технической поддержки.'});
};

Core.modal.confirm = function(selector, onTrue, onFalse) 
{
	
	var ob, dOb = {
		t: 'Внимание!', d: 'Вы действительно хотите удалить выбранные объекты ?'
	};
	
	
	typeof selector == 'number' ?
		selector = Core.modal.txt[selector] : false;		
	
	
	if (typeof selector !== 'object') {
		return;
	}
	
	ob = {
		t: selector.t || $(selector).attr('data-title') || dOb.t, 
		d: selector.d || $(selector).attr('data-desc')	|| dOb.d
	};
	
	ob.type = 'del';	
	
	/*Close MD*/
	Core.modal.close();
	
	$('body').append(Core.modal.html(ob));	
	
	Core.onEnter(onTrue);
	
	typeof onTrue  === 'function' 
		? $('section.modal').find('button[data-action="true"]')
			.on('click', function(){
				return onTrue(selector);
		}) :
		false;
	
	typeof onFalse  === 'function' 
		? $('section.modal').find('button[data-action="false"]')
			.on('click', function(){
				return onFalse(selector);
		}) :
		false;
	
	return;	
};

Core.tChecked = function(e) 
{
	var $dc = $(e).filter("[data-clicked]");
	
	switch (typeof $(e).attr('checked') !== 'undefined') 
	{	
		case true:		
			
			$(e).attr('checked', false);
			$(e).closest('tr').removeClass('checked');
			
			if (typeof $dc.attr('data-clicked') == 'undefined') {
				return;
			}			
			
			switch (parseInt($dc.attr('data-clicked'))) 
			{
				case 0:				
					$dc.attr('data-clicked', 1);
					$dc.prop('checked', true).change();					
				break;
				
				case 1:
					$dc.removeAttr('data-clicked');
					$dc.prop('checked', false).change();	
				break;
			}
			
		break;
		
		case false:
		
			$(e).attr('checked', true);
			$(e).closest('tr').addClass('checked');	
				
		break;
	}
	
	 
};

Core.task = 
{
	get : function() 
	{
		var ob = 
		{
				type: 'task', 
			  action: 'html',
			    data: {
					type: 'html',
				}
		};
		
		$.post(Core.ajaxPath, ob)
			.done(function(data) {							
				$('section.tasks').html(data);				
				Core.task.exec();
			});
	},
	
	exec : function() 
	{
		$('div.task')
			.on('dblclick', function()
			{
				var _this = this;
				
				Core.modal.confirm(2, function()
				{
					var ob = 
					{
						    type: 'task', 
						  action: 'execute',						
							data: 
							{
									 loc: window.location.href,
									type: $(_this).attr('data-task-type'),
								  taskId: $(_this).attr('data-task-id'),								  
							}
					};
					
					$.post(Core.ajaxPath, ob)
						.done(function(data) 
						{							
							var ob = Core.json(data);
							Core.task.run();
							
							// console.log(data);
							if (typeof ob !== 'object') {
								return;
							}
							
							Core.modal.inf(ob);	
							return;
							
						});
					
				}, function(){
					Core.modal.close();
				});
			});
	},
	
	run : function() {
		Core.task.get();
	}
};

Core.contentMenu = 
{
	html: false,
	
	refresh: function() 
	{
		var cm = 'nav.content-menu';
		
		if (!Core.contentMenu.html) {
			Core.contentMenu.html = $(cm).html();
			return;
		}
		
		$(cm).html(Core.contentMenu.html);
		return;
	}
};

Core.json = function(data) 
{
	var dataJson = data && data.length && $.trim(data);

	if (typeof dataJson == 'undefined') {
		return;
	}
	
	try {
		dataJson = JSON.parse(dataJson);
	} catch (e) {
		return false;
	}
	
	return typeof dataJson == 'object'  ? dataJson : false;
};

Core.catEscape = function() 
{
	$('ul.disabled').removeClass('disabled');
			$(this).removeAttr('style');
			$('ul.categories').find('a[href]').removeAttr('contenteditable').removeAttr('style');
			
			$('ul.categories a.current')
				.removeClass('current');
			
			$('a[href="'+window.location.href+'"]').addClass('current');
			$('li[data-action="safe"]').hide();
};

Core.catRefresh = function(data) 
{	
	$('ul.categories').remove();
	$('section.a-left').append(data);
}

Core.onCtrlEn = function(elt, fn) 
{	
	$(elt).keydown(function (e) 
		{
			if (Core.notFn(fn)) {
				return;
			}
			
			if (e.ctrlKey && e.keyCode == 13) 
			{				
				return fn(this);
			} 								
		});
	
	return;
}

Core.onPaste = function(el)
{	
	if (typeof el !== 'object' || !$(el).length)  return;
	$(el).on('paste', function(e)
	{		
		e.preventDefault();
		var txt = (e.originalEvent || e).clipboardData.getData('text/plain');
		document.execCommand('insertText', false, txt);
		return;	
	});
}

Core.onAltKey = function(elt, key, fn) 
{
	var elt = elt || 'body', key = key || false, 
		fn = fn || false;

	$(elt).keydown(function (e) 
		{			
			if (Core.notFn(fn)) {
				return;
			}
			
			if (e.altKey && e.keyCode == 67) {				
				return fn();
			} 								
		});
}

Core.onEscape = function(element, f) 
{
	var elt = element || 'body', 
		fn = f || false;
	
	$(elt).keydown(function (e) 
	{			
		if (Core.notFn(fn)) {
			return;
		}
		
		if (e.keyCode == 27) {				
			return fn();
		} 	
		
	});
}

Core.onEnter = function(f, element) 
{
	var e;
	
		typeof element == 'string' ?
			e = element : e = 'body';
	
		if (Core.notFn(f)) {
			return;
		}
	
		$(e).on('keydown', function(e){
			if (e.keyCode !== 13) {
				return;
			}
			
			return(f(this));
		});
	
	
};

Core.lastCall = {};

Core.timeout = function(methodName, interval) 
{	
	var current = new Date();
		current = current.getTime();
		
	
	switch (typeof Core.lastCall.onScrollEnd == 'undefined') 
	{				
		case false:
		
			if (current - Core.lastCall[methodName] < interval) {
				Core.lastCall[methodName] = current;
				return false;
			}
			
		break;
	}
	
	Core.lastCall[methodName] = current;
	return true;
	
};

Core.onScrollEnd = function(elt, f) 
{
	if 
	(
		$(elt).length == 0 || 
		Core.notFn(f)
	) 
	{
		return;
	}
	
	$(elt).on('scroll', function(e)
	{
		var y = 0, h = 0, s = 0, pos = 0, lim = 500, t = 0, tm = 0; 
			
			y = this.scrollHeight;
			h = this.offsetHeight;
			s = this.scrollTop;
			
			pos = y - h - s;
			
			console.lo
			
			if 
			(
				pos > lim								||
				s == 0 									||
				!Core.timeout('onScrollEnd', 1000)
			) 
			{
				return;
			}
					
			f(elt);			
			return;
	});
	
};

Core.notFn = function(fn) {
	return typeof fn === 'function' ?
		false : true;
}

Core.onShiftMouse = function(elt, c) 
{
	if (Core.notFn(c)) {
		return;
	}
	
	$(elt).on('click', function(e)
	{
		if(!e.shiftKey) {
			return;
		}
		
		return c(this);
	});
}

Core.onDeleteKey = function(elt, c) 
{	
	if (Core.notFn(c)) {
		return;
	}
	
	$(elt).keyup(function(e)
	{
		if(e.keyCode !== 46) {
			return;
		}
		
		return c(this, e.target);
	});
}

Core.contextMenu = function(elt, fn) 
{
	var $cm = $(elt).find('span.context-menu');
	
	if ($cm.length == 0) {
		return;
	}
	
	$cm.toggleClass('hide');
	
	if (Core.notFn(fn)) {
		return;
	}
	
	$cm.find('a[data-context]')
		.on('click', function(e){
			return fn(e, this);
		});
	
	return; 
}

Core.table = 
{
	terminal: function() 
	{
		Core.onEnter
		(
			function(e)
			{
				var 
					cmd = $(e).val(),
					ob = {};
				
					if (cmd.length == 0) {
						return;
					} 
					
					ob = 
					{
						  type: 'tables', 
						action: 'TERMINAL',
						 TABLE: $('table').attr('data-table'),	
						 
					    KAS_ID: $('section.t-groups').find('li.current')
							.find('a').attr('href'),
							
					   COMMAND: cmd
					};
					
					$('table[data-table]').attr('data-disabled', 1);
					
					$.post(Core.ajaxPath, ob)
						.done(function(data)
						{							
							var ob = Core.json(data);
							
							if (typeof ob == 'object') 
							{
								Core.modal.inf(ob);	
								return;
							}
							
							$('table').find('tbody').html(data);
							$('table[data-table]').attr('data-disabled', 0);							
							Core.table.run();
							
						});	
				
			}, 
			'input.t-terminal'
		);
	},
	
	selectGropup: function() 
	{
		$('section.t-groups').find('a')
			.on('click', function(e)
			{
						
				var ob = 
				{
					          type: 'tables', 
						    action: 'GET_GROUP',
							KAS_ID: $(this).attr('href'),
							 TABLE: $('table').attr('data-table'),								 
					 GROUP_COLUMN : $(this).closest('section[data-group-col]')
										.attr('data-group-col'),
				};
				
				$(this).closest('ul').find('li').removeClass('current');
				$(this).closest('li').addClass('current');
				$('table[data-table]').attr('data-disabled', 1);
				
				// HERE
				$.post(Core.ajaxPath, ob)
					.done(function(data)
					{
						$('table').find('tbody').html(data);
						$('table[data-table]').attr('data-disabled', 0);
						
						Core.table.run();
					}); 
				
				
				e.preventDefault();
			});
	},
	
	getChecked: function() 
	{
		var c = {};
		
		$('tr.checked')
			.each(function(k, v){
				c[k] = $(v).attr('data-id');
			});
			
		return c;
	},
	
	getTableName: function() {
		return $('table').attr('data-table');
	},
	
	onSelectAll: function()
	{
		$('td[data-key="SELECT_ALL"]')
		.on('click', function()
		{
			var $cb = $(this).closest('table').find('input[type="checkbox"]'); 
			Core.tChecked($cb);				
		});
	},
	
	onSynch: function() 
	{
		var $synch = $('li[data-action="table-sn"]');
		
		$synch
			.on('click', function()
			{
				if (!Core.timeout('onSunch', 1000)) {
					return;
				}
				
				console.log('aaa');
				
				if ( $('tr.checked').length == 0 ) {				
					Core.modal.inf(Core.modal.txt[0]);
					return;
				}
				
				Core.contextMenu($synch, function(e, elt)
				{
					var ob = 
					{
						    type: 'task', 
						  action: 'push',						
							data: 
							{
									type: 'synchronization',
							 parentTable: $(elt).attr('data-context'),
							  childTable: Core.table.getTableName(),
								parentId: {},
								 childId: Core.table.getChecked(),
								  taskId: '',
							}
					};
					
					$.post(Core.ajaxPath, ob)
						.done(function(data)
						{							
							Core.modal.inf(Core.modal.txt[1], function(){
								Core.task.run();
								return;
							});							
						});
						
					e.preventDefault();
					
				});
			});
	},
	
	onSyncRemove: function() 
	{
		$('li[data-action="table-sn-remove"]')
			.on('click', function()
			{
				var $g = $('section.t-groups').find('li.current'), 
					sn = 'synchronization', gc = 'data-group-col';
				
				var ob = 
					{
							type: sn, 
						  action: 'remove',						
							data: 
							{
										 type: sn,
								   childTable: Core.table.getTableName(),
									 parentId: $g.find('a').attr('href'),								
									  childId: Core.table.getChecked(),						   
									   taskId: '',
								GROUP_COLUMN : $g.closest('section['+gc+']').attr(gc),
							}
					};
				
				Core.modal.confirm(4, function()
				{
					$.post(Core.ajaxPath, ob)
						.done(function(data)
						{						
							var ob = Core.json(data);
							
							// console.log(data);
							if (typeof ob !== 'object') {
								return;
							}
							
							Core.modal.inf(ob);	
							return;	
						});					
				},						
				function(){
					Core.modal.close();
				});
				
				
			});
	},
	
	onScrollEnd : function() 
	{	
							
		Core.onScrollEnd('section.t-wrapper', 
			function()
			{	
				var cid;
				var ob = 
				{
					   type: 'tables', 
					 action: 'UPLOAD',
					 KAS_ID: $('tr[data-id]').last().attr('data-id'),
					KAS_CID: false, 
					  TABLE: $('table').attr('data-table'),
				};
				
				cid = $('.t-groups li.current').find('a').attr('href');
				typeof cid !== 'undefined' ? ob['KAS_CID'] = cid : false;
				
				$.post(Core.ajaxPath, ob)
					.done(function(data)
					{
						// console.log(data);
						$('table[data-table]').find('tbody')
							.append($.trim(data));
							
						
						Core.table.run();						
						return;
					});
			});
	},
	
	onIns: function() 
	{
		$('li[data-action="table-ins"]').on('click', function()
		{
			var cid;
			var ob = 
				{
					   type: 'tables', 
					 action: 'INSERT',
					 KAS_ID: $('tr[data-id]').first().attr('data-id'),
					KAS_CID: false, 
					  TABLE: $('table').attr('data-table'),
				};
				
			cid = $('.t-groups li.current').find('a').attr('href');
				typeof cid !== 'undefined' ? ob['KAS_CID'] = cid : false;
				
			$.post(Core.ajaxPath, ob)
					.done(function(data)
					{
						
						var ob = Core.json(data);
							
							// console.log(data);
							if (typeof ob !== 'object') {
								return;
							}
							
							Core.modal.inf(ob);	
							return;
							
						Core.table.run();						
						return;
					});
		});
	},
	
	onRange: function() 
	{
		Core.onShiftMouse('input[type="checkbox"]', 
			function(elt)
			{
				var r = {start:0,
					end:0};
				
				if ($('tr.checked').length < 2) {
					return;
				}
				
				r.start = parseInt($('tr.checked').first().attr('data-id'));
				r.end = parseInt($('tr.checked').last().attr('data-id'));
				
				for (var i = r.start; i < r.end; i++) {
					$('tr[data-id="'+i+'"]').addClass('checked');
					Core.tChecked($('tr[data-id="'+i+'"]')
						.find('input[type="checkbox"]'));
				}
				
			});
	},
	
	onCellEdit: function()  
	{
		Core.onEscape('td[data-key]', function()
		{
			$('td[data-key]').find('textarea')
				.each(function(k, v){
					$(v).closest('div[data-content]')	
						.removeAttr('style')				
						.html($(v).val().replace(/\</g, '[[').replace(/\>/g, ']]'));
				});
				
			$('table[data-table]')
				.find('td').removeClass('current');
			$('table[data-table]')
				.find('tr').removeAttr('style');
				
		});
		
		$('td[data-key]')
		.on('dblclick', function()
		{
			var key, has = {KAS_ID: 1, SELECT: 2}, cont;
				key = $(this).attr('data-key');
				
			var _this = this;
				
			$('td[data-key]').find('textarea')
				.each(function(k, v){
					$(v).closest('div[data-content]')	
						.removeAttr('style')				
						.html($(v).val().replace(/\</g, '[[').replace(/\>/g, ']]'));
				});
			
			if 
			(
				has[key]									||
				!$(this).find('div[data-content]').length
			) 
			{
				return;
			}
			
			$('table[data-table]')
				.find('td').removeClass('current');				
			$('table[data-table]')
				.find('tr').removeAttr('style');
				
			$(this).closest('tr')
				.css({outline: '2px solid #85fa1a'});
			
			cont = $(this).find('div[data-content]').html()
				.replace(/\[\[/g, '<')
				.replace(/\]\]/g, '>');
				
			
			$(this).find('div[data-content]').html('<textarea>'+cont+'</textarea>');
			$(this).addClass('current');
			
			/**ctrl+enter*/ 
			Core.onCtrlEn($(this).find('textarea'), 
				function(elt) 
				{
					var ob = {
						type: 'tables', action: 'UPDATE'
					};
					
					ob['TABLE']  = $('table[data-table]').attr('data-table');
					ob['KAS_ID'] = $(_this).closest('tr').attr('data-id');
					
					ob.column = key;
					ob.data = $(elt).val();
					
					$('#ajx5').appendTo($(_this)).show();			
					
					$.post(Core.ajaxPath, ob).done(function(data)
					{	
						console.log(data);
						
						$('#ajx5').hide();
						
						if (isNaN(parseInt(data))) 
						{
							Core.modal.e();
							return;
						}					
						
					});
					
				});
		});
	},
	
	onCheckbox: function() 
	{
		$('input[type="checkbox"]')
			.on('click', function(e) 
			{				
				switch (this.checked) 
				{
					case true:
						$(this).attr('data-clicked', 1);
						$(this).closest('tr').addClass('checked');
					break;
					
					case false:
						$(this).attr('data-clicked', 0);					
						$(this).closest('tr').removeClass('checked');
					break;
				}
				
				return;
				
			});
	},
	
	onDelete: function(f) 
	{
		Core.onDeleteKey('table[data-table]', 
		function(elt, target)
		{
			var ob = 
			{
				  type: 'tables', 
				action: 'DELETE',
				KAS_ID: {},
				 TABLE: $(elt).attr('data-table'),
			};
			
			if 
			(
				typeof $(target).attr('contenteditable') !== 'undefined'	||
				$('tr.checked').length == 0
			) 
			{
				return;
			}
			
			$('tr.checked')
				.each(function(k,v){
					ob.KAS_ID[k] = $(v).attr('data-id');
				});
			
			Core.modal.confirm({}, function()
			{				
				$.post(Core.ajaxPath, ob)
					.done(function(data)
					{
						console.log(data);
						$('tr.checked').remove();						
						Core.modal.close();
						
						/*Reload self*/
						Core.table.onDelete();	
						
						Core.notFn(f) ? true : f();
						return;
					});				
			}, 
			
			function()
			{
				Core.modal.close();
			});
			
		});
	},
	
	run: function() 
	{
		Core.contentMenu.refresh();
		Core.table.onCheckbox();
		Core.table.onIns();
		Core.table.onRange();
		Core.table.onSelectAll();	
		Core.table.onCellEdit();
		Core.table.onScrollEnd();
		Core.table.onDelete();
		Core.table.onSynch();
		Core.table.onSyncRemove();
		
		console.log('Run');
	},
	
	al: {run: function(){
		return Core.table.run();
	}},
};

Proj = {};

Proj.offers = function() 
{	
	//e = document.createEvent('Event');
	//event.initEvent('table', true, true);	
	//document.addEventListener('table', );
	
	//var e = Core.table.run();
	//e = 0;
	
	Core.table.al.run();
	Core.table.selectGropup();
	Core.table.terminal();
}

Proj.catalog = function() 
{
	kas('#li-bar').on('click', function(){
		kas('#nav-bar').cls('bar-open');
	});
	
	kas('li[data-type="project"]').on('click', function(ob, e){
		ob.cls('dropdown');
	});
	
	kas('span.ico-minimize').on('click', function(){
		kas('footer').cls('min');
	});
	
	$('ul.sub').find('a[href]')
		.each(function(k,v)
		{
			var rExp = new RegExp($(v).attr('href'));
			
			if (!window.location.href.match(rExp)) {
				return;
			}
			
			$(v).addClass('active')
				.closest('li.transition').addClass('dropdown');
		});
	
	$('a[href="'+window.location.href+'"]').addClass('current');
	
	/*Catralog edit*/
	$('li[data-action="edit"]').on('click', function()
	{
		
		var txt;
		
		/*Exit mode*/
		if ($('ul.disabled').length) 
		{			
			Core.catEscape();				
			return true;
		}
		
		/*Change target*/
		if (!$('ul.categories a.current').length) {
			$($('ul.categories a').get(0)).addClass('current');
		}
			
		txt = $('ul.categories a.current').text();
		
		/*block href*/
		$('ul.categories').addClass('disabled');
		
		$('ul.disabled a').on('click', function()
		{		
			$('ul.categories a.current')
				.removeClass('current');
			
			/*Esc*/
			$(this).on('keyup', function(e){
				e.keyCode == 27 ?
					Core.catEscape() : false;
			});
			
			/*If old DOM*/
			if (!$('ul.disabled').length) {
				return true;
			}
				
			$(this).addClass('current').css({outline: '1px solid #6996D9', padding: '2px'})
				.attr('contenteditable', true);
				
			/*show safe mode*/
			$('li[data-action="safe"]:hidden').show(0);
				
			return false;
			
		});
			
		$(this).css({color: '#fff'});	
		$('ul.categories a.current')
			.css({outline: '1px solid #6996D9', padding: '2px'}).attr('contenteditable', true);
		
	});	
	
	$('li[data-action="safe"]')
		.on('click', function()
		{
			if (!$('a[contenteditable="true"]').length) {
				return false;
			}
			
			var ob = {};
			
				$('a[contenteditable="true"]')
					.each(function(k,v)
					{
						ob[k] = {name: $(v).text(), 
							loc: $(v).attr('href')};
					});
				
				ob.type = 'KAS_CATEGORIES';
				ob.action = 'safe';
				
				$.post(Core.ajaxPath, ob).done(function(data)
				{
					$('li[data-action="edit"]').attr('style', '');
					$('li[data-action="safe"]').hide();
					Core.catRefresh(data);	
					Core.modal.inf();
				});
		});	
	
	$('li[data-action="ins"]').on('click', function()
	{		
		var _this = this;
		
		$(this).find('.context-menu').toggleClass('hide');		

		$('section.a-left').find('a.current').length == 0 ?
			$(this).find('a[data-context="cat-sub"]').hide() :
			$(this).find('a[data-context="cat-sub"]').show();
		
		$(this).find('a[data-context]').on('click', function()
		{
			$(_this).addClass('active');
			
			$('section.v-zone').removeClass('hide');
			$('div.cat-ins').removeClass('hide');
			$('div.cat-ins button').attr('data-action', $(this).attr('data-context'));
			$(this).toggleClass('context-selected');
			return false;
		});
		
		return false;
	});
	
	$('textarea').on('click', function(){
		$('.context-menu').addClass('hide');
	});
	
	$('textarea').on('keyup', function(){
		$('button.hide').show();
	});
	
	$('div.cat-ins button').on('click', function()
	{
		var str = $('textarea').val(), ob = {};
		
		ob.type 			= 'KAS_CATEGORIES';
		ob.action 			= 'insert';		
		ob.data 			= str;	
		ob['actionType'] 	= $(this).attr('data-action');
		ob['KAS_PID'] 		= window.location.href.replace(/[^0-9]+/, '');
		
		if (!str.length) {
			$('textarea').addClass('e-bg');
			return false;
		}
		
		$('textarea').removeClass('e-bg');
		
		$.post(Core.ajaxPath, ob).done(function(data)
		{
			Core.catRefresh(data);
			Core.modal.inf();	
			console.log(data);			
		});
		
	});	
	
	$('li[data-action="del"]').on('click', function()
	{
		var $current;
			$current = $('ul.categories').find('a.current');
			
		if ($current.length == 0) {
			Core.modal.inf(Core.modal.txt[0]);
			return;
		}
		
		Core.modal.confirm(this, function()
		{
			var ob = {};
			ob.type 			= 'KAS_CATEGORIES';
			ob.action 			= 'delete';	
			ob['actionType'] 	= $(this).attr('data-action');
			ob['KAS_ID'] 		= $current.attr('href').replace(/[^0-9]+/, '');		

			$.post(Core.ajaxPath, ob).done(function(data)
			{
				Core.catRefresh(data);
				Core.modal.close();
			});
		}, 
		
		function() {
			Core.modal.close();
		});
		
		return;
	});	
};



kas(function()
{
	Proj.catalog();
	Proj.offers();
	Core.task.run();
	Core.terminal.run();
	Core.FileManager.run();
});