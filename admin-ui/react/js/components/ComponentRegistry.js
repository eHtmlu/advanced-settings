/**
 * Component Registry
 * 
 * A registry for managing and rendering components in the Advanced Settings plugin.
 * This registry allows components to be registered and rendered dynamically.
 */

class ComponentRegistry {
    constructor() {
        this.components = new Map();
    }

    /**
     * Register a new component
     * 
     * @param {string} name - Component name
     * @param {Object} component - Component implementation
     */
    register(name, component) {
        if (!name || typeof name !== 'string') {
            console.warn('ComponentRegistry: Invalid component name');
            return;
        }

        if (!component || typeof component !== 'object') {
            console.warn(`ComponentRegistry: Invalid component implementation for ${name}`);
            return;
        }

        this.components.set(name, component);
    }

    /**
     * Get a registered component
     * 
     * @param {string} name - Component name
     * @returns {Object|null} Component implementation or null if not found
     */
    get(name) {
        return this.components.get(name) || null;
    }

    /**
     * Render a component
     * 
     * @param {string} name - Component name
     * @param {Object} props - Component properties
     * @returns {string} Rendered HTML
     */
    render(name, props = {}) {
        const component = this.get(name);
        if (!component || typeof component.render !== 'function') {
            console.warn(`ComponentRegistry: Component ${name} not found or invalid`);
            return '';
        }

        return component.render(props);
    }

    /**
     * Initialize a component
     * 
     * @param {string} name - Component name
     * @param {string} id - Element ID
     * @param {Function} onChange - Change callback
     */
    init(name, id, onChange) {
        const component = this.get(name);
        if (!component || typeof component.init !== 'function') {
            console.warn(`ComponentRegistry: Component ${name} not found or invalid`);
            return;
        }

        component.init(id, onChange);
    }
}

// Create and export a singleton instance
const registry = new ComponentRegistry();
export default registry;

// Make available globally for backward compatibility
window.AdvSetComponentRegistry = registry; 