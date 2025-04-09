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