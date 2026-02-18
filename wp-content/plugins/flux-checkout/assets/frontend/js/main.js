/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 428
(module) {

"use strict";
module.exports = window["jQuery"];

/***/ },

/***/ 732
(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*! Magnific Popup - v1.2.0 - 2024-06-08
* http://dimsemenov.com/plugins/magnific-popup/
* Copyright (c) 2024 Dmytro Semenov; */
;(function (factory) { 
if (true) { 
 // AMD. Register as an anonymous module. 
 !(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(428)], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
		__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
		(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
		__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__)); 
 } else // removed by dead control flow
{} 
 }(function($) { 

/*>>core*/
/**
 * 
 * Magnific Popup Core JS file
 * 
 */


/**
 * Private static constants
 */
var CLOSE_EVENT = 'Close',
	BEFORE_CLOSE_EVENT = 'BeforeClose',
	AFTER_CLOSE_EVENT = 'AfterClose',
	BEFORE_APPEND_EVENT = 'BeforeAppend',
	MARKUP_PARSE_EVENT = 'MarkupParse',
	OPEN_EVENT = 'Open',
	CHANGE_EVENT = 'Change',
	NS = 'mfp',
	EVENT_NS = '.' + NS,
	READY_CLASS = 'mfp-ready',
	REMOVING_CLASS = 'mfp-removing',
	PREVENT_CLOSE_CLASS = 'mfp-prevent-close';


/**
 * Private vars 
 */
/*jshint -W079 */
var mfp, // As we have only one instance of MagnificPopup object, we define it locally to not to use 'this'
	MagnificPopup = function(){},
	_isJQ = !!(window.jQuery),
	_prevStatus,
	_window = $(window),
	_document,
	_prevContentType,
	_wrapClasses,
	_currPopupType;


/**
 * Private functions
 */
var _mfpOn = function(name, f) {
		mfp.ev.on(NS + name + EVENT_NS, f);
	},
	_getEl = function(className, appendTo, html, raw) {
		var el = document.createElement('div');
		el.className = 'mfp-'+className;
		if(html) {
			el.innerHTML = html;
		}
		if(!raw) {
			el = $(el);
			if(appendTo) {
				el.appendTo(appendTo);
			}
		} else if(appendTo) {
			appendTo.appendChild(el);
		}
		return el;
	},
	_mfpTrigger = function(e, data) {
		mfp.ev.triggerHandler(NS + e, data);

		if(mfp.st.callbacks) {
			// converts "mfpEventName" to "eventName" callback and triggers it if it's present
			e = e.charAt(0).toLowerCase() + e.slice(1);
			if(mfp.st.callbacks[e]) {
				mfp.st.callbacks[e].apply(mfp, Array.isArray(data) ? data : [data]);
			}
		}
	},
	_getCloseBtn = function(type) {
		if(type !== _currPopupType || !mfp.currTemplate.closeBtn) {
			mfp.currTemplate.closeBtn = $( mfp.st.closeMarkup.replace('%title%', mfp.st.tClose ) );
			_currPopupType = type;
		}
		return mfp.currTemplate.closeBtn;
	},
	// Initialize Magnific Popup only when called at least once
	_checkInstance = function() {
		if(!$.magnificPopup.instance) {
			/*jshint -W020 */
			mfp = new MagnificPopup();
			mfp.init();
			$.magnificPopup.instance = mfp;
		}
	},
	// CSS transition detection, http://stackoverflow.com/questions/7264899/detect-css-transitions-using-javascript-and-without-modernizr
	supportsTransitions = function() {
		var s = document.createElement('p').style, // 's' for style. better to create an element if body yet to exist
			v = ['ms','O','Moz','Webkit']; // 'v' for vendor

		if( s['transition'] !== undefined ) {
			return true; 
		}
			
		while( v.length ) {
			if( v.pop() + 'Transition' in s ) {
				return true;
			}
		}
				
		return false;
	};



/**
 * Public functions
 */
MagnificPopup.prototype = {

	constructor: MagnificPopup,

	/**
	 * Initializes Magnific Popup plugin. 
	 * This function is triggered only once when $.fn.magnificPopup or $.magnificPopup is executed
	 */
	init: function() {
		var appVersion = navigator.appVersion;
		mfp.isLowIE = mfp.isIE8 = document.all && !document.addEventListener;
		mfp.isAndroid = (/android/gi).test(appVersion);
		mfp.isIOS = (/iphone|ipad|ipod/gi).test(appVersion);
		mfp.supportsTransition = supportsTransitions();

		// We disable fixed positioned lightbox on devices that don't handle it nicely.
		// If you know a better way of detecting this - let me know.
		mfp.probablyMobile = (mfp.isAndroid || mfp.isIOS || /(Opera Mini)|Kindle|webOS|BlackBerry|(Opera Mobi)|(Windows Phone)|IEMobile/i.test(navigator.userAgent) );
		_document = $(document);

		mfp.popupsCache = {};
	},

	/**
	 * Opens popup
	 * @param  data [description]
	 */
	open: function(data) {

		var i;

		if(data.isObj === false) { 
			// convert jQuery collection to array to avoid conflicts later
			mfp.items = data.items.toArray();

			mfp.index = 0;
			var items = data.items,
				item;
			for(i = 0; i < items.length; i++) {
				item = items[i];
				if(item.parsed) {
					item = item.el[0];
				}
				if(item === data.el[0]) {
					mfp.index = i;
					break;
				}
			}
		} else {
			mfp.items = Array.isArray(data.items) ? data.items : [data.items];
			mfp.index = data.index || 0;
		}

		// if popup is already opened - we just update the content
		if(mfp.isOpen) {
			mfp.updateItemHTML();
			return;
		}
		
		mfp.types = []; 
		_wrapClasses = '';
		if(data.mainEl && data.mainEl.length) {
			mfp.ev = data.mainEl.eq(0);
		} else {
			mfp.ev = _document;
		}

		if(data.key) {
			if(!mfp.popupsCache[data.key]) {
				mfp.popupsCache[data.key] = {};
			}
			mfp.currTemplate = mfp.popupsCache[data.key];
		} else {
			mfp.currTemplate = {};
		}



		mfp.st = $.extend(true, {}, $.magnificPopup.defaults, data ); 
		mfp.fixedContentPos = mfp.st.fixedContentPos === 'auto' ? !mfp.probablyMobile : mfp.st.fixedContentPos;

		if(mfp.st.modal) {
			mfp.st.closeOnContentClick = false;
			mfp.st.closeOnBgClick = false;
			mfp.st.showCloseBtn = false;
			mfp.st.enableEscapeKey = false;
		}
		

		// Building markup
		// main containers are created only once
		if(!mfp.bgOverlay) {

			// Dark overlay
			mfp.bgOverlay = _getEl('bg').on('click'+EVENT_NS, function() {
				mfp.close();
			});

			mfp.wrap = _getEl('wrap').attr('tabindex', -1).on('click'+EVENT_NS, function(e) {
				if(mfp._checkIfClose(e.target)) {
					mfp.close();
				}
			});

			mfp.container = _getEl('container', mfp.wrap);
		}

		mfp.contentContainer = _getEl('content');
		if(mfp.st.preloader) {
			mfp.preloader = _getEl('preloader', mfp.container, mfp.st.tLoading);
		}


		// Initializing modules
		var modules = $.magnificPopup.modules;
		for(i = 0; i < modules.length; i++) {
			var n = modules[i];
			n = n.charAt(0).toUpperCase() + n.slice(1);
			mfp['init'+n].call(mfp);
		}
		_mfpTrigger('BeforeOpen');


		if(mfp.st.showCloseBtn) {
			// Close button
			if(!mfp.st.closeBtnInside) {
				mfp.wrap.append( _getCloseBtn() );
			} else {
				_mfpOn(MARKUP_PARSE_EVENT, function(e, template, values, item) {
					values.close_replaceWith = _getCloseBtn(item.type);
				});
				_wrapClasses += ' mfp-close-btn-in';
			}
		}

		if(mfp.st.alignTop) {
			_wrapClasses += ' mfp-align-top';
		}

	

		if(mfp.fixedContentPos) {
			mfp.wrap.css({
				overflow: mfp.st.overflowY,
				overflowX: 'hidden',
				overflowY: mfp.st.overflowY
			});
		} else {
			mfp.wrap.css({ 
				top: _window.scrollTop(),
				position: 'absolute'
			});
		}
		if( mfp.st.fixedBgPos === false || (mfp.st.fixedBgPos === 'auto' && !mfp.fixedContentPos) ) {
			mfp.bgOverlay.css({
				height: _document.height(),
				position: 'absolute'
			});
		}

		

		if(mfp.st.enableEscapeKey) {
			// Close on ESC key
			_document.on('keyup' + EVENT_NS, function(e) {
				if(e.keyCode === 27) {
					mfp.close();
				}
			});
		}

		_window.on('resize' + EVENT_NS, function() {
			mfp.updateSize();
		});


		if(!mfp.st.closeOnContentClick) {
			_wrapClasses += ' mfp-auto-cursor';
		}
		
		if(_wrapClasses)
			mfp.wrap.addClass(_wrapClasses);


		// this triggers recalculation of layout, so we get it once to not to trigger twice
		var windowHeight = mfp.wH = _window.height();

		
		var windowStyles = {};

		if( mfp.fixedContentPos ) {
            if(mfp._hasScrollBar(windowHeight)){
                var s = mfp._getScrollbarSize();
                if(s) {
                    windowStyles.marginRight = s;
                }
            }
        }

		if(mfp.fixedContentPos) {
			if(!mfp.isIE7) {
				windowStyles.overflow = 'hidden';
			} else {
				// ie7 double-scroll bug
				$('body, html').css('overflow', 'hidden');
			}
		}

		
		
		var classesToadd = mfp.st.mainClass;
		if(mfp.isIE7) {
			classesToadd += ' mfp-ie7';
		}
		if(classesToadd) {
			mfp._addClassToMFP( classesToadd );
		}

		// add content
		mfp.updateItemHTML();

		_mfpTrigger('BuildControls');

		// remove scrollbar, add margin e.t.c
		$('html').css(windowStyles);
		
		// add everything to DOM
		mfp.bgOverlay.add(mfp.wrap).prependTo( mfp.st.prependTo || $(document.body) );

		// Save last focused element
		mfp._lastFocusedEl = document.activeElement;
		
		// Wait for next cycle to allow CSS transition
		setTimeout(function() {
			
			if(mfp.content) {
				mfp._addClassToMFP(READY_CLASS);
				mfp._setFocus();
			} else {
				// if content is not defined (not loaded e.t.c) we add class only for BG
				mfp.bgOverlay.addClass(READY_CLASS);
			}
			
			// Trap the focus in popup
			_document.on('focusin' + EVENT_NS, mfp._onFocusIn);

		}, 16);

		mfp.isOpen = true;
		mfp.updateSize(windowHeight);
		_mfpTrigger(OPEN_EVENT);

		return data;
	},

	/**
	 * Closes the popup
	 */
	close: function() {
		if(!mfp.isOpen) return;
		_mfpTrigger(BEFORE_CLOSE_EVENT);

		mfp.isOpen = false;
		// for CSS3 animation
		if(mfp.st.removalDelay && !mfp.isLowIE && mfp.supportsTransition )  {
			mfp._addClassToMFP(REMOVING_CLASS);
			setTimeout(function() {
				mfp._close();
			}, mfp.st.removalDelay);
		} else {
			mfp._close();
		}
	},

	/**
	 * Helper for close() function
	 */
	_close: function() {
		_mfpTrigger(CLOSE_EVENT);

		var classesToRemove = REMOVING_CLASS + ' ' + READY_CLASS + ' ';

		mfp.bgOverlay.detach();
		mfp.wrap.detach();
		mfp.container.empty();

		if(mfp.st.mainClass) {
			classesToRemove += mfp.st.mainClass + ' ';
		}

		mfp._removeClassFromMFP(classesToRemove);

		if(mfp.fixedContentPos) {
			var windowStyles = {marginRight: ''};
			if(mfp.isIE7) {
				$('body, html').css('overflow', '');
			} else {
				windowStyles.overflow = '';
			}
			$('html').css(windowStyles);
		}
		
		_document.off('keyup' + EVENT_NS + ' focusin' + EVENT_NS);
		mfp.ev.off(EVENT_NS);

		// clean up DOM elements that aren't removed
		mfp.wrap.attr('class', 'mfp-wrap').removeAttr('style');
		mfp.bgOverlay.attr('class', 'mfp-bg');
		mfp.container.attr('class', 'mfp-container');

		// remove close button from target element
		if(mfp.st.showCloseBtn &&
		(!mfp.st.closeBtnInside || mfp.currTemplate[mfp.currItem.type] === true)) {
			if(mfp.currTemplate.closeBtn)
				mfp.currTemplate.closeBtn.detach();
		}


		if(mfp.st.autoFocusLast && mfp._lastFocusedEl) {
			$(mfp._lastFocusedEl).trigger('focus'); // put tab focus back
		}
		mfp.currItem = null;	
		mfp.content = null;
		mfp.currTemplate = null;
		mfp.prevHeight = 0;

		_mfpTrigger(AFTER_CLOSE_EVENT);
	},
	
	updateSize: function(winHeight) {

		if(mfp.isIOS) {
			// fixes iOS nav bars https://github.com/dimsemenov/Magnific-Popup/issues/2
			var zoomLevel = document.documentElement.clientWidth / window.innerWidth;
			var height = window.innerHeight * zoomLevel;
			mfp.wrap.css('height', height);
			mfp.wH = height;
		} else {
			mfp.wH = winHeight || _window.height();
		}
		// Fixes #84: popup incorrectly positioned with position:relative on body
		if(!mfp.fixedContentPos) {
			mfp.wrap.css('height', mfp.wH);
		}

		_mfpTrigger('Resize');

	},

	/**
	 * Set content of popup based on current index
	 */
	updateItemHTML: function() {
		var item = mfp.items[mfp.index];

		// Detach and perform modifications
		mfp.contentContainer.detach();

		if(mfp.content)
			mfp.content.detach();

		if(!item.parsed) {
			item = mfp.parseEl( mfp.index );
		}

		var type = item.type;

		_mfpTrigger('BeforeChange', [mfp.currItem ? mfp.currItem.type : '', type]);
		// BeforeChange event works like so:
		// _mfpOn('BeforeChange', function(e, prevType, newType) { });

		mfp.currItem = item;

		if(!mfp.currTemplate[type]) {
			var markup = mfp.st[type] ? mfp.st[type].markup : false;

			// allows to modify markup
			_mfpTrigger('FirstMarkupParse', markup);

			if(markup) {
				mfp.currTemplate[type] = $(markup);
			} else {
				// if there is no markup found we just define that template is parsed
				mfp.currTemplate[type] = true;
			}
		}

		if(_prevContentType && _prevContentType !== item.type) {
			mfp.container.removeClass('mfp-'+_prevContentType+'-holder');
		}

		var newContent = mfp['get' + type.charAt(0).toUpperCase() + type.slice(1)](item, mfp.currTemplate[type]);
		mfp.appendContent(newContent, type);

		item.preloaded = true;

		_mfpTrigger(CHANGE_EVENT, item);
		_prevContentType = item.type;

		// Append container back after its content changed
		mfp.container.prepend(mfp.contentContainer);

		_mfpTrigger('AfterChange');
	},


	/**
	 * Set HTML content of popup
	 */
	appendContent: function(newContent, type) {
		mfp.content = newContent;

		if(newContent) {
			if(mfp.st.showCloseBtn && mfp.st.closeBtnInside &&
				mfp.currTemplate[type] === true) {
				// if there is no markup, we just append close button element inside
				if(!mfp.content.find('.mfp-close').length) {
					mfp.content.append(_getCloseBtn());
				}
			} else {
				mfp.content = newContent;
			}
		} else {
			mfp.content = '';
		}

		_mfpTrigger(BEFORE_APPEND_EVENT);
		mfp.container.addClass('mfp-'+type+'-holder');

		mfp.contentContainer.append(mfp.content);
	},


	/**
	 * Creates Magnific Popup data object based on given data
	 * @param  {int} index Index of item to parse
	 */
	parseEl: function(index) {
		var item = mfp.items[index],
			type;

		if(item.tagName) {
			item = { el: $(item) };
		} else {
			type = item.type;
			item = { data: item, src: item.src };
		}

		if(item.el) {
			var types = mfp.types;

			// check for 'mfp-TYPE' class
			for(var i = 0; i < types.length; i++) {
				if( item.el.hasClass('mfp-'+types[i]) ) {
					type = types[i];
					break;
				}
			}

			item.src = item.el.attr('data-mfp-src');
			if(!item.src) {
				item.src = item.el.attr('href');
			}
		}

		item.type = type || mfp.st.type || 'inline';
		item.index = index;
		item.parsed = true;
		mfp.items[index] = item;
		_mfpTrigger('ElementParse', item);

		return mfp.items[index];
	},


	/**
	 * Initializes single popup or a group of popups
	 */
	addGroup: function(el, options) {
		var eHandler = function(e) {
			e.mfpEl = this;
			mfp._openClick(e, el, options);
		};

		if(!options) {
			options = {};
		}

		var eName = 'click.magnificPopup';
		options.mainEl = el;

		if(options.items) {
			options.isObj = true;
			el.off(eName).on(eName, eHandler);
		} else {
			options.isObj = false;
			if(options.delegate) {
				el.off(eName).on(eName, options.delegate , eHandler);
			} else {
				options.items = el;
				el.off(eName).on(eName, eHandler);
			}
		}
	},
	_openClick: function(e, el, options) {
		var midClick = options.midClick !== undefined ? options.midClick : $.magnificPopup.defaults.midClick;


		if(!midClick && ( e.which === 2 || e.ctrlKey || e.metaKey || e.altKey || e.shiftKey ) ) {
			return;
		}

		var disableOn = options.disableOn !== undefined ? options.disableOn : $.magnificPopup.defaults.disableOn;

		if(disableOn) {
			if(typeof disableOn === "function") {
				if( !disableOn.call(mfp) ) {
					return true;
				}
			} else { // else it's number
				if( _window.width() < disableOn ) {
					return true;
				}
			}
		}

		if(e.type) {
			e.preventDefault();

			// This will prevent popup from closing if element is inside and popup is already opened
			if(mfp.isOpen) {
				e.stopPropagation();
			}
		}

		options.el = $(e.mfpEl);
		if(options.delegate) {
			options.items = el.find(options.delegate);
		}
		mfp.open(options);
	},


	/**
	 * Updates text on preloader
	 */
	updateStatus: function(status, text) {

		if(mfp.preloader) {
			if(_prevStatus !== status) {
				mfp.container.removeClass('mfp-s-'+_prevStatus);
			}

			if(!text && status === 'loading') {
				text = mfp.st.tLoading;
			}

			var data = {
				status: status,
				text: text
			};
			// allows to modify status
			_mfpTrigger('UpdateStatus', data);

			status = data.status;
			text = data.text;

			if (mfp.st.allowHTMLInStatusIndicator) {
				mfp.preloader.html(text);
			} else {
				mfp.preloader.text(text);
			}

			mfp.preloader.find('a').on('click', function(e) {
				e.stopImmediatePropagation();
			});

			mfp.container.addClass('mfp-s-'+status);
			_prevStatus = status;
		}
	},


	/*
		"Private" helpers that aren't private at all
	 */
	// Check to close popup or not
	// "target" is an element that was clicked
	_checkIfClose: function(target) {

		if($(target).closest('.' + PREVENT_CLOSE_CLASS).length) {
			return;
		}

		var closeOnContent = mfp.st.closeOnContentClick;
		var closeOnBg = mfp.st.closeOnBgClick;

		if(closeOnContent && closeOnBg) {
			return true;
		} else {

			// We close the popup if click is on close button or on preloader. Or if there is no content.
			if(!mfp.content || $(target).closest('.mfp-close').length || (mfp.preloader && target === mfp.preloader[0]) ) {
				return true;
			}

			// if click is outside the content
			if(  (target !== mfp.content[0] && !$.contains(mfp.content[0], target))  ) {
				if(closeOnBg) {
					// last check, if the clicked element is in DOM, (in case it's removed onclick)
					if( $.contains(document, target) ) {
						return true;
					}
				}
			} else if(closeOnContent) {
				return true;
			}

		}
		return false;
	},
	_addClassToMFP: function(cName) {
		mfp.bgOverlay.addClass(cName);
		mfp.wrap.addClass(cName);
	},
	_removeClassFromMFP: function(cName) {
		this.bgOverlay.removeClass(cName);
		mfp.wrap.removeClass(cName);
	},
	_hasScrollBar: function(winHeight) {
		return (  (mfp.isIE7 ? _document.height() : document.body.scrollHeight) > (winHeight || _window.height()) );
	},
	_setFocus: function() {
		(mfp.st.focus ? mfp.content.find(mfp.st.focus).eq(0) : mfp.wrap).trigger('focus');
	},
	_onFocusIn: function(e) {
		if( e.target !== mfp.wrap[0] && !$.contains(mfp.wrap[0], e.target) ) {
			mfp._setFocus();
			return false;
		}
	},
	_parseMarkup: function(template, values, item) {
		var arr;
		if(item.data) {
			values = $.extend(item.data, values);
		}
		_mfpTrigger(MARKUP_PARSE_EVENT, [template, values, item] );

		$.each(values, function(key, value) {
			if(value === undefined || value === false) {
				return true;
			}
			arr = key.split('_');
			if(arr.length > 1) {
				var el = template.find(EVENT_NS + '-'+arr[0]);

				if(el.length > 0) {
					var attr = arr[1];
					if(attr === 'replaceWith') {
						if(el[0] !== value[0]) {
							el.replaceWith(value);
						}
					} else if(attr === 'img') {
						if(el.is('img')) {
							el.attr('src', value);
						} else {
							el.replaceWith( $('<img>').attr('src', value).attr('class', el.attr('class')) );
						}
					} else {
						el.attr(arr[1], value);
					}
				}

			} else {
				if (mfp.st.allowHTMLInTemplate) {
					template.find(EVENT_NS + '-'+key).html(value);
				} else {
					template.find(EVENT_NS + '-'+key).text(value);
				}
			}
		});
	},

	_getScrollbarSize: function() {
		// thx David
		if(mfp.scrollbarSize === undefined) {
			var scrollDiv = document.createElement("div");
			scrollDiv.style.cssText = 'width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;';
			document.body.appendChild(scrollDiv);
			mfp.scrollbarSize = scrollDiv.offsetWidth - scrollDiv.clientWidth;
			document.body.removeChild(scrollDiv);
		}
		return mfp.scrollbarSize;
	}

}; /* MagnificPopup core prototype end */




/**
 * Public static functions
 */
$.magnificPopup = {
	instance: null,
	proto: MagnificPopup.prototype,
	modules: [],

	open: function(options, index) {
		_checkInstance();

		if(!options) {
			options = {};
		} else {
			options = $.extend(true, {}, options);
		}

		options.isObj = true;
		options.index = index || 0;
		return this.instance.open(options);
	},

	close: function() {
		return $.magnificPopup.instance && $.magnificPopup.instance.close();
	},

	registerModule: function(name, module) {
		if(module.options) {
			$.magnificPopup.defaults[name] = module.options;
		}
		$.extend(this.proto, module.proto);
		this.modules.push(name);
	},

	defaults: {

		// Info about options is in docs:
		// http://dimsemenov.com/plugins/magnific-popup/documentation.html#options

		disableOn: 0,

		key: null,

		midClick: false,

		mainClass: '',

		preloader: true,

		focus: '', // CSS selector of input to focus after popup is opened

		closeOnContentClick: false,

		closeOnBgClick: true,

		closeBtnInside: true,

		showCloseBtn: true,

		enableEscapeKey: true,

		modal: false,

		alignTop: false,

		removalDelay: 0,

		prependTo: null,

		fixedContentPos: 'auto',

		fixedBgPos: 'auto',

		overflowY: 'auto',

		closeMarkup: '<button title="%title%" type="button" class="mfp-close">&#215;</button>',

		tClose: 'Close (Esc)',

		tLoading: 'Loading...',

		autoFocusLast: true,

		allowHTMLInStatusIndicator: false,

		allowHTMLInTemplate: false

	}
};



$.fn.magnificPopup = function(options) {
	_checkInstance();

	var jqEl = $(this);

	// We call some API method of first param is a string
	if (typeof options === "string" ) {

		if(options === 'open') {
			var items,
				itemOpts = _isJQ ? jqEl.data('magnificPopup') : jqEl[0].magnificPopup,
				index = parseInt(arguments[1], 10) || 0;

			if(itemOpts.items) {
				items = itemOpts.items[index];
			} else {
				items = jqEl;
				if(itemOpts.delegate) {
					items = items.find(itemOpts.delegate);
				}
				items = items.eq( index );
			}
			mfp._openClick({mfpEl:items}, jqEl, itemOpts);
		} else {
			if(mfp.isOpen)
				mfp[options].apply(mfp, Array.prototype.slice.call(arguments, 1));
		}

	} else {
		// clone options obj
		options = $.extend(true, {}, options);

		/*
		 * As Zepto doesn't support .data() method for objects
		 * and it works only in normal browsers
		 * we assign "options" object directly to the DOM element. FTW!
		 */
		if(_isJQ) {
			jqEl.data('magnificPopup', options);
		} else {
			jqEl[0].magnificPopup = options;
		}

		mfp.addGroup(jqEl, options);

	}
	return jqEl;
};

/*>>core*/

/*>>inline*/

var INLINE_NS = 'inline',
	_hiddenClass,
	_inlinePlaceholder,
	_lastInlineElement,
	_putInlineElementsBack = function() {
		if(_lastInlineElement) {
			_inlinePlaceholder.after( _lastInlineElement.addClass(_hiddenClass) ).detach();
			_lastInlineElement = null;
		}
	};

$.magnificPopup.registerModule(INLINE_NS, {
	options: {
		hiddenClass: 'hide', // will be appended with `mfp-` prefix
		markup: '',
		tNotFound: 'Content not found'
	},
	proto: {

		initInline: function() {
			mfp.types.push(INLINE_NS);

			_mfpOn(CLOSE_EVENT+'.'+INLINE_NS, function() {
				_putInlineElementsBack();
			});
		},

		getInline: function(item, template) {

			_putInlineElementsBack();

			if(item.src) {
				var inlineSt = mfp.st.inline,
					el = $(item.src);

				if(el.length) {

					// If target element has parent - we replace it with placeholder and put it back after popup is closed
					var parent = el[0].parentNode;
					if(parent && parent.tagName) {
						if(!_inlinePlaceholder) {
							_hiddenClass = inlineSt.hiddenClass;
							_inlinePlaceholder = _getEl(_hiddenClass);
							_hiddenClass = 'mfp-'+_hiddenClass;
						}
						// replace target inline element with placeholder
						_lastInlineElement = el.after(_inlinePlaceholder).detach().removeClass(_hiddenClass);
					}

					mfp.updateStatus('ready');
				} else {
					mfp.updateStatus('error', inlineSt.tNotFound);
					el = $('<div>');
				}

				item.inlineElement = el;
				return el;
			}

			mfp.updateStatus('ready');
			mfp._parseMarkup(template, {}, item);
			return template;
		}
	}
});

/*>>inline*/

/*>>ajax*/
var AJAX_NS = 'ajax',
	_ajaxCur,
	_removeAjaxCursor = function() {
		if(_ajaxCur) {
			$(document.body).removeClass(_ajaxCur);
		}
	},
	_destroyAjaxRequest = function() {
		_removeAjaxCursor();
		if(mfp.req) {
			mfp.req.abort();
		}
	};

$.magnificPopup.registerModule(AJAX_NS, {

	options: {
		settings: null,
		cursor: 'mfp-ajax-cur',
		tError: 'The content could not be loaded.'
	},

	proto: {
		initAjax: function() {
			mfp.types.push(AJAX_NS);
			_ajaxCur = mfp.st.ajax.cursor;

			_mfpOn(CLOSE_EVENT+'.'+AJAX_NS, _destroyAjaxRequest);
			_mfpOn('BeforeChange.' + AJAX_NS, _destroyAjaxRequest);
		},
		getAjax: function(item) {

			if(_ajaxCur) {
				$(document.body).addClass(_ajaxCur);
			}

			mfp.updateStatus('loading');

			var opts = $.extend({
				url: item.src,
				success: function(data, textStatus, jqXHR) {
					var temp = {
						data:data,
						xhr:jqXHR
					};

					_mfpTrigger('ParseAjax', temp);

					mfp.appendContent( $(temp.data), AJAX_NS );

					item.finished = true;

					_removeAjaxCursor();

					mfp._setFocus();

					setTimeout(function() {
						mfp.wrap.addClass(READY_CLASS);
					}, 16);

					mfp.updateStatus('ready');

					_mfpTrigger('AjaxContentAdded');
				},
				error: function() {
					_removeAjaxCursor();
					item.finished = item.loadError = true;
					mfp.updateStatus('error', mfp.st.ajax.tError.replace('%url%', item.src));
				}
			}, mfp.st.ajax.settings);

			mfp.req = $.ajax(opts);

			return '';
		}
	}
});

/*>>ajax*/

/*>>image*/
var _imgInterval,
	_getTitle = function(item) {
		if(item.data && item.data.title !== undefined)
			return item.data.title;

		var src = mfp.st.image.titleSrc;

		if(src) {
			if(typeof src === "function") {
				return src.call(mfp, item);
			} else if(item.el) {
				return item.el.attr(src) || '';
			}
		}
		return '';
	};

$.magnificPopup.registerModule('image', {

	options: {
		markup: '<div class="mfp-figure">'+
					'<div class="mfp-close"></div>'+
					'<figure>'+
						'<div class="mfp-img"></div>'+
						'<figcaption>'+
							'<div class="mfp-bottom-bar">'+
								'<div class="mfp-title"></div>'+
								'<div class="mfp-counter"></div>'+
							'</div>'+
						'</figcaption>'+
					'</figure>'+
				'</div>',
		cursor: 'mfp-zoom-out-cur',
		titleSrc: 'title',
		verticalFit: true,
		tError: 'The image could not be loaded.'
	},

	proto: {
		initImage: function() {
			var imgSt = mfp.st.image,
				ns = '.image';

			mfp.types.push('image');

			_mfpOn(OPEN_EVENT+ns, function() {
				if(mfp.currItem.type === 'image' && imgSt.cursor) {
					$(document.body).addClass(imgSt.cursor);
				}
			});

			_mfpOn(CLOSE_EVENT+ns, function() {
				if(imgSt.cursor) {
					$(document.body).removeClass(imgSt.cursor);
				}
				_window.off('resize' + EVENT_NS);
			});

			_mfpOn('Resize'+ns, mfp.resizeImage);
			if(mfp.isLowIE) {
				_mfpOn('AfterChange', mfp.resizeImage);
			}
		},
		resizeImage: function() {
			var item = mfp.currItem;
			if(!item || !item.img) return;

			if(mfp.st.image.verticalFit) {
				var decr = 0;
				// fix box-sizing in ie7/8
				if(mfp.isLowIE) {
					decr = parseInt(item.img.css('padding-top'), 10) + parseInt(item.img.css('padding-bottom'),10);
				}
				item.img.css('max-height', mfp.wH-decr);
			}
		},
		_onImageHasSize: function(item) {
			if(item.img) {

				item.hasSize = true;

				if(_imgInterval) {
					clearInterval(_imgInterval);
				}

				item.isCheckingImgSize = false;

				_mfpTrigger('ImageHasSize', item);

				if(item.imgHidden) {
					if(mfp.content)
						mfp.content.removeClass('mfp-loading');

					item.imgHidden = false;
				}

			}
		},

		/**
		 * Function that loops until the image has size to display elements that rely on it asap
		 */
		findImageSize: function(item) {

			var counter = 0,
				img = item.img[0],
				mfpSetInterval = function(delay) {

					if(_imgInterval) {
						clearInterval(_imgInterval);
					}
					// decelerating interval that checks for size of an image
					_imgInterval = setInterval(function() {
						if(img.naturalWidth > 0) {
							mfp._onImageHasSize(item);
							return;
						}

						if(counter > 200) {
							clearInterval(_imgInterval);
						}

						counter++;
						if(counter === 3) {
							mfpSetInterval(10);
						} else if(counter === 40) {
							mfpSetInterval(50);
						} else if(counter === 100) {
							mfpSetInterval(500);
						}
					}, delay);
				};

			mfpSetInterval(1);
		},

		getImage: function(item, template) {

			var guard = 0,

				imgSt = mfp.st.image,

				// image error handler
				onLoadError = function() {
					if(item) {
						item.img.off('.mfploader');
						if(item === mfp.currItem){
							mfp._onImageHasSize(item);
							mfp.updateStatus('error', imgSt.tError.replace('%url%', item.src) );
						}

						item.hasSize = true;
						item.loaded = true;
						item.loadError = true;
					}
				},

				// image load complete handler
				onLoadComplete = function() {
					if(item) {
						if (item.img[0].complete) {
							item.img.off('.mfploader');

							if(item === mfp.currItem){
								mfp._onImageHasSize(item);

								mfp.updateStatus('ready');
							}

							item.hasSize = true;
							item.loaded = true;

							_mfpTrigger('ImageLoadComplete');

						}
						else {
							// if image complete check fails 200 times (20 sec), we assume that there was an error.
							guard++;
							if(guard < 200) {
								setTimeout(onLoadComplete,100);
							} else {
								onLoadError();
							}
						}
					}
				};
				


			var el = template.find('.mfp-img');
			if(el.length) {
				var img = document.createElement('img');
				img.className = 'mfp-img';
				if(item.el && item.el.find('img').length) {
					img.alt = item.el.find('img').attr('alt');
				}
				item.img = $(img).on('load.mfploader', onLoadComplete).on('error.mfploader', onLoadError);
				img.src = item.src;

				// without clone() "error" event is not firing when IMG is replaced by new IMG
				// TODO: find a way to avoid such cloning
				if(el.is('img')) {
					item.img = item.img.clone();
				}

				img = item.img[0];
				if(img.naturalWidth > 0) {
					item.hasSize = true;
				} else if(!img.width) {
					item.hasSize = false;
				}
			}

			mfp._parseMarkup(template, {
				title: _getTitle(item),
				img_replaceWith: item.img
			}, item);

			mfp.resizeImage();

			if(item.hasSize) {
				if(_imgInterval) clearInterval(_imgInterval);

				if(item.loadError) {
					template.addClass('mfp-loading');
					mfp.updateStatus('error', imgSt.tError.replace('%url%', item.src) );
				} else {
					template.removeClass('mfp-loading');
					mfp.updateStatus('ready');
				}
				return template;
			}

			mfp.updateStatus('loading');
			item.loading = true;

			if(!item.hasSize) {
				item.imgHidden = true;
				template.addClass('mfp-loading');
				mfp.findImageSize(item);
			}

			return template;
		}
	}
});

/*>>image*/

/*>>zoom*/
var hasMozTransform,
	getHasMozTransform = function() {
		if(hasMozTransform === undefined) {
			hasMozTransform = document.createElement('p').style.MozTransform !== undefined;
		}
		return hasMozTransform;
	};

$.magnificPopup.registerModule('zoom', {

	options: {
		enabled: false,
		easing: 'ease-in-out',
		duration: 300,
		opener: function(element) {
			return element.is('img') ? element : element.find('img');
		}
	},

	proto: {

		initZoom: function() {
			var zoomSt = mfp.st.zoom,
				ns = '.zoom',
				image;

			if(!zoomSt.enabled || !mfp.supportsTransition) {
				return;
			}

			var duration = zoomSt.duration,
				getElToAnimate = function(image) {
					var newImg = image.clone().removeAttr('style').removeAttr('class').addClass('mfp-animated-image'),
						transition = 'all '+(zoomSt.duration/1000)+'s ' + zoomSt.easing,
						cssObj = {
							position: 'fixed',
							zIndex: 9999,
							left: 0,
							top: 0,
							'-webkit-backface-visibility': 'hidden'
						},
						t = 'transition';

					cssObj['-webkit-'+t] = cssObj['-moz-'+t] = cssObj['-o-'+t] = cssObj[t] = transition;

					newImg.css(cssObj);
					return newImg;
				},
				showMainContent = function() {
					mfp.content.css('visibility', 'visible');
				},
				openTimeout,
				animatedImg;

			_mfpOn('BuildControls'+ns, function() {
				if(mfp._allowZoom()) {

					clearTimeout(openTimeout);
					mfp.content.css('visibility', 'hidden');

					// Basically, all code below does is clones existing image, puts in on top of the current one and animated it

					image = mfp._getItemToZoom();

					if(!image) {
						showMainContent();
						return;
					}

					animatedImg = getElToAnimate(image);

					animatedImg.css( mfp._getOffset() );

					mfp.wrap.append(animatedImg);

					openTimeout = setTimeout(function() {
						animatedImg.css( mfp._getOffset( true ) );
						openTimeout = setTimeout(function() {

							showMainContent();

							setTimeout(function() {
								animatedImg.remove();
								image = animatedImg = null;
								_mfpTrigger('ZoomAnimationEnded');
							}, 16); // avoid blink when switching images

						}, duration); // this timeout equals animation duration

					}, 16); // by adding this timeout we avoid short glitch at the beginning of animation


					// Lots of timeouts...
				}
			});
			_mfpOn(BEFORE_CLOSE_EVENT+ns, function() {
				if(mfp._allowZoom()) {

					clearTimeout(openTimeout);

					mfp.st.removalDelay = duration;

					if(!image) {
						image = mfp._getItemToZoom();
						if(!image) {
							return;
						}
						animatedImg = getElToAnimate(image);
					}

					animatedImg.css( mfp._getOffset(true) );
					mfp.wrap.append(animatedImg);
					mfp.content.css('visibility', 'hidden');

					setTimeout(function() {
						animatedImg.css( mfp._getOffset() );
					}, 16);
				}

			});

			_mfpOn(CLOSE_EVENT+ns, function() {
				if(mfp._allowZoom()) {
					showMainContent();
					if(animatedImg) {
						animatedImg.remove();
					}
					image = null;
				}
			});
		},

		_allowZoom: function() {
			return mfp.currItem.type === 'image';
		},

		_getItemToZoom: function() {
			if(mfp.currItem.hasSize) {
				return mfp.currItem.img;
			} else {
				return false;
			}
		},

		// Get element postion relative to viewport
		_getOffset: function(isLarge) {
			var el;
			if(isLarge) {
				el = mfp.currItem.img;
			} else {
				el = mfp.st.zoom.opener(mfp.currItem.el || mfp.currItem);
			}

			var offset = el.offset();
			var paddingTop = parseInt(el.css('padding-top'),10);
			var paddingBottom = parseInt(el.css('padding-bottom'),10);
			offset.top -= ( $(window).scrollTop() - paddingTop );


			/*

			Animating left + top + width/height looks glitchy in Firefox, but perfect in Chrome. And vice-versa.

			 */
			var obj = {
				width: el.width(),
				// fix Zepto height+padding issue
				height: (_isJQ ? el.innerHeight() : el[0].offsetHeight) - paddingBottom - paddingTop
			};

			// I hate to do this, but there is no another option
			if( getHasMozTransform() ) {
				obj['-moz-transform'] = obj['transform'] = 'translate(' + offset.left + 'px,' + offset.top + 'px)';
			} else {
				obj.left = offset.left;
				obj.top = offset.top;
			}
			return obj;
		}

	}
});



/*>>zoom*/

/*>>iframe*/

var IFRAME_NS = 'iframe',
	_emptyPage = '//about:blank',

	_fixIframeBugs = function(isShowing) {
		if(mfp.currTemplate[IFRAME_NS]) {
			var el = mfp.currTemplate[IFRAME_NS].find('iframe');
			if(el.length) {
				// reset src after the popup is closed to avoid "video keeps playing after popup is closed" bug
				if(!isShowing) {
					el[0].src = _emptyPage;
				}

				// IE8 black screen bug fix
				if(mfp.isIE8) {
					el.css('display', isShowing ? 'block' : 'none');
				}
			}
		}
	};

$.magnificPopup.registerModule(IFRAME_NS, {

	options: {
		markup: '<div class="mfp-iframe-scaler">'+
					'<div class="mfp-close"></div>'+
					'<iframe class="mfp-iframe" src="//about:blank" frameborder="0" allowfullscreen></iframe>'+
				'</div>',

		srcAction: 'iframe_src',

		// we don't care and support only one default type of URL by default
		patterns: {
			youtube: {
				index: 'youtube.com',
				id: 'v=',
				src: '//www.youtube.com/embed/%id%?autoplay=1'
			},
			vimeo: {
				index: 'vimeo.com/',
				id: '/',
				src: '//player.vimeo.com/video/%id%?autoplay=1'
			},
			gmaps: {
				index: '//maps.google.',
				src: '%id%&output=embed'
			}
		}
	},

	proto: {
		initIframe: function() {
			mfp.types.push(IFRAME_NS);

			_mfpOn('BeforeChange', function(e, prevType, newType) {
				if(prevType !== newType) {
					if(prevType === IFRAME_NS) {
						_fixIframeBugs(); // iframe if removed
					} else if(newType === IFRAME_NS) {
						_fixIframeBugs(true); // iframe is showing
					}
				}// else {
					// iframe source is switched, don't do anything
				//}
			});

			_mfpOn(CLOSE_EVENT + '.' + IFRAME_NS, function() {
				_fixIframeBugs();
			});
		},

		getIframe: function(item, template) {
			var embedSrc = item.src;
			var iframeSt = mfp.st.iframe;

			$.each(iframeSt.patterns, function() {
				if(embedSrc.indexOf( this.index ) > -1) {
					if(this.id) {
						if(typeof this.id === 'string') {
							embedSrc = embedSrc.substr(embedSrc.lastIndexOf(this.id)+this.id.length, embedSrc.length);
						} else {
							embedSrc = this.id.call( this, embedSrc );
						}
					}
					embedSrc = this.src.replace('%id%', embedSrc );
					return false; // break;
				}
			});

			var dataObj = {};
			if(iframeSt.srcAction) {
				dataObj[iframeSt.srcAction] = embedSrc;
			}

			mfp._parseMarkup(template, dataObj, item);

			mfp.updateStatus('ready');

			return template;
		}
	}
});



/*>>iframe*/

/*>>gallery*/
/**
 * Get looped index depending on number of slides
 */
var _getLoopedId = function(index) {
		var numSlides = mfp.items.length;
		if(index > numSlides - 1) {
			return index - numSlides;
		} else  if(index < 0) {
			return numSlides + index;
		}
		return index;
	},
	_replaceCurrTotal = function(text, curr, total) {
		return text.replace(/%curr%/gi, curr + 1).replace(/%total%/gi, total);
	};

$.magnificPopup.registerModule('gallery', {

	options: {
		enabled: false,
		arrowMarkup: '<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',
		preload: [0,2],
		navigateByImgClick: true,
		arrows: true,

		tPrev: 'Previous (Left arrow key)',
		tNext: 'Next (Right arrow key)',
		tCounter: '%curr% of %total%',
		
		langDir: null,
		loop: true,
	},

	proto: {
		initGallery: function() {

			var gSt = mfp.st.gallery,
				ns = '.mfp-gallery';

			mfp.direction = true; // true - next, false - prev

			if(!gSt || !gSt.enabled ) return false;
			
			if (!gSt.langDir) {
				gSt.langDir = document.dir || 'ltr';
			}

			_wrapClasses += ' mfp-gallery';

			_mfpOn(OPEN_EVENT+ns, function() {

				if(gSt.navigateByImgClick) {
					mfp.wrap.on('click'+ns, '.mfp-img', function() {
						if(mfp.items.length > 1) {
							mfp.next();
							return false;
						}
					});
				}

				_document.on('keydown'+ns, function(e) {
					if (e.keyCode === 37) {
						if (gSt.langDir === 'rtl') mfp.next();
						else mfp.prev();
					} else if (e.keyCode === 39) {
						if (gSt.langDir === 'rtl') mfp.prev();
						else mfp.next();
					}
				});

				mfp.updateGalleryButtons();

			});

			_mfpOn('UpdateStatus'+ns, function(/*e, data*/) {
				mfp.updateGalleryButtons();
			});

			_mfpOn('UpdateStatus'+ns, function(e, data) {
				if(data.text) {
					data.text = _replaceCurrTotal(data.text, mfp.currItem.index, mfp.items.length);
				}
			});

			_mfpOn(MARKUP_PARSE_EVENT+ns, function(e, element, values, item) {
				var l = mfp.items.length;
				values.counter = l > 1 ? _replaceCurrTotal(gSt.tCounter, item.index, l) : '';
			});

			_mfpOn('BuildControls' + ns, function() {
				if(mfp.items.length > 1 && gSt.arrows && !mfp.arrowLeft) {

					var arrowLeftDesc, arrowRightDesc, arrowLeftAction, arrowRightAction;

					if (gSt.langDir === 'rtl') {
						arrowLeftDesc = gSt.tNext;
						arrowRightDesc = gSt.tPrev;
						arrowLeftAction = 'next';
						arrowRightAction = 'prev';
					} else {
						arrowLeftDesc = gSt.tPrev;
						arrowRightDesc = gSt.tNext;
						arrowLeftAction = 'prev';
						arrowRightAction = 'next';
					}

					var markup     = gSt.arrowMarkup,
					    arrowLeft  = mfp.arrowLeft = $( markup.replace(/%title%/gi, arrowLeftDesc).replace(/%action%/gi, arrowLeftAction).replace(/%dir%/gi, 'left') ).addClass(PREVENT_CLOSE_CLASS),
					    arrowRight = mfp.arrowRight = $( markup.replace(/%title%/gi, arrowRightDesc).replace(/%action%/gi, arrowRightAction).replace(/%dir%/gi, 'right') ).addClass(PREVENT_CLOSE_CLASS);

					if (gSt.langDir === 'rtl') {
						mfp.arrowNext = arrowLeft;
						mfp.arrowPrev = arrowRight;
					} else {
						mfp.arrowNext = arrowRight;
						mfp.arrowPrev = arrowLeft;
					}

					arrowLeft.on('click', function() {
						if (gSt.langDir === 'rtl') mfp.next();
						else mfp.prev();
					});
					arrowRight.on('click', function() {
						if (gSt.langDir === 'rtl') mfp.prev();
						else mfp.next();
					});

					mfp.container.append(arrowLeft.add(arrowRight));

				}
			});

			_mfpOn(CHANGE_EVENT+ns, function() {
				if(mfp._preloadTimeout) clearTimeout(mfp._preloadTimeout);

				mfp._preloadTimeout = setTimeout(function() {
					mfp.preloadNearbyImages();
					mfp._preloadTimeout = null;
				}, 16);
			});


			_mfpOn(CLOSE_EVENT+ns, function() {
				_document.off(ns);
				mfp.wrap.off('click'+ns);
				mfp.arrowRight = mfp.arrowLeft = null;
			});

		},
		next: function() {
			var newIndex = _getLoopedId(mfp.index + 1);
			if (!mfp.st.gallery.loop && newIndex === 0 ) return false;
			mfp.direction = true;
			mfp.index = newIndex;
			mfp.updateItemHTML();
		},
		prev: function() {
			var newIndex = mfp.index - 1;
			if (!mfp.st.gallery.loop && newIndex < 0) return false;
			mfp.direction = false;
			mfp.index = _getLoopedId(newIndex);
			mfp.updateItemHTML();
		},
		goTo: function(newIndex) {
			mfp.direction = (newIndex >= mfp.index);
			mfp.index = newIndex;
			mfp.updateItemHTML();
		},
		preloadNearbyImages: function() {
			var p = mfp.st.gallery.preload,
				preloadBefore = Math.min(p[0], mfp.items.length),
				preloadAfter = Math.min(p[1], mfp.items.length),
				i;

			for(i = 1; i <= (mfp.direction ? preloadAfter : preloadBefore); i++) {
				mfp._preloadItem(mfp.index+i);
			}
			for(i = 1; i <= (mfp.direction ? preloadBefore : preloadAfter); i++) {
				mfp._preloadItem(mfp.index-i);
			}
		},
		_preloadItem: function(index) {
			index = _getLoopedId(index);

			if(mfp.items[index].preloaded) {
				return;
			}

			var item = mfp.items[index];
			if(!item.parsed) {
				item = mfp.parseEl( index );
			}

			_mfpTrigger('LazyLoad', item);

			if(item.type === 'image') {
				item.img = $('<img class="mfp-img" />').on('load.mfploader', function() {
					item.hasSize = true;
				}).on('error.mfploader', function() {
					item.hasSize = true;
					item.loadError = true;
					_mfpTrigger('LazyLoadError', item);
				}).attr('src', item.src);
			}


			item.preloaded = true;
		},

		/**
		 * Show/hide the gallery prev/next buttons if we're at the start/end, if looping is turned off
		 * Added by Joloco for Veg
		 */
		updateGalleryButtons: function() {

			if ( !mfp.st.gallery.loop && typeof mfp.arrowPrev === 'object' && mfp.arrowPrev !== null) {

				if (mfp.index === 0) mfp.arrowPrev.hide();
				else mfp.arrowPrev.show();

				if (mfp.index === (mfp.items.length - 1)) mfp.arrowNext.hide();
				else mfp.arrowNext.show();

			}

		},

	}

});


/*>>gallery*/

/*>>retina*/

var RETINA_NS = 'retina';

$.magnificPopup.registerModule(RETINA_NS, {
	options: {
		replaceSrc: function(item) {
			return item.src.replace(/\.\w+$/, function(m) { return '@2x' + m; });
		},
		ratio: 1 // Function or number.  Set to 1 to disable.
	},
	proto: {
		initRetina: function() {
			if(window.devicePixelRatio > 1) {

				var st = mfp.st.retina,
					ratio = st.ratio;

				ratio = !isNaN(ratio) ? ratio : ratio();

				if(ratio > 1) {
					_mfpOn('ImageHasSize' + '.' + RETINA_NS, function(e, item) {
						item.img.css({
							'max-width': item.img[0].naturalWidth / ratio,
							'width': '100%'
						});
					});
					_mfpOn('ElementParse' + '.' + RETINA_NS, function(e, item) {
						item.src = st.replaceSrc(item, ratio);
					});
				}
			}

		}
	}
});

/*>>retina*/
 _checkInstance(); }));

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";

;// ./source/frontend/js/helper.js
var fluxHelper = {};

/**
 * Serialize Data
 * 
 * Transform a JavaScript object into a serialized string so it can be passed
 * into PHP and decoded as an array.
 *
 * @param {object} obj    Object.
 * @param {string} prefix Key.
 * @returns 
 */
fluxHelper.serializeData = function (obj, prefix) {
  var str = [];
  for (var p in obj) {
    if (obj.hasOwnProperty(p)) {
      var k = prefix ? prefix + '[' + p + ']' : p,
        v = obj[p];
      str.push(typeof v === 'object' ? fluxHelper.serializeData(v, k) : encodeURIComponent(k) + '=' + encodeURIComponent(v));
    }
  }
  return str.join('&');
};

/**
 * Get Field Value.
 * 
 * Get the value of a field, depending on its type.
 *
 * @param {object} field Field.
 * @returns 
 */
fluxHelper.getFieldValue = function (field) {
  var value = field.value; // @todo account for other field types here.

  return value;
};

/**
 * Do AJAX.
 * 
 * A simple native AJAX function.
 * 
 * @param {object} data        Data.
 * @param {function} onSuccess Success Function.
 * @param {function} onError   Error Function.
 */
fluxHelper.ajaxRequest = async function (data, onSuccess, onError) {
  await new Promise((resolve, reject) => {
    var request = new XMLHttpRequest();
    request.open('POST', wc_checkout_params.ajax_url);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.onload = function () {
      if (this.status >= 200 && this.status < 400) {
        // Success.
        resolve(this.response);
      } else {
        // Error.
        if (typeof onError === 'function') {
          reject('error');
        }
      }
    };
    request.onerror = function () {
      // Error.
      if (typeof onError === 'function') {
        reject('error');
      }
    };
    request.send(fluxHelper.serializeData(data));
  }).then(response => {
    onSuccess(response);
  }).catch(error => {
    if (typeof onError === 'function') {
      onError();
    } else {
      console.log(error);
    }
  });
};
fluxHelper.ajaxRequestWoo = async function (data, onSuccess, onError) {
  await new Promise((resolve, reject) => {
    var request = new XMLHttpRequest();
    var url = wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', data.action);

    // @todo: we get deprecation warnings when passing false, 
    // we need to transpile the whole thing into ESNext and use async await.
    request.open('POST', url);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.onload = function () {
      if (this.status >= 200 && this.status < 400) {
        // Success.
        resolve(this.response);
      } else {
        // Error.
        if (typeof onError === 'function') {
          reject('error');
        }
      }
    };
    request.onerror = function () {
      // Error.
      if (typeof onError === 'function') {
        reject('error');
      }
    };
    request.send(fluxHelper.serializeData(data));
  }).then(response => {
    onSuccess(response);
  }).catch(error => {
    onError(error);
  });
};

/**
 * Clean up the DOM instead of theme file override.
 */
fluxHelper.removeDomElements = function () {
  var loginToggle = document.querySelector('.woocommerce-form-login-toggle');
  var shopKeeperLogin = document.querySelector('.shopkeeper_checkout_login');
  if (loginToggle) {
    loginToggle.remove();
  }
  if (shopKeeperLogin) {
    shopKeeperLogin.remove();
  }
  fluxHelper.repositionNotices();
};

/**
 * Reposition Notices.
 * 
 * For some reason Woo does not always put the notices inside the wrapper, which breaks the layout. This fixes that.
 */
fluxHelper.repositionNotices = function () {
  var isModern = document.querySelectorAll('.flux-checkout--modern').length;
  var formNotice = document.querySelector('form.woocommerce-checkout > .woocommerce-NoticeGroup.woocommerce-NoticeGroup-checkout');
  var noticeWrapper = document.querySelector('.woocommerce-notices-wrapper');
  if (isModern && formNotice) {
    var error = formNotice.querySelector('.woocommerce-error');
    if (!error) {
      return;
    }
    var errorContainer = document.querySelector('.woocommerce > .woocommerce-notices-wrapper');
    errorContainer.append(error);
    formNotice.remove();
  }
  if (isModern && noticeWrapper) {
    jQuery('.woocommerce-notices-wrapper').prependTo('.flux-checkout__steps');
  }
};
fluxHelper.isModernCheckout = function () {
  return document.querySelectorAll('.flux-checkout--modern').length;
};
fluxHelper.isMobile = function () {
  const width = jQuery(window).width();
  return width <= 1024;
};
fluxHelper.debounce = function (func, timeout = 300) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      func.apply(this, args);
    }, timeout);
  };
};
fluxHelper.isTrue = function (value) {
  return value === '1' || value === true;
};
/* harmony default export */ const helper = (fluxHelper);
;// ./source/frontend/js/stepper.js


var fluxStepper = {
  steps_hash: flux_checkout_vars.steps
};

/**
 * Run.
 */
fluxStepper.init = function () {
  this.handeStepOnPageLoad();
  this.onNextClick();
  this.onStepperClick();
  window.addEventListener('hashchange', fluxStepper.onHashChange);
};
fluxStepper.handeStepOnPageLoad = function () {
  if (!window.location.hash) {
    window.location.hash = this.steps_hash[1];
    return;
  }
  fluxStepper.onHashChange();
};

/**
 * Validation on Step change.
 *
 * By default Woo does not provide inline validation messages.
 * We use AJAX to get the correct message and then trigger Woo validation.
 */
fluxStepper.onNextClick = function () {
  var steps = document.querySelectorAll('[data-step-next]');
  Array.from(steps).forEach(function (step) {
    step.addEventListener('click', async function (e) {
      e.preventDefault();
      fluxStepper.loadSpinner(helper.isModernCheckout());
      validation.clearErrorMessages();
      const fields = fluxStepper.getFields(step.closest('[data-step]'));
      var errorFields = await validation.checkFieldsForErrors(fields);
      if (errorFields) {
        return false;
      }
      var nextStepNumber = step.attributes['data-step-show'].value;
      var nextStep = document.querySelector('[data-step="' + nextStepNumber + '"]'); // ES5 Support.

      if (!nextStep) {
        return false;
      }

      // Only change the hash. Panels will be toggled by hashchange vent listener.
      window.location.hash = '#' + fluxStepper.steps_hash[nextStepNumber];

      // Woo trigger select2 reload.
      jQuery(document.body).trigger('country_to_state_changed');
      return false;
    });
  });
};
fluxStepper.onStepperClick = function () {
  var steppers = document.querySelectorAll('[data-stepper]');
  Array.from(steppers).forEach(function (stepper) {
    stepper.addEventListener('click', async function (e) {
      e.preventDefault();
      validation.clearErrorMessages();
      var hasErrors = false;
      var stepNumber = stepper.attributes['data-stepper'].value;
      var isActive = stepper.closest('[data-stepper-li]').classList.contains('selected');
      if (isActive) {
        return false;
      }

      // Check current step fields.
      if (stepNumber > 1) {
        const fields = fluxStepper.getFields(document.querySelector('[data-step="' + (stepNumber - 1) + '"]')); // ES5 Support.
        hasErrors = await validation.checkFieldsForErrors(fields);
      }
      if (hasErrors) {
        return false;
      }

      // Only change the hash. Panels will be toggled by hashchange event listener.
      window.location.hash = '#' + fluxStepper.steps_hash[stepNumber];

      // Woo trigger select2 reload.
      jQuery(document.body).trigger('country_to_state_changed');
      return false;
    });
  });
};

/**
 * Disable Next Steppers.
 *
 * @param {int} stepNumber The Step Number.
 */
fluxStepper.disableNextSteppers = function (stepNumber) {
  Array.from(document.querySelectorAll('[data-stepper-li]')).forEach(function (stepper) {
    if (stepNumber === stepper.attributes['data-stepper-li'].value) {
      stepper.classList.remove('complete');
    }
    if (stepNumber >= stepper.attributes['data-stepper-li'].value) {
      return;
    }
    stepper.classList.add('disabled');
    stepper.classList.remove('complete');
    stepper.querySelector('[data-stepper]').setAttribute('disabled', 'disabled');
    stepper.querySelector('[data-stepper]').setAttribute('aria-disabled', 'true');
  });
};

/**
 * Complete Previous Steps.
 *
 * @param {int} stepNumber The Step Number.
 */
fluxStepper.completePreviousSteppers = function (stepNumber) {
  Array.from(document.querySelectorAll('[data-stepper-li]')).forEach(function (stepper) {
    if (stepper.attributes['data-stepper-li'].value >= stepNumber) {
      return;
    }
    stepper.classList.add('complete');
  });
};

/**
 * Disable Next Steppers.
 *
 * @param {int} currentStepNumber The current step number.
 * @param {int} nextStepNumber The next step number.
 */
fluxStepper.switchStepper = function (currentStepNumber, nextStepNumber) {
  // Steppers.
  // var currentStepper = document.querySelector( `[data-stepper-li="${currentStepNumber}"]` );
  var currentStepper = document.querySelector('[data-stepper-li="' + currentStepNumber + '"]'); // ES5 Support
  // var nextStepper = document.querySelector( `[data-stepper-li="${nextStepNumber}"]` );
  var nextStepper = document.querySelector('[data-stepper-li="' + nextStepNumber + '"]'); // ES5 Support.

  // Handle Steppers.
  currentStepper.classList.remove('error');
  currentStepper.classList.remove('disabled');
  currentStepper.querySelector('button').removeAttribute('disabled');
  currentStepper.querySelector('button').removeAttribute('aria-disabled');
  currentStepper.classList.remove('selected');
  nextStepper.classList.remove('error');
  nextStepper.classList.remove('disabled');
  nextStepper.querySelector('button').removeAttribute('disabled');
  nextStepper.querySelector('button').removeAttribute('aria-disabled');
  nextStepper.classList.add('selected');
  fluxStepper.completePreviousSteppers(nextStepNumber);
};

/**
 * Switch Panels
 *
 * @param {int} currentStepNumber The current step number.
 * @param {int} nextStepNumber The next step number.
 */
fluxStepper.switchPanels = function (currentStepNumber, nextStepNumber) {
  var currentStep = document.querySelector('[data-step="' + currentStepNumber + '"]');
  var nextStep = document.querySelector('[data-step="' + nextStepNumber + '"]');
  currentStep.style.display = 'none';
  currentStep.setAttribute('aria-hidden', 'true');
  nextStep.style.display = '';
  nextStep.setAttribute('aria-hidden', 'false');
  window.scrollTo(0, 0);
};

/**
 * Get Fields.
 *
 * Get all the fields that are relevant to the current step.
 *
 * @param {Element} parent Parent Element.
 * @return {Array} Fields.
 */
fluxStepper.getFields = function (parent) {
  const allFields = parent.querySelectorAll('input, select, textarea');
  const accountFields = parent.querySelectorAll('.create-account input, .create-account select, .create-account textarea');
  const shippingFields = parent.querySelectorAll('.woocommerce-shipping-fields input, .woocommerce-shipping-fields select, .woocommerce-shipping-fields textarea');
  const billingFields = parent.querySelectorAll('.woocommerce-billing-fields input, .woocommerce-billing-fields select, .woocommerce-billing-fields textarea');
  const additionalFields = parent.querySelectorAll('.woocommerce-additional-fields input, .woocommerce-additional-fields select, .woocommerce-additional-fields textarea');
  const fields = [];
  Array.from(allFields).forEach(function (field) {
    if (!parent.querySelectorAll('input[name=createaccount]:checked').length && !parent.querySelectorAll('.create-account[style="display:block;"]').length && Array.from(accountFields).includes(field)) {
      return;
    }

    // Exclude shipping fields if shipping destination is billing, else exclude billing fields.
    if ('billing' === flux_checkout_vars.shipping_destination) {
      if (!parent.querySelectorAll('input[name=ship_to_different_address]:checked').length && Array.from(shippingFields).includes(field)) {
        return;
      }
    } else if ('shipping' === flux_checkout_vars.shipping_destination) {
      if (parent.querySelectorAll('input[name=billing_same_billing_address]:checked').length && Array.from(billingFields).includes(field)) {
        return;
      }
    }
    if (!parent.querySelectorAll('input[name=show_additional_fields]:checked').length && Array.from(additionalFields).includes(field)) {
      return;
    }

    // Don't validate these fields.
    if (['billing_phone_full_number', 'billing_phone_country_code'].includes(field.name)) {
      return;
    }
    fields.push(field);
  });
  return fields;
};
fluxStepper.onHashChange = async function (e) {
  if (!window.location.hash) {
    return;
  }
  var hash, parts, step, scroll_element, goingForward;
  hash = window.location.hash.replace('#', '');
  goingForward = fluxStepper.isHashGoingForward(e);
  if (hash.includes('|')) {
    parts = hash.split('|');
    step = parts[0];
    scroll_element = parts[1];
  } else {
    step = hash;
  }
  var nextStepper = document.querySelector('[data-hash="' + step + '"]');
  if (!nextStepper) {
    return;
  }
  var nextStepNumber = nextStepper.attributes['data-stepper'].value;
  var stepper = document.querySelector('.flux-stepper__step.selected .flux-stepper__button');
  var currentStepNumber = stepper.attributes['data-stepper'].value;
  var stepNumber = stepper.attributes['data-stepper'].value;
  var isActive = nextStepNumber === currentStepNumber;
  if (goingForward) {
    validation.clearErrorMessages();
  }
  if (isActive) {
    fluxStepper.scrollToElement(scroll_element);
    return false;
  }
  fluxStepper.switchPanels(stepNumber, nextStepNumber);
  fluxStepper.switchStepper(stepNumber, nextStepNumber);
  fluxStepper.scrollToElement(scroll_element);

  // Woo trigger select2 reload.
  jQuery(document.body).trigger('country_to_state_changed');

  // Trigger custom event.
  jQuery(document.body).trigger('flux_step_change');
  if (document.getElementById('billing_phone')) {
    document.getElementById('billing_phone').dispatchEvent(new Event('keyup'));
  }
};

/**
 * Load the Spinner.
 *
 * @param {bool} buttonSpinner If true, then it will show a small spinner within the navgation buttons.
 */
fluxStepper.loadSpinner = function (buttonSpinner) {
  jQuery('[data-step-next]').prop('disabled', true);
  if (buttonSpinner) {
    jQuery('[data-step-next]').addClass('flux-button--processing');
  } else {
    document.querySelector('.flux-checkout__spinner').style.display = 'block';
  }
};

/**
 * Remove the Spinner.
 */
fluxStepper.removeSpinner = function (buttonSpinner) {
  jQuery('[data-step-next]').prop('disabled', false).removeClass('flux-button--processing');
  document.querySelector('.flux-checkout__spinner').style.display = 'none';
};
fluxStepper.scrollToElement = function (scroll_element) {
  if (scroll_element && jQuery(`#${scroll_element}`).length) {
    jQuery('html, body').animate({
      scrollTop: jQuery(`#${scroll_element}`).offset().top - 60
    }, 'fast');
  }
};
fluxStepper.updateCustomFragments = function (fragments) {
  for (var selector in fragments) {
    if (jQuery(selector).length) {
      jQuery(selector).replaceWith(fragments[selector]);
    }
  }
};

/**
 * Should be called on hashchange event. It tells if we are navigating to the
 * next step by returning true.
 *
 * @param Event e Event Object.
 * @returns
 */
fluxStepper.isHashGoingForward = function (e) {
  if (!e) {
    return false;
  }
  var newUrl = new URL(e.newURL);
  var oldUrl = new URL(e.oldURL);
  var newHashIndex = fluxStepper.findHashIndex(newUrl.hash);
  var oldHashIndex = fluxStepper.findHashIndex(oldUrl.hash);
  return parseInt(newHashIndex) > parseInt(oldHashIndex);
};

/**
 * Return index of the provided step slug.
 * @param {*} step_slug
 * @returns
 */
fluxStepper.findHashIndex = function (step_slug) {
  step_slug = step_slug.replace('#', '');
  for (var idx in this.steps_hash) {
    if (this.steps_hash[idx] === step_slug) {
      return idx;
    }
  }
  return false;
};
/* harmony default export */ const stepper = (fluxStepper);
// EXTERNAL MODULE: ./node_modules/magnific-popup/dist/jquery.magnific-popup.js
var jquery_magnific_popup = __webpack_require__(732);
;// ./source/frontend/js/loginForm.js
var fluxLoginForm = {
  /**
   * Elements.
   */
  els: {
    $form: jQuery('.woocommerce-form-login')
  },
  /**
   * Init.
   */
  init: function () {
    jQuery(document).ready(function () {
      jQuery('.woocommerce-form-login>h2:first').append('<div class="flux-login-notice"></div>');
      jQuery('.woocommerce-form-login').on('submit', fluxLoginForm.onSubmit);
    });
  },
  /**
   * Handle submit event.
   *
   * @param {obj} e event. 
   */
  onSubmit: function (e) {
    e.preventDefault();
    var data = {
      action: 'flux_login',
      username: fluxLoginForm.els.$form.find('#username').val(),
      password: fluxLoginForm.els.$form.find('#password').val(),
      remember: fluxLoginForm.els.$form.find('#rememberme').val(),
      _wpnonce: fluxLoginForm.els.$form.find('#woocommerce-login-nonce').val()
    };
    fluxLoginForm.block();
    jQuery.post(flux_checkout_vars.ajax_url, data).done(function (data) {
      if (data.success) {
        fluxLoginForm.showGlobalNotice(flux_checkout_vars.i18n.login_successful, 'success');
        window.location.reload();
      } else {
        fluxLoginForm.showGlobalNotice(data.data.error, 'error');
      }
    }).fail(function () {
      fluxLoginForm.showGlobalNotice(flux_checkout_vars.i18n.error, 'error');
    }).always(function () {
      fluxLoginForm.unblock();
    });
  },
  /**
   * Show global notice for the login form.
   *
   * @param {string} msg  The message to display.
   * @param {string} type 'error' or 'success'.
   */
  showGlobalNotice: function (msg, type) {
    if (!type) {
      type = 'error';
    }
    var $notice = jQuery('.flux-login-notice');
    var typeClass = `flux-login-notice--${type}`;
    $notice.removeClass('flux-login-notice--success flux-login-notice--error flux-login-notice--info');
    $notice.addClass(typeClass);
    $notice.html(msg);
  },
  /**
   * Block spinner.
   */
  block: function () {
    fluxLoginForm.els.$form.block({
      message: null,
      overlayCSS: {
        background: '#fff',
        opacity: 0.6
      }
    });
  },
  /**
   * Unblock spinner.
   */
  unblock: function () {
    fluxLoginForm.els.$form.unblock();
  }
};
/* harmony default export */ const loginForm = (fluxLoginForm);
;// ./source/frontend/js/loginButtons.js


var fluxLoginButtons = {};

/**
 * Run.
 */
fluxLoginButtons.init = function () {
  this.onClick();

  /**
   * If auto-open class is present in the login for i.e. user has entered a wrong password,
   * Then open the login form automatically.
   */
  if (jQuery('.woocommerce-form-login').hasClass('woocommerce-form-login--auto-open')) {
    window.setTimeout(function () {
      fluxLoginButtons.openPopup();
    }, 1000);
  }
};

/**
 * Login Buttons On Click.
 * 
 * Handle the show and hide of the login form from a custom button.
 */
fluxLoginButtons.onClick = function () {
  // Remove the event listener added by WooCommerce, as it returns false,
  // causing our event listener to never run.
  setTimeout(() => {
    jQuery(document.body).off('click', 'a.showlogin');
  }, 100);
  jQuery(document).on('click', '[data-login], .showlogin', function (e) {
    e.preventDefault();
    fluxLoginButtons.openPopup();
  });
};

/**
 * Open popup.
 */
fluxLoginButtons.openPopup = function (auto_popup) {
  var billing_email = jQuery('#billing_email').val();
  if (billing_email) {
    jQuery('.woocommerce-form-login #username').val(billing_email).trigger('change');
  }
  if (auto_popup) {
    loginForm.showGlobalNotice(flux_checkout_vars.i18n.account_exists, 'info');
  }
  window.setTimeout(function () {
    jQuery('.woocommerce-form-login #password').focus().trigger('focus');
  }, 300);
  jQuery.magnificPopup.open({
    items: {
      src: '.woocommerce-form-login',
      type: 'inline'
    },
    prependTo: 'form.checkout'
  });
};
/* harmony default export */ const loginButtons = (fluxLoginButtons);
;// ./source/frontend/js/validation.js
/* global flux_checkout_vars */
/* eslint-disable no-var */



const fluxValidation = {};

/**
 * Run.
 */
fluxValidation.init = function () {
  this.onChange();
  jQuery(document.body).on('checkout_error', fluxValidation.onCheckoutError);
  jQuery(function () {
    // Offer to login if a user a user already exits with the matching email.
    if (jQuery('#billing_email').val() && fluxValidation.isValidEmail(jQuery('#billing_email').val())) {
      fluxValidation.checkFieldForErrors(document.getElementById('billing_email'));
    }
  });
};

/**
 * Validation on Change.
 *
 * By default Woo does not provide inline validation messages.
 * We use AJAX to get the correct message and then trigger Woo validation.
 */
fluxValidation.onChange = function () {
  const fields = document.querySelectorAll('input, select, textarea');
  Array.from(fields).forEach(function (field) {
    field.addEventListener('change', async function (e) {
      e.preventDefault();
      await fluxValidation.checkFieldForErrors(field);
      return false;
    });
  });
};

/**
 * Check Field for Errors.
 *
 * @param {Array} field Field.
 * @return {boolean}
 */
fluxValidation.checkFieldForErrors = async function (field) {
  var row = field.closest('.form-row');
  if (!row) {
    return false;
  }
  if (!row.attributes['data-label'] || !row.attributes['data-type']) {
    return false;
  }
  var value = helper.getFieldValue(field);
  var type = row.attributes['data-type'].value;
  var data = {
    action: 'flux_check_for_inline_error',
    args: {
      label: row.attributes['data-label'].value,
      required: row.classList.contains('required'),
      type: type
    },
    country: jQuery('#billing_country').val(),
    key: field.attributes.name.value,
    value: value
  };

  // Its too slow to trigger every field, so check the more advanced fields with ajax.
  if ('country' === type || 'postcode' === type || 'phone' === type || 'email' === type) {
    await helper.ajaxRequest(data, function (response) {
      var value = JSON.parse(response).data;
      var $row = jQuery(field).closest('.form-row');

      // Update the inline validation messages for the field.
      field.closest('.form-row').querySelector('.error').innerHTML = value.message;
      field.closest('.form-row').classList.remove('woocommerce-invalid');

      // Trigger Woo Validation.
      if (field.closest('.form-row').classList.contains('validate-required')) {
        jQuery(field).trigger('validate');
      }

      // If a custom message has been returned, mark the row as invalid.
      if (value.isCustom) {
        field.closest('.form-row').classList.add('woocommerce-invalid');
      }
      if ('dont_offer' !== flux_checkout_vars.allow_login_existing_user) {
        if ('info' === value.messageType) {
          if (!$row.find('.info').length) {
            $row.append('<span class="info" style="display:none"></span>');
          }
          const $span = $row.find('.info');
          $span.slideDown();
          $span.html(value.message);
          if ('inline_popup' === flux_checkout_vars.allow_login_existing_user) {
            loginButtons.openPopup(true);
          }
        } else {
          let $span = $row.find('.info');
          $span.slideUp();
        }
      }
    });
  } else {
    // Trigger Woo Validation.
    if (field.closest('.form-row').classList.contains('validate-required')) {
      jQuery(field).trigger('validate');
    }
  }
  var hasError = field.closest('.form-row').classList.contains('woocommerce-invalid');
  if (hasError) {
    stepper.disableNextSteppers(field.closest('[data-step]').attributes['data-step'].value);
  }
  fluxValidation.accessibleErrors();
  return hasError;
};

/**
 * Check Fields for Errors.
 *
 * @param {Array} fields Fields.
 * @return {boolean} Returns the fields with errors.
 */
fluxValidation.checkFieldsForErrors = async function (fields) {
  const inputs = {};
  const errorFields = [];

  // Return true if google address auto-complete field is present and empty.
  for (const field of fields) {
    if ('billing_address_search' === field.id) {
      if ('' === field.value.trim() && 'none' === jQuery('.woocommerce-billing-fields').css('display')) {
        field.closest('.form-row').classList.add('woocommerce-invalid');
        jQuery(field).trigger('validate');
        field.closest('.form-row').classList.add('woocommerce-invalid');
        stepper.removeSpinner();
        return true;
      }
    }
  }
  stepper.loadSpinner(true);
  // Get all the data so we can do an inline validation.
  Array.from(fields).forEach(function (field) {
    const row = field.closest('.form-row');
    if (!row) {
      return;
    }
    if (!row.attributes['data-label'] || !row.attributes['data-type']) {
      return;
    }
    const value = helper.getFieldValue(field);
    inputs[field?.attributes?.name?.value] = {
      args: {
        label: row.attributes['data-label'].value,
        required: row.classList.contains('required'),
        type: row.attributes['data-type'].value
      },
      country: document.getElementById('billing_country').value,
      key: field?.attributes?.name?.value,
      value
    };
  });
  const data = {
    action: 'flux_check_for_inline_errors',
    fields: inputs,
    post_data: jQuery('form.checkout').serialize()
  };
  await helper.ajaxRequest(data, function (response) {
    const messages = JSON.parse(response).data;

    // Update the inline validation messages for each field.
    Object.entries(messages).forEach(function (object) {
      const key = object[0];
      const value = object[1];
      const field = document.querySelector('[name="' + key + '"]');
      if (!field) {
        return;
      }

      // If this field is hidden by Conditional Field of Checkout Fields Manager plugin
      // then skip validation for this field.
      if (fluxValidation.isHiddenConditionalField(field)) {
        return;
      }
      field.closest('.form-row').querySelector('.error').innerHTML = value.message;
      field.closest('.form-row').classList.remove('woocommerce-invalid');

      // Trigger Woo Validation.
      if (field.closest('.form-row').classList.contains('validate-required')) {
        jQuery(field).trigger('validate');
        jQuery(field).trigger('flux_validate');
      }

      // If a custom message has been returned, mark the row as invalid.
      if (value.isCustom) {
        field.closest('.form-row').classList.add('woocommerce-invalid');
      }
      if (field.closest('.form-row').classList.contains('woocommerce-invalid')) {
        errorFields.push(jQuery(field).attr('id'));
      }
    });
    stepper.updateCustomFragments(messages.fragments);
  });
  fluxValidation.clearErrorMessages('data-flux-error');

  // Check password strength if set.
  const passwords = fields[0].closest('[data-step]').querySelectorAll('#account_password');
  Array.from(passwords).forEach(function (password) {
    if (password.closest('.woocommerce-account-fields').querySelector('#createaccount') && !password.closest('.woocommerce-account-fields').querySelector('#createaccount').checked) {
      return;
    }
    if (!password.value) {
      return;
    }
    if (wp.passwordStrength && !password.closest('.form-row').querySelectorAll('.woocommerce-password-strength.good, .woocommerce-password-strength.strong').length) {
      errorFields.push(password);
    }
  });
  if (errorFields.length) {
    const step = fields[0].closest('[data-step]').attributes['data-step'].value;
    // document.querySelector( `[data-stepper-li="${step}"]`).classList.add( 'error' );
    document.querySelector('[data-stepper-li="' + step + '"]').classList.add('error'); // ES5 Support.
    stepper.disableNextSteppers(step);
    fluxValidation.scrollToError();
    fluxValidation.validateSearchForm();
    fluxValidation.maybeShowShippingForm(errorFields);
  }
  fluxValidation.accessibleErrors();
  stepper.removeSpinner();
  return errorFields.length ? errorFields : false;
};

/**
 * Scroll to first error on page.
 */
fluxValidation.scrollToError = function () {
  const error = document.querySelectorAll('.woocommerce-invalid')[0];
  if (!error) {
    return;
  }
  error.scrollIntoView({
    behavior: 'smooth'
  });
};

/**
 * Accessible Errors.
 *
 * Add some accessibility classes to our errors to help those using accessibility tools.
 */
fluxValidation.accessibleErrors = function () {
  var fields = document.querySelectorAll('input, select, textarea');
  Array.from(fields).forEach(function (field) {
    var row = field.closest('.form-row');
    if (!row) {
      return;
    }
    var error = row.querySelector('.error');
    if (error) {
      error.setAttribute('aria-hidden', 'true');
      error.setAttribute('aria-live', 'off');
    }
    if (row.classList.contains('woocommerce-invalid')) {
      field.setAttribute('aria-invalid', 'true');
      if (error) {
        error.setAttribute('aria-hidden', 'false');
        error.setAttribute('aria-live', 'polite');
      }
    }
  });
};

/**
 * Display Global Notice.
 *
 * Render a global validation notice. Useful when an inline message is not possible.
 *
 * @param {string} message Message.
 * @param {string} type Type.
 * @param {string} format Format.
 */
fluxValidation.displayGlobalNotice = function (message, type, format, data) {
  // ES5 Support.
  if (!type) {
    type = 'error';
  }
  const alreadyHasErrorDom = fluxValidation.alreadyHasErrorDom(message);
  if (!format) {
    format = alreadyHasErrorDom ? 'html' : 'list';
  }
  var noticeArea = document.querySelectorAll('.woocommerce-notices-wrapper');
  if (!noticeArea) {
    return;
  }
  noticeArea = noticeArea[noticeArea.length - 1];
  fluxValidation.clearErrorMessages('data-flux-error');
  var noticeContainer = document.createElement('div');
  var noticeType = 'woocommerce-error';
  if ('error' !== type) {
    noticeType = 'woocommerce-message';
  }
  if ('info' === type) {
    noticeType = 'woocommerce-info';
  }
  if (typeof data === 'object' && !Array.isArray(data) && data !== null) {
    Object.entries(data).forEach(function (object) {
      var key = object[0];
      var value = object[1];
      noticeContainer.setAttribute(key, value);
    });
  }
  if ('list' === format) {
    noticeContainer.classList.add('woocommerce-NoticeGroup');
    noticeContainer.classList.add('woocommerce-NoticeGroup-checkout');
    var noticeContainerList = document.createElement('ul');
    noticeContainerList.setAttribute('role', 'alert');
    noticeContainerList.classList.add(noticeType);
    var noticeListItem = document.createElement('li');
    noticeListItem.innerHTML = message;
    noticeContainerList.append(noticeListItem);
    noticeContainer.append(noticeContainerList);
  } else if (alreadyHasErrorDom) {
    // No need to add the error classes if the WooCommerce error UL is already present.
    noticeContainer.innerHTML = message;
  } else {
    noticeContainer.setAttribute('role', 'alert');
    noticeContainer.classList.add(noticeType);
    noticeContainer.innerHTML = message;
  }
  noticeArea.append(noticeContainer);
};

/**
 * Does the HTML already have a WooCommerce error UL?
 *
 * @param {*} html
 * @returns
 */
fluxValidation.alreadyHasErrorDom = function (html) {
  if (!html) {
    return false;
  }
  let $element;
  try {
    $element = jQuery(html);
  } catch (error) {
    return false;
  }
  if ($element.length === 0) {
    return false;
  }
  return $element.is('ul') || $element.is('.wc-block-components-notice-banner');
};

/**
 * Clear error messages.
 *
 * @param {string} Group Clear error messages only for this group. Group is the name of the data-* attribute. Example data-flux-error.
 */
fluxValidation.clearErrorMessages = function (group) {
  jQuery('.woocommerce-notices-wrapper > div, .woocommerce-notices-wrapper ul').each(function () {
    // if group is provided, only remove the notices beloging to this group.
    if (group) {
      if (jQuery(this).attr(group)) {
        jQuery(this).remove();
      }
    } else {
      jQuery(this).remove();
    }
  });
  jQuery('.woocommerce-NoticeGroup').remove();
};

/**
 * Validate Search Form.
 *
 * If there is a search form error, display a message.
 */
fluxValidation.validateSearchForm = function () {
  const $useSameForBillingAddress = jQuery('#same-billing-address-input');
  if ($useSameForBillingAddress && $useSameForBillingAddress.prop('checked')) {
    return;
  }
  const addressSearches = document.querySelectorAll('#billing_address_search');
  Array.from(addressSearches).forEach(function (addressSearch) {
    const addressSection = addressSearch.closest('.woocommerce-billing-fields__wrapper').querySelector('.woocommerce-billing-fields');
    const style = window.getComputedStyle(addressSection);
    if (style.display === 'none') {
      // Remove previous notices.
      Array.from(addressSearch.closest('.form-row').querySelectorAll('.error')).forEach(function (error) {
        error.remove();
      });
      Array.from(addressSearch.closest('.form-row').querySelectorAll('.error')).forEach(function (error) {
        error.remove();
      });

      // Do inline notice.
      const row = addressSearch.closest('.form-row');
      row.classList.add('woocommerce-invalid');
      const error = document.createElement('span');
      error.setAttribute('aria-hidden', 'false');
      error.setAttribute('aria-live', 'polite');
      error.classList.add('error');
      error.innerHTML = flux_checkout_vars.i18n.errorAddressSearch;
      row.append(error);
    }
  });
};

/**
 * Display global errors on 'checkout_error' event.
 *
 * @param {object} e Event.
 * @param {string} data Error message in HTML format.
 */
fluxValidation.onCheckoutError = function (e, data) {
  /**
   * In modern checkout layout, we use CSS to hide the default error messages because that breaks the layout.
   * So we need to display our own.
   */
  if (helper.isModernCheckout() && data) {
    fluxValidation.displayGlobalNotice(jQuery(data).html(), 'error');
  }
};

/**
 * Is valid email?
 *
 * @param {string} email Email.
 * @return {boolean}
 */
fluxValidation.isValidEmail = function (email) {
  var pattern = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
  return email.match(pattern);
};

/**
 * Is this field purposfully marked as hidden by Checkout fields manager plugin.
 *
 * @param {*} field
 * @returns
 */
fluxValidation.isHiddenConditionalField = function (field) {
  var $row = jQuery(field).closest('.form-row');
  return $row.is(':hidden') && $row.hasClass('wooccm-conditional-child');
};

/**
 * Because of address autocomplete, the validation error on fields do not appear.
 * Display shipping form (manual) if 'Ship to a different address' is checked and address autocomplete
 *
 * @param {*} errorFields
 * @returns
 */
fluxValidation.maybeShowShippingForm = function (errorFields) {
  if (!jQuery('#ship-to-different-address-checkbox').is(':checked') || !errorFields) {
    return;
  }
  var showManualAddressFields = false;

  // If at least one of the fields is a shipping field.
  errorFields.forEach(field => {
    if (field.includes('shipping_')) {
      showManualAddressFields = true;
    }
  });
  if (showManualAddressFields) {
    jQuery('.shipping-address-search').slideUp();
    jQuery('.woocommerce-shipping-fields').slideDown();
  }
};
/* harmony default export */ const validation = (fluxValidation);
;// ./source/frontend/js/ui.js
var fluxUI = {};

/**
 * Slide down with JQuery and JS.
 * @param {object} element Element.
 */
fluxUI.slideDown = function (element) {
  if ('block' === element.style.display) {
    return;
  }
  element.style.height = 0;
  element.classList.add('slide-down');
  element.style.display = 'block';
  // element.style.height = `${element.scrollHeight}px`;
  element.style.height = element.scrollHeight + 'px'; // ES5 Support.
  setTimeout(function () {
    element.classList.remove('slide-down');
    element.style.height = '';
  }, 500);
};

/**
 * Slide up with JQuery and JS.
 * @param {object} element Element.
 */
fluxUI.slideUp = function (element) {
  if ('none' === element.style.display) {
    return;
  }

  // element.style.height = `${element.scrollHeight}px`;
  element.style.height = element.scrollHeight + 'px'; // ES5 Support.
  element.classList.add('slide-up');
  setTimeout(function () {
    element.style.height = 0;
  }, 10);
  setTimeout(function () {
    element.style.display = 'none';
    element.classList.remove('slide-up');
    element.style.height = '';
  }, 500);
};
/* harmony default export */ const ui = (fluxUI);
;// ./source/frontend/js/geocodeMap.js
var GeocodeMap = {
  $address: null,
  geocoder: null,
  map: null,
  /**
   * Init.
   */
  init: function () {
    jQuery(document).ready(function () {
      if (!jQuery('#flux-ty-map-canvas').length || !google) {
        return;
      }
      GeocodeMap.geocode();
    });
  },
  /**
   * Geocode.
   */
  geocode: function () {
    GeocodeMap.geocoder = new google.maps.Geocoder();
    GeocodeMap.geocoder.geocode({
      'address': jQuery('#flux-ty-map-canvas').data('address')
    }, GeocodeMap.setupMap);
  },
  /**
   * Setup Map.
   *
   * @param {*} results 
   * @param {*} status 
   * @returns 
   */
  setupMap: function (results, status) {
    if (status !== google.maps.GeocoderStatus.OK) {
      jQuery('#flux-ty-map-canvas').hide();
    }
    var options = {
      zoom: 12,
      center: results[0].geometry.location,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      zoomControl: false,
      mapTypeControl: false,
      scaleControl: false,
      streetViewControl: false,
      rotateControl: false,
      fullscreenControl: false
    };
    GeocodeMap.map = new google.maps.Map(document.getElementById("flux-ty-map-canvas"), options);
    var marker = new google.maps.Marker({
      map: GeocodeMap.map,
      position: results[0].geometry.location,
      title: jQuery('#flux-ty-map-canvas').data('address')
    });
  }
};
/* harmony default export */ const geocodeMap = (GeocodeMap);
;// external ["wp","hooks"]
const external_wp_hooks_namespaceObject = window["wp"]["hooks"];
;// ./source/frontend/js/addressSearch.js
/* global google, wc_checkout_params */
/* eslint-disable camelcase */





const isTrue = helper.isTrue;
const originalAttachShadow = Element.prototype.attachShadow;
Element.prototype.attachShadow = function (init) {
  if (this.localName === 'gmp-place-autocomplete') {
    const shadow = originalAttachShadow.call(this, {
      ...init,
      mode: 'open'
    });
    const style = document.createElement('style');
    /* This style should be applied to the autocomplete widget only on mobile */
    style.textContent = `
            @media (max-width: 450px) {
                /* do not apply black color when the widget if expanded on mobile view. */
                .widget-container input[aria-expanded="false"] {
                    color: black !important;
                }
            }
            @media (min-width: 450px) {
                .widget-container input {
                    color: black !important;
                }
                .widget-container input::placeholder{
                    color: #16110E !important;
                }

                .widget-container .focus-ring {
                    display: none !important;
                }

                .widget-container li[part="prediction-item"]:hover{
                    background-color: #f5f5f5 !important;
                    color: #16110E !important;
                }

                .dropdown > ul > li[aria-selected="true"] {
                    background-color: #f5f5f5 !important;
                    color: #16110E !important;
                }

                .widget-container .dropdown {
                    color: black !important;
                }

                .widget-container .dropdown>ul {
                    background-color: white !important;
                }

                .widget-container .dropdown>ul>li {
                    border-bottom: 1px solid #e5e5e5;
                }

                .place-autocomplete-element-place-name,
                .place-autocomplete-element-place-details {
                    color: black !important;
                }

                .place-autocomplete-element-place-result--matched {
                    color: black !important;
                }

                .input-container {
                    position: relative;
                }

                input[role=combobox] {
                    font-size: 16px !important;
                }

                input[role=combobox]::placeholder {
                    color: #5F6061 !important;
                }

                .widget-container .input-container button.clear-button {
                    height: 30px;
                    width: 30px;
                    margin-right: 7px;
                }
            }
		`;
    shadow.appendChild(style);
    return shadow;
  }
  return originalAttachShadow.call(this, init);
};
const fluxAddressSearch = {};

/**
 * Run.
 */
fluxAddressSearch.init = function () {
  this.watchSelect2();
  jQuery(document.body).trigger('country_to_state_changed');
};

/**
 * Watch select2 events.
 */
fluxAddressSearch.watchSelect2 = function () {
  jQuery('select.country_select').on('select2:open', function () {
    const $select2Above = jQuery('.select2-dropdown--above');
    if ($select2Above.length <= 0) {
      return;
    }
    const $fieldRow = jQuery(this).closest('.form-row');
    const $label = $fieldRow.find('label');
    $label.hide();
  }).on('select2:close', function () {
    const $fieldRow = jQuery(this).closest('.form-row');
    const $label = $fieldRow.find('label');
    $label.show();
  });
};

/**
 * Initialise the Address Search.
 */
fluxAddressSearch.initSearch = async function () {
  await google.maps.importLibrary('places');
  fluxAddressSearch.handleManualButtonClick();
  if ('undefined' === typeof google) {
    fluxAddressSearch.hideLookup();
    return;
  }
  geocodeMap.init();
  const billingAddressSearch = document.getElementById('billing_address_search');
  const shippingAddressSearch = document.getElementById('shipping_address_search');
  let placeAutocomplete;
  if (!billingAddressSearch && !shippingAddressSearch) {
    return;
  }

  // Create the autocomplete object, restricting the search to geographical
  // location types.
  let options = {};

  // Only limits to the first 5 countries, so if there's more than 5, it's
  // best to just ignore this setting.
  if (flux_checkout_vars.allowed_countries.length <= 5) {
    options.includedRegionCodes = flux_checkout_vars.allowed_countries;
  }
  options = wp.hooks.applyFilters('flux_google_autocomplete_options', options);
  if (billingAddressSearch) {
    placeAutocomplete = new google.maps.places.PlaceAutocompleteElement(options);
    window.billingPlaceAutocomplete = placeAutocomplete;
    fluxAddressSearch.setPlaceholder(placeAutocomplete, flux_checkout_vars.i18n.enter_location);
    billingAddressSearch.appendChild(placeAutocomplete);
    // When the user selects an address from the dropdown, populate the address
    // fields in the form.
    placeAutocomplete.addEventListener('gmp-select', async function ({
      placePrediction
    }) {
      const place = placePrediction.toPlace();
      await place.fetchFields({
        fields: ['displayName', 'formattedAddress', 'location', 'addressComponents']
      });
      fluxAddressSearch.fillInAddress(place, 'billing');
    });
    billingAddressSearch.addEventListener('focus', fluxAddressSearch.preventAutocomplete);
  }
  if (shippingAddressSearch) {
    const placeAutocompleteShippingAddress = new google.maps.places.PlaceAutocompleteElement(options);
    shippingAddressSearch.appendChild(placeAutocompleteShippingAddress);
    fluxAddressSearch.setPlaceholder(placeAutocompleteShippingAddress, flux_checkout_vars.i18n.enter_location);

    // When the user selects an address from the dropdown, populate the address
    // fields in the form.
    placeAutocompleteShippingAddress.addEventListener('gmp-select', async function ({
      placePrediction
    }) {
      const place = placePrediction.toPlace();
      await place.fetchFields({
        fields: ['displayName', 'formattedAddress', 'location', 'addressComponents']
      });
      fluxAddressSearch.fillInAddress(place, 'shipping');
    });
    shippingAddressSearch.addEventListener('focus', fluxAddressSearch.preventAutocomplete);
  }
};

/**
 * To be called by the callback function from the Google Maps API.
 */
window.flux_checkout_init_address_search = fluxAddressSearch.initSearch;

/**
 * Prevent Autocomplete on the Search Field.
 *
 * @param {Object} e Event.
 */
fluxAddressSearch.preventAutocomplete = function (e) {
  e.target.setAttribute('autocomplete', 'flux-address-autocomplete');
};

/**
 * Fill in the Billing Address Fields.
 *
 * @param {Object} place - Place.
 * @param {string} type  - Type.
 */
fluxAddressSearch.fillInAddress = function (place, type) {
  var _formattedComponents$, _formattedComponents$2;
  place = place.toJSON();
  const addressComponents = (0,external_wp_hooks_namespaceObject.applyFilters)('flux_address_components', place.addressComponents, place, type);
  if (wc_checkout_params.debug_mode) {
    // eslint-disable-next-line no-console
    console.log('place', place);
    // eslint-disable-next-line no-console
    console.log('addressComponents', addressComponents);
  }
  const countryCode = fluxAddressSearch.getAddressComponent(addressComponents, 'country', 'shortText');
  const formattedComponents = {
    street_address: fluxAddressSearch.getAddressComponent(addressComponents, 'street_address', 'longText'),
    street_number: fluxAddressSearch.getAddressComponent(addressComponents, 'street_number', 'shortText'),
    premise: fluxAddressSearch.getAddressComponent(addressComponents, 'premise', 'longText'),
    route: fluxAddressSearch.getAddressComponent(addressComponents, 'route', 'longText'),
    postal_town: fluxAddressSearch.getAddressComponent(addressComponents, 'postal_town', 'longText'),
    locality: fluxAddressSearch.getAddressComponent(addressComponents, 'locality', 'longText'),
    sublocality_level_1: fluxAddressSearch.getAddressComponent(addressComponents, 'sublocality_level_1', 'longText'),
    administrative_area_level_1: fluxAddressSearch.getAddressComponent(addressComponents, 'administrative_area_level_1', 'shortText'),
    administrative_area_level_2: fluxAddressSearch.getAddressComponent(addressComponents, 'administrative_area_level_2', 'longText'),
    country: fluxAddressSearch.getAddressComponent(addressComponents, 'country', 'longText'),
    postal_code: fluxAddressSearch.getAddressComponent(addressComponents, 'postal_code', 'shortText')
  };
  const addressComponentFormMap = {
    [`${type}_address_1`]: (_formattedComponents$ = formattedComponents.street_address) !== null && _formattedComponents$ !== void 0 ? _formattedComponents$ : '',
    [`${type}_street_number`]: (_formattedComponents$2 = formattedComponents.street_number) !== null && _formattedComponents$2 !== void 0 ? _formattedComponents$2 : '',
    [`${type}_city`]: formattedComponents.postal_town ? formattedComponents.postal_town : formattedComponents.locality,
    [`${type}_state`]: formattedComponents.administrative_area_level_1,
    [`${type}_postcode`]: formattedComponents.postal_code,
    [`${type}_country`]: formattedComponents.country
  };
  if (formattedComponents.route) {
    addressComponentFormMap[`${type}_address_1`] += formattedComponents.route;
  }
  if (!addressComponentFormMap[`${type}_city`] && formattedComponents.administrative_area_level_2 && formattedComponents.administrative_area_level_2 !== addressComponentFormMap[`${type}_country`]) {
    addressComponentFormMap[`${type}_city`] = formattedComponents.administrative_area_level_2;
  }
  if (formattedComponents.sublocality_level_1 && formattedComponents.street_number) {
    addressComponentFormMap[`${type}_street_number`] = formattedComponents.sublocality_level_1 + '/' + formattedComponents.street_number;
  }

  // When seperate street number setting is not enabled.
  if (!isTrue(flux_checkout_vars.separate_street_number)) {
    /**
     * We cannot always assume that street number comes before the address1.
     *
     * 1. In some countries like Netherlands, street number comes after the address1 (ex: Stroombaan 4, Netherlands)
     * So we prefer to use the displayName i.e. let Google Maps API handle the street number and address1 order.
     *
     * 2. But if formattedComponents.street_number is not available, it could be because user has searched for
     * postcode only (or City or state only). We cannot use displayName in this case because postcode goes to its own field.
     * So we use the street_number and address1 as is.
     */
    if (formattedComponents.street_number) {
      addressComponentFormMap[`${type}_address_1`] = place.displayName;
    } else {
      addressComponentFormMap[`${type}_address_1`] = formattedComponents.street_number + ' ' + addressComponentFormMap[`${type}_address_1`];
    }
  }

  // For Italy states we want to use administrative_area_level_2 instead of administrative_area_level_1.
  if ('Italy' === formattedComponents.country) {
    const state = formattedComponents.administrative_area_level_2;
    if (state) {
      addressComponentFormMap[`${type}_state`] = state;
    }
  }

  // For Mexico, we don't want to use the short_name here because it doesn't match with Woo's shortnames.
  if ('MX' === countryCode) {
    const state = fluxAddressSearch.getAddressComponent(addressComponents, 'administrative_area_level_1', 'long_name');
    if (state) {
      addressComponentFormMap[`${type}_state`] = state;
    }
  }
  if (jQuery(`#${type}_state`).is('input')) {
    const level1 = formattedComponents.administrative_area_level_1;
    const level2 = formattedComponents.administrative_area_level_2;
    const locality = formattedComponents.locality;
    let state;
    if (level2) {
      state = level2;
    } else if (level1 === locality) {
      state = '';
    } else {
      state = level1;
    }
    addressComponentFormMap[`${type}_state`] = state;
  }

  // For Taiwan, we want to use administrative_area_level_3 for city.
  if ('TW' === formattedComponents.country) {
    const state = formattedComponents.administrative_area_level_1;
    const city = formattedComponents.administrative_area_level_2;
    if (city) {
      addressComponentFormMap[`${type}_city`] = city;
    }
    if (state) {
      addressComponentFormMap[`${type}_state`] = state;
    }
  }
  for (const f in addressComponentFormMap) {
    if (f === `${type}_country`) {
      if (document.getElementById(f) === null) {
        return false;
      }
      const el = document.getElementById(f);
      const eltype = el.nodeName;
      if (eltype === 'SELECT') {
        for (let i = 0; i < el.options.length; i++) {
          if (el.options[i].text === addressComponentFormMap[f] || el.options[i].value === countryCode) {
            el.selectedIndex = i;
            const event = new CustomEvent('change');
            document.getElementById(f).dispatchEvent(event);
            document.getElementById(f).closest('.form-row').classList.add('is-dirty');
            break;
          }
        }
      }
    } else {
      if (document.getElementById(f) === null) {
        continue;
      }
      const el = document.getElementById(f);
      if (f === `${type}_state`) {
        fluxAddressSearch.handleStateField(el, addressComponents);
      } else {
        document.getElementById(f).value = wp.htmlEntities.decodeEntities(addressComponentFormMap[f]);
      }
      jQuery('#' + f).trigger('change').trigger('keydown');
      document.getElementById(f).closest('.form-row').classList.add('is-dirty');
    }
  }
  if (place.vicinity && place.vicinity !== addressComponentFormMap[`${type}_city`]) {
    jQuery(`#${type}_address_2`).val(place.vicinity);
  }
  const billingFields = document.querySelectorAll(`.woocommerce-${type}-fields`);
  if (!billingFields.length) {
    return;
  }
  const showManualBillingFieldsButtons = document.querySelectorAll(`.flux-address-button-wrapper--${type}-manual`);
  Array.from(billingFields).forEach(function (field) {
    field.style.display = 'block';
    Array.from(field.querySelectorAll('input, select')).forEach(function (input) {
      if (input.value) {
        input.parentElement.classList.add('is-dirty');
      } else {
        input.parentElement.classList.remove('is-dirty');
      }
      if (input.attributes.placeholder) {
        input.parentElement.classList.add('has-placeholder');
      } else {
        input.parentElement.classList.remove('has-placeholder');
      }
      if (input.parentElement.classList.contains('.validate-required')) {
        input.parentElement.classList.remove('is-invalid');
      }
    });
    const fieldWrapper = field.closest(`.woocommerce-${type}-fields__wrapper`);
    if (fieldWrapper) {
      fieldWrapper.querySelector(`.${type}-address-search`).style.display = 'none';
    }
  });
  Array.from(showManualBillingFieldsButtons).forEach(function (button) {
    button.closest(`.${type}-address-search`).style.display = 'none';
  });

  // Woo trigger select2 reload.
  jQuery(document.body).trigger('country_to_state_changed');
  jQuery('.country_to_state').trigger('change');
  validation.clearErrorMessages();
};

/**
 * Handle Manual Address clicks.
 */
fluxAddressSearch.handleManualButtonClick = function () {
  const showManualBillingFieldsButtons = document.querySelectorAll('.flux-address-button--billing-manual');
  const showManualShippingFieldsButtons = document.querySelectorAll('.flux-address-button--shipping-manual');
  const openBillingSearchFieldButtons = document.querySelectorAll('.flux-address-button--billing-lookup');
  const openShippingSearchFieldButtons = document.querySelectorAll('.flux-address-button--shipping-lookup');
  Array.from(showManualBillingFieldsButtons).forEach(function (button) {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const panel = e.target.closest('.billing-address-search').parentElement.querySelector('.billing-address-search + .woocommerce-billing-fields');
      if ('block' !== panel.style.display) {
        jQuery(panel).slideDown();
        jQuery(button.closest('.billing-address-search')).slideUp();
      } else {
        jQuery(panel).slideUp();
      }

      // Woo trigger select2 reload.
      jQuery(document.body).trigger('country_to_state_changed');
      return false;
    });
  });
  Array.from(openBillingSearchFieldButtons).forEach(function (button) {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      ui.slideUp(button.closest('.woocommerce-billing-fields'));
      ui.slideDown(button.closest('.woocommerce-billing-fields__wrapper').querySelector('.billing-address-search'));

      // Woo trigger select2 reload.
      jQuery(document.body).trigger('country_to_state_changed');
      return false;
    });
  });
  Array.from(showManualShippingFieldsButtons).forEach(function (button) {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const panel = e.target.closest('.shipping-address-search').parentElement.querySelector('.shipping-address-search + .woocommerce-shipping-fields');
      if ('block' !== panel.style.display) {
        jQuery(panel).slideDown();
        jQuery(button.closest('.shipping-address-search')).slideUp();
      } else {
        jQuery(panel).slideUp();
      }

      // Woo trigger select2 reload.
      jQuery(document.body).trigger('country_to_state_changed');
      return false;
    });
  });
  Array.from(openShippingSearchFieldButtons).forEach(function (button) {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      ui.slideUp(button.closest('.woocommerce-shipping-fields'));
      ui.slideDown(button.closest('.woocommerce-shipping-fields__wrapper').querySelector('.shipping-address-search'));

      // Woo trigger select2 reload.
      jQuery(document.body).trigger('country_to_state_changed');
      return false;
    });
  });
};
fluxAddressSearch.getAddressComponent = function (components, component, name) {
  if (!components) {
    return '';
  }
  if (!name) {
    name = 'longText';
  }
  for (const loopComponent of components) {
    if (loopComponent.types.includes(component)) {
      return loopComponent[name];
    }
  }
  return '';
};
fluxAddressSearch.handleStateField = function (el, addressComponents) {
  let longName = fluxAddressSearch.getAddressComponent(addressComponents, 'administrative_area_level_1', 'longText');
  let shortName = fluxAddressSearch.getAddressComponent(addressComponents, 'administrative_area_level_1', 'shortText');
  const countryCode = fluxAddressSearch.getAddressComponent(addressComponents, 'country', 'shortText');
  const preferLevel2ForTheseCountries = ['GB', 'PH'];
  if (preferLevel2ForTheseCountries.includes(countryCode)) {
    longName = fluxAddressSearch.getAddressComponent(addressComponents, 'administrative_area_level_2', 'longText');
    shortName = fluxAddressSearch.getAddressComponent(addressComponents, 'administrative_area_level_2', 'shortText');
  }
  fluxAddressSearch.setDropdownValue(el, shortName, longName);
  jQuery(document).one('country_to_state_changed', {
    field: el.id,
    // eslint-disable-next-line object-shorthand
    shortName: shortName,
    // eslint-disable-next-line object-shorthand
    longName: longName
  }, function (event) {
    window.setTimeout(() => {
      if (!event.data.field) {
        return;
      }
      fluxAddressSearch.setDropdownValue(document.getElementById(event.data.field), event.data.shortName, event.data.longName);
      jQuery('#' + event.data.field).trigger('change');
    }, 200);
  });
};

/**
 * Set value of selectElement. First try to match with the
 * option value, if not found then try option text.
 *
 * @param {HTMLSelectElement|HTMLInputElement} selectElement - Select element.
 * @param {string}                             value         - Value.
 * @param {string}                             text          - Text.
 * @return {boolean} - True if the value is set, false otherwise.
 */
fluxAddressSearch.setDropdownValue = function (selectElement, value, text = '') {
  if (!selectElement) {
    return false;
  }
  if ('INPUT' === selectElement.nodeName) {
    selectElement.value = value;
    return true;
  }
  if (!selectElement || !selectElement.options) {
    return false;
  }
  const options = selectElement.options;
  for (let i = 0; i < options.length; i++) {
    if (options[i].value === value) {
      selectElement.selectedIndex = i;
      return true;
    }
  }
  const textToMatch = text || value;
  for (let i = 0; i < options.length; i++) {
    if (options[i].text === textToMatch) {
      selectElement.selectedIndex = i;
      return true;
    }
  }
  return false;
};

/**
 * Hide Address lookup and show manual fields.
 */
fluxAddressSearch.hideLookup = function () {
  jQuery('.billing-address-search').hide();
  jQuery('.shipping-address-search').hide();
  jQuery('.woocommerce-billing-fields').show();
  jQuery('.woocommerce-shipping-fields').show();
};

/**
 * Set placeholder for the address search input.
 * @param {Element} element - Element.
 */
fluxAddressSearch.setPlaceholder = function (element) {
  // We have noticed that sometimes there are changes in the DOM which result in placeholder
  // not being set, we are looping through all the elements and setting the placeholder if it's an input element.
  Object.keys(element).forEach(key => {
    if (element[key]?.nodeType === 1 && element[key]?.nodeName?.toLowerCase() === 'input') {
      element[key]?.setAttribute('placeholder', flux_checkout_vars.i18n.enter_location);
    }
  });
};
/* harmony default export */ const addressSearch = (fluxAddressSearch);
;// ./source/frontend/js/components.js

var fluxComponents = {};

/**
 * Run.
 */
fluxComponents.init = function () {
  this.accountToggle();
  this.shippingToggle();
  this.orderNotesToggle();
  this.watchCountryChange();
  this.billingAddressToggle();
  document.querySelector('html').classList.remove('no-js');
};

/**
 * Enable the Account Toggle.
 */
fluxComponents.accountToggle = function () {
  jQuery(document).ready(function () {
    jQuery('.woocommerce-account-fields input#createaccount').unbind();
    Array.from(document.querySelectorAll('.woocommerce-account-fields input#createaccount')).forEach(function (checkbox) {
      checkbox.addEventListener('change', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var accountFields = e.target.closest('.woocommerce-account-fields').querySelector('div.create-account');
        if (!accountFields) {
          return false;
        }
        if (e.target.checked) {
          ui.slideDown(accountFields);
          accountFields.setAttribute('aria-hidden', 'false');
        } else {
          ui.slideUp(accountFields);
          accountFields.setAttribute('aria-hidden', 'true');
        }

        // Remove errors.
        setTimeout(function () {
          Array.from(accountFields.querySelectorAll('input, select, textarea')).forEach(function (field) {
            field.closest('.form-row').classList.remove('woocommerce-invalid');
          });
        }, 1);
        jQuery(document.body).trigger('country_to_state_changed');
        return false;
      });
    });
  });
};

/**
 * Enable the Shipping Toggle.
 */
fluxComponents.shippingToggle = function () {
  Array.from(document.querySelectorAll('#ship-to-different-address input')).forEach(function (checkbox) {
    checkbox.addEventListener('change', function (e) {
      e.preventDefault();
      var shippingAddressFields = e.target.closest('.woocommerce-shipping-fields__wrapper').querySelector('.shipping_address');
      if (e.target.checked) {
        ui.slideDown(shippingAddressFields);
        shippingAddressFields.setAttribute('aria-hidden', 'false');
      } else {
        ui.slideUp(shippingAddressFields);
        shippingAddressFields.setAttribute('aria-hidden', 'true');
      }
      jQuery(document.body).trigger('country_to_state_changed');
      return false;
    });
  });
};

/**
 * Enable the Order Notes Toggle.
 */
fluxComponents.orderNotesToggle = function () {
  Array.from(document.querySelectorAll('#show-additional-fields input')).forEach(function (checkbox) {
    checkbox.addEventListener('change', function (e) {
      e.preventDefault();
      var additionalFields = e.target.closest('.woocommerce-additional-fields__wrapper').querySelector('.woocommerce-additional-fields');
      if (e.target.checked) {
        ui.slideDown(additionalFields);
        additionalFields.setAttribute('aria-hidden', 'false');
      } else {
        ui.slideUp(additionalFields);
        additionalFields.setAttribute('aria-hidden', 'true');
      }
      jQuery(document.body).trigger('country_to_state_changed');
      return false;
    });
  });
};

/**
 * Change data-type value for the county/state field when country is changed.
 */
fluxComponents.onCountryChange = function () {
  window.setTimeout(function () {
    if (jQuery("#billing_state").length) {
      var tagName = jQuery("#billing_state").prop('tagName');
      if ('SELECT' === tagName) {
        jQuery("#billing_state").closest('.form-row').attr('data-type', 'select');
      } else {
        jQuery("#billing_state").closest('.form-row').attr('data-type', 'text');
        jQuery("#billing_state").attr('placeholder', '');
      }
    }
    if (jQuery("#shipping_state").length) {
      var tagName = jQuery("#shipping_state").prop('tagName');
      if ('SELECT' === tagName) {
        jQuery("#shipping_state").closest('.form-row').attr('data-type', 'select');
      } else {
        jQuery("#shipping_state").closest('.form-row').attr('data-type', 'text');
        jQuery("#shipping_state").attr('placeholder', '');
      }
    }
  });
};

/**
 * Watch country change.
 */
fluxComponents.watchCountryChange = function () {
  if (!jQuery('#billing_country').length) {
    fluxComponents.onCountryChange();
    return;
  }
  jQuery('#billing_country, #shipping_country').change(function () {
    fluxComponents.onCountryChange();
  });
  fluxComponents.onCountryChange();
};

/**
 * Toggle billing address when the ' Use same address for billing?' checkbox is changed.
 */
fluxComponents.billingAddressToggle = function () {
  jQuery('#same-billing-address-input').on('change', function () {
    if (jQuery('#same-billing-address-input').prop('checked')) {
      jQuery('.billing_address').hide();
    } else {
      jQuery('.billing_address').show();
    }
  });
  jQuery('#same-billing-address-input').trigger('change');
};
/* harmony default export */ const components = (fluxComponents);
;// ./source/frontend/js/coupon.js



var fluxCoupon = {};
fluxCoupon.init = function () {
  this.onButtonClick();
  this.onFormSubmit();
  this.onChange();
  var sideBar = document.querySelector('.flux-checkout--has-sidebar');
  (function ($, document) {
    $(document).ready(function () {
      if (sideBar) {
        $(document).on('keydown', '#coupon_code', function (e) {
          if (e.key === 'Enter' || e.keyCode === 13) {
            jQuery("[name=apply_coupon]").trigger('click');
            e.preventDefault();
          }
        });

        // Call removeCoupon after the WooCommerce event listener has been added.
        setTimeout(fluxCoupon.removeCoupon, 100);
      }
    });
  })(jQuery, document);
};

/**
 * Login Buttons On Click.
 * 
 * Handle the show and hide of the login form from a custom button.
 */
fluxCoupon.onButtonClick = function () {
  jQuery(document).on('click', '#enter_coupon_button', function (e) {
    e.preventDefault();
    jQuery(this).closest('.woocommerce-form-coupon__wrapper').find('.checkout_coupon').slideToggle();
  });
};
fluxCoupon.onFormSubmit = function () {
  jQuery(document).on('click', 'button[name=apply_coupon]', async function (e) {
    e.preventDefault();
    var $form = jQuery(this).closest('.woocommerce-form-coupon__wrapper');
    var $row = $form.find('.form-row');
    jQuery('.woocommerce-form-coupon__wrapper').find('.error, .success').remove();
    var data = {
      action: 'apply_coupon',
      coupon_code: $form.find('input[name="coupon_code"]').val(),
      billing_email: jQuery('#billing_email').val(),
      security: wc_checkout_params.apply_coupon_nonce
    };
    stepper.loadSpinner();
    let onError = function (err) {};
    await helper.ajaxRequestWoo(data, function (response) {
      var message = response.replace(/(<([^>]+)>)/gi, '');
      if (response.includes('woocommerce-error')) {
        $row.addClass('woocommerce-invalid');
        $row.eq(0).append(`<div class='error' aria-hidden='false' aria-live='polite'>${message}</div>`);
      } else {
        jQuery(document.body).trigger('update_checkout', {
          update_shipping_method: false
        });
        jQuery(document.body).one('updated_checkout', function () {
          jQuery('.woocommerce-form-coupon__inner .form-row-first').append(`<div class="success" aria-hidden="false" aria-live="polite">${message}</div>`);
        });
      }
    }, onError);
    stepper.removeSpinner();
    return false;
  });
};
fluxCoupon.onChange = function () {
  jQuery(document).on('keyup', '#coupon_code', function () {
    var $btn = jQuery(this).closest('.checkout_coupon').find('.flux-coupon-button');
    if (jQuery(this).val().trim()) {
      $btn.removeClass('flux-coupon-button--disabled');
    } else {
      $btn.addClass('flux-coupon-button--disabled');
    }
  });
};
fluxCoupon.removeCoupon = function (e) {
  // Remove WooCommerce's event listener.
  jQuery(document.body).off('click', '.woocommerce-remove-coupon');
  jQuery(document.body).on('click', '.woocommerce-remove-coupon', function (e) {
    e.preventDefault();
    var container = jQuery(this).parents('.woocommerce-checkout-review-order'),
      coupon = jQuery(this).data('coupon');
    container.addClass('processing').block({
      message: null,
      overlayCSS: {
        background: '#fff',
        opacity: 0.6
      }
    });
    var data = {
      security: wc_checkout_params.remove_coupon_nonce,
      coupon: coupon
    };
    jQuery.ajax({
      type: 'POST',
      url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'remove_coupon'),
      data: data,
      success: function (code) {
        jQuery('.woocommerce-error, .woocommerce-message').remove();
        jQuery('.woocommerce-form-coupon__wrapper').find('.error, .success').remove();
        container.removeClass('processing').unblock();
        if (code) {
          jQuery(document.body).trigger('removed_coupon_in_checkout', [data.coupon]);
          jQuery(document.body).trigger('update_checkout', {
            update_shipping_method: false
          });
          jQuery(document.body).one('updated_checkout', function () {
            jQuery('.woocommerce-form-coupon__inner .form-row-first').append(`<div class="success" aria-hidden="false" aria-live="polite">${flux_checkout_vars.i18n.coupon_success}</div>`);
          });

          // Remove coupon code from coupon field
          jQuery('form.checkout_coupon').find('input[name="coupon_code"]').val('');
        }
      },
      error: function (jqXHR) {
        if (wc_checkout_params.debug_mode) {
          /* jshint devel: true */
          console.log(jqXHR.responseText);
        }
      },
      dataType: 'html'
    });
  });
};
/* harmony default export */ const coupon = (fluxCoupon);
;// ./source/frontend/js/compatibility.js


var fluxCompatibility = {};
fluxCompatibility.init = function () {
  this.compatSalesBooster();
  this.ie11PasswordStrength();
  this.compatDeliverySlots();
  this.compatPaymentPluginsStripe();
};

/**
 * Add compatibility with Sales Booster.
 */
fluxCompatibility.compatSalesBooster = function () {
  Array.from(document.querySelectorAll('[data-iconic-wsb-checkout-bump-trigger]')).forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
      jQuery('[data-iconic-wsb-checkout-bump-trigger]').trigger('change');
    });
  });
};
fluxCompatibility.compatDeliverySlots = function () {
  // setTimeOut because we want our event listener to run after wc_checkout_form::validate_field().
  window.setTimeout(function () {
    jQuery('#jckwds-delivery-date, #jckwds-delivery-time').on('validate', function (e) {
      if ('1' === jQuery('[name=iconic-wds-fields-hidden]').val()) {
        jQuery(e.target).closest('.form-row').removeClass('woocommerce-invalid');
        e.stopPropagation();
      }
    });
  });
};

/**
 * Add compatibility with Stripe plugin by Payment Plugins.
 */
fluxCompatibility.compatPaymentPluginsStripe = function () {
  jQuery(document.body).on('wc_stripe_error_message', function (e, msg) {
    // show error message.
    if (helper.isModernCheckout() && msg) {
      validation.displayGlobalNotice(jQuery(msg).text(), 'error');
    }
  });
};

/**
 *  IE11 Compatibility.
 */
if (!('remove' in Element.prototype)) {
  Element.prototype.remove = function () {
    if (this.parentNode) {
      this.parentNode.removeChild(this);
    }
  };
}
if (!Array.from) {
  Array.from = function () {
    var toStr = Object.prototype.toString;
    var isCallable = function (fn) {
      return typeof fn === 'function' || toStr.call(fn) === '[object Function]';
    };
    var toInteger = function (value) {
      var number = Number(value);
      if (isNaN(number)) {
        return 0;
      }
      if (number === 0 || !isFinite(number)) {
        return number;
      }
      return (number > 0 ? 1 : -1) * Math.floor(Math.abs(number));
    };
    var maxSafeInteger = Math.pow(2, 53) - 1;
    var toLength = function (value) {
      var len = toInteger(value);
      return Math.min(Math.max(len, 0), maxSafeInteger);
    };

    // The length property of the from method is 1.
    return function from(arrayLike /*, mapFn, thisArg */) {
      // 1. Let C be the this value.
      var C = this;

      // 2. Let items be ToObject(arrayLike).
      var items = Object(arrayLike);

      // 3. ReturnIfAbrupt(items).
      if (arrayLike == null) {
        throw new TypeError("Array.from requires an array-like object - not null or undefined");
      }

      // 4. If mapfn is undefined, then let mapping be false.
      var mapFn = arguments.length > 1 ? arguments[1] : void undefined;
      var T;
      if (typeof mapFn !== 'undefined') {
        // 5. else
        // 5. a If IsCallable(mapfn) is false, throw a TypeError exception.
        if (!isCallable(mapFn)) {
          throw new TypeError('Array.from: when provided, the second argument must be a function');
        }

        // 5. b. If thisArg was supplied, let T be thisArg; else let T be undefined.
        if (arguments.length > 2) {
          T = arguments[2];
        }
      }

      // 10. Let lenValue be Get(items, "length").
      // 11. Let len be ToLength(lenValue).
      var len = toLength(items.length);

      // 13. If IsConstructor(C) is true, then
      // 13. a. Let A be the result of calling the [[Construct]] internal method of C with an argument list containing the single item len.
      // 14. a. Else, Let A be ArrayCreate(len).
      var A = isCallable(C) ? Object(new C(len)) : new Array(len);

      // 16. Let k be 0.
      var k = 0;
      // 17. Repeat, while k < len… (also steps a - h)
      var kValue;
      while (k < len) {
        kValue = items[k];
        if (mapFn) {
          A[k] = typeof T === 'undefined' ? mapFn(kValue, k) : mapFn.call(T, kValue, k);
        } else {
          A[k] = kValue;
        }
        k += 1;
      }
      // 18. Let putStatus be Put(A, "length", len, true).
      A.length = len;
      // 20. Return A.
      return A;
    };
  }();
}
if (!Element.prototype.matches) {
  Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
}
if (!Element.prototype.closest) {
  Element.prototype.closest = function (s) {
    var el = this;
    do {
      if (Element.prototype.matches.call(el, s)) {
        return el;
      }
      el = el.parentElement || el.parentNode;
    } while (el !== null && el.nodeType === 1);
    return null;
  };
}
if (!Object.entries) {
  Object.entries = function (obj) {
    var ownProps = Object.keys(obj),
      i = ownProps.length,
      resArray = new Array(i); // preallocate the Array
    while (i--) {
      resArray[i] = [ownProps[i], obj[ownProps[i]]];
    }
    return resArray;
  };
}
if (!String.prototype.includes) {
  String.prototype.includes = function (search, start) {
    'use strict';

    if (typeof start !== 'number') {
      start = 0;
    }
    if (start + search.length > this.length) {
      return false;
    } else {
      return this.indexOf(search, start) !== -1;
    }
  };
}
if (!Array.prototype.includes) {
  Object.defineProperty(Array.prototype, "includes", {
    enumerable: false,
    value: function (obj) {
      var newArr = this.filter(function (el) {
        return el === obj;
      });
      return newArr.length > 0;
    }
  });
}
(function (arr) {
  arr.forEach(function (item) {
    if (item.hasOwnProperty('prepend')) {
      return;
    }
    Object.defineProperty(item, 'prepend', {
      configurable: true,
      enumerable: true,
      writable: true,
      value: function prepend() {
        var argArr = Array.prototype.slice.call(arguments),
          docFrag = document.createDocumentFragment();
        argArr.forEach(function (argItem) {
          var isNode = argItem instanceof Node;
          docFrag.appendChild(isNode ? argItem : document.createTextNode(String(argItem)));
        });
        this.insertBefore(docFrag, this.firstChild);
      }
    });
  });
})([Element.prototype, Document.prototype, DocumentFragment.prototype]);
(function () {
  if (typeof window.CustomEvent === "function") {
    return false;
  }
  function CustomEvent(event, params) {
    params = params || {
      bubbles: false,
      cancelable: false,
      detail: null
    };
    var evt = document.createEvent('CustomEvent');
    evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
    return evt;
  }
  window.CustomEvent = CustomEvent;
})();

/**
 * Add support for IE11 password strength.
 */
fluxCompatibility.ie11PasswordStrength = function () {
  if ('undefined' === typeof wp) {
    window.wp = {};
  }
  if ('undefined' === wp.i18n) {
    /* global wp, pwsL10n, wc_password_strength_meter_params */

    wp.passwordStrength = {
      /**
       * Determines the strength of a given password.
       *
       * Compares first password to the password confirmation.
       *
       * @since 3.7.0
       *
       * @param {string} password1       The subject password.
       * @param {Array}  disallowedList An array of words that will lower the entropy of
       *                                 the password.
       * @param {string} password2       The password confirmation.
       *
       * @return {number} The password strength score.
       */
      meter: function (password1, disallowedList, password2) {
        if (!Array.isArray(disallowedList)) disallowedList = [disallowedList.toString()];
        if (password1 != password2 && password2 && password2.length > 0) return 5;
        if ('undefined' === typeof window.zxcvbn) {
          // Password strength unknown.
          return -1;
        }
        var result = zxcvbn(password1, disallowedList);
        return result.score;
      },
      /**
       * Builds an array of words that should be penalized.
       *
       * Certain words need to be penalized because it would lower the entropy of a
       * password if they were used. The disallowedList is based on user input fields such
       * as username, first name, email etc.
       *
       * @since 3.7.0
       * @deprecated 5.5.0 Use {@see 'userInputDisallowedList()'} instead.
       *
       * @return {string[]} The array of words to be disallowed.
       */
      userInputBlacklist: function () {
        window.console.log(sprintf(/* translators: 1: Deprecated function name, 2: Version number, 3: Alternative function name. */
        __('%1$s is deprecated since version %2$s! Use %3$s instead. Please consider writing more inclusive code.'), 'wp.passwordStrength.userInputBlacklist()', '5.5.0', 'wp.passwordStrength.userInputDisallowedList()'));
        return wp.passwordStrength.userInputDisallowedList();
      },
      /**
       * Builds an array of words that should be penalized.
       *
       * Certain words need to be penalized because it would lower the entropy of a
       * password if they were used. The disallowed list is based on user input fields such
       * as username, first name, email etc.
       *
       * @since 5.5.0
       *
       * @return {string[]} The array of words to be disallowed.
       */
      userInputDisallowedList: function () {
        var i,
          userInputFieldsLength,
          rawValuesLength,
          currentField,
          rawValues = [],
          disallowedList = [],
          userInputFields = ['user_login', 'first_name', 'last_name', 'nickname', 'display_name', 'email', 'url', 'description', 'weblog_title', 'admin_email'];

        // Collect all the strings we want to disallow.
        rawValues.push(document.title);
        rawValues.push(document.URL);
        userInputFieldsLength = userInputFields.length;
        for (i = 0; i < userInputFieldsLength; i++) {
          currentField = jQuery('#' + userInputFields[i]);
          if (0 === currentField.length) {
            continue;
          }
          rawValues.push(currentField[0].defaultValue);
          rawValues.push(currentField.val());
        }

        /*
         * Strip out non-alphanumeric characters and convert each word to an
         * individual entry.
         */
        rawValuesLength = rawValues.length;
        for (i = 0; i < rawValuesLength; i++) {
          if (rawValues[i]) {
            disallowedList = disallowedList.concat(rawValues[i].replace(/\W/g, ' ').split(' '));
          }
        }

        /*
         * Remove empty values, short words and duplicates. Short words are likely to
         * cause many false positives.
         */
        disallowedList = jQuery.grep(disallowedList, function (value, key) {
          if ('' === value || 4 > value.length) {
            return false;
          }
          return jQuery.inArray(value, disallowedList) === key;
        });
        return disallowedList;
      }
    };

    // Backward compatibility.

    /**
     * Password strength meter function.
     *
     * @since 2.5.0
     * @deprecated 3.7.0 Use wp.passwordStrength.meter instead.
     *
     * @global
     *
     * @type {wp.passwordStrength.meter}
     */
    window.passwordStrength = wp.passwordStrength.meter;

    /**
     * Password Strength Meter class.
     */
    var wc_password_strength_meter = {
      /**
       * Initialize strength meter actions.
       */
      init: function () {
        Array.from(document.querySelectorAll('form.checkout #account_password')).forEach(function (password) {
          password.addEventListener('keyup', wc_password_strength_meter.strengthMeter);
        });
      },
      /**
       * Strength Meter.
       */
      strengthMeter: function () {
        var wrapper = jQuery('form.register, form.checkout, form.edit-account, form.lost_reset_password'),
          submit = jQuery('button[type="submit"]', wrapper),
          field = jQuery('#reg_password, #account_password, #password_1', wrapper),
          strength = 1,
          fieldValue = field.val(),
          stop_checkout = !wrapper.is('form.checkout'); // By default is disabled on checkout.

        wc_password_strength_meter.includeMeter(wrapper, field);
        strength = wc_password_strength_meter.checkPasswordStrength(wrapper, field);

        // Allow password strength meter stop checkout.
        if (wc_password_strength_meter_params.stop_checkout) {
          stop_checkout = true;
        }
        if (fieldValue.length > 0 && strength < wc_password_strength_meter_params.min_password_strength && -1 !== strength && stop_checkout) {
          submit.attr('disabled', 'disabled').addClass('disabled');
        } else {
          submit.prop('disabled', false).removeClass('disabled');
        }
      },
      /**
       * Include meter HTML.
       *
       * @param {Object} wrapper
       * @param {Object} field
       */
      includeMeter: function (wrapper, field) {
        var meter = wrapper.find('.woocommerce-password-strength');
        if ('' === field.val()) {
          meter.hide();
          jQuery(document.body).trigger('wc-password-strength-hide');
        } else if (0 === meter.length) {
          field.after('<div class="woocommerce-password-strength" aria-live="polite"></div>');
          jQuery(document.body).trigger('wc-password-strength-added');
        } else {
          meter.show();
          jQuery(document.body).trigger('wc-password-strength-show');
        }
      },
      /**
       * Check password strength.
       *
       * @param {Object} field
       *
       * @return {Int}
       */
      checkPasswordStrength: function (wrapper, field) {
        var meter = wrapper.find('.woocommerce-password-strength'),
          hint = wrapper.find('.woocommerce-password-hint'),
          hint_html = '<small class="woocommerce-password-hint">' + wc_password_strength_meter_params.i18n_password_hint + '</small>',
          strength = wp.passwordStrength.meter(field.val(), wp.passwordStrength.userInputDisallowedList()),
          error = '';

        // Reset.
        meter.removeClass('short bad good strong');
        hint.remove();
        if (meter.is(':hidden')) {
          return strength;
        }

        // Error to append
        if (strength < wc_password_strength_meter_params.min_password_strength) {
          error = ' - ' + wc_password_strength_meter_params.i18n_password_error;
        }
        switch (strength) {
          case 0:
            meter.addClass('short').html(pwsL10n['short'] + error);
            meter.after(hint_html);
            break;
          case 1:
            meter.addClass('bad').html(pwsL10n.bad + error);
            meter.after(hint_html);
            break;
          case 2:
            meter.addClass('bad').html(pwsL10n.bad + error);
            meter.after(hint_html);
            break;
          case 3:
            meter.addClass('good').html(pwsL10n.good + error);
            break;
          case 4:
            meter.addClass('strong').html(pwsL10n.strong + error);
            break;
          case 5:
            meter.addClass('short').html(pwsL10n.mismatch);
            break;
        }
        return strength;
      }
    };
    wc_password_strength_meter.init();
  }
};
/* harmony default export */ const compatibility = (fluxCompatibility);
;// ./source/frontend/js/cart.js
/* global flux_checkout_vars */
/* eslint-disable camelcase */



const fluxCart = {};
fluxCart.init = function () {
  fluxCart.removeControls();
  fluxCart.quantityControls();
  fluxCart.moveShippingRow();
};
fluxCart.runOnce = function () {
  fluxCart.orderSummaryToggle(true);
  const header = document.querySelector('.flux-checkout__sidebar-header');
  if (header) {
    header.addEventListener('click', fluxCart.orderSummaryToggle);
  }
  jQuery(window).on('resize', fluxCart.orderSummaryResize);
};

/**
 * Remove button.
 */
fluxCart.removeControls = function () {
  jQuery(document).on('click', '.flux-checkout__remove-link a.remove', function (e) {
    e.preventDefault();
    jQuery(this).closest('.cart_item').find('input').val(0);
    jQuery('body').trigger('update_checkout');
  });
};

/**
 * Add quantity control.
 */
fluxCart.quantityControls = function () {
  const quantityControls = document.querySelectorAll('.quantity input[type="number"]');
  Array.from(quantityControls).forEach(function (control) {
    const controlWrapper = control.closest('.quantity');
    if (0 < jQuery(controlWrapper).find('.quantity__button').length) {
      return;
    }
    const buttonMinus = document.createElement('button');
    buttonMinus.setAttribute('type', 'button');
    buttonMinus.classList.add('quantity__button');
    buttonMinus.classList.add('quantity__button--minus');
    buttonMinus.innerHTML = '-';
    controlWrapper.prepend(buttonMinus);
    buttonMinus.addEventListener('click', function () {
      control.value = parseInt(control.value) - 1;
      control.dispatchEvent(new Event('change'));
    });
    const buttonPlus = document.createElement('button');
    buttonPlus.setAttribute('type', 'button');
    buttonPlus.classList.add('quantity__button');
    buttonPlus.classList.add('quantity__button--plus');
    buttonPlus.innerHTML = '+';
    controlWrapper.appendChild(buttonPlus);
    buttonPlus.addEventListener('click', function () {
      control.value = parseInt(control.value) + 1;
      control.dispatchEvent(new Event('change'));
    });
    control.addEventListener('change', async function (e) {
      e.preventDefault();

      // Do not update qty if qty value is invalid.
      if (jQuery('form.checkout').get(0).reportValidity()) {
        // PHP side will be able to handle the quantity update.
        jQuery('body').trigger('update_checkout');
      }
      return false;
    });
  });
  jQuery('.quantity input[type="number"]').on('focusin', function () {
    jQuery(this).closest('.quantity').addClass('quantity--on-focus');
  });
  jQuery('.quantity input[type="number"]').on('focusout', function () {
    jQuery(this).closest('.quantity').removeClass('quantity--on-focus');
  });
};

/**
 * Updates the Cart Count.
 *
 * Updates the cart count that is shown on the modern theme.
 */
fluxCart.updateCartCount = function () {
  let total = 0;
  jQuery('.quantity input.qty').each(function () {
    total += parseInt(jQuery(this).val(), 10);
  });
  const cart_count = jQuery('.order_review_heading__count');
  if (cart_count.length) {
    cart_count.html(total);
  }
};

/**
 * Update Total.
 *
 * Update the total when the cart changes.
 */
fluxCart.update_total = function () {
  const total = jQuery('.order-total td:last-of-type').html();
  jQuery('.flux-checkout__sidebar-header-total').html(total);
};

/**
 * Move Shipping Row.
 *
 * Move the shipping row to the top of the order table or
 * to address tab on mobile.
 */
fluxCart.moveShippingRow = function () {
  const is_modern = document.querySelectorAll('.flux-checkout--modern').length;

  // No need to run this code for classic theme.
  if (!is_modern) {
    return;
  }
  if (jQuery('.woocommerce-checkout-review-order-table .woocommerce-shipping-totals').length) {
    jQuery('.flux-checkout__shipping-table tbody').html('');
  }
  const has_sidebar = jQuery('.flux-checkout--has-sidebar').length;

  // Pick the shipping row from content-right/sidebar.
  const $shipping_row = wp.hooks.applyFilters('flux_checkout_move_shipping_row_element', jQuery('.flux-checkout__content-right tr.woocommerce-shipping-totals.shipping'));
  if (!$shipping_row.length) {
    return;
  }
  const isMobile = helper.isMobile();
  if (!isMobile && has_sidebar) {
    // Site admin can place a table.flux-shipping-container--desktop anywhere on the page to change the location of shipping method for *desktop*.
    if (jQuery('.flux-shipping-container--desktop').length) {
      jQuery('.flux-shipping-container--desktop').empty().prepend($shipping_row);
    } else {
      // Else Place shipping row on the sidebar.
      jQuery('.flux-checkout__content-right .flux-checkout__shipping-table>tbody').html($shipping_row);
    }
  } else {
    // Site admin can place a table.flux-shipping-container--mobile anywhere on the page to change the location of shipping method for *mobile*.
    // eslint-disable-next-line no-lonely-if
    if (jQuery('.flux-shipping-container--mobile').length) {
      jQuery('.flux-shipping-container--mobile').empty().prepend($shipping_row);
    } else {
      // Place Shipping row on step 2.
      jQuery('.flux-step--address .flux-checkout__shipping-table>tbody').html($shipping_row);
    }
  }
};
jQuery(document.body).on('update_checkout', function () {
  jQuery('.flux-checkout__shipping-table').block({
    message: null,
    overlayCSS: {
      background: '#fff',
      opacity: 0.6
    }
  });
});
jQuery(document.body).on('updated_checkout', function (e, data) {
  jQuery('.flux-checkout__shipping-table').unblock();
  if (helper.isModernCheckout()) {
    fluxCart.addShippingRowToOrderSummary(data);
  }
  if (data?.fragments?.flux?.global_error) {
    validation.displayGlobalNotice(data.fragments.flux.global_error);
  }
  if (data?.fragments?.flux?.empty_cart) {
    // jshint ignore:line
    jQuery('.flux-checkout').append(data.fragments.flux.empty_cart);
    setTimeout(() => {
      window.location = flux_checkout_vars.shop_page;
    }, 3000);
  }
});

/**
 * Hide Show Order Summary.
 *
 * Toggle for the checkout summary on mobile view.
 *
 * @param {boolean} first - Whether the order summary is first.
 */
fluxCart.orderSummaryToggle = function (first) {
  if (!helper.isMobile()) {
    return;
  }
  const isModern = document.querySelectorAll('.flux-checkout--modern').length;
  const linkHide = document.querySelector('.flux-checkout__sidebar-header-link--hide');
  let sideBar = document.querySelector('.flux-checkout__order-review');
  if (isModern) {
    sideBar = document.querySelector('.flux-checkout__content-right');
  }
  if (!linkHide || !sideBar) {
    return;
  }
  const linkShow = document.querySelector('.flux-checkout__sidebar-header-link--show');
  const style = window.getComputedStyle(linkHide);
  if (style.display === 'none') {
    linkHide.style.display = 'block';
    linkShow.style.display = 'none';
    if (true === first) {
      sideBar.style.display = 'block';
    } else {
      ui.slideDown(sideBar);
    }
    ui.slideDown(sideBar);
  } else {
    linkHide.style.display = 'none';
    linkShow.style.display = 'block';
    if (true === first) {
      sideBar.style.display = 'none';
    } else {
      ui.slideUp(sideBar);
    }
  }
};
fluxCart.orderSummaryResize = function () {
  const $linkHide = jQuery('.flux-checkout__sidebar-header-link--hide');
  let $sideBar = jQuery('.flux-checkout__order-review');
  if (helper.isModernCheckout()) {
    $sideBar = jQuery('.flux-checkout__content-right');
  }

  // If the link is visible, show the sidebar.
  if ($linkHide.is(':visible')) {
    $sideBar.show();
  }

  // We never want to hide sidebar for desktop.
  if (!helper.isMobile() && $sideBar.is(':hidden')) {
    $sideBar.show();
  }
};
fluxCart.block = function () {
  jQuery('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table').block({
    message: null,
    overlayCSS: {
      background: '#fff',
      opacity: 0.6
    }
  });
};
fluxCart.unblock = function () {
  jQuery('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table').unblock();
};

/**
 * Add shipping cost row to the order review table for mobile view.
 *
 * @param {Object} data - The data received from the fragment.
 */
fluxCart.addShippingRowToOrderSummary = function (data) {
  // Add row if it doesn't exits.
  if (!jQuery('.flux-shop-table-shipping-price').length) {
    jQuery('.shop_table tfoot .cart-subtotal').first().after('<tr class="flux-shop-table-shipping-price"></tr>');
  }

  // Add shipping cost data received from the fragment.
  if (data?.fragments?.flux?.shipping_row) {
    // jshint ignore:line
    jQuery('.flux-shop-table-shipping-price').html(data.fragments.flux.shipping_row);
  }
};
let on_resize;
jQuery(window).on('resize', function () {
  clearTimeout(on_resize);
  on_resize = setTimeout(fluxCart.moveShippingRow, 250);
});
/* harmony default export */ const cart = (fluxCart);
;// ./source/frontend/js/form.js

var fluxForm = {};
fluxForm.init = function () {
  jQuery(function () {
    if (!helper.isModernCheckout()) {
      return;
    }
    fluxForm.prepareFields();
    fluxForm.addRemoveFocusClass();

    // Add is-active class.
    jQuery(document).on('change focus keydown', '.form-row input', function () {
      jQuery(this).closest('.form-row').addClass('is-active');
    });
    jQuery(document).on('blur', '.form-row input', function () {
      var $row = jQuery(this).closest('.form-row');
      if (fluxForm.hasPermanentPlaceholder($row) || jQuery(this).val()) {
        return;
      }
      $row.removeClass('is-active');
    });
    jQuery(document.body).on('country_to_state_changed', function () {
      fluxForm.prepareFields();
    });
  });
};

/**
 * Prepare fields on the pageload i.e. add is-active class for the input which are not empty.
 */
fluxForm.prepareFields = function () {
  jQuery('.form-row input').each(function () {
    fluxForm.prepareField(jQuery(this));
  });
};

/**
 * Prepare a field i.e. add/remove is-active based on its content.
 * @param {*} $input 
 * @returns 
 */
fluxForm.prepareField = function ($input) {
  var $row = $input.closest('.form-row');
  var $label = $row.find('label');
  if (fluxForm.hasPermanentPlaceholder($row)) {
    $row.addClass('is-active');
    return;
  }
  if ($input.val()) {
    $row.addClass('is-active');
  } else {
    $row.removeClass('is-active');
  }
};

/**
 * Does this field have permanent placeholder?
 * 
 * @param {jQuery} $row Row object.
 * 
 * @returns bool
 */
fluxForm.hasPermanentPlaceholder = function ($row) {
  var $label = $row.find('label');
  if (!$label.length) {
    return false;
  }
  var _for = $label.attr('for');
  if (['billing_address_search', 'shipping_address_search', ''].includes(_for)) {
    return true;
  }
  if ('billing_phone' === _for && '1' === flux_checkout_vars.international_phone) {
    return true;
  }
  return false;
};
fluxForm.addRemoveFocusClass = function () {
  jQuery(document).on('focus', '.form-row input', function () {
    jQuery(this).closest('.form-row').addClass('form-row--focus');
  });
  jQuery(document).on('blur', '.form-row input', function () {
    jQuery(this).closest('.form-row').removeClass('form-row--focus');
  });
};
/* harmony default export */ const js_form = (fluxForm);
;// ./source/frontend/js/localStorage.js
/**
 * Save all field's data in localStorage. Load it when page is loaded.
 */
const fluxLocalStorage = {
  init: function () {
    fluxLocalStorage.load_data();
    fluxLocalStorage.watch_data_change();
  },
  /**
   * Watch data change and save in localStorage as JSON.
   */
  watch_data_change: function () {
    const inputs = document.querySelectorAll('form.checkout input, form.checkout textarea, form.checkout select');
    Array.from(inputs).forEach(input => {
      input.addEventListener('change', e => {
        const form = input.closest('form');
        if (!form) {
          return;
        }
        const form_data = fluxLocalStorage.formSerialize(form);
        const json = JSON.stringify(form_data);
        localStorage.setItem('iconic_flux_checkout_form_data', json);
      });
    });
  },
  /**
   * Load data from localStorage and populate fields.
   */
  load_data: function () {
    const json = localStorage.getItem('iconic_flux_checkout_form_data');
    const form = document.querySelector('form.checkout');
    if (!json || !form) {
      return;
    }
    const single_checkbox = ['order_notes_switch', 'show_shipping'];
    const data = JSON.parse(json);
    if ('object' !== typeof data) {
      return;
    }
    data.forEach(fieldData => {
      const field = form.querySelector('[name="' + window.CSS.escape(fieldData.name) + '"]');
      if (!field) {
        return;
      }
      if (flux_checkout_vars.localstorage_fields.includes(fieldData.name) && fieldData.value && '' == field.value) {
        field.value = fieldData.value;
        field.dispatchEvent(new Event('change'));
      }
      if (single_checkbox.includes(fieldData.name) && 'on' === fieldData.value) {
        field.setAttribute('checked', true);
        field.dispatchEvent(new Event('change'));
      }
    });
  },
  formSerialize: function (formElement) {
    const values = [];
    const inputs = formElement.elements;
    for (let i = 0; i < inputs.length; i++) {
      values.push({
        name: inputs[i].name,
        value: inputs[i].value
      });
    }
    return values;
  }
};
/* harmony default export */ const js_localStorage = (fluxLocalStorage);
;// ./source/frontend/js/checkoutButton.js

var CheckoutButton = {
  cache: {
    button_html: ''
  },
  /**
   * Init.
   */
  init: function () {
    if (!helper.isModernCheckout() || jQuery('#payment_method_stripe').length) {
      return;
    }
    if (wp.hooks.applyFilters('flux_checkout_checkout_button_animation', true)) {
      CheckoutButton.prepare_button_dom();
      jQuery(document).on('click', '#place_order', CheckoutButton.on_button_click);
    }
    jQuery(document.body).on('checkout_error', CheckoutButton.on_error);
    jQuery(document.body).on('wc_stripe_submit_error', CheckoutButton.on_error);
    jQuery(document.body).on('updated_checkout', CheckoutButton.on_updated_checkout);
    jQuery(document.body).on('payment_method_selected', CheckoutButton.on_payment_method_selected);

    // This hack is needed because when already selected payment method is clicked again,
    // the event 'payment_method_selected' is not triggered.
    jQuery('form.checkout').on('click', 'input[name="payment_method"]', function () {
      window.setTimeout(CheckoutButton.on_payment_method_selected, 1);
    });
  },
  /**
   * Prepare button DOM for animation.
   * @returns 
   */
  prepare_button_dom: function () {
    if (jQuery("#place_order").find('.flux-submit-dots').length) {
      return;
    }
    jQuery("#place_order").html(jQuery("#place_order").html() + `<span class='flux-submit-dots'>
			<i class='flux-submit-dot flux-submit-dot__1'></i>
			<i class='flux-submit-dot flux-submit-dot__2'></i>
			<i class='flux-submit-dot flux-submit-dot__3'></i>
		</span>`);
  },
  /**
   * On button click.
   */
  on_button_click: function () {
    CheckoutButton.prepare_button_dom();
    jQuery("#place_order").addClass('flux-checkout-btn-loading');
  },
  /**
   * On WooCommerce error.
   */
  on_error: function () {
    jQuery("#place_order").removeClass('flux-checkout-btn-loading');
  },
  /**
   * On updated_checkout event. Modify the button html.
   *
   * @param {event} e
   * @param {data} data
   */
  on_updated_checkout: function (e, data) {
    if (!data || !data.fragments || !data.fragments.flux) {
      return;
    }
    if (data.fragments.flux.total) {
      CheckoutButton.cache.button_html = `${flux_checkout_vars.i18n.pay} ${data.fragments.flux.total}`;
      jQuery('#place_order').html(CheckoutButton.cache.button_html);
    }
  },
  /**
   * The HTML of checkout button would reset to default when payment method is selected.
   * This function would change it back.
   *
   * @returns 
   */
  on_payment_method_selected: function () {
    if (!CheckoutButton.cache.button_html || 'paypal' === jQuery("[name='payment_method']:checked").val()) {
      return;
    }
    jQuery('#place_order').html(CheckoutButton.cache.button_html);
  }
};
/* harmony default export */ const checkoutButton = (CheckoutButton);
;// ./source/frontend/js/intlPhone.js
/* global intlTelInput, intlTelInputGlobals */

const fluxIntlPhone = {
  iti_open: false,
  iti: [],
  init: () => {
    jQuery(() => {
      if (typeof intlTelInput === 'undefined') {
        return;
      }
      fluxIntlPhone.initialiseFields();

      /**
       * We need to handle click outside ourself because, otherwise
       * it takes 2 clicks outside to close the country dropdown.
       */
      if (document.querySelector('.iti__selected-flag')) {
        document.querySelector('.iti__selected-flag').removeEventListener('click', intlTelInputGlobals.instances[0]._handleClickSelectedFlag, {}, false);
      }
      fluxIntlPhone.toggleDropdown();
      fluxIntlPhone.handleClickOutside();
      jQuery(document.body).on('flux_step_change', fluxIntlPhone.onStepChange);
    });
  },
  initialiseFields: () => {
    const idx = 0;
    jQuery('.flux-intl-phone input[type=tel], .flux-intl-phone input[type=text]').each(function () {
      let args = {
        hiddenInput: () => ({
          phone: jQuery(this).attr('name') + '_full_number',
          country: jQuery(this).attr('name') + '_country_code'
        }),
        onlyCountries: flux_checkout_vars.allowed_countries,
        preferredCountries: [],
        nationalMode: true,
        autoPlaceholder: 'polite',
        separateDialCode: !jQuery('body').hasClass('rtl'),
        // 2 = fixed line and mobile numbers are considered valid.
        // See https://github.com/jackocnr/intl-tel-input/blob/master/src/js/utils.js#L207
        validationNumberTypes: [0, 1]
      };
      if (flux_checkout_vars?.base_country) {
        args.initialCountry = flux_checkout_vars?.base_country;
        args.preferredCountries = [flux_checkout_vars?.base_country];
      }

      /**
       * Filter to modify arguments before they are passed to intlTelInput function.
       *
       * Full list of arguments can be found here: https://github.com/jackocnr/intl-tel-input#initialisation-options
       */
      args = wp.hooks.applyFilters('flux_checkout_intl_phone_args', args);
      fluxIntlPhone.iti[idx] = window.intlTelInput(jQuery(this).get(0), args);

      // Update the hidden field when field is changed.
      jQuery(this).on('countrychange', fluxIntlPhone.updateHiddenField);
      jQuery(this).on('change', fluxIntlPhone.updateHiddenField);

      // Validation.
      const debounced_validate = helper.debounce(fluxIntlPhone.validate, 1000);

      // Mark as changed on blur - we dont want to show error if user has not changed the field.
      jQuery(this).on('blur', fluxIntlPhone.mark_changed);

      // Validate on blur and on flux_validate event.
      jQuery(this).on('blur validate flux_validate', fluxIntlPhone.validate);

      // Debounce validation so that we do not validate on every keyup.
      jQuery(this).on('keyup', debounced_validate);

      // Don't show error when user is typing.
      jQuery(this).on('keyup', fluxIntlPhone.hideError);
      jQuery(this).closest('.form-row').addClass('flux-intl-phone--init');

      // Disable wc_checkout_form.validate_field() event listener on input event.
      jQuery('form.checkout').off('input', '**');
    });
  },
  /**
   * Update hidden field.
   */
  updateHiddenField: () => {
    var hidden_name = jQuery(undefined).attr('name') + '_full_number';
    var $hidden_field = jQuery(`[name=${hidden_name}]`);
    if ($hidden_field.length) {
      $hidden_field.val(fluxIntlPhone.iti[0].getNumber());
    }
  },
  /**
   * Update hidden field on step change.
   */
  onStepChange: () => {
    jQuery('.flux-intl-phone input[type=tel], .flux-intl-phone input[type=text]').each(function () {
      fluxIntlPhone.updateHiddenField.apply(this);
    });
  },
  /**
   * Manually toggle dropdown opening and closing since it doesn't automatically work perfectly.
   *
   * WooCommerce stops the event propogation when clicked within `.woocommerce-input-wrapper`.
   * This interfere's with the default behaviour of intlTelInput.
   *
   * Read: https://github.com/woocommerce/woocommerce/issues/22720
   */
  toggleDropdown: () => {
    jQuery('.iti__selected-flag').click(function () {
      if (jQuery('.iti__country-list').hasClass('iti__hide') || 0 === jQuery('.iti__country-list').length) {
        fluxIntlPhone.iti[0]._showDropdown();
      } else {
        fluxIntlPhone.iti[0]._closeDropdown();
      }
    });
  },
  /**
   * Close dropdown when clicked outside.
   */
  handleClickOutside: () => {
    document.addEventListener('click', function (e) {
      if (fluxIntlPhone.iti && !e.target.closest('.iti__selected-flag')) {
        fluxIntlPhone.iti[0]._closeDropdown();
      }
    });
  },
  hideError: () => {
    jQuery(undefined).closest('.form-row').removeClass('woocommerce-invalid woocommerce-invalid-phone');
  },
  /**
   * Validate the phone field.
   */
  validate: () => {
    const $input = jQuery('.flux-intl-phone .iti__tel-input'),
      val = $input.val().trim(),
      $parent = $input.closest('.form-row');
    if (!val && $parent.hasClass('woocommerce-invalid-required-field')) {
      return;
    }
    const iti = intlTelInput.getInstance($input.get(0));
    const isValid = iti?.isValidNumber();
    if (!$parent.hasClass('has-changed') && val.length < 4) {
      $parent.removeClass('woocommerce-validated woocommerce-invalid');
      return;
    }
    if (!isValid) {
      $parent.removeClass('woocommerce-validated').addClass('woocommerce-invalid woocommerce-invalid-phone');
      $parent.find('.error').text(flux_checkout_vars.i18n.phone.invalid);
    } else {
      $parent.removeClass('woocommerce-invalid woocommerce-invalid-phone');
    }
  },
  /**
   * Add class `has-changed` if the value has changed.
   */
  mark_changed: () => {
    if (jQuery(undefined).val()) {
      jQuery(undefined).closest('.form-row').addClass('has-changed');
    }
  }
};
window.fluxIntlPhone = fluxIntlPhone;
/* harmony default export */ const intlPhone = (fluxIntlPhone);
;// ./source/frontend/js/orderpay.js
let fluxOrderPay = {
  init: function () {
    jQuery(document).ready(function () {
      jQuery('body.woocommerce-order-pay #place_order').each(function () {
        var text = jQuery(this).data('text');
        if (text) {
          jQuery(this).html(text);
        }
      });
    });
  }
};
/* harmony default export */ const orderpay = (fluxOrderPay);
;// ./source/frontend/js/expressCheckout.js
var fluxExpressCheckout = {
  /**
   * Elements.
   */
  els: {
    $wrap: jQuery('.flux-express-checkout-wrap')
  },
  vars: {
    checkCount: 0
  },
  /**
   * Init.
   */
  init: function () {
    jQuery(function () {
      fluxExpressCheckout.relocateExpressButtons();
      fluxExpressCheckout.els.$wrap = jQuery('.flux-express-checkout-wrap');
      if (jQuery('#wc-stripe-payment-request-button').length) {
        fluxExpressCheckout.handleStripeButton();
      }
    });
  },
  /**
   * Move all express checkout buttons to $wrap so they appear altogether within the checkout page.
   */
  relocateExpressButtons: function () {
    // Stripe.
    jQuery("#wc-stripe-payment-request-wrapper>div").each(function () {
      jQuery(this).appendTo(fluxExpressCheckout.els.$wrap).wrap("<div class='flux-express-checkout__btn flux-expresss-checkout__btn--stripe flux-skeleton'></div>");
    });

    // Paypal.
    jQuery(".eh_paypal_express_link").appendTo(fluxExpressCheckout.els.$wrap).wrap("<div class='flux-express-checkout__btn'></div>");

    // Stripe (Payment Plugins)
    if (jQuery('.banner_payment_method_stripe_googlepay .gpay-card-info-container-fill').length) {
      jQuery('.banner_payment_method_stripe_googlepay .gpay-card-info-container-fill').appendTo(fluxExpressCheckout.els.$wrap).wrap("<div class='flux-express-checkout__btn'></div>");
      jQuery('.wc-stripe-banner-checkout').hide();
    }

    // Paypal (Payment Plugins)
    jQuery('.express_payment_method_ppcp').appendTo(fluxExpressCheckout.els.$wrap).wrap("<div class='flux-express-checkout__btn'></div>");
  },
  /**
   * Keep checking till 10 seconds for Google pay/apple pay buttons to load.
   * Will opt-out if buttons are not loaded in this while.
   *
   * @returns 
   */
  handleStripeButton: function () {
    ++fluxExpressCheckout.vars.checkCount;
    if (fluxExpressCheckout.gPayApplePayButtonsLoaded()) {
      fluxExpressCheckout.show();
      jQuery(".flux-expresss-checkout__btn--stripe").show().removeClass('flux-skeleton');
      return;
    } else {
      if (fluxExpressCheckout.onlyStripeButtonExist()) {
        fluxExpressCheckout.hide();
      }
    }
    if (fluxExpressCheckout.vars.checkCount < 20) {
      jQuery(".flux-expresss-checkout__btn--stripe").hide();
      setTimeout(fluxExpressCheckout.handleStripeButton, 500);
    }
  },
  /**
   * Is there only Stripe buttons(Apple/Gpay buttons) present?
   * @returns 
   */
  onlyStripeButtonExist: function () {
    return fluxExpressCheckout.els.$wrap.find(">div,>span,>a").length < 2;
  },
  /**
   * Has Stripe buttons loaded?
   * @returns 
   */
  gPayApplePayButtonsLoaded: function () {
    return jQuery("#wc-stripe-payment-request-button>div").length;
  },
  /**
   * Hide express checkout wrap.
   */
  hide: function () {
    fluxExpressCheckout.els.$wrap.hide();
  },
  /**
   * Show express checkout wrap.
   */
  show: function () {
    fluxExpressCheckout.els.$wrap.show();
  }
};
/* harmony default export */ const expressCheckout = (fluxExpressCheckout);
;// ./source/frontend/js/variationHandler.js
/**
 * Reusable utility to handles the dropdown changes for the variation dropdowns.
 * 1. Fetches the variation price when all dropdowns are selected
 * 2. Saves attributes data in hidden fields 'iconic-wsb-acb-variation-data' or 'iconic-wsb-checkout-variation-data'
 * 3. Shows and Hides spinner while AJAX
 *
 * @param {Object}   form The jQuery instance of the form where variation dropdowns reside
 * @param {str}      container_selector selector string for the containing div
 * @param {Function} onVariationFound callback function to be called when the variation is purchasable
 * @param {Function} onVariationNotFound callback function to be called when the variation is not purchasable
 */
var IconicVariationHandler = function (form, container_selector, onVariationFound, onVariationNotFound) {
  this.$form = form;
  this.container_selector = container_selector;
  this.getVariation = function () {
    var self = this,
      $variationIdField = self.$form.find(".flux-cross-sell__variation-id");
    if ($variationIdField.length <= 0) {
      return;
    }
    if (self.checkAllSelects()) {
      self.$attributeFields = jQuery(self.container_selector).find('.flux-crosssell__variation-select');
      var attributes = self.getChosenAttributes();
      var currentAttributes = attributes.data;
      jQuery(container_selector).find(".flux-cross-sell__variation-data").val(JSON.stringify(currentAttributes));
      currentAttributes.product_id = parseInt(jQuery(self.container_selector).attr('data-product-id'));
      currentAttributes.action = "flux_get_variation";
      currentAttributes._ajax_nonce = wc_checkout_params.update_order_review_nonce;
      self.showLoader();
      self.xhr = jQuery.ajax({
        url: flux_checkout_vars.ajax_url,
        type: 'POST',
        data: currentAttributes,
        success: function (data) {
          self.hideLoader();
          if (!data.success || !data.data) {
            onVariationNotFound(self);
            jQuery(container_selector).find(".flux-cross-sell__variation-id").val("");
            return;
          }
          let variation = data.data;
          if (variation.price_html) {
            jQuery(container_selector).find(".flux-crosssell__product-price").html(variation.price_html);
          }
          if (!variation.is_purchasable || !variation.is_in_stock || !variation.variation_is_visible) {
            onVariationNotFound(self);
            jQuery(container_selector).find(".flux-cross-sell__variation-id").val("");
          } else {
            jQuery(self.container_selector).find('.flux-crosssell__unavailable-msg').hide();
            onVariationFound(variation, self);
            jQuery(container_selector).find(".flux-cross-sell__variation-id").val(variation.variation_id);
          }
        },
        complete: function () {
          self.hideLoader();
        }
      });
    } else {}
  };

  /**
   * Checks all the variation dropdowns.
   * @returns boolean. Returns true iff all attribute dropdowns have a selected value. Returns false even is a single attribute dropdown doesnt has a value.
   */
  this.checkAllSelects = function () {
    var self = this;
    self.allVariationSelectedFlag = true;
    jQuery(self.container_selector).find(".flux-crosssell__variation-select").each(function () {
      if (jQuery(this).val()) {
        jQuery(this).closest("tr").removeClass("flux-crosssell__variation-error");
      } else {
        self.allVariationSelectedFlag = false;
        jQuery(this).closest("tr").addClass("flux-crosssell__variation-error");
      }
    });
    return self.allVariationSelectedFlag;
  };

  /**
   * Get chosen attributes from form.
   * @return array
   */
  this.getChosenAttributes = function () {
    var data = {};
    var count = 0;
    var chosen = 0;
    this.$attributeFields.each(function () {
      var attribute_name = jQuery(this).data('attribute_name') || jQuery(this).attr('name');
      var value = jQuery(this).val() || '';
      if (value.length > 0) {
        chosen++;
      }
      count++;
      data[attribute_name] = value;
    });
    return {
      'count': count,
      'chosenCount': chosen,
      'data': data
    };
  };

  /**
   * Show spinner/loader for ajax.
   */
  this.showLoader = function () {
    this.$form.block({
      message: null,
      overlayCSS: {
        background: '#fff',
        opacity: 0.6
      }
    });
  };

  /**
   * used in conjection to showLoader() to hide the spinner/loader
   */
  this.hideLoader = function () {
    this.$form.unblock();
  };
};
/* harmony default export */ const variationHandler = (IconicVariationHandler);
;// ./source/frontend/js/crossSell.js

var fluxCrossSell = {
  variationHandlers: {}
};
fluxCrossSell.init = function () {
  jQuery(document).on('click', '.flux-crosssell__add-to-cart-btn', function (e) {
    e.preventDefault();
    fluxCrossSell.onButtonClick(this);
  });
  fluxCrossSell.handleVariableProduct();
};

/**
 * Initialize variable product.
 */
fluxCrossSell.handleVariableProduct = function () {
  jQuery('.flux-crosssell__variation-select').select2();
  jQuery('.flux-crosssell__product--variable').each(function () {
    jQuery(this).find('.flux-crosssell__add-to-cart-btn').attr('disbaled', true);
  });

  // Listen to dropdown changes.
  jQuery(document).on('change', '.flux-crosssell__variation-select', function () {
    let $container = jQuery(this).closest('.flux-crosssell__product');
    let product_id = $container.data('product-id');
    let variationHandler = fluxCrossSell.getVariationHandler(product_id);
    $container.find('.flux-crosssell__add-to-cart-btn').attr('disabled', true);
    variationHandler.getVariation(variationHandler);
  });

  /**
   * Try to get variation on page load.
   */
  jQuery('.flux-crosssell__product--variable').each(function () {
    let $container = jQuery(this);
    let product_id = $container.data('product-id');
    let variationHandler = fluxCrossSell.getVariationHandler(product_id);
    variationHandler.getVariation(variationHandler);
  });

  /**
   * When checkout is updated, check if
   * 
   * 1. all dropdowns are selected
   * 2. variation ID is set 
   * 
   * If so, enable the add to cart button.
   */
  jQuery(document.body).on('updated_checkout', function () {
    jQuery('.flux-crosssell__product--variable').each(function () {
      let product_id = jQuery(this).data('product-id');
      let variationHandler = fluxCrossSell.getVariationHandler(product_id);
      if (variationHandler.checkAllSelects() && jQuery(this).find('.flux-cross-sell__variation-id').val()) {
        jQuery(this).find('.flux-crosssell__add-to-cart-btn').attr('disabled', false);
      }
    });
    jQuery('.flux-crosssell__variation-select').select2();
  });
};
fluxCrossSell.getVariationHandler = function (product_id) {
  if (!fluxCrossSell.variationHandlers[product_id]) {
    fluxCrossSell.variationHandlers[product_id] = new variationHandler(jQuery('form.woocommerce-checkout'), `.flux-crosssell__product-id-${product_id}`, fluxCrossSell.onVariationFound, fluxCrossSell.onVariationNotFound);
  }
  return fluxCrossSell.variationHandlers[product_id];
};

/**
 * Add to cart button click handler.
 * 
 * @param {*} _this 
 */
fluxCrossSell.onButtonClick = function (_this) {
  if (!jQuery('.woocommerce-checkout').find('.flux-cross-sell-payload').length) {
    jQuery('.woocommerce-checkout').append('<input type="hidden" name="flux_cross_sell" value="1" class="flux-cross-sell-payload">');
  }
  var $payload = jQuery('.flux-cross-sell-payload');
  var payload = {};
  payload.product_id = jQuery(_this).data('product-id');
  payload.variation_data = jQuery(_this).closest('.flux-crosssell__product').find('.flux-cross-sell__variation-data').val();
  payload.variation_id = jQuery(_this).closest('.flux-crosssell__product').find('.flux-cross-sell__variation-id').val();
  $payload.val(JSON.stringify(payload));
  jQuery(document.body).trigger('update_checkout');
};

/**
 * Variation found handler.
 * 
 * @param {*} variation 
 */
fluxCrossSell.onVariationFound = function (variation, handler) {
  jQuery(handler.container_selector).find('.flux-crosssell__add-to-cart-btn').attr('disabled', false);
};

/**
 * Variation not found handler.
 */
fluxCrossSell.onVariationNotFound = function (handler) {
  jQuery(handler.container_selector).find('.flux-crosssell__add-to-cart-btn').attr('disabled', true);
  jQuery(handler.container_selector).find('.flux-crosssell__unavailable-msg').show();
};
/* harmony default export */ const crossSell = (fluxCrossSell);
;// ./source/frontend/js/main.js

















document.addEventListener('DOMContentLoaded', function (event) {
  (function () {
    if (typeof Event !== 'function') {
      window.Event = CustomEvent;
    }
  })();
  helper.removeDomElements();
  validation.init();
  stepper.init();
  loginButtons.init();
  addressSearch.init();
  components.init();
  coupon.init();
  compatibility.init();
  cart.init();
  cart.runOnce();
  js_form.init();
  js_localStorage.init();
  checkoutButton.init();
  intlPhone.init();
  orderpay.init();
  loginForm.init();
  expressCheckout.init();
  crossSell.init();
});
(function ($, document) {
  $(document).ready(function () {
    $(document.body).on('wc_fragments_refreshed', function () {
      helper.removeDomElements();
      cart.init();
      cart.update_total();
    });
    $(document.body).on('updated_checkout', function () {
      helper.removeDomElements();
      cart.init();
      cart.update_total();
    });
    $(document.body).on('change', 'input.shipping_method', function () {
      cart.update_total();
    });

    // Handle the condition where back button is pressed and document.ready event is not triggered.
    $(window).on('pageshow', function () {
      js_form.prepareFields();
    });

    // When auto-saved address is pasted from the keyboard in iOS, it doesnt trigger update_checkout.
    jQuery(".address-field input.input-text").on('input propertychange paste', function () {
      jQuery(this).trigger("keydown");
    });
  });
})(jQuery, document);
})();

/******/ })()
;