jQuery( document ).ready( function( $ ) {
	$( '.enhanced-welcome-tab .nav-tab-link' ).click( function( event ) {
		event.preventDefault();
		const contain = $( this ).closest( '.enhanced-welcome-tab' );
		const panel = contain.find( '.nav-tab-wrapper' );
		const active = panel.find( '.nav-tab-active' );
		const opencontent = $( this ).closest( '.enhanced-panel-contain' ).find( '.nav-tab-content.panel_open' );
		const contentid = $( this ).data( 'tab-id' );
		const tab = panel.find( 'a[data-tab-id="' + contentid + '"]' );
		if ( active.data( 'tab-id' ) == contentid ) {
			//leave
		} else {
			tab.addClass( 'nav-tab-active' );
			active.removeClass( 'nav-tab-active' );
			opencontent.removeClass( 'panel_open' );
			$( '#' + contentid ).addClass( 'panel_open' );
		}

		return false;
	} );
} );
