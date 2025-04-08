/**
 * React App for Advanced Settings Modal
 * 
 * A lightweight React-like application for managing advanced settings
 * without requiring a build process or JSX
 */
const AdvSetModalApp = {
    /**
     * Application state
     */
    state: {
        searchQuery: '',
        items: [],
        allItems: [], // Cache for all items
        isLoading: false,
        categories: [],
        settings: {} // Store for settings values
    },

    /**
     * Initialize the application
     * 
     * @param {HTMLElement} container - The container element to render into
     */
    init(container) {
        this.container = container;
        this.setupEventListeners();
        this.loadAllFeatures();
        this.render();
    },

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        document.addEventListener('advset-search', (event) => {
            this.setState({ searchQuery: event.detail.query });
            this.performLocalSearch(event.detail.query);
        });
    },

    /**
     * Update the application state and re-render
     * 
     * @param {Object} newState - The new state to merge with existing state
     */
    setState(newState) {
        this.state = { ...this.state, ...newState };
        this.render();
    },

    /**
     * Load all features from the API
     */
    async loadAllFeatures() {
        // Dispatch event to show loading
        document.dispatchEvent(new CustomEvent('advset-show-loading'));
        this.setState({ isLoading: true });
        
        try {
            const response = await fetch(`/wp-json/advanced-settings/v1/features`, {
                headers: {
                    'X-WP-Nonce': wpApiSettings.nonce
                }
            });
            
            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }
            
            const data = await response.json();

            this.setState({ 
                allItems: data.features,
                items: data.features, // Initially show all items
                categories: data.categories
            });
            
            // Initialize components after rendering
            setTimeout(() => this.initializeComponents(), 0);
        } catch (error) {
            console.error('Failed to load features:', error);
            // Show error message to user
            this.showError('Failed to load settings. Please try again later.');
        } finally {
            // Dispatch event to hide loading
            document.dispatchEvent(new CustomEvent('advset-hide-loading'));
            this.setState({ isLoading: false });
        }
    },

    /**
     * Perform local search on items
     * 
     * @param {string} query - The search query
     */
    performLocalSearch(query) {
        if (!query) {
            // If query is empty, show all items
            this.setState({ items: this.state.allItems });
            return;
        }
        
        // Filter items locally
        const filteredItems = this.state.allItems.filter(item => {
            const searchText = `${item.title} ${item.description} ${item.label}`.toLowerCase();
            return searchText.includes(query.toLowerCase());
        });
        
        this.setState({ items: filteredItems });
    },

    /**
     * Render the application
     */
    render() {
        const { searchQuery, items, isLoading } = this.state;
        
        // Update the no results element
        const noResultsElement = document.querySelector('.advset-no-results');
        if (noResultsElement) {
            noResultsElement.style.display = items.length ? 'none' : 'block';
        }
        
        // Render the content
        const content = this.renderContent(items);
        this.container.innerHTML = content;
    },

    /**
     * Render the content for all items
     * 
     * @param {Array} items - The items to render
     * @returns {string} HTML string
     */
    renderContent(items) {
        if (!items.length) {
            return '';
        }

        return items.map(item => this.renderItem(item)).join('');
    },

    /**
     * Render a single item
     * 
     * @param {Object} item - The item to render
     * @returns {string} HTML string
     */
    renderItem(item) {
        // Generate a unique ID for the component
        const componentId = `advset-${item.id.replace(/\./g, '-')}`;
        
        // Get the component name from the item
        const componentName = item.ui_component || 'GenericToggle';
        
        // Prepare props for the component
        const props = {
            id: componentId,
            label: item.label || item.title,
            checked: this.state.settings[item.id] || false
        };
        
        // Render the component using the registry
        const componentHtml = window.AdvSetComponentRegistry ? 
            window.AdvSetComponentRegistry.render(componentName, props) : '';
        
        return `
            <div class="advset-item" data-id="${item.id}">
                <div class="advset-item-header">
                    <h3>${item.title}</h3>
                    <span class="advset-item-category">${item.category}</span>
                </div>
                <p>${item.description}</p>
                <div class="advset-item-control">
                    ${componentHtml}
                </div>
            </div>
        `;
    },
    
    /**
     * Initialize all components after rendering
     */
    initializeComponents() {
        const { items } = this.state;
        
        items.forEach(item => {
            const componentId = `advset-${item.id.replace(/\./g, '-')}`;
            const componentName = item.ui_component || 'GenericToggle';
            
            // Initialize the component with a callback
            if (window.AdvSetComponentRegistry) {
                window.AdvSetComponentRegistry.init(componentName, componentId, (value) => {
                    this.handleSettingChange(item.id, value);
                });
            }
        });
    },
    
    /**
     * Handle setting changes
     * 
     * @param {string} settingId - The ID of the setting
     * @param {any} value - The new value
     */
    handleSettingChange(settingId, value) {
        // Update the state
        this.setState({
            settings: {
                ...this.state.settings,
                [settingId]: value
            }
        });
        
        // Save the setting to the server
        this.saveSetting(settingId, value);
    },
    
    /**
     * Save a setting to the server
     * 
     * @param {string} settingId - The ID of the setting
     * @param {any} value - The new value
     */
    async saveSetting(settingId, value) {
        try {
            const response = await fetch('/wp-json/advanced-settings/v1/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                body: JSON.stringify({
                    settings: {
                        [settingId]: value
                    }
                })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                console.error('Failed to save setting:', data);
                this.showError(`Failed to save setting: ${data.message || 'Unknown error'}`);
            }
        } catch (error) {
            console.error('Error saving setting:', error);
            this.showError('Failed to save setting. Please try again later.');
        }
    },
    
    /**
     * Show an error message to the user
     * 
     * @param {string} message - The error message to display
     */
    showError(message) {
        // Create error element if it doesn't exist
        let errorElement = document.querySelector('.advset-error-message');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'advset-error-message';
            this.container.prepend(errorElement);
        }
        
        // Set the error message
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        
        // Hide after 5 seconds
        setTimeout(() => {
            errorElement.style.display = 'none';
        }, 5000);
    }
};

// Export for use in other files
window.AdvSetModalApp = AdvSetModalApp; 