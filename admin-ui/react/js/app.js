/**
 * React App for Advanced Settings Modal
 * 
 * A lightweight React application for managing advanced settings
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
        
        // Clear the container
        this.container.innerHTML = '';
        
        // Create the main app element
        const appElement = document.createElement('div');
        appElement.className = 'advset-react-app';
        
        // Render the content
        if (items.length) {
            const itemsContainer = document.createElement('div');
            itemsContainer.className = 'advset-results';
            
            items.forEach(item => {
                const itemElement = this.createItemElement(item);
                itemsContainer.appendChild(itemElement);
            });
            
            appElement.appendChild(itemsContainer);
        }
        
        // Add the app element to the container
        this.container.appendChild(appElement);
    },

    /**
     * Create a DOM element for a single item
     * 
     * @param {Object} item - The item to render
     * @returns {HTMLElement} DOM element for the item
     */
    createItemElement(item) {
        // Create the item container
        const itemElement = document.createElement('div');
        itemElement.className = 'advset-item';
        itemElement.dataset.id = item.id;
        
        // Create the header
        const headerElement = document.createElement('div');
        headerElement.className = 'advset-item-header';
        
        const titleElement = document.createElement('h3');
        titleElement.textContent = item.title;
        
        const categoryElement = document.createElement('span');
        categoryElement.className = 'advset-item-category';
        categoryElement.textContent = item.category;
        
        headerElement.appendChild(titleElement);
        headerElement.appendChild(categoryElement);
        
        // Create the description
        const descriptionElement = document.createElement('p');
        descriptionElement.textContent = item.description;
        
        // Create the control container
        const controlElement = document.createElement('div');
        controlElement.className = 'advset-item-control';
        
        // Create the component
        const componentId = `advset-${item.id.replace(/\./g, '-')}`;
        const componentName = item.ui_component || 'GenericToggle';
        
        // Create the component element
        const componentElement = document.createElement('div');
        componentElement.innerHTML = window.AdvSetComponentRegistry ? 
            window.AdvSetComponentRegistry.render(componentName, {
                id: componentId,
                label: item.label || item.title,
                checked: this.state.settings[item.id] || false
            }) : '';
        
        // Initialize the component
        if (window.AdvSetComponentRegistry) {
            window.AdvSetComponentRegistry.init(componentName, componentId, (value) => {
                this.handleSettingChange(item.id, value);
            });
        }
        
        // Add all elements to the item
        controlElement.appendChild(componentElement);
        itemElement.appendChild(headerElement);
        itemElement.appendChild(descriptionElement);
        itemElement.appendChild(controlElement);
        
        return itemElement;
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