<?php
/**
 * Plugin Name: Simple Announcement Bar
 * Plugin URI:  https://wordpress.org/plugins/simple-announcement-bar/
 * Description: A lightweight, customizable announcement bar with advanced positioning, scheduling, and targeting.
 * Version:     2.1.0
 * Author:      Safa Tash (NOVA Advertising)
 * Author URI:  https://www.novaadvertising.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-announcement-bar
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Tested up to:      6.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'SIMPANBAR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIMPANBAR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SIMPANBAR_VERSION', '2.1.0' );

register_uninstall_hook( __FILE__, 'simpanbar_uninstall' );
function simpanbar_uninstall() {
    delete_option( 'simpanbar_settings' );
}

// Include admin settings page
if ( is_admin() ) {
    require_once SIMPANBAR_PLUGIN_DIR . 'admin/settings-page.php';
}

/**
 * Default settings array
 */
function simpanbar_get_default_settings() {
    return array(
        // General
        'message' => 'Welcome to our website! Check out our latest offers.',
        'btn_text' => 'Learn More',
        'btn_url' => '#',
        
        // Visibility
        'show_on' => 'entire_site', // entire_site, homepage, posts, pages, specific
        'specific_ids' => '',
        'exclude_ids' => '',
        'hide_mobile' => 0,
        'hide_desktop' => 0,
        'show_limit' => 0, // 0 = unlimited
        
        // Design - Layout
        'position' => 'top', // top, bottom, left, right
        'content_width' => 'boxed', // boxed, full
        'margin_top' => 0,
        'margin_right' => 0,
        'margin_bottom' => 0,
        'margin_left' => 0,
        'padding_top' => 10,
        'padding_right' => 15,
        'padding_bottom' => 10,
        'padding_left' => 15,
        'spacing' => 15,
        
        // Design - Background
        'bg_color' => '#000000',
        'bg_image' => '',
        'bg_opacity' => 100,
        'text_color' => '#ffffff',
        
        // Design - Borders
        'border_width' => 0,
        'border_style' => 'solid',
        'border_color' => '#ffffff',
        
        // Design - Buttons
        'btn_bg_color' => '#ffffff',
        'btn_text_color' => '#000000',
        'btn_hover_bg' => '#eeeeee',
        'btn_hover_text' => '#000000',
        'btn_padding' => '6px 16px',
        'btn_radius' => 4,
        
        'close_bg_color' => '#000000',
        'close_text_color' => '#ffffff',
        'close_hover_bg' => '#333333',
        'close_hover_text' => '#ffffff',
        
        'open_bg_color' => '#000000',
        'open_text_color' => '#ffffff',
        'open_hover_bg' => '#333333',
        'open_hover_text' => '#ffffff',
        
        // Behavior
        'load_minimized' => 0,
        'show_open_btn' => 0,
        'show_close_btn' => 1,
        'close_text' => '×',
        'open_text' => '🔔',
        'delay' => 0,
        'is_sticky' => 1,
        'z_index' => 99999,
        'animation' => 'slide', // slide, fade, none
        'scroll_show_percent' => 0,
        'hide_on_scroll_down' => 0,
        
        // Scheduling
        'enable_schedule' => 0,
        'start_date' => '',
        'end_date' => '',
        'enable_countdown' => 0,
        'countdown_target' => '',
        'hide_after_countdown' => 0,
    );
}

/**
 * Get current settings merged with defaults
 */
function simpanbar_get_settings() {
    $defaults = simpanbar_get_default_settings();
    $saved    = get_option( 'simpanbar_settings', array() );
    return wp_parse_args( $saved, $defaults );
}

/**
 * Check if the bar should be displayed on the current page based on visibility rules
 */
function simpanbar_should_display() {
    if ( is_admin() ) {
        return false;
    }

    $settings = simpanbar_get_settings();

    // Check if message is empty
    if ( empty( $settings['message'] ) ) {
        return false;
    }

    // Check device targeting (basic PHP check, JS will also enforce)
    if ( $settings['hide_mobile'] && wp_is_mobile() ) {
        return false;
    }

    // Check page visibility rules
    $show_on = $settings['show_on'];
    $current_id = get_the_ID();

    // Exclusions override everything
    if ( ! empty( $settings['exclude_ids'] ) && $current_id ) {
        $exclude_ids = array_map( 'trim', explode( ',', $settings['exclude_ids'] ) );
        if ( in_array( $current_id, $exclude_ids ) ) {
            return false;
        }
    }

    if ( $show_on === 'homepage' && ! ( is_front_page() || is_home() ) ) {
        return false;
    }

    if ( $show_on === 'posts' && ! is_single() ) {
        return false;
    }

    if ( $show_on === 'pages' && ! is_page() ) {
        return false;
    }

    if ( $show_on === 'specific' && $current_id ) {
        $specific_ids = array_map( 'trim', explode( ',', $settings['specific_ids'] ) );
        if ( ! in_array( $current_id, $specific_ids ) ) {
            return false;
        }
    }

    // Check scheduling (PHP side)
    if ( $settings['enable_schedule'] ) {
        $now = time();

        if ( ! empty( $settings['start_date'] ) ) {
            $start = strtotime( $settings['start_date'] );
            if ( false !== $start && $now < $start ) {
                return false;
            }
        }

        if ( ! empty( $settings['end_date'] ) ) {
            $end = strtotime( $settings['end_date'] );
            if ( false !== $end && $now > $end ) {
                return false;
            }
        }
    }

    return true;
}

/**
 * Enqueue frontend scripts and styles
 */
function simpanbar_enqueue_assets() {
    if ( ! simpanbar_should_display() ) {
        return;
    }

    wp_enqueue_style( 'simpanbar-style', SIMPANBAR_PLUGIN_URL . 'assets/css/style.css', array(), SIMPANBAR_VERSION );
    wp_enqueue_script( 'simpanbar-script', SIMPANBAR_PLUGIN_URL . 'assets/js/script.js', array(), SIMPANBAR_VERSION, true );

    $settings = simpanbar_get_settings();

    // Pass settings to JS
    wp_localize_script( 'simpanbar-script', 'simpanbarSettings', array(
        'position' => $settings['position'],
        'isSticky' => $settings['is_sticky'],
        'delay' => absint( $settings['delay'] ),
        'animation' => $settings['animation'],
        'loadMinimized' => $settings['load_minimized'],
        'showLimit' => absint( $settings['show_limit'] ),
        'scrollShowPercent' => absint( $settings['scroll_show_percent'] ),
        'hideOnScrollDown' => $settings['hide_on_scroll_down'],
        'hideMobile' => $settings['hide_mobile'],
        'hideDesktop' => $settings['hide_desktop'],
        'enableCountdown' => $settings['enable_countdown'],
        'countdownTarget' => $settings['countdown_target'],
        'hideAfterCountdown' => $settings['hide_after_countdown'],
        'serverTime' => time() * 1000, // Pass server time for accurate countdown
    ) );
}
add_action( 'wp_enqueue_scripts', 'simpanbar_enqueue_assets' );

/**
 * Render the announcement bar on the frontend
 */
function simpanbar_render_bar() {
    if ( ! simpanbar_should_display() ) {
        return;
    }

    $settings = simpanbar_get_settings();

    $message = wp_kses_post( $settings['message'] );
    $btn_text = esc_html( $settings['btn_text'] );
    $btn_url = esc_url( $settings['btn_url'] );
    
    $classes = array( 'simpanbar-bar', 'simpanbar-pos-' . esc_attr( $settings['position'] ) );
    
    if ( $settings['is_sticky'] ) {
        $classes[] = 'simpanbar-sticky';
    }

    if ( $settings['content_width'] === 'full' ) {
        $classes[] = 'simpanbar-full-width';
    }

    $classes[] = 'simpanbar-anim-' . esc_attr( $settings['animation'] );

    // Generate dynamic CSS variables for styling
    $css_vars = array(
        '--simpanbar-bg-color' => esc_attr( $settings['bg_color'] ),
        '--simpanbar-bg-opacity' => esc_attr( $settings['bg_opacity'] / 100 ),
        '--simpanbar-text-color' => esc_attr( $settings['text_color'] ),
        '--simpanbar-z-index' => esc_attr( $settings['z_index'] ),
        
        '--simpanbar-margin' => sprintf( '%dpx %dpx %dpx %dpx', $settings['margin_top'], $settings['margin_right'], $settings['margin_bottom'], $settings['margin_left'] ),
        '--simpanbar-padding' => sprintf( '%dpx %dpx %dpx %dpx', $settings['padding_top'], $settings['padding_right'], $settings['padding_bottom'], $settings['padding_left'] ),
        '--simpanbar-spacing' => esc_attr( $settings['spacing'] ) . 'px',
        
        '--simpanbar-border-width' => esc_attr( $settings['border_width'] ) . 'px',
        '--simpanbar-border-style' => esc_attr( $settings['border_style'] ),
        '--simpanbar-border-color' => esc_attr( $settings['border_color'] ),
        
        '--simpanbar-btn-bg' => esc_attr( $settings['btn_bg_color'] ),
        '--simpanbar-btn-text' => esc_attr( $settings['btn_text_color'] ),
        '--simpanbar-btn-hover-bg' => esc_attr( $settings['btn_hover_bg'] ),
        '--simpanbar-btn-hover-text' => esc_attr( $settings['btn_hover_text'] ),
        '--simpanbar-btn-padding' => esc_attr( $settings['btn_padding'] ),
        '--simpanbar-btn-radius' => esc_attr( $settings['btn_radius'] ) . 'px',
        
        '--simpanbar-close-bg' => esc_attr( $settings['close_bg_color'] ),
        '--simpanbar-close-text' => esc_attr( $settings['close_text_color'] ),
        '--simpanbar-close-hover-bg' => esc_attr( $settings['close_hover_bg'] ),
        '--simpanbar-close-hover-text' => esc_attr( $settings['close_hover_text'] ),
        
        '--simpanbar-open-bg' => esc_attr( $settings['open_bg_color'] ),
        '--simpanbar-open-text' => esc_attr( $settings['open_text_color'] ),
        '--simpanbar-open-hover-bg' => esc_attr( $settings['open_hover_bg'] ),
        '--simpanbar-open-hover-text' => esc_attr( $settings['open_hover_text'] ),
    );

    // Enforce only allowed positions
    if ( ! in_array( $settings['position'], array( 'top', 'bottom' ) ) ) {
        $settings['position'] = 'top';
    }

    if ( ! empty( $settings['bg_image'] ) ) {
        $css_vars['--simpanbar-bg-image'] = 'url(' . esc_url( $settings['bg_image'] ) . ')';
    }

    $style_attr = '';
    foreach ( $css_vars as $key => $val ) {
        $style_attr .= $key . ': ' . $val . '; ';
    }

    ?>
    <!-- Simple Announcement Bar -->
    <div id="simpanbar-announcement-bar" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( $style_attr ); ?>" aria-hidden="true">
        <div class="simpanbar-bg-overlay"></div>
        <div class="simpanbar-content">
            <div class="simpanbar-message-wrap">
                <span class="simpanbar-message"><?php echo wp_kses_post( $message ); ?></span>
                
                <?php if ( $settings['enable_countdown'] && ! empty( $settings['countdown_target'] ) ) : ?>
                    <span class="simpanbar-countdown" id="simpanbar-countdown-timer">
                        <span class="simpanbar-cd-part"><span class="simpanbar-cd-val" id="simpanbar-cd-d">00</span><span class="simpanbar-cd-label">d</span></span>
                        <span class="simpanbar-cd-part"><span class="simpanbar-cd-val" id="simpanbar-cd-h">00</span><span class="simpanbar-cd-label">h</span></span>
                        <span class="simpanbar-cd-part"><span class="simpanbar-cd-val" id="simpanbar-cd-m">00</span><span class="simpanbar-cd-label">m</span></span>
                        <span class="simpanbar-cd-part"><span class="simpanbar-cd-val" id="simpanbar-cd-s">00</span><span class="simpanbar-cd-label">s</span></span>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $btn_text ) && ! empty( $btn_url ) ) : ?>
                <a href="<?php echo esc_url( $btn_url ); ?>" class="simpanbar-button">
                    <?php echo esc_html( $btn_text ); ?>
                </a>
            <?php endif; ?>
        </div>

        <?php if ( $settings['show_close_btn'] ) : ?>
            <button id="simpanbar-close-btn" class="simpanbar-close" aria-label="<?php esc_attr_e( 'Close Announcement', 'simple-announcement-bar' ); ?>">
                <?php echo esc_html( $settings['close_text'] ); ?>
            </button>
        <?php endif; ?>
    </div>

    <?php if ( $settings['show_open_btn'] ) : ?>
        <button id="simpanbar-open-btn" class="simpanbar-open-btn simpanbar-pos-<?php echo esc_attr( $settings['position'] ); ?>" style="<?php echo esc_attr( $style_attr ); ?>" aria-label="<?php esc_attr_e( 'Open Announcement', 'simple-announcement-bar' ); ?>">
            <?php echo esc_html( $settings['open_text'] ); ?>
        </button>
    <?php endif; ?>
    <!-- /Simple Announcement Bar -->
    <?php
}
// Hook to wp_body_open if available, fallback to wp_footer
add_action( 'wp_body_open', 'simpanbar_render_bar' );
// Fallback for themes that don't support wp_body_open
add_action( 'wp_footer', 'simpanbar_render_bar_fallback' );
function simpanbar_render_bar_fallback() {
    if ( ! did_action( 'wp_body_open' ) ) {
        simpanbar_render_bar();
    }
}

/**
 * Add settings link on plugin page
 */
function simpanbar_plugin_action_links( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=simple-announcement-bar' ) ) . '">' . esc_html__( 'Settings', 'simple-announcement-bar' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'simpanbar_plugin_action_links' );
