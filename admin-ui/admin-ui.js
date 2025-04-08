/**
 * Advanced Settings Admin UI JavaScript
 */

(function() {
    // Get modal element
    const modal = document.getElementById('advset-admin-modal');
    if (!modal) return;
    
    // Get close button
    const closeBtn = modal.querySelector('.advset-modal-close');
    
    // React app initialization state
    let reactAppInitialized = false;
    
    // Setup search input event immediately
    setupSearchInput();
    
    // Function to show loading animation
    function showLoading() {
        const modalBody = modal.querySelector('.advset-modal-body');
        const loadingElement = modalBody.querySelector('.advset-modal-body-loading');
        if (loadingElement) {
            loadingElement.classList.remove('advset-modal-body-loading--hidden');
        }
    }
    
    // Function to hide loading animation
    function hideLoading() {
        const loadingElement = modal.querySelector('.advset-modal-body-loading');
        if (loadingElement) {
            loadingElement.classList.add('advset-modal-body-loading--hidden');
        }
    }
    
    // Function to open modal
    window.advset_open_modal = function() {
        // Show the modal
        modal.showModal();
        
        // Show loading animation
        showLoading();
        
        // Dispatch modal opened event for React integration
        document.dispatchEvent(new CustomEvent('advset-modal-opened'));
        
        // Load content via AJAX
        fetch(advsetAdminUI.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=advset_get_modal_content&nonce=' + advsetAdminUI.nonce
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update modal content
                const modalBodyContent = modal.querySelector('.advset-modal-body-content');
                modalBodyContent.innerHTML = data.data.content;
            }
        })
        .catch(error => {
            console.error('Error loading modal content:', error);
            // Hide loading animation even if there's an error
            hideLoading();
        })
        .finally(() => {
            // Hide loading animation when done
            hideLoading();
        });
    };
    
    // Function to setup search input event
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
    
    // Function to close modal with animation
    function closeModal() {
        const modal = document.getElementById('advset-admin-modal');
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
    
    // Event listeners
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
    document.addEventListener('advset-modal-opened', function() {
        if (!reactAppInitialized) {
            const modalContent = modal.querySelector('.advset-modal-body-content');
            if (modalContent) {
                // Load React app CSS
                const cssLink = document.createElement('link');
                cssLink.rel = 'stylesheet';
                cssLink.href = advsetAdminUI.reactAppCssUrl;
                document.head.appendChild(cssLink);
                
                // Load ComponentRegistry first
                const registryScript = document.createElement('script');
                registryScript.src = advsetAdminUI.componentRegistryUrl;
                registryScript.onload = function() {
                    // Load GenericToggle component
                    const toggleScript = document.createElement('script');
                    toggleScript.src = advsetAdminUI.genericToggleUrl;
                    toggleScript.onload = function() {
                        // Load React app script
                        const appScript = document.createElement('script');
                        appScript.src = advsetAdminUI.reactAppUrl;
                        appScript.onload = function() {
                            window.AdvSetModalApp.init(modalContent);
                            reactAppInitialized = true;
                        };
                        document.head.appendChild(appScript);
                    };
                    document.head.appendChild(toggleScript);
                };
                document.head.appendChild(registryScript);
            }
        }
    });
    
    // Listen for loading events from React app
    document.addEventListener('advset-show-loading', function() {
        showLoading();
    });
    
    document.addEventListener('advset-hide-loading', function() {
        hideLoading();
    });
    
})(); 