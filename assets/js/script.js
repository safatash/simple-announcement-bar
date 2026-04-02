/**
 * Simple Announcement Bar — Frontend Script
 * Version: 2.0.0
 * Vanilla JavaScript — no external dependencies
 */

( function () {
    'use strict';

    // =========================================
    // Settings & State
    // =========================================
    var S = ( typeof simpanbarSettings !== 'undefined' ) ? simpanbarSettings : {};

    var cfg = {
        position:           S.position           || 'top',
        isSticky:           parseInt( S.isSticky )           || 0,
        delay:              parseInt( S.delay )              || 0,
        animation:          S.animation          || 'slide',
        loadMinimized:      parseInt( S.loadMinimized )      || 0,
        showLimit:          parseInt( S.showLimit )          || 0,
        scrollShowPercent:  parseInt( S.scrollShowPercent )  || 0,
        hideOnScrollDown:   parseInt( S.hideOnScrollDown )   || 0,
        hideMobile:         parseInt( S.hideMobile )         || 0,
        hideDesktop:        parseInt( S.hideDesktop )        || 0,
        enableCountdown:    parseInt( S.enableCountdown )    || 0,
        countdownTarget:    S.countdownTarget    || '',
        hideAfterCountdown: parseInt( S.hideAfterCountdown ) || 0,
        serverTime:         parseInt( S.serverTime )         || Date.now(),
    };

    var SAB_DISMISSED_KEY = 'simpanbar_dismissed';
    var SAB_SHOW_COUNT_KEY = 'simpanbar_show_count';

    var bar, openBtn, countdownTimer;
    var isBarVisible = false;
    var lastScrollY = 0;
    var scrollListenerAttached = false;
    var timeDrift = Date.now() - cfg.serverTime; // Correct for server/client time difference

    // =========================================
    // Helpers
    // =========================================
    function isMobile() {
        return window.innerWidth <= 768;
    }

    function getShowCount() {
        return parseInt( localStorage.getItem( SAB_SHOW_COUNT_KEY ) ) || 0;
    }

    function incrementShowCount() {
        localStorage.setItem( SAB_SHOW_COUNT_KEY, getShowCount() + 1 );
    }

    function isDismissed() {
        return localStorage.getItem( SAB_DISMISSED_KEY ) === '1';
    }

    function setDismissed() {
        localStorage.setItem( SAB_DISMISSED_KEY, '1' );
    }

    function clearDismissed() {
        localStorage.removeItem( SAB_DISMISSED_KEY );
    }

    // =========================================
    // Device Targeting
    // =========================================
    function passesDeviceCheck() {
        if ( cfg.hideMobile && isMobile() ) return false;
        if ( cfg.hideDesktop && ! isMobile() ) return false;
        return true;
    }

    // =========================================
    // Show Limit Check
    // =========================================
    function passesShowLimit() {
        if ( cfg.showLimit === 0 ) return true;
        return getShowCount() < cfg.showLimit;
    }

    // =========================================
    // Show Bar
    // =========================================
    function showBar() {
        if ( ! bar ) return;
        if ( isBarVisible ) return;

        isBarVisible = true;
        bar.setAttribute( 'aria-hidden', 'false' );
        bar.classList.remove( 'simpanbar-hiding' );
        bar.classList.add( 'simpanbar-visible' );

        if ( cfg.isSticky ) {
            applyBodyPadding();
        }

        // Hide the open button if visible
        if ( openBtn ) {
            openBtn.style.display = 'none';
        }

        incrementShowCount();
    }

    // =========================================
    // Hide Bar (dismiss)
    // =========================================
    function hideBar( persist ) {
        if ( ! bar ) return;
        if ( ! isBarVisible ) return;

        isBarVisible = false;
        bar.setAttribute( 'aria-hidden', 'true' );
        bar.classList.remove( 'simpanbar-visible' );
        bar.classList.add( 'simpanbar-hiding' );

        if ( persist ) {
            setDismissed();
        }

        // After animation ends, fully hide and clean up
        bar.addEventListener( 'transitionend', function onEnd() {
            if ( ! isBarVisible ) {
                removeBodyPadding();
                // Show open button if configured
                if ( openBtn ) {
                    openBtn.style.display = '';
                }
            }
            bar.removeEventListener( 'transitionend', onEnd );
        }, { once: true } );

        // Fallback for 'none' animation (no transitionend fires)
        if ( cfg.animation === 'none' ) {
            removeBodyPadding();
            if ( openBtn ) openBtn.style.display = '';
        }
    }

    // =========================================
    // Body Padding (sticky compensation)
    // =========================================
    function applyBodyPadding() {
        var h = bar.offsetHeight;
        document.documentElement.style.setProperty( '--simpanbar-bar-height', h + 'px' );
        document.body.classList.add( 'simpanbar-sticky-' + cfg.position );
        window.addEventListener( 'resize', recalcBodyPadding );
    }

    function recalcBodyPadding() {
        if ( ! isBarVisible || ! bar ) return;
        document.documentElement.style.setProperty( '--simpanbar-bar-height', bar.offsetHeight + 'px' );
    }

    function removeBodyPadding() {
        document.body.classList.remove( 'simpanbar-sticky-top', 'simpanbar-sticky-bottom' );
        document.documentElement.style.removeProperty( '--simpanbar-bar-height' );
        window.removeEventListener( 'resize', recalcBodyPadding );
    }

    // =========================================
    // Scroll Behavior
    // =========================================
    function getScrollPercent() {
        var scrollTop = window.scrollY || document.documentElement.scrollTop;
        var docHeight = document.documentElement.scrollHeight - window.innerHeight;
        if ( docHeight <= 0 ) return 0;
        return ( scrollTop / docHeight ) * 100;
    }

    function handleScroll() {
        var currentScrollY = window.scrollY || document.documentElement.scrollTop;
        var scrollPercent = getScrollPercent();

        // Show after scroll %
        if ( cfg.scrollShowPercent > 0 && ! isBarVisible && ! isDismissed() ) {
            if ( scrollPercent >= cfg.scrollShowPercent ) {
                showBar();
            }
        }

        // Hide on scroll down / show on scroll up
        if ( cfg.hideOnScrollDown ) {
            if ( currentScrollY > lastScrollY && isBarVisible ) {
                // Scrolling down — hide without persisting
                isBarVisible = false;
                bar.classList.remove( 'simpanbar-visible' );
                bar.classList.add( 'simpanbar-hiding' );
                removeBodyPadding();
            } else if ( currentScrollY < lastScrollY && ! isBarVisible && ! isDismissed() ) {
                // Scrolling up — show again
                showBar();
            }
        }

        lastScrollY = currentScrollY;
    }

    function attachScrollListener() {
        if ( scrollListenerAttached ) return;
        scrollListenerAttached = true;
        window.addEventListener( 'scroll', handleScroll, { passive: true } );
    }

    // =========================================
    // Countdown Timer
    // =========================================
    function startCountdown() {
        if ( ! cfg.enableCountdown || ! cfg.countdownTarget ) return;

        var targetTime = new Date( cfg.countdownTarget ).getTime();
        if ( isNaN( targetTime ) ) return;

        var elD = document.getElementById( 'simpanbar-cd-d' );
        var elH = document.getElementById( 'simpanbar-cd-h' );
        var elM = document.getElementById( 'simpanbar-cd-m' );
        var elS = document.getElementById( 'simpanbar-cd-s' );

        if ( ! elD || ! elH || ! elM || ! elS ) return;

        function pad( n ) {
            return n < 10 ? '0' + n : String( n );
        }

        function tick() {
            var now = Date.now() - timeDrift; // Corrected time
            var diff = targetTime - now;

            if ( diff <= 0 ) {
                elD.textContent = '00';
                elH.textContent = '00';
                elM.textContent = '00';
                elS.textContent = '00';
                clearInterval( countdownTimer );
                if ( cfg.hideAfterCountdown ) {
                    hideBar( false );
                }
                return;
            }

            var days    = Math.floor( diff / ( 1000 * 60 * 60 * 24 ) );
            var hours   = Math.floor( ( diff % ( 1000 * 60 * 60 * 24 ) ) / ( 1000 * 60 * 60 ) );
            var minutes = Math.floor( ( diff % ( 1000 * 60 * 60 ) ) / ( 1000 * 60 ) );
            var seconds = Math.floor( ( diff % ( 1000 * 60 ) ) / 1000 );

            elD.textContent = pad( days );
            elH.textContent = pad( hours );
            elM.textContent = pad( minutes );
            elS.textContent = pad( seconds );
        }

        tick();
        countdownTimer = setInterval( tick, 1000 );
    }

    // =========================================
    // Main Init
    // =========================================
    function init() {
        bar = document.getElementById( 'simpanbar-announcement-bar' );
        openBtn = document.getElementById( 'simpanbar-open-btn' );

        if ( ! bar ) return;

        // Device check
        if ( ! passesDeviceCheck() ) {
            bar.style.display = 'none';
            return;
        }

        // Show limit check
        if ( ! passesShowLimit() ) {
            bar.style.display = 'none';
            return;
        }

        // If previously dismissed, keep hidden (unless open button exists)
        if ( isDismissed() ) {
            bar.style.display = 'none';
            if ( openBtn ) openBtn.style.display = '';
            return;
        }

        // Close button
        var closeBtn = document.getElementById( 'simpanbar-close-btn' );
        if ( closeBtn ) {
            closeBtn.addEventListener( 'click', function () {
                hideBar( true ); // Persist dismissal in localStorage
            } );
        }

        // Open button (reopen)
        if ( openBtn ) {
            openBtn.style.display = 'none'; // Hidden until bar is dismissed
            openBtn.addEventListener( 'click', function () {
                clearDismissed();
                openBtn.style.display = 'none';
                showBar();
            } );
        }

        // Attach scroll listener if needed
        if ( cfg.scrollShowPercent > 0 || cfg.hideOnScrollDown ) {
            attachScrollListener();
        }

        // Determine if we should show immediately or wait for scroll
        var showImmediately = cfg.scrollShowPercent === 0;

        if ( cfg.loadMinimized ) {
            // Start hidden, show open button
            if ( openBtn ) openBtn.style.display = '';
            showImmediately = false;
        }

        if ( showImmediately ) {
            if ( cfg.delay > 0 ) {
                setTimeout( showBar, cfg.delay * 1000 );
            } else {
                // Slight defer to allow CSS to apply before animating
                requestAnimationFrame( function () {
                    requestAnimationFrame( showBar );
                } );
            }
        }

        // Start countdown if enabled
        startCountdown();
    }

    // =========================================
    // Boot
    // =========================================
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
