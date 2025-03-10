(function($) {
    document.addEventListener('DOMContentLoaded', function() {
        const dialog = document.getElementById('advset-tracking-dialog');
        const buttons = document.querySelectorAll('[data-choice]');

        // Show dialog after delay
        setTimeout(() => {
            dialog.showModal();
            /* wp.a11y.speak(advsetTracking.dialog_opened); */
        }, 1000);

        // Handle button clicks
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const choice = e.currentTarget.dataset.choice;
                
                $.ajax({
                    url: advsetTracking.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'advset_track_choice',
                        choice: choice,
                        nonce: advsetTracking.nonce
                    },
                    success: function() {
                        dialog.close();
                        /* wp.a11y.speak(
                            choice === 'agree' 
                            ? advsetTracking.thanks_agree 
                            : advsetTracking.thanks_disagree
                        ); */
                    }
                });
            });
        });

        // Close dialog on ESC
        dialog.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
            }
        });
    });
})(jQuery);