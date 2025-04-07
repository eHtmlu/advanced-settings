/**
 * Advanced Settings Admin UI JavaScript
 */

(function() {
    // Get modal element
    const modal = document.getElementById('advset-admin-modal');
    if (!modal) return;
    
    // Get close button
    const closeBtn = modal.querySelector('.advset-modal-close');
    
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
    
    // Function to close modal with animation
    function closeModal() {
        const modal = document.getElementById('advset-admin-modal');
        if (modal) {
            // Add closing class to trigger animation
            modal.classList.add('closing');
            
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
    
})(); 