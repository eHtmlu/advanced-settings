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
    const modalBody = modal.querySelector('.advset-modal-body');
    const searchInput = modal.querySelector('.advset-modal-search input');
    
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
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                document.dispatchEvent(new CustomEvent('advset-search', {
                    detail: { query: searchInput.value }
                }));
            });
        }
    }
    
    /**
     * Show loading animation
     */
    function showLoading() {
        const loadingElement = modal.querySelector('.advset-modal-body-processindicator');
        if (loadingElement) {
            loadingElement.classList.add('advset-modal-body-processindicator--processing');
        }
    }
    
    /**
     * Hide loading animation
     */
    function hideLoading() {
        const loadingElement = modal.querySelector('.advset-modal-body-processindicator');
        if (loadingElement) {
            loadingElement.classList.remove('advset-modal-body-processindicator--processing');
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
            // Dispatch modal closed event for React integration
            document.dispatchEvent(new CustomEvent('advset-modal-closed'));
            modal.close();
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
            
            // Load React first, then ReactDOM
            const reactScript = document.createElement('script');
            reactScript.src = advsetAdminUI.wpReactUrl;
            
            reactScript.onload = function() {
                reactLoaded = true;
                
                // Only load ReactDOM after React is fully loaded
                const reactDomScript = document.createElement('script');
                reactDomScript.src = advsetAdminUI.wpReactDomUrl;
                
                reactDomScript.onload = function() {
                    reactDomLoaded = true;
                    checkReactLoaded(resolve);
                };
                
                document.head.appendChild(reactDomScript);
            };
            
            document.head.appendChild(reactScript);
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
     * Initialize React app
     */
    function initializeReactApp() {
        if (reactAppInitialized) return;
        
        const modalContent = modal.querySelector('.advset-modal-body-content');
        if (!modalContent) return;
        
        // Load React app CSS
        const cssLink = document.createElement('link');
        cssLink.rel = 'stylesheet';
        cssLink.href = advsetAdminUI.reactAppCssUrl;
        document.head.appendChild(cssLink);
        
        // Load React and ReactDOM
        loadReact().then(() => {
            // Import the React app module directly
            import(advsetAdminUI.reactAppUrl)
                .then(module => {
                    // Initialize the React app using the imported module
                    if (module.AdvSetModalApp) {
                        document.addEventListener('advset-data-loaded', function() {
                            if (searchInput?.value) {
                                document.dispatchEvent(new CustomEvent('advset-search', {
                                    detail: { query: searchInput.value }
                                }));
                            }
                        });
                        module.AdvSetModalApp.init(modalContent);
                        reactAppInitialized = true;
                    } else {
                        console.error('React app not loaded properly');
                    }
                })
                .catch(error => {
                    console.error('Failed to load React app module:', error);
                });
        });
    }
})(); 