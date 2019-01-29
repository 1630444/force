void function ( exports, $, _, Backbone ) {

	var log = console.log.bind(console)


	/* Medium Toobar */

	var ES_MediumToolbarModel = B.Model({
		style: B.Collection(B.Model({ key: '', value: '' }))
	})
	var ES_MediumToolbar = exports.ES_MediumToolbar = ES_PanelView.extend({
		constructor: function ES_MediumToolbar ( options ) {
			_.bindAll(this, 'update', 'change', 'select')
			this.update = _.debounce(this.update);
			this.change = _.debounce(this.change);
			options.model = new ES_MediumToolbarModel;
			ES_PanelView.call(this, options)
		},
		events: {
			'mousedown': function( e ) {
				this.$medium.addClass('medium-toolbar-editing');
			},
			'input .classname-input': function( e ) {
				var topParent = this.getTopParentOf(this.lastElementAtCursor);
				topParent.className = e.currentTarget.value;
			},
			'change .tagname-select': function( e ) {
				var tagName = e.currentTarget.value;
				var topParent = this.getTopParentOf(this.lastElementAtCursor);
				var newEl = this.medium.utils.changeTag(topParent, tagName)
				$(newEl).focus().selectText()
				this.change()
				this.update()
			},
			'mousedown .invoke-btn' : function( e ) {
				this.medium.focus()
				this.medium.invokeElement($(e.currentTarget).data('tag'), {});
				//$(e.currentTarget).toggleClass('active');
				this.change()
				this.update()
			},
			'mousedown .done-btn': function( e ) {
				this.close();
			},
			'mousedown .insert-fa-btn': function( e ) {
				this.rootView.fontAwesomePicker
					.open({ target: e.currentTarget })
					.select(function ( name, className, content ) {
						var el = document.createElement('span');
						el.className = 'fa';
						el.innerHTML = content;
						el.setAttribute('contenteditable', false)
						this.medium.focus()
						this.medium.insertHtml(el)
						this.medium.cursor.caretToAfter(el)
						//$(el).before('&nbsp;').after('&nbsp;')
						//this.medium.insertHtml(space)
						//this.medium.insertHtml('<span class="fa">'+ text +'</span>')
					}, this)
			}
		},
		ready: function () {
			$(window).resize(_.debounce(this.close, 100, true));
			$(window).resize(_.debounce(this.update, 100));
			this.on('close', function () {
				this.$medium &&
				this.$medium.removeClass('medium-toolbar-editing').blur()
			});
		},
		inspect: function ( medium, event ) {
			if ( this.medium === medium )
				return;

			if ( this.$medium ) {
				this.$medium.off('keydown', this.change)
				this.$medium.off('keypress', this.change)
				this.$medium.off('mousedown', this.select)
				this.$medium.off('click', this.select)
				this.$medium.off('paste', this.change)
			}

			window.medium = medium;
			this.medium = medium;
			this.$medium = $(medium.element);

			this.$medium.on('keydown', this.change)
			this.$medium.on('keypress', this.change)
			this.$medium.on('mousedown', this.select)
			this.$medium.on('click', this.select)
			this.$medium.on('paste', this.change)

			//if (event)
			//	$(event.target).selectText();
			this.rootView.fontAwesomePicker.close();
			//this.lastSelection && this.medium.selection.restoreSelection(this.lastSelection)

			this.change()
			this.update()
		},
		release: function () {
			if ( this.medium ) {
				this.medium.lastSelection = this.medium.selection.saveSelection()
				this.$medium.off('keydown', this.change)
				this.$medium.off('keypress', this.change)
				this.$medium.off('mousedown', this.select)
				this.$medium.off('click', this.select)
				this.$medium.off('paste', this.change)
				this.$medium.deselectText()
			}
			delete this.medium;
			delete this.$medium;
			this.$el.hide()
		},
		update: function ( e ) {
			if ( !this.medium )
				return;
			if ( !this.lastElementAtCursor || this.lastElementAtCursor === this.medium.element )
				return this.$el.hide();

			this.rootView.fontAwesomePicker.close();

			var activeEl = this.lastElementAtCursor;
			var topParent = this.getTopParentOf(this.lastElementAtCursor);
			//var nodeDomHierchy = this.getDomHierchy(this.lastElementAtCursor);

			this.$('.classname-input').val(topParent.className);
			this.$('.tagname-select').val(topParent.tagName);
			//this.$('.bold-btn')[ activeEl.tagName == 'B' ? 'addClass':'removeClass' ]('active');
			//this.$('.italic-btn')[ activeEl.tagName == 'I' ? 'addClass':'removeClass' ]('active');
			//this.$('.underline-btn')[ activeEl.tagName == 'U' ? 'addClass':'removeClass' ]('active');
			//this.$('.node-hierchy').text(nodeDomHierchy);

			this.open({ target: topParent, direction: 'top', background: false })
		},
		change: function ( e ) {
			if ( !this.medium )
				return;

			/**
			 * Make sure we have range count. Otherwise, the following error will be occurred on Safari:
			 *
			 * IndexSizeError: DOM Exception 1: Index or size was negative, or greater than the allowed value.
			 *
			 * => This problem should be fixed already after setting '-webkit-user-select: initial' for newly
			 * added text item. However, still keep this as a fall-back fix in case the property '-webkit-user-select'
			 * is overridden by accident.
			 */
			var sel = window.getSelection && window.getSelection();

			if ( sel && sel.rangeCount == 0 ) {
				$( this.medium.element ).children().css( '-webkit-user-select', 'initial' );
			}

			this.medium.focus();
			this.lastElementAtCursor = this.medium.cursor.parent();
			//if (e && e.type == 'keydown') {
			//	switch (e.keyCode) {
			//		case 8:
			//			var topParent = this.getTopParentOf(this.lastElementAtCursor);
			//			if (topParent.innerText.length == 0)
			//				topParent.innerHTML = '';
			//			this.lastElementAtCursor = topParent;
			//			break;
			//		case 37:
			//			log(this.lastElementAtCursor)
			//			break;
			//		case 39:
			//			log(this.lastElementAtCursor)
			//			break;
			//	}
			//}
			this.lastSelection = this.medium.selection.saveSelection();
			this.update()
		},
		select: function ( e ) {
			if ( !this.medium )
				return;
			this.lastElementAtCursor = e.target;
			this.lastSelection = this.medium.selection.saveSelection();
			if ( $(e.target).is(this.medium.element) ) {
				this.lastElementAtCursor = this.medium.cursor.parent();
			}
			if ( e.type == 'mousedown' && $(e.target).is('.fa') ) {
				this.$medium.addClass('medium-toolbar-editing');
				this.rootView.mediumToolbar.$el.hide();
				this.rootView.fontAwesomePicker
					.open({ target: e.target, activeTarget: false })
					.select(function ( name, className, content ) {
						e.target.innerHTML = content
						this.$medium.focus();
						this.$medium.removeClass('medium-toolbar-editing');
						$(e.target).selectText()
						this.update()
					}, this);
				return;
			}
			this.update()
		},
		getDomHierchy: function ( element ) {
			var parent = element.parentNode;
			var text = element.tagName;
			while ( parent !== this.medium.element ) {
				text = parent.tagName + '.' + parent.className.replace(/\s+/g, '.') + ' > ' + text;
				parent = parent.parentNode;
			}
			return text
		},
		getTopParentOf: function ( element ) {
			var parent = element;
			var found = element;
			while ( parent && parent !== this.medium.element ) {
				found = parent;
				parent = found.parentNode;
			}
			return found;
		}
	})

}(this, jQuery, _, JSNES_Backbone);