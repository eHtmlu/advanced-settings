/**
 * Generic Component
 * 
 * A flexible component that supports multiple field types and conditional visibility
 */

export function SettingComponentGeneric(props) {
    const { id, texts, value = {}, onChange, config } = props;

    // Ensure we have a valid ID and config
    if (!id || !config || !config.fields) {
        console.warn('SettingComponentGeneric: Missing required props');
        return React.createElement('div', { 
            className: 'advset-component-error' 
        }, 'Generic component error: Missing required props');
    }
    
    // Handle field value changes
    const handleFieldChange = (fieldId, fieldValue) => {
        onChange({
            ...value,
            [fieldId]: fieldValue
        });
    };
    
    // Check if a field should be visible based on its conditions
    const isFieldVisible = (field) => {
        if (!field.visible) return true;
        
        return Object.entries(field.visible).every(([fieldId, expectedValue]) => {
            const currentValue = value[fieldId] ?? config.fields[fieldId].default;
            return currentValue === expectedValue;
        });
    };
    
    // Render a field based on its type
    const renderField = (fieldId, field) => {
        if (!isFieldVisible(field)) return null;
        
        const fieldValue = fieldId in value ? value[fieldId] : ('default' in field ? field.default : null);
        
        switch (field.type) {
            case 'toggle':
                return React.createElement('div', { 
                    key: fieldId,
                    className: 'advset-generic-field advset-generic-field-toggle'
                },
                    React.createElement('label', { className: 'advset-generic-field-toggle-container', htmlFor: `${id}-${fieldId}` },
                        React.createElement('div', { className: 'advset-generic-field-toggle-element' },
                            React.createElement('input', {
                                type: 'checkbox',
                                id: `${id}-${fieldId}`,
                                className: 'advset-generic-field-toggle-input',
                                checked: fieldValue === true,
                                'data-component': 'generic-toggle',
                                'aria-checked': fieldValue === true ? 'true' : 'false',
                                onChange: (e) => handleFieldChange(fieldId, e.target.checked)
                            }),
                            React.createElement('span', { className: 'advset-generic-field-toggle-element-slider' })
                        ),
                        React.createElement('span', { className: 'advset-generic-field-toggle-label' }, field.label)
                    ),
                    (field.description || field.descriptionHtml) && React.createElement('p', { 
                        className: 'advset-generic-field-description advset-generic-field-toggle-description',
                        ...(field.descriptionHtml ? { dangerouslySetInnerHTML: { __html: field.descriptionHtml } } : { children: field.description })
                    })
                );
                
            case 'radio':
                return React.createElement('div', {
                    key: fieldId,
                    className: 'advset-generic-field advset-generic-field-radio'
                },
                    React.createElement('fieldset', { className: 'advset-generic-field-radio-fieldset' },
                        React.createElement('legend', { className: 'advset-generic-field-radio-legend' }, field.label),
                        React.createElement('div', { className: 'advset-generic-field-radio-options' },
                            Object.entries(field.options).map(([optionId, option]) =>
                                React.createElement('label', {
                                    key: optionId,
                                    className: 'advset-generic-field-radio-option',
                                    htmlFor: `${id}-${fieldId}-${optionId}`
                                },
                                    React.createElement('input', {
                                        type: 'radio',
                                        name: `${id}-${fieldId}`,
                                        id: `${id}-${fieldId}-${optionId}`,
                                        value: optionId,
                                        checked: fieldValue === optionId,
                                        onChange: (e) => handleFieldChange(fieldId, e.target.value)
                                    }),
                                    React.createElement('span', { className: 'advset-generic-field-radio-option-label' }, option.label),
                                    option.description && React.createElement('p', { 
                                        className: 'advset-generic-field-radio-option-description' 
                                    }, option.description)
                                )
                            )
                        ),
                        (field.description || field.descriptionHtml) && React.createElement('p', { 
                            className: 'advset-generic-field-description advset-generic-field-radio-description',
                            ...(field.descriptionHtml ? { dangerouslySetInnerHTML: { __html: field.descriptionHtml } } : { children: field.description })
                        })
                    )
                );

            case 'checkbox':
                return React.createElement('div', {
                    key: fieldId,
                    className: 'advset-generic-field advset-generic-field-checkbox'
                },
                    React.createElement('label', { className: 'advset-generic-field-checkbox-container', htmlFor: `${id}-${fieldId}` },
                        React.createElement('div', { className: 'advset-generic-field-checkbox-element' },
                            React.createElement('input', {
                                type: 'checkbox',
                                id: `${id}-${fieldId}`,
                                className: 'advset-generic-field-checkbox-input',
                                checked: fieldValue === true,
                                'data-component': 'generic-checkbox',
                                'aria-checked': fieldValue === true ? 'true' : 'false',
                                onChange: (e) => handleFieldChange(fieldId, e.target.checked)
                            }),
                            React.createElement('span', { className: 'advset-generic-field-checkbox-element-checkmark' })
                        ),
                        React.createElement('span', { className: 'advset-generic-field-checkbox-label' }, field.label)
                    ),
                    (field.description || field.descriptionHtml) && React.createElement('p', { 
                        className: 'advset-generic-field-description advset-generic-field-checkbox-description',
                        ...(field.descriptionHtml ? { dangerouslySetInnerHTML: { __html: field.descriptionHtml } } : { children: field.description })
                    })
                );
            
            case 'select':
                return React.createElement('div', {
                    key: fieldId,
                    className: 'advset-generic-field advset-generic-field-select'
                },
                    React.createElement('label', { className: 'advset-generic-field-select-container', htmlFor: `${id}-${fieldId}` },
                        React.createElement('span', { className: 'advset-generic-field-select-label' }, field.label),
                        React.createElement('span', { className: 'advset-generic-field-select-input-container' },
                            React.createElement('select', {
                                id: `${id}-${fieldId}`,
                                className: 'advset-generic-field-select-input',
                                ...(typeof fieldValue === 'string' && typeof field.options[fieldValue] !== 'undefined' ? { value: fieldValue } : {}),
                                onChange: (e) => handleFieldChange(fieldId, e.target.value)
                            },
                                Object.entries(field.options).map(([optionId, option]) =>
                                    React.createElement('option', {
                                        key: optionId,
                                        value: optionId
                                    }, option.label)
                                )
                            )
                        )
                    ),
                    (field.description || field.descriptionHtml) && React.createElement('p', { 
                        className: 'advset-generic-field-description advset-generic-field-select-description',
                        ...(field.descriptionHtml ? { dangerouslySetInnerHTML: { __html: field.descriptionHtml } } : { children: field.description })
                    })
                );

            case 'text':
            case 'number':
            case 'email':
            case 'url':
            case 'tel':
            case 'password':
            case 'color':
            case 'date':
            case 'time':
            case 'datetime-local':
            case 'month':
            case 'week':
            case 'range':
                const inputType = field.type;
                return React.createElement('div', {
                    key: fieldId,
                    className: `advset-generic-field advset-generic-field-textual advset-generic-field-textual--${inputType}`
                },
                    React.createElement('label', { className: 'advset-generic-field-textual-container', htmlFor: `${id}-${fieldId}` },
                        React.createElement('span', { className: 'advset-generic-field-textual-label' }, field.label),
                        React.createElement('span', { className: 'advset-generic-field-textual-input-container' },
                            React.createElement('input', {
                                type: inputType,
                                id: `${id}-${fieldId}`,
                                className: 'advset-generic-field-textual-input',
                                ...(typeof fieldValue === 'string' ? { value: fieldValue } : {value: ''}),
                                ...(
                                    ['number', 'date', 'time', 'datetime-local', 'month', 'week', 'range'].includes(inputType) ? {
                                        ...(typeof field.min !== 'undefined' && { min: field.min }),
                                        ...(typeof field.max !== 'undefined' && { max: field.max }),
                                        ...(typeof field.step !== 'undefined' && { step: field.step })
                                    } : {}
                                ),
                                ...(
                                    ['text', 'url', 'email', 'tel', 'password'].includes(inputType) ? {
                                        ...(typeof field.pattern === 'string' && { pattern: field.pattern }),
                                    } : {}
                                ),
                                ...(field.placeholder ? { placeholder: field.placeholder } : {}),
                                onChange: (e) => handleFieldChange(fieldId, e.target.value)
                            })
                        )
                    ),
                    (field.description || field.descriptionHtml) && React.createElement('p', { 
                        className: 'advset-generic-field-description advset-generic-field-textual-description',
                        ...(field.descriptionHtml ? { dangerouslySetInnerHTML: { __html: field.descriptionHtml } } : { children: field.description })
                    })
                );

            case 'info':
                return React.createElement('div', {
                    key: fieldId,
                    className: 'advset-generic-field advset-generic-field-info'
                },
                    field.label && React.createElement('h4', { className: 'advset-generic-field-info-label' }, field.label),
                    (field.description || field.descriptionHtml) && React.createElement('p', { 
                        className: 'advset-generic-field-description advset-generic-field-info-text',
                        ...(field.descriptionHtml ? { dangerouslySetInnerHTML: { __html: field.descriptionHtml } } : { children: field.description })
                    })
                );
                
            default:
                console.warn(`SettingComponentGeneric: Unknown field type "${field.type}"`);
                return null;
        }
    };
    
    return React.createElement('div', { className: 'advset-generic' },
        //React.createElement('h3', { className: 'advset-generic-title' }, texts.title),
        //texts.description && React.createElement('p', { className: 'advset-generic-description' }, texts.description),
        React.createElement('div', { className: 'advset-generic-fields' },
            Object.entries(config.fields).map(([fieldId, field]) => renderField(fieldId, field))
        )
    );
} 