<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Enhanced_Blocks_Front_End_Css {
	private static $instance = null;
	
	// google font
	public static $gfonts = array();

	// instance control
	public static function get_instance(){
		if( is_null(self::$instance) ){
			self::$instance = new self();
		}
		return self::$instance;
	}

	// constructor function
	public function  __construct() {
		add_action('wp_head', array( $this, 'enhanced_blocks_frontend_inline_css'), 80);
		add_action('wp_head', array( $this, 'frontend_gfonts'), 90);
	
	}

	
	/**
	 * Load the frontend Google Fonts
	 */
	public function frontend_gfonts() {
		if ( empty( self::$gfonts ) ) {
			return;
		}
		$print_google_fonts = apply_filters( 'enhanced_blocks_print_google_fonts', true );
		if ( ! $print_google_fonts ) {
			return;
		}
		$link    = '';
		$subsets = array();
		foreach ( self::$gfonts as $key => $gfont_values ) {
			if ( ! empty( $link ) ) {
				$link .= '%7C'; // Append a new font to the string.
			}
			$link .= $gfont_values['fontfamily'];
			if ( ! empty( $gfont_values['fontvariants'] ) ) {
				$link .= ':';
				$link .= implode( ',', $gfont_values['fontvariants'] );
			}
			if ( ! empty( $gfont_values['fontsubsets'] ) ) {
				foreach ( $gfont_values['fontsubsets'] as $subset ) {
					if ( ! in_array( $subset, $subsets ) ) {
						array_push( $subsets, $subset );
					}
				}
			}
		}
		if ( ! empty( $subsets ) ) {
			$link .= '&amp;subset=' . implode( ',', $subsets );
		}
		echo '<link rel="stylesheet" id="enhanced-block-fonts" href="https://fonts.googleapis.com/css?family=' . esc_attr( str_replace( '|', '%7C', $link ) ) . '"  type="text/css" media="all" />';
	}

	public function enhanced_blocks_parse_blocks( $content ) {
		if ( class_exists( 'WP_Block_Parser' ) ) {
			$parser = new WP_Block_Parser();
			return $parser->parse( $content );
		} elseif ( function_exists( 'gutenberg_parse_blocks' ) ) {
			return gutenberg_parse_blocks( $content );
		} else {
			return false;
		}
	}
	public function enhanced_blocks_frontend_inline_css(){
		if ( function_exists( 'has_blocks' ) && has_blocks( get_the_ID() ) ) {
			global $post;
			if( !is_object($post) ) {
				return;
			}
			if ( has_blocks( $post->post_content ) ) {
				$blocks = $this->enhanced_blocks_parse_blocks($post->post_content);
				$css = '<style type="text/css" id="enhanced-blocks-frontend-css" media="all">';
				
				foreach ( $blocks as $index => $block ) {
					if ( $block['blockName'] !== 'enhanced-blocks/row-layout' ) {
						// Testimonial Block Retrieve 
						if ( $block['blockName'] === 'enhanced-blocks/eb-testimonial' ) {
							$attrs = $block['attrs'];
							$this->enhanced_testimonial_gfont( $attrs ); //Google font attrs
							if ( isset( $attrs['uniqueID'] ) ) {
								$unique_id = '#' . $attrs['uniqueID']; // fontend css 
								$css       .= $this->enhanced_testimonial_blocks_css( $attrs, $unique_id );
							}

						}

						// Heading Block Retrieve 
						if ( $block['blockName'] === 'enhanced-blocks/eb-heading' ) {
							$attrs = $block['attrs'];
							$this->enhanced_heading_gfont( $attrs ); //Google font attrs
							if ( isset( $attrs['uniqueID'] ) ) {
								$unique_id = '#' . $attrs['uniqueID']; // fontend css 
								$css       .= $this->enhanced_heading_blocks_css( $attrs, $unique_id );
							}
						}

						// Profile Block Retrieve 
						if ( $block['blockName'] === 'enhanced-blocks/author-profile' ) {
							$attrs = $block['attrs'];
							$this->enhanced_profile_gfont( $attrs ); //Google font attrs
							if ( isset( $attrs['uniqueID'] ) ) {
								$unique_id = '#' . $attrs['uniqueID']; // fontend css 
								$css       .= $this->enhanced_profile_blocks_css( $attrs, $unique_id );
							}
							
						}

						// social-sharing
						if ( $block['blockName'] === 'enhanced-blocks/social-sharing' ) {
							if ( isset( $block['attrs'] ) ) {
								$attrs = $block['attrs'];
								if ( isset( $attrs['uniqueID'] ) ) {
									$unique_id = '#' . $attrs['uniqueID'];
									$css       .= $this->generate_social_share_css( $attrs, $unique_id );
								}
							}
						}
						// comparison-slider
						if ( $block['blockName'] === 'enhanced-blocks/enhanced-before-and-after-image' ) {
							if ( isset( $block['attrs'] ) ) {
								$attrs = $block['attrs'];
								if ( isset( $attrs['uniqueID'] ) ) {
									$unique_id = '#' . $attrs['uniqueID'];
									$css       .= $this->generate_comparison_slider_css( $attrs, $unique_id );

								}
							}
						}
						// call-to-action
						if ( $block['blockName'] === 'enhanced-blocks/enhanced-cta' ) {
							$attrs = $block['attrs'];
							$this->enhanced_cta_gfont( $attrs ); //Google font attrs
						}
						// block-icon
						if ( $block['blockName'] === 'enhanced-blocks/enhanced-block-icon' ) {
							if ( isset( $block['attrs'] ) ) {
								$attrs = $block['attrs'];
								if ( isset( $attrs['uniqueID'] ) ) {
									$unique_id = '#' . $attrs['uniqueID'];
									$css       .= $this->generate_block_icon_css( $attrs, $unique_id );

								}
							}
						}
						// post grid
						if ( $block['blockName'] === 'enhanced-blocks/post-grid' ) {
							if ( isset( $block['attrs'] ) ) {
								$attrs = $block['attrs'];
								$this->post_grid_gfont( $attrs ); //Google font attrs
								if ( isset( $attrs['uniqueID'] ) ) {
									$unique_id = '#' . $attrs['uniqueID'];
									$css       .= $this->generate_post_grid_css( $attrs, $unique_id );
								}
							}
						}

						if ( $block['blockName'] === 'enhanced-blocks/icon-list' ) {
							if ( isset( $block['attrs'] ) ) {
								$attrs = $block['attrs'];
								$this->icon_list_gfont( $attrs ); //Google font attrs
								if ( isset( $attrs['uniqueID'] ) ) {
									$unique_id = '#' . $attrs['uniqueID'];
									$css       .= $this->generate_icon_list_css( $attrs, $unique_id );
								}
							}
						}

						if ( $block['blockName'] === 'enhanced-blocks/eb-notice' ) {
							if ( isset( $block['attrs'] ) ) {
								$attrs = $block['attrs'];
								$this->notice_block_gfont( $attrs ); //Google font attrs
							}
						}
						
						if( 'enhanced-blocks/enhanced-cta' === $block['blockName'] ){
							if ( isset( $block['attrs'] ) ) {
								$attrs = $block['attrs'];
								if( isset($attrs['uniqueID']) ){
									$unique_id = '#'.$attrs['uniqueID'];
									$css .= $this->generate_call_to_action_css($attrs, $unique_id);	
								}
							}
						}

						if( 'enhanced-blocks/button' === $block['blockName'] ){
							if ( isset( $block['attrs'] ) ) {
								$attrs = $block['attrs'];
								$this->button_gfont($attrs );
								if( isset($attrs['uniqueID']) ){
									$unique_id = '#'.$attrs['uniqueID'];
									$css .= $this->generate_button_css($attrs, $unique_id);
								}
							}
						}

						
					}
					
					if ( $block['blockName'] === 'enhanced-blocks/row-layout' ) {
						if( isset($block['attrs']) ){
							$attrs = $block['attrs'];
							if( isset($attrs['uniqueID']) ){
								$unique_id = '#rlfg'.$attrs['uniqueID'];
								$css .= $this->enhanced_blocks_generate_row_layout_css($attrs, $unique_id);
								
								if( isset($block['innerBlocks']) && !empty($block['innerBlocks']) ){
									$css .= $this->enhanced_blocks_get_inner_blocks($block['innerBlocks'], $unique_id);
									foreach ( $block['innerBlocks'] as $index => $inner_block ) {
										if( isset($inner_block['innerBlocks']) ) {
											foreach ( $inner_block['innerBlocks'] as $index => $block ) {
												if( is_array($block) ){
													if( isset($block['blockName']) ){
														if( 'enhanced-blocks/row-layout' === $block['blockName'] ){
															if( isset($block['attrs']) ){
																$attrs = $block['attrs'];
																if( isset($attrs['uniqueID']) ){
																	$unique_id = '#rlfg'.$attrs['uniqueID'];
																	$css .= $this->enhanced_blocks_generate_row_layout_css($attrs, $unique_id);
																	if( isset($block['innerBlocks']) && !empty($block['innerBlocks']) ){
																		$css .= $this->enhanced_blocks_get_inner_blocks($block['innerBlocks'], $unique_id);
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
				$css .= '</style>';
				echo $css;
			}
		}

	}

	public function enhanced_blocks_get_inner_blocks($inner_blocks, $unique_id){
		
		$css= '';
		foreach ( $inner_blocks as $index => $inner_block ) {
			if( is_array($inner_block) ){
			
				if( isset($inner_block['blockName']) ){
					if( 'enhanced-blocks/column' === $inner_block['blockName'] ){
						if( isset($inner_block['attrs']) ){
							$attrs = $inner_block['attrs'];
							$column_key = $index + 1;
							$c_unique_id = isset($attrs['uniqueID']) ? '.enhanced-blocks-column-'.$attrs['uniqueID'] : '';
							$css .= $this->enhanced_blocks_generate_column_layout_css($attrs, $unique_id, $column_key, $c_unique_id);
						}
					}
				}

				if( isset($inner_block['innerBlocks']) && !empty($inner_block['innerBlocks']) ){
					$css .= $this->enhanced_blocks_get_custom_blocks($inner_block['innerBlocks']);
				}
				
			}
			
		}
		return $css;
	}
	
	public function enhanced_blocks_get_custom_blocks( $inner_blocks ){
		$css= '';
		foreach ( $inner_blocks as $index => $inner_block ) {
			if( isset($inner_block['blockName']) && !empty($inner_block['blockName']) ){
				
				if ( 'enhanced-blocks/eb-testimonial' === $inner_block['blockName'] ) {
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						$this->enhanced_testimonial_gfont( $attrs ); //Google font attrs
						if ( isset( $attrs['uniqueID'] ) ) {
							$unique_id = '#' . $attrs['uniqueID']; // fontend css 
							$css .= $this->enhanced_testimonial_blocks_css( $attrs, $unique_id );
						}
					}
				}

				if ( 'enhanced-blocks/author-profile' === $inner_block['blockName'] ) {
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						$this->enhanced_profile_gfont( $attrs ); //Google font attrs
						if ( isset( $attrs['uniqueID'] ) ) {
							$unique_id = '#' . $attrs['uniqueID']; // fontend css 
							$css .= $this->enhanced_profile_blocks_css( $attrs, $unique_id );
						}
					}
				}

				if ( 'enhanced-blocks/enhanced-cta' === $inner_block['blockName'] ) {
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						$css .= $this->enhanced_cta_gfont( $attrs );
					}
				}

				if ( 'enhanced-blocks/eb-heading' === $inner_block['blockName'] ) {
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						$this->enhanced_heading_gfont( $attrs ); //Google font attrs
						if ( isset( $attrs['uniqueID'] ) ) {
							$unique_id = '#' . $attrs['uniqueID']; // fontend css 
							$css .= $this->enhanced_heading_blocks_css( $attrs, $unique_id );
						}
					}
				}
				
				if( 'enhanced-blocks/post-grid' === $inner_block['blockName'] ){
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						$this->post_grid_gfont( $attrs ); //Google font attrs
						if( isset($attrs['uniqueID']) ){
							$unique_id = '#'.$attrs['uniqueID'];
							$css .= $this->generate_post_grid_css($attrs, $unique_id);
						}
					}
				}

				if( 'enhanced-blocks/social-sharing' === $inner_block['blockName'] ){
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						if( isset($attrs['uniqueID']) ){
							$unique_id = '#'.$attrs['uniqueID'];
							$css .= $this->generate_social_share_css($attrs, $unique_id);
						}
					}
				}

				if( 'enhanced-blocks/icon-list' === $inner_block['blockName'] ){
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						$this->icon_list_gfont( $attrs ); //Google font attrs
						if( isset($attrs['uniqueID']) ){
							$unique_id = '#'.$attrs['uniqueID'];
							$css .= $this->generate_icon_list_css($attrs, $unique_id);
						}
					}
				}

				if( 'enhanced-blocks/enhanced-block-icon' === $inner_block['blockName'] ){
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						if( isset($attrs['uniqueID']) ){
							$unique_id = '#'.$attrs['uniqueID'];
							$css .= $this->generate_block_icon_css($attrs, $unique_id);	
						}
					}
				}


				if( 'enhanced-blocks/enhanced-before-and-after-image' === $inner_block['blockName'] ){
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						if( isset($attrs['uniqueID']) ){
							$unique_id = '#'.$attrs['uniqueID'];
							$css .= $this->generate_comparison_slider_css($attrs, $unique_id);	
						}
					}
				}


				if ( 'enhanced-blocks/eb-notice' === $inner_block['blockName'] ) {
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						$css .= $this->notice_block_gfont($attrs);
					}
				}
				
				if( 'enhanced-blocks/enhanced-cta' === $inner_block['blockName'] ){
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						if( isset($attrs['uniqueID']) ){
							$unique_id = '#'.$attrs['uniqueID'];
							$css .= $this->generate_call_to_action_css($attrs, $unique_id);	
						}
					}
				}

				if( 'enhanced-blocks/button' === $inner_block['blockName'] ){
					if ( isset( $inner_block['attrs'] ) ) {
						$attrs = $inner_block['attrs'];
						$this->button_gfont($attrs );
						if( isset($attrs['uniqueID']) ){
							$unique_id = '#'.$attrs['uniqueID'];
							$css .= $this->generate_button_css($attrs, $unique_id);
						}
					}
				}
				
			}
		}
		return $css;
	}
	
	public function enhanced_blocks_get_attributes() {
		$attributes = array(
			'desktop_attributes'  => array(
				'borderTypeNormal'              => 'border-style',
				'borderColorNormal'             => 'border-color',
				'borderWidthNormalTop'          => 'border-top-width',
				'borderWidthNormalRight'        => 'border-right-width',
				'borderWidthNormalBottom'       => 'border-bottom-width',
				'borderWidthNormalLeft'         => 'border-left-width',
				'borderRadiusNormalTop'         => 'border-top-left-radius',
				'borderRadiusNormalRight'       => 'border-top-right-radius',
				'borderRadiusNormalLeft'      => 'border-bottom-right-radius',
				'borderRadiusNormalBottom'        => 'border-bottom-left-radius',
				'borderTransitionDurationHover' => 'transition',
				'marginTopDesktop'              => 'margin-top',
				'marginRightDesktop'            => 'margin-right',
				'marginBottomDesktop'           => 'margin-bottom',
				'marginLeftDesktop'             => 'margin-left',
				'paddingTopDesktop'             => 'padding-top',
				'paddingRightDesktop'           => 'padding-right',
				'paddingBottomDesktop'          => 'padding-bottom',
				'paddingLeftDesktop'            => 'padding-left',
				'layoutZIndex' => 'z-index',
				'cWidth' => 'width',
				'cDasktopMinHeight' => 'min-height'
			),
			'desktop_bg_image_attributes'  => array(
				'bgColor'           => 'background-color',
				'bgImageUrl'        => 'background-image',
				'classicPosition'   => 'background-position',
				'classicAttachment' => 'background-attachment',
				'classicRepeat'     => 'background-repeat',
				'classicSize'       => 'background-size',
			),
			'bg_hover_image_attributes'  => array(
				'columnGradientTransitionHover' => 'transition',
				'columnBgColorHover'     => 'background',
				'columnBgImageUrlHover'  => 'background-image',
				'columnClassicPositionHover'   => 'background-position',
				'columnClassicAttachmentHover' => 'background-attachment',
				'columnClassicRepeatHover'     => 'background-repeat',
				'columnClassicSizeHover'       => 'background-size',
			),
			'desktop_bg_overlay_attributes'  => array(
				'backgroundOverlayNormalColor'   => 'background-color',
				'backgroundOverlayNormalOpacity' => 'opacity',
				'backgroundOverlayHoverTransitionDuration' => 'transition',
			),
			'desktop_bg_overlay_bg_img_attributes' => array(
				'backgroundOverlayNormalImage'      => 'background-image',
				'backgroundOverlayNormalPosition'   => 'background-position',
				'backgroundOverlayNormalAttachment' => 'background-attachment',
				'backgroundOverlayNormalRepeat'     => 'background-repeat',
				'backgroundOverlayNormalSize'       => 'background-size',
			),
			'desktop_bg_overlay_hover_attributes'  => array(
				'backgroundOverlayHoverColor'   => 'background-color',
				'backgroundOverlayHoverOpacity' => 'opacity',
			),
			'desktop_bg_overlay_hover_bg_img_attributes' => array(
				'backgroundOverlayHoverImage'  => 'background-image',
				'backgroundOverlayHoverBgImagePosition'   => 'background-position',
				'backgroundOverlayHoverAttachment' => 'background-attachment',
				'backgroundOverlayHoverRepeat'     => 'background-repeat',
				'backgroundOverlayHoverSize'       => 'background-size',
			),
			'box_shadow_attributes' => array(
				'shadowHorizontalNormal' => '',
				'shadowVerticalNormal' => '',
				'shadowBlurNormal' => '',
				'shadowSpreadNormal' => '',
				'boxShadowNormalColor' => '',
				'shadowPositionNormal' => '',
			),
			'box_shadow_hover_attributes' => array(
				'shadowHorizontalHover' => '',
				'shadowVerticalHover' => '',
				'shadowBlurHover' => '',
				'shadowSpreadHover' => '',
				'boxShadowHoverColor' => '',
				'shadowPositionHover' => '',
			),
			'bg_gradient_linear_attributes' => array(
				'gradientAngle'=> '',
				'gradientColor' => '',
				'gradientLocation' => '',
				'gradientSecondColor' => '',
				'gradientSecondLocation' => '',
			),
			'bg_gradient_radial_attributes' => array(
				'gradientPosition' => '',
				'gradientColor' => '',
				'gradientLocation' => '',
				'gradientSecondColor' => '',
				'gradientSecondLocation' => '',
			),
			
			'bg_hover_gradient_linear_attributes' => array(
				'columnGradientAngleHover' => '',
				'columnGradientColorHover' => '',
				'columnGradientLocationHover' => '',
				'columnGradientSecondColorHover' => '',
				'columnGradientSecondLocationHover' => '',
			),
			'bg_hover_gradient_radial_attributes' => array(
				'columnGradientPositionHover' => '',
				'columnGradientColorHover' => '',
				'columnGradientLocationHover' => '',
				'columnGradientSecondColorHover' => '',
				'columnGradientSecondLocationHover' => '',
			),
			
			'bg_overlay_gradient_linear_attributes' => array(
				'backgroundOverlayGradientAngle'=> '',
				'backgroundOverlayGradientColor' => '',
				'backgroundOverlayGradientLocation' => '',
				'backgroundOverlayGradientSecondColor' => '',
				'backgroundOverlayGradientSecondLocation' => '',
			),
			'bg_overlay_gradient_radial_attributes' => array(
				'backgroundOverlayGradientPosition'=> '',
				'backgroundOverlayGradientColor' => '',
				'backgroundOverlayGradientLocation' => '',
				'backgroundOverlayGradientSecondColor' => '',
				'backgroundOverlayGradientSecondLocation' => '',
			),

			'bg_overlay_hover_gradient_linear_attributes' => array(
				'backgroundOverlayHoverAngle'=> '',
				'backgroundOverlayHoverGradientColor' => '',
				'backgroundOverlayHoverLocation' => '',
				'backgroundOverlayHoverSecondColor' => '',
				'backgroundOverlayHoverSecondLocation' => '',
			),
			'bg_overlay_hover_gradient_radial_attributes' => array(
				'backgroundOverlayHoverGradientPosition'=> '',
				'backgroundOverlayHoverGradientColor' => '',
				'backgroundOverlayHoverLocation' => '',
				'backgroundOverlayHoverSecondColor' => '',
				'backgroundOverlayHoverSecondLocation' => '',
			),
			
			'shape_divider_top_attributes' => array(
				'shapeDividerTopWidth'=> 'width',
				'shapeDividerTopHeight'=> 'height',
			),
			'shape_divider_top_color_attributes' => array(
				'shapeDividerTopColor'=> 'fill',
			),
			'shape_divider_bottom_attributes' => array(
				'shapeDividerBottomWidth'=> 'width',
				'shapeDividerBottomHeight'=> 'height',
			),
			'shape_divider_bottom_color_attributes' => array(
				'shapeDividerBottomColor'=> 'fill',
			),
			'desktop_attributes_hover' => array(
				'borderTypeHover'         => 'border-style',
				'borderColorHover'        => 'border-color',
				'borderWidthHoverTop'     => 'border-top-width',
				'borderWidthHoverRight'   => 'border-right-width',
				'borderWidthHoverBottom'  => 'border-bottom-width',
				'borderWidthHoverLeft'    => 'border-left-width',
				'borderRadiusHoverTop'    => 'border-top-left-radius',
				'borderRadiusHoverRight'  => 'border-top-right-radius',
				'borderRadiusHoverBottom' => 'border-bottom-right-radius',
				'borderRadiusHoverLeft'   => 'border-bottom-left-radius',
			),
			'desktop_innerblocks_attributes'  => array(
				'minimumHeight'  => 'min-height',
				'columnPosition' => 'align-items',
			),
			'desktop_boxed_innerblocks_attributes'  => array(
				'layoutRange' => 'max-width',
			),
			'column_gap' => array(
				'columnsGap'   			=> 'padding',
				'columnWidthDesktop'	=> 'width',
				'layoutContentPosition' => 'align-items',
			),
			'tab_attributes'    => array(
				'marginTopTab'     => 'margin-top',
				'marginRightTab'   => 'margin-right',
				'marginBottomTab'  => 'margin-bottom',
				'marginLeftTab'    => 'margin-left',
				'paddingTopTab'    => 'padding-top',
				'paddingRightTab'  => 'padding-right',
				'paddingBottomTab' => 'padding-bottom',
				'paddingLeftTab'   => 'padding-left',
				'cTabWidth' 	   => 'width',
				'cTabMinHeight'    => 'min-height'
			),
			'mobile_attributes'   => array(
				'marginTopMobile'     => 'margin-top',
				'marginRightMobile'   => 'margin-right',
				'marginBottomMobile'  => 'margin-bottom',
				'marginLeftMobile'    => 'margin-left',
				'paddingTopMobile'    => 'padding-top',
				'paddingRightMobile'  => 'padding-right',
				'paddingBottomMobile' => 'padding-bottom',
				'paddingLeftMobile'   => 'padding-left',
				'cMobileWidth' 		  => 'width',
				'cMobileMinHeight' 	  => 'min-height'
			),

			// // Heading Block
			'heading_attributes' => array(
				'typography' => 'font-family',
				'fontWeight' => 'font-weight',
				'fontStyle' => 'font-style',
				'headingColor' => 'color',
				'headingAlignment' => 'text-align',
				'deskHeadingMarginT'	=>	'margin-top', 	
				'deskHeadingMarginR'	=>	'margin-right', 	
				'deskHeadingMarginB'	=>	'margin-bottom', 	
				'deskHeadingMarginL'	=>	'margin-left', 	
				'deskHeadingPaddingT'	=>	'padding-top', 	
				'deskHeadingPaddingR'	=>	'padding-right',
				'deskHeadingPaddingB'	=>	'padding-bottom',
				'deskHeadingPaddingL'	=>	'padding-left',  
				'deskHeadingFontSize' 	=> 'font-size',
				'deskHeadingLineHeight'	=> 'line-height',
				'deskHeadingLetterSpacing' => 'letter-spacing'
			),
			'heading_tab_attributes' => array( //Heading tab & Highlight tab
				'tabHeadingMarginT'		=>	'margin-top', 	
				'tabHeadingMarginR'		=>	'margin-right', 	
				'tabHeadingMarginB'		=>	'margin-bottom', 	
				'tabHeadingMarginL'		=>	'margin-left', 	
				'tabHeadingPaddingT'	=>	'padding-top', 	
				'tabHeadingPaddingR'	=>	'padding-right',
				'tabHeadingPaddingB'	=>	'padding-bottom',
				'tabHeadingPaddingL'	=>	'padding-left',  
				'tabHeadingFontSize' 	=> 'font-size',
				'tabHeadingLineHeight'	=> 'line-height',
				'tabHeadingLetterSpacing' => 'letter-spacing'
			),
			'heading_mob_attributes' => array( //Heading tab & Highlight tab
				'mobHeadingMarginT'		=>	'margin-top', 	
				'mobHeadingMarginR'		=>	'margin-right', 	
				'mobHeadingMarginB'		=>	'margin-bottom', 	
				'mobHeadingMarginL'		=>	'margin-left', 	
				'mobHeadingPaddingT'	=>	'padding-top', 	
				'mobHeadingPaddingR'	=>	'padding-right',
				'mobHeadingPaddingB'	=>	'padding-bottom',
				'mobHeadingPaddingL'	=>	'padding-left',  
				'mobHeadingFontSize' 	=> 'font-size',
				'mobHeadingLineHeight'	=> 'line-height',
				'mobHeadingLetterSpacing' => 'letter-spacing'
			),

			// Heading Highlight default attributes
			'highlight_attributes' => array(
				'highlightColor'   		=>	'color',
				'highlightBgColor'	    =>	'background',	
				'highlightBorder'		=>	'border-style' ,
				'highlightBorderColor'	=>	'border-color',  
				'highlightBorderWidthT'	=>	'border-top-width',
				'highlightBorderWidthR'	=>	'border-right-width',  
				'highlightBorderWidthB'	=>	'border-bottom-width',  
				'highlightBorderWidthL'	=>	'border-left-width',   
				'deskHighlightFontSize'	=>	'font-size', 	
				'deskHighlightLineHeight'=>	'line-height', 	
				'deskHighlightLetterSpacing'=>	'letter-spacing',
				'highlightTypography'	=>  'font-family',
				'highlightFontWeight' 	=> 	'font-weight',
				'highlightFontStyle' 	=>  'font-style',
				'deskHighlightMarginT'	=>	'margin-top', 	
				'deskHighlightMarginR'	=>	'margin-right', 	
				'deskHighlightMarginB'	=>	'margin-bottom', 	
				'deskHighlightMarginL'	=>	'margin-left', 	
				'deskHighlightPaddingT'	=>	'padding-top', 	
				'deskHighlightPaddingR'	=>	'padding-right',
				'deskHighlightPaddingB'=>	'padding-bottom',
				'deskHighlightPaddingL'	=>	'padding-left',  
				'deskHighlightLineHeight'	=> 'line-height',
				'deskHighlightLetterSpacing' => 'letter-spacing'

			),
			'highlight_tab_attributes' => array(
				'tabHighlightMarginT'		=>	'margin-top', 	
				'tabHighlightMarginR'		=>	'margin-right', 	
				'tabHighlightMarginB'		=>	'margin-bottom', 	
				'tabHighlightMarginL'		=>	'margin-left', 	
				'tabHighlightPaddingT'		=>	'padding-top', 	
				'tabHighlightPaddingR'		=>	'padding-right',
				'tabHighlightPaddingB'	    =>	'padding-bottom',
				'tabHighlightPaddingL'	    =>	'padding-left', 
				'tabHighlightFontSize'		=>	'font-size', 	
				'tabHighlightLineHeight'	=> 'line-height',
				'tabHighlightLetterSpacing' => 'letter-spacing'
			),
			'highlight_mob_attributes' => array(
				'mobHighlightMarginT'		=>	'margin-top', 	
				'mobHighlightMarginR'		=>	'margin-right', 	
				'mobHighlightMarginB'		=>	'margin-bottom', 	
				'mobHighlightMarginL'		=>	'margin-left', 	
				'mobHighlightPaddingT'		=>	'padding-top', 	
				'mobHighlightPaddingR'		=>	'padding-right',
				'mobHighlightPaddingB'		=>	'padding-bottom',
				'mobHighlightPaddingL'		=>	'padding-left',
				'mobHighlightFontSize'		=>	'font-size', 	
				'mobHighlightLineHeight'	=> 'line-height',
				'mobHighlightLetterSpacing' => 'letter-spacing'
			),


		// Testimonial Desktop Description
			'testimonial_attributes' => array(
				'deskDesFontSize'		=>	'font-size', 	
				'deskDesLineHeight'		=> 'line-height',
				'deskDesLetterSpacing'  => 'letter-spacing',
				'testimonialAlignment'  =>	'text-align',
				'testimonialTextColor'  => 'color',
				'typography'			=> 'font-family',
				'fontWeight' 			=> 'font-weight',
				'fontStyle'				=> 'font-style',
				'desTransform'			=> 'text-transform',
				'deskDesMarginT'		=>	'margin-top', 
				'deskDesMarginR'		=>	'margin-right', 	
				'deskDesMarginL'		=>	'margin-left', 	
				'deskDesMarginB'		=>	'margin-bottom', 
				'deskDesPaddingT'		=>	'padding-top', 
				'deskDesPaddingR'		=>	'padding-right', 	
				'deskDesPaddingL'		=>	'padding-left', 	
				'deskDesPaddingB'		=>	'padding-bottom', 
			),
			'testimonial_des_tab_attributes' => array(
				'tabDesFontSize'		=>	'font-size', 	
				'tabDesLineHeight'		=> 'line-height',
				'tabDesLetterSpacing' 	=> 'letter-spacing',
				'tabDesMarginT'			=>	'margin-top', 
				'tabDesMarginR'			=>	'margin-right', 	
				'tabDesMarginL'			=>	'margin-left', 	
				'tabDesMarginB'			=>	'margin-bottom', 
				'tabDesPaddingT'		=>	'padding-top', 
				'tabDesPaddingR'		=>	'padding-right', 	
				'tabDesPaddingL'		=>	'padding-left', 	
				'tabDesPaddingB'		=>	'padding-bottom', 
			),
			'testimonial_des_mob_attributes' => array(
				'mobDesFontSize'		=>	'font-size', 	
				'mobDesLineHeight'		=> 'line-height',
				'mobDesLetterSpacing'	=> 'letter-spacing',
				'mobDesMarginT'			=>	'margin-top', 
				'mobDesMarginR'			=>	'margin-right', 	
				'mobDesMarginL'			=>	'margin-left', 	
				'mobDesMarginB'			=>	'margin-bottom', 
				'mobDesPaddingT'		=>	'padding-top', 
				'mobDesPaddingR'		=>	'padding-right', 	
				'mobDesPaddingL'		=>	'padding-left', 	
				'mobDesPaddingB'		=>	'padding-bottom', 
			),

			// Testimonial Desktop Name
			'testimonial_name_attributes' => array(
				'deskNameFontSize'		=>	'font-size', 	
				'deskNameLineHeight'	=> 'line-height',
				'deskNameLetterSpacing' => 'letter-spacing',
				'tesNameColor'  		=> 'color',
				'tesNameTypography'		=> 'font-family',
				'tesNameFontWeight' 	=> 'font-weight',
				'tesNameFontStyle'		=> 'font-style',
				'tesNameTransform'		=> 'text-transform',
				'deskNameMarginT'		=>	'margin-top', 
				'deskNameMarginR'		=>	'margin-right', 	
				'deskNameMarginL'		=>	'margin-left', 	
				'deskNameMarginB'		=>	'margin-bottom', 
				'deskNamePaddingT'		=>	'padding-top', 
				'deskNamePaddingR'		=>	'padding-right', 	
				'deskNamePaddingL'		=>	'padding-left', 	
				'deskNamePaddingB'		=>	'padding-bottom', 
			),
			//Testimonial name tab
			'testimonial_name_tab_attributes' => array(
				'tabNameFontSize'		=>	'font-size', 	
				'tabNameLineHeight'		=> 'line-height',
				'tabNameMarginT'		=>	'margin-top', 
				'tabNameMarginR'		=>	'margin-right', 	
				'tabNameMarginL'		=>	'margin-left', 	
				'tabNameMarginB'		=>	'margin-bottom', 
				'tabNamePaddingT'		=>	'padding-top', 
				'tabNamePaddingR'		=>	'padding-right', 	
				'tabNamePaddingL'		=>	'padding-left', 	
				'tabNamePaddingB'		=>	'padding-bottom', 
			),
			// Testimonial name mob
			'testimonial_name_mob_attributes' => array(
				'mobNameFontSize'		=>	'font-size', 	
				'mobNameLineHeight'		=> 'line-height',
				'mobNameLetterSpacing' 	=> 'letter-spacing',
				'mobNameMarginT'		=>	'margin-top', 
				'mobNameMarginR'		=>	'margin-right', 	
				'mobNameMarginL'		=>	'margin-left', 	
				'mobNameMarginB'		=>	'margin-bottom', 
				'mobNamePaddingT'		=>	'padding-top', 
				'mobNamePaddingR'		=>	'padding-right', 	
				'mobNamePaddingL'		=>	'padding-left', 	
				'mobNamePaddingB'		=>	'padding-bottom', 
			),

		// Testimonial Desktop Title
			'testimonial_title_attributes' => array(
				'deskTitleFontSize'		=>	'font-size', 	
				'tesTitleColor' 		=> 'color',
				'tesTitleTypography'	=> 'font-family',
				'tesTitleFontWeight' 	=> 'font-weight',
				'tesTitleFontStyle'		=> 'font-style',
				'tesTitleTransform'		=> 'text-transform',
				'deskTitleLineHeight'	=> 'line-height',
				'deskTitleLetterSpacing'=> 'letter-spacing',
				'deskTitleMarginT'		=>	'margin-top', 
				'deskTitleMarginR'		=>	'margin-right', 	
				'deskTitleMarginL'		=>	'margin-left', 	
				'deskTitleMarginB'		=>	'margin-bottom', 
				'deskTitlePaddingT'		=>	'padding-top', 
				'deskTitlePaddingR'		=>	'padding-right', 	
				'deskTitlePaddingL'		=>	'padding-left', 	
				'deskTitlePaddingB'		=>	'padding-bottom', 
			),

			'testimonial_title_tab_attributes' => array(
				'tabTitleFontSize'		=>	'font-size', 	
				'tabTitleLineHeight'	=> 'line-height',
				'tabTitleLetterSpacing' => 'letter-spacing',
				'tabTitleMarginT'		=>	'margin-top', 
				'tabTitleMarginR'		=>	'margin-right', 	
				'tabTitleMarginL'		=>	'margin-left', 	
				'tabTitleMarginB'		=>	'margin-bottom', 
				'tabTitlePaddingT'		=>	'padding-top', 
				'tabTitlePaddingR'		=>	'padding-right', 	
				'tabTitlePaddingL'		=>	'padding-left', 	
				'tabTitlePaddingB'		=>	'padding-bottom', 
			),

			'testimonial_title_mob_attributes' => array(
				'mobTitleFontSize'		=>	'font-size', 	
				'mobTitleLineHeight'	=> 'line-height',
				'mobTitleLetterSpacing' => 'letter-spacing',
				'mobTitleMarginT'		=>	'margin-top', 
				'mobTitleMarginR'		=>	'margin-right', 	
				'mobTitleMarginL'		=>	'margin-left', 	
				'mobTitleMarginB'		=>	'margin-bottom', 
				'mobTitlePaddingT'		=>	'padding-top', 
				'mobTitlePaddingR'		=>	'padding-right', 	
				'mobTitlePaddingL'		=>	'padding-left', 	
				'mobTitlePaddingB'		=>	'padding-bottom', 
			),

			'testimonial_quote_attributes' => array(
				'deskQuoteFontSize'	=>	'font-size', 	
				'deskQuoteSpacing'  => 	'margin-top',
				'quoteIconColor'    => 'color'
			),
			'testimonial_quote_tab_attributes' => array(
				'tabQuoteFontSize'	=>	'font-size', 	
				'tabQuoteSpacing'   => 	'margin-top',
			),
			'testimonial_quote_mob_attributes' => array(
				'mobQuoteFontSize'	=>	'font-size', 	
				'mobQuoteSpacing'   => 	'margin-top',
			),

			'testimonial_background_attributes' => array(
				'bgColor'           => 'background-color',
				'bgImageUrl'        => 'background-image',
				'classicPosition'   => 'background-position',
				'classicAttachment' => 'background-attachment',
				'classicRepeat'     => 'background-repeat',
				'classicSize'       => 'background-size',
			),
			
			'testimonial_bg_linear_gradient_attributes' => array(
				'gradientAngle'=> '',
				'gradientColor' => '',
				'gradientLocation' => '',
				'gradientSecondColor' => '',
				'gradientSecondLocation' => '',
			),

			'testimonial_bg_radial_gradient_attributes' => array(
				'gradientPosition' => '',
				'gradientColor' => '',
				'gradientLocation' => '',
				'gradientSecondColor' => '',
				'gradientSecondLocation' => '',
			),

		// Profile Desktop Title
			'profile_attributes' => array(
				'titleColor'  			=>  'color',
				'typography'			=>  'font-family',
				'fontWeight' 			=>  'font-weight',
				'fontStyle'				=>  'font-style',
				'deskTitleFontSize'		=>	'font-size', 	
				'deskTitleLineHeight'	=>  'line-height',
				'deskTitlePaddingT'		=>	'padding-top', 
				'deskTitlePaddingR'		=>	'padding-right', 	
				'deskTitlePaddingL'		=>	'padding-left', 	
				'deskTitlePaddingB'		=>	'padding-bottom', 
			), 
			'profile_title_tab_attributes' => array(
				'tabTitleFontSize'	=>	'font-size', 	
				'tabTitleLineHeight'=>  'line-height',
				'tabTitlePaddingT'	=>	'padding-top', 
				'tabTitlePaddingR'	=>	'padding-right', 	
				'tabTitlePaddingL'	=>	'padding-left', 	
				'tabTitlePaddingB'	=>	'padding-bottom', 
			), 
			'profile_title_mob_attributes' => array(
				'mobTitleFontSize'	=>	'font-size', 	
				'mobTitleLineHeight'=>  'line-height',
				'mobTitlePaddingT'	=>	'padding-top', 
				'mobTitlePaddingR'	=>	'padding-right', 	
				'mobTitlePaddingL'	=>	'padding-left', 	
				'mobTitlePaddingB'	=>	'padding-bottom', 
			), 

			// Designation
			'profile_designation_attributes' => array(
				'designationColor'  			=>  'color',
				'designationTypography'			=>  'font-family',
				'designationFontWeight' 		=>  'font-weight',
				'designationFontStyle'			=>  'font-style',
				'deskDesignationFontSize'		=>	'font-size', 	
				'deskDesignationLineHeight'		=>  'line-height',
				'deskDesignationPaddingT'		=>	'padding-top', 
				'deskDesignationPaddingR'		=>	'padding-right', 	
				'deskDesignationPaddingL'		=>	'padding-left', 	
				'deskDesignationPaddingB'		=>	'padding-bottom', 
			), 
			'profile_designation_tab_attributes' => array(
				'tabDesignationFontSize'	=>	'font-size', 	
				'tabDesignationLineHeight'	=>  'line-height',
				'tabDesignationPaddingT'	=>	'padding-top', 
				'tabDesignationPaddingR'	=>	'padding-right', 	
				'tabDesignationPaddingL'	=>	'padding-left', 	
				'tabDesignationPaddingB'	=>	'padding-bottom', 
			), 
			'profile_designation_mob_attributes' => array(
				'mobDesignationFontSize'	=>	'font-size', 	
				'mobDesignationLineHeight'	=>  'line-height',
				'mobDesignationPaddingT'	=>	'padding-top', 
				'mobDesignationPaddingR'	=>	'padding-right', 	
				'mobDesignationPaddingL'	=>	'padding-left', 	
				'mobDesignationPaddingB'	=>	'padding-bottom', 
			), 

			// Description
			'profile_description_attributes' => array(
				'descriptionColor'  			=>  'color',
				'descriptionTypography'			=>  'font-family',
				'descriptionFontWeight' 		=>  'font-weight',
				'descriptionFontStyle'			=>  'font-style',
				'deskDescriptionFontSize'		=>	'font-size', 	
				'deskDescriptionLineHeight'		=>  'line-height',
				'deskDescriptionPaddingT'		=>	'padding-top', 
				'deskDescriptionPaddingR'		=>	'padding-right', 	
				'deskDescriptionPaddingL'		=>	'padding-left', 	
				'deskDescriptionPaddingB'		=>	'padding-bottom', 
			), 
			'profile_description_tab_attributes' => array(
				'tabDescriptionFontSize'	=>	'font-size', 	
				'tabDescriptionLineHeight'	=>  'line-height',
				'tabDescriptionPaddingT'	=>	'padding-top', 
				'tabDescriptionPaddingR'	=>	'padding-right', 	
				'tabDescriptionPaddingL'	=>	'padding-left', 	
				'tabDescriptionPaddingB'	=>	'padding-bottom', 
			), 
			'profile_description_mob_attributes' => array(
				'mobDescriptionFontSize'	=>	'font-size', 	
				'mobDescriptionLineHeight'	=>  'line-height',
				'mobDescriptionPaddingT'	=>	'padding-top', 
				'mobDescriptionPaddingR'	=>	'padding-right', 	
				'mobDescriptionPaddingL'	=>	'padding-left', 	
				'mobDescriptionPaddingB'	=>	'padding-bottom', 
			), 
			'profile_background_attributes' => array(
				'bgColor'           => 'background-color',
				'bgImageUrl'        => 'background-image',
				'classicPosition'   => 'background-position',
				'classicAttachment' => 'background-attachment',
				'classicRepeat'     => 'background-repeat',
				'classicSize'       => 'background-size',
			),
			'profile_bg_linear_gradient_attributes' => array(
				'gradientAngle'=> '',
				'gradientColor' => '',
				'gradientLocation' => '',
				'gradientSecondColor' => '',
				'gradientSecondLocation' => '',
			),

			'profile_bg_radial_gradient_attributes' => array(
				'gradientPosition' => '',
				'gradientColor' => '',
				'gradientLocation' => '',
				'gradientSecondColor' => '',
				'gradientSecondLocation' => '',
			),
			'profile_background_overlay_attributes' => array(
				'backgroundOverlayNormalOpacity'      => 'opacity',
			),
			'profile_background_overlay_bg_color_attributes' => array(
				'backgroundOverlayNormalColor'      => 'background-color',
			),
			'profile_background_overlay_bg_img_attributes' => array(
				'backgroundOverlayNormalImage'      => 'background-image',
				'backgroundOverlayNormalPosition'   => 'background-position',
				'backgroundOverlayNormalAttachment' => 'background-attachment',
				'backgroundOverlayNormalRepeat'     => 'background-repeat',
				'backgroundOverlayNormalSize'       => 'background-size',
			),
			'profile_bg_overlay_gradient_linear_attributes' => array(
				'backgroundOverlayGradientAngle'=> '',
				'backgroundOverlayGradientColor' => '',
				'backgroundOverlayGradientLocation' => '',
				'backgroundOverlayGradientSecondColor' => '',
				'backgroundOverlayGradientSecondLocation' => '',
			),
			'profile_bg_overlay_gradient_radial_attributes' => array(
				'backgroundOverlayGradientPosition'=> '',
				'backgroundOverlayGradientColor' => '',
				'backgroundOverlayGradientLocation' => '',
				'backgroundOverlayGradientSecondColor' => '',
				'backgroundOverlayGradientSecondLocation' => '',
			),


	);
		return $attributes;
	}

	public function enhanced_blocks_get_css_value($array, $key, $attr_key, $default = '') {
		if( $attr_key === 'box_shadow_attributes' || $attr_key === 'box_shadow_hover_attributes' ){
			if( $key === 'shadowHorizontalNormal' || $key === 'shadowVerticalNormal' || $key === 'shadowBlurNormal' || $key === 'shadowSpreadNormal' || $key === 'shadowHorizontalHover' || $key === 'shadowVerticalHover' || $key === 'shadowBlurHover' || $key === 'shadowSpreadHover') {
				return isset($array[$key]) && is_numeric($array[$key]) ? $array[$key] .'px ' : '0px ';
			} elseif ( isset($array[$key]) && ($key === 'boxShadowNormalColor' || $key === 'boxShadowHoverColor' ) ){
				return $this->getColor($array[ $key ]);
			} elseif ( isset($array[$key]) && ($key === 'shadowPositionNormal' || $key === 'shadowPositionHover' ) ){
				return ' '.$array[$key];
			} else {
				return false;
			}
		}

		if( $attr_key === 'bg_gradient_linear_attributes' || $attr_key === 'bg_gradient_radial_attributes' || $attr_key === 'bg_overlay_gradient_linear_attributes' || $attr_key === 'bg_overlay_gradient_radial_attributes' || $attr_key === 'bg_hover_gradient_radial_attributes' || $attr_key === 'bg_hover_gradient_linear_attributes' || $attr_key === 'bg_overlay_hover_gradient_linear_attributes' || $attr_key === 'bg_overlay_hover_gradient_radial_attributes' || $attr_key === 'testimonial_bg_linear_gradient_attributes' || $attr_key === 'testimonial_bg_radial_gradient_attributes' || $attr_key === 'profile_bg_linear_gradient_attributes' || $attr_key === 'profile_bg_radial_gradient_attributes' || $attr_key === 'profile_bg_overlay_gradient_linear_attributes' || $attr_key === 'profile_bg_overlay_gradient_radial_attributes' ) {
			
			if ( $key === 'gradientLocation' || $key === 'backgroundOverlayGradientLocation' || $key === 'columnGradientLocationHover' || $key === 'backgroundOverlayGradientLocation' || $key === 'backgroundOverlayHoverLocation') {
				return isset( $array[ $key ] ) ? $array[ $key ] . '%, ' : '1%, ';
			} elseif ( $key === 'gradientSecondLocation' || $key === 'backgroundOverlayGradientSecondLocation' || $key === 'columnGradientSecondLocationHover' || $key === 'backgroundOverlayHoverSecondLocation') {
				return isset( $array[ $key ] ) ? $array[ $key ] . '%' : '100%';
			} elseif ( $key === 'gradientAngle' || $key === 'backgroundOverlayGradientAngle' || $key === 'columnGradientAngleHover' || $key === 'backgroundOverlayGradientAngle' || $key === 'backgroundOverlayHoverAngle') {
				return isset( $array[ $key ] ) ? $array[ $key ] . 'deg, ' : '180deg, ';
			} elseif ( $key === 'gradientPosition' || $key === 'backgroundOverlayGradientPosition' || $key === 'columnGradientPositionHover' || $key === 'backgroundOverlayGradientPosition' || $key === 'backgroundOverlayHoverGradientPosition') {
				return isset( $array[ $key ] ) ? $array[ $key ] . ', ' : 'center center, ';
			} elseif ( $key === 'gradientColor' || $key === 'columnGradientColorHover' || $key === 'backgroundOverlayGradientColor' || $key === 'backgroundOverlayHoverGradientColor') {
				return isset( $array[ $key ] ) ? $this->getColor($array[ $key ]) . ' ' : '#4c10cd ';
			} else {
				return isset( $array[ $key ] ) ? $this->getColor($array[ $key ]) . ' ' : '#4c10cd ';
			}
		}
		return $default;
	}

	public function enhanced_blocks_validate_css_selector($attrs, $attr_value){
		foreach( $attr_value as $key => $val ){
			if( ($key === 'bgImageUrl' || $key === 'backgroundOverlayNormalImage' || $key === 'columnBgImageUrlHover' || $key === 'backgroundOverlayHoverImage') && empty($attrs[$key]) ) {
				return false;
			}elseif( isset($attrs[$key]) && !empty($attrs[$key]) ){
				return $attrs[$key];
			}
		}
	}

	public function enhanced_blocks_get_css_property_value($attr_value, $attr_key, $attrs, $unique_id, $column_key = '', $c_unique_id = '') {
		if( is_array($attr_value) ){
			if( $this->enhanced_blocks_validate_css_selector($attrs, $attr_value) ){
				$css = '';
				if( $column_key === '' ){
  				    $css .= ( $attr_key === 'column_gap' ) ?  $unique_id . ' .inner-blocks-wrap .enhanced-blocks-column .enhanced-blocks-column-inner{' : '';
					$css .= ( $attr_key === 'desktop_attributes' ) ?  $unique_id . '{' : '';
					$css .= ( $attr_key === 'tab_attributes' ) ? '@media only screen and (max-width: 1024px) and (min-width: 768px) {' . $unique_id . '{' : '';
					$css .= ( $attr_key === 'mobile_attributes' ) ? '@media (max-width: 767px) {' . $unique_id . '{' : '';
					$css .= ( $attr_key === 'desktop_bg_image_attributes' ) ?  $unique_id . '.section-bg-image{' : '';
					$css .= ( $attr_key === 'desktop_innerblocks_attributes' ) ?  $unique_id .' > .inner-blocks-wrap{' : '';
					$css .= ( $attr_key === 'desktop_boxed_innerblocks_attributes' ) ?  $unique_id .'.enhanced-blocks-section-boxed > .inner-blocks-wrap{' : '';
					$css .= ( $attr_key === 'desktop_attributes_hover' ) ?  $unique_id . ':hover{' : '';
					$css .= ( $attr_key === 'desktop_bg_overlay_attributes' ) ?  $unique_id . '> .enhanced-blocks-background-overlay{' : '';
					$css .= ( $attr_key === 'desktop_bg_overlay_bg_img_attributes' ) ?  $unique_id . '> .enhanced-blocks-background-overlay.enhanced-blocks-overlay-bg-image{' : '';
					$css .= ( $attr_key === 'shape_divider_top_attributes' ) ?  $unique_id . '> .enhanced-blocks-row-layout-shape-top svg{' : '';
					$css .= ( $attr_key === 'shape_divider_bottom_attributes' ) ?  $unique_id . '> .enhanced-blocks-row-layout-shape-bottom svg{' : '';
					$css .= ( $attr_key === 'shape_divider_top_color_attributes' ) ?  $unique_id . '> .enhanced-blocks-row-layout-shape-top svg path{' : '';
					$css .= ( $attr_key === 'shape_divider_bottom_color_attributes' ) ?  $unique_id . '> .enhanced-blocks-row-layout-shape-bottom svg path{' : '';
				}
				if( $column_key !== '' && $c_unique_id !== ''){
					$css .= ( $attr_key === 'column_gap' ) ?  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.'{' : '';
					$css .= ( $attr_key === 'desktop_attributes' ) ?  $unique_id . '> .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.'{' : '';
					$css .= ( $attr_key === 'desktop_attributes_hover' ) ?  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.':hover{' : '';
					$css .= ( $attr_key === 'desktop_bg_image_attributes' ) ?  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.column-bg-image.enhanced-blocks-inner-column-'.$column_key.'{' : '';
					$css .= ( $attr_key === 'bg_hover_image_attributes' ) ?  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-column-hover-bg-image.enhanced-blocks-inner-column-'.$column_key.':hover{' : '';
					$css .= ( $attr_key === 'desktop_bg_overlay_bg_img_attributes' ) ?  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.'> .enhanced-blocks-column-overlay-bg-image{' : '';

					$css .= ( $attr_key === 'desktop_bg_overlay_hover_bg_img_attributes' ) ?  $unique_id . '> .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.':hover> .enhanced-blocks-column-overlay-hover-bg-image.enhanced-blocks-column-background-overlay{' : '';

					$css .= ( $attr_key === 'desktop_bg_overlay_attributes' ) ?  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.'> .enhanced-blocks-column-background-overlay{' : '';

					$css .= ( $attr_key === 'desktop_bg_overlay_hover_attributes' ) ?  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.':hover> .enhanced-blocks-column-background-overlay{' : '';
					
					
					$css .= ( $attr_key === 'mobile_attributes' ) ? '@media (max-width: 767px) { ' . $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.'{' : '';
					$css .= ( $attr_key === 'tab_attributes' ) ? '@media only screen and (max-width: 1024px) and (min-width: 768px) { ' . $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.'{' : '';
				}
			// Heading block css
				$css .= ( $attr_key === 'heading_attributes' ) ? $unique_id.'{' : ''; 
				$css .= ( $attr_key === 'heading_tab_attributes' ) ? '@media only screen and (max-width: 1024px) and (min-width: 768px) { '.$unique_id .'{' : '';
				$css .= ( $attr_key === 'heading_mob_attributes' ) ? '@media (max-width: 767px) { ' . $unique_id .'{' : '';
				// Heading highlight css
				$css .= ( $attr_key === 'highlight_attributes' ) ? $unique_id.' mark {' : '';
				$css .= ( $attr_key === 'highlight_tab_attributes' ) ? '@media only screen and (max-width: 1024px) and (min-width: 768px) { '.$unique_id .' mark {' : '';
				$css .= ( $attr_key === 'highlight_mob_attributes' ) ? '@media (max-width: 767px) { ' . $unique_id .' mark {' : '';


			// Testimonial Description block css
				$css .= ( $attr_key === 'testimonial_attributes' ) ? $unique_id.' .en-testimonial .testimonial-content p{' : ''; 
				$css .= ( $attr_key === 'testimonial_des_tab_attributes' ) ? '@media only screen and (max-width: 1024px) and (min-width: 768px) { '.$unique_id .' .en-testimonial .testimonial-content p {' : '';
				$css .= ( $attr_key === 'testimonial_des_mob_attributes' ) ? '@media (max-width: 767px) { ' . $unique_id .' .en-testimonial .testimonial-content p {' : '';
			
			// Testimonial name block css
				$css .= ( $attr_key === 'testimonial_name_attributes' ) ? $unique_id.' .testimonial-details .name{' : '';  
				$css .= ( $attr_key === 'testimonial_name_tab_attributes' ) ? '@media only screen and (max-width: 1024px) and (min-width: 768px) { '.$unique_id .' .testimonial-details .name {' : '';
				$css .= ( $attr_key === 'testimonial_name_mob_attributes' ) ? '@media (max-width: 767px) { ' . $unique_id .' .testimonial-details .name {' : '';
			// Testimonial title block css
				$css .= ( $attr_key === 'testimonial_title_attributes' ) ? $unique_id.' .testimonial-details .title{' : ''; 
				$css .= ( $attr_key === 'testimonial_title_tab_attributes' ) ? '@media only screen and (max-width: 1024px) and (min-width: 768px) { '.$unique_id .' .testimonial-details .title {' : '';
				$css .= ( $attr_key === 'testimonial_title_mob_attributes' ) ? '@media (max-width: 767px) { ' . $unique_id .' .testimonial-details .title {' : '';
			// Testimonial Quote Icon css
				$css .= ( $attr_key === 'testimonial_quote_attributes' ) ? $unique_id.' .en-testimonial.en-quote-align-left:before, '.$unique_id .' .en-testimonial.en-quote-align-right:before, '.$unique_id .' .en-testimonial.en-quote-align-center:before{ ' : ''; 
				$css .= ( $attr_key === 'testimonial_quote_tab_attributes' ) ? '@media only screen and (max-width: 1024px) and (min-width: 768px) { '.$unique_id .' .en-testimonial.en-quote-align-left:before, '.$unique_id .' .en-testimonial.en-quote-align-right:before, '.$unique_id .' .en-testimonial.en-quote-align-center:before {' : '';
				$css .= ( $attr_key === 'testimonial_quote_mob_attributes' ) ? '@media (max-width: 767px) { ' . $unique_id .' .en-testimonial.en-quote-align-left:before,  '.$unique_id .' .en-testimonial.en-quote-align-right:before, '.$unique_id .' .en-testimonial.en-quote-align-center:before{' : '';
			// testimonail Background
				$css .= ( $attr_key === 'testimonial_background_attributes' ) ? $unique_id.'{ ' : ''; 
			// Profile title block css
				$css .= ( $attr_key === 'profile_attributes' ) ? $unique_id.'.en-author-profile .author_information .title{' : ''; 
				$css .= ( $attr_key === 'profile_title_tab_attributes' ) ? '@media only screen and (max-width: 1024px) and (min-width: 768px) { '.$unique_id .'.en-author-profile .author_information .title {' : '';
				$css .= ( $attr_key === 'profile_title_mob_attributes' ) ? '@media (max-width: 767px) { ' . $unique_id .'.en-author-profile .author_information .title {' : '';
			// Profile designation block css
				$css .= ( $attr_key === 'profile_designation_attributes' ) ? $unique_id.'.en-author-profile .author_information .designation{' : '';  
				$css .= ( $attr_key === 'profile_designation_tab_attributes' ) ? '@media only screen and (max-width: 1024px) and (min-width: 768px) { '.$unique_id .'.en-author-profile .author_information .designation {' : '';
				$css .= ( $attr_key === 'profile_designation_mob_attributes' ) ? '@media (max-width: 767px) { ' . $unique_id .'.en-author-profile .author_information .designation {' : '';
			// Profile description block css
				$css .= ( $attr_key === 'profile_description_attributes' ) ? $unique_id.'.en-author-profile .author_information .desc{' : ''; 
				$css .= ( $attr_key === 'profile_description_tab_attributes' ) ? '@media only screen and (max-width: 1024px) and (min-width: 768px) { '.$unique_id .'.en-author-profile .author_information .desc {' : '';
				$css .= ( $attr_key === 'profile_description_mob_attributes' ) ? '@media (max-width: 767px) { ' . $unique_id .'.en-author-profile .author_information .desc {' : '';
			// Profile Background
				$css .= ( $attr_key === 'profile_background_attributes' ) ? $unique_id.'{ ' : '';
				$css .= ( $attr_key  === 'profile_background_overlay_attributes' ) ? $unique_id.' .en-author-profile-overlay{ ' : ''; 
				$css .= ( $attr_key  === 'profile_background_overlay_bg_color_attributes' ) ? $unique_id.' .en-author-profile-overlay.overlay-bg-color{ ' : ''; 
				$css .= ( $attr_key === 'profile_background_overlay_bg_img_attributes' ) ? $unique_id.' .en-author-profile-overlay.overlay-bg-img{ ' : ''; 



				foreach ($attr_value as $key => $value){
					if( isset($attrs[$key]) && is_numeric($attrs[$key])) {
						$css_value = $attrs[$key] .'px;';
					} elseif ( isset($attrs[$key]) && is_string($attrs[$key]) ){
						$css_value = $attrs[$key] .';';
					} else {
						$css_value = '';
					}
					
					
					if( $value === 'color' || $value === 'background' || $value === 'background-color' || $value === 'border-color' || $value === 'fill' ){
						$css_value = isset($attrs[$key]) ? $this->getColor($attrs[$key]) . ';' : '';
					}

					if( $value === 'font-weight'){
						$css_value = isset($attrs[$key]) ? $attrs[$key].';' : '';
					}
					
					if( $value === 'font-family' && isset($attrs['googleFont']) ){
						$css_value = (isset($attrs[$key]) && $attrs['googleFont'] ) ? "'$attrs[$key]', sans-serif;" : '';
					} else if( $value === 'font-family'){
						$css_value = isset($attrs[$key]) ? "'$attrs[$key]';" : '';
					}
					
					if ( $key === 'layoutZIndex'){
						$css_value = isset($attrs[$key]) ? $attrs[$key].';' : '';
					}
					
					if ( $key === 'bgImageUrl'|| $key === 'backgroundOverlayNormalImage' || $key === 'columnBgImageUrlHover' || $key === 'backgroundOverlayHoverImage'){
						$css_value = isset($attrs[$key]) && !empty($attrs[$key]) ? 'url('.$attrs[$key].');' : '';
					}
					

					if ( $key === 'backgroundOverlayNormalOpacity' || $key === 'backgroundOverlayHoverOpacity' ){
						$css_value = isset($attrs[$key]) ? $attrs[$key].';' : '';
					}

					if ( $key === 'borderTransitionDurationHover' ){
						$css_value = isset($attrs[$key]) ? 'border '.$attrs[$key].'s,border-radius '.$attrs[$key].'s,box-shadow '.$attrs[$key].'s;' : '';
					}
					
					if ( $key === 'shapeDividerTopWidth' || $key === 'shapeDividerBottomWidth' ){
						$css_value = isset($attrs[$key]) ? 'calc('.$attrs[$key].'% + 1.3px);' : '';
					}

					if ( $key === 'columnGradientTransitionHover' || $key === 'backgroundOverlayHoverTransitionDuration' ){
						$css_value = isset($attrs[$key]) ? 'background '.$attrs[$key].'s, opacity '.$attrs[$key].'s;': '';
					}
					
					if ( $key === 'cWidth' || $key === 'cTabWidth' || $key === 'cMobileWidth' ) {
						$css_value = isset($attrs[$key]) ? $attrs[$key].'%;' : '';
					}
					
					$css_property = '';
					
					if($value === 'opacity' && $css_value === 0){
						$css_property .=  $value .':';
					}
			
					$css_property .= !empty($css_value) ? $value .':' : '';
					
					$css .= $css_property.''.$css_value;
					
				}
				$css .= ($attr_key === 'tab_attributes' || $attr_key === 'mobile_attributes' || 
					$attr_key === 'heading_tab_attributes' || $attr_key === 'heading_mob_attributes' || 
					$attr_key === 'highlight_tab_attributes' || $attr_key === 'highlight_mob_attributes' ||  
					$attr_key === 'testimonial_des_tab_attributes' ||  $attr_key === 'testimonial_des_mob_attributes' ||  
					$attr_key === 'testimonial_name_tab_attributes' || $attr_key === 'testimonial_name_mob_attributes' || 
					$attr_key === 'testimonial_title_tab_attributes' || $attr_key === 'testimonial_title_mob_attributes'  ||
					$attr_key === 'testimonial_quote_tab_attributes' || $attr_key === 'testimonial_quote_mob_attributes' ||

					$attr_key === 'profile_title_tab_attributes' || $attr_key === 'profile_title_mob_attributes' ||
					$attr_key === 'profile_designation_tab_attributes' || $attr_key === 'profile_designation_mob_attributes' ||
					$attr_key === 'profile_description_tab_attributes' || $attr_key === 'profile_description_mob_attributes' 

				) ? '}}' : '}';
				return $css;
			}
		}

	}

	public function enhanced_blocks_get_multiple_css_property_value($attr_value, $attr_key, $attrs, $unique_id, $column_key = '', $c_unique_id = ''){
		if( is_array($attr_value) ) {
			$css_value = array();

			foreach ($attr_value as $key => $value){
				$css_value[] = $this->enhanced_blocks_get_css_value($attrs, $key, $attr_key);
			}

			$css_value = implode('', $css_value);

			if( $this->enhanced_blocks_validate_css_selector($attrs, $attr_value) ) {
				$css = '';

				if( $column_key !== '' && $c_unique_id !== '' ){
					if ( $attr_key === 'box_shadow_attributes' && $css_value ) {
						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-column-box-shadow-outline.enhanced-blocks-inner-column-' . $column_key . '{';
						$css .= 'box-shadow:' . $css_value . ';';
						$css .= '}';

						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-column-box-shadow-inset.enhanced-blocks-inner-column-' . $column_key . '{';
						$css .= 'box-shadow:' . $css_value . ';';
						$css .= '}';
					}

					if ( $attr_key === 'box_shadow_hover_attributes' && $css_value ) {
						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-column-box-shadow-outline.enhanced-blocks-inner-column-' . $column_key . ':hover{';
						$css .= 'box-shadow:' . $css_value . ';';
						$css .= '}';

						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-column-box-shadow-inset.enhanced-blocks-inner-column-' . $column_key . ':hover{';
						$css .= 'box-shadow:' . $css_value . ';';
						$css .= '}';
					}

					if ( $attr_key === 'bg_gradient_linear_attributes' && $css_value ) {
						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.column-bg-gradient-linear.enhanced-blocks-inner-column-'.$column_key.'{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: linear-gradient(' . $css_value . ');';
						$css .= '}';
					}

					if ( $attr_key === 'bg_gradient_radial_attributes' && $css_value ) {
						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.column-bg-gradient-radial.enhanced-blocks-inner-column-'.$column_key.'{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: radial-gradient(at ' . $css_value . ');';
						$css .= '}';
					}

					if ( $attr_key === 'bg_overlay_gradient_linear_attributes' && $css_value ) {
						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.' .enhanced-blocks-column-background-overlay.enhanced-blocks-column-overlay-bg-gradient-linear{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: linear-gradient(' . $css_value . ');';
						$css .= '}';
					}

					if ( $attr_key === 'bg_overlay_gradient_radial_attributes' && $css_value ) {
						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.' .enhanced-blocks-column-background-overlay.enhanced-blocks-column-overlay-bg-gradient-radial{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: radial-gradient(at ' . $css_value . ');';
						$css .= '}';
					}


					if ( $attr_key === 'bg_hover_gradient_linear_attributes' && $css_value ) {
						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-column-bg-hover-gradient-linear.enhanced-blocks-inner-column-'.$column_key.':hover{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: linear-gradient(' . $css_value . ');';
						$css .= '}';
					}

					if ( $attr_key === 'bg_hover_gradient_radial_attributes' && $css_value ) {
						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-column-bg-hover-gradient-radial.enhanced-blocks-inner-column-'.$column_key.':hover{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: radial-gradient(at ' . $css_value . ');';
						$css .= '}';
					}

					if ( $attr_key === 'bg_overlay_hover_gradient_linear_attributes' && $css_value ) {
						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.':hover .enhanced-blocks-column-hover-overlay-bg-gradient-linear{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: linear-gradient(' . $css_value . ');';
						$css .= '}';
					}
					
					if ( $attr_key === 'bg_overlay_hover_gradient_radial_attributes' && $css_value ) {
						$css .=  $unique_id . ' .inner-blocks-wrap '.$c_unique_id.'.enhanced-blocks-inner-column-'.$column_key.':hover .enhanced-blocks-column-hover-overlay-bg-gradient-radial{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: radial-gradient(at ' . $css_value . ');';
						$css .= '}';
					}
				}

				if( $column_key === '' ){
					if ( $attr_key === 'box_shadow_attributes' && $css_value ) {
						$css .=  $unique_id . '.enhanced-blocks-box-shadow-outline{';
						$css .= 'box-shadow:'. $css_value .';';
						$css .= '}';

						$css .=  $unique_id . '.enhanced-blocks-box-shadow-inset{';
						$css .= 'box-shadow:'. $css_value .';';
						$css .= '}';
					}

					if ( $attr_key === 'box_shadow_hover_attributes' && $css_value ) {
						$css .=  $unique_id . '.enhanced-blocks-box-shadow-outline-hover:hover{';
						$css .= 'box-shadow:'. $css_value .';';
						$css .= '}';

						$css .=  $unique_id . '.enhanced-blocks-box-shadow-inset-hover:hover{';
						$css .= 'box-shadow:'. $css_value .';';
						$css .= '}';
					}

					if ( $attr_key === 'bg_gradient_linear_attributes' && $css_value ) {
						$css .=  $unique_id . '.section-bg-gradient-linear{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: linear-gradient(' . $css_value . ');';
						$css .= '}';
					}
					

					if ( $attr_key === 'bg_gradient_radial_attributes' && $css_value ) {
						$css .=  $unique_id . '.section-bg-gradient-radial{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: radial-gradient(at ' . $css_value . ');';
						$css .= '}';
					}


					if ( $attr_key === 'bg_overlay_gradient_linear_attributes' && $css_value ) {
						$css .= $unique_id . ' .enhanced-blocks-background-overlay.enhanced-blocks-overlay-bg-gradient-linear{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: linear-gradient(' . $css_value . ');';
						$css .= '}';
					}

					if ( $attr_key === 'bg_overlay_gradient_radial_attributes' && $css_value ) {
						$css .=  $unique_id . ' .enhanced-blocks-background-overlay.enhanced-blocks-overlay-bg-gradient-radial{';
						$css .= 'background-color: transparent;';
						$css .= 'background-image: radial-gradient(at ' . $css_value . ');';
						$css .= '}';
					}

				}

				if ( $attr_key === 'testimonial_bg_linear_gradient_attributes' && $css_value ) {
					$css .=  $unique_id . '.en-testimonial-slider.gradient-linear {';
					$css .= 'background-color: transparent;';
					$css .= 'background-image: linear-gradient(' . $css_value . ');';
					$css .= '}';
				}

				if ( $attr_key === 'testimonial_bg_radial_gradient_attributes' && $css_value ) {
					$css .=  $unique_id . '.en-testimonial-slider.gradient-radial {';
					$css .= 'background-color: transparent;';
					$css .= 'background-image: radial-gradient(at ' . $css_value . ');';
					$css .= '}';
				}
			// profile Background
				if ( $attr_key === 'profile_bg_linear_gradient_attributes' && $css_value ) {
					$css .=  $unique_id . '.en-author-profile.gradient-linear {';
					$css .= 'background-color: transparent;';
					$css .= 'background-image: linear-gradient(' . $css_value . ');';
					$css .= '}';
				}

				if ( $attr_key === 'profile_bg_radial_gradient_attributes' && $css_value ) {
					$css .=  $unique_id . '.en-author-profile.gradient-radial {';
					$css .= 'background-color: transparent;';
					$css .= 'background-image: radial-gradient(at ' . $css_value . ');';
					$css .= '}';
				}
				
				if ( $attr_key === 'profile_bg_overlay_gradient_linear_attributes' && $css_value ) {
					$css .= $unique_id . ' .en-author-profile-overlay.gradient-overlay-linear{';
					$css .= 'background-color: transparent;';
					$css .= 'background-image: linear-gradient(' . $css_value . ');';
					$css .= '}';
				}
				
				if ( $attr_key === 'profile_bg_overlay_gradient_radial_attributes' && $css_value ) {
					$css .=  $unique_id . ' .en-author-profile-overlay.gradient-overlay-radial{';
					$css .= 'background-color: transparent;';
					$css .= 'background-image: radial-gradient(at ' . $css_value . ');';
					$css .= '}';
				}

				return $css;
			}
		}
	}

	public function enhanced_blocks_generate_row_layout_css( $attrs, $unique_id){

		$attributes = $this->enhanced_blocks_get_attributes();

		$css = '';

		foreach ($attributes as $attr_key => $attr_value){

			if( $attr_key === 'desktop_attributes' || $attr_key === 'tab_attributes' || $attr_key === 'mobile_attributes' || $attr_key === 'desktop_bg_image_attributes' || $attr_key === 'desktop_attributes_hover' || $attr_key === 'shadow_attributes' || $attr_key === 'desktop_innerblocks_attributes' || $attr_key === 'desktop_boxed_innerblocks_attributes' || $attr_key === 'desktop_bg_overlay_attributes' || $attr_key === 'desktop_bg_overlay_bg_img_attributes' || $attr_key === 'shape_divider_top_attributes' || $attr_key === 'shape_divider_bottom_attributes' || $attr_key === 'shape_divider_top_color_attributes' || $attr_key === 'shape_divider_bottom_color_attributes' || $attr_key === 'column_gap' ){
				$css .= $this->enhanced_blocks_get_css_property_value($attr_value, $attr_key, $attrs, $unique_id);
			}

			if( $attr_key === 'box_shadow_attributes' ||  $attr_key === 'box_shadow_hover_attributes' || $attr_key === 'bg_gradient_linear_attributes' || $attr_key === 'bg_gradient_radial_attributes' || $attr_key === 'bg_overlay_gradient_linear_attributes' || $attr_key === 'bg_overlay_gradient_radial_attributes'){
				$css .= $this->enhanced_blocks_get_multiple_css_property_value($attr_value, $attr_key, $attrs, $unique_id);
			}
		}

		return $css;

	}

	public function enhanced_blocks_generate_column_layout_css($attrs, $unique_id, $column_key, $c_unique_id){
		$css = '';
		$attributes = $this->enhanced_blocks_get_attributes();
		foreach ($attributes as $attr_key => $attr_value){
			if( $attr_key === 'desktop_attributes' || $attr_key === 'tab_attributes' || $attr_key === 'mobile_attributes' || $attr_key === 'desktop_bg_image_attributes' || $attr_key === 'desktop_attributes_hover' || $attr_key === 'shadow_attributes' || $attr_key === 'desktop_innerblocks_attributes' || $attr_key === 'desktop_boxed_innerblocks_attributes' || $attr_key === 'desktop_bg_overlay_attributes' || $attr_key === 'desktop_bg_overlay_bg_img_attributes' || $attr_key === 'bg_hover_image_attributes' || $attr_key === 'desktop_bg_overlay_hover_bg_img_attributes' || $attr_key === 'desktop_bg_overlay_hover_attributes' || $attr_key === 'column_gap' ){
				$css .= $this->enhanced_blocks_get_css_property_value($attr_value, $attr_key, $attrs, $unique_id, $column_key, $c_unique_id);
			}

			if( $attr_key === 'box_shadow_attributes' ||  $attr_key === 'box_shadow_hover_attributes' || $attr_key === 'bg_gradient_linear_attributes' || $attr_key === 'bg_gradient_radial_attributes' || $attr_key === 'bg_overlay_gradient_linear_attributes' || $attr_key === 'bg_overlay_gradient_radial_attributes' || $attr_key === 'bg_hover_gradient_linear_attributes' || $attr_key === 'bg_hover_gradient_radial_attributes' || $attr_key === 'bg_overlay_hover_gradient_linear_attributes' || $attr_key === 'bg_overlay_hover_gradient_radial_attributes'){
				$css .= $this->enhanced_blocks_get_multiple_css_property_value($attr_value, $attr_key, $attrs, $unique_id, $column_key, $c_unique_id);
			}
		}
		return $css;
	}
	
	public function icon_list_gfont( $attr ){
		if ( isset( $attr['googleFont'] ) && $attr['googleFont'] && ( ! isset( $attr['loadGoogleFont'] ) || true == $attr['loadGoogleFont'] ) && isset( $attr['typography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['typography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['typography'],
					'fontvariants' => ( isset( $attr['fontVariant'] ) && ! empty( $attr['fontVariant'] ) ? array( $attr['fontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['fontSubset'] ) && ! empty( $attr['fontSubset'] ) ? array( $attr['fontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['typography'] ] = $add_font;
			} else {
				if ( isset( $attr['fontVariant'] ) && ! empty( $attr['fontVariant'] ) ) {
					if ( ! in_array( $attr['fontVariant'], self::$gfonts[ $attr['typography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['typography'] ]['fontvariants'], $attr['fontVariant'] );
					}
				}
				if ( isset( $attr['fontSubset'] ) && ! empty( $attr['fontSubset'] ) ) {
					if ( ! in_array( $attr['fontSubset'], self::$gfonts[ $attr['typography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['typography'] ]['fontsubsets'], $attr['fontSubset'] );
					}
				}
			}
		}
	}

	public function notice_block_gfont($attr){
		if ( isset( $attr['googleFont'] ) && $attr['googleFont'] && ( ! isset( $attr['loadGoogleFont'] ) || true == $attr['loadGoogleFont'] ) && isset( $attr['typography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['typography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['typography'],
					'fontvariants' => ( isset( $attr['fontVariant'] ) && ! empty( $attr['fontVariant'] ) ? array( $attr['fontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['fontSubset'] ) && ! empty( $attr['fontSubset'] ) ? array( $attr['fontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['typography'] ] = $add_font;
			} else {
				if ( isset( $attr['fontVariant'] ) && ! empty( $attr['fontVariant'] ) ) {
					if ( ! in_array( $attr['fontVariant'], self::$gfonts[ $attr['typography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['typography'] ]['fontvariants'], $attr['fontVariant'] );
					}
				}
				if ( isset( $attr['fontSubset'] ) && ! empty( $attr['fontSubset'] ) ) {
					if ( ! in_array( $attr['fontSubset'], self::$gfonts[ $attr['typography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['typography'] ]['fontsubsets'], $attr['fontSubset'] );
					}
				}
			}
		}
	}
	// Post Grid Google font
	public function post_grid_gfont( $attr ){
		
		if ( isset( $attr['metaGoogleFont'] ) && $attr['metaGoogleFont'] && ( ! isset( $attr['metaLoadGoogleFont'] ) || true == $attr['metaLoadGoogleFont'] ) && isset( $attr['metaTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['metaTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['metaTypography'],
					'fontvariants' => ( isset( $attr['metaFontVariant'] ) && ! empty( $attr['metaFontVariant'] ) ? array( $attr['metaFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['metaFontSubset'] ) && ! empty( $attr['metaFontSubset'] ) ? array( $attr['metaFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['metaTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['metaFontVariant'] ) && ! empty( $attr['metaFontVariant'] ) ) {
					if ( ! in_array( $attr['metaFontVariant'], self::$gfonts[ $attr['metaTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['metaTypography'] ]['fontvariants'], $attr['metaFontVariant'] );
					}
				}
				if ( isset( $attr['metaFontSubset'] ) && ! empty( $attr['metaFontSubset'] ) ) {
					if ( ! in_array( $attr['metaFontSubset'], self::$gfonts[ $attr['metaTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['metaTypography'] ]['fontsubsets'], $attr['metaFontSubset'] );
					}
				}
			}
		}


		if ( isset( $attr['titleGoogleFont'] ) && $attr['titleGoogleFont'] && ( ! isset( $attr['titleLoadGoogleFont'] ) || true == $attr['titleLoadGoogleFont'] ) && isset( $attr['titleTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['titleTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['titleTypography'],
					'fontvariants' => ( isset( $attr['titleFontVariant'] ) && ! empty( $attr['titleFontVariant'] ) ? array( $attr['titleFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['titleFontSubset'] ) && ! empty( $attr['titleFontSubset'] ) ? array( $attr['titleFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['titleTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['titleFontVariant'] ) && ! empty( $attr['titleFontVariant'] ) ) {
					if ( ! in_array( $attr['titleFontVariant'], self::$gfonts[ $attr['titleTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['titleTypography'] ]['fontvariants'], $attr['titleFontVariant'] );
					}
				}
				if ( isset( $attr['titleFontSubset'] ) && ! empty( $attr['titleFontSubset'] ) ) {
					if ( ! in_array( $attr['titleFontSubset'], self::$gfonts[ $attr['titleTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['titleTypography'] ]['fontsubsets'], $attr['titleFontSubset'] );
					}
				}
			}
		}

		if ( isset( $attr['contentGoogleFont'] ) && $attr['contentGoogleFont'] && ( ! isset( $attr['contentLoadGoogleFont'] ) || true == $attr['contentLoadGoogleFont'] ) && isset( $attr['contentTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['contentTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['contentTypography'],
					'fontvariants' => ( isset( $attr['contentFontVariant'] ) && ! empty( $attr['contentFontVariant'] ) ? array( $attr['contentFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['contentFontSubset'] ) && ! empty( $attr['contentFontSubset'] ) ? array( $attr['contentFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['contentTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['contentFontVariant'] ) && ! empty( $attr['contentFontVariant'] ) ) {
					if ( ! in_array( $attr['contentFontVariant'], self::$gfonts[ $attr['contentTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['contentTypography'] ]['fontvariants'], $attr['contentFontVariant'] );
					}
				}
				if ( isset( $attr['contentFontSubset'] ) && ! empty( $attr['contentFontSubset'] ) ) {
					if ( ! in_array( $attr['contentFontSubset'], self::$gfonts[ $attr['contentTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['contentTypography'] ]['fontsubsets'], $attr['contentFontSubset'] );
					}
				}
			}
		}

		if ( isset( $attr['buttonGoogleFont'] ) && $attr['buttonGoogleFont'] && ( ! isset( $attr['buttonLoadGoogleFont'] ) || true == $attr['buttonLoadGoogleFont'] ) && isset( $attr['buttonTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['buttonTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['buttonTypography'],
					'fontvariants' => ( isset( $attr['buttonFontVariant'] ) && ! empty( $attr['buttonFontVariant'] ) ? array( $attr['buttonFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['buttonFontSubset'] ) && ! empty( $attr['buttonFontSubset'] ) ? array( $attr['buttonFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['buttonTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['buttonFontVariant'] ) && ! empty( $attr['buttonFontVariant'] ) ) {
					if ( ! in_array( $attr['buttonFontVariant'], self::$gfonts[ $attr['buttonTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['buttonTypography'] ]['fontvariants'], $attr['buttonFontVariant'] );
					}
				}
				if ( isset( $attr['buttonFontSubset'] ) && ! empty( $attr['buttonFontSubset'] ) ) {
					if ( ! in_array( $attr['buttonFontSubset'], self::$gfonts[ $attr['buttonTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['buttonTypography'] ]['fontsubsets'], $attr['buttonFontSubset'] );
					}
				}
			}
		}
	}
	
	//Testimonial Google font
	public function enhanced_testimonial_gfont( $attr ) {
		// Testimonial Description
		if ( isset( $attr['googleFont'] ) && $attr['googleFont'] && ( ! isset( $attr['loadGoogleFont'] ) || true == $attr['loadGoogleFont'] ) && isset( $attr['typography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['typography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['typography'],
					'fontvariants' => ( isset( $attr['fontVariant'] ) && ! empty( $attr['fontVariant'] ) ? array( $attr['fontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['fontSubset'] ) && ! empty( $attr['fontSubset'] ) ? array( $attr['fontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['typography'] ] = $add_font;
			} else {
				if ( isset( $attr['fontVariant'] ) && ! empty( $attr['fontVariant'] ) ) {
					if ( ! in_array( $attr['fontVariant'], self::$gfonts[ $attr['typography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['typography'] ]['fontvariants'], $attr['fontVariant'] );
					}
				}
				if ( isset( $attr['fontSubset'] ) && ! empty( $attr['fontSubset'] ) ) {
					if ( ! in_array( $attr['fontSubset'], self::$gfonts[ $attr['typography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['typography'] ]['fontsubsets'], $attr['fontSubset'] );
					}
				}
			}
		}
		// Testimonial Name
		if ( isset( $attr['tesNameGoogleFont'] ) && $attr['tesNameGoogleFont'] && ( ! isset( $attr['tesNameLoadGoogleFont'] ) || true == $attr['tesNameLoadGoogleFont'] ) && isset( $attr['tesNameTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['tesNameTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['tesNameTypography'],
					'fontvariants' => ( isset( $attr['tesNameFontVariant'] ) && ! empty( $attr['tesNameFontVariant'] ) ? array( $attr['tesNameFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['tesNameFontSubset'] ) && ! empty( $attr['tesNameFontSubset'] ) ? array( $attr['tesNameFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['tesNameTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['tesNameFontVariant'] ) && ! empty( $attr['tesNameFontVariant'] ) ) {
					if ( ! in_array( $attr['tesNameFontVariant'], self::$gfonts[ $attr['tesNameTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['tesNameTypography'] ]['fontvariants'], $attr['tesNameFontVariant'] );
					}
				}
				if ( isset( $attr['tesNameFontSubset'] ) && ! empty( $attr['tesNameFontSubset'] ) ) {
					if ( ! in_array( $attr['tesNameFontSubset'], self::$gfonts[ $attr['tesNameTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['tesNameTypography'] ]['fontsubsets'], $attr['tesNameFontSubset'] );
					}
				}
			}
		}
		// Testimonial Title
		if ( isset( $attr['tesTitleGoogleFont'] ) && $attr['tesTitleGoogleFont'] && ( ! isset( $attr['tesTitleLoadGoogleFont'] ) || true == $attr['tesTitleLoadGoogleFont'] ) && isset( $attr['tesTitleTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['tesTitleTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['tesTitleTypography'],
					'fontvariants' => ( isset( $attr['tesTitleFontVariant'] ) && ! empty( $attr['tesTitleFontVariant'] ) ? array( $attr['tesTitleFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['tesTitleFontSubset'] ) && ! empty( $attr['tesTitleFontSubset'] ) ? array( $attr['tesTitleFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['tesTitleTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['tesTitleFontVariant'] ) && ! empty( $attr['tesTitleFontVariant'] ) ) {
					if ( ! in_array( $attr['tesTitleFontVariant'], self::$gfonts[ $attr['tesTitleTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['tesTitleTypography'] ]['fontvariants'], $attr['tesTitleFontVariant'] );
					}
				}
				if ( isset( $attr['tesTitleFontSubset'] ) && ! empty( $attr['tesTitleFontSubset'] ) ) {
					if ( ! in_array( $attr['tesTitleFontSubset'], self::$gfonts[ $attr['tesTitleTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['tesTitleTypography'] ]['fontsubsets'], $attr['tesTitleFontSubset'] );
					}
				}
			}
		}
	}

	public function generate_social_share_css($attrs, $unique_id){

		$shareButtonStyle = isset($attrs['shareButtonStyle']) ? $attrs['shareButtonStyle'] : 'eb-sharing-icon-only';

		$css = '';

		if ( isset( $attrs['desktopIconSpacing'] ) && ! empty( $attrs['desktopIconSpacing'] ) ) {
			$css .= $unique_id . ' ul.eb-social-sharing-links li {';
			$css .= 'margin-right:' . $attrs['desktopIconSpacing'] . 'px;';
			$css .= '}';
		}

		if ( isset( $attrs['shareButtonColorType'] ) && $attrs['shareButtonColorType'] === 'eb-sharing-icon-color-standard' ) {
			if ( (isset( $attrs['bgColor'] ) && ! empty( $attrs['bgColor'] )) && $this->getColor($attrs['bgColor']) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a {';
				$css .= 'background-color:' . $this->getColor($attrs['bgColor']) . ';';
				$css .= '}';
			} else {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a {';
				$css .= 'background-color: #a7376f;';
				$css .= '}';
			}

			if ( (isset( $attrs['borderColor'] ) && ! empty( $attrs['borderColor'] )) && $this->getColor($attrs['borderColor']) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a {';
				$css .= 'border-color:' . $this->getColor($attrs['borderColor']) . ';';
				$css .= '}';
			} else {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a {';
				$css .= 'border-color: #a7376f;';
				$css .= '}';
			}


			if ( (isset( $attrs['iconColor'] ) && ! empty( $attrs['iconColor'] ) ) && $this->getColor($attrs['iconColor']) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a i{';
				$css .= 'color:' . $this->getColor($attrs['iconColor']) . ';';
				$css .= '}';
			}

			if ( (isset( $attrs['textColor'] ) && ! empty( $attrs['textColor'] ) ) && $this->getColor($attrs['textColor']) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a span{';
				$css .= 'color:' . $this->getColor($attrs['textColor']) . ';';
				$css .= '}';
			}

		}

		if ( $shareButtonStyle === 'eb-sharing-icon-only') {
			if ( isset( $attrs['desktopIconWidth'] ) && ! empty( $attrs['desktopIconWidth'] ) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a {';
				$css .= 'width:' . $attrs['desktopIconWidth'] . 'px;';
				$css .= '}';
			}

			if ( isset( $attrs['desktopIconHeight'] ) && ! empty( $attrs['desktopIconHeight'] ) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a {';
				$css .= 'height:' . $attrs['desktopIconHeight'] . 'px;';
				$css .= 'line-height:' . $attrs['desktopIconHeight'] . 'px;';
				$css .= '}';
			}
		}

		if ( isset( $attrs['desktopIconSize'] ) && ! empty( $attrs['desktopIconSize'] ) ) {
			$css .= $unique_id . ' ul.eb-social-sharing-links li a i{';
			$css .= 'font-size:' . $attrs['desktopIconSize'] . 'px;';
			$css .= '}';
		}

		if ( isset( $attrs['desktopTextSize'] ) && ! empty( $attrs['desktopTextSize'] ) ) {
			$css .= $unique_id . ' ul.eb-social-sharing-links li a span{';
			$css .= 'font-size:' . $attrs['desktopTextSize'] . 'px;';
			$css .= '}';
		}


		if ( isset( $attrs['tabTextSize'] ) || isset( $attrs['tabIconSize'] ) || isset( $attrs['tabIconHeight'] ) || isset( $attrs['tabIconWidth'] ) || isset( $attrs['tabIconSpacing'] ) ) {

			$css .= '@media only screen and (max-width: 1024px) and (min-width: 768px) {';

			if ( isset( $attrs['tabTextSize'] ) && ! empty( $attrs['tabTextSize'] ) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a span{';
				$css .= 'font-size:' . $attrs['tabTextSize'] . 'px;';
				$css .= '}';
			}

			if ( isset( $attrs['tabIconSize'] ) && ! empty( $attrs['tabIconSize'] ) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a i{';
				$css .= 'font-size:' . $attrs['tabIconSize'] . 'px;';
				$css .= '}';
			}


			if ( $shareButtonStyle === 'eb-sharing-icon-only') {

				if ( isset( $attrs['tabIconWidth'] ) && ! empty( $attrs['tabIconWidth'] ) ) {
					$css .= $unique_id . ' ul.eb-social-sharing-links li a {';
					$css .= 'width:' . $attrs['tabIconWidth'] . 'px;';
					$css .= '}';
				}

				if ( isset( $attrs['tabIconHeight'] ) && ! empty( $attrs['tabIconHeight'] ) ) {
					$css .= $unique_id . ' ul.eb-social-sharing-links li a {';
					$css .= 'height:' . $attrs['tabIconHeight'] . 'px;';
					$css .= 'line-height:' . ($attrs['tabIconHeight']+5) . 'px;';
					$css .= '}';
				}


			}

			if ( isset( $attrs['tabIconSpacing'] ) && ! empty( $attrs['tabIconSpacing'] ) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li {';
				$css .= 'margin-right:' . $attrs['tabIconSpacing'] . 'px;';
				$css .= '}';
			}

			$css .= '}';
		}



		if ( isset( $attrs['mobileTextSize'] ) || isset( $attrs['mobileIconSize'] ) || isset( $attrs['mobileIconHeight'] ) || isset( $attrs['mobileIconWidth'] ) || isset( $attrs['mobileIconSpacing'] ) ) {

			$css .= '@media (max-width: 767px) {';

			if ( isset( $attrs['mobileTextSize'] ) && ! empty( $attrs['mobileTextSize'] ) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a span{';
				$css .= 'font-size:' . $attrs['mobileTextSize'] . 'px;';
				$css .= '}';
			}

			if ( isset( $attrs['mobileIconSize'] ) && ! empty( $attrs['mobileIconSize'] ) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li a i{';
				$css .= 'font-size:' . $attrs['mobileIconSize'] . 'px;';
				$css .= '}';
			}


			if ( $shareButtonStyle === 'eb-sharing-icon-only') {

				if ( isset( $attrs['mobileIconWidth'] ) && ! empty( $attrs['mobileIconWidth'] ) ) {
					$css .= $unique_id . ' ul.eb-social-sharing-links li a {';
					$css .= 'width:' . $attrs['mobileIconWidth'] . 'px;';
					$css .= '}';
				}

				if ( isset( $attrs['mobileIconHeight'] ) && ! empty( $attrs['mobileIconHeight'] ) ) {
					$css .= $unique_id . ' ul.eb-social-sharing-links li a {';
					$css .= 'height:' . $attrs['mobileIconHeight'] . 'px;';
					$css .= 'line-height:' . ($attrs['mobileIconHeight']+5) . 'px;';
					$css .= '}';
				}


			}

			if ( isset( $attrs['mobileIconSpacing'] ) && ! empty( $attrs['mobileIconSpacing'] ) ) {
				$css .= $unique_id . ' ul.eb-social-sharing-links li {';
				$css .= 'margin-right:' . $attrs['mobileIconSpacing'] . 'px;';
				$css .= '}';
			}

			$css .= '}';
		}

		return $css;

	}

	public function generate_icon_list_css($attrs, $unique_id){
		$columns = isset($attrs['columns']) ? $attrs['columns'] : 1;
		$borderTypeNormal = isset($attrs['borderTypeNormal']) ? $attrs['borderTypeNormal'] : 'solid';
		$typography = isset($attrs['typography']) && !empty($attrs['typography']) ? $attrs['typography'] : '';
		$font_family = isset($attrs['googleFont']) && $typography !== '' ? '"'.$typography.'", sans-serif' : $typography;
		$icon_prefix = isset($attrs['icon']) ? substr($attrs['icon'], 0, 3) : 'fas';
		
		

		$css = '';
		if ( $columns ) {
			$css .= $unique_id . '{';
			$css .= 'columns:' . $columns . '';
			$css .= '}';
		}

		if ( isset( $attrs['typography'] ) || isset( $attrs['fontWeight'] ) || isset( $attrs['fontStyle'] ) || isset( $attrs['desktopItemGap'] )  || isset($attrs['desktopTextIndent']) || isset($attrs['textColor']) || isset($attrs['desktopTextSize']) || isset($attrs['desktopIconBoxHeight']) ) {
			$css .= $unique_id . ' ul li {';
			$css .= isset( $font_family ) && ! empty( $font_family ) ? 'font-family:' . $font_family . ';' : '';
			$css .= isset( $attrs['fontWeight'] ) && ! empty( $attrs['fontWeight'] ) ? 'font-weight:' . $attrs['fontWeight'] . ';' : '';
			$css .= isset( $attrs['fontStyle'] ) && ! empty( $attrs['fontStyle'] ) ? 'font-style:' . $attrs['fontStyle'] . ';' : '';
			$css .= ( (isset( $attrs['textColor'] ) && ! empty( $attrs['textColor'] )) && $this->getColor($attrs['textColor']) ) ? 'color:' . $this->getColor($attrs['textColor']) . ';' : '';
			$css .= isset( $attrs['desktopTextSize'] ) && ! empty( $attrs['desktopTextSize'] ) ? 'font-size:' . $attrs['desktopTextSize'] . 'px;' : '';
			$css .= isset( $attrs['desktopIconBoxHeight'] ) && ! empty( $attrs['desktopIconBoxHeight'] ) ? 'line-height:' . $attrs['desktopIconBoxHeight'] . 'px;' : '';
			$css .= isset( $attrs['desktopTextIndent'] ) && ! empty( $attrs['desktopTextIndent'] ) ? 'padding-left:' . $attrs['desktopTextIndent'] . 'px;' : '';
			$css .= isset( $attrs['desktopItemGap'] ) && ! empty( $attrs['desktopItemGap'] ) ? 'margin-bottom:' . $attrs['desktopItemGap'] . 'px;' : '';
			$css .= '}';
		}
		

		if ( isset( $attrs['desktopIconSize'] ) || isset($attrs['unicode']) || isset($attrs['iconColor']) || isset($attrs['iconBoxBgColor']) || isset($attrs['desktopIconBoxHeight']) || isset($attrs['desktopIconBoxWidth']) || $borderTypeNormal || isset( $attrs['borderRadiusNormalTop'] ) || isset( $attrs['borderRadiusNormalRight'] ) || isset( $attrs['borderRadiusNormalLeft'] ) || isset( $attrs['borderRadiusNormalBottom'] ) ) {
			$css .= $unique_id . ' ul li:before {';
			$css .= isset( $attrs['desktopIconSize'] ) && ! empty( $attrs['desktopIconSize'] ) ? 'font-size:' . $attrs['desktopIconSize'] . 'px;' : '';
			$css .= isset( $attrs['unicode'] ) && ! empty( $attrs['unicode'] ) ? 'content:\'' . $attrs['unicode'] . '\';' : '';
			$css .= $icon_prefix === 'fab' ? 'font-weight: 400; font-family: \'Font Awesome 5 Brands\';' : '';
			$css .= ((isset( $attrs['iconColor'] ) && ! empty( $attrs['iconColor'] )) && $this->getColor($attrs['iconColor'])) ? 'color:' .  $this->getColor($attrs['iconColor']) . ';' : '';
			$css .= ((isset( $attrs['iconBoxBgColor'] ) && ! empty( $attrs['iconBoxBgColor'] ) ) && $this->getColor($attrs['iconBoxBgColor']) )? 'background-color:' .  $this->getColor($attrs['iconBoxBgColor']) . ';' : '';
			$css .= isset( $attrs['desktopIconBoxHeight'] ) && ! empty( $attrs['desktopIconBoxHeight'] ) ? 'height:' . $attrs['desktopIconBoxHeight'] . 'px;' : '';
			$css .= isset( $attrs['desktopIconBoxHeight'] ) && ! empty( $attrs['desktopIconBoxHeight'] ) ? 'line-height:' . $attrs['desktopIconBoxHeight'] . 'px;' : '';
			$css .= isset( $attrs['desktopIconBoxWidth'] ) && ! empty( $attrs['desktopIconBoxWidth'] ) ? 'width:' . $attrs['desktopIconBoxWidth'] . 'px;' : '';
			$css .= isset( $attrs['borderTransitionDurationHover'] ) && ! empty( $attrs['borderTransitionDurationHover'] ) ? 'transition:' . $attrs['borderTransitionDurationHover'] . 's;' : '';
			$css .= $borderTypeNormal ? 'border-style:' . $borderTypeNormal . ';' : '';
			$css .= isset( $attrs['borderColorNormal'] ) && ! empty( $attrs['borderColorNormal'] ) ? 'border-color:' . $attrs['borderColorNormal'] . ';' : 'border-color: #a7376f;';
			$css .= isset( $attrs['borderWidthNormalTop'] ) && ! empty( $attrs['borderWidthNormalTop'] ) ? 'border-top-width:' . $attrs['borderWidthNormalTop'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthNormalRight'] ) && ! empty( $attrs['borderWidthNormalRight'] ) ? 'border-right-width:' . $attrs['borderWidthNormalRight'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthNormalBottom'] ) && ! empty( $attrs['borderWidthNormalBottom'] ) ? 'border-bottom-width:' . $attrs['borderWidthNormalBottom'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthNormalLeft'] ) && ! empty( $attrs['borderWidthNormalLeft'] ) ? 'border-left-width:' . $attrs['borderWidthNormalLeft'] . 'px;' : '';
			$css .= isset( $attrs['borderRadiusNormalTop'] ) && ! empty( $attrs['borderRadiusNormalTop'] ) ? 'border-top-left-radius:' . $attrs['borderRadiusNormalTop'] . 'px;' : '';
			$css .= isset( $attrs['borderRadiusNormalRight'] ) && ! empty( $attrs['borderRadiusNormalRight'] ) ? 'border-top-right-radius:' . $attrs['borderRadiusNormalRight'] . 'px;' : '';
			$css .= isset( $attrs['borderRadiusNormalLeft'] ) && ! empty( $attrs['borderRadiusNormalLeft'] ) ? 'border-bottom-left-radius:' . $attrs['borderRadiusNormalLeft'] . 'px;' : '';
			$css .= isset( $attrs['borderRadiusNormalBottom'] ) && ! empty( $attrs['borderRadiusNormalBottom'] ) ? 'border-bottom-right-radius:' . $attrs['borderRadiusNormalBottom'] . 'px;' : '';
			$css .= '}';
		}


		if ( isset($attrs['iconHoverColor']) || isset($attrs['iconBoxBgHoverColor']) || isset( $attrs['borderTypeHover'] ) || isset( $attrs['borderRadiusHoverTop'] ) || isset( $attrs['borderRadiusHoverRight'] ) || isset( $attrs['borderRadiusHoverBottom'] ) || isset( $attrs['borderRadiusHoverLeft'] ) ) {
			$css .= $unique_id . ' ul li:hover:before {';
			$css .= ((isset( $attrs['iconHoverColor'] ) && ! empty( $attrs['iconHoverColor'] )) && $this->getColor($attrs['iconHoverColor']) ) ? 'color:' . $this->getColor($attrs['iconHoverColor']) . ';' : '';
			$css .= ((isset( $attrs['iconBoxBgHoverColor'] ) && ! empty( $attrs['iconBoxBgHoverColor'] )) && $this->getColor($attrs['iconBoxBgHoverColor']) ) ? 'background-color:' . $this->getColor($attrs['iconBoxBgHoverColor']) . ';' : '';
			$css .= isset( $attrs['borderTypeHover'] ) && ! empty( $attrs['borderTypeHover'] ) ? 'border-style:' . $attrs['borderTypeHover'] . ';' : '';
			$css .= isset( $attrs['borderColorHover'] ) && ! empty( $attrs['borderColorHover'] ) ? 'border-color:' . $attrs['borderColorHover'] . ';' : '';
			$css .= isset( $attrs['borderWidthHoverTop'] ) && ! empty( $attrs['borderWidthHoverTop'] ) ? 'border-top-width:' . $attrs['borderWidthHoverTop'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthHoverRight'] ) && ! empty( $attrs['borderWidthHoverRight'] ) ? 'border-right-width:' . $attrs['borderWidthHoverRight'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthHoverBottom'] ) && ! empty( $attrs['borderWidthHoverBottom'] ) ? 'border-bottom-width:' . $attrs['borderWidthHoverBottom'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthHoverLeft'] ) && ! empty( $attrs['borderWidthHoverLeft'] ) ? 'border-left-width:' . $attrs['borderWidthHoverLeft'] . 'px;' : '';
			$css .= isset( $attrs['borderRadiusHoverTop'] ) && ! empty( $attrs['borderRadiusHoverTop'] ) ? 'border-top-left-radius:' . $attrs['borderRadiusHoverTop'] . 'px;' : 'border-top-left-radius: 50px;';
			$css .= isset( $attrs['borderRadiusHoverRight'] ) && ! empty( $attrs['borderRadiusHoverRight'] ) ? 'border-top-right-radius:' . $attrs['borderRadiusHoverRight'] . 'px;' : 'border-top-right-radius: 50px;';
			$css .= isset( $attrs['borderRadiusHoverBottom'] ) && ! empty( $attrs['borderRadiusHoverBottom'] ) ? 'border-bottom-left-radius:' . $attrs['borderRadiusHoverBottom'] . 'px;' : 'border-bottom-left-radius: 50px;';
			$css .= isset( $attrs['borderRadiusHoverLeft'] ) && ! empty( $attrs['borderRadiusHoverLeft'] ) ? 'border-bottom-right-radius:' . $attrs['borderRadiusHoverLeft'] . 'px;' : 'border-bottom-right-radius: 50px;';
			$css .= '}';
		}
		
		if ( isset( $attrs['textHoverColor'] ) ) {
			$css .= $unique_id . ' ul li:hover{';
			$css .= ( (isset( $attrs['textHoverColor'] ) && ! empty( $attrs['textHoverColor'] ) ) && $this->getColor($attrs['textHoverColor']) ) ? 'color:' . $this->getColor($attrs['textHoverColor']) . ';' : '';
			$css .= '}';
		}

		if ( isset( $attrs['boxShadowNormalColor'] ) ) {
			$css .= $unique_id . ' ul li:before{';
			$css .= $this->getBoxShadow($attrs, $type='Normal');
			$css .= '}';
		}

		if ( isset( $attrs['boxShadowHoverColor'] ) ) {
			$css .= $unique_id . ' ul li:before:hover{';
			$css .= $this->getBoxShadow($attrs, $type='Hover');
			$css .= '}';
		}

		// tab layout css
		if ( isset( $attrs['tabItemGap'] )  || isset($attrs['tabTextIndent']) || isset($attrs['tabTextSize']) || isset($attrs['tabIconBoxHeight']) ) {
			$css .= '@media only screen and (max-width: 1024px) and (min-width: 768px) {';
			$css .= $unique_id . ' ul li {';
			$css .= isset( $attrs['tabTextSize'] ) && ! empty( $attrs['tabTextSize'] ) ? 'font-size:' . $attrs['tabTextSize'] . 'px;' : '';
			$css .= isset( $attrs['tabIconBoxHeight'] ) && ! empty( $attrs['tabIconBoxHeight'] ) ? 'line-height:' . $attrs['tabIconBoxHeight'] . 'px;' : '';
			$css .= isset( $attrs['tabTextIndent'] ) && ! empty( $attrs['tabTextIndent'] ) ? 'padding-left:' . $attrs['tabTextIndent'] . 'px;' : '';
			$css .= isset( $attrs['tabItemGap'] ) && ! empty( $attrs['tabItemGap'] ) ? 'margin-bottom:' . $attrs['tabItemGap'] . 'px;' : '';
			$css .= '}';
			$css .= '}';
		}

		if ( isset($attrs['tabIconBoxHeight']) || isset($attrs['tabIconBoxWidth']) || isset($attrs['tabIconSize']) ) {
			$css .= '@media only screen and (max-width: 1024px) and (min-width: 768px) {';
			$css .= $unique_id . ' ul li:before {';
			$css .= isset( $attrs['tabIconSize'] ) && ! empty( $attrs['tabIconSize'] ) ? 'font-size:' . $attrs['tabIconSize'] . 'px;' : '';
			$css .= isset( $attrs['tabIconBoxHeight'] ) && ! empty( $attrs['tabIconBoxHeight'] ) ? 'height:' . $attrs['tabIconBoxHeight'] . 'px;' : '';
			$css .= isset( $attrs['tabIconBoxHeight'] ) && ! empty( $attrs['tabIconBoxHeight'] ) ? 'line-height:' . $attrs['tabIconBoxHeight'] . 'px;' : '';
			$css .= isset( $attrs['tabIconBoxWidth'] ) && ! empty( $attrs['tabIconBoxWidth'] ) ? 'width:' . $attrs['tabIconBoxWidth'] . 'px;' : '';

			$css .= '}';
			$css .= '}';
		}

		// mobile layout css
		if ( isset( $attrs['mobileItemGap'] )  || isset($attrs['mobileTextIndent']) || isset($attrs['mobileTextSize']) || isset($attrs['mobileIconBoxHeight']) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id . ' ul li {';
			$css .= isset( $attrs['mobileTextSize'] ) && ! empty( $attrs['mobileTextSize'] ) ? 'font-size:' . $attrs['mobileTextSize'] . 'px;' : '';
			$css .= isset( $attrs['mobileIconBoxHeight'] ) && ! empty( $attrs['mobileIconBoxHeight'] ) ? 'line-height:' . $attrs['mobileIconBoxHeight'] . 'px;' : '';
			$css .= isset( $attrs['mobileTextIndent'] ) && ! empty( $attrs['mobileTextIndent'] ) ? 'padding-left:' . $attrs['mobileTextIndent'] . 'px;' : '';
			$css .= isset( $attrs['mobileItemGap'] ) && ! empty( $attrs['mobileItemGap'] ) ? 'margin-bottom:' . $attrs['mobileItemGap'] . 'px;' : '';
			$css .= '}';
			$css .= '}';
		}

		if ( isset($attrs['mobileIconBoxHeight']) || isset($attrs['mobileIconBoxWidth']) || isset($attrs['mobileIconSize']) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id . ' ul li:before {';
			$css .= isset( $attrs['mobileIconSize'] ) && ! empty( $attrs['mobileIconSize'] ) ? 'font-size:' . $attrs['mobileIconSize'] . 'px;' : '';
			$css .= isset( $attrs['mobileIconBoxHeight'] ) && ! empty( $attrs['mobileIconBoxHeight'] ) ? 'height:' . $attrs['mobileIconBoxHeight'] . 'px;' : '';
			$css .= isset( $attrs['mobileIconBoxHeight'] ) && ! empty( $attrs['mobileIconBoxHeight'] ) ? 'line-height:' . $attrs['mobileIconBoxHeight'] . 'px;' : '';
			$css .= isset( $attrs['mobileIconBoxWidth'] ) && ! empty( $attrs['mobileIconBoxWidth'] ) ? 'width:' . $attrs['mobileIconBoxWidth'] . 'px;' : '';

			$css .= '}';
			$css .= '}';
		}
		
		return $css;
	}


	public function generate_comparison_slider_css($attrs, $unique_id) {
		$css = "";
		
		if(isset($attrs['imageLabelColor']) && strlen($attrs['imageLabelColor']) === 0) {
			$attrs['imageLabelColor'] = "#fff";
		}
		if(isset($attrs['imageLabelColor']) && $attrs['imageLabelColor'][2]==="r") {
			$attrs['imageLabelColor']=json_decode($attrs['imageLabelColor'], true);
			$css .=  $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-overlay .twentytwenty-before-label:before  {';
			$css .= 'color: rgba(' .$attrs['imageLabelColor']["r"].','. $attrs['imageLabelColor']["g"].','.$attrs['imageLabelColor']["b"].','.$attrs['imageLabelColor']["a"]. ');';
			$css .= 'font-family: sans-serif'; 
			$css .= '}';
			$css .=  $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-overlay .twentytwenty-after-label:before  {';
			$css .= 'color: rgba(' .$attrs['imageLabelColor']["r"].','. $attrs['imageLabelColor']["g"].','.$attrs['imageLabelColor']["b"].','.$attrs['imageLabelColor']["a"]. ');';
			$css .= 'font-family: sans-serif'; 
			$css .= '}'; 
		} else if(isset($attrs['imageLabelColor']) && $attrs['imageLabelColor'][2]!=="r") {
			$css .= $unique_id. '  .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-overlay .twentytwenty-before-label:before {';
			$css .= 'color: '.$attrs['imageLabelColor'].';';
			$css .= '}';
			$css .= $unique_id. '  .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-overlay .twentytwenty-after-label:before {';
			$css .= 'color: '.$attrs['imageLabelColor'].';';
			$css .= '}';
		}
		if(isset($attrs['imageLabelBackground']) && strlen($attrs['imageLabelBackground']) === 0) {
			$attrs['imageLabelBackground'] = "none";
		}
		if(isset($attrs['imageLabelBackground']) && $attrs['imageLabelBackground'][2]==="r") {
			$attrs['imageLabelBackground']=json_decode($attrs['imageLabelBackground'], true);
			$css .=  $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-overlay .twentytwenty-after-label:before  {';
			$css .= 'background: rgba(' .$attrs['imageLabelBackground']["r"].','. $attrs['imageLabelBackground']["g"].','.$attrs['imageLabelBackground']["b"].','.$attrs['imageLabelBackground']["a"]. ');';
			$css .= 'font-family: sans-serif'; 
			$css .= '}'; 
			$css .=  $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-overlay .twentytwenty-before-label:before  {';
			$css .= 'background: rgba(' .$attrs['imageLabelBackground']["r"].','. $attrs['imageLabelBackground']["g"].','.$attrs['imageLabelBackground']["b"].','.$attrs['imageLabelBackground']["a"]. ');';
			$css .= 'font-family: sans-serif'; 
			$css .= '}';
		} else if(isset($attrs['imageLabelBackground']) && $attrs['imageLabelBackground'][2]!=="r") {
			$css .=  $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-overlay .twentytwenty-after-label:before  {';
			$css .= 'background: ' .$attrs['imageLabelBackground']. ';';
			$css .= 'font-family: sans-serif'; 
			$css .= '}'; 
			$css .=  $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-overlay .twentytwenty-before-label:before  {';
			$css .= 'background: ' .$attrs['imageLabelBackground']. ';';
			$css .= 'font-family: sans-serif'; 
			$css .= '}';
		}
		if(isset($attrs['sliderBgColor']) && strlen($attrs['sliderBgColor']) === 0) {
			$attrs['sliderBgColor'] = "none";
		}
		if( isset( $attrs['sliderBgColor'] ) && $attrs['sliderBgColor'] !== "none" && $attrs['sliderBgColor'][2] === "r" ) {
			$attrs['sliderBgColor']=json_decode($attrs['sliderBgColor'], true);
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle {';
			$css .= 'background: rgba(' .$attrs['sliderBgColor']["r"].','. $attrs['sliderBgColor']["g"].','.$attrs['sliderBgColor']["b"].','.$attrs['sliderBgColor']["a"]. ');';
			$css .= '}';
		} else if(isset( $attrs['sliderBgColor'] ) && $attrs['sliderBgColor'][2]!=="r") {
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle {';
			$css .= 'background: '. $attrs['sliderBgColor'].';';
			$css .= '}';
		}
		if(isset($attrs['sliderBorderColor']) && strlen($attrs['sliderBorderColor']) === 0) {
			$attrs['sliderBorderColor'] = "#fff";
		}
		if(isset( $attrs['sliderBorderColor'] ) && $attrs['sliderBorderColor'][2]==="r" ) {
			$attrs['sliderBorderColor']=json_decode($attrs['sliderBorderColor'], true);
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle {';
			$css .= 'border-color: rgba(' .$attrs['sliderBorderColor']["r"].','. $attrs['sliderBorderColor']["g"].','.$attrs['sliderBorderColor']["b"].','.$attrs['sliderBorderColor']["a"]. ');';
			$css .= '}';
		} else if(isset( $attrs['sliderBorderColor'] ) && $attrs['sliderBorderColor'][2]!=="r") {
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle {';
			$css .= 'border-color: '. $attrs['sliderBorderColor'].';';
			$css .= '}';
		}
		if(isset($attrs['twoSliderColor']) && strlen($attrs['twoSliderColor']) === 0) {
			$attrs['twoSliderColor'] = "#111";
		}
		if( isset( $attrs['twoSliderColor']) && $attrs['twoSliderColor'][2]==="r") {
			$attrs['twoSliderColor']=json_decode($attrs['twoSliderColor'], true);
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle .twentytwenty-left-arrow {';
			$css .= 'border-right-color: rgba(' .$attrs['twoSliderColor']["r"].','. $attrs['twoSliderColor']["g"].','.$attrs['twoSliderColor']["b"].','.$attrs['twoSliderColor']["a"]. ');';
			$css .= '}';

			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle .twentytwenty-right-arrow {';
			$css .= 'border-left-color: rgba(' .$attrs['twoSliderColor']["r"].','. $attrs['twoSliderColor']["g"].','.$attrs['twoSliderColor']["b"].','.$attrs['twoSliderColor']["a"]. ');';
			$css .= '}';
		} else if(isset( $attrs['twoSliderColor']) && $attrs['twoSliderColor'][2] !== "r") {
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle .twentytwenty-left-arrow {';
			$css .= 'border-right-color: ' .$attrs['twoSliderColor'] .';';
			$css .= '}';

			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle .twentytwenty-right-arrow {';
			$css .= 'border-left-color: ' .$attrs['twoSliderColor']. ';';
			$css .= '}';
		}
		if( !isset( $attrs['showSliderLine'] ) ) {
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle:after {';
			$css .= 'display: none;';
			$css .= '}';
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle:before {';
			$css .= 'display: none;';
			$css .= '}';
		}
		if(!isset($attrs['sliderLineColor'])) {
			$attrs['sliderLineColor'] = "#ea6bab";
		}
		if(strlen($attrs['sliderLineColor']) === 0) {
			$attrs['sliderLineColor'] = "#111";
		}
		if( isset( $attrs['sliderLineColor']) && isset( $attrs['showSliderLine'] ) && $attrs['sliderLineColor'][2] === "r" ) {
			$attrs['sliderLineColor']=json_decode($attrs['sliderLineColor'], true);
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle:after {';
			$css .= 'background: rgba(' .$attrs['sliderLineColor']["r"].','. $attrs['sliderLineColor']["g"].','.$attrs['sliderLineColor']["b"].','.$attrs['sliderLineColor']["a"]. ');';
			$css .= '}';
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle:before {';
			$css .= 'background: rgba(' .$attrs['sliderLineColor']["r"].','. $attrs['sliderLineColor']["g"].','.$attrs['sliderLineColor']["b"].','.$attrs['sliderLineColor']["a"]. ');';
			$css .= '}';
		} else if(isset( $attrs['sliderLineColor']) && isset( $attrs['showSliderLine'] ) && $attrs['sliderLineColor'][2] !== "r") {
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle:after {';
			$css .= 'background: ' .$attrs['sliderLineColor']. ';';
			$css .= '}';
			$css .= $unique_id . ' .img_area .twentytwenty-wrapper .enhanced_image_comparison_container .twentytwenty-handle:before {';
			$css .= 'background: ' .$attrs['sliderLineColor']. ';';
			$css .= '}';
		}
		return $css;
	}

	// call to action
	public function generate_call_to_action_css($attrs, $unique_id) {
		$css = "";
		//background
		if(!isset($attrs['bgTypes'])) {
			$attrs['bgTypes'] = "classic";
		}
		if(!isset($attrs['classicPosition'])) {
			$attrs['classicPosition'] = "center center";
		}
		if(!isset($attrs['classicAttachment'])) {
			$attrs['classicAttachment'] = "scroll";
		}
		if(!isset($attrs['classicRepeat'])) {
			$attrs['classicRepeat'] = "no-repeat";
		}
		if(!isset($attrs['classicSize'])) {
			$attrs['classicSize'] = "cover";
		}
		if(isset($attrs['bgColor']) && $attrs['bgTypes'] === "classic") {
			$css .= $unique_id. ' {';
			$css .= $this->getColor($attrs['bgColor']) ? 'background-color: '.$this->getColor($attrs['bgColor']).';' : '';
			$css .= '}';
		}
		if(isset($attrs['highlightColor'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= $this->getColor($attrs['highlightColor']) ? 'color: '.$this->getColor($attrs['highlightColor']).';' : '';
			$css .= '}';
		}
		if(isset($attrs['highlightBgColor'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= $this->getColor($attrs['highlightBgColor']) ? 'background-color: '.$this->getColor($attrs['highlightBgColor']).';' : '';
			$css .= '}';
		}
		if(isset($attrs['deskHighlightFontSize'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= 'font-size: '.$attrs['deskHighlightFontSize'].'px;';
			$css .= '}';
		}
		if(isset($attrs['deskHighlightLineHeight'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= 'line-height: '.$attrs['deskHighlightLineHeight'].'px;';
			$css .= '}';
		}
		if(isset($attrs['highlitedLetterSpacing'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= 'letter-spacing: '.$attrs['highlitedLetterSpacing'].'px;';
			$css .= '}';
		}
		if(isset($attrs['highlightBorderWidth'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= 'border-width: '.$attrs['highlightBorderWidth'].'px;';
			$css .= '}';
		}
		if(isset($attrs['highlightBorder'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= 'border-style: '.$attrs['highlightBorder'].';';
			$css .= '}';
		}
		if(isset($attrs['highlightBorderColor'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= $this->getColor($attrs['highlightBorderColor']) ? 'border-color: '.$this->getColor($attrs['highlightBorderColor']).';' : '';
			$css .= '}';
		}
		if(isset($attrs['tabHightlightFontSize'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= 'font-size: '.$attrs['tabHightlightFontSize'].'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['mobileHightlightFontSize'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= 'font-size: '.$attrs['mobileHightlightFontSize'].'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['tabHighlightLineHeight'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= 'line-height: '.$attrs['tabHighlightLineHeight'].'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['mobileHighlightLineHeight'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= 'line-height: '.$attrs['mobileHighlightLineHeight'].'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['highlightTypography'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= 'font-family: ' .$attrs['highlightTypography']. ';';
			$css .= 'font-weight: ' .$attrs['highlightFontWeight']. ';';
			$css .= '}';
		}
		if(isset($attrs['highlightFontStyle'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title mark {';
			$css .= 'font-style: '. $attrs['highlightFontStyle']. ';';
			$css .= '}';
		}
		if(isset($attrs['bgImageUrl']) && $attrs['bgTypes'] === "classic"){
			if($attrs['bgTypes'] !== "gradients") {
				$css .= $unique_id. '{';
				$css .= 'background-image: url('.$attrs['bgImageUrl'].');';
				$css .= '}';
			}
			if(isset($attrs['classicPosition'])) {
				$css .= $unique_id. '{';
				$css .= 'background-position: '.$attrs['classicPosition'].';';
				$css .= '}';
			}
			if(isset($attrs['classicAttachment'])) {
				$css .= $unique_id. '{';
				$css .= 'background-attachment: '.$attrs['classicAttachment'].';';
				$css .= '}';
			}
			if(isset($attrs['classicRepeat'])) {
				$css .= $unique_id. '{';
				$css .= 'background-repeat: '.$attrs['classicRepeat'].';';
				$css .= '}';
			}
			if(isset($attrs['classicSize'])) {
				$css .= $unique_id. '{';
				$css .= 'background-size: '.$attrs['classicSize'].';';
				$css .= '}';
			}
		}
		if(!isset($attrs['gradientType'])) {
			$attrs['gradientType'] = "linear";
		}
		if(!isset($attrs['gradientSecondLocation'])) {
			$attrs['gradientSecondLocation'] = 100;
		}
		if(!isset($attrs['gradientLocation'])) {
			$attrs['gradientLocation'] = 1;
		}
		if(!isset($attrs['gradientAngle'])) {
			$attrs['gradientAngle'] = 180;
		}
		if(!isset($attrs['gradientPosition'])) {
			$attrs['gradientPosition'] = "center center";
		}
		if(!isset($attrs['gradientSecondColor'])) {
			$attrs['gradientSecondColor'] = "#4D16AB";
		}
		if($attrs['bgTypes'] === "gradients" && isset($attrs['gradientColor'])) {
			if($attrs['gradientType'] === "linear") {
				if($attrs['gradientColor'][2]==="r") {
					$attrs['gradientColor']=json_decode($attrs['gradientColor'], true);
					$css .= $unique_id. ' {';
					$css .= 'background-image: linear-gradient(' .$attrs['gradientAngle'].'deg, rgba('. $attrs['gradientColor']["r"].','.$attrs['gradientColor']["g"].','.$attrs['gradientColor']["b"].','.$attrs['gradientColor']["a"]. ') '.$attrs['gradientLocation'].'%, '.$this->getColor($attrs['gradientSecondColor']).' '.$attrs['gradientSecondLocation'].'%);';
					$css .= '}';
				} else {
					$css .= $unique_id. ' {';
					$css .= 'background-image: linear-gradient('.$attrs['gradientAngle'].'deg, '.$attrs['gradientColor'].' '.$attrs['gradientLocation'].'%, '.$attrs['gradientSecondColor'].' '.$attrs['gradientSecondLocation'].'%);';
					$css .= '}';
				}
			}
			if($attrs['gradientType'] === "radial") {
				if($attrs['gradientColor'][2]==="r") {
					$attrs['gradientColor']=json_decode($attrs['gradientColor'], true);
					$css .= $unique_id. ' {';
					$css .= 'background-image: radial-gradient(at ' .$attrs['gradientPosition'].', rgba('. $attrs['gradientColor']["r"].','.$attrs['gradientColor']["g"].','.$attrs['gradientColor']["b"].','.$attrs['gradientColor']["a"]. ') '.$attrs['gradientLocation'].'%, '.$this->getColor($attrs['gradientSecondColor']).' '.$attrs['gradientSecondLocation'].'%);';
					$css .= '}';
				} else {
					$css .= $unique_id. '{';
					$css .= 'background-image: radial-gradient(at '.$attrs['gradientPosition'].', '.$attrs['gradientColor'].' '.$attrs['gradientLocation'].'%, '.$this->getColor($attrs['gradientSecondColor']).' '.$attrs['gradientSecondLocation'].'%);';
					$css .= '}';
				}
			}
		}
		// overlay
		if(!isset($attrs['overlayBgTypes'])) {
			$attrs['overlayBgTypes'] = "overlay-classic";
		}
		if(isset($attrs['backgroundOverlayNormalColor']) && $attrs['overlayBgTypes'] === "overlay-classic") {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-overlay-wrap {';
			$css .= $this->getColor($attrs['backgroundOverlayNormalColor']) ? 'background-color: '.$this->getColor($attrs['backgroundOverlayNormalColor']).' !important;' : '';
			$css .= '}';
		}
		if(!isset($attrs['backgroundOverlayNormalPosition'])) {
			$attrs['backgroundOverlayNormalPosition'] = "center center";
		}
		if(!isset($attrs['backgroundOverlayNormalAttachment'])) {
			$attrs['backgroundOverlayNormalAttachment'] = "scroll";
		}
		if(!isset($attrs['backgroundOverlayNormalRepeat'])) {
			$attrs['backgroundOverlayNormalRepeat'] = "no-repeat";
		}
		if(!isset($attrs['backgroundOverlayNormalSize'])) {
			$attrs['backgroundOverlayNormalSize'] = "cover";
		}
		if(!isset($attrs['backgroundOverlayNormalOpacity'])) {
			if(isset($attrs['backgroundOverlayNormalColor']) || isset($attrs['backgroundOverlayNormalImage']) || isset($attrs['backgroundOverlayGradientColor'])) {
				$attrs['backgroundOverlayNormalOpacity'] = 0.5;
			} else {
				$attrs['backgroundOverlayNormalOpacity'] = 0;
			}
		}
		if(isset($attrs['backgroundOverlayNormalOpacity'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-overlay-wrap  {';
			$css .= 'opacity: '.$attrs['backgroundOverlayNormalOpacity'].' !important;';
			$css .= '}';
		}
		if(isset($attrs['backgroundOverlayNormalImage'])){
			if($attrs['overlayBgTypes'] !== "overlay-gradients") {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-overlay-wrap  {';
				$css .= 'background-image: url('.$attrs['backgroundOverlayNormalImage'].');';
				$css .= '}';
			}
			if(isset($attrs['backgroundOverlayNormalPosition'])) {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-overlay-wrap  {';
				$css .= 'background-position: '.$attrs['backgroundOverlayNormalPosition'].';';
				$css .= '}';
			}
			if(isset($attrs['backgroundOverlayNormalAttachment'])) {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-overlay-wrap  {';
				$css .= 'background-attachment: '.$attrs['backgroundOverlayNormalAttachment'].';';
				$css .= '}';
			}
			if(isset($attrs['backgroundOverlayNormalRepeat'])) {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-overlay-wrap  {';
				$css .= 'background-repeat: '.$attrs['backgroundOverlayNormalRepeat'].';';
				$css .= '}';
			}
			if(isset($attrs['backgroundOverlayNormalSize'])) {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-overlay-wrap  {';
				$css .= 'background-size: '.$attrs['backgroundOverlayNormalSize'].';';
				$css .= '}';
			}
		}
		if(!isset($attrs['backgroundOverlayGradientType'])) {
			$attrs['backgroundOverlayGradientType'] = "linear";
		}
		if(!isset($attrs['backgroundOverlayGradientSecondLocation'])) {
			$attrs['backgroundOverlayGradientSecondLocation'] = 100;
		}
		if(!isset($attrs['backgroundOverlayGradientLocation'])) {
			$attrs['backgroundOverlayGradientLocation'] = 1;
		}
		if(!isset($attrs['backgroundOverlayGradientAngle'])) {
			$attrs['backgroundOverlayGradientAngle'] = 180;
		}
		if(!isset($attrs['backgroundOverlayGradientPosition'])) {
			$attrs['backgroundOverlayGradientPosition'] = "center center";
		}
		if(!isset($attrs['backgroundOverlayGradientSecondColor'])) {
			$attrs['backgroundOverlayGradientSecondColor'] = "#F2285B";
		}
		if($attrs['overlayBgTypes'] === "overlay-gradients" && isset($attrs['backgroundOverlayGradientColor']) && isset($attrs['backgroundOverlayGradientSecondColor'])) {
			if(isset($attrs['backgroundOverlayNormalOpacity'])) {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-overlay-wrap  {';
				$css .= 'opacity: '.$attrs['backgroundOverlayNormalOpacity'].';';
				$css .= '}';
			}
			if($attrs['backgroundOverlayGradientType'] === "linear") {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-overlay-wrap  {';
				$css .= 'background-image: linear-gradient('.$attrs['backgroundOverlayGradientAngle'].'deg, '.$this->getColor($attrs['backgroundOverlayGradientColor']).' '.$attrs['backgroundOverlayGradientLocation'].'%, '.$this->getColor($attrs['backgroundOverlayGradientSecondColor']).' '.$attrs['backgroundOverlayGradientSecondLocation'].'%);';
				$css .= '}';
			}
			if($attrs['backgroundOverlayGradientType'] === "radial") {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-overlay-wrap  {';
				$css .= 'background-image: radial-gradient(at '.$attrs['backgroundOverlayGradientPosition'].', '.$this->getColor($attrs['backgroundOverlayGradientColor']).' '.$attrs['backgroundOverlayGradientLocation'].'%, '.$this->getColor($attrs['backgroundOverlayGradientSecondColor']).' '.$attrs['backgroundOverlayGradientSecondLocation'].'%);';
				$css .= '}';
			}
		}
		
		if(isset( $attrs['ctaTitle'] ) && isset($attrs['ctaTitleColor'])) {
			if($attrs['ctaTitleColor'][2]==="r") {
				$attrs['ctaTitleColor']=json_decode($attrs['ctaTitleColor'], true);
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title {';
				$css .= 'color: rgba(' .$attrs['ctaTitleColor']["r"].','. $attrs['ctaTitleColor']["g"].','.$attrs['ctaTitleColor']["b"].','.$attrs['ctaTitleColor']["a"]. ');';
				$css .= '}';
			} else {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title {';
				$css .= 'color: ' .$attrs['ctaTitleColor']. ';';
				$css .= '}';
			}
		}
		if(isset( $attrs['ctaTitle'] ) && isset($attrs['titleTypography'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title {';
			$css .= 'font-family: ' .$attrs['titleTypography']. ';';
			$css .= 'font-weight: ' .$attrs['titleFontWeight']. ';';
			$css .= '}';
		}
		if(isset( $attrs['ctaTitle'] ) && isset($attrs['titleFontStyle'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title {';
			$css .= 'font-style: '. $attrs['titleFontStyle']. ';';
			$css .= '}';
		}
		if(!isset($attrs['ctaTitleFontSizeDesktop'])) {
			$attrs['ctaTitleFontSizeDesktop'] = 40;
		}
		if(isset($attrs['ctaTitleFontSizeDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title {';
			$css .= 'font-size: '. $attrs['ctaTitleFontSizeDesktop']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['ctaTextColor'])) {
			if($attrs['ctaTextColor'][2]==="r") {
				$attrs['ctaTextColor']=json_decode($attrs['ctaTextColor'], true);
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-text-wrap .enhanced-cta-text {';
				$css .= 'color: rgba(' .$attrs['ctaTextColor']["r"].','. $attrs['ctaTextColor']["g"].','.$attrs['ctaTextColor']["b"].','.$attrs['ctaTextColor']["a"]. ');';
				$css .= '}';
			} else {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-text-wrap .enhanced-cta-text {';
				$css .= 'color: '. $attrs['ctaTextColor']. ';';
				$css .= '}';
			}
		}
		if(!isset($attrs['ctaTextFontSizeDesktop'])) {
			$attrs['ctaTextFontSizeDesktop'] = 23;
		}
		if(isset($attrs['ctaTextFontSizeDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-text-wrap .enhanced-cta-text {';
			$css .= 'font-size: '. $attrs['ctaTextFontSizeDesktop']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['ctaTitlePaddingTop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title {';
			$css .= 'padding-top: '. $attrs['ctaTitlePaddingTop'] .'px;';
			$css .= '}';
		}
		if(isset( $attrs['ctaText'] ) && isset($attrs['textTypography'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-text-wrap .enhanced-cta-text {';
			$css .= 'font-family: ' .$attrs['textTypography']. ';';
			$css .= 'font-weight: ' .$attrs['textFontWeight']. ';';
			$css .= '}';
		}
		if(isset( $attrs['ctaText'] ) && isset($attrs['textFontStyle'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-text-wrap .enhanced-cta-text {';
			$css .= 'font-style: '. $attrs['textFontStyle']. ';';
			$css .= '}';
		}
		if(isset( $attrs['firstButtonLabel'] ) && isset($attrs['firstBtnTypography'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn .cta-button-content {';
			$css .= 'font-family: ' .$attrs['firstBtnTypography']. ';';
			$css .= 'font-weight: ' .$attrs['firstBtnFontWeight']. ';';
			$css .= '}';
		}
		if(isset( $attrs['firstButtonLabel'] ) && isset($attrs['firstBtnFontStyle'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn .cta-button-content {';
			$css .= 'font-style: '. $attrs['firstBtnFontStyle']. ';';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingTopDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-top: '. $attrs['firstBtnPaddingTopDesktop']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingRightDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-right: '. $attrs['firstBtnPaddingRightDesktop']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingBottomDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-bottom: '. $attrs['firstBtnPaddingBottomDesktop']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingLeftDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-left: '. $attrs['firstBtnPaddingLeftDesktop']. 'px;';
			$css .= '}';
		}
		if(!isset($attrs['firstBtnIconSpacing'])) {
			$attrs['firstBtnIconSpacing'] = 12;
		}
		if(!isset($attrs['firstBtnIconAlignment'])) {
			$attrs['firstBtnIconAlignment'] = "left";
		}
		if(isset($attrs['firstBtnIconAlignment']) && isset($attrs['firstBtnIconSpacing'])) {
			if($attrs['firstBtnIconAlignment'] === "right") {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn i {';
				$css .= 'padding-left: '. $attrs['firstBtnIconSpacing']. 'px;';
				$css .= '}';
			}
			if($attrs['firstBtnIconAlignment'] === "left") {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn i {';
				$css .= 'padding-right: '. $attrs['firstBtnIconSpacing']. 'px;';
				$css .= '}';
			}
		}
		if(isset($attrs['ctaFirstBtnSizeDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'font-size: '. $attrs['ctaFirstBtnSizeDesktop']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['ctaSecondBtnSizeDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'font-size: '. $attrs['ctaSecondBtnSizeDesktop']. 'px;';
			$css .= '}';
		}
		if(isset( $attrs['secondButtonLabel'] ) && isset($attrs['secondBtnTypography'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn .cta-button-content {';
			$css .= 'font-family: ' .$attrs['secondBtnTypography']. ';';
			$css .= 'font-weight: ' .$attrs['secondBtnFontWeight']. ';';
			$css .= '}';
		}
		if(isset( $attrs['secondButtonLabel'] ) && isset($attrs['secondBtnFontStyle'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn .cta-button-content {';
			$css .= 'font-style: '. $attrs['secondBtnFontStyle']. ';';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingTopDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-top: '. $attrs['secondBtnPaddingTopDesktop']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingRightDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-right: '. $attrs['secondBtnPaddingRightDesktop']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingBottomDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-bottom: '. $attrs['secondBtnPaddingBottomDesktop']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingLeftDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-left: '. $attrs['secondBtnPaddingLeftDesktop']. 'px;';
			$css .= '}';
		}
		if(!isset($attrs['secondBtnIconSpacing'])) {
			$attrs['secondBtnIconSpacing'] = 12;
		}
		if(!isset($attrs['secondBtnIconAlignment'])) {
			$attrs['secondBtnIconAlignment'] = "left";
		}
		if(isset($attrs['secondBtnIconAlignment']) && isset($attrs['secondBtnIconSpacing'])) {
			if($attrs['secondBtnIconAlignment'] === "right") {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn i {';
				$css .= 'padding-left: '. $attrs['secondBtnIconSpacing']. 'px;';
				$css .= '}';
			}
			if($attrs['secondBtnIconAlignment'] === "left") {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn i {';
				$css .= 'padding-right: '. $attrs['secondBtnIconSpacing']. 'px;';
				$css .= '}';
			}
		}
		if(isset( $attrs['additionalText'] ) && isset($attrs['additionalTxtTypography'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-additional-text-wrap .cta-additional-text {';
			$css .= 'font-family: ' .$attrs['additionalTxtTypography']. ';';
			$css .= 'font-weight: ' .$attrs['additionalTxtFontWeight']. ';';
			$css .= '}';
		}
		if(isset( $attrs['additionalText'] ) && isset($attrs['additionalTxtFontStyle'])) {
			$css .= $unique_id. '.enhanced-cta .enhanced-cta-additional-text-wrap .cta-additional-text {';
			$css .= 'font-style: '. $attrs['additionalTxtFontStyle']. ';';
			$css .= '}';
		}
		if(isset($attrs['btnAlignment'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title, .enhanced-cta-text-wrap .enhanced-cta-text {';
			$css .= 'text-align: '. $attrs['btnAlignment']. ';';
			$css .= '}';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-text-wrap .enhanced-cta-text {';
			$css .= 'text-align: '. $attrs['btnAlignment']. ';';
			$css .= '}';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-additional-text-wrap .cta-additional-text {';
			$css .= 'text-align: '. $attrs['btnAlignment']. ';';
			$css .= '}';
			if($attrs['btnAlignment'] === "center") {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap {';
				$css .= 'justify-content: '. $attrs['btnAlignment']. ';';
				$css .= '}';
			}
			if($attrs['btnAlignment'] === "right") {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap {';
				$css .= 'justify-content: flex-end;';
				$css .= '}';
			}
			if($attrs['btnAlignment'] === "left") {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap {';
				$css .= 'justify-content: flex-start;';
				$css .= '}';
			}
		}
		if(isset($attrs['buttonGapDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn {';
			$css .= 'margin-right: '. $attrs['buttonGapDesktop']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['firstBtnBorderRadius'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'border-radius: '. $attrs['firstBtnBorderRadius']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['firstBtnBorderWidth'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'border-style: solid;';
			$css .= 'border-width: '. $attrs['firstBtnBorderWidth']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['firstBtnBorderColor'])) {
			if($attrs['firstBtnBorderColor'][2]==="r") {
				$attrs['firstBtnBorderColor']=json_decode($attrs['firstBtnBorderColor'], true);
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
				$css .= 'border-color: rgba(' .$attrs['firstBtnBorderColor']["r"].','. $attrs['firstBtnBorderColor']["g"].','.$attrs['firstBtnBorderColor']["b"].','.$attrs['firstBtnBorderColor']["a"]. ');';
				$css .= '}';
			} else {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
				$css .= 'border-color: '. $attrs['firstBtnBorderColor']. ';';
				$css .= '}';
			}
		}
		if(isset($attrs['firstBtnBgColor'])) {
			if($attrs['firstBtnBgColor'][2]==="r") {
				$attrs['firstBtnBgColor']=json_decode($attrs['firstBtnBgColor'], true);
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
				$css .= 'background: rgba(' .$attrs['firstBtnBgColor']["r"].','. $attrs['firstBtnBgColor']["g"].','.$attrs['firstBtnBgColor']["b"].','.$attrs['firstBtnBgColor']["a"]. ');';
				$css .= '}';
			} else {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
				$css .= 'background-color: '. $attrs['firstBtnBgColor']. ';';
				$css .= '}';
			}
		}
		if(isset($attrs['firstBtnIconColor'])) {
			if($attrs['firstBtnIconColor'][2]==="r") {
				$attrs['firstBtnIconColor']=json_decode($attrs['firstBtnIconColor'], true);
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn i {';
				$css .= 'color: rgba(' .$attrs['firstBtnIconColor']["r"].','. $attrs['firstBtnIconColor']["g"].','.$attrs['firstBtnIconColor']["b"].','.$attrs['firstBtnIconColor']["a"]. ');';
				$css .= '}';
			} else {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn i {';
				$css .= 'color: '. $attrs['firstBtnIconColor']. ';';
				$css .= '}';
			}
		}
		if(isset($attrs['firstBtnIconAlignment'])) {
			if($attrs['firstBtnIconAlignment'] === "right")  {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn i {';
				$css .= 'order: 1;';
				$css .= '}';
			}
		}
		if(isset($attrs['firstBtnColor'])) {
			if($attrs['firstBtnColor'][2]==="r") {
				$attrs['firstBtnColor']=json_decode($attrs['firstBtnColor'], true);
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn .cta-button-content {';
				$css .= 'color: rgba(' .$attrs['firstBtnColor']["r"].','. $attrs['firstBtnColor']["g"].','.$attrs['firstBtnColor']["b"].','.$attrs['firstBtnColor']["a"]. ');';
				$css .= '}';
			} else {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn .cta-button-content {';
				$css .= 'color: '. $this->getColor($attrs['firstBtnColor']).';';
				$css .= '}';
			}
		}
		if(isset($attrs['secondBtnBorderRadius'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'border-radius: '. $attrs['secondBtnBorderRadius']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['secondBtnBgColor'])) {
			if($attrs['secondBtnBgColor'][2]==="r") {
				$attrs['secondBtnBgColor']=json_decode($attrs['secondBtnBgColor'], true);
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
				$css .= 'background-color: rgba(' .$attrs['secondBtnBgColor']["r"].','. $attrs['secondBtnBgColor']["g"].','.$attrs['secondBtnBgColor']["b"].','.$attrs['secondBtnBgColor']["a"]. ');';
				$css .= '}';
			} else {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
				$css .= 'background-color: '. $attrs['secondBtnBgColor']. ';';
				$css .= '}';
			}
		}
		if(isset($attrs['secondBtnIconColor'])) {
			if($attrs['secondBtnIconColor'][2]==="r") {
				$attrs['secondBtnIconColor']=json_decode($attrs['secondBtnIconColor'], true);
				$css .= $unique_id. '  .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn i {';
				$css .= 'color: rgba(' .$attrs['secondBtnIconColor']["r"].','. $attrs['secondBtnIconColor']["g"].','.$attrs['secondBtnIconColor']["b"].','.$attrs['secondBtnIconColor']["a"]. ');';
				$css .= '}';
			} else {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn i {';
				$css .= 'color: '. $attrs['secondBtnIconColor']. ';';
				$css .= '}';
			}
		}
		if(isset($attrs['secondBtnIconAlignment'])) {
			if($attrs['secondBtnIconAlignment'] === "right")  {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn i {';
				$css .= 'order: 1;';
				$css .= '}';
			}
		}
		if(isset($attrs['secondBtnColor'])) {
			if($attrs['secondBtnColor'][2]==="r") {
				$attrs['secondBtnColor']=json_decode($attrs['secondBtnColor'], true);
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn .cta-button-content {';
				$css .= 'color: rgba(' .$attrs['secondBtnColor']["r"].','. $attrs['secondBtnColor']["g"].','.$attrs['secondBtnColor']["b"].','.$attrs['secondBtnColor']["a"]. ');';
				$css .= '}';
			} else {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn .cta-button-content {';
				$css .= 'color: '. $attrs['secondBtnColor'] .';';
				$css .= '}';
			}
		}
		if(!isset($attrs['additionalTextSizeDesktop'])) {
			$attrs['additionalTextSizeDesktop'] = 20;
		}
		if(isset($attrs['additionalTextSizeDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-additional-text-wrap .cta-additional-text {';
			$css .= 'font-size: '. $attrs['additionalTextSizeDesktop'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['additionalTextColor'])) {
			if($attrs['additionalTextColor'][2]==="r") {
				$attrs['additionalTextColor']=json_decode($attrs['additionalTextColor'], true);
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-additional-text-wrap .cta-additional-text {';
				$css .= 'color: rgba(' .$attrs['additionalTextColor']["r"].','. $attrs['additionalTextColor']["g"].','.$attrs['additionalTextColor']["b"].','.$attrs['additionalTextColor']["a"]. ');';
				$css .= '}';
			} else {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-additional-text-wrap .cta-additional-text {';
				$css .= 'color: '. $attrs['additionalTextColor'] .';';
				$css .= '}';
			}
		}
		if(isset($attrs['secondBtnTopPadding'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-top: '. $attrs['secondBtnTopPadding'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['secondBtnLeftPadding'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-left: '. $attrs['secondBtnLeftPadding'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['secondBtnRightPadding'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-right: '. $attrs['secondBtnRightPadding'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['secondBtnBorderWidth'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'border-style: solid;';
			$css .= 'border-width: '. $attrs['secondBtnBorderWidth']. 'px;';
			$css .= '}';
		}
		if(isset($attrs['secondBtnBorderColor'])) {
			if($attrs['secondBtnBorderColor'][2]==="r") {
				$attrs['secondBtnBorderColor']=json_decode($attrs['secondBtnBorderColor'], true);
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
				$css .= 'border-color: rgba(' .$attrs['secondBtnBorderColor']["r"].','. $attrs['secondBtnBorderColor']["g"].','.$attrs['secondBtnBorderColor']["b"].','.$attrs['secondBtnBorderColor']["a"]. ');';
				$css .= '}';
			} else {
				$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
				$css .= 'border-color: '. $attrs['secondBtnBorderColor']. ';';
				$css .= '}';
			}
		}
		if(isset($attrs['secondBtnBottomPadding'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-bottom: '. $attrs['secondBtnBottomPadding'] .'px;';
			$css .= '}';
			}
		if(isset($attrs['firstBtnTopPadding'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-top: '. $attrs['firstBtnTopPadding'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['firstBtnLeftPadding'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-left: '. $attrs['firstBtnLeftPadding'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['firstBtnRightPadding'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-right: '. $attrs['firstBtnRightPadding'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['firstBtnBottomPadding'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-bottom: '. $attrs['firstBtnBottomPadding'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['btnSpaceBottomDesktop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'margin-bottom: '. $attrs['btnSpaceBottomDesktop'] .'px;';
			$css .= '}';

			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'margin-bottom: '. $attrs['btnSpaceBottomDesktop'] .'px;';
			$css .= '}';
		}
		if(!isset($attrs['ctaTitleMarginBottom'])) {
			$attrs['ctaTitleMarginBottom'] = 0;
		}
		if(isset($attrs['ctaTitleMarginBottom'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title {';
			$css .= 'margin-bottom: '. $attrs['ctaTitleMarginBottom'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['ctaTextMarginBottom'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-text-wrap .enhanced-cta-text {';
			$css .= 'margin-bottom: '. $attrs['ctaTextMarginBottom'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['additionalTextSpaceTop'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-additional-text-wrap {';
			$css .= 'padding-top: '. $attrs['additionalTextSpaceTop'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['additionalTextSpaceBottom'])) {
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-additional-text-wrap {';
			$css .= 'padding-bottom: '. $attrs['additionalTextSpaceBottom'] .'px;';
			$css .= '}';
		}
		if(isset($attrs['ctaTitleFontSizeTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title {';
			$css .= 'font-size: '. $attrs['ctaTitleFontSizeTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['ctaTitleFontSizeMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-title-wrap .enhanced-cta-title {';
			$css .= 'font-size: '. $attrs['ctaTitleFontSizeMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['ctaTextFontSizeTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-text-wrap .enhanced-cta-text {';
			$css .= 'font-size: '. $attrs['ctaTextFontSizeTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['ctaTextFontSizeMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-text-wrap .enhanced-cta-text {';
			$css .= 'font-size: '. $attrs['ctaTextFontSizeMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['ctaFirstBtnSizeTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'font-size: '. $attrs['ctaFirstBtnSizeTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['ctaFirstBtnSizeMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'font-size: '. $attrs['ctaFirstBtnSizeMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['ctaSecondBtnSizeTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'font-size: '. $attrs['ctaSecondBtnSizeTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['ctaSecondBtnSizeMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'font-size: '. $attrs['ctaSecondBtnSizeMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['additionalTextSizeTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-additional-text-wrap .cta-additional-text {';
			$css .= 'font-size: '. $attrs['additionalTextSizeTab'] .'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['additionalTextSizeMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-additional-text-wrap .cta-additional-text {';
			$css .= 'font-size: '. $attrs['additionalTextSizeMobile'] .'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['buttonGapTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn {';
			$css .= 'margin-right: '. $attrs['buttonGapTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['buttonGapMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn {';
			$css .= 'margin-right: '. $attrs['buttonGapMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingTopTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-top: '. $attrs['firstBtnPaddingTopTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingRightTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-right: '. $attrs['firstBtnPaddingRightTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingBottomTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-bottom: '. $attrs['firstBtnPaddingBottomTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingLeftTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-left: '. $attrs['firstBtnPaddingLeftTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingTopMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-top: '. $attrs['firstBtnPaddingTopMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingRightMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-right: '. $attrs['firstBtnPaddingRightMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingBottomMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-bottom: '. $attrs['firstBtnPaddingBottomMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['firstBtnPaddingLeftMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-first-btn .cta-btn {';
			$css .= 'padding-left: '. $attrs['firstBtnPaddingLeftMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingTopTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-top: '. $attrs['secondBtnPaddingTopTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingRightTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-right: '. $attrs['secondBtnPaddingRightTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingBottomTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-bottom: '. $attrs['secondBtnPaddingBottomTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingLeftTab'])) {
			$css .= '@media (max-width: 1024px) and (min-width: 768px){';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-left: '. $attrs['secondBtnPaddingLeftTab']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingTopMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-top: '. $attrs['secondBtnPaddingTopMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingRightMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-right: '. $attrs['secondBtnPaddingRightMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingBottomMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-bottom: '. $attrs['secondBtnPaddingBottomMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		if(isset($attrs['secondBtnPaddingLeftMobile'])) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id. ' .enhanced-cta .enhanced-cta-btn-wrap .cta-second-btn .cta-btn {';
			$css .= 'padding-left: '. $attrs['secondBtnPaddingLeftMobile']. 'px;';
			$css .= '}';
			$css .= '}';
		}
		return $css;
	}

	// block-icon css
	public function generate_block_icon_css($attrs, $unique_id) {
		$layoutType = isset($attrs['layoutType']) ? $attrs['layoutType'] : 'horizontal';
		$css = "";
		if (is_array( $attrs['icons'] ) && !empty($attrs['icons'])) {
			foreach( $attrs['icons'] as $key => $value ){
				$gradientAngle = isset($value['gradientAngle']) ? ($value['gradientAngle']."deg ") : ""; 
				$gradientLocation = isset($value['gradientLocation']) ? ($value['gradientLocation']."%") : "";
				$gradientSecondColor = isset($value['gradientSecondColor']) ? ($value['gradientSecondColor']) : "#4c10cd";
				$gradientSecondLocation = isset($value['gradientSecondLocation']) ? ($value['gradientSecondLocation']."%") : "";
				$gradientFirstColor = isset($value['gradientColor']) ? $value['gradientColor']: '';
				$css .=  $unique_id . ' .enhanced-icons .enhanced-icon-item-'.$key.' {';
				$css .= ($value['view'] === "stacked" && isset($value['iconWidthDesktop'])) ? "width: " .$value['iconWidthDesktop'] . "px;" : "";
				$css .= ($value['view'] === "stacked" && isset($value['iconHeightDesktop'])) ? "height: " .$value['iconHeightDesktop'] . "px;" : "";
				$css .= $value['view'] === "stacked" ? "text-align: center;" : "";
				$css .= ( ($value['view'] === "stacked" && isset($value['border'])) && ($value['border']!== "transparent" && $this->getColor($value['border'])) ) ? 'border: 1px solid '.$this->getColor($value['border']).';' : '';
				$css .= isset($value['borderRadius']) ? 'border-radius: '.$value['borderRadius'].'px;' : '';
				$css .= (isset($value['background']) && $this->getColor($value['background'])) ? 'background-color: '.$this->getColor($value['background']).';' : '';
				$css .= isset($value['borderWidth']) ? 'border-width: '.$value['borderWidth'].'px;' : '';
				$css .= (isset($value['iconHeightDesktop']) && $layoutType !== "horizontal") ? 'line-height: '.($value['iconHeightDesktop']+6).'px;' :  '';
				$css .= (  ( isset($value['gradientColor']) && ($value['view']=== "stacked") ) && ($value['selectedStackedTab'] !== "classic") ) ? $this->getGradients($value) : ''; 
				$css .= '}';

				$css .=  $unique_id . ' .enhanced-icons .enhanced-icon-item-'.$key.' .enhanced-icon i {';
				$css .= isset($value['iconSizeDesktop']) ? 'font-size: '.$value['iconSizeDesktop'].'px;' : '';
				$css .= (isset($value['color']) && $this->getColor($value['color'])) ? 'color: '.$this->getColor($value['color']).';' : '';
				$css .= isset($value['transform']) ? 'transform: rotate('.$value['transform'].'deg);':'';
				$css .= '}';
	
				$css .= '@media (max-width: 1024px) and (min-width: 768px){';
				$css .=  $unique_id . ' .enhanced-icons .enhanced-icon-item-'.$key.' {';
				$css .= isset($value['iconWidthTab']) ? "width: ".$value['iconWidthTab']."px;" : "";
				$css .= isset($value['iconHeightTab']) ? 'height: '.$value['iconHeightTab'].'px;' : '';
				$css .= isset($value['iconHeightTab']) ? "line-height: ".($value['iconHeightTab']+6)."px;": "";
				$css .= isset($value['iconTabMarginRight']) ? 'margin-right: '.$value['iconTabMarginRight'].'px;' : ''; 
				$css .= '}';
				$css .=  $unique_id . ' .enhanced-icons .enhanced-icon-item-'.$key.' .enhanced-icon i {';
				$css .= isset($value['iconSizeTab']) ? 'font-size: '.$value['iconSizeTab'].'px;' : '';
				$css .= '}';
				$css .= '}';

				$css .= '@media (max-width: 767px) {';
				$css .=  $unique_id . ' .enhanced-icons .enhanced-icon-item-'.$key.' {';
				$css .= isset($value['iconWidthMobile']) ? "width: ".$value['iconWidthMobile']."px;" : "";
				$css .= isset($value['iconHeightMobile']) ? 'height: '.$value['iconHeightMobile'].'px;' : '';
				$css .= isset($value['iconHeightMobile']) ? "line-height: ".($value['iconHeightMobile']+6)."px;": "";
				$css .= isset($value['iconMobileMarginRight']) ? 'margin-right: '.$value['iconMobileMarginRight'].'px;' : ''; 
				$css .= '}';
				$css .=  $unique_id . ' .enhanced-icons .enhanced-icon-item-'.$key.' .enhanced-icon i {';
				$css .= isset($value['iconSizeMobile']) ? 'font-size: '.$value['iconSizeMobile'].'px;' : '';
				$css .= '}';
				$css .= '}';

			}
		} 	
		if( !isset($attrs['gapBetweenItems']) ) {
			$attrs['gapBetweenItems'] = 5;
		}
		if( $layoutType === "vertical" ) {
			$css .=  $unique_id . ' .enhanced-icons > div {';
			$css .= 'display: block;';
			$css .= isset($attrs['gapBetweenItems']) ? 'margin-bottom: '. $attrs['gapBetweenItems'] . 'px;' : '';
			$css .= '}';
		} else {
			$css .=  $unique_id . ' .enhanced-icons > div {';
			$css .= 'display: flex;';
			$css .= 'justify-content: center;';
			$css .= 'align-items: center;';
			$css .= isset($attrs['gapBetweenItems']) ? 'margin-right: '. $attrs['gapBetweenItems'] . 'px;' : '';
			$css .= '}';
		}
		
		return $css;
}


	// Heading Block Css
	public function enhanced_heading_blocks_css($attrs, $unique_id){
		$css = '';
		$attributes = $this->enhanced_blocks_get_attributes();
		foreach ($attributes as $attr_key => $attr_value){
			if( $attr_key === 'heading_attributes' || $attr_key === 'highlight_attributes' || $attr_key === 'heading_tab_attributes' || $attr_key === 'heading_mob_attributes' ||  $attr_key === 'highlight_tab_attributes' || $attr_key === 'highlight_mob_attributes' ){
				$css .= $this->enhanced_blocks_get_css_property_value($attr_value, $attr_key, $attrs, $unique_id);
			}
		}
		return $css;
	}

	// Testimonial Block Css
	public function enhanced_testimonial_blocks_css($attrs, $unique_id){
		$css = '';
		$attributes = $this->enhanced_blocks_get_attributes();
		foreach ($attributes as $attr_key => $attr_value){
			if( $attr_key === 'testimonial_attributes' ||
				$attr_key === 'testimonial_des_tab_attributes' || 
				$attr_key === 'testimonial_des_mob_attributes' || 
				$attr_key === 'testimonial_title_attributes' || 
				$attr_key === 'testimonial_title_tab_attributes' ||
				$attr_key === 'testimonial_title_mob_attributes' || 
				$attr_key === 'testimonial_name_attributes' || 
				$attr_key === 'testimonial_name_tab_attributes' || 
				$attr_key === 'testimonial_name_mob_attributes' || 
				$attr_key === 'testimonial_quote_attributes'  ||  
				$attr_key === 'testimonial_quote_tab_attributes'  || 
				$attr_key === 'testimonial_quote_mob_attributes'  ||
				$attr_key === 'testimonial_background_attributes' ){
				$css .= $this->enhanced_blocks_get_css_property_value($attr_value, $attr_key, $attrs, $unique_id);
			}
			if( $attr_key === 'testimonial_bg_linear_gradient_attributes' || $attr_key === 'testimonial_bg_radial_gradient_attributes' ){
				$css .= $this->enhanced_blocks_get_multiple_css_property_value($attr_value, $attr_key, $attrs, $unique_id);
			}
		}
		return $css;
	}

	// Profile Block Css
	public function enhanced_profile_blocks_css($attrs, $unique_id){
		$css = '';
		$attributes = $this->enhanced_blocks_get_attributes();
		foreach ($attributes as $attr_key => $attr_value){
			if( $attr_key === 'profile_attributes' ||
				$attr_key === 'profile_title_tab_attributes' || 
				$attr_key === 'profile_title_mob_attributes' || 
				$attr_key === 'profile_designation_attributes' || 
				$attr_key === 'profile_designation_tab_attributes' ||
				$attr_key === 'profile_designation_mob_attributes' || 
				$attr_key === 'profile_description_attributes' || 
				$attr_key === 'profile_description_tab_attributes' || 
				$attr_key === 'profile_description_mob_attributes' || 
				$attr_key === 'profile_background_attributes' ||
				$attr_key === 'profile_background_overlay_bg_img_attributes' || 
				$attr_key === 'profile_background_overlay_bg_color_attributes' || 
				$attr_key === 'profile_background_overlay_attributes'
				){
				$css .= $this->enhanced_blocks_get_css_property_value($attr_value, $attr_key, $attrs, $unique_id);
			}
			if( $attr_key === 'profile_bg_linear_gradient_attributes' || $attr_key === 'profile_bg_radial_gradient_attributes' || $attr_key === 'profile_bg_overlay_gradient_linear_attributes' || $attr_key === 'profile_bg_overlay_gradient_radial_attributes' ){
				$css .= $this->enhanced_blocks_get_multiple_css_property_value($attr_value, $attr_key, $attrs, $unique_id);
			}
		}
		return $css;
	}

	// heading Google font
	public function enhanced_heading_gfont( $attr ) {
		// Description
		if ( isset( $attr['googleFont'] ) && $attr['googleFont'] && ( ! isset( $attr['loadGoogleFont'] ) || true == $attr['loadGoogleFont'] ) && isset( $attr['typography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['typography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['typography'],
					'fontvariants' => ( isset( $attr['fontVariant'] ) && ! empty( $attr['fontVariant'] ) ? array( $attr['fontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['fontSubset'] ) && ! empty( $attr['fontSubset'] ) ? array( $attr['fontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['typography'] ] = $add_font;
			} else {
				if ( isset( $attr['fontVariant'] ) && ! empty( $attr['fontVariant'] ) ) {
					if ( ! in_array( $attr['fontVariant'], self::$gfonts[ $attr['typography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['typography'] ]['fontvariants'], $attr['fontVariant'] );
					}
				}
				if ( isset( $attr['fontSubset'] ) && ! empty( $attr['fontSubset'] ) ) {
					if ( ! in_array( $attr['fontSubset'], self::$gfonts[ $attr['typography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['typography'] ]['fontsubsets'], $attr['fontSubset'] );
					}
				}
			}
		}
		// Name
		if ( isset( $attr['highlightGoogleFont'] ) && $attr['highlightGoogleFont'] && ( ! isset( $attr['highlightLoadGoogleFont'] ) || true == $attr['highlightLoadGoogleFont'] ) && isset( $attr['highlightTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['highlightTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['highlightTypography'],
					'fontvariants' => ( isset( $attr['highlightFontVariant'] ) && ! empty( $attr['highlightFontVariant'] ) ? array( $attr['highlightFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['highlightFontSubset'] ) && ! empty( $attr['highlightFontSubset'] ) ? array( $attr['highlightFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['highlightTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['highlightFontVariant'] ) && ! empty( $attr['highlightFontVariant'] ) ) {
					if ( ! in_array( $attr['highlightFontVariant'], self::$gfonts[ $attr['highlightTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['highlightTypography'] ]['fontvariants'], $attr['highlightFontVariant'] );
					}
				}
				if ( isset( $attr['highlightFontSubset'] ) && ! empty( $attr['highlightFontSubset'] ) ) {
					if ( ! in_array( $attr['highlightFontSubset'], self::$gfonts[ $attr['highlightTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['highlightTypography'] ]['fontsubsets'], $attr['highlightFontSubset'] );
					}
				}
			}
		}
		
	}

	// Profile Google font
	public function enhanced_profile_gfont( $attr ) {
		// Testimonial Description
		if ( isset( $attr['googleFont'] ) && $attr['googleFont'] && ( ! isset( $attr['loadGoogleFont'] ) || true == $attr['loadGoogleFont'] ) && isset( $attr['typography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['typography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['typography'],
					'fontvariants' => ( isset( $attr['fontVariant'] ) && ! empty( $attr['fontVariant'] ) ? array( $attr['fontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['fontSubset'] ) && ! empty( $attr['fontSubset'] ) ? array( $attr['fontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['typography'] ] = $add_font;
			} else {
				if ( isset( $attr['fontVariant'] ) && ! empty( $attr['fontVariant'] ) ) {
					if ( ! in_array( $attr['fontVariant'], self::$gfonts[ $attr['typography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['typography'] ]['fontvariants'], $attr['fontVariant'] );
					}
				}
				if ( isset( $attr['fontSubset'] ) && ! empty( $attr['fontSubset'] ) ) {
					if ( ! in_array( $attr['fontSubset'], self::$gfonts[ $attr['typography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['typography'] ]['fontsubsets'], $attr['fontSubset'] );
					}
				}
			}
		}
		// Testimonial Name
		if ( isset( $attr['designationGoogleFont'] ) && $attr['designationGoogleFont'] && ( ! isset( $attr['designationLoadGoogleFont'] ) || true == $attr['designationLoadGoogleFont'] ) && isset( $attr['designationTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['designationTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['designationTypography'],
					'fontvariants' => ( isset( $attr['designationFontVariant'] ) && ! empty( $attr['designationFontVariant'] ) ? array( $attr['designationFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['designationFontSubset'] ) && ! empty( $attr['designationFontSubset'] ) ? array( $attr['designationFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['designationTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['designationFontVariant'] ) && ! empty( $attr['designationFontVariant'] ) ) {
					if ( ! in_array( $attr['designationFontVariant'], self::$gfonts[ $attr['designationTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['designationTypography'] ]['fontvariants'], $attr['designationFontVariant'] );
					}
				}
				if ( isset( $attr['designationFontSubset'] ) && ! empty( $attr['designationFontSubset'] ) ) {
					if ( ! in_array( $attr['designationFontSubset'], self::$gfonts[ $attr['designationTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['designationTypography'] ]['fontsubsets'], $attr['designationFontSubset'] );
					}
				}
			}
		}
		// Testimonial Title
		if ( isset( $attr['descriptionGoogleFont'] ) && $attr['descriptionGoogleFont'] && ( ! isset( $attr['descriptionLoadGoogleFont'] ) || true == $attr['descriptionLoadGoogleFont'] ) && isset( $attr['descriptionTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['descriptionTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['descriptionTypography'],
					'fontvariants' => ( isset( $attr['descriptionFontVariant'] ) && ! empty( $attr['descriptionFontVariant'] ) ? array( $attr['descriptionFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['descriptionFontSubset'] ) && ! empty( $attr['descriptionFontSubset'] ) ? array( $attr['descriptionFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['descriptionTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['descriptionFontVariant'] ) && ! empty( $attr['descriptionFontVariant'] ) ) {
					if ( ! in_array( $attr['descriptionFontVariant'], self::$gfonts[ $attr['descriptionTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['descriptionTypography'] ]['fontvariants'], $attr['descriptionFontVariant'] );
					}
				}
				if ( isset( $attr['descriptionFontSubset'] ) && ! empty( $attr['descriptionFontSubset'] ) ) {
					if ( ! in_array( $attr['descriptionFontSubset'], self::$gfonts[ $attr['descriptionTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['descriptionTypography'] ]['fontsubsets'], $attr['descriptionFontSubset'] );
					}
				}
			}
		}
	}


	// Call-to-action Google font
	public function enhanced_cta_gfont( $attr )
	{
		// Title
		if ( isset( $attr['titleGoogleFont'] ) && $attr['titleGoogleFont'] && ( ! isset( $attr['loadGoogleFont'] ) || true == $attr['loadGoogleFont'] ) && isset( $attr['titleTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['titleTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['titleTypography'],
					'fontvariants' => ( isset( $attr['titleFontVariant'] ) && ! empty( $attr['titleFontVariant'] ) ? array( $attr['titleFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['titleFontSubset'] ) && ! empty( $attr['titleFontSubset'] ) ? array( $attr['titleFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['titleTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['titleFontVariant'] ) && ! empty( $attr['titleFontVariant'] ) ) {
					if ( ! in_array( $attr['titleFontVariant'], self::$gfonts[ $attr['titleTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['titleTypography'] ]['fontvariants'], $attr['titleFontVariant'] );
					}
				}
				if ( isset( $attr['titleFontSubset'] ) && ! empty( $attr['titleFontSubset'] ) ) {
					if ( ! in_array( $attr['titleFontSubset'], self::$gfonts[ $attr['titleTypography'] ]['titleFontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['titleTypography'] ]['fontsubsets'], $attr['titleFontSubset'] );
					}
				}
			}
		}
		// text
		if ( isset( $attr['textGoogleFont'] ) && $attr['textGoogleFont'] && ( ! isset( $attr['textLoadGoogleFont'] ) || true == $attr['textLoadGoogleFont'] ) && isset( $attr['textTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['textTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['textTypography'],
					'fontvariants' => ( isset( $attr['textFontVariant'] ) && ! empty( $attr['textFontVariant'] ) ? array( $attr['textFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['textFontSubset'] ) && ! empty( $attr['textFontSubset'] ) ? array( $attr['textFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['textTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['textFontVariant'] ) && ! empty( $attr['textFontVariant'] ) ) {
					if ( ! in_array( $attr['textFontVariant'], self::$gfonts[ $attr['textTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['textTypography'] ]['fontvariants'], $attr['textFontVariant'] );
					}
				}
				if ( isset( $attr['textFontSubset'] ) && ! empty( $attr['textFontSubset'] ) ) {
					if ( ! in_array( $attr['textFontSubset'], self::$gfonts[ $attr['textTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['textTypography'] ]['fontsubsets'], $attr['textFontSubset'] );
					}
				}
			}
		}

		// first btn
		if ( isset( $attr['firstBtnGoogleFont'] ) && $attr['firstBtnGoogleFont'] && ( ! isset( $attr['firstBtnLoadGoogleFont'] ) || true == $attr['firstBtnLoadGoogleFont'] ) && isset( $attr['firstBtnTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['firstBtnTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['firstBtnTypography'],
					'fontvariants' => ( isset( $attr['firstBtnFontVariant'] ) && ! empty( $attr['firstBtnFontVariant'] ) ? array( $attr['firstBtnFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['firstBtnFontSubset'] ) && ! empty( $attr['firstBtnFontSubset'] ) ? array( $attr['firstBtnFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['firstBtnTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['firstBtnFontVariant'] ) && ! empty( $attr['firstBtnFontVariant'] ) ) {
					if ( ! in_array( $attr['firstBtnFontVariant'], self::$gfonts[ $attr['firstBtnTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['firstBtnTypography'] ]['fontvariants'], $attr['firstBtnFontVariant'] );
					}
				}
				if ( isset( $attr['firstBtnFontSubset'] ) && ! empty( $attr['firstBtnFontSubset'] ) ) {
					if ( ! in_array( $attr['firstBtnFontSubset'], self::$gfonts[ $attr['firstBtnTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['firstBtnTypography'] ]['fontsubsets'], $attr['firstBtnFontSubset'] );
					}
				}
			}
		}

		// second btn
		if ( isset( $attr['secondBtnGoogleFont'] ) && $attr['secondBtnGoogleFont'] && ( ! isset( $attr['secondBtnLoadGoogleFont'] ) || true == $attr['secondBtnLoadGoogleFont'] ) && isset( $attr['secondBtnTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['secondBtnTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['secondBtnTypography'],
					'fontvariants' => ( isset( $attr['secondBtnFontVariant'] ) && ! empty( $attr['secondBtnFontVariant'] ) ? array( $attr['secondBtnFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['secondBtnFontSubset'] ) && ! empty( $attr['secondBtnFontSubset'] ) ? array( $attr['secondBtnFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['secondBtnTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['secondBtnFontVariant'] ) && ! empty( $attr['secondBtnFontVariant'] ) ) {
					if ( ! in_array( $attr['secondBtnFontVariant'], self::$gfonts[ $attr['secondBtnTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['secondBtnTypography'] ]['fontvariants'], $attr['secondBtnFontVariant'] );
					}
				}
				if ( isset( $attr['secondBtnFontSubset'] ) && ! empty( $attr['secondBtnFontSubset'] ) ) {
					if ( ! in_array( $attr['secondBtnFontSubset'], self::$gfonts[ $attr['secondBtnTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['secondBtnTypography'] ]['fontsubsets'], $attr['secondBtnFontSubset'] );
					}
				}
			}
		}

		// additional text
		if ( isset( $attr['additionalTxtGoogleFont'] ) && $attr['additionalTxtGoogleFont'] && ( ! isset( $attr['additionalTxtLoadGoogleFont'] ) || true == $attr['additionalTxtLoadGoogleFont'] ) && isset( $attr['additionalTxtTypography'] ) ) {
			// Check if the font has been added yet.
			if ( ! array_key_exists( $attr['additionalTxtTypography'], self::$gfonts ) ) {
				$add_font = array(
					'fontfamily' => $attr['additionalTxtTypography'],
					'fontvariants' => ( isset( $attr['additionalTxtFontVariant'] ) && ! empty( $attr['additionalTxtFontVariant'] ) ? array( $attr['additionalTxtFontVariant'] ) : array() ),
					'fontsubsets' => ( isset( $attr['additionalTxtFontSubset'] ) && ! empty( $attr['additionalTxtFontSubset'] ) ? array( $attr['additionalTxtFontSubset'] ) : array() ),
				);
				self::$gfonts[ $attr['additionalTxtTypography'] ] = $add_font;
			} else {
				if ( isset( $attr['additionalTxtFontVariant'] ) && ! empty( $attr['additionalTxtFontVariant'] ) ) {
					if ( ! in_array( $attr['additionalTxtFontVariant'], self::$gfonts[ $attr['additionalTxtTypography'] ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $attr['additionalTxtTypography'] ]['fontvariants'], $attr['additionalTxtFontVariant'] );
					}
				}
				if ( isset( $attr['additionalTxtFontSubset'] ) && ! empty( $attr['additionalTxtFontSubset'] ) ) {
					if ( ! in_array( $attr['additionalTxtFontSubset'], self::$gfonts[ $attr['additionalTxtTypography'] ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $attr['additionalTxtTypography'] ]['fontsubsets'], $attr['additionalTxtFontSubset'] );
					}
				}
			}
		}
	}

	public function getGradients($attrs){
		$gradientType = isset($attrs['gradientType']) ? $attrs['gradientType'] : 'linear';
		
		if( isset($attrs['gradientColor']) && is_array($attrs['gradientColor'])){
			$gradientColor = isset($attrs['gradientColor']) && $this->getColor($attrs['gradientColor']) ? $this->getColor($attrs['gradientColor']) : '';
		} else if(isset($attrs['gradientColor']) && is_string($attrs['gradientColor']) ) {
			$gradientColor = isset($attrs['gradientColor']) && $this->getColor($attrs['gradientColor']) ? $this->getColor($attrs['gradientColor']) : '';
		} else {
			$gradientColor = isset($attrs['gradientColor']) ? $attrs['gradientColor'] : '';
		}

		if( isset($attrs['gradientSecondColor']) && is_array($attrs['gradientSecondColor']) ){
			$gradientSecondColor = isset($attrs['gradientSecondColor']) && $this->getColor($attrs['gradientSecondColor']) ? $this->getColor($attrs['gradientSecondColor']) : '#4c10cd';
		} else if( isset($attrs['gradientSecondColor']) && is_string($attrs['gradientSecondColor']) ){
			$gradientSecondColor = isset($attrs['gradientSecondColor']) && $this->getColor($attrs['gradientSecondColor']) ? $this->getColor($attrs['gradientSecondColor']) : '#4c10cd';
		} else {
			$gradientSecondColor = isset($attrs['gradientSecondColor']) ? $attrs['gradientSecondColor'] : '#4c10cd';
		}
		
		$gradientLocation = isset($attrs['gradientLocation']) ? $attrs['gradientLocation'] : 1;
		$gradientSecondLocation = isset($attrs['gradientSecondLocation']) ? $attrs['gradientSecondLocation'] : 100;
		$gradientAngle = isset($attrs['gradientAngle']) ? $attrs['gradientAngle'] : 180;
		$gradientPosition = isset($attrs['gradientPosition']) ? $attrs['gradientPosition'] : 'center center';
		$css = '';
		if( $gradientType === 'linear' ) {
			$css .= 'background-image: linear-gradient(' .$gradientAngle. 'deg, ' . $gradientColor .' '. $gradientLocation.'%, '. $gradientSecondColor.' ' .$gradientSecondLocation .'%'.');';
		}
		
		if( $gradientType === 'radial' ) {
			$css .= 'background-image: radial-gradient(at ' .$gradientPosition. ', ' . $gradientColor .' '. $gradientLocation.'%, '. $gradientSecondColor.' ' .$gradientSecondLocation .'%'.');';
		}
		
		return $css;
		
	}
	public function getGradientsHover($attrs){
		$gradientTypeHover = isset($attrs['gradientTypeHover']) ? $attrs['gradientTypeHover'] : 'linear';
		
		if( isset($attrs['gradientColorHover']) && is_array($attrs['gradientColorHover']) ){
			$gradientColorHover = isset($attrs['gradientColorHover']) && $this->getColor($attrs['gradientColorHover']) ? $this->getColor($attrs['gradientColorHover']) : '';
		} else if( isset($attrs['gradientColorHover']) && is_string($attrs['gradientColorHover']) ){
			$gradientColorHover = isset($attrs['gradientColorHover']) && $this->getColor($attrs['gradientColorHover']) ? $this->getColor($attrs['gradientColorHover']) : '';
		} else {
			$gradientColorHover = isset($attrs['gradientColorHover']) ? $attrs['gradientColorHover'] : '';
		}

		if( isset($attrs['gradientSecondColorHover']) && is_array($attrs['gradientSecondColorHover']) ){
			$gradientSecondColorHover = isset($attrs['gradientSecondColorHover']) && $this->getColor($attrs['gradientSecondColorHover']) ? $this->getColor($attrs['gradientSecondColorHover']) : '#4c10cd';
		} else if( isset($attrs['gradientSecondColorHover']) && is_string($attrs['gradientSecondColorHover']) ){
			$gradientSecondColorHover = isset($attrs['gradientSecondColorHover']) && $this->getColor($attrs['gradientSecondColorHover']) ? $this->getColor($attrs['gradientSecondColorHover']) : '#4c10cd';
		} else {
			$gradientSecondColorHover = isset($attrs['gradientSecondColorHover']) ? $attrs['gradientSecondColorHover'] : '#4c10cd';
		}

		$gradientLocationHover = isset($attrs['gradientLocationHover']) ? $attrs['gradientLocationHover'] : 1;
		$gradientSecondLocationHover = isset($attrs['gradientSecondLocationHover']) ? $attrs['gradientSecondLocationHover'] : 100;
		$gradientAngleHover = isset($attrs['gradientAngleHover']) ? $attrs['gradientAngleHover'] : 180;
		$gradientPositionHover = isset($attrs['gradientPositionHover']) ? $attrs['gradientPositionHover'] : 'center center';
		$css = '';
		if( $gradientTypeHover === 'linear' ) {
			$css .= 'background-image: linear-gradient(' .$gradientAngleHover. 'deg, ' . $gradientColorHover .' '. $gradientLocationHover.'%, '. $gradientSecondColorHover.' ' .$gradientSecondLocationHover .'%'.');';
		}
		
		if( $gradientTypeHover === 'radial' ) {
			$css .= 'background-image: radial-gradient(at ' .$gradientPositionHover. ', ' . $gradientColorHover .' '. $gradientLocationHover.'%, '. $gradientSecondColorHover.' ' .$gradientSecondLocationHover .'%'.');';
		}
		
		return $css;
		
	}
	
	
	public function getBoxShadow($attrs, $type){
		$shadowHorizontalNormal = isset($attrs['shadowHorizontal'.$type]) ? $attrs['shadowHorizontal'.$type].'px': '0px';
		$shadowVerticalNormal = isset($attrs['shadowVertical'.$type]) ? $attrs['shadowVertical'.$type].'px': '0px';
		$shadowBlurNormal = isset($attrs['shadowBlur'.$type]) ? $attrs['shadowBlur'.$type].'px': '0px';
		$shadowSpreadNormal = isset($attrs['shadowSpread'.$type]) ? $attrs['shadowSpread'.$type].'px': '0px';
		$boxShadowNormalColor = isset($attrs['boxShadow'.$type.'Color']) ? $attrs['boxShadow'.$type.'Color'] : '';
		$shadowPositionNormal = isset($attrs['shadowPosition'.$type]) ? $attrs['shadowPosition'.$type] : '';
		return 'box-shadow: '.$shadowHorizontalNormal.' '.$shadowVerticalNormal.' '.$shadowBlurNormal.' '.$shadowSpreadNormal.' '.$boxShadowNormalColor.' '.$shadowPositionNormal.';';
	}


	public function getColor($color){
		
		$colorVal = '';
		if( ((isset($color['hex'])) && (isset($color['color']) && $color['color']['_a'] === 1))){
			$colorVal = isset($color['hex']) ? $color['hex'] : '';
		} else if( (isset($color['hex']) && isset($color['rgb'])) && $color['rgb']['a'] === 1){
			$colorVal = isset($color['hex']) ? $color['hex'] : '';
		} else if( (isset($color) && isset($color['rgb'])) && $color['rgb']['a'] < 1){
			$colorVal = 'rgba('.$color['rgb']['r'].','.$color['rgb']['g'].','.$color['rgb']['b'].','.$color['rgb']['a'].')';
		} else if( isset($color[2]) && $color[2] === 'r' ){
			$color = json_decode($color, true);
			$colorVal = 'rgba('.$color['r'].','.$color['g'].','.$color['b'].','.$color['a'].')';
		} else if( isset($color) && !is_array($color) ) {
			$colorVal = $color;
		}
		return $colorVal;
	}


	public function generate_post_grid_css($attrs, $unique_id){
		$selectedBgTab = isset($attrs['selectedBgTab']) ? $attrs['selectedBgTab'] : 'classic';
		$columns = isset($attrs['columns']) ? $attrs['columns'] : 3;
		$columnGap = isset($attrs['columnGap'])  ? $attrs['columnGap'] : 15;
		$rowGap = isset($attrs['rowGap'])  ? $attrs['rowGap'] : 30;
		
		$metaTypography = isset($attrs['metaTypography']) && !empty($attrs['metaTypography']) ? $attrs['metaTypography'] : '';
		$titleTypography = isset($attrs['titleTypography']) && !empty($attrs['titleTypography']) ? $attrs['titleTypography'] : '';
		$contentTypography = isset($attrs['contentTypography']) && !empty($attrs['contentTypography']) ? $attrs['contentTypography'] : '';
		$buttonTypography = isset($attrs['buttonTypography']) && !empty($attrs['buttonTypography']) ? $attrs['buttonTypography'] : '';

		$meta_font_family = isset($attrs['metaGoogleFont']) ? '"'.$metaTypography.'", sans-serif' : $metaTypography;
		$title_font_family = isset($attrs['titleGoogleFont']) ? '"'.$titleTypography.'", sans-serif' : $titleTypography;
		$content_font_family = isset($attrs['contentGoogleFont']) ? '"'.$contentTypography.'", sans-serif' : $contentTypography;
		$button_font_family = isset($attrs['buttonGoogleFont']) ? '"'.$buttonTypography.'", sans-serif' : $buttonTypography;
		
		$css = '';
		
		 // background 
		if ( isset( $attrs['bgColor'] ) || isset( $attrs['gradientColor'] ) || isset($attrs['gradientSecondColor']) ) {
			$css .= $unique_id . ' .post-item .post-item-inner-wrapper{';
			$css .= ( (isset( $attrs['bgColor'] ) && ! empty( $attrs['bgColor'] )) && $this->getColor($attrs['bgColor']) ) ? 'background-color:' . $this->getColor($attrs['bgColor']) . ';' : '';
			$css .= $selectedBgTab === 'gradients' ? $this->getGradients($attrs) : '';
			$css .= '}';
		}
		
		if ( isset( $attrs['borderTypeNormal'] ) || isset( $attrs['borderRadiusNormalTop'] ) || isset( $attrs['borderRadiusNormalRight'] ) || isset( $attrs['borderRadiusNormalLeft'] ) || isset( $attrs['borderRadiusNormalBottom'] )  ) {
			$css .= $unique_id . ' .post-item{';
			$css .= isset( $attrs['borderTypeNormal'] ) && ! empty( $attrs['borderTypeNormal'] ) ? 'border-style:' . $attrs['borderTypeNormal'] . ';' : '';
			$css .= isset( $attrs['borderColorNormal'] ) && ! empty( $attrs['borderColorNormal'] ) ? 'border-color:' . $attrs['borderColorNormal'] . ';' : '';
			$css .= isset( $attrs['borderWidthNormalTop'] ) && ! empty( $attrs['borderWidthNormalTop'] ) ? 'border-top-width:' . $attrs['borderWidthNormalTop'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthNormalRight'] ) && ! empty( $attrs['borderWidthNormalRight'] ) ? 'border-right-width:' . $attrs['borderWidthNormalRight'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthNormalBottom'] ) && ! empty( $attrs['borderWidthNormalBottom'] ) ? 'border-bottom-width:' . $attrs['borderWidthNormalBottom'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthNormalLeft'] ) && ! empty( $attrs['borderWidthNormalLeft'] ) ? 'border-left-width:' . $attrs['borderWidthNormalLeft'] . 'px;' : '';
			$css .= isset( $attrs['borderRadiusNormalTop'] ) && ! empty( $attrs['borderRadiusNormalTop'] ) ? 'border-top-left-radius:' . $attrs['borderRadiusNormalTop'] . 'px;' : 'border-top-left-radius: 0px;';
			$css .= isset( $attrs['borderRadiusNormalRight'] ) && ! empty( $attrs['borderRadiusNormalRight'] ) ? 'border-top-right-radius:' . $attrs['borderRadiusNormalRight'] . 'px;' : 'border-top-right-radius: 0px;';
			$css .= isset( $attrs['borderRadiusNormalLeft'] ) && ! empty( $attrs['borderRadiusNormalLeft'] ) ? 'border-bottom-left-radius:' . $attrs['borderRadiusNormalLeft'] . 'px;' : 'border-bottom-left-radius: 0px;';
			$css .= isset( $attrs['borderRadiusNormalBottom'] ) && ! empty( $attrs['borderRadiusNormalBottom'] ) ? 'border-bottom-right-radius:' . $attrs['borderRadiusNormalBottom'] . 'px;' : 'border-bottom-right-radius: 0px;';
			$css .= '}';
		}

		if ( isset( $attrs['borderTypeHover'] ) || isset( $attrs['borderWidthHoverLeft'] ) || isset( $attrs['borderRadiusHoverRight'] ) || isset( $attrs['borderRadiusHoverLeft'] ) || isset( $attrs['borderRadiusHoverBottom'] ) ) {
			$css .= $unique_id . ' .post-item:hover{';
			$css .= isset( $attrs['borderTypeHover'] ) && ! empty( $attrs['borderTypeHover'] ) ? 'border-style:' . $attrs['borderTypeHover'] . ';' : '';
			$css .= isset( $attrs['borderColorHover'] ) && ! empty( $attrs['borderColorHover'] ) ? 'border-color:' . $attrs['borderColorHover'] . ';' : '';
			$css .= isset( $attrs['borderWidthHoverTop'] ) && ! empty( $attrs['borderWidthHoverTop'] ) ? 'border-top-width:' . $attrs['borderWidthHoverTop'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthHoverRight'] ) && ! empty( $attrs['borderWidthHoverRight'] ) ? 'border-right-width:' . $attrs['borderWidthHoverRight'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthHoverBottom'] ) && ! empty( $attrs['borderWidthHoverBottom'] ) ? 'border-bottom-width:' . $attrs['borderWidthHoverBottom'] . 'px;' : '';
			$css .= isset( $attrs['borderWidthHoverLeft'] ) && ! empty( $attrs['borderWidthHoverLeft'] ) ? 'border-left-width:' . $attrs['borderWidthHoverLeft'] . 'px;' : '';
			$css .= isset( $attrs['borderRadiusHoverTop'] ) && ! empty( $attrs['borderRadiusHoverTop'] ) ? 'border-top-left-radius:' . $attrs['borderRadiusHoverTop'] . 'px;' : 'border-top-left-radius:0px;';
			$css .= isset( $attrs['borderRadiusHoverRight'] ) && ! empty( $attrs['borderRadiusHoverRight'] ) ? 'border-top-right-radius:' . $attrs['borderRadiusHoverRight'] . 'px;' : 'border-top-right-radius:0px;';
			$css .= isset( $attrs['borderRadiusHoverLeft'] ) && ! empty( $attrs['borderRadiusHoverLeft'] ) ? 'border-bottom-left-radius:' . $attrs['borderRadiusHoverLeft'] . 'px;' : 'border-bottom-left-radius:0px;';
			$css .= isset( $attrs['borderRadiusHoverBottom'] ) && ! empty( $attrs['borderRadiusHoverBottom'] ) ? 'border-bottom-right-radius:' . $attrs['borderRadiusHoverBottom'] . 'px;' : 'border-bottom-right-radius:0px;';
			$css .= '}';
		}

		if ( isset( $attrs['borderTransitionDurationHover'] ) || isset( $attrs['borderTransitionDurationHover'] ) ) {
			$css .= $unique_id . ' .post-item{';
			$css .= isset( $attrs['borderTransitionDurationHover'] ) && ! empty( $attrs['borderTransitionDurationHover'] ) ? 'transition:' . $attrs['borderTransitionDurationHover'] . 's;' : '';
			$css .= '}';
		}
		
		if ( isset( $attrs['boxShadowNormalColor'] ) ) {
			$css .= $unique_id . ' .post-item{';
			$css .= $this->getBoxShadow($attrs, $type='Normal');
			$css .= '}';
		}

		if ( isset( $attrs['boxShadowHoverColor'] ) ) {
			$css .= $unique_id . ' .post-item:hover{';
			$css .= $this->getBoxShadow($attrs, $type='Hover');
			$css .= '}';
		}
		
		// column margin, padding
		if ( $columnGap || isset( $attrs['rowGap'] ) ) {
			$css .= $unique_id . ' .post-item {';
			if( isset( $attrs['postLayout'] )&&  $attrs['postLayout'] !== 'list') {
				$css .= $columnGap ? 'width:calc(' . ( 100 / $columns ) . '% - ' . ( $columnGap * 2 ) . 'px);'
					: 'width:calc(' . ( 100 / $columns ) . '% - 0px);';
			}
			$css .= $columnGap !== 0 ? 'margin-left:' . $columnGap . 'px;' : 'margin-left:0px;';
			$css .= $columnGap !== 0 ? 'margin-right:' . $columnGap . 'px;' : 'margin-right:0px;';
			$css .= $rowGap !== 0? 'margin-bottom:' . $rowGap . 'px;' : 'margin-bottom:0px;';
			$css .= '}';
		}
		

		// meta
		if ( isset( $attrs['metaTypography'] ) || isset( $attrs['metaFontWeight'] ) || isset( $attrs['metaFontStyle'] ) || isset( $attrs['metaColor'] ) || isset( $attrs['desktopMetaTextSize'] ) || isset($attrs['desktopMetaSpacing']) || isset($attrs['desktopMetaLineHeight']) || isset($attrs['metaTextTransform']) ) {
			$css .= $unique_id . ' .post-item .post-content-area .post-meta time, '.$unique_id . ' .post-item .post-content-area .post-meta a, '.$unique_id . ' .post-item .post-content-area .post-meta span.comment {';
			$css .= isset( $meta_font_family ) && ! empty( $meta_font_family ) ? 'font-family:' . $meta_font_family . ';' : '';
			$css .= isset( $attrs['metaFontWeight'] ) && ! empty( $attrs['metaFontWeight'] ) ? 'font-weight:' . $attrs['metaFontWeight'] . ';' : '';
			$css .= isset( $attrs['metaFontStyle'] ) && ! empty( $attrs['metaFontStyle'] ) ? 'font-style:' . $attrs['metaFontStyle'] . ';' : '';
			$css .= ( (isset( $attrs['metaColor'] ) && ! empty( $attrs['metaColor'] )) && $this->getColor($attrs['metaColor']) ) ? 'color:' . $this->getColor($attrs['metaColor']) . ';' : '';
			$css .= isset( $attrs['desktopMetaTextSize'] ) && ! empty( $attrs['desktopMetaTextSize'] ) ? 'font-size:' . $attrs['desktopMetaTextSize'] . 'px;' : '';
			$css .= isset( $attrs['desktopMetaSpacing'] ) && ! empty( $attrs['desktopMetaSpacing'] ) ? 'padding-right:' . $attrs['desktopMetaSpacing'] . 'px;' : '';
			$css .= isset( $attrs['desktopMetaLineHeight'] ) && ! empty( $attrs['desktopMetaLineHeight'] ) ? 'line-height:' . $attrs['desktopMetaLineHeight'] . 'px;' : '';
			$css .= isset( $attrs['metaTextTransform'] ) && ! empty( $attrs['metaTextTransform'] ) ? 'text-transform:' . $attrs['metaTextTransform'] . ';' : '';
			$css .= '}';
		}

		if ( isset( $attrs['metaAlignment'] ) && ! empty( $attrs['metaAlignment'] ) ) {
			$css .= $unique_id . ' .post-item .post-content-area .post-meta {';
			$css .= 'text-align:' . $attrs['metaAlignment'] . ';';
			$css .= '}';
		}

        // title
		if ( isset( $attrs['titleTypography'] ) || isset( $attrs['titleFontWeight'] ) || isset( $attrs['titleFontStyle'] ) || isset( $attrs['titleColor'] ) || isset( $attrs['desktopTitleTextSize'] ) || isset($attrs['desktopTitleLineHeight']) || isset($attrs['titleTextTransform']) || isset($attrs['titleAlignment']) || isset($attrs['desktopTitleTopSpacing']) ) {
			$css .= $unique_id . ' .post-item .post-item-inner-wrapper .post-content-area .post-title a{';
			$css .= isset( $title_font_family ) && ! empty( $title_font_family ) ? 'font-family:' . $title_font_family . ';' : '';
			$css .= isset( $attrs['titleFontWeight'] ) && ! empty( $attrs['titleFontWeight'] ) ? 'font-weight:' . $attrs['titleFontWeight'] . ';' : '';
			$css .= isset( $attrs['titleFontStyle'] ) && ! empty( $attrs['titleFontStyle'] ) ? 'font-style:' . $attrs['titleFontStyle'] . ';' : '';
			$css .= ( (isset( $attrs['titleColor'] ) && ! empty( $attrs['titleColor'] ) ) && $this->getColor($attrs['titleColor']) ) ? 'color:' . $this->getColor($attrs['titleColor']) . ';' : '';
			$css .= isset( $attrs['desktopTitleTextSize'] ) && ! empty( $attrs['desktopTitleTextSize'] ) ? 'font-size:' . $attrs['desktopTitleTextSize'] . 'px;' : '';
			$css .= isset( $attrs['desktopTitleLineHeight'] ) && ! empty( $attrs['desktopTitleLineHeight'] ) ? 'line-height:' . $attrs['desktopTitleLineHeight'] . 'px;' : '';
			$css .= isset( $attrs['titleTextTransform'] ) && ! empty( $attrs['titleTextTransform'] ) ? 'text-transform:' . $attrs['titleTextTransform'] . ';' : '';
			$css .= isset( $attrs['titleAlignment'] ) && ! empty( $attrs['titleAlignment'] ) ? 'text-align:' . $attrs['titleAlignment'] . ';' : '';
			$css .= isset( $attrs['desktopTitleTopSpacing'] ) && ! empty( $attrs['desktopTitleTopSpacing'] ) ? 'margin-top:' . $attrs['desktopTitleTopSpacing'] . 'px;' : '';
			$css .= '}';
		}
		
		// content
		if ( isset( $attrs['contentTypography'] ) || isset( $attrs['contentFontWeight'] ) || isset( $attrs['contentFontStyle'] ) || isset( $attrs['contentColor'] ) || isset( $attrs['desktopContentTextSize'] ) || isset($attrs['desktopContentLineHeight']) || isset($attrs['contentTextTransform']) || isset($attrs['contentAlignment']) || isset($attrs['desktopContentTopSpacing']) ) {
			$css .= $unique_id . ' .post-item .post-item-inner-wrapper .post-content-area .post-excerpt p{';
			$css .= isset( $content_font_family ) && ! empty( $content_font_family ) ? 'font-family:' . $content_font_family . ';' : '';
			$css .= isset( $attrs['contentFontWeight'] ) && ! empty( $attrs['contentFontWeight'] ) ? 'font-weight:' . $attrs['contentFontWeight'] . ';' : '';
			$css .= isset( $attrs['contentFontStyle'] ) && ! empty( $attrs['contentFontStyle'] ) ? 'font-style:' . $attrs['contentFontStyle'] . ';' : '';
			$css .= ( (isset( $attrs['contentColor'] ) && ! empty( $attrs['contentColor'] ) ) && $this->getColor($attrs['contentColor']) ) ? 'color:' . $this->getColor($attrs['contentColor']) . ';' : '';
			$css .= isset( $attrs['desktopContentTextSize'] ) && ! empty( $attrs['desktopContentTextSize'] ) ? 'font-size:' . $attrs['desktopContentTextSize'] . 'px;' : '';
			$css .= isset( $attrs['desktopContentLineHeight'] ) && ! empty( $attrs['desktopContentLineHeight'] ) ? 'line-height:' . $attrs['desktopContentLineHeight'] . 'px;' : '';
			$css .= isset( $attrs['contentTextTransform'] ) && ! empty( $attrs['contentTextTransform'] ) ? 'text-transform:' . $attrs['contentTextTransform'] . ';' : '';
			$css .= isset( $attrs['contentAlignment'] ) && ! empty( $attrs['contentAlignment'] ) ? 'text-align:' . $attrs['contentAlignment'] . ';' : '';
			$css .= isset( $attrs['desktopContentTopSpacing'] ) && ! empty( $attrs['desktopContentTopSpacing'] ) ? 'margin-top:' . $attrs['desktopContentTopSpacing'] . 'px;' : '';
			$css .= '}';
		}
		
		// button
		if ( isset( $attrs['buttonTypography'] ) || isset( $attrs['buttonFontWeight'] ) || isset( $attrs['buttonFontStyle'] ) || isset( $attrs['buttonColor'] ) || isset( $attrs['borderColor'] ) || isset( $attrs['desktopButtonTextSize'] ) || isset($attrs['desktopButtonLineHeight']) || isset($attrs['buttonTextTransform']) || isset($attrs['desktopButtonTopSpacing']) ) {
			$css .= $unique_id . ' .post-item .post-item-inner-wrapper .post-content-area .post-read-moore{';
			$css .= isset( $button_font_family ) && ! empty( $button_font_family ) ? 'font-family:' . $button_font_family . ';' : '';
			$css .= isset( $attrs['buttonFontWeight'] ) && ! empty( $attrs['buttonFontWeight'] ) ? 'font-weight:' . $attrs['buttonFontWeight'] . ';' : '';
			$css .= isset( $attrs['buttonFontStyle'] ) && ! empty( $attrs['buttonFontStyle'] ) ? 'font-style:' . $attrs['buttonFontStyle'] . ';' : '';
			$css .= ((isset( $attrs['buttonColor'] ) && ! empty( $attrs['buttonColor'] ) ) && $this->getColor($attrs['buttonColor']) )? 'color:' . $this->getColor($attrs['buttonColor']) . ';' : '';
			$css .= ( (isset( $attrs['borderColor'] ) && ! empty( $attrs['borderColor'] ) ) && $this->getColor($attrs['buttonColor']) ) ? 'border-color:' . $this->getColor($attrs['borderColor']) . '; border-bottom-width: 1px;' : '';
			$css .= isset( $attrs['desktopButtonTextSize'] ) && ! empty( $attrs['desktopButtonTextSize'] ) ? 'font-size:' . $attrs['desktopButtonTextSize'] . 'px;' : '';
			$css .= isset( $attrs['desktopButtonLineHeight'] ) && ! empty( $attrs['desktopButtonLineHeight'] ) ? 'line-height:' . $attrs['desktopButtonLineHeight'] . 'px;' : '';
			$css .= isset( $attrs['buttonTextTransform'] ) && ! empty( $attrs['buttonTextTransform'] ) ? 'text-transform:' . $attrs['buttonTextTransform'] . ';' : '';
			$css .= isset( $attrs['desktopButtonTopSpacing'] ) && ! empty( $attrs['desktopButtonTopSpacing'] ) ? 'margin-top:' . $attrs['desktopButtonTopSpacing'] . 'px;' : '';
			$css .= '}';
		}

		if ( isset( $attrs['buttonAlignment'] ) && ! empty( $attrs['buttonAlignment'] ) ) {
			$css .= $unique_id . ' .post-item .post-item-inner-wrapper .post-content-area .post-btn {';
			$css .= 'text-align:' . $attrs['buttonAlignment'] . ';';
			$css .= '}';
		}

		
		// responsive tab meta 
		if ( isset( $attrs['tabMetaTextSize'] ) || isset($attrs['tabMetaSpacing']) || isset($attrs['tabMetaLineHeight']) ) {
			$css .= '@media only screen and (max-width: 1024px) and (min-width: 768px) {';
				$css .= $unique_id . ' .post-item .post-content-area .post-meta time, '.$unique_id . ' .post-item .post-content-area .post-meta a, '.$unique_id . ' .post-item .post-content-area .post-meta span.comment {';
				$css .= isset( $attrs['tabMetaTextSize'] ) && ! empty( $attrs['tabMetaTextSize'] ) ? 'font-size:' . $attrs['tabMetaTextSize'] . 'px;' : '';
				$css .= isset( $attrs['tabMetaSpacing'] ) && ! empty( $attrs['tabMetaSpacing'] ) ? 'padding-right:' . $attrs['tabMetaSpacing'] . 'px;' : '';
				$css .= isset( $attrs['tabMetaLineHeight'] ) && ! empty( $attrs['tabMetaLineHeight'] ) ? 'line-height:' . $attrs['tabMetaLineHeight'] . 'px;' : '';
				$css .= '}';
				
			$css .= '}';
		}


		// responsive tab title  
		if ( isset( $attrs['tabTitleTextSize'] ) || isset($attrs['tabTitleSpacing']) || isset($attrs['tabTitleLineHeight']) || isset($attrs['tabTitleTopSpacing']) ) {
			$css .= '@media only screen and (max-width: 1024px) and (min-width: 768px) {';

			$css .= $unique_id . ' .post-item .post-item-inner-wrapper .post-content-area .post-title a{';
			$css .= isset( $attrs['tabTitleTextSize'] ) && ! empty( $attrs['tabTitleTextSize'] ) ? 'font-size:' . $attrs['tabTitleTextSize'] . 'px;' : '';
			$css .= isset( $attrs['tabTitleSpacing'] ) && ! empty( $attrs['tabTitleSpacing'] ) ? 'padding-right:' . $attrs['tabTitleSpacing'] . 'px;' : '';
			$css .= isset( $attrs['tabTitleLineHeight'] ) && ! empty( $attrs['tabTitleLineHeight'] ) ? 'line-height:' . $attrs['tabTitleLineHeight'] . 'px;' : '';
			$css .= isset( $attrs['tabTitleTopSpacing'] ) && ! empty( $attrs['tabTitleTopSpacing'] ) ? 'margin-top:' . $attrs['tabTitleTopSpacing'] . 'px;' : '';
			$css .= '}';

			$css .= '}';
		}

		// responsive tab content 
		if ( isset( $attrs['tabContentTextSize'] ) || isset($attrs['tabContentSpacing']) || isset($attrs['tabContentLineHeight']) || isset($attrs['tabContentTopSpacing']) ) {
			$css .= '@media only screen and (max-width: 1024px) and (min-width: 768px) {';
			
			$css .= $unique_id . ' .post-item .post-item-inner-wrapper .post-content-area .post-excerpt p{';
			$css .= isset( $attrs['tabContentTextSize'] ) && ! empty( $attrs['tabContentTextSize'] ) ? 'font-size:' . $attrs['tabContentTextSize'] . 'px;' : '';
			$css .= isset( $attrs['tabContentSpacing'] ) && ! empty( $attrs['tabContentSpacing'] ) ? 'padding-right:' . $attrs['tabContentSpacing'] . 'px;' : '';
			$css .= isset( $attrs['tabContentLineHeight'] ) && ! empty( $attrs['tabContentLineHeight'] ) ? 'line-height:' . $attrs['tabContentLineHeight'] . 'px;' : '';
			$css .= isset( $attrs['tabContentTopSpacing'] ) && ! empty( $attrs['tabContentTopSpacing'] ) ? 'margin-top:' . $attrs['tabContentTopSpacing'] . 'px;' : '';
			$css .= '}';

			$css .= '}';
		}


        // responsive tab button 
		if ( isset( $attrs['tabButtonTextSize'] ) || isset($attrs['tabButtonSpacing']) || isset($attrs['tabButtonLineHeight']) || isset($attrs['tabButtonTopSpacing']) ) {
			$css .= '@media only screen and (max-width: 1024px) and (min-width: 768px) {';

			$css .= $unique_id . ' .post-item .post-item-inner-wrapper .post-content-area .post-read-moore{';
			$css .= isset( $attrs['tabButtonTextSize'] ) && ! empty( $attrs['tabButtonTextSize'] ) ? 'font-size:' . $attrs['tabButtonTextSize'] . 'px;' : '';
			$css .= isset( $attrs['tabButtonSpacing'] ) && ! empty( $attrs['tabButtonSpacing'] ) ? 'padding-right:' . $attrs['tabButtonSpacing'] . 'px;' : '';
			$css .= isset( $attrs['tabButtonLineHeight'] ) && ! empty( $attrs['tabButtonLineHeight'] ) ? 'line-height:' . $attrs['tabButtonLineHeight'] . 'px;' : '';
			$css .= isset( $attrs['tabButtonTopSpacing'] ) && ! empty( $attrs['tabButtonTopSpacing'] ) ? 'margin-top:' . $attrs['tabButtonTopSpacing'] . 'px;' : '';
			$css .= '}';

			$css .= '}';
		}

		// responsive mobile meta
		if ( isset( $attrs['mobileMetaTextSize'] ) || isset($attrs['mobileMetaSpacing']) || isset($attrs['mobileMetaLineHeight']) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id . ' .post-item .post-content-area .post-meta time, '.$unique_id . ' .post-item .post-content-area .post-meta a, '.$unique_id . ' .post-item .post-content-area .post-meta span.comment {';
			$css .= isset( $attrs['mobileMetaTextSize'] ) && ! empty( $attrs['mobileMetaTextSize'] ) ? 'font-size:' . $attrs['mobileMetaTextSize'] . 'px;' : '';
			$css .= isset( $attrs['mobileMetaSpacing'] ) && ! empty( $attrs['mobileMetaSpacing'] ) ? 'padding-right:' . $attrs['mobileMetaSpacing'] . 'px;' : '';
			$css .= isset( $attrs['mobileMetaLineHeight'] ) && ! empty( $attrs['mobileMetaLineHeight'] ) ? 'line-height:' . $attrs['tabMetaLineHeight'] . 'px;' : '';
			$css .= '}';
			$css .= '}';
		}

		// responsive mobile title
		if ( isset( $attrs['mobileTitleTextSize'] ) || isset($attrs['mobileTitleSpacing']) || isset($attrs['mobileTitleLineHeight']) || isset($attrs['mobileTitleTopSpacing']) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id . ' .post-item .post-item-inner-wrapper .post-content-area .post-title a{';
			$css .= isset( $attrs['mobileTitleTextSize'] ) && ! empty( $attrs['mobileTitleTextSize'] ) ? 'font-size:' . $attrs['mobileTitleTextSize'] . 'px;' : '';
			$css .= isset( $attrs['mobileTitleLineHeight'] ) && ! empty( $attrs['mobileTitleLineHeight'] ) ? 'line-height:' . $attrs['tabTitleLineHeight'] . 'px;' : '';
			$css .= isset( $attrs['mobileTitleTopSpacing'] ) && ! empty( $attrs['mobileTitleTopSpacing'] ) ? 'margin-top:' . $attrs['mobileTitleTopSpacing'] . 'px;' : '';
			$css .= '}';
			$css .= '}';
		}

		// responsive mobile content
		if ( isset( $attrs['mobileContentTextSize'] ) || isset($attrs['mobileContentSpacing']) || isset($attrs['mobileContentLineHeight']) || isset($attrs['mobileContentTopSpacing']) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id . ' .post-item .post-item-inner-wrapper .post-content-area .post-excerpt p{';
			$css .= isset( $attrs['mobileContentTextSize'] ) && ! empty( $attrs['mobileContentTextSize'] ) ? 'font-size:' . $attrs['mobileContentTextSize'] . 'px;' : '';
			$css .= isset( $attrs['mobileContentLineHeight'] ) && ! empty( $attrs['mobileContentLineHeight'] ) ? 'line-height:' . $attrs['tabContentLineHeight'] . 'px;' : '';
			$css .= isset( $attrs['mobileContentTopSpacing'] ) && ! empty( $attrs['mobileContentTopSpacing'] ) ? 'margin-top:' . $attrs['mobileContentTopSpacing'] . 'px;' : '';
			$css .= '}';
			$css .= '}';
		}

		// responsive mobile button
		if ( isset( $attrs['mobileButtonTextSize'] ) || isset($attrs['mobileButtonSpacing']) || isset($attrs['mobileButtonLineHeight']) || isset($attrs['mobileButtonTopSpacing']) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= $unique_id . ' .post-item .post-item-inner-wrapper .post-content-area .post-read-moore{';
			$css .= isset( $attrs['mobileButtonTextSize'] ) && ! empty( $attrs['mobileButtonTextSize'] ) ? 'font-size:' . $attrs['mobileButtonTextSize'] . 'px;' : '';
			$css .= isset( $attrs['mobileButtonLineHeight'] ) && ! empty( $attrs['mobileButtonLineHeight'] ) ? 'line-height:' . $attrs['tabButtonLineHeight'] . 'px;' : '';
			$css .= isset( $attrs['mobileButtonTopSpacing'] ) && ! empty( $attrs['mobileButtonTopSpacing'] ) ? 'margin-top:' . $attrs['mobileButtonTopSpacing'] . 'px;' : '';
			$css .= '}';
			$css .= '}';
		}
		
		
		return $css;
	}
	
	public function generate_button_css($attrs, $unique_id){
		if (!empty($attrs['buttons']) && is_array( $attrs['buttons'] )) {
			$css = "";

			if ( isset( $attrs['buttonGap'] ) && $attrs['buttonGap'] !== '' ) {
				$css .= $unique_id . ' .enhanced-single-button{';
				$css .= isset( $attrs['buttonGap'] ) && $attrs['buttonGap'] !== '' ? 'margin-right:' . $attrs['buttonGap'] . 'px;' : '';
				$css .= '}';
			}
			
			foreach( $attrs['buttons'] as $key => $value ) {
			
				$typography = isset($value['typography']) && !empty($value['typography']) ? $value['typography'] : '';
				$font_family = isset($value['googleFont']) && $typography !== '' ? '"'.$typography.'", sans-serif' : $typography;
				
				$selectedBgTypes = isset($value['selectedBgTypes']) ? $value['selectedBgTypes'] : 'classic';
				$selectedBgTypesHover = isset($value['selectedBgTypesHover']) ? $value['selectedBgTypesHover'] : 'classicHover';
				
				if ( $value['paddingTopDesktop'] !== '' || $value['paddingRightDesktop'] !== '' || $value['paddingBottomDesktop'] !== '' || $value['paddingLeftDesktop'] !== '' ) {
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].'{';
					$css .= isset( $value['paddingTopDesktop'] ) && $value['paddingTopDesktop'] !== '' ? 'padding-top:' . $value['paddingTopDesktop'] . 'px;' : '';
					$css .= isset( $value['paddingRightDesktop'] ) && $value['paddingRightDesktop'] !== ''  ? 'padding-right:' . $value['paddingRightDesktop'] . 'px;' : '';
					$css .= isset( $value['paddingBottomDesktop'] ) && $value['paddingBottomDesktop'] !== ''  ? 'padding-bottom:' . $value['paddingBottomDesktop'] . 'px;' : '';
					$css .= isset( $value['paddingLeftDesktop'] ) && $value['paddingLeftDesktop'] !== '' ? 'padding-left:' . $value['paddingLeftDesktop'] . 'px;' : '';
					$css .= '}';
				}
				
				if ( isset( $value['textColor'] ) || isset($font_family) || isset($value['fontWeight']) || isset($value['fontStyle']) || isset($value['desktopTextSize']) || isset($value['desktopLineHeight']) || isset( $value['textTransform'] ) ) {
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].'{';
					$css .= isset( $font_family ) && $font_family !== '' ? 'font-family:' . $font_family . ';' : '';
					$css .= isset( $value['fontWeight'] ) && $value['fontWeight'] !== '' ? 'font-weight:' . $value['fontWeight'] . ';' : '';
					$css .= isset( $value['fontStyle'] ) && $value['fontStyle'] !== '' ? 'font-style:' . $value['fontStyle'] . ';' : '';
					
						$css .= isset( $value['desktopTextSize'] ) && $value['desktopTextSize'] !== '' ? 'font-size:' . $value['desktopTextSize'] . 'px;' : '';
						$css .= isset( $value['desktopLineHeight'] ) && $value['desktopLineHeight'] !== '' ? 'line-height:' . $value['desktopLineHeight'] . 'px;' : '';
						$css .= ( (isset( $value['textColor'] ) && ! empty( $value['textColor'] ) ) && $this->getColor($value['textColor']) ) ? 'color:' . $this->getColor($value['textColor']) . ';' : '';
						$css .= isset( $value['textTransform'] ) && ! empty( $value['textTransform'] ) ? 'text-transform:' . $value['textTransform'] . ';' : '';
					$css .= '}';
				}
			

				if ( isset( $value['textHoverColor'] ) ) {
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].':hover{';
					$css .= ( (isset( $value['textHoverColor'] ) && ! empty( $value['textHoverColor'] )) && $this->getColor($value['textHoverColor']) ) ? 'color:' . $this->getColor($value['textHoverColor']) . ';' : '';
					$css .= '}';
				}
				
				if ( (isset( $value['iconColor'] ) ) || ($value['desktopIconSize'] !== '' && isset( $value['desktopIconSize'] )) || ($value['desktopIconGap'] !== '' && isset( $value['desktopIconGap'])) ) {
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].' .enhanced-icon{';
					$css .= isset( $value['desktopIconSize'] ) && $value['desktopIconSize'] !== '' ? 'font-size:' . $value['desktopIconSize'] . 'px;' : '';
					$css .= ( ( isset( $value['iconColor'] ) && ! empty( $value['iconColor'] ) ) && $this->getColor($value['iconColor']) ) ? 'color:' . $this->getColor($value['iconColor']) . ';' : '';
					$css .= ((isset( $value['desktopIconGap'] ) && $value['desktopIconGap'] !== '' ) && ($value['iconPosition'] !== '' && $value['iconPosition'] === 'right') ) ? 'padding-left:' . $value['desktopIconGap'] . 'px;' : '';
					$css .= ((isset( $value['desktopIconGap'] ) && $value['desktopIconGap'] !== '' ) && ($value['iconPosition'] !== '' && $value['iconPosition'] === 'left') ) ? 'padding-right:' . $value['desktopIconGap'] . 'px;' : '';
					$css .= '}';
				}
				

				if ( isset( $value['iconHoverColor'] ) ) {
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].':hover .enhanced-icon{';
					$css .= ((isset( $value['iconHoverColor'] ) && ! empty( $value['iconHoverColor'] )) && $this->getColor($value['iconHoverColor']) ) ? 'color:' . $this->getColor($value['iconHoverColor']) . ';' : '';
					$css .= '}';
				}


				if ( isset( $value['borderTypeNormal'] ) || isset( $value['borderRadiusNormalTop'] ) || isset( $value['borderRadiusNormalRight'] ) || isset( $value['borderRadiusNormalLeft'] ) || isset( $value['borderRadiusNormalBottom'] )  ) {
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].'{';
					$css .= isset( $value['borderTypeNormal'] ) && ! empty( $value['borderTypeNormal'] ) ? 'border-style:' . $value['borderTypeNormal'] . ';' : '';
					$css .= $this->getColor($value['borderColorNormal']) ? 'border-color:' . $this->getColor($value['borderColorNormal']) . ';' : '';
					$css .= isset( $value['borderWidthNormalTop'] ) ? 'border-top-width:' . $value['borderWidthNormalTop'] . 'px;' : '';
					$css .= isset( $value['borderWidthNormalRight'] ) ? 'border-right-width:' . $value['borderWidthNormalRight'] . 'px;' : '';
					$css .= isset( $value['borderWidthNormalBottom'] )  ? 'border-bottom-width:' . $value['borderWidthNormalBottom'] . 'px;' : '';
					$css .= isset( $value['borderWidthNormalLeft'] ) ? 'border-left-width:' . $value['borderWidthNormalLeft'] . 'px;' : '';
					$css .= isset( $value['borderRadiusNormalTop'] ) && ! empty( $value['borderRadiusNormalTop'] ) ? 'border-top-left-radius:' . $value['borderRadiusNormalTop'] . 'px;' : 'border-top-left-radius: 0px;';
					$css .= isset( $value['borderRadiusNormalRight'] ) && ! empty( $value['borderRadiusNormalRight'] ) ? 'border-top-right-radius:' . $value['borderRadiusNormalRight'] . 'px;' : 'border-top-right-radius: 0px;';
					$css .= isset( $value['borderRadiusNormalLeft'] ) && ! empty( $value['borderRadiusNormalLeft'] )? 'border-bottom-left-radius:' . $value['borderRadiusNormalLeft'] . 'px;' : 'border-bottom-left-radius: 0px;';
					$css .= isset( $value['borderRadiusNormalBottom'] ) && ! empty( $value['borderRadiusNormalBottom'] ) ? 'border-bottom-right-radius:' . $value['borderRadiusNormalBottom'] . 'px;' : 'border-bottom-right-radius: 0px;';
					$css .= '}';
				}
				
				// background 
				if ( isset( $value['bgColor'] ) || isset( $value['gradientColor'] ) || isset($value['gradientSecondColor']) ) {
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].'{';
					$css .= $this->getColor($value['bgColor']) ? 'background-color:' . $this->getColor($value['bgColor']) . ';' : '';
					$css .= $selectedBgTypes === 'gradients' ? $this->getGradients($value) : '';
					$css .= '}';
				}

				if ( isset( $value['bgColorHover'] ) || isset( $value['gradientColorHover'] ) || isset($value['gradientSecondColorHover']) ) {
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].':hover{';
					$css .=  $this->getColor($value['bgColorHover']) ? 'background-color:' . $this->getColor($value['bgColorHover']) . ';' : '';
					$css .= $selectedBgTypesHover === 'gradientsHover' ? $this->getGradientsHover($value) : '';
					$css .= '}';
				}

				if ( isset( $value['borderColorHover'] ) ) {
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].':hover{';
					$css .= $this->getColor($value['borderColorHover']) ? 'border-color:' . $this->getColor($value['borderColorHover']) . ';' : '';
					$css .= '}';
				}


				// tab responsive 
				if ( isset( $value['tabTextSize'] ) || isset( $value['tabLineHeight']) ) {
					$css .= '@media only screen and (max-width: 1024px) and (min-width: 768px) {';
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].'{';
					$css .= isset( $value['tabTextSize'] ) && $value['tabTextSize'] !== '' ? 'font-size:' . $value['tabTextSize'] . 'px;' : '';
					$css .= isset( $value['tabLineHeight'] ) && $value['tabLineHeight'] !== '' ? 'line-height:' . $value['tabLineHeight'] . 'px;' : '';
					$css .= '}';
					$css .= '}';
				}
				
				if ( isset( $value['tabIconSize'] ) || isset( $value['tabIconGap']) ) {
					$css .= '@media only screen and (max-width: 1024px) and (min-width: 768px) {';
						$css .= $unique_id . ' .enhanced-button-'. $value['id'].' .enhanced-icon{';
						$css .= isset( $value['tabIconSize'] ) && $value['tabIconSize'] !== '' ? 'font-size:' . $value['tabIconSize'] . 'px;' : '';
						$css .= ((isset( $value['tabIconGap'] ) && $value['tabIconGap'] !== '' ) && ($value['iconPosition'] !== '' && $value['iconPosition'] === 'right') ) ? 'padding-left:' . $value['tabIconGap'] . 'px;' : '';
						$css .= ((isset( $value['tabIconGap'] ) && $value['tabIconGap'] !== '' ) && ($value['iconPosition'] !== '' && $value['iconPosition'] === 'left') ) ? 'padding-right:' . $value['tabIconGap'] . 'px;' : '';
						$css .= '}';
					$css .= '}';
				}

				if ( $value['paddingTopTab'] !== '' || $value['paddingRightTab'] !== '' || $value['paddingBottomTab'] !== '' || $value['paddingLeftTab'] !== '' ) {
					$css .= '@media only screen and (max-width: 1024px) and (min-width: 768px) {';
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].'{';
					$css .= isset( $value['paddingTopTab'] ) && $value['paddingTopTab'] !== '' ? 'padding-top:' . $value['paddingTopTab'] . 'px;' : '';
					$css .= isset( $value['paddingRightTab'] ) && $value['paddingRightTab'] !== ''  ? 'padding-right:' . $value['paddingRightTab'] . 'px;' : '';
					$css .= isset( $value['paddingBottomTab'] ) && $value['paddingBottomTab'] !== ''  ? 'padding-bottom:' . $value['paddingBottomTab'] . 'px;' : '';
					$css .= isset( $value['paddingLeftTab'] ) && $value['paddingLeftTab'] !== '' ? 'padding-left:' . $value['paddingLeftTab'] . 'px;' : '';
					$css .= '}';
					$css .= '}';
				}


				// mobile responsive 
				if ( isset( $value['mobileTextSize'] ) || isset( $value['mobileLineHeight']) ) {
					$css .= '@media (max-width: 767px) {';
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].'{';
					$css .= isset( $value['mobileTextSize'] ) && $value['mobileTextSize'] !== '' ? 'font-size:' . $value['mobileTextSize'] . 'px;' : '';
					$css .= isset( $value['mobileLineHeight'] ) && $value['mobileLineHeight'] !== '' ? 'line-height:' . $value['mobileLineHeight'] . 'px;' : '';
					$css .= '}';
					$css .= '}';
				}

				if ( isset( $value['mobileIconSize'] ) || isset( $value['mobileIconGap']) ) {
					$css .= '@media (max-width: 767px) {';
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].' .enhanced-icon{';
					$css .= isset( $value['mobileIconSize'] ) && $value['mobileIconSize'] !== '' ? 'font-size:' . $value['mobileIconSize'] . 'px;' : '';
					$css .= ((isset( $value['mobileIconGap'] ) && $value['mobileIconGap'] !== '' ) && ($value['iconPosition'] !== '' && $value['iconPosition'] === 'right') ) ? 'padding-left:' . $value['mobileIconGap'] . 'px;' : '';
					$css .= ((isset( $value['mobileIconGap'] ) && $value['mobileIconGap'] !== '' ) && ($value['iconPosition'] !== '' && $value['iconPosition'] === 'left') ) ? 'padding-right:' . $value['mobileIconGap'] . 'px;' : '';
					$css .= '}';
					$css .= '}';
				}

				if ( $value['paddingTopMobile'] !== '' || $value['paddingRightMobile'] !== '' || $value['paddingBottomMobile'] !== '' || $value['paddingLeftMobile'] !== '' ) {
					$css .= '@media (max-width: 767px) {';
					$css .= $unique_id . ' .enhanced-button-'. $value['id'].'{';
					$css .= isset( $value['paddingTopMobile'] ) && $value['paddingTopMobile'] !== '' ? 'padding-top:' . $value['paddingTopMobile'] . 'px;' : '';
					$css .= isset( $value['paddingRightMobile'] ) && $value['paddingRightMobile'] !== ''  ? 'padding-right:' . $value['paddingRightMobile'] . 'px;' : '';
					$css .= isset( $value['paddingBottomMobile'] ) && $value['paddingBottomMobile'] !== ''  ? 'padding-bottom:' . $value['paddingBottomMobile'] . 'px;' : '';
					$css .= isset( $value['paddingLeftMobile'] ) && $value['paddingLeftMobile'] !== '' ? 'padding-left:' . $value['paddingLeftMobile'] . 'px;' : '';
					$css .= '}';
					$css .= '}';
				}
				
				
			}
		
			return $css;
		}
	}



	public function button_gfont( $attrs ){

		if (!empty($attrs['buttons']) && is_array( $attrs['buttons'] ) ) {
			foreach( $attrs['buttons'] as $key => $value ) {
				if ( isset( $value['googleFont'] ) && $value['googleFont'] && ( ! isset( $value['loadGoogleFont'] ) || true == $value['loadGoogleFont'] ) && isset( $value['typography'] ) ) {
					// Check if the font has been added yet.
					if ( ! array_key_exists( $value['typography'], self::$gfonts ) ) {
						$add_font = array(
							'fontfamily' => $value['typography'],
							'fontvariants' => ( isset( $value['fontVariant'] ) && ! empty( $value['fontVariant'] ) ? array( $value['fontVariant'] ) : array() ),
							'fontsubsets' => ( isset( $value['fontSubset'] ) && ! empty( $value['fontSubset'] ) ? array( $value['fontSubset'] ) : array() ),
						);
						self::$gfonts[ $value['typography'] ] = $add_font;
					} else {
						if ( isset( $value['fontVariant'] ) && ! empty( $value['fontVariant'] ) ) {
							if ( ! in_array( $value['fontVariant'], self::$gfonts[ $value['typography'] ]['fontvariants'], true ) ) {
								array_push( self::$gfonts[ $value['typography'] ]['fontvariants'], $value['fontVariant'] );
							}
						}
						if ( isset( $value['fontSubset'] ) && ! empty( $value['fontSubset'] ) ) {
							if ( ! in_array( $value['fontSubset'], self::$gfonts[ $value['typography'] ]['fontsubsets'], true ) ) {
								array_push( self::$gfonts[ $value['typography'] ]['fontsubsets'], $value['fontSubset'] );
							}
						}
					}
				}
				
			}
		}
	}


}

Enhanced_Blocks_Front_End_Css::get_instance();
