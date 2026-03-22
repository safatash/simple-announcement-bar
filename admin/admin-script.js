/**
 * Simple Announcement Bar — Admin Script
 * Version: 2.0.0
 * Handles: Tab navigation, conditional fields, range sliders, live preview
 */

( function ( $ ) {
    'use strict';

    // =========================================
    // 1. Tab Navigation
    // =========================================
    $( '.sab-tabs li' ).on( 'click', function () {
        var targetTab = $( this ).data( 'tab' );
        
        // Update active tab
        $( '.sab-tabs li' ).removeClass( 'active' );
        $( this ).addClass( 'active' );
        
        // Show target content
        $( '.sab-tab-content' ).removeClass( 'active' );
        $( '#' + targetTab ).addClass( 'active' );
    } );

    // =========================================
    // 2. Range Slider Live Value Display
    // =========================================
    function initRangeSliders() {
        $( 'input[type="range"]' ).each( function () {
            var $slider = $( this );
            var $display = $slider.next( 'span' );
            if ( $display.length ) {
                $slider.on( 'input', function () {
                    var val = $( this ).val();
                    var name = $( this ).attr( 'name' ) || '';
                    if ( name.indexOf( 'opacity' ) !== -1 ) {
                        $display.text( val + '%' );
                    } else if ( name.indexOf( 'spacing' ) !== -1 ) {
                        $display.text( val + 'px' );
                    } else {
                        $display.text( val + 's' );
                    }
                } );
            }
        } );
    }

    // =========================================
    // 3. Color Pickers
    // =========================================
    function initColorPickers() {
        $( '.sab-color-picker' ).wpColorPicker( {
            change: function () {
                // Debounce preview update
                clearTimeout( window.sabPreviewTimer );
                window.sabPreviewTimer = setTimeout( updatePreview, 100 );
            },
            clear: function () {
                clearTimeout( window.sabPreviewTimer );
                window.sabPreviewTimer = setTimeout( updatePreview, 100 );
            }
        } );
    }

    // =========================================
    // 4. Conditional Field Visibility
    // =========================================
    function initConditionalFields() {
        function evaluateConditions() {
            $( '.sab-conditional' ).each( function () {
                var $row = $( this );
                var controlId = $row.data( 'show-if' );
                var showVal = String( $row.data( 'show-val' ) );
                var $control = $( '#' + controlId );
                
                if ( ! $control.length ) return;
                
                var show = false;
                
                if ( showVal === 'checked' ) {
                    show = $control.is( ':checked' );
                } else {
                    show = $control.val() === showVal;
                }
                
                if ( show ) {
                    $row.addClass( 'sab-visible' );
                } else {
                    $row.removeClass( 'sab-visible' );
                }
            } );
        }
        
        // Run on page load
        evaluateConditions();
        
        // Re-evaluate on any input change
        $( '#sab-settings-form' ).on( 'change input', 'input, select', function () {
            evaluateConditions();
        } );
    }

    // =========================================
    // 5. Live Preview
    // =========================================
    function getFieldValue( name ) {
        var $el = $( '[name="sab_settings[' + name + ']"]' );
        if ( ! $el.length ) return '';
        if ( $el.is( ':checkbox' ) ) return $el.is( ':checked' ) ? '1' : '0';
        return $el.val() || '';
    }

    function hexToRgba( hex, opacity ) {
        if ( ! hex || hex.charAt( 0 ) !== '#' ) return hex;
        var r = parseInt( hex.slice( 1, 3 ), 16 );
        var g = parseInt( hex.slice( 3, 5 ), 16 );
        var b = parseInt( hex.slice( 5, 7 ), 16 );
        return 'rgba(' + r + ',' + g + ',' + b + ',' + opacity + ')';
    }

    function updatePreview() {
        var $container = $( '#sab-preview-container' );
        if ( ! $container.length ) return;

        // Gather all current values
        var message = $( '#sab_message' ).val() || 'Your announcement message here.';
        var btnText = $( '#sab_btn_text' ).val() || '';
        var btnUrl = $( '#sab_btn_url' ).val() || '#';
        
        var position = $( '#sab_position' ).val() || 'top';
        var contentWidth = $( '#sab_content_width' ).val() || 'boxed';
        
        var paddingTop = getFieldValue( 'padding_top' ) || 10;
        var paddingRight = getFieldValue( 'padding_right' ) || 15;
        var paddingBottom = getFieldValue( 'padding_bottom' ) || 10;
        var paddingLeft = getFieldValue( 'padding_left' ) || 15;
        var spacing = getFieldValue( 'spacing' ) || 15;
        
        var bgColor = getFieldValue( 'bg_color' ) || '#000000';
        var bgOpacity = ( parseInt( getFieldValue( 'bg_opacity' ) ) || 100 ) / 100;
        var textColor = getFieldValue( 'text_color' ) || '#ffffff';
        
        var borderWidth = getFieldValue( 'border_width' ) || 0;
        var borderStyle = getFieldValue( 'border_style' ) || 'solid';
        var borderColor = getFieldValue( 'border_color' ) || '#ffffff';
        
        var btnBg = getFieldValue( 'btn_bg_color' ) || '#ffffff';
        var btnText2 = getFieldValue( 'btn_text_color' ) || '#000000';
        var btnPadding = getFieldValue( 'btn_padding' ) || '6px 16px';
        var btnRadius = getFieldValue( 'btn_radius' ) || 4;
        
        var showClose = getFieldValue( 'show_close_btn' ) === '1';
        var closeText = getFieldValue( 'close_text' ) || '×';
        var closeTextColor = getFieldValue( 'close_text_color' ) || '#ffffff';
        var closeBgColor = getFieldValue( 'close_bg_color' ) || '#000000';
        var closeHoverBg = getFieldValue( 'close_hover_bg' ) || '#333333';
        var closeHoverText = getFieldValue( 'close_hover_text' ) || '#ffffff';
        
        var enableCountdown = getFieldValue( 'enable_countdown' ) === '1';
        var countdownTarget = getFieldValue( 'countdown_target' );

        // Build CSS variables
        var cssVars = [
            '--sab-bg-color: ' + bgColor,
            '--sab-bg-opacity: ' + bgOpacity,
            '--sab-text-color: ' + textColor,
            '--sab-padding: ' + paddingTop + 'px ' + ( parseInt( paddingRight ) + ( showClose ? 40 : 0 ) ) + 'px ' + paddingBottom + 'px ' + paddingLeft + 'px',
            '--sab-spacing: ' + spacing + 'px',
            '--sab-border-width: ' + borderWidth + 'px',
            '--sab-border-style: ' + borderStyle,
            '--sab-border-color: ' + borderColor,
            '--sab-btn-bg: ' + btnBg,
            '--sab-btn-text: ' + btnText2,
            '--sab-btn-hover-bg: ' + btnBg,
            '--sab-btn-hover-text: ' + btnText2,
            '--sab-btn-padding: ' + btnPadding,
            '--sab-btn-radius: ' + btnRadius + 'px',
            '--sab-close-text: ' + closeTextColor,
            '--sab-close-bg: ' + closeBgColor,
            '--sab-close-hover-bg: ' + closeHoverBg,
            '--sab-close-hover-text: ' + closeHoverText,
            '--sab-z-index: 1',
            '--sab-margin: 0',
        ].join( '; ' );

        // Enforce only top/bottom in preview
        if ( position !== 'top' && position !== 'bottom' ) position = 'top';

        // Build classes
        var classes = 'sab-bar sab-pos-' + position;
        if ( contentWidth === 'full' ) classes += ' sab-full-width';

        // Build countdown HTML
        var countdownHtml = '';
        if ( enableCountdown && countdownTarget ) {
            countdownHtml = '<span class="sab-countdown" style="margin-left:12px;">' +
                '<span class="sab-cd-part"><span class="sab-cd-val">00</span><span class="sab-cd-label">d</span></span>' +
                '<span class="sab-cd-part"><span class="sab-cd-val">00</span><span class="sab-cd-label">h</span></span>' +
                '<span class="sab-cd-part"><span class="sab-cd-val">00</span><span class="sab-cd-label">m</span></span>' +
                '<span class="sab-cd-part"><span class="sab-cd-val">00</span><span class="sab-cd-label">s</span></span>' +
                '</span>';
        }

        // Build button HTML
        var buttonHtml = '';
        if ( btnText ) {
            buttonHtml = '<a href="#" class="sab-button" style="pointer-events:none;">' + sabEscHtml( btnText ) + '</a>';
        }

        // Build close button HTML
        var closeHtml = '';
        if ( showClose ) {
            closeHtml = '<button class="sab-close" style="color:' + closeTextColor + ';">' + sabEscHtml( closeText ) + '</button>';
        }

        // Build the full preview bar HTML
        var html = '<div class="' + classes + '" style="' + cssVars + '">' +
            '<div class="sab-bg-overlay"></div>' +
            '<div class="sab-content">' +
                '<div class="sab-message-wrap">' +
                    '<span class="sab-message">' + message + '</span>' +
                    countdownHtml +
                '</div>' +
                buttonHtml +
            '</div>' +
            closeHtml +
        '</div>';

        $container.html( html );
    }

    // Simple HTML escape helper
    function sabEscHtml( str ) {
        return String( str )
            .replace( /&/g, '&amp;' )
            .replace( /</g, '&lt;' )
            .replace( />/g, '&gt;' )
            .replace( /"/g, '&quot;' );
    }

    // =========================================
    // 6. Bind All Input Changes to Preview
    // =========================================
    function bindPreviewUpdates() {
        $( '#sab-settings-form' ).on( 'input change', 'input, select, textarea', function () {
            clearTimeout( window.sabPreviewTimer );
            window.sabPreviewTimer = setTimeout( updatePreview, 150 );
        } );
    }

    // =========================================
    // 7. Init
    // =========================================
    $( document ).ready( function () {
        initRangeSliders();
        initColorPickers();
        initConditionalFields();
        bindPreviewUpdates();
        updatePreview(); // Initial render
    } );

} )( jQuery );
