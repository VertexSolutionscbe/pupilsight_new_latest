var rbReportsData = {};
var rbTempVarName = null;
var rbTempVarDef = null;
var rbTempVarLabel = null;
var rbTempShortcodeId = null;

var wdt_rb_fetch_data = function( id ){
    if( typeof window.rbReportsData[id] == 'undefined' ){
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'report_builder_get_report_data',
                id: id
            },
            success: function(data){
                window.rbReportsData[id] = data;
                wdt_rb_update_vars_dropdown( id );
            }
        })
    }else{
        wdt_rb_update_vars_dropdown( id );
    }
}

var wdt_rb_update_vars_dropdown = function( id ){
    // Using timeout voodoo as VC has no .onShow() hook
    setTimeout(function(){
        $('div.vc_ui-panel-window[data-vc-shortcode="reportbuilder"] select.name').html('');
        if( typeof window.rbReportsData[id].additionalVars != 'undefined' ){
            for( var i in window.rbReportsData[id].additionalVars ){
                $('div.vc_ui-panel-window[data-vc-shortcode="reportbuilder"] select.name')
                    .append('<option data-val="'+window.rbReportsData[id].additionalVars[i]+'" class="'+i+'" value="'+i+'">'+i+'</option>');
            }
            if( rbTempVarName != null ){
                $('div.vc_ui-panel-window[data-vc-shortcode="reportbuilder"] select.name').val(rbTempVarName);
                rbTempVarName = null;
            }
        }
    }, 1000);

}

var wdt_rb_set_ids = function(){
    $ = jQuery;
    $('div.vc_ui-panel-window[data-vc-shortcode="reportbuilder"] select.id').change();
}



jQuery(document).ready(function(){
   $ = jQuery;

    $(document).on('change','div.vc_ui-panel-window[data-vc-shortcode="reportbuilder"] select.id',function(e){
        var id = $(this).val();
        if( id == 0 ) { return; }
        wdt_rb_fetch_data( id );
    });


    $('#visual_composer_content').on('click','div.wpb_reportbuilder a.vc_control-btn-edit',function(e){

        // Store the saved names etc for buttons
        var $container = $(this).closest('div.wpb_reportbuilder');
        var shortcodeParams = vc.shortcodes._byId[ $container.data('model-id') ].attributes.params;
        rbTempShortcodeId = $container.data('model-id');

        if( shortcodeParams.element == 'varInput' ){
            // Save the selected values to prefill these in editor
            wdt_rb_fetch_data( shortcodeParams.id );
            rbTempVarName = shortcodeParams.name;
            rbTempVarDef = shortcodeParams.default;
            rbTempVarLabel = shortcodeParams.text;
        }

    });

    jQuery(document).on('change','select.name',function(e){
        if( $(this).is(':visible') ){
            if( $(this).val() == null ){ return; }
            var $tab = $(this).closest('div.vc_edit-form-tab');

            if( rbTempVarDef != null ){
                var inputDefault = rbTempVarDef;
            }else{
                var inputDefault = $(this).find('option:selected').data('val');
            }

            if( rbTempVarLabel != null ){
                var inputText = rbTempVarLabel;
            }else{
                var inputText = 'Please input '+$(this).val()+':';
            }

            if( $tab.find('input.text').val() == '' ){
                $tab.find('input.text').val( inputText );
            }
            if( $tab.find('input.default').val() == '' ){
                $tab.find('input.default').val( inputDefault );
            }
        }
    });

    jQuery(document).on('change','select.type',function(e){
        if( $(this).is(':visible') ){
            if( $(this).val() == null ){ return; }
            var $tab = $(this).closest('div.vc_edit-form-tab');

            if( $tab.find('input.text').val() != '' ){
                return;
            }

            if( $(this).val() == 'download' ){
                var text = 'Download report';
            }else{
                var text = 'Save report to Media Library';
            }

            $tab.find('input.text').val( text );
        }
    });

});

if( typeof window.InlineShortcodeView != 'undefined' ) {
    window.InlineShortcodeView_reportbuilder = window.InlineShortcodeView.extend({
        edit: function (e) {
            e.preventDefault();
            e.stopPropagation();

            var shortcodeParams = this.model.attributes.params;
            rbTempShortcodeId = this.model.id;

            if (shortcodeParams.element == 'varInput') {
                // Save the selected values to prefill these in editor
                wdt_rb_fetch_data(shortcodeParams.id);
                rbTempVarName = shortcodeParams.name;
                rbTempVarDef = shortcodeParams.default;
                rbTempVarLabel = shortcodeParams.text;
            }

            window.InlineShortcodeView_reportbuilder.__super__.edit.call(this);
        }
    });
}