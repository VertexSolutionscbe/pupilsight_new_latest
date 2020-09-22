<?php
function enhanced_blocks_active_mesonry_in_post_grid(){
	?>
	<script type="text/javascript" id="enhanced-mesonry">
        ( function( $ ) {
            const windowWidth = screen.width;
            if( windowWidth > 600 ){
                $('.enhanced-masonry-post-grid').imagesLoaded(function () {
                    $('.enhanced-masonry-post-grid').masonry();
                });
            }
           
        } )( jQuery );
	</script>
    <?php
}
add_action('wp_footer', 'enhanced_blocks_active_mesonry_in_post_grid', 1000);

function enhanced_blocks_render_post_grid( $attributes ){
	$recent_posts = wp_get_recent_posts( array(
		'numberposts' => $attributes['postscount'],
		'post_status' => 'publish',
		'order'       => $attributes['order'],
		'orderby'     => $attributes['orderBy'],
		'category'    => isset($attributes['categories']) ? $attributes['categories'] : '',
	), 'OBJECT');

	if ( count( $recent_posts ) === 0 ) {
		return false;
	}
	
	$markup = '';
	
	$widthClass = (isset($attributes['postBlockWidth']) && $attributes['postBlockWidth'] ) ? 'align'.$attributes['postBlockWidth'] : '';
	
	if( isset($attributes['postLayout']) && $attributes['postLayout'] === 'enhanced-slides-post-grid') {
		$markup .= sprintf('<div id="%5$s" class="wp-block-enhanced-blocks-post-grid post-grid-view %6$s %7$s %8$s" data-count="%1$d" data-slides-to-show="%2$s" data-autoplay="%3$s" data-navigation="%4$s">', count( $recent_posts ), $attributes['slidesToShow'], $attributes['autoPlay'], $attributes['navigation'], $attributes['uniqueID'], $attributes['postLayout'], $widthClass, $attributes['additionalCssClasses']);
		$markup .= sprintf('<div class="enhanced-blocks-post-grid-inner eb-d-flex eb-row eb-flex-wrap">');
	}
	if( $attributes['postLayout'] !== 'enhanced-slides-post-grid') {
		$markup .= '<div class="wp-block-enhanced-blocks-post-grid ' . esc_attr( $attributes['postLayout'] )
		           . ' post-grid-view eb-d-flex eb-row eb-flex-wrap ' . esc_attr( $widthClass ) . ' ' .$attributes['additionalCssClasses'].'" id="'.$attributes['uniqueID'].'">';
	}
	foreach ( $recent_posts as $post ){
		$post_id = $post->ID;
		$post_thumbnail_id = get_post_thumbnail_id($post_id);
		$columns = (isset($attributes['columns']) && $attributes['columns'] ) ? $attributes['columns'] : 3;

		$gridView = $attributes['postLayout'] === 'grid' || $attributes['postLayout'] === 'enhanced-masonry-post-grid' ? 'post-item column-'.$columns.' '.$attributes['align'].'' : 'post-item '.$attributes['align'].'';
		
		// start the post-item wrap
		$markup .= sprintf( '<article class="%1$s">', esc_attr($gridView) );
		
		$postItemHeight = isset($attributes['equalHeight']) && $attributes['equalHeight'] ? 'enhanced-equal-height' : '';
		// start inner wrapper
		$markup .= '<div class="post-item-inner-wrapper '.esc_attr($postItemHeight).'">';
		
		if( isset($attributes['displayPostImage']) && $attributes['displayPostImage'] ) {
			$markup .= sprintf( '<div class="post-image"><a href="%1$s" target="_blank" rel="bookmark">%2$s</a></div>',
				esc_url( get_permalink( $post_id ) ),
				wp_get_attachment_image( $post_thumbnail_id, ''.$attributes['postImageSizes'].'')
			);
		}
		// start the post content wrap
		$markup .= '<div class="post-content-area">';
		
		// start the post meta wrap
		$markup .= '<div class="post-meta">';
		
		if( isset($attributes['displayPostAuthor']) && $attributes['displayPostAuthor'] ) {
			$markup .= sprintf(
				'<a target="_blank" href="%2$s">%1$s</a>',
				esc_html( get_the_author_meta( 'display_name', $post->post_author ) ),
				esc_url( get_author_posts_url( get_author_posts_url( $post->post_author ) ) )
			);
		}
		
		if( isset($attributes['displayPostDate']) && $attributes['displayPostDate'] ) {
			$markup .= sprintf(
				'<time datetime="%1$s">%2$s</time>',
				esc_attr( get_the_date( 'c', $post_id ) ),
				esc_html( get_the_date( '', $post_id ) )
			);
		}
		if( isset($attributes['displayPostComment']) && $attributes['displayPostComment'] ) {
			$comments_num = get_comments_number();
			if ( $comments_num == 0 ) {
				$comments = esc_html__( 'No Comments', 'enhanced-blocks' );
			} elseif ( $comments_num > 1 ) {
				$comments = esc_html( $comments_num . ' Comment' );
			} else {
				$comments = esc_html__( '1 Comment', 'enhanced-blocks' );
			}

			$markup .= sprintf( '<span class="comment">%1$s</span>', $comments );
		}
		$markup .= '</div>';
		// close the post meta wrap
		
		// start the post title wrap
        if( get_the_title($post_id) ) {
	        $markup .= sprintf( '<h2 class="post-title"><a href="%1$s" target="_blank" rel="bookmark">%2$s</a></h2>',
		        esc_url( get_permalink( $post_id ) ),
		        esc_html( get_the_title( $post_id ) ) );
        }
		// close the post title wrap
		
		// start the post excerpt wrap
		$content = $post->post_content;
		if( $content && $attributes['displayPostExcerpt'] ) {
			$excerpt = html_entity_decode( $content, ENT_QUOTES, get_option( 'blog_charset' ) );
			
			$excerpt = esc_attr( wp_trim_words($excerpt, $attributes['excerptLength'], ' [&hellip;]' ) );
            $get_entity = substr( $excerpt, -10);
			$excerpt = str_replace($get_entity, '', $excerpt);
			if( $excerpt ) {
				$markup .= sprintf( ' <div class="post-excerpt"><div><p>%1$s</p></div></div>', esc_html( $excerpt ) );
			}
			
		}
		// close the post excerpt wrap
		
		// start the post read more wrap
		if( (isset($attributes['displayPostReadMoreButton']) && $attributes['displayPostReadMoreButton'] ) && (isset( $attributes['postReadMoreButtonText']) && !empty( $attributes['postReadMoreButtonText'])) ) {
			$markup .= sprintf( '<div class="post-btn"><a class="post-read-moore" href="%1$s" target="%3$s" rel="bookmark">%2$s</a></div>', esc_url( get_permalink( $post_id ) ),esc_html( $attributes['postReadMoreButtonText'] ), isset($attributes['linkTarget']) && !empty($attributes['linkTarget']) ? '_blank' : '_self' );
		}
		// close the post read more wrap
		
		$markup .= '</div>';
		// close the post content wrap

		$markup .= '</div>';
		// close the inner wrapper
		
		$markup .= '</article>';
		// close the post-item wrap
        
        
	}
	if( isset($attributes['postLayout']) && $attributes['postLayout'] === 'enhanced-slides-post-grid') {
		$markup .= '</div>';
    }
	$markup .= '</div>';
	

	return $markup;

}

function enhanced_blocks_register_post_grid(){
	
	if( !function_exists('register_block_type') ){
		return;
	}

	register_block_type( 'enhanced-blocks/post-grid', array(
		'attributes' => array(
			'uniqueID' =>  array(
				'type' => 'string',
				'default' => ''
			),
			'additionalCssClasses' => array(
				'type' => 'string',
				'default' => '',
			),
            'postBlockWidth' => array(
				'type' => 'string',
			),
			'align' => array(
				'type' => 'string',
				'default' => 'eb-text-left',
			),
			'categories' => array(
				'type' => 'string',
			),
			'postscount' => array(
				'type' => 'number',
				'default' => 5,
			),
			'equalHeight' => array(
				'type' => 'boolen',
				'default' => true
			),
			'columnGap' => array(
				'type' => 'number',
				'default' => 15,
			),
			'rowGap' => array(
				'type' => 'number',
				'default' => 30,
			),
			'order' => array(
				'type' => 'string',
				'default' => 'desc',
			),
			'orderBy'  => array(
				'type' => 'string',
				'default' => 'date',
			),
			'columns' => array(
				'type' => 'number',
			),
			'postLayout' => array(
				'type' => 'string',
				'default' => 'grid',
			),
			'postImageSizes' => array(
				'type' => 'string',
				'default' => 'full',
			),
			'displayPostImage' => array(
				'type' => 'boolen',
				'default' => true
			),
			'displayPostDate' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'displayPostAuthor' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'displayPostComment' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'displayPostExcerpt' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'excerptLength' => array(
				'type' => 'number',
				'default' => 20,
			),
			'displayPostReadMoreButton' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'postReadMoreButtonText' => array(
				'type' => 'string',
				'default' => 'Read More',
			),
			'linkTarget' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'slidesToShow' => array(
				'type' => 'number',
				'default' => 3,
			),
			'autoPlay' => array(
				'type' => 'boolen',
				'default' => true
			),
			'navigation' => array(
				'type' => 'string',
				'default' => 'arrows'
			),

			// meta attributes
            'metaColor' => array(
	            'type' => 'string',
            ),
            'metaAlignment' => array(
	            'type' => 'string',
            ), 
            'metaTextTransform' => array(
	            'type' => 'string',
            ),
			
			// text size
			'desktopMetaTextSize' => array(
				'type' => 'number',
				'default' => 14,
			),
			'tabMetaTextSize' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileMetaTextSize' => array(
				'type' => 'number',
				'default' => '',
			),
			// spacing
			'desktopMetaSpacing' => array(
				'type' => 'number',
				'default' => 15,
			),
			'tabMetaSpacing' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileMetaSpacing' => array(
				'type' => 'number',
				'default' => '',
			),
			
			//line height
			'desktopMetaLineHeight' => array(
				'type' => 'number',
				'default' => 25,
			),
			'tabMetaLineHeight' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileMetaLineHeight' => array(
				'type' => 'number',
				'default' => '',
			),

             // Google Fonts..
			'metaFontSubset' => array(
				'type' => 'string',
				'default' => '',
			),
			'metaFontVariant' => array(
				'type' => 'string',
				'default' => '',
			),
			'metaFontWeight' => array(
				'type' => 'string',
				'default' => '400',
			),
			'metaFontStyle' => array(
				'type' => 'string',
				'default' => 'normal',
			),
            'metaLoadGoogleFont' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'metaGoogleFont' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'metaTypography' => array(
				'type' => 'string',
				'default' => '',
			),

            // title attributes
			'titleColor' => array(
				'type' => 'string',
			),
			'titleAlignment' => array(
				'type' => 'string',
			),
			'titleTextTransform' => array(
				'type' => 'string',
			),

			// text size
			'desktopTitleTextSize' => array(
				'type' => 'number',
				'default' => 14,
			),
			'tabTitleTextSize' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileTitleTextSize' => array(
				'type' => 'number',
				'default' => '',
			),
			// spacing
			'desktopTitleTopSpacing' => array(
				'type' => 'number',
				'default' => 10,
			),
			'tabTitleTopSpacing' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileTitleTopSpacing' => array(
				'type' => 'number',
				'default' => '',
			),

			//line height
			'desktopTitleLineHeight' => array(
				'type' => 'number',
				'default' => 25,
			),
			'tabTitleLineHeight' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileTitleLineHeight' => array(
				'type' => 'number',
				'default' => '',
			),

			// Google Fonts..
			'titleFontSubset' => array(
				'type' => 'string',
				'default' => '',
			),
			'titleFontVariant' => array(
				'type' => 'string',
				'default' => '',
			),
			'titleFontWeight' => array(
				'type' => 'string',
				'default' => '400',
			),
			'titleFontStyle' => array(
				'type' => 'string',
				'default' => 'normal',
			),
			'titleLoadGoogleFont' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'titleGoogleFont' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'titleTypography' => array(
				'type' => 'string',
				'default' => '',
			),



			// content attributes
			'contentColor' => array(
				'type' => 'string',
			),
			'contentAlignment' => array(
				'type' => 'string',
			),
			'contentTextTransform' => array(
				'type' => 'string',
			),

			// text size
			'desktopContentTextSize' => array(
				'type' => 'number',
				'default' => 14,
			),
			'tabContentTextSize' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileContentTextSize' => array(
				'type' => 'number',
				'default' => '',
			),
			// spacing
			'desktopContentTopSpacing' => array(
				'type' => 'number',
				'default' => 15,
			),
			'tabContentTopSpacing' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileContentTopSpacing' => array(
				'type' => 'number',
				'default' => '',
			),

			//line height
			'desktopContentLineHeight' => array(
				'type' => 'number',
				'default' => 26,
			),
			'tabContentLineHeight' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileContentLineHeight' => array(
				'type' => 'number',
				'default' => '',
			),

			// Google Fonts..
			'contentFontSubset' => array(
				'type' => 'string',
				'default' => '',
			),
			'contentFontVariant' => array(
				'type' => 'string',
				'default' => '',
			),
			'contentFontWeight' => array(
				'type' => 'string',
				'default' => '400',
			),
			'contentFontStyle' => array(
				'type' => 'string',
				'default' => 'normal',
			),
			'contentLoadGoogleFont' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'contentGoogleFont' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'contentTypography' => array(
				'type' => 'string',
				'default' => '',
			),



			// button attributes
			'buttonColor' => array(
				'type' => 'string',
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => '',
			),
			'buttonAlignment' => array(
				'type' => 'string',
			),
			'buttonTextTransform' => array(
				'type' => 'string',
			),

			// text size
			'desktopButtonTextSize' => array(
				'type' => 'number',
				'default' => 14,
			),
			'tabButtonTextSize' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileButtonTextSize' => array(
				'type' => 'number',
				'default' => '',
			),
			// spacing
			'desktopButtonTopSpacing' => array(
				'type' => 'number',
				'default' => 15,
			),
			'tabButtonTopSpacing' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileButtonTopSpacing' => array(
				'type' => 'number',
				'default' => '',
			),

			//line height
			'desktopButtonLineHeight' => array(
				'type' => 'number',
				'default' => 25,
			),
			'tabButtonLineHeight' => array(
				'type' => 'number',
				'default' => '',
			),
			'mobileButtonLineHeight' => array(
				'type' => 'number',
				'default' => '',
			),

			// Google Fonts..
			'buttonFontSubset' => array(
				'type' => 'string',
				'default' => '',
			),
			'buttonFontVariant' => array(
				'type' => 'string',
				'default' => '',
			),
			'buttonFontWeight' => array(
				'type' => 'string',
				'default' => '700',
			),
			'buttonFontStyle' => array(
				'type' => 'string',
				'default' => 'normal',
			),
			'buttonLoadGoogleFont' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'buttonGoogleFont' => array(
				'type' => 'boolean',
				'default' => false,
			),
			'buttonTypography' => array(
				'type' => 'string',
				'default' => '',
			),

			// box attributes
			'bgTypes' => array(
				'type' => 'string',
				'default' => 'classic',
			),
            'selectedBgTab' => array(
	            'type' => 'string',
	            'default' => 'classic',
            ),
			'bgColor' => array(
				'type' => 'string',
			),
			'gradientType' => array(
				'type' => 'string',
				'default' => 'linear'
			),
			'gradientColor' => array(
				'type' => 'string',
			),
			'gradientSecondColor' => array(
				'type' => 'string',
				'default' => '#4c10cd'
			),
			'gradientLocation' => array(
				'type' => 'number',
                'default' => 1
			),
			'gradientSecondLocation' => array(
				'type' => 'number',
				'default' => 100
			),
			'gradientAngle' => array(
				'type' => 'number',
				'default' => 180
			),
			'gradientPosition' => array(
				'type' => 'string',
				'default' => 'center center'
			),
			
			//border
			'selectedBorderTabPanel' => array(
				'type' => 'string',
				'default' => 'normal'
			),
			
			'borderTypeNormal' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderTypeHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderTransitionDurationHover' => array(
				'type' => 'number',
				'default' => 0.3
			),
			'borderTypeHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColorNormal' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderWidthNormalTop' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderWidthNormalRight' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderWidthNormalBottom' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderWidthNormalLeft' => array(
				'type' => 'number',
				'default' => ''
			),

            // Border Width Hover
			'borderWidthHoverTop' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderWidthHoverRight' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderWidthHoverBottom' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderWidthHoverLeft' => array(
				'type' => 'number',
				'default' => ''
			),

			// Border Radius Normal
			'borderRadiusNormalTop' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderRadiusNormalRight' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderRadiusNormalBottom' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderRadiusNormalLeft' => array(
				'type' => 'number',
				'default' => ''
			),

			// Border Radius Hover
			'borderRadiusHoverTop' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderRadiusHoverRight' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderRadiusHoverBottom' => array(
				'type' => 'number',
				'default' => ''
			),
			'borderRadiusHoverLeft' => array(
				'type' => 'number',
				'default' => ''
			),
			
			// Box shadow Normal
			'boxShadowNormalColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'boxShadowHoverColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'shadowHorizontalNormal' => array(
				'type' => 'number',
				'default' => 0
			),
			'shadowHorizontalHover' => array(
				'type' => 'number',
				'default' => 0
			),
			'shadowVerticalNormal' => array(
				'type' => 'number',
				'default' => 0
			),
			'shadowVerticalHover' => array(
				'type' => 'number',
				'default' => 0
			),

			'shadowBlurNormal' => array(
				'type' => 'number',
				'default' => 0
			),
			'shadowBlurHover' => array(
				'type' => 'number',
				'default' => 0
			),
			'shadowSpreadNormal' => array(
				'type' => 'number',
				'default' => 0
			),
			'shadowSpreadHover' => array(
				'type' => 'number',
				'default' => 0
			),
			'shadowPositionNormal' => array(
				'type' => 'string',
				'default' => ''
			),
			'shadowPositionHover' => array(
				'type' => 'string',
				'default' => ''
			),
		),
		'render_callback' => 'enhanced_blocks_render_post_grid',
	));
	
	
}
add_action( 'init', 'enhanced_blocks_register_post_grid' );


/**
 * Create API fields for additional info
 */

function enhanced_blocks_register_rest_fields() {
	
	register_rest_field(
		'post',
		'enhanced_blocks_featured_media_urls',
		array(
			'get_callback' => 'get_enhanced_blocks_featured_media',
			'update_callback' => null,
			'schema' => array(
				'description' => __( 'Different Sized Featured Images', 'enhanced-blocks'),
				'type' => 'array'
			)
		)
	);

	register_rest_field(
		'post',
		'enhanced_blocks_comment_info',
		array(
			'get_callback' => 'get_enhanced_blocks_comment_info',
			'update_callback' => null,
			'schema' => null
		)
		
	);
}
add_action('rest_api_init', 'enhanced_blocks_register_rest_fields');

function get_enhanced_blocks_featured_media($object){
	$featured_media = wp_get_attachment_image_src( $object['featured_media'], 'full', false );

	return array(
		'thumbnail' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'thumbnail',
			false
		) : '',
		'enhanced_blocks_landscape_large' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'enhanced_blocks_landscape_large',
			false
		) : '',
		'enhanced_blocks_portrait_large' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'enhanced_blocks_portrait_large',
			false
		) : '',
		'enhanced_blocks_square_large' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'enhanced_blocks_square_large',
			false
		) : '',
		'enhanced_blocks_landscape' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'enhanced_blocks_landscape',
			false
		) : '',
		'enhanced_blocks_portrait' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'enhanced_blocks_portrait',
			false
		) : '',
		'enhanced_blocks_square' => is_array($featured_media) ? wp_get_attachment_image_src(
			$object['featured_media'],
			'enhanced_blocks_square',
			false
		) : '',
		'full' => is_array($featured_media) ? $featured_media : '',
	);

}
/**
 * Add image sizes
 */
function enhanced_blocks_image_sizes() {
	add_image_size( 'enhanced_blocks_landscape_large', 1200, 800, true );
	add_image_size( 'enhanced_blocks_portrait_large', 1200, 1800, true );
	add_image_size( 'enhanced_blocks_square_large', 1200, 1200, true );
	add_image_size( 'enhanced_blocks_landscape', 600, 400, true );
	add_image_size( 'enhanced_blocks_portrait', 600, 900, true );
	add_image_size( 'enhanced_blocks_square', 600, 600, true );
}
add_action( 'after_setup_theme', 'enhanced_blocks_image_sizes' );


function get_enhanced_blocks_comment_info($object){
	
	$comments_count = wp_count_comments( $object['id'] );
	$comments_num = $comments_count->total_comments;
	
	if( $comments_num == 0 ){
		return esc_html__('No Comments', 'enhanced-blocks');
	} elseif( $comments_num > 1 ){
		return esc_html($comments_num .' Comment');
	} else {
		return esc_html__('1 Comment', 'enhanced-blocks');
	}
	
}