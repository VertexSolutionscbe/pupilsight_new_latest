/**
 * MCE Plugin for ReportBuilder button
 */

var globalMCE = null;

(function() {
    tinymce.create('tinymce.plugins.reportbuilder', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            ed.addButton('reportbuilder', {
                title : 'Insert a ReportBuilder element',
                cmd : 'reportbuilder'
            });

            ed.addCommand( 'reportbuilder', function(){
                jQuery.ajax({
                    url: ajaxurl,
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        action: 'report_builder_get_all_reports'
                    },
                    success: function( reports ){

                        var selectboxValues = [ { value: 0, text: 'Please choose a report...' } ];

                        for( var i in reports ){
                            selectboxValues.push({ value: reports[i].id, text: reports[i].name+' (id: '+reports[i].id+')' })
                        }

                        var rbMCEPopup = ed.windowManager.open({
                            title: 'Report Builder',
                            body: [
                                {
                                    type: 'listbox',
                                    name: 'report_select',
                                    label: 'Choose a Report',
                                    values: selectboxValues,
                                    onselect: function( e ){
                                        var report_id = this.value();
                                        if(  report_id != 0 ){
                                            jQuery('#rbShortcodeParams div').html('Loading...');
                                            jQuery.ajax({
                                                url: ajaxurl,
                                                method: 'POST',
                                                data: {
                                                    action: 'report_builder_get_report_shortcodes_form',
                                                    id: report_id
                                                },
                                                success: function( shortcodesForm ){
                                                    window.globalMCE = ed;
                                                    jQuery('#rbShortcodeParams div').html( shortcodesForm );
                                                }
                                            })
                                        }
                                    }
                                },
                                {
                                    type: 'container',
                                    name: 'reportbuilder_shortcode_container',
                                    label: 'Shortcodes',
                                    id: 'rbShortcodeParams',
                                    html   : ''
                                }
                            ],
                            width: 700,
                            height: 400,
                            autoScroll: true,
                            scrollbars: true,
                            id: 'reportbuilder_shortcodes_modal',
                            buttons: [{
                                text: 'Close',
                                classes: 'widget btn primary first abs-layout-item',
                                onclick: 'close',
                                id: 'rbCloseButton'
                            }]
                        });
                    }
                });
            });

        },

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'reportbuilder',
                author : 'touchmesoft',
                authorurl : 'https://touchmesoft.net',
                infourl : 'https://wpreportbuilder.com',
                version : "1.1.6"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add( 'reportbuilder', tinymce.plugins.reportbuilder );
})();

jQuery(document).ready(function(){

    /**
     * Download/save button shortcode
     */
    jQuery(document).on('click','button.insertButtonShortcode',function(e){
        e.preventDefault();
        var $formGroup = jQuery(this).closest('div.rb_form_group');
        var shortcode_str = '[reportbuilder id='+jQuery('#reportbuilder_report_id').val()+' text="';
        shortcode_str += $formGroup.find('input.buttonLabel').val();
        shortcode_str += '" class="' + $formGroup.find('input.buttonCSSClass').val()+'" ';
        if( jQuery(this).data('type') != '' ){
            shortcode_str += 'type="' + jQuery(this).data('type')+'" ';
        }
        shortcode_str += ']';

        globalMCE.focus();
        globalMCE.execCommand( 'mceInsertContent', 0, shortcode_str )

    });

    /**
     * Additional var button shortcode
     */
    jQuery(document).on('click','button.insertInputShortcode',function(e){
        e.preventDefault();
        var $formGroup = jQuery(this).closest('div.rb_form_group');
        var shortcode_str = '[reportbuilder id='+jQuery('#reportbuilder_report_id').val();
        shortcode_str += ' element="varInput" name="'+$formGroup.find('span.wdt-rb-var-name').html()+'" text="';
        shortcode_str += $formGroup.find('input.inputLabel').val();
        shortcode_str += '" default="' + $formGroup.find('input.inputValue').val()+'" ';
        shortcode_str += ' class="' + $formGroup.find('input.inputCSSClass').val()+'"]';

        globalMCE.focus();
        globalMCE.execCommand( 'mceInsertContent', 0, shortcode_str )

    })


})