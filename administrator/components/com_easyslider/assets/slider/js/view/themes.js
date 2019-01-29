/**
 * Created by GiangTH on 6/5/2017.
 */

void function ( exports, $, _, Backbone ) {
    exports.ES_Themes = B.View.extend({
        constructor: function ES_Themes ( options ) {
            B.View.apply(this, arguments);
        },

        modelEvents: {
            'change:theme': 'render',
        },
        initialize: function () {

        },
        render: function () {
            if(this.model.get('theme') == 'light')
            {
                this.rootView.model.set('grid.color', 'rgba(0,0,0,0.5)');
                $('.es-app').addClass('jsn-es-theme-light-bg-white');
                $('.es-header').addClass('jsn-es-theme-light-bg-white');
                $('.jsn-es-select-theme').addClass('jsn-es-theme-light-bg-semi-white');
                $('.jsn-master .es-sidebar').addClass('jsn-es-theme-light-bg-semi-white');
                $('.es-thumbs-layout').addClass('jsn-es-theme-light-bg-semi-white');
            }
            else
            {
                this.rootView.model.set('grid.color', 'rgba(255,255,255,0.2)');
                $('.es-app').removeClass('jsn-es-theme-light-bg-white');
                $('.es-header').removeClass('jsn-es-theme-light-bg-white');
                $('.jsn-es-select-theme').removeClass('jsn-es-theme-light-bg-semi-white');
                $('.jsn-master .es-sidebar').removeClass('jsn-es-theme-light-bg-semi-white');
                $('.es-thumbs-layout').removeClass('jsn-es-theme-light-bg-semi-white');
            }
        }
    });

}(this, jQuery, _, JSNES_Backbone);