/**
 * Generic Toggle Component
 * 
 * A simple toggle switch component for boolean settings.
 * This component provides a clean, accessible toggle UI that works
 * well for boolean settings like enabling/disabling features.
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
        const { id, label, checked = false } = props;
        
        // Ensure we have a valid ID
        if (!id) {
            console.warn('GenericToggle: Missing required id prop');
            return '<div class="advset-component-error">Toggle component error: Missing ID</div>';
        }
        
        return `
            <div class="advset-toggle-container">
                <label class="advset-toggle" for="${id}">
                    <input 
                        type="checkbox" 
                        id="${id}" 
                        class="advset-toggle-input" 
                        ${checked ? 'checked' : ''}
                        data-component="generic-toggle"
                        aria-checked="${checked ? 'true' : 'false'}"
                    >
                    <span class="advset-toggle-slider"></span>
                </label>
                <span class="advset-toggle-label">${label || ''}</span>
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
        if (!toggle) {
            console.warn(`GenericToggle: Element with id "${id}" not found`);
            return;
        }
        
        toggle.addEventListener('change', (event) => {
            if (onChange && typeof onChange === 'function') {
                onChange(event.target.checked);
            }
        });
        
        // Add keyboard accessibility
        toggle.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggle.checked = !toggle.checked;
                toggle.dispatchEvent(new Event('change'));
            }
        });
    }
};

// Register the component with the registry
if (window.AdvSetComponentRegistry) {
    window.AdvSetComponentRegistry.register('GenericToggle', GenericToggle);
}

// Export for use in other files
window.AdvSetComponents = window.AdvSetComponents || {};
window.AdvSetComponents.GenericToggle = GenericToggle; 