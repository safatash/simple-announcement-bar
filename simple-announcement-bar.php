<?php
/**
 * Plugin Name: Simple Announcement Bar
 * Plugin URI: https://example.com/simple-announcement-bar
 * Description: A lightweight, customizable announcement bar with advanced positioning, scheduling, and targeting.
 * Version: 2.0.0
 * Author: Manus AI
 * Author URI: https://manus.im
 * License: GPL2
 * Text Domain: simple-announcement-bar
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'SAB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SAB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SAB_VERSION', '2.0.0' );

// Include admin settings page
if ( is_admin() ) {
    require_once SAB_PLUGIN_DIR . 'admin/settings-page.php';
}

/**
 * Default settings array
 */
function sab_get_default_settings() {
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
function sab_get_settings() {
    $defaults = sab_get_default_settings();
    $saved = get_option( 'sab_settings', array() );
    return wp_parse_args( $saved, $defaults );
}

/**
 * Check if the bar should be displayed on the current page based on visibility rules
 */
function sab_should_display() {
    if ( is_admin() ) {
        return false;
    }

    $settings = sab_get_settings();

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
        $now = current_time( 'timestamp' );
        
        if ( ! empty( $settings['start_date'] ) ) {
            $start = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $settings['start_date'] ) ) ) );
            if ( $now < $start ) {
                return false;
            }
        }
        
        if ( ! empty( $settings['end_date'] ) ) {
            $end = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $settings['end_date'] ) ) ) );
            if ( $now > $end ) {
                return false;
            }
        }
    }

    return true;
}

/**
 * Enqueue frontend scripts and styles
 */
function sab_enqueue_assets() {
    if ( ! sab_should_display() ) {
        return;
    }

    wp_enqueue_style( 'sab-style', SAB_PLUGIN_URL . 'assets/css/style.css', array(), SAB_VERSION );
    wp_enqueue_script( 'sab-script', SAB_PLUGIN_URL . 'assets/js/script.js', array(), SAB_VERSION, true );

    $settings = sab_get_settings();

    // Pass settings to JS
    wp_localize_script( 'sab-script', 'sabSettings', array(
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
        'serverTime' => current_time( 'timestamp' ) * 1000, // Pass server time for accurate countdown
    ) );
}
add_action( 'wp_enqueue_scripts', 'sab_enqueue_assets' );

/**
 * Render the announcement bar on the frontend
 */
function sab_render_bar() {
    if ( ! sab_should_display() ) {
        return;
    }

    $settings = sab_get_settings();

    $message = wp_kses_post( $settings['message'] );
    $btn_text = esc_html( $settings['btn_text'] );
    $btn_url = esc_url( $settings['btn_url'] );
    
    $classes = array( 'sab-bar', 'sab-pos-' . esc_attr( $settings['position'] ) );
    
    if ( $settings['is_sticky'] ) {
        $classes[] = 'sab-sticky';
    }
    
    if ( $settings['content_width'] === 'full' ) {
        $classes[] = 'sab-full-width';
    }

    $classes[] = 'sab-anim-' . esc_attr( $settings['animation'] );

    // Generate dynamic CSS variables for styling
    $css_vars = array(
        '--sab-bg-color' => esc_attr( $settings['bg_color'] ),
        '--sab-bg-opacity' => esc_attr( $settings['bg_opacity'] / 100 ),
        '--sab-text-color' => esc_attr( $settings['text_color'] ),
        '--sab-z-index' => esc_attr( $settings['z_index'] ),
        
        '--sab-margin' => sprintf( '%dpx %dpx %dpx %dpx', $settings['margin_top'], $settings['margin_right'], $settings['margin_bottom'], $settings['margin_left'] ),
        '--sab-padding' => sprintf( '%dpx %dpx %dpx %dpx', $settings['padding_top'], $settings['padding_right'], $settings['padding_bottom'], $settings['padding_left'] ),
        '--sab-spacing' => esc_attr( $settings['spacing'] ) . 'px',
        
        '--sab-border-width' => esc_attr( $settings['border_width'] ) . 'px',
        '--sab-border-style' => esc_attr( $settings['border_style'] ),
        '--sab-border-color' => esc_attr( $settings['border_color'] ),
        
        '--sab-btn-bg' => esc_attr( $settings['btn_bg_color'] ),
        '--sab-btn-text' => esc_attr( $settings['btn_text_color'] ),
        '--sab-btn-hover-bg' => esc_attr( $settings['btn_hover_bg'] ),
        '--sab-btn-hover-text' => esc_attr( $settings['btn_hover_text'] ),
        '--sab-btn-padding' => esc_attr( $settings['btn_padding'] ),
        '--sab-btn-radius' => esc_attr( $settings['btn_radius'] ) . 'px',
        
        '--sab-close-bg' => esc_attr( $settings['close_bg_color'] ),
        '--sab-close-text' => esc_attr( $settings['close_text_color'] ),
        '--sab-close-hover-bg' => esc_attr( $settings['close_hover_bg'] ),
        '--sab-close-hover-text' => esc_attr( $settings['close_hover_text'] ),
        
        '--sab-open-bg' => esc_attr( $settings['open_bg_color'] ),
        '--sab-open-text' => esc_attr( $settings['open_text_color'] ),
        '--sab-open-hover-bg' => esc_attr( $settings['open_hover_bg'] ),
        '--sab-open-hover-text' => esc_attr( $settings['open_hover_text'] ),
    );

    // Enforce only allowed positions
    if ( ! in_array( $settings['position'], array( 'top', 'bottom' ) ) ) {
        $settings['position'] = 'top';
    }

    if ( ! empty( $settings['bg_image'] ) ) {
        $css_vars['--sab-bg-image'] = 'url(' . esc_url( $settings['bg_image'] ) . ')';
    }

    $style_attr = '';
    foreach ( $css_vars as $key => $val ) {
        $style_attr .= $key . ': ' . $val . '; ';
    }

    ?>
    <!-- Simple Announcement Bar -->
    <div id="sab-announcement-bar" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( $style_attr ); ?>" style="display:none;" aria-hidden="true">
        <div class="sab-bg-overlay"></div>
        <div class="sab-content">
            <div class="sab-message-wrap">
                <span class="sab-message"><?php echo $message; ?></span>
                
                <?php if ( $settings['enable_countdown'] && ! empty( $settings['countdown_target'] ) ) : ?>
                    <span class="sab-countdown" id="sab-countdown-timer">
                        <span class="sab-cd-part"><span class="sab-cd-val" id="sab-cd-d">00</span><span class="sab-cd-label">d</span></span>
                        <span class="sab-cd-part"><span class="sab-cd-val" id="sab-cd-h">00</span><span class="sab-cd-label">h</span></span>
                        <span class="sab-cd-part"><span class="sab-cd-val" id="sab-cd-m">00</span><span class="sab-cd-label">m</span></span>
                        <span class="sab-cd-part"><span class="sab-cd-val" id="sab-cd-s">00</span><span class="sab-cd-label">s</span></span>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $btn_text ) && ! empty( $btn_url ) ) : ?>
                <a href="<?php echo esc_url( $btn_url ); ?>" class="sab-button">
                    <?php echo esc_html( $btn_text ); ?>
                </a>
            <?php endif; ?>
        </div>
        
        <?php if ( $settings['show_close_btn'] ) : ?>
            <button id="sab-close-btn" class="sab-close" aria-label="Close Announcement">
                <?php echo esc_html( $settings['close_text'] ); ?>
            </button>
        <?php endif; ?>
    </div>

    <?php if ( $settings['show_open_btn'] ) : ?>
        <button id="sab-open-btn" class="sab-open-btn sab-pos-<?php echo esc_attr( $settings['position'] ); ?>" style="<?php echo esc_attr( $style_attr ); ?>" aria-label="Open Announcement" style="display:none;">
            <?php echo esc_html( $settings['open_text'] ); ?>
        </button>
    <?php endif; ?>
    <!-- /Simple Announcement Bar -->
    <?php
}
// Hook to wp_body_open if available, fallback to wp_footer
add_action( 'wp_body_open', 'sab_render_bar' );
// Fallback for themes that don't support wp_body_open
add_action( 'wp_footer', 'sab_render_bar_fallback' );
function sab_render_bar_fallback() {
    if ( ! did_action( 'wp_body_open' ) ) {
        sab_render_bar();
    }
}

/**
 * Add settings link on plugin page
 */
function sab_plugin_action_links( $links ) {
    $settings_link = '<a href="options-general.php?page=simple-announcement-bar">' . __( 'Settings', 'simple-announcement-bar' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'sab_plugin_action_links' );
