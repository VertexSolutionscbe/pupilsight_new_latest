<script id="wdt-rb-custom-var-tmpl" type="text/x-jsrender">
    <div class="row wdt-rb-custom-var-block">
        <div class="col-sm-4">
            <div class="form-group">
                <div class="fg-line">
                    <input type="text" class="form-control input-sm" value="{{>name}}" id="wdt-rb-var-name" placeholder="<?php _e( 'Name of the variable', 'wpdatatables' ); ?>">
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <div class="fg-line">
                    <input type="text" class="form-control input-sm" value="{{>value}}" id="wdt-rb-var-default-value" placeholder="<?php _e( 'Default value of the variable', 'wpdatatables' ); ?>">
                </div>
            </div>
        </div>
        <div class="col-sm-4">
         <div class="wpdt-shortcode-container">
                <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="var-{{>name.toLowerCase().replace('/\s/g', '')}}" data-placement="top" title="" data-original-title="To use this shortcode you can click this button to copy it to the clipboard and then paste it to your template">
                    <i class="wpdt-icon-copy"></i>
                </a>
                <span class="wdt-shortcode-class wdt-var" id="wdt-var-{{>name.toLowerCase().replace('/\s/g', '')}}-shortcode-id">${<span id="wdt-tb-var-definition">{{>name}}</span>}</span>
          </div>
        </div>
        <div class="col-sm-1">
            <ul class="actions pull-right">
                <li id="wdt-rb-delete-custom-var">
                    <a>
                        <i class="wpdt-icon-trash"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</script>