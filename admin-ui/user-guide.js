/**
 * Advanced Settings User Guide
 * 
 * Handles the user guide functionality
 */

(function() {
    'use strict';
    
    // Translation helper (fallback if wp.i18n is not available)
    const __ = (text, domain) => {
        return typeof wp !== 'undefined' && wp.i18n && wp.i18n.__ 
            ? wp.i18n.__(text, domain)
            : text;
    };

    let userGuideStep = 0;
    const modal = document.getElementById('advset-admin-modal');

    // Initialize
    initUserGuide();
    setupEventListeners();

    /**
     * Initialize user guide if needed
     */
    function initUserGuide() {
        if (advsetAdminUI.showUserGuide) {
            userGuideStep = 1;
            showUserGuideStep();
        }
    }

    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        document.addEventListener('advset-guide-next', function() {
            userGuideStep++;
            showUserGuideStep();
        });

        document.addEventListener('advset-guide-close', function() {
            closeUserGuide();
        });
    }

    /**
     * Show current user guide step
     */
    function showUserGuideStep() {
        removeExistingTooltips();

        if (userGuideStep === 1) {
            const adminBarIcon = document.querySelector('#wp-admin-bar-advset-admin-icon .ab-item');
            if (!adminBarIcon) return;

            const tooltip = createTooltip(
                __('Click here to open Advanced Settings', 'advanced-settings'),
                '1/2',
                false
            );

            const iconRect = adminBarIcon.getBoundingClientRect();
            tooltip.style.top = (iconRect.bottom + window.scrollY + 8) + 'px';
            tooltip.style.left = (iconRect.left + (iconRect.width / 2)) + 'px';
            document.body.appendChild(tooltip);

            adminBarIcon.addEventListener('click', function() {
                document.dispatchEvent(new CustomEvent('advset-guide-next'));
            }, { once: true });
        }
        else if (userGuideStep === 2) {
            const searchInput = modal.querySelector('.advset-modal-search input');
            if (!searchInput) return;

            const tooltip = createTooltip(
                __('Use this search field to quickly find settings', 'advanced-settings'),
                '2/2',
                true
            );

            const searchRect = searchInput.getBoundingClientRect();
            const modalRect = modal.getBoundingClientRect();
            
            // Position relative to modal
            tooltip.style.top = (searchRect.bottom - modalRect.top + 8) + 'px';
            tooltip.style.left = (searchRect.left - modalRect.left + (searchRect.width / 2)) + 'px';
            modal.appendChild(tooltip);
        }
    }

    /**
     * Create a tooltip element
     */
    function createTooltip(content, progress, isLast) {
        const tooltip = document.createElement('div');
        tooltip.className = 'advset-tooltip';
        
        tooltip.innerHTML = `
            <div class="advset-tooltip__content">${content}</div>
            <div class="advset-tooltip__footer">
                <div class="advset-tooltip__progress">${progress}</div>
                <button type="button" class="advset-tooltip__close">
                    ${isLast ? __('Finish', 'advanced-settings') : __('Skip Guide', 'advanced-settings')}
                </button>
            </div>
        `;

        tooltip.querySelector('.advset-tooltip__close').addEventListener('click', function() {
            document.dispatchEvent(new CustomEvent('advset-guide-close'));
        });

        return tooltip;
    }

    /**
     * Remove any existing tooltips
     */
    function removeExistingTooltips() {
        document.querySelectorAll('.advset-tooltip').forEach(tooltip => {
            tooltip.remove();
        });
    }

    /**
     * Close the user guide
     */
    function closeUserGuide() {
        removeExistingTooltips();
        userGuideStep = 0;

        // Save that the guide has been shown
        fetch(advsetAdminUI.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'advset_mark_guide_shown',
                nonce: advsetAdminUI.nonce
            })
        });
    }
})(); 