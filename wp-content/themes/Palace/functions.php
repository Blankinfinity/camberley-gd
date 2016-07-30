<?php
/**
 * Theme Functions
 *
 * @package Betheme
 * @author Muffin group
 * @link http://muffingroup.com
 */


define( 'THEME_DIR', get_template_directory() );
define( 'THEME_URI', get_template_directory_uri() );

define( 'THEME_NAME', 'betheme' );
define( 'THEME_VERSION', '10.5' );

define( 'LIBS_DIR', THEME_DIR. '/functions' );
define( 'LIBS_URI', THEME_URI. '/functions' );
define( 'LANG_DIR', THEME_DIR. '/languages' );

add_filter( 'widget_text', 'do_shortcode' );


/* ---------------------------------------------------------------------------
 * White Label
 * IMPORTANT: We recommend the use of Child Theme to change this
 * --------------------------------------------------------------------------- */
defined( 'WHITE_LABEL' ) or define( 'WHITE_LABEL', false );


/* ---------------------------------------------------------------------------
 * Loads Theme Textdomain
 * --------------------------------------------------------------------------- */
load_theme_textdomain( 'betheme',  LANG_DIR );
load_theme_textdomain( 'mfn-opts', LANG_DIR );


/* ---------------------------------------------------------------------------
 * Loads the Options Panel
 * --------------------------------------------------------------------------- */
if( ! function_exists( 'mfn_admin_scripts' ) )
{
	function mfn_admin_scripts() {
		wp_enqueue_script( 'jquery-ui-sortable' );
	}
}   
add_action( 'wp_enqueue_scripts', 'mfn_admin_scripts' );
add_action( 'admin_enqueue_scripts', 'mfn_admin_scripts' );
	
require( THEME_DIR .'/muffin-options/theme-options.php' );

$theme_disable = mfn_opts_get( 'theme-disable' );


/* ---------------------------------------------------------------------------
 * Loads Theme Functions
 * --------------------------------------------------------------------------- */

// Functions --------------------------------------------------------------------
require_once( LIBS_DIR .'/theme-functions.php' );

// Header -----------------------------------------------------------------------
require_once( LIBS_DIR .'/theme-head.php' );

// Menu -------------------------------------------------------------------------
require_once( LIBS_DIR .'/theme-menu.php' );
if( ! isset( $theme_disable['mega-menu'] ) ){
	require_once( LIBS_DIR .'/theme-mega-menu.php' );
}

// Meta box ---------------------------------------------------------------------

require_once( LIBS_DIR .'/builder/back.php' );
require_once( LIBS_DIR .'/builder/front.php' );

// Custom post types ------------------------------------------------------------
$post_types_disable = mfn_opts_get( 'post-type-disable' );

if( ! isset( $post_types_disable['client'] ) ){
	require_once( LIBS_DIR .'/meta-client.php' );
}
if( ! isset( $post_types_disable['offer'] ) ){
	require_once( LIBS_DIR .'/meta-offer.php' );
}
if( ! isset( $post_types_disable['portfolio'] ) ){
	require_once( LIBS_DIR .'/meta-portfolio.php' );
}
if( ! isset( $post_types_disable['slide'] ) ){
	require_once( LIBS_DIR .'/meta-slide.php' );
}
if( ! isset( $post_types_disable['testimonial'] ) ){
	require_once( LIBS_DIR .'/meta-testimonial.php' );
}

if( ! isset( $post_types_disable['layout'] ) ){
	require_once( LIBS_DIR .'/meta-layout.php' );
}
if( ! isset( $post_types_disable['template'] ) ){
	require_once( LIBS_DIR .'/meta-template.php' );
}

require_once( LIBS_DIR .'/meta-page.php' );
require_once( LIBS_DIR .'/meta-post.php' );

// Content ----------------------------------------------------------------------
require_once( THEME_DIR .'/includes/content-post.php' );
require_once( THEME_DIR .'/includes/content-portfolio.php' );

// Shortcodes -------------------------------------------------------------------
require_once( LIBS_DIR .'/theme-shortcodes.php' );

// Hooks ------------------------------------------------------------------------
require_once( LIBS_DIR .'/theme-hooks.php' );

// Widgets ----------------------------------------------------------------------
require_once( LIBS_DIR .'/widget-functions.php' );

require_once( LIBS_DIR .'/widget-flickr.php' );
require_once( LIBS_DIR .'/widget-login.php' );
require_once( LIBS_DIR .'/widget-menu.php' );
require_once( LIBS_DIR .'/widget-recent-comments.php' );
require_once( LIBS_DIR .'/widget-recent-posts.php' );
require_once( LIBS_DIR .'/widget-tag-cloud.php' );

// TinyMCE ----------------------------------------------------------------------
require_once( LIBS_DIR .'/tinymce/tinymce.php' );

// Plugins ---------------------------------------------------------------------- 
if( ! isset( $theme_disable['demo-data'] ) ){
	require_once( LIBS_DIR .'/importer/import.php' );
}

require_once( LIBS_DIR .'/class-love.php' );
require_once( LIBS_DIR .'/class-tgm-plugin-activation.php' );

require_once( LIBS_DIR .'/plugins/visual-composer.php' );

// WooCommerce specified functions
if( function_exists( 'is_woocommerce' ) ){
	require_once( LIBS_DIR .'/theme-woocommerce.php' );
}

// Hide activation and update specific parts ------------------------------------

// Slider Revolution
if( ! mfn_opts_get( 'plugin-rev' ) ){
	if( function_exists( 'set_revslider_as_theme' ) ){
		set_revslider_as_theme();
	}
}

// LayerSlider
if( ! mfn_opts_get( 'plugin-layer' ) ){
	add_action('layerslider_ready', 'mfn_layerslider_overrides');
	function mfn_layerslider_overrides() {
		// Disable auto-updates
		$GLOBALS['lsAutoUpdateBox'] = false;
	}
}

// Visual Composer 
if( ! mfn_opts_get( 'plugin-visual' ) ){
	add_action( 'vc_before_init', 'mfn_vcSetAsTheme' );
	function mfn_vcSetAsTheme() {
		vc_set_as_theme();
	}
}

function mfn_migrate_menu() {
    add_submenu_page(
    'tools.php',
    'Muffin Content Builder Migrate Tool',
    'Mfn CB Migrate Tool',
    'edit_theme_options',
    'mfn_migrate_cb',
    'mfn_migrate_cb'
            );
}
add_action('admin_menu', 'mfn_migrate_menu');

function mfn_migrate_cb(){

    global $wpdb;

    $safety_limit = 10;

    if( key_exists( 'mfn_migrate_nonce',$_POST ) ) {
        if ( wp_verify_nonce( $_POST['mfn_migrate_nonce'], basename(__FILE__) ) ) {
               
            $old_url = stripslashes(htmlspecialchars($_POST['old']));
            $new_url = stripslashes(htmlspecialchars($_POST['new']));
               
            if( strlen($old_url) < $safety_limit || strlen($new_url) < $safety_limit ){

                echo '<p><strong>For your own safety please use URLs longer than '. $safety_limit .' characters !</strong></p>';

            } elseif( strpos( $old_url, 'http' ) !== 0 || strpos( $new_url, 'http' !== 0 )  ){

                echo '<p><strong>URLs must begin with http:// or https:// !</strong></p>';

            } else {

                $results = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta
                        WHERE `meta_key` = 'mfn-page-items'
                        " );
                   
                if( is_array( $results ) ){
                    // posts loop -----------------
                    foreach( $results as $result_key=>$result ){
                        $meta_id = $result->meta_id;
                        $meta_value = unserialize(base64_decode($result->meta_value));

                        // meta items loop ----------------
                        $meta_value = recursive_array_replace($old_url, $new_url, $meta_value);

                        $meta_value = base64_encode(serialize($meta_value));
                        $wpdb->query( "UPDATE $wpdb->postmeta
                                SET `meta_value` = '". addslashes($meta_value) ."'
                            WHERE `meta_key` = 'mfn-page-items'
                            AND `meta_id`= ". $meta_id ."
                        ");
                    }
                }
                echo '<p><strong>All done. Have fun!</strong></p>';

            }
            } else {
            echo '<p><strong>Invalid Nonce !</strong></p>';
            }
            }

            ?>
        <div class="wrap">

            <div id="icon-tools" class="icon32"></div>
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
            <br />
           
            <form action="" method="post">
               
                <input type="hidden" name="mfn_migrate_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)); ?>" />
               
                <label style="width:50px; display:inline-block;">Find</label>
                <input type="text" name="old" value="" placeholder="Old URL" style="width:300px;" />
                <br />
               
                <label style="width:50px; display:inline-block;">Replace</label>
                <input type="text" name="new" value="<?php echo home_url(); ?>" style="width:300px;" />
               
                <input type="submit" name="submit" class="button button-primary" value="Replace" />
               
            </form>

        </div>
    <?php
}

?>
<?php

// Recursive String Replace - recursive_array_replace(mixed, mixed, array);
function recursive_array_replace($find, $replace, $array){
    if (!is_array($array)) {
        return str_replace($find, $replace, $array);
    }

    $newArray = array();

    foreach ($array as $key => $value) {
        $newArray[$key] = recursive_array_replace($find, $replace, $value);
    }

    return $newArray;
}

?>