<?php
/**
 * Admin Settings Page for Simple Announcement Bar
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register settings menu
 */
function simpanbar_add_admin_menu() {
    $page_hook = add_options_page(
        __( 'Announcement Bar Settings', 'simple-announcement-bar' ),
        __( 'Announcement Bar', 'simple-announcement-bar' ),
        'manage_options',
        'simple-announcement-bar',
        'simpanbar_options_page'
    );

    // Load admin assets only on this page
    add_action( "admin_print_scripts-{$page_hook}", 'simpanbar_admin_assets' );
}
add_action( 'admin_menu', 'simpanbar_add_admin_menu' );

/**
 * Enqueue admin scripts and styles
 */
function simpanbar_admin_assets() {
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );

    // We also enqueue the frontend CSS to make the live preview accurate
    wp_enqueue_style( 'simpanbar-frontend-style', SIMPANBAR_PLUGIN_URL . 'assets/css/style.css', array(), SIMPANBAR_VERSION );

    wp_enqueue_style( 'simpanbar-admin-style', SIMPANBAR_PLUGIN_URL . 'admin/admin-style.css', array(), SIMPANBAR_VERSION );
    wp_enqueue_script( 'simpanbar-admin-script', SIMPANBAR_PLUGIN_URL . 'admin/admin-script.js', array( 'jquery', 'wp-color-picker' ), SIMPANBAR_VERSION, true );
}

/**
 * Register settings
 */
function simpanbar_settings_init() {
    register_setting( 'simpanbar_plugin_page', 'simpanbar_settings', 'simpanbar_sanitize_settings' );
}
add_action( 'admin_init', 'simpanbar_settings_init' );

/**
 * Sanitize settings
 */
function simpanbar_sanitize_settings( $input ) {
    $sanitized = simpanbar_get_default_settings();
    
    // General
    if ( isset( $input['message'] ) ) $sanitized['message'] = wp_kses_post( $input['message'] );
    if ( isset( $input['btn_text'] ) ) $sanitized['btn_text'] = sanitize_text_field( $input['btn_text'] );
    if ( isset( $input['btn_url'] ) ) $sanitized['btn_url'] = esc_url_raw( $input['btn_url'] );
    
    // Visibility
    if ( isset( $input['show_on'] ) ) $sanitized['show_on'] = sanitize_text_field( $input['show_on'] );
    if ( isset( $input['specific_ids'] ) ) $sanitized['specific_ids'] = sanitize_text_field( $input['specific_ids'] );
    if ( isset( $input['exclude_ids'] ) ) $sanitized['exclude_ids'] = sanitize_text_field( $input['exclude_ids'] );
    $sanitized['hide_mobile'] = isset( $input['hide_mobile'] ) ? 1 : 0;
    $sanitized['hide_desktop'] = isset( $input['hide_desktop'] ) ? 1 : 0;
    if ( isset( $input['show_limit'] ) ) $sanitized['show_limit'] = absint( $input['show_limit'] );
    
    // Design - Layout
    if ( isset( $input['position'] ) ) {
        $allowed_positions = array( 'top', 'bottom' );
        $sanitized['position'] = in_array( $input['position'], $allowed_positions ) ? $input['position'] : 'top';
    }
    if ( isset( $input['content_width'] ) ) $sanitized['content_width'] = sanitize_text_field( $input['content_width'] );
    if ( isset( $input['margin_top'] ) ) $sanitized['margin_top'] = intval( $input['margin_top'] );
    if ( isset( $input['margin_right'] ) ) $sanitized['margin_right'] = intval( $input['margin_right'] );
    if ( isset( $input['margin_bottom'] ) ) $sanitized['margin_bottom'] = intval( $input['margin_bottom'] );
    if ( isset( $input['margin_left'] ) ) $sanitized['margin_left'] = intval( $input['margin_left'] );
    if ( isset( $input['padding_top'] ) ) $sanitized['padding_top'] = intval( $input['padding_top'] );
    if ( isset( $input['padding_right'] ) ) $sanitized['padding_right'] = intval( $input['padding_right'] );
    if ( isset( $input['padding_bottom'] ) ) $sanitized['padding_bottom'] = intval( $input['padding_bottom'] );
    if ( isset( $input['padding_left'] ) ) $sanitized['padding_left'] = intval( $input['padding_left'] );
    if ( isset( $input['spacing'] ) ) $sanitized['spacing'] = intval( $input['spacing'] );
    
    // Design - Background
    if ( isset( $input['bg_color'] ) ) $sanitized['bg_color'] = sanitize_hex_color( $input['bg_color'] );
    if ( isset( $input['bg_image'] ) ) $sanitized['bg_image'] = esc_url_raw( $input['bg_image'] );
    if ( isset( $input['bg_opacity'] ) ) $sanitized['bg_opacity'] = intval( $input['bg_opacity'] );
    if ( isset( $input['text_color'] ) ) $sanitized['text_color'] = sanitize_hex_color( $input['text_color'] );
    
    // Design - Borders
    if ( isset( $input['border_width'] ) ) $sanitized['border_width'] = intval( $input['border_width'] );
    if ( isset( $input['border_style'] ) ) $sanitized['border_style'] = sanitize_text_field( $input['border_style'] );
    if ( isset( $input['border_color'] ) ) $sanitized['border_color'] = sanitize_hex_color( $input['border_color'] );
    
    // Design - Buttons
    if ( isset( $input['btn_bg_color'] ) ) $sanitized['btn_bg_color'] = sanitize_hex_color( $input['btn_bg_color'] );
    if ( isset( $input['btn_text_color'] ) ) $sanitized['btn_text_color'] = sanitize_hex_color( $input['btn_text_color'] );
    if ( isset( $input['btn_hover_bg'] ) ) $sanitized['btn_hover_bg'] = sanitize_hex_color( $input['btn_hover_bg'] );
    if ( isset( $input['btn_hover_text'] ) ) $sanitized['btn_hover_text'] = sanitize_hex_color( $input['btn_hover_text'] );
    if ( isset( $input['btn_padding'] ) ) $sanitized['btn_padding'] = sanitize_text_field( $input['btn_padding'] );
    if ( isset( $input['btn_radius'] ) ) $sanitized['btn_radius'] = intval( $input['btn_radius'] );
    
    if ( isset( $input['close_bg_color'] ) ) $sanitized['close_bg_color'] = sanitize_hex_color( $input['close_bg_color'] ) ?: '#000000';
    if ( isset( $input['close_text_color'] ) ) $sanitized['close_text_color'] = sanitize_hex_color( $input['close_text_color'] );
    if ( isset( $input['close_hover_bg'] ) ) $sanitized['close_hover_bg'] = sanitize_hex_color( $input['close_hover_bg'] ) ?: '#333333';
    if ( isset( $input['close_hover_text'] ) ) $sanitized['close_hover_text'] = sanitize_hex_color( $input['close_hover_text'] );
    
    if ( isset( $input['open_bg_color'] ) ) $sanitized['open_bg_color'] = sanitize_hex_color( $input['open_bg_color'] );
    if ( isset( $input['open_text_color'] ) ) $sanitized['open_text_color'] = sanitize_hex_color( $input['open_text_color'] );
    if ( isset( $input['open_hover_bg'] ) ) $sanitized['open_hover_bg'] = sanitize_hex_color( $input['open_hover_bg'] );
    if ( isset( $input['open_hover_text'] ) ) $sanitized['open_hover_text'] = sanitize_hex_color( $input['open_hover_text'] );
    
    // Behavior
    $sanitized['load_minimized'] = isset( $input['load_minimized'] ) ? 1 : 0;
    $sanitized['show_open_btn'] = isset( $input['show_open_btn'] ) ? 1 : 0;
    $sanitized['show_close_btn'] = isset( $input['show_close_btn'] ) ? 1 : 0;
    if ( isset( $input['close_text'] ) ) $sanitized['close_text'] = sanitize_text_field( $input['close_text'] );
    if ( isset( $input['open_text'] ) ) $sanitized['open_text'] = sanitize_text_field( $input['open_text'] );
    if ( isset( $input['delay'] ) ) $sanitized['delay'] = absint( $input['delay'] );
    $sanitized['is_sticky'] = isset( $input['is_sticky'] ) ? 1 : 0;
    if ( isset( $input['z_index'] ) ) $sanitized['z_index'] = intval( $input['z_index'] );
    if ( isset( $input['animation'] ) ) $sanitized['animation'] = sanitize_text_field( $input['animation'] );
    if ( isset( $input['scroll_show_percent'] ) ) $sanitized['scroll_show_percent'] = absint( $input['scroll_show_percent'] );
    $sanitized['hide_on_scroll_down'] = isset( $input['hide_on_scroll_down'] ) ? 1 : 0;
    
    // Scheduling
    $sanitized['enable_schedule'] = isset( $input['enable_schedule'] ) ? 1 : 0;
    if ( isset( $input['start_date'] ) ) $sanitized['start_date'] = sanitize_text_field( $input['start_date'] );
    if ( isset( $input['end_date'] ) ) $sanitized['end_date'] = sanitize_text_field( $input['end_date'] );
    $sanitized['enable_countdown'] = isset( $input['enable_countdown'] ) ? 1 : 0;
    if ( isset( $input['countdown_target'] ) ) $sanitized['countdown_target'] = sanitize_text_field( $input['countdown_target'] );
    $sanitized['hide_after_countdown'] = isset( $input['hide_after_countdown'] ) ? 1 : 0;

    return $sanitized;
}

/**
 * Options Page HTML
 */
function simpanbar_options_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'simple-announcement-bar' ) );
    }

    $settings = simpanbar_get_settings();
    ?>
    <div class="wrap simpanbar-admin-wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        
        <div class="simpanbar-admin-container">
            
            <!-- Sidebar Navigation -->
            <div class="simpanbar-admin-sidebar">
                <ul class="simpanbar-tabs">
                    <li class="active" data-tab="tab-general">General</li>
                    <li data-tab="tab-visibility">Visibility</li>
                    <li data-tab="tab-design">Design</li>
                    <li data-tab="tab-behavior">Behavior</li>
                    <li data-tab="tab-scheduling">Scheduling & Countdown</li>
                </ul>
                <div class="simpanbar-save-panel">
                    <button type="submit" form="simpanbar-settings-form" class="button button-primary button-hero">Save Settings</button>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="simpanbar-admin-main">
                
                <!-- Live Preview Panel -->
                <div class="simpanbar-preview-panel">
                    <h3>Live Preview</h3>
                    <div class="simpanbar-preview-container" id="simpanbar-preview-container">
                        <!-- Preview will be injected here via JS -->
                    </div>
                </div>

                <form action="options.php" method="post" id="simpanbar-settings-form">
                    <?php settings_fields( 'simpanbar_plugin_page' ); ?>
                    
                    <!-- TAB: GENERAL -->
                    <div id="tab-general" class="simpanbar-tab-content active">
                        <h2>General Content</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Announcement Message</th>
                                <td>
                                    <textarea name="simpanbar_settings[message]" id="simpanbar_message" rows="4" class="large-text"><?php echo esc_textarea( $settings['message'] ); ?></textarea>
                                    <p class="description">Basic HTML allowed (e.g., &lt;strong&gt;, &lt;a&gt;).</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Button Text</th>
                                <td>
                                    <input type="text" name="simpanbar_settings[btn_text]" id="simpanbar_btn_text" value="<?php echo esc_attr( $settings['btn_text'] ); ?>" class="regular-text">
                                    <p class="description">Leave blank to hide the button.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Button URL</th>
                                <td>
                                    <input type="url" name="simpanbar_settings[btn_url]" id="simpanbar_btn_url" value="<?php echo esc_url( $settings['btn_url'] ); ?>" class="regular-text">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- TAB: VISIBILITY -->
                    <div id="tab-visibility" class="simpanbar-tab-content">
                        <h2>Visibility & Targeting</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Show On</th>
                                <td>
                                    <select name="simpanbar_settings[show_on]" id="simpanbar_show_on">
                                        <option value="entire_site" <?php selected( $settings['show_on'], 'entire_site' ); ?>>Entire Site</option>
                                        <option value="homepage" <?php selected( $settings['show_on'], 'homepage' ); ?>>Homepage Only</option>
                                        <option value="posts" <?php selected( $settings['show_on'], 'posts' ); ?>>All Posts</option>
                                        <option value="pages" <?php selected( $settings['show_on'], 'pages' ); ?>>All Pages</option>
                                        <option value="specific" <?php selected( $settings['show_on'], 'specific' ); ?>>Specific Pages/Posts</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="simpanbar-conditional" data-show-if="sab_show_on" data-show-val="specific">
                                <th scope="row">Include Specific IDs</th>
                                <td>
                                    <input type="text" name="simpanbar_settings[specific_ids]" value="<?php echo esc_attr( $settings['specific_ids'] ); ?>" class="regular-text">
                                    <p class="description">Comma-separated list of Post/Page IDs (e.g., 12, 45, 99).</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Exclude Specific IDs</th>
                                <td>
                                    <input type="text" name="simpanbar_settings[exclude_ids]" value="<?php echo esc_attr( $settings['exclude_ids'] ); ?>" class="regular-text">
                                    <p class="description">Comma-separated list of Post/Page IDs to hide the bar on.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Device Targeting</th>
                                <td>
                                    <label class="simpanbar-toggle">
                                        <input type="checkbox" name="simpanbar_settings[hide_mobile]" value="1" <?php checked( 1, $settings['hide_mobile'] ); ?>>
                                        <span class="simpanbar-slider"></span>
                                    </label> Hide on Mobile<br><br>
                                    <label class="simpanbar-toggle">
                                        <input type="checkbox" name="simpanbar_settings[hide_desktop]" value="1" <?php checked( 1, $settings['hide_desktop'] ); ?>>
                                        <span class="simpanbar-slider"></span>
                                    </label> Hide on Desktop
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Show Limit</th>
                                <td>
                                    <input type="number" name="simpanbar_settings[show_limit]" value="<?php echo esc_attr( $settings['show_limit'] ); ?>" min="0" class="small-text">
                                    <p class="description">Show X times per user. Set to 0 for unlimited.</p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- TAB: DESIGN -->
                    <div id="tab-design" class="simpanbar-tab-content">
                        <h2>Design Settings</h2>
                        
                        <h3>Layout</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Position</th>
                                <td>
                                    <select name="simpanbar_settings[position]" id="simpanbar_position">
                                        <option value="top" <?php selected( $settings['position'], 'top' ); ?>>Top</option>
                                        <option value="bottom" <?php selected( $settings['position'], 'bottom' ); ?>>Bottom</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Content Width</th>
                                <td>
                                    <select name="simpanbar_settings[content_width]" id="simpanbar_content_width">
                                        <option value="boxed" <?php selected( $settings['content_width'], 'boxed' ); ?>>Boxed (Max 1200px)</option>
                                        <option value="full" <?php selected( $settings['content_width'], 'full' ); ?>>Full Width</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Padding (px)</th>
                                <td>
                                    Top: <input type="number" name="simpanbar_settings[padding_top]" id="simpanbar_padding_top" value="<?php echo esc_attr( $settings['padding_top'] ); ?>" class="small-text">
                                    Right: <input type="number" name="simpanbar_settings[padding_right]" id="simpanbar_padding_right" value="<?php echo esc_attr( $settings['padding_right'] ); ?>" class="small-text">
                                    Bottom: <input type="number" name="simpanbar_settings[padding_bottom]" id="simpanbar_padding_bottom" value="<?php echo esc_attr( $settings['padding_bottom'] ); ?>" class="small-text">
                                    Left: <input type="number" name="simpanbar_settings[padding_left]" id="simpanbar_padding_left" value="<?php echo esc_attr( $settings['padding_left'] ); ?>" class="small-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Margin (px)</th>
                                <td>
                                    Top: <input type="number" name="simpanbar_settings[margin_top]" id="simpanbar_margin_top" value="<?php echo esc_attr( $settings['margin_top'] ); ?>" class="small-text">
                                    Right: <input type="number" name="simpanbar_settings[margin_right]" id="simpanbar_margin_right" value="<?php echo esc_attr( $settings['margin_right'] ); ?>" class="small-text">
                                    Bottom: <input type="number" name="simpanbar_settings[margin_bottom]" id="simpanbar_margin_bottom" value="<?php echo esc_attr( $settings['margin_bottom'] ); ?>" class="small-text">
                                    Left: <input type="number" name="simpanbar_settings[margin_left]" id="simpanbar_margin_left" value="<?php echo esc_attr( $settings['margin_left'] ); ?>" class="small-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Spacing (Text to Button)</th>
                                <td>
                                    <input type="range" name="simpanbar_settings[spacing]" id="simpanbar_spacing" value="<?php echo esc_attr( $settings['spacing'] ); ?>" min="0" max="100">
                                    <span id="simpanbar_spacing_val"><?php echo esc_attr( $settings['spacing'] ); ?>px</span>
                                </td>
                            </tr>
                        </table>

                        <h3>Background & Text</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Background Color</th>
                                <td><input type="text" name="simpanbar_settings[bg_color]" id="simpanbar_bg_color" value="<?php echo esc_attr( $settings['bg_color'] ); ?>" class="simpanbar-color-picker"></td>
                            </tr>
                            <tr>
                                <th scope="row">Text Color</th>
                                <td><input type="text" name="simpanbar_settings[text_color]" id="simpanbar_text_color" value="<?php echo esc_attr( $settings['text_color'] ); ?>" class="simpanbar-color-picker"></td>
                            </tr>
                            <tr>
                                <th scope="row">Background Opacity</th>
                                <td>
                                    <input type="range" name="simpanbar_settings[bg_opacity]" id="simpanbar_bg_opacity" value="<?php echo esc_attr( $settings['bg_opacity'] ); ?>" min="0" max="100">
                                    <span id="simpanbar_bg_opacity_val"><?php echo esc_attr( $settings['bg_opacity'] ); ?>%</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Background Image URL</th>
                                <td><input type="url" name="simpanbar_settings[bg_image]" id="simpanbar_bg_image" value="<?php echo esc_url( $settings['bg_image'] ); ?>" class="regular-text"></td>
                            </tr>
                        </table>

                        <h3>Borders</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Border Width (px)</th>
                                <td><input type="number" name="simpanbar_settings[border_width]" id="simpanbar_border_width" value="<?php echo esc_attr( $settings['border_width'] ); ?>" min="0" class="small-text"></td>
                            </tr>
                            <tr>
                                <th scope="row">Border Style</th>
                                <td>
                                    <select name="simpanbar_settings[border_style]" id="simpanbar_border_style">
                                        <option value="solid" <?php selected( $settings['border_style'], 'solid' ); ?>>Solid</option>
                                        <option value="dashed" <?php selected( $settings['border_style'], 'dashed' ); ?>>Dashed</option>
                                        <option value="dotted" <?php selected( $settings['border_style'], 'dotted' ); ?>>Dotted</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Border Color</th>
                                <td><input type="text" name="simpanbar_settings[border_color]" id="simpanbar_border_color" value="<?php echo esc_attr( $settings['border_color'] ); ?>" class="simpanbar-color-picker"></td>
                            </tr>
                        </table>

                        <h3>CTA Button Styling</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Background Color</th>
                                <td><input type="text" name="simpanbar_settings[btn_bg_color]" id="simpanbar_btn_bg_color" value="<?php echo esc_attr( $settings['btn_bg_color'] ); ?>" class="simpanbar-color-picker"></td>
                            </tr>
                            <tr>
                                <th scope="row">Text Color</th>
                                <td><input type="text" name="simpanbar_settings[btn_text_color]" id="simpanbar_btn_text_color" value="<?php echo esc_attr( $settings['btn_text_color'] ); ?>" class="simpanbar-color-picker"></td>
                            </tr>
                            <tr>
                                <th scope="row">Hover Background</th>
                                <td><input type="text" name="simpanbar_settings[btn_hover_bg]" id="simpanbar_btn_hover_bg" value="<?php echo esc_attr( $settings['btn_hover_bg'] ); ?>" class="simpanbar-color-picker"></td>
                            </tr>
                            <tr>
                                <th scope="row">Hover Text</th>
                                <td><input type="text" name="simpanbar_settings[btn_hover_text]" id="simpanbar_btn_hover_text" value="<?php echo esc_attr( $settings['btn_hover_text'] ); ?>" class="simpanbar-color-picker"></td>
                            </tr>
                            <tr>
                                <th scope="row">Padding</th>
                                <td><input type="text" name="simpanbar_settings[btn_padding]" id="simpanbar_btn_padding" value="<?php echo esc_attr( $settings['btn_padding'] ); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row">Border Radius (px)</th>
                                <td>
                                    <input type="number" name="simpanbar_settings[btn_radius]" id="simpanbar_btn_radius" value="<?php echo esc_attr( $settings['btn_radius'] ); ?>" min="0" class="small-text">
                                    <p class="description">Controls the roundness of the button corners (0 = square).</p>
                                </td>
                            </tr>
                        </table>

                        <h3>Close Button Styling</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Icon / Text Color</th>
                                <td><input type="text" name="simpanbar_settings[close_text_color]" id="simpanbar_close_text_color" value="<?php echo esc_attr( $settings['close_text_color'] ); ?>" class="simpanbar-color-picker"></td>
                            </tr>
                            <tr>
                                <th scope="row">Background Color</th>
                                <td>
                                    <input type="text" name="simpanbar_settings[close_bg_color]" id="simpanbar_close_bg_color" value="<?php echo esc_attr( $settings['close_bg_color'] === 'transparent' ? '#000000' : $settings['close_bg_color'] ); ?>" class="simpanbar-color-picker">
                                    <p class="description">Background behind the × icon. Use a dark/light color to make it stand out.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Hover Icon / Text Color</th>
                                <td><input type="text" name="simpanbar_settings[close_hover_text]" id="simpanbar_close_hover_text" value="<?php echo esc_attr( $settings['close_hover_text'] ); ?>" class="simpanbar-color-picker"></td>
                            </tr>
                            <tr>
                                <th scope="row">Hover Background Color</th>
                                <td><input type="text" name="simpanbar_settings[close_hover_bg]" id="simpanbar_close_hover_bg" value="<?php echo esc_attr( $settings['close_hover_bg'] === 'rgba(255,255,255,0.1)' ? '#ffffff' : $settings['close_hover_bg'] ); ?>" class="simpanbar-color-picker"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- TAB: BEHAVIOR -->
                    <div id="tab-behavior" class="simpanbar-tab-content">
                        <h2>Behavior Controls</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Sticky Bar</th>
                                <td>
                                    <label class="simpanbar-toggle">
                                        <input type="checkbox" name="simpanbar_settings[is_sticky]" id="simpanbar_is_sticky" value="1" <?php checked( 1, $settings['is_sticky'] ); ?>>
                                        <span class="simpanbar-slider"></span>
                                    </label>
                                    <p class="description">Fixes the bar to the viewport as users scroll.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Animation Type</th>
                                <td>
                                    <select name="simpanbar_settings[animation]" id="simpanbar_animation">
                                        <option value="slide" <?php selected( $settings['animation'], 'slide' ); ?>>Slide</option>
                                        <option value="fade" <?php selected( $settings['animation'], 'fade' ); ?>>Fade</option>
                                        <option value="none" <?php selected( $settings['animation'], 'none' ); ?>>None</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Notification Delay (Seconds)</th>
                                <td>
                                    <input type="range" name="simpanbar_settings[delay]" value="<?php echo esc_attr( $settings['delay'] ); ?>" min="0" max="60">
                                    <span><?php echo esc_attr( $settings['delay'] ); ?>s</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Scroll Behavior</th>
                                <td>
                                    Show after scrolling %: <input type="number" name="simpanbar_settings[scroll_show_percent]" value="<?php echo esc_attr( $settings['scroll_show_percent'] ); ?>" min="0" max="100" class="small-text">%<br><br>
                                    <label class="simpanbar-toggle">
                                        <input type="checkbox" name="simpanbar_settings[hide_on_scroll_down]" value="1" <?php checked( 1, $settings['hide_on_scroll_down'] ); ?>>
                                        <span class="simpanbar-slider"></span>
                                    </label> Hide on scroll down, show on scroll up
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Z-Index</th>
                                <td>
                                    <input type="number" name="simpanbar_settings[z_index]" id="simpanbar_z_index" value="<?php echo esc_attr( $settings['z_index'] ); ?>" class="regular-text">
                                </td>
                            </tr>
                        </table>

                        <h3>Close & Open Buttons</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Show Close Button</th>
                                <td>
                                    <label class="simpanbar-toggle">
                                        <input type="checkbox" name="simpanbar_settings[show_close_btn]" id="simpanbar_show_close_btn" value="1" <?php checked( 1, $settings['show_close_btn'] ); ?>>
                                        <span class="simpanbar-slider"></span>
                                    </label>
                                </td>
                            </tr>
                            <tr class="simpanbar-conditional" data-show-if="sab_show_close_btn" data-show-val="checked">
                                <th scope="row">Close Button Text</th>
                                <td><input type="text" name="simpanbar_settings[close_text]" id="simpanbar_close_text" value="<?php echo esc_attr( $settings['close_text'] ); ?>" class="small-text"></td>
                            </tr>
                            
                            <tr>
                                <th scope="row">Load Minimized</th>
                                <td>
                                    <label class="simpanbar-toggle">
                                        <input type="checkbox" name="simpanbar_settings[load_minimized]" value="1" <?php checked( 1, $settings['load_minimized'] ); ?>>
                                        <span class="simpanbar-slider"></span>
                                    </label>
                                    <p class="description">Bar starts hidden, only open button is visible.</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">Show Open Button</th>
                                <td>
                                    <label class="simpanbar-toggle">
                                        <input type="checkbox" name="simpanbar_settings[show_open_btn]" id="simpanbar_show_open_btn" value="1" <?php checked( 1, $settings['show_open_btn'] ); ?>>
                                        <span class="simpanbar-slider"></span>
                                    </label>
                                    <p class="description">Allows users to reopen the bar after closing it.</p>
                                </td>
                            </tr>
                            <tr class="simpanbar-conditional" data-show-if="sab_show_open_btn" data-show-val="checked">
                                <th scope="row">Open Button Text</th>
                                <td><input type="text" name="simpanbar_settings[open_text]" id="simpanbar_open_text" value="<?php echo esc_attr( $settings['open_text'] ); ?>" class="small-text"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- TAB: SCHEDULING -->
                    <div id="tab-scheduling" class="simpanbar-tab-content">
                        <h2>Scheduling & Countdown</h2>
                        
                        <h3>Scheduling</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Enable Scheduling</th>
                                <td>
                                    <label class="simpanbar-toggle">
                                        <input type="checkbox" name="simpanbar_settings[enable_schedule]" id="simpanbar_enable_schedule" value="1" <?php checked( 1, $settings['enable_schedule'] ); ?>>
                                        <span class="simpanbar-slider"></span>
                                    </label>
                                </td>
                            </tr>
                            <tr class="simpanbar-conditional" data-show-if="sab_enable_schedule" data-show-val="checked">
                                <th scope="row">Start Date/Time</th>
                                <td>
                                    <input type="datetime-local" name="simpanbar_settings[start_date]" value="<?php echo esc_attr( $settings['start_date'] ); ?>">
                                </td>
                            </tr>
                            <tr class="simpanbar-conditional" data-show-if="sab_enable_schedule" data-show-val="checked">
                                <th scope="row">End Date/Time</th>
                                <td>
                                    <input type="datetime-local" name="simpanbar_settings[end_date]" value="<?php echo esc_attr( $settings['end_date'] ); ?>">
                                </td>
                            </tr>
                        </table>

                        <h3>Countdown Timer</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Enable Countdown</th>
                                <td>
                                    <label class="simpanbar-toggle">
                                        <input type="checkbox" name="simpanbar_settings[enable_countdown]" id="simpanbar_enable_countdown" value="1" <?php checked( 1, $settings['enable_countdown'] ); ?>>
                                        <span class="simpanbar-slider"></span>
                                    </label>
                                </td>
                            </tr>
                            <tr class="simpanbar-conditional" data-show-if="sab_enable_countdown" data-show-val="checked">
                                <th scope="row">Target Date/Time</th>
                                <td>
                                    <input type="datetime-local" name="simpanbar_settings[countdown_target]" id="simpanbar_countdown_target" value="<?php echo esc_attr( $settings['countdown_target'] ); ?>">
                                </td>
                            </tr>
                            <tr class="simpanbar-conditional" data-show-if="sab_enable_countdown" data-show-val="checked">
                                <th scope="row">Auto-hide when ended</th>
                                <td>
                                    <label class="simpanbar-toggle">
                                        <input type="checkbox" name="simpanbar_settings[hide_after_countdown]" value="1" <?php checked( 1, $settings['hide_after_countdown'] ); ?>>
                                        <span class="simpanbar-slider"></span>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <?php
}
