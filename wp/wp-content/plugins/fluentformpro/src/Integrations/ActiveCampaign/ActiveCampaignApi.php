<?php

namespace FluentFormPro\Integrations\ActiveCampaign;
	
class ActiveCampaignApi
{
	protected $apiUrl = null;
	
	protected $apiKey = null;

	public function __construct( $apiUrl, $apiKey = null )
	{	
		$this->apiUrl = $apiUrl;
		$this->apiKey = $apiKey;	
	}
	
	public function default_options()
	{
		return array(
			'api_key'    => $this->apiKey,
			'api_output' => 'json'
		);
	}

	public function make_request( $action, $options = array(), $method = 'GET' )
	{	
		/* Build request options string. */
		$request_options = $this->default_options();
		$request_options['api_action'] = $action;
		
		if ( $request_options['api_action'] == 'contact_edit' )
			$request_options['overwrite'] = '0';
		
		$request_options  = http_build_query( $request_options );
		$request_options .= ( $method == 'GET' ) ? '&' . http_build_query( $options ) : null;
		
		/* Build request URL. */
		$request_url = untrailingslashit( $this->apiUrl ) . '/admin/api.php?' . $request_options;
		
		/* Execute request based on method. */
		switch ( $method ) {
			
			case 'POST':
				$args = array( 'body' => $options );
				$response = wp_remote_post( $request_url, $args );
				break;
				
			case 'GET':
				$response = wp_remote_get( $request_url );
				break;
		}
					
		/* If WP_Error, die. Otherwise, return decoded JSON. */
		if ( is_wp_error( $response ) ) {
		   return $response;
		} else {
			return json_decode( $response['body'], true );
		}	
	}
	
	/**
	 * Test the provided API credentials.
	 * 
	 * @access public
	 * @return bool
	 */
	public function auth_test()
	{	
		/* Build options string. */
		$request_options = $this->default_options();
		$request_options['api_action'] = 'list_paginator';
		$request_options = http_build_query( $request_options );

		/* Setup request URL. */
		$request_url = untrailingslashit( $this->apiUrl ) . '/admin/api.php?' . $request_options;
		
		/* Execute request. */
		$response = wp_remote_get( $request_url );			

		/* If invalid content type, API URL is invalid. */
		if ( is_wp_error( $response ) || strpos( $response['headers']['content-type'], 'application/json' ) != 0 && strpos( $response['headers']['content-type'], 'application/json' ) > 0 )
			throw new \Exception( 'Invalid API URL.' );
		
		/* If result code is false, API key is invalid. */
		$response['body'] = json_decode( $response['body'], true );
		if ( $response['body']['result_code'] == 0 ) {
			throw new \Exception( 'Invalid API Key.' );
		}
		
		return true;
	}
	

	/**
	 * Add a new custom list field.
	 * 
	 * @access public
	 * @param array $custom_field
	 * @return array
	 */
	public function add_custom_field( $custom_field )
	{	
		return $this->make_request( 'list_field_add', $custom_field, 'POST' );
	}

	/**
	 * Get all custom list fields.
	 * 
	 * @access public
	 * @return array
	 */
	public function get_custom_fields()
	{ 	
		return $this->make_request( 'list_field_view', array( 'ids' => 'all' ) );
	}

	/**
	 * Get all forms in the system.
	 * 
	 * @access public
	 * @return array
	 */
	public function get_forms()
	{
		return $this->make_request( 'form_getforms' );	
	}

	/**
	 * Get specific list.
	 * 
	 * @access public
	 * @param int $list_id
	 * @return array
	 */
	public function get_list( $list_id )
	{	
		return $this->make_request( 'list_view', array( 'id' => $list_id ) );	
	}

	/**
	 * Get all lists in the system.
	 * 
	 * @access public
	 * @return array
	 */
	public function get_lists()
	{	
		return $this->make_request( 'list_list', array( 'ids' => 'all' ) );	
	}

	
	/**
	 * Add or edit a contact.
	 * 
	 * @access public
	 * @param mixed $contact
	 * @return array
	 */
	public function sync_contact( $contact )
	{	
		return $this->make_request( 'contact_sync', $contact, 'POST' );	
	}

    /**
     * Add note to contact.
     */
    function add_note( $contact_id, $list_id, $note ) {

        $request = array(
            'id'     => $contact_id,
            'listid' => $list_id,
            'note'   => $note
        );

        return $this->make_request( 'contact_note_add', $request, 'POST' );
    }
}
