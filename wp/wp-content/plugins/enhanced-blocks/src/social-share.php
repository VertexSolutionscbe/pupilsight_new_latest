<?php

function enhanced_blocks_register_socail_sharing(){
	if( !function_exists('register_block_type') ){
		return;
	}

	register_block_type('enhanced-blocks/social-sharing', array(
		'attributes' => array(
			'uniqueID' =>  array(
				'type' => 'string',
                 'default' => ''
			),
			'additionalCssClasses' =>  array(
				'type' => 'string',
				'default' => ''
			),
			'displayBeforeIcon' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'facebook' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'twitter' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'google' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'linkedin' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'pinterest' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'reddit' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'tumblr' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'stumbleupon' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'digg' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'blogger' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'myspace' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'email' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'socialSharingLayout' => array(
				'type' => 'string',
				'default' => 'horizontal',
			),
			'shareButtonAlignment' => array(
				'type' => 'string',
				'default' => 'left',
			),
			'shareButtonStyle' => array(
				'type' => 'string',
			),
			'shareButtonShape' => array(
				'type' => 'string',
				'default' => 'eb-sharing-icon-circle',
			),
			'shareButtonSize' => array(
				'type' => 'string',
				'default' => 'eb-sharing-button-medium',
			),
			'shareButtonColorType' => array(
				'type' => 'string',
				'default' => 'eb-sharing-icon-color-social',
			),
			'bgColor' => array(
				'type' => 'string',
				'default' => '#a7376f',
			),
			'textColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'desktopTextSize' => array(
				'type' => 'number',
				'default' => 20,
			),
			'tabTextSize' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileTextSize' => array(
				'type' => 'number',
				'default' => '',
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => '#a7376f',
			),
			'selectedIconSizeTab' => array(
				'type' => 'string',
			),
			'selectedIconTextTab' => array(
				'type' => 'string',
			),
			'selectedIconSpacingTab' => array(
				'type' => 'string',
			),
			'selectedIconHeightTab' => array(
				'type' => 'string',
			),
			'desktopIconSize' => array(
				'type' => 'number',
				'default' => 20,
			),
			'tabIconSize' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileIconSize' => array(
				'type' => 'number',
				'default' => '',
			),
			'desktopIconHeight' => array(
				'type' => 'number',
				'default' => '',
			),
			'tabIconHeight' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileIconHeight' => array(
				'type' => 'number',
				'default' => '',
			),

			'desktopIconWidth' => array(
				'type' => 'number',
				'default' => '',
			),
			'tabIconWidth' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileIconWidth' => array(
				'type' => 'number',
				'default' => '',
			),
			
			'desktopIconSpacing' => array(
				'type' => 'number',
				'default' => 10,
			),
			'tabIconSpacing' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileIconSpacing' => array(
				'type' => 'number',
				'default' => '',
			),
			'iconColor' => array(
				'type' => 'string',
				'default' => '',
			),
			
		),
		'render_callback' => 'enhanced_blocks_render_social_sharing',
	));
}
add_action('init', 'enhanced_blocks_register_socail_sharing');



function enhanced_blocks_render_social_sharing( $attr ){
	global $post;
	
	if( has_post_thumbnail() ){
		$thumbnail_id = get_post_thumbnail_id($post->ID);
		$thumbnail = $thumbnail_id ? current( wp_get_attachment_image_src( $thumbnail_id, 'full') ) : '';
	} else {
		$thumbnail = null;
	}
	
	$post_title = str_replace( ' ', '%20', get_the_title($post->ID) );
	
	// facebook url
	$facebook_url = 'https://www.facebook.com/sharer/sharer.php?u=' . get_the_permalink() . '&title=' . esc_attr($post_title) . '';
	
	// twitter url
	$twitter_url = 'http://twitter.com/share?text=' . esc_attr($post_title) . '&url='.get_the_permalink().'';
	
	// google url
	$google_url = 'https://plus.google.com/share?url=' . get_the_permalink() . '';
	
	// linkedin url 
	$linkedin_url = 'https://www.linkedin.com/shareArticle?mini=true&url=' . get_the_permalink() . '&title=' . esc_attr($post_title) . '';
	
	// pinterest url 
	$pinterest_url = 'https://pinterest.com/pin/create/button/?&url=' . get_the_permalink() . '&description=' . esc_attr($post_title) . '&media=' . esc_url( $thumbnail ) . '';
	
	// reddit url
	$reddit_url = 'https://www.reddit.com/submit?url=' . get_the_permalink() . '';

	// tumblr url
	$tumblr_url = 'https://www.tumblr.com/widgets/share/tool?canonicalUrl='.get_the_permalink().'&title=' . esc_attr($post_title) . '';
	
	// stumbleupon url
	$stumbleupon_url = 'http://mix.com/submit?url=' . get_the_permalink() . '';

	// digg url
	$digg_url = 'http://digg.com/submit?url=' . get_the_permalink() . '';


	// blogger url
	$blogger_url = 'https://www.blogger.com/blog-this.g?u='.get_the_permalink().'&n=' . esc_attr($post_title) . '';

	// myspace url
	$myspace_url = 'https://myspace.com/post?u='.get_the_permalink().'&t=' . esc_attr($post_title) . '';
	
	// email url 
	$email_url = 'mailto:?subject=' .esc_attr($post_title)  . '&body=' .esc_attr($post_title)  . '&mdash;' . get_the_permalink() . '';
	
	$html = '';
	
	if( isset($attr['facebook']) && $attr['facebook'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-facebook"><i class="eb-icon-facebook"></i><span>%2$s</span></a></li>',
			esc_url( $facebook_url ),
			esc_html__( 'Share on Facebook', 'enhanced-blocks' )
		);
	}
	
	if( isset($attr['twitter']) && $attr['twitter'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-twitter"><i class="eb-icon-twitter"></i><span>%2$s</span></a></li>',
			esc_url( $twitter_url ),
			esc_html__( 'Share on Twitter', 'enhanced-blocks' )
		);
	}

	if( isset($attr['google']) && $attr['google'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-google"><i class="eb-icon-google-plus"></i><span>%2$s</span></a></li>',
			esc_url( $google_url ),
			esc_html__( 'Share on Google', 'enhanced-blocks' )
		);
	}

	if( isset($attr['linkedin']) && $attr['linkedin'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-linkedin"><i class="eb-icon-linkedin"></i><span>%2$s</span></a></li>',
			esc_url( $linkedin_url ),
			esc_html__( 'Share on Linkedin', 'enhanced-blocks' )
		);
	}
	
	if( isset($attr['pinterest']) && $attr['pinterest'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-pinterest"><i class="eb-icon-pinterest"></i><span>%2$s</span></a></li>',
			esc_url( $pinterest_url ),
			esc_html__( 'Share on Pinterest', 'enhanced-blocks' )
		);
	}

	if( isset($attr['reddit']) && $attr['reddit'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-reddit"><i class="eb-icon-reddit"></i><span>%2$s</span></a></li>',
			esc_url( $reddit_url ),
			esc_html__( 'Share on Reddit', 'enhanced-blocks' )
		);
	}

	if( isset($attr['tumblr']) && $attr['tumblr'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-tumblr"><i class="eb-icon-tumblr"></i><span>%2$s</span></a></li>',
			esc_url( $tumblr_url ),
			esc_html__( 'Share on Tumblr', 'enhanced-blocks' )
		);
	}

	if( isset($attr['stumbleupon']) && $attr['stumbleupon'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-stumbleupon"><i class="eb-icon-stumbleupon"></i><span>%2$s</span></a></li>',
			esc_url( $stumbleupon_url ),
			esc_html__( 'Share on StumbleUpon', 'enhanced-blocks' )
		);
	}

	if( isset($attr['digg']) && $attr['digg'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-digg"><i class="eb-icon-digg"></i><span>%2$s</span></a></li>',
			esc_url( $digg_url ),
			esc_html__( 'Share on Digg', 'enhanced-blocks' )
		);
	}

	if( isset($attr['blogger']) && $attr['blogger'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-blogger"><i class="eb-icon-social-blogger"></i><span>%2$s</span></a></li>',
			esc_url( $blogger_url ),
			esc_html__( 'Share on Blogger', 'enhanced-blocks' )
		);
	}

	if( isset($attr['myspace']) && $attr['myspace'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-myspace"><i class="eb-icon-social-myspace"></i><span>%2$s</span></a></li>',
			esc_url( $myspace_url ),
			esc_html__( 'Share on Myspace', 'enhanced-blocks' )
		);
	}
	
	if( isset($attr['email']) && $attr['email'] ) {
		$html .= sprintf( '<li><a href="%1$s" target="_blank" class="eb-share-email"><i class="eb-icon-envelope"></i><span>%2$s</span></a></li>',
			esc_url( $email_url ),
			esc_html__( 'Share on Email', 'enhanced-blocks' )
		);
	}
    $shareButtonStyle = isset($attr['shareButtonStyle']) ? $attr['shareButtonStyle'] : 'eb-sharing-icon-only';
	if( isset($attr['displayBeforeIcon']) && $attr['displayBeforeIcon'] ) {
		$share_content = sprintf(
			'<div class="wp-block-enhanced-blocks-social-sharing %2$s %3$s %4$s %5$s %6$s %7$s %9$s" id="%8$s"><span class="share-icon-name"><i class="eb-icon-share-alt"></i>
                </span><ul class="eb-social-sharing-links">%1$s</ul></div>',
			$html,
			$shareButtonStyle,
			$attr['shareButtonShape'],
			$attr['shareButtonColorType'],
			$attr['shareButtonSize'],
			$attr['shareButtonAlignment'],
			$attr['socialSharingLayout'],
			$attr['uniqueID'],
			$attr['additionalCssClasses']
		);
	} else {
		$share_content = sprintf(
			'<div class="wp-block-enhanced-blocks-social-sharing %2$s %3$s %4$s %5$s %6$s %7$s %9$s" id="%8$s"><ul class="eb-social-sharing-links">%1$s</ul></div>',
			$html,
			$attr['shareButtonStyle'],
			$attr['shareButtonShape'],
			$attr['shareButtonColorType'],
			$attr['shareButtonSize'],
			$attr['shareButtonAlignment'],
			$attr['socialSharingLayout'],
			$attr['uniqueID'],
			$attr['additionalCssClasses']
		);
	}
	return $share_content;
	
	
}