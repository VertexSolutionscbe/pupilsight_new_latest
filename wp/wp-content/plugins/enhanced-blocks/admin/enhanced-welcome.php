<?php

if( ! defined('ABSPATH') ){
    exit;
}

class enhanced_welcome_page{

    private static $instance = null;

    public static function get_instance(){
        if( is_null( self::$instance ) ){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct(){
        if( is_admin() ){
            add_action('admin_menu', array($this, 'add_menu') );
            add_action( 'admin_init', array( $this, 'activation_redirect' ) );
            add_action( 'admin_enqueue_scripts', array($this, 'enhanced_script')  );
        }
    }

    public function add_menu(){
        add_menu_page(
            __('Enhanced Blocks - Page Builder blocks for Gutenberg Editor', 'enhanced-blocks'),
            __('Enhanced Blocks'),
            'manage_options',
            'enhanced_blocks',
            array( $this, 'config_page' ),
            'data:image/svg+xml;base64,'
		       . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><defs><style>.cls-1{fill:#fff;}</style></defs><title>Asset 1</title><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><circle class="cls-1" cx="210.86" cy="44.87" r="12.79"/><path class="cls-1" d="M100.85,0A100.85,100.85,0,0,0,0,100.85V256H155.15A100.85,100.85,0,0,0,256,155.15V0Zm-59,149.49-8.29,8.28a4.83,4.83,0,1,1-6.83-6.83l8.28-8.28a4.83,4.83,0,1,1,6.84,6.83Zm34.86,53.16a9.31,9.31,0,0,1,0-13.13l10.69-10.69A9.28,9.28,0,0,1,100.57,192L89.88,202.65A9.31,9.31,0,0,1,76.75,202.65Zm35.92,18.92L105.22,229a5.75,5.75,0,0,1-8.13-8.13l7.45-7.45a5.75,5.75,0,0,1,8.13,8.13ZM123.24,211a4.84,4.84,0,0,1-6.83,0l-1.23-1.24a4.81,4.81,0,0,1,0-6.8l31.07-31.48a8.4,8.4,0,0,0,0-11.88l-.89-.89a8.4,8.4,0,0,0-11.88,0l-16.94,16.94a8.41,8.41,0,0,1-11.89,0l-1.06-1.06a8.41,8.41,0,0,1,0-11.89l15.08-15.08a8.4,8.4,0,0,0,0-11.88l-.89-.89a8.41,8.41,0,0,0-11.89,0L90.8,149.93a8.42,8.42,0,0,1-13-1.35,8.61,8.61,0,0,1,1.33-10.76l16.71-16.71a8.41,8.41,0,0,0,0-11.89l-.88-.88a8.4,8.4,0,0,0-11.89,0L51.8,139.56A4.83,4.83,0,0,1,45,132.73L95.13,82.58l78.26,78.27Zm52.54-52.54L97.52,80.19l3.28-3.28,78.27,78.26Zm48.34-95.19a22.15,22.15,0,0,1-11.47,6.1,28.49,28.49,0,0,0-8.17,2.82,65.85,65.85,0,0,0-10.35,7.18c-10.3,11.06-12.26,20-12.25,26,0,16,14.07,26.16,9.68,37.63-1.91,5-6.41,7.87-9.38,9.38L143,113.18h0L103.72,73.94c1.51-3,4.39-7.47,9.38-9.38,11.47-4.39,21.58,9.68,37.63,9.68,6,0,15-1.95,26-12.25a75.27,75.27,0,0,0,5.62-7.69,31.65,31.65,0,0,0,4.17-11,22.25,22.25,0,1,1,37.58,19.94Z"/></g></g></svg>' ),
            81
        );
    }

    public function config_page(){
    ?> 
        <div class="enhanced-welcome-container"> 
            <div class="enhanced-welcome-tab enhanced-panel-contain"> 
                <h2 class="nav-tab-wrapper">
                    <a class="nav-tab nav-tab-active nav-tab-link" data-tab-id="en-dashboard" href="#"><?php echo esc_html__( 'Dashboard', 'enhanced-blocks' ); ?></a>
                    <a class="nav-tab nav-tab-link" data-tab-id="en-help" href="#"><?php echo esc_html__( 'Help', 'enhanced-blocks' ); ?></a>
                    <a class="nav-tab nav-tab-link" data-tab-id="en-review" href="#"><?php echo esc_html__( 'Review', 'enhanced-blocks' ); ?></a>
                </h2>
                <div class="enhanced-wrapper">
                    <!-- dashboard page -->
                    <div class="nav-tab-content panel_open" id="en-dashboard"> 
                        <div class="enhanced-welcome-header"> 
                            <h1 class="title">Welcome to Enhanced Blocks - Page Builder Block for Gutenberg !</h1> 
                        </div>
                        <!-- Features -->
                        <div class="enhanced-section"> 
                            <span class="enhanced-features"> Features </span> 
                            <div class="enhanced-row">
                                <div class="enhanced-columns-8">
                                    <div class="enhanced-features-list"> 
                                        <ul class="enhanced-lists"> 
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Row Layout Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Post Grid Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Heading Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Button Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Icon Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Testimonial Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> List Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Image Comparison Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Profile Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Social Sharing Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Call To Action Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Notice Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Spacer Block </li>
                                            <li class="enhanced-list-item"> <i class="fas fa-check"> </i> Divider Block </li>
                                        </ul>    
                                    </div>    
                                </div>
                                <div class="enhanced-columns-1">
                                    <div class="enhanced-features-image"> 
                                        <img class="enhanced-embed-responsive-item" src="<?php echo plugins_url( 'admin/img/enhanced-image1.png', dirname( __FILE__ ));?>"/>
                                        <!-- <iframe class="enhanced-embed-responsive-item" width="560" height="315" src="https://www.youtube.com/embed/uLHMInk1DFs" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> -->
                                    </div> 
                                </div>
                            </div>
                            <div class="enhanced-row"> 
                                <div class="enhanced-columns-8"> 
                                    <p class="enhanced-documentation">
                                        <a href="#" class="button button-primary"> Documentation </a>
                                    </p>
                                </div>       
                            </div>
                        </div>
                        <!-- Overview -->
                        <div class="enhanced-section"> 
                            <span class="enhanced-features"> Overview </span> 
                                <div class="enhanced-row">
                                    <div class="enhanced-columns-1">
                                        <div class="enhanced-overview-image"> 
                                            <img class="enhanced-responsive-image" src="<?php echo plugins_url( 'admin/img/enhanced-image2.png', dirname( __FILE__ ));?>"/>
                                            <!-- <img class="enhanced-responsive-image" src="https://ps.w.org/enhanced-blocks/assets/screenshot-1.png?rev=2093669"/> -->
                                        </div>    
                                    </div>
                                    <div class="enhanced-columns-8">
                                        <div class="enhanced-overview-text"> 
                                            <p class="enhanced-overview-description">
                                                Enhanced Blocks is the most powerful page builder kit for Gutenberg Editor AKA WordPress Block Editor. In a few clicks, you can build awesome and professional websites just using our Enhanced Blocks.
                                            </p>
                                            <p class="enhanced-overview-description">
                                                Either you are a professional web developer or even a WordPress noob, it does not block your path to create websites as we have been working to make your life easier while building your WordPress sites; Yes, we are now giving you a free page builder based on Gutenberg Block editor! The most exciting feature of this plugin is you might not need any other plugins to make your website as you think of. Click here to see the Gutenberg Block live demos 
                                            </p>
                                            <a href="https://gutendev.com/plugins/enhanced-blocks" target="_blank"class="enhanced-demo">Click here to see the Gutenberg Block live demos</a>
                                        </div> 
                                    </div>
                                </div>
                        </div>
                        <!-- Available Blocks -->
                        <div class="enhanced-section"> 
                            <span class="enhanced-features"> Available Blocks </span> 
                            <div class="enhanced-row en-ml-minus en-mr-minus">
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/row-layout.png', dirname( __FILE__ ));?>"/>
                                        <h2 class="enhanced-block-title"> Row Layout Block </h2>
                                        <a href="https://gutendev.com/plugins/row-layout-block/" class=""  target=_blank> View Demo </a> 
                                    </div>
                                    <div class="enhanced-col"> 
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/post-grid.png', dirname( __FILE__ ));?>"/>
                                        <h2 class="enhanced-block-title"> Post Grid Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/heading.png', dirname( __FILE__ ));?>"/>                                        
                                        <h2 class="enhanced-block-title"> Heading Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/button.png', dirname( __FILE__ ));?>"/>                                        
                                        <h2 class="enhanced-block-title"> Button Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/icon.png', dirname( __FILE__ ));?>"/>                                        
                                        <h2 class="enhanced-block-title"> Icon Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/testimonial.png', dirname( __FILE__ ));?>"/>                                        
                                        <h2 class="enhanced-block-title"> Testimonial Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/list.png', dirname( __FILE__ ));?>"/>                                        
                                        <h2 class="enhanced-block-title"> List Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/image-comparison.png', dirname( __FILE__ ));?>"/>                                        
                                        <h2 class="enhanced-block-title"> Image Comparison Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/profile.png', dirname( __FILE__ ));?>"/>                                        
                                        <h2 class="enhanced-block-title"> Profile Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/social-sharing.png', dirname( __FILE__ ));?>"/>  
                                        <h2 class="enhanced-block-title"> social Sharing Block </h2>
                                        <a href="https://gutendev.com/plugins/social-sharing-block/" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/call-to-action.png', dirname( __FILE__ ));?>"/>  
                                        <h2 class="enhanced-block-title"> Call To Action Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/notice.png', dirname( __FILE__ ));?>"/>   
                                        <h2 class="enhanced-block-title"> Notice Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/divider.png', dirname( __FILE__ ));?>"/>    
                                        <h2 class="enhanced-block-title"> Divider Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                                    <div class="enhanced-col">
                                        <img class="enhanced-block-image" src="<?php echo plugins_url( 'admin/img/spacer.png', dirname( __FILE__ ));?>"/>     
                                        <h2 class="enhanced-block-title"> Spacer Block </h2>
                                        <a href="#" class="" target=_blank> View Demo </a>
                                    </div>
                            </div>
                        </div>
                    </div>
                    <!-- help page -->
                    <div class="nav-tab-content" id="en-help"> 
                       <div class="enhanced-section"> 
                            <div class="enhanced-row en-mt-minus">
                                <div class="enhanced-columns-8">
                                    <h1 class="enhanced-help-title">
                                         Gatting Started with Enhanced Blocks – Page Builder Blocks for Gutenberg ! 
                                    </h1>
                                    <p class="enhanced-help-description">
                                      
                                    </p>
                                    <!-- <ul class="help-lists"> 
                                            <li class="help-list-item"> 
                                              <a href=""> Getting Started with WPPayForm </a>
                                            </li>

                                            <li class="help-list-item child"> 
                                              <a href=""> WPPayForm Introduction </a>
                                            </li>

                                            <li class="help-list-item child"> 
                                              <a href=""> Install and Activate – WPPayForm </a>
                                            </li>

                                            <li class="help-list-item child"> 
                                              <a href=""> Configure Payment Methods and Currency </a>
                                            </li>

                                            <li class="help-list-item"> 
                                              <a href=""> Form Configuration – WPPayForm </a>
                                            </li>

                                            <li class="help-list-item child"> 
                                              <a href=""> Create your first Payment Form under a minute and accept payments with WPPayForm </a>
                                            </li>


                                            <li class="help-list-item child"> 
                                              <a href=""> 	Form Input Fields – WPPayForm </a>
                                            </li>

                                            <li class="help-list-item child"> 
                                              <a href=""> Form Confirmation Settings – WPPayForm  </a>
                                            </li>

                                            <li class="help-list-item child"> 
                                              <a href=""> Custom Currency Settings – WPPayForm </a>
                                            </li>

                                            <li class="help-list-item child"> 
                                              <a href=""> Form Design Settings in WPPayForm </a>
                                            </li>

                                            <li class="help-list-item child"> 
                                              <a href=""> Form Scheduling and Restriction Settings in WPPayForm  </a>
                                            </li>

                                            <li class="help-list-item child"> 
                                              <a href="">  Custom CSS / JS in WPPayForm </a>
                                            </li>

                                            <li class="help-list-item"> 
                                              <a href=""> Managing Form Entries in WPPayForm  </a>
                                            </li>

                                            <li class="help-list-item child"> 
                                              <a href=""> View and Manage all Form Entries in WPPayForm </a>
                                            </li>
	
                                            <li class="help-list-item child"> 
                                              <a href=""> View Single Submission Data and Managing Payments in WPPayForm  </a>
                                            </li>

                                            <li class="help-list-item"> 
                                              <a href=""> Frequently Asked Questions for WPPayForm  </a>
                                            </li>
                                            <li class="help-list-item child"> 
                                              <a href=""> Date Formats Customization </a>
                                            </li>
	                                    </ul> -->
                                </div>
                                <div class="enhanced-columns-1">
                                    <p class="enhanced-help-documentation">
                                        <a href="#" class="button button-primary"> Documentation </a>
                                    </p>
                                    <img class="enhanced-block-help-image" src="<?php echo plugins_url( 'admin/img/help-service.png', dirname( __FILE__ ));?>"/>
                                </div>
                            </div>
                       </div>
                    </div>
                    <!-- review page -->
                    <div class="nav-tab-content" id="en-review"> 
                       <div class="enhanced-section"> 
                            <div class="enhanced-row en-mt-minus">
                                <div class="enhanced-columns-8">
                                    <h1 class="enhanced-review-title">
                                        Give Your Valuable FeedBack
                                    </h1>
                                    <p class="enhanced-review-description">
                                        
                                    </p>
                                    <p class="enhanced-review-btn">
                                        <a href="https://wordpress.org/support/plugin/enhanced-blocks/reviews/#new-post" class="button button-primary" target="_blank"> Post Your Review </a>
                                    </p>
                                </div>
                                <div class="enhanced-columns-1">
                                    <img class="enhanced-block-review-image" src="<?php echo plugins_url( 'admin/img/review.png', dirname( __FILE__ ));?>"/>
                                </div>
                            </div>
                       </div>
                    </div>                    
                </div>
            </div> 
        </div>
    <?php
    }


    public function enhanced_script(){
        wp_enqueue_style(
            'enhanced-blocks-admin-css', // Handle.
            plugins_url( 'admin/css/styles.css', dirname( __FILE__ ) ), //css
            array(), // Dependency to include the CSS after it.
            ENHANCED_BLOCKS_VERSION // Version: File modification time.
        );

        wp_enqueue_script(
            'enhanced-blocks-admin-js', 
            plugins_url( 'admin/js/script.js', dirname( __FILE__ ) ), //js
            array('jquery'), 
            ENHANCED_BLOCKS_VERSION 
        );
    }

    public function activation_redirect() {
		if ( get_option( 'enhanced_blocks_redirect_on_activation', false ) ) {
			delete_option( 'enhanced_blocks_redirect_on_activation' );
			if ( ! isset( $_GET['activate-multi'] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=enhanced_blocks' ) );
			}
		}
	}

}

enhanced_welcome_page::get_instance();