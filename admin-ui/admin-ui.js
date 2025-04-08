/**
 * Advanced Settings Admin UI JavaScript
 * 
 * Handles the modal dialog functionality and integration with the React app
 */

(function() {
    'use strict';
    
    // DOM Elements
    const modal = document.getElementById('advset-admin-modal');
    if (!modal) return;
    
    const closeBtn = modal.querySelector('.advset-modal-close');
    
    // State
    let reactAppInitialized = false;
    let reactLoaded = false;
    let reactDomLoaded = false;
    let dataLoaded = false;
    
    // Initialize
    setupSearchInput();
    setupEventListeners();
    
    /**
     * Setup all event listeners
     */
    function setupEventListeners() {
        // Close button click
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.open) {
                e.preventDefault();
                closeModal();
            }
        });
        
        // Initialize React app when modal is opened
        document.addEventListener('advset-modal-opened', initializeReactApp);
        
        // Listen for loading events from React app
        document.addEventListener('advset-show-loading', showLoading);
        document.addEventListener('advset-hide-loading', hideLoading);
        
        // Listen for data loaded event
        document.addEventListener('advset-data-loaded', function() {
            dataLoaded = true;
            hideLoading();
        });
    }
    
    /**
     * Setup search input event
     */
    function setupSearchInput() {
        const searchInput = modal.querySelector('.advset-modal-search input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // Dispatch search event for React integration
                    document.dispatchEvent(new CustomEvent('advset-search', {
                        detail: { query: e.target.value }
                    }));
                }, 300); // Debounce search for better performance
            });
        }
    }
    
    /**
     * Show loading animation
     */
    function showLoading() {
        const loadingElement = modal.querySelector('.advset-modal-body-loading');
        if (loadingElement) {
            loadingElement.classList.remove('advset-modal-body-loading--hidden');
        }
    }
    
    /**
     * Hide loading animation
     */
    function hideLoading() {
        const loadingElement = modal.querySelector('.advset-modal-body-loading');
        if (loadingElement) {
            loadingElement.classList.add('advset-modal-body-loading--hidden');
        }
    }
    
    /**
     * Open the modal dialog
     */
    window.advset_open_modal = function() {
        // Show the modal
        modal.showModal();
        
        // Show loading animation only if data is not already loaded
        if (!dataLoaded) {
            showLoading();
        } else {
            hideLoading();
        }
        
        // Dispatch modal opened event for React integration
        document.dispatchEvent(new CustomEvent('advset-modal-opened'));
    };
    
    /**
     * Close the modal dialog with animation
     */
    function closeModal() {
        if (modal) {
            // Add closing class to trigger animation
            modal.classList.add('closing');
            
            // Dispatch modal closed event for React integration
            document.dispatchEvent(new CustomEvent('advset-modal-closed'));
            
            // Wait for animation to complete before actually closing
            setTimeout(() => {
                modal.close();
                modal.classList.remove('closing');
            }, 300); // Match the animation duration
        }
    }
    
    /**
     * Load React and ReactDOM dynamically
     */
    function loadReact() {
        return new Promise((resolve) => {
            // Check if React is already loaded
            if (window.React && window.ReactDOM) {
                reactLoaded = true;
                reactDomLoaded = true;
                resolve();
                return;
            }
            
            // Load React
            const reactScript = document.createElement('script');
            reactScript.src = advsetAdminUI.wpReactUrl;
            reactScript.onload = function() {
                reactLoaded = true;
                checkReactLoaded(resolve);
            };
            document.head.appendChild(reactScript);
            
            // Load ReactDOM
            const reactDomScript = document.createElement('script');
            reactDomScript.src = advsetAdminUI.wpReactDomUrl;
            reactDomScript.onload = function() {
                reactDomLoaded = true;
                checkReactLoaded(resolve);
            };
            document.head.appendChild(reactDomScript);
        });
    }
    
    /**
     * Check if both React and ReactDOM are loaded
     */
    function checkReactLoaded(resolve) {
        if (reactLoaded && reactDomLoaded) {
            resolve();
        }
    }
    
    /**
     * Initialize the React app
     */
    function initializeReactApp() {
        if (!reactAppInitialized) {
            const modalContent = modal.querySelector('.advset-modal-body-content');
            if (modalContent) {
                // Load React app CSS
                const cssLink = document.createElement('link');
                cssLink.rel = 'stylesheet';
                cssLink.href = advsetAdminUI.reactAppCssUrl;
                document.head.appendChild(cssLink);
                
                // Load React and ReactDOM
                loadReact().then(() => {
                    // Load React app script
                    const appScript = document.createElement('script');
                    appScript.src = advsetAdminUI.reactAppUrl;
                    appScript.onload = function() {
                        // Initialize the React app
                        if (window.AdvSetModalApp) {
                            window.AdvSetModalApp.init(modalContent);
                            reactAppInitialized = true;
                        } else {
                            console.error('React app not loaded properly');
                        }
                    };
                    document.head.appendChild(appScript);
                });
            }
        }
    }
})(); 