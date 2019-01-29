void function ( exports, $, _, Backbone ) {

	/* Google Fonts Picker */

	var ES_FontsPicker = exports.ES_FontsPicker = ES_PanelView.extend({
		constructor: function ES_FontsPicker () {
			ES_PanelView.apply(this, arguments)
		},
		views: {
			'groupsView > .font-groups': B.CollectionView.extend({
				itemView: B.View.extend({
					views: {
						'itemsView collection:fonts > .font-items': B.CollectionView.extend({
							itemView: B.View.extend({
								showImage: function() {
									var filename = this.model.get('name').toLowerCase().replace(/\s+/g,'-') + '@2x.png';
									this.$('img').attr('src', ES_Config.URL.ASSETS + 'images/fonts/' + filename);
								},
                                ready: function(){
                                    this.fontName = this.model.get('name').toLowerCase();
                                },
								checkVisible: function(name){
                                    if ( name ) {
                                        name = '^' + name.toLowerCase();
                                        var re = new RegExp(name, 'g');
                                        if ( this.fontName.match(re) ) {
                                            this.$el.show()
                                            this.showImage();
                                        }
                                        else {
                                            this.$el.hide()
                                        }
                                    }
                                    else {
                                        this.$el.show()
                                        this.showImage();
                                    }
								}
							})
						})
					}
				})
			})
		},
		options: {
			activeTarget: true,
			background: true,
			direction: 'top'
		},
		events: {
			'click .font-item': function( e ) {
				var selected = e.currentTarget.getAttribute('data-name');
				this.trigger('select', selected)
				this.close()
			}
		},
		ready: function() {
            var view = this;
			this.groupsView.setCollection(new ES_FontsCollection)
			this.once('open', function() {
				_.each(this.groupsView.subViews, function(groupView) {
					_.each(groupView.itemsView.subViews, function(itemView) {
						itemView.checkVisible('');
					})
				})
			});
            this.on('before:open', function(){
                view.setFonts();
            })
		},
        setFonts: function(){
            var view = this;
            this.groupsView.setCollection(new ES_FontsCollection)
            _.each(this.groupsView.subViews, function(groupView) {
                _.each(groupView.itemsView.subViews, function(itemView) {
                    itemView.checkVisible(view.regex);
                })
            })
        }
	})


	/* Color Picker */

	var colorPickerOptions = {

		doRender: true,
		buildCallback: function( $elm ) {
			//$elm.append('<div class="cp-panel"><input type="text" class="cp-HEX" /></div>')
			var colorInstance = this.color,
					colorPicker = this;
			var $trigger = this.$trigger;

			$( '.cp-color-picker' ).addClass( 'color-picker-hidden' );
		},

		renderCallback: function( $elm, toggled ) {
			var action;

			if ( toggled === false && !$( '.cp-color-picker' ).hasClass( 'color-picker-hidden' ) )
				action = 'close';

			if ( toggled === true && $( '.cp-color-picker' ).hasClass('color-picker-hidden') )
				action = 'open';

			if (action == 'open' && !$elm.val())
				$elm.val('#000000');

			var colors = this.color.colors.RND,
					modes = {
						r: colors.rgb.r, g: colors.rgb.g, b: colors.rgb.b,
						h: colors.hsv.h, s: colors.hsv.s, v: colors.hsv.v,
						HEX: this.color.colors.HEX
					};

			$( 'input', '.cp-panel' ).each( function() {
				this.value = modes[ this.className.substr( 3 ) ];
			} );

			if ( action == 'close' ) {
				$elm.trigger( 'change' );
				$( '.cp-color-picker' ).addClass( 'color-picker-hidden' );
			}
			if ( action == 'open' ) {
				$( '.cp-color-picker' ).removeClass( 'color-picker-hidden' );
			}
			if ( this.color.colors.alpha == 1) {
				$elm.val('#' + this.color.colors.HEX)
			}
		}
	};
	var ES_ColorPicker = exports.ES_ColorPicker = ES_PanelView.extend({
		options: {
			activeTarget: true,
			background: false,
		},
		events: {
			'change .input-color-value': function(e) {
				this.options.target.value = e.currentTarget.value;
				$(this.options.target).trigger('change')
			},
			'mousedown': function(e){
				e.stopPropagation();
			}
		},
		constructor: function ES_ColorPicker () {
			ES_PanelView.apply(this, arguments)
		},
		initialize: function() {
			this.$input = this.$('.input-color-value');
			this.on('open', function() {
				if(_.isElement(this.options.target)) {
					this.$input.val(this.options.target.value);
				}
				this.$input.focus();
			});
			//console.log(this.el)
			colorPickerOptions.parentELEM = this.$('.panel-body').get(0);
			this.$('.input-color-value').colorPicker(colorPickerOptions);
		}
	})


	/* Font Awesome Icons Picker */

	var ES_FontAwesomePickerModel = B.Model({
		'groups': B.Collection([
			B.Model({
				'icons': B.Collection([
					B.Model()
				])
			})
		])
	})
	var ES_FontAwesomePicker = exports.ES_FontAwesomePicker = ES_PanelView.extend({
		constructor: function ES_FontAwesomePicker ( options ) {
			options.model = new ES_FontAwesomePickerModel({
				groups: _.map(ES_FontAwesomeIcons, function ( group ) {
					return {
						'name': group.name,
						'icons': _.map(group.icons, function ( icon ) {
							return {
								'name': icon,
								'class': 'fa fa-' + icon
							}
						})
					}
				})
			})
			ES_PanelView.call(this, options)
		},
		options: {
			activeTarget: true,
			background: true,
		},
		views: {
			'groupsView collection:groups > .picker-groups': B.CollectionView.extend({
				itemView: B.View.extend({
					views: {
						'groupsView collection:icons > .picker-icons': B.CollectionView
					}
				})
			})
		},
		events: {
			'click .picker-icon': function( e ) {
				var name = e.currentTarget.getAttribute('data-name');
				var $icon = $(e.currentTarget).children('.fa');
				var content = getComputedStyle($icon.get(0), ':before')
					.getPropertyValue('content')
					.replace(/['"]+/g, '');
				this.callback && this.callback(name, 'fa fa-' + name, content);
				this.close()
			}
		},
		select: function( callback, context ) {
			if ( typeof callback !== 'function' )
				throw "A callback function is required."
			this.callback = _.once(_.bind(callback, context));
			return this;
		}
	})
	/*
	 $('#icons section').toArray().map(function(el){

	 var name = $(el).children('.page-header').text()
	 var icons = _.compact( $(el).find('.fa-hover a i').toArray().map(function(el){
	 return el.className.replace('fa fa-','')
	 }))
	 return { name: name, icons: icons }

	 })
	 */
	var ES_FontAwesomeIcons = [
		{
			"name": "Web Application Icons",
			"icons": [ "adjust", "anchor", "archive", "area-chart", "arrows", "arrows-h", "arrows-v", "asterisk", "at", "automobile", "balance-scale", "ban", "bank", "bar-chart", "bar-chart-o", "barcode", "bars", "battery-0", "battery-1", "battery-2", "battery-3", "battery-4", "battery-empty", "battery-full", "battery-half", "battery-quarter", "battery-three-quarters", "bed", "beer", "bell", "bell-o", "bell-slash", "bell-slash-o", "bicycle", "binoculars", "birthday-cake", "bolt", "bomb", "book", "bookmark", "bookmark-o", "briefcase", "bug", "building", "building-o", "bullhorn", "bullseye", "bus", "cab", "calculator", "calendar", "calendar-check-o", "calendar-minus-o", "calendar-o", "calendar-plus-o", "calendar-times-o", "camera", "camera-retro", "car", "caret-square-o-down", "caret-square-o-left", "caret-square-o-right", "caret-square-o-up", "cart-arrow-down", "cart-plus", "cc", "certificate", "check", "check-circle", "check-circle-o", "check-square", "check-square-o", "child", "circle", "circle-o", "circle-o-notch", "circle-thin", "clock-o", "clone", "close", "cloud", "cloud-download", "cloud-upload", "code", "code-fork", "coffee", "cog", "cogs", "comment", "comment-o", "commenting", "commenting-o", "comments", "comments-o", "compass", "copyright", "creative-commons", "credit-card", "crop", "crosshairs", "cube", "cubes", "cutlery", "dashboard", "database", "desktop", "diamond", "dot-circle-o", "download", "edit", "ellipsis-h", "ellipsis-v", "envelope", "envelope-o", "envelope-square", "eraser", "exchange", "exclamation", "exclamation-circle", "exclamation-triangle", "external-link", "external-link-square", "eye", "eye-slash", "eyedropper", "fax", "feed", "female", "fighter-jet", "file-archive-o", "file-audio-o", "file-code-o", "file-excel-o", "file-image-o", "file-movie-o", "file-pdf-o", "file-photo-o", "file-picture-o", "file-powerpoint-o", "file-sound-o", "file-video-o", "file-word-o", "file-zip-o", "film", "filter", "fire", "fire-extinguisher", "flag", "flag-checkered", "flag-o", "flash", "flask", "folder", "folder-o", "folder-open", "folder-open-o", "frown-o", "futbol-o", "gamepad", "gavel", "gear", "gears", "gift", "glass", "globe", "graduation-cap", "group", "hand-grab-o", "hand-lizard-o", "hand-paper-o", "hand-peace-o", "hand-pointer-o", "hand-rock-o", "hand-scissors-o", "hand-spock-o", "hand-stop-o", "hdd-o", "headphones", "heart", "heart-o", "heartbeat", "history", "home", "hotel", "hourglass", "hourglass-1", "hourglass-2", "hourglass-3", "hourglass-end", "hourglass-half", "hourglass-o", "hourglass-start", "i-cursor", "image", "inbox", "industry", "info", "info-circle", "institution", "key", "keyboard-o", "language", "laptop", "leaf", "legal", "lemon-o", "level-down", "level-up", "life-bouy", "life-buoy", "life-ring", "life-saver", "lightbulb-o", "line-chart", "location-arrow", "lock", "magic", "magnet", "mail-forward", "mail-reply", "mail-reply-all", "male", "map", "map-marker", "map-o", "map-pin", "map-signs", "meh-o", "microphone", "microphone-slash", "minus", "minus-circle", "minus-square", "minus-square-o", "mobile", "mobile-phone", "money", "moon-o", "mortar-board", "motorcycle", "mouse-pointer", "music", "navicon", "newspaper-o", "object-group", "object-ungroup", "paint-brush", "paper-plane", "paper-plane-o", "paw", "pencil", "pencil-square", "pencil-square-o", "phone", "phone-square", "photo", "picture-o", "pie-chart", "plane", "plug", "plus", "plus-circle", "plus-square", "plus-square-o", "power-off", "print", "puzzle-piece", "qrcode", "question", "question-circle", "quote-left", "quote-right", "random", "recycle", "refresh", "registered", "remove", "reorder", "reply", "reply-all", "retweet", "road", "rocket", "rss", "rss-square", "search", "search-minus", "search-plus", "send", "send-o", "server", "share", "share-alt", "share-alt-square", "share-square", "share-square-o", "shield", "ship", "shopping-cart", "sign-in", "sign-out", "signal", "sitemap", "sliders", "smile-o", "soccer-ball-o", "sort", "sort-alpha-asc", "sort-alpha-desc", "sort-amount-asc", "sort-amount-desc", "sort-asc", "sort-desc", "sort-down", "sort-numeric-asc", "sort-numeric-desc", "sort-up", "space-shuttle", "spinner", "spoon", "square", "square-o", "star", "star-half", "star-half-empty", "star-half-full", "star-half-o", "star-o", "sticky-note", "sticky-note-o", "street-view", "suitcase", "sun-o", "support", "tablet", "tachometer", "tag", "tags", "tasks", "taxi", "television", "terminal", "thumb-tack", "thumbs-down", "thumbs-o-down", "thumbs-o-up", "thumbs-up", "ticket", "times", "times-circle", "times-circle-o", "tint", "toggle-down", "toggle-left", "toggle-off", "toggle-on", "toggle-right", "toggle-up", "trademark", "trash", "trash-o", "tree", "trophy", "truck", "tty", "tv", "umbrella", "university", "unlock", "unlock-alt", "unsorted", "upload", "user", "user-plus", "user-secret", "user-times", "users", "video-camera", "volume-down", "volume-off", "volume-up", "warning", "wheelchair", "wifi", "wrench" ]
		},
		{
			"name": "Hand Icons",
			"icons": [ "hand-grab-o", "hand-lizard-o", "hand-o-down", "hand-o-left", "hand-o-right", "hand-o-up", "hand-paper-o", "hand-peace-o", "hand-pointer-o", "hand-rock-o", "hand-scissors-o", "hand-spock-o", "hand-stop-o", "thumbs-down", "thumbs-o-down", "thumbs-o-up", "thumbs-up" ]
		},
		{
			"name": "Transportation Icons",
			"icons": [ "ambulance", "automobile", "bicycle", "bus", "cab", "car", "fighter-jet", "motorcycle", "plane", "rocket", "ship", "space-shuttle", "subway", "taxi", "train", "truck", "wheelchair" ]
		},
		{
			"name": "Gender Icons",
			"icons": [ "genderless", "intersex", "mars", "mars-double", "mars-stroke", "mars-stroke-h", "mars-stroke-v", "mercury", "neuter", "transgender", "transgender-alt", "venus", "venus-double", "venus-mars" ]
		},
		{
			"name": "File Type Icons",
			"icons": [ "file", "file-archive-o", "file-audio-o", "file-code-o", "file-excel-o", "file-image-o", "file-movie-o", "file-o", "file-pdf-o", "file-photo-o", "file-picture-o", "file-powerpoint-o", "file-sound-o", "file-text", "file-text-o", "file-video-o", "file-word-o", "file-zip-o" ]
		},
		{
			"name": "Spinner Icons",
			"icons": [ "circle-o-notch", "cog", "gear", "refresh", "spinner" ]
		},
		{
			"name": "Form Control Icons",
			"icons": [ "check-square", "check-square-o", "circle", "circle-o", "dot-circle-o", "minus-square", "minus-square-o", "plus-square", "plus-square-o", "square", "square-o" ]
		},
		{
			"name": "Payment Icons",
			"icons": [ "cc-amex", "cc-diners-club", "cc-discover", "cc-jcb", "cc-mastercard", "cc-paypal", "cc-stripe", "cc-visa", "credit-card", "google-wallet", "paypal" ]
		},
		{
			"name": "Chart Icons",
			"icons": [ "area-chart", "bar-chart", "bar-chart-o", "line-chart", "pie-chart" ]
		},
		{
			"name": "Currency Icons",
			"icons": [ "bitcoin", "btc", "cny", "dollar", "eur", "euro", "gbp", "gg", "gg-circle", "ils", "inr", "jpy", "krw", "money", "rmb", "rouble", "rub", "ruble", "rupee", "shekel", "sheqel", "try", "turkish-lira", "usd", "won", "yen" ]
		},
		{
			"name": "Text Editor Icons",
			"icons": [ "align-center", "align-justify", "align-left", "align-right", "bold", "chain", "chain-broken", "clipboard", "columns", "copy", "cut", "dedent", "eraser", "file", "file-o", "file-text", "file-text-o", "files-o", "floppy-o", "font", "header", "indent", "italic", "link", "list", "list-alt", "list-ol", "list-ul", "outdent", "paperclip", "paragraph", "paste", "repeat", "rotate-left", "rotate-right", "save", "scissors", "strikethrough", "subscript", "superscript", "table", "text-height", "text-width", "th", "th-large", "th-list", "underline", "undo", "unlink" ]
		},
		{
			"name": "Directional Icons",
			"icons": [ "angle-double-down", "angle-double-left", "angle-double-right", "angle-double-up", "angle-down", "angle-left", "angle-right", "angle-up", "arrow-circle-down", "arrow-circle-left", "arrow-circle-o-down", "arrow-circle-o-left", "arrow-circle-o-right", "arrow-circle-o-up", "arrow-circle-right", "arrow-circle-up", "arrow-down", "arrow-left", "arrow-right", "arrow-up", "arrows", "arrows-alt", "arrows-h", "arrows-v", "caret-down", "caret-left", "caret-right", "caret-square-o-down", "caret-square-o-left", "caret-square-o-right", "caret-square-o-up", "caret-up", "chevron-circle-down", "chevron-circle-left", "chevron-circle-right", "chevron-circle-up", "chevron-down", "chevron-left", "chevron-right", "chevron-up", "exchange", "hand-o-down", "hand-o-left", "hand-o-right", "hand-o-up", "long-arrow-down", "long-arrow-left", "long-arrow-right", "long-arrow-up", "toggle-down", "toggle-left", "toggle-right", "toggle-up" ]
		},
		{
			"name": "Video Player Icons",
			"icons": [ "arrows-alt", "backward", "compress", "eject", "expand", "fast-backward", "fast-forward", "forward", "pause", "play", "play-circle", "play-circle-o", "random", "step-backward", "step-forward", "stop", "youtube-play" ]
		},
		{
			"name": "Brand Icons",
			"icons": [ "500px", "adn", "amazon", "android", "angellist", "apple", "behance", "behance-square", "bitbucket", "bitbucket-square", "bitcoin", "black-tie", "btc", "buysellads", "cc-amex", "cc-diners-club", "cc-discover", "cc-jcb", "cc-mastercard", "cc-paypal", "cc-stripe", "cc-visa", "chrome", "codepen", "connectdevelop", "contao", "css3", "dashcube", "delicious", "deviantart", "digg", "dribbble", "dropbox", "drupal", "empire", "expeditedssl", "facebook", "facebook-f", "facebook-official", "facebook-square", "firefox", "flickr", "fonticons", "forumbee", "foursquare", "ge", "get-pocket", "gg", "gg-circle", "git", "git-square", "github", "github-alt", "github-square", "gittip", "google", "google-plus", "google-plus-square", "google-wallet", "gratipay", "hacker-news", "houzz", "html5", "instagram", "internet-explorer", "ioxhost", "joomla", "jsfiddle", "lastfm", "lastfm-square", "leanpub", "linkedin", "linkedin-square", "linux", "maxcdn", "meanpath", "medium", "odnoklassniki", "odnoklassniki-square", "opencart", "openid", "opera", "optin-monster", "pagelines", "paypal", "pied-piper", "pied-piper-alt", "pinterest", "pinterest-p", "pinterest-square", "qq", "ra", "rebel", "reddit", "reddit-square", "renren", "safari", "sellsy", "share-alt", "share-alt-square", "shirtsinbulk", "simplybuilt", "skyatlas", "skype", "slack", "slideshare", "soundcloud", "spotify", "stack-exchange", "stack-overflow", "steam", "steam-square", "stumbleupon", "stumbleupon-circle", "tencent-weibo", "trello", "tripadvisor", "tumblr", "tumblr-square", "twitch", "twitter", "twitter-square", "viacoin", "vimeo", "vimeo-square", "vine", "vk", "wechat", "weibo", "weixin", "whatsapp", "wikipedia-w", "windows", "wordpress", "xing", "xing-square", "y-combinator", "y-combinator-square", "yahoo", "yc", "yc-square", "yelp", "youtube", "youtube-play", "youtube-square" ]
		},
		{
			"name": "Medical Icons",
			"icons": [ "ambulance", "h-square", "heart", "heart-o", "heartbeat", "hospital-o", "medkit", "plus-square", "stethoscope", "user-md", "wheelchair" ]
		}
	];

}(this, jQuery, _, JSNES_Backbone);