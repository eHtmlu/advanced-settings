/**
 * Component Registry
 * 
 * Manages all available UI components for the Advanced Settings plugin
 */
const ComponentRegistry = {
    /**
     * Get a component by its name
     * 
     * @param {string} name - Component name
     * @returns {Object|null} Component object or null if not found
     */
    get(name) {
        if (!window.AdvSetComponents) {
            return null;
        }
        
        return window.AdvSetComponents[name] || null;
    },
    
    /**
     * Render a component by its name
     * 
     * @param {string} name - Component name
     * @param {Object} props - Component properties
     * @returns {string} HTML string for the component
     */
    render(name, props) {
        const component = this.get(name);
        if (!component || !component.render) {
            console.warn(`Component "${name}" not found or does not have a render method`);
            return '';
        }
        
        return component.render(props);
    },
    
    /**
     * Initialize a component by its name
     * 
     * @param {string} name - Component name
     * @param {string} id - Element ID
     * @param {Function} callback - Callback function
     */
    init(name, id, callback) {
        const component = this.get(name);
        if (!component || !component.init) {
            console.warn(`Component "${name}" not found or does not have an init method`);
            return;
        }
        
        component.init(id, callback);
    }
};

// Export for use in other files
window.AdvSetComponentRegistry = ComponentRegistry; 