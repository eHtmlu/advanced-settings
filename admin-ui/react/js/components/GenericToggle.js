/**
 * Generic Toggle Component
 * 
 * A simple toggle switch component for boolean settings
 */
const GenericToggle = {
    /**
     * Render the toggle component
     * 
     * @param {Object} props - Component properties
     * @param {string} props.id - Unique identifier for the toggle
     * @param {string} props.label - Label text for the toggle
     * @param {boolean} props.checked - Whether the toggle is checked
     * @param {Function} props.onChange - Callback function when toggle state changes
     * @returns {string} HTML string for the toggle component
     */
    render(props) {
        const { id, label, checked = false, onChange } = props;
        
        return `
            <div class="advset-toggle-container">
                <label class="advset-toggle" for="${id}">
                    <input 
                        type="checkbox" 
                        id="${id}" 
                        class="advset-toggle-input" 
                        ${checked ? 'checked' : ''}
                        data-component="generic-toggle"
                    >
                    <span class="advset-toggle-slider"></span>
                </label>
                <span class="advset-toggle-label">${label}</span>
            </div>
        `;
    },
    
    /**
     * Initialize event listeners for the toggle component
     * 
     * @param {string} id - Unique identifier for the toggle
     * @param {Function} onChange - Callback function when toggle state changes
     */
    init(id, onChange) {
        const toggle = document.getElementById(id);
        if (toggle) {
            toggle.addEventListener('change', (event) => {
                if (onChange) {
                    onChange(event.target.checked);
                }
            });
        }
    }
};

// Export for use in other files
window.AdvSetComponents = window.AdvSetComponents || {};
window.AdvSetComponents.GenericToggle = GenericToggle; 