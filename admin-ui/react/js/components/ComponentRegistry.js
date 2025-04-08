/**
 * Component Registry
 * 
 * Manages all available UI components for the Advanced Settings plugin.
 * This registry allows for dynamic loading and rendering of components
 * without requiring a build process.
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
            console.warn('Component registry not initialized');
            return null;
        }
        
        const component = window.AdvSetComponents[name];
        if (!component) {
            console.warn(`Component "${name}" not found in registry`);
        }
        
        return component || null;
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
        
        try {
            return component.render(props);
        } catch (error) {
            console.error(`Error rendering component "${name}":`, error);
            return `<div class="advset-component-error">Error rendering component: ${name}</div>`;
        }
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
        
        try {
            // Use setTimeout to ensure the DOM is ready
            setTimeout(() => {
                component.init(id, callback);
            }, 0);
        } catch (error) {
            console.error(`Error initializing component "${name}":`, error);
        }
    },
    
    /**
     * Register a new component
     * 
     * @param {string} name - Component name
     * @param {Object} component - Component object with render and init methods
     */
    register(name, component) {
        if (!window.AdvSetComponents) {
            window.AdvSetComponents = {};
        }
        
        if (!component.render || !component.init) {
            console.warn(`Component "${name}" is missing required methods (render, init)`);
            return;
        }
        
        window.AdvSetComponents[name] = component;
    }
};

// Export for use in other files
window.AdvSetComponentRegistry = ComponentRegistry; 