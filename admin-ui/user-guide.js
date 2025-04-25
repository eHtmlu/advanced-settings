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
    let userGuideClosed = false;
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
        if (userGuideClosed) return;

        if (userGuideStep === 1) {
            const adminBarIcon = document.querySelector('#wp-admin-bar-advset-admin-icon .ab-item');
            if (!adminBarIcon) return;

            createTooltip({
                content: __('Click here to open Advanced Settings 3', 'advanced-settings'),
                progress: '1/2',
                isLast: false,
                onBeforeNextClick: function() {
                    advset_open_modal();
                },
                bindTo: adminBarIcon,
                parent: document.body,
                arrowPosition: 'top-left',
            });

            adminBarIcon.addEventListener('click', function() {
                document.dispatchEvent(new CustomEvent('advset-guide-next'));
            }, { once: true });
        }
        else if (userGuideStep === 2) {
            const searchInput = modal.querySelector('.advset-modal-search input');
            if (!searchInput) return;

            createTooltip({
                content: __('Use this search field to quickly find settings', 'advanced-settings'),
                progress: '2/2',
                isLast: true,
                bindTo: searchInput,
                parent: modal,
                arrowPosition: 'top-center',
                delay: 500,
                onCloseClick: function() {
                    searchInput.focus();
                },
            });

            searchInput.addEventListener('input', function() {
                document.dispatchEvent(new CustomEvent('advset-guide-close'));
            }, { once: true });
        }
    }

    /**
     * Create a tooltip element
     */
    function createTooltip({content, progress, isLast, onBeforeNextClick, onCloseClick, bindTo, parent, arrowPosition, delay}) {
        const tooltip = document.createElement('div');
        const arrowPositionClass = arrowPosition ? `advset-tooltip--${arrowPosition}-arrow` : '';
        tooltip.className = `advset-setup advset-tooltip ${arrowPositionClass}`;
        
        tooltip.innerHTML = `
            <div class="advset-tooltip__content">${content}</div>
            <div class="advset-tooltip__footer">
                <div class="advset-tooltip__progress">${progress}</div>
                <div class="advset-tooltip__buttons">
                    <button type="button" class="advset-tooltip__button advset-tooltip__skip${isLast ? ' advset-tooltip__button--primary' : ''}">
                        ${isLast ? __('Finish', 'advanced-settings') : __('Skip Guide', 'advanced-settings')}
                    </button>
                    ${!isLast ? `
                        <button type="button" class="advset-tooltip__button advset-tooltip__button--primary advset-tooltip__next">
                            ${__('Next', 'advanced-settings')}
                        </button>
                    ` : ''}
                </div>
            </div>
        `;

        tooltip.querySelector('.advset-tooltip__skip').addEventListener('click', function() {
            userGuideClosed = true;
            document.dispatchEvent(new CustomEvent('advset-guide-close'));
            if (onCloseClick) onCloseClick();
        });

        if (!isLast) {
            tooltip.querySelector('.advset-tooltip__next').addEventListener('click', function() {
                if (onBeforeNextClick) onBeforeNextClick();
                document.dispatchEvent(new CustomEvent('advset-guide-next'));
            });
        }

        function updateTooltipPosition() {
            const bindRect = bindTo.getBoundingClientRect();
            const parentRect = parent === document.body ? {top: 0, left: 0} : parent.getBoundingClientRect();
            tooltip.style.setProperty('--bindto-y', Math.round(bindRect.bottom - parentRect.top) + 'px');
            tooltip.style.setProperty('--bindto-x', Math.round((bindRect.left + (bindRect.width / 2)) - parentRect.left) + 'px');
        }

        const observer = new ResizeObserver(updateTooltipPosition);
        observer.observe(bindTo);

        parent?.appendChild(tooltip);
        updateTooltipPosition();

        setTimeout(() => {
            updateTooltipPosition();
            tooltip.classList.add('advset-tooltip--visible');
        }, delay || 0);

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