
function Kas(arg) 
{
	this.a = arg;
	this.collection;
	this.loc;
	
	this.method = 
	{
				"ID" : 'getElementById',
		  "SELECTOR" : 'querySelectorAll',
	};
	
	this.support = {
				  "ID" : new RegExp("^#([a-zA-Z0-9-_]+)$"),
			"SELECTOR" : new RegExp("^([^#]+)$"),
		};
		
	this.conf();
};

Kas.prototype = 
{
	element : function(callable) 
	{
		typeof this.collection[0] == 'undefined' ?
			this.collection = {0: this.collection} : false;
		
		if (!this.func(callable)) {
			return false;
		}
		
		for (p in this.collection) 
		{	
			if (!this.ob(this.collection[p])) {
				continue;
			}
			
			this.collection[p] = callable(this.collection[p]);
		}
		
		return this;
	},
	
	
	
	// Public
	on : function(type, callable) 
	{
		
		if (!this.str(type) || !this.func(callable)) {
			return false;
		}
		
		var _this = this;
		
		this.element(function(elt)
		{
			
			elt.addEventListener(type, function(e){
				return callable(_this, e);});
		});
		
		return this;
	},
	
	// Добавляет элементу либо 
	// группе элементов новый класс.
	cls : function(cName)
	{	
		if (!this.str(cName)) {
			return this;
		}
		
		this.element(function(elt)
		{
			var 
			c    = elt.className, 
			rExp = new RegExp(' ' + cName);
		
			if (c.match(rExp)) 
			{			
				elt.className = c.replace(' ' + cName, '');
				return this;
			}		
			
			elt.className += ' ' + cName;			
		});		
		
		return this;
	},
	
	find : function(selector) 
	{		
		if (typeof this.a !== 'string') {
			return this;
		}
		
		typeof selector == 'string' ?
			this.a = selector : false;		
			
		this.getCollection(this.getMethod(this.a));	
		return this;
	},
	
	// Проверка типов данных
	prop: function(arg, type) 
	{
		if (typeof arg === type) {
				return true;
		}
		
		return false;
	},
	
	func : function(arg) 
	{
		if (this.prop(arg, 'function')) {
			return true;
		}
		
		return false;
	},
	
	ob : function(arg) 
	{
		if (arg && this.prop(arg, 'object')) {
			return true;
		}
		
		return false;
	},
	
	str: function(arg) 
	{
		if (this.prop(arg, 'string')) {
			return true;
		}
		
		return false;
	},
	
	// Возвращает объект типа 
	// {type: [тип селектора], selector: [значение селектора]}
	getMethod : function(selector) 
	{		
		for (type in this.support) 
		{
			var m = selector.match(this.support[type]);
			
			if (!this.ob(m)) {
				continue;
			}
			
			return {'type': type, 'selector': m[1]};			
		}
		
		return {};
	},
	
	getCollection : function(methodOb) 
	{
		if (methodOb.type == 'undefined' || methodOb.selector == 'undefined') {
			return {};
		}
		
		this.collection = document[this.method[methodOb.type]](methodOb.selector);
	},
	
	conf: function() {
		this.find();
	}
};

Kas.run = function(arg) 
{
	if (typeof arg == 'function') 
	{
		return window.addEventListener('load', function(){			
			return arg();			
		});
	}
	
	var ob = new Kas(arg);
	return ob;
	
};

kas = function (arg) {
	return Kas.run(arg);
}

