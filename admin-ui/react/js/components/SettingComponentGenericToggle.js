/**
 * Generic Toggle Component
 * 
 * A simple toggle switch component for boolean settings.
 * This component provides a clean, accessible toggle UI that works
 * well for boolean settings like enabling/disabling features.
 */

/**
 * Generic Toggle Component
 * 
 * @param {Object} props - Component properties
 * @param {string} props.id - Unique identifier for the toggle
 * @param {string} props.label - Label text for the toggle
 * @param {boolean} props.checked - Whether the toggle is checked
 * @param {Function} props.onChange - Callback function when toggle state changes
 * @returns {ReactElement} React element for the toggle component
 */
export function SettingComponentGenericToggle(props) {
    const { id, label, checked = false, onChange } = props;
    
    // Ensure we have a valid ID
    if (!id) {
        console.warn('SettingComponentGenericToggle: Missing required id prop');
        return React.createElement('div', { 
            className: 'advset-component-error' 
        }, 'Toggle component error: Missing ID');
    }
    
    return React.createElement('label', { className: 'advset-toggle-container', htmlFor: id },
        React.createElement('div', { className: 'advset-toggle' },
            React.createElement('input', {
                type: 'checkbox',
                id: id,
                className: 'advset-toggle-input',
                checked: checked,
                'data-component': 'generic-toggle',
                'aria-checked': checked ? 'true' : 'false',
                onChange: (e) => onChange && onChange(e.target.checked)
            }),
            React.createElement('span', { className: 'advset-toggle-slider' })
        ),
        React.createElement('span', { className: 'advset-toggle-label' }, label || '')
    );
}

// Register the component with the registry for backward compatibility
if (window.AdvSetComponentRegistry) {
    window.AdvSetComponentRegistry.register('SettingComponentGenericToggle', {
        render: (props) => {
            const element = SettingComponentGenericToggle(props);
            return ReactDOMServer.renderToString(element);
        },
        init: (id, onChange) => {
            const toggle = document.getElementById(id);
            if (!toggle) {
                console.warn(`SettingComponentGenericToggle: Element with id "${id}" not found`);
                return;
            }
            
            // Remove any existing event listeners to prevent duplicates
            const newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);
            
            newToggle.addEventListener('change', (event) => {
                if (onChange && typeof onChange === 'function') {
                    onChange(event.target.checked);
                }
            });
            
            // Add keyboard accessibility
            newToggle.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    newToggle.checked = !newToggle.checked;
                    newToggle.dispatchEvent(new Event('change'));
                }
            });
        }
    });
}

// Make available globally for backward compatibility
window.AdvSetComponents = window.AdvSetComponents || {};
window.AdvSetComponents.SettingComponentGenericToggle = SettingComponentGenericToggle; 