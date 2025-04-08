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
        
        // Render the React app
        if (window.React && window.ReactDOM) {
            const appElement = React.createElement(App, {
                items: items,
                onSettingChange: this.handleSettingChange.bind(this),
                settings: this.state.settings
            });
            
            ReactDOM.render(appElement, this.container);
        } else {
            console.error('React or ReactDOM not loaded');
        }
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

/**
 * Main App Component
 */
function App(props) {
    const { items, onSettingChange, settings } = props;
    
    return React.createElement('div', { className: 'advset-react-app' },
        React.createElement('div', { className: 'advset-results' },
            items.map(item => 
                React.createElement(ItemCard, {
                    key: item.id,
                    item: item,
                    onSettingChange: onSettingChange,
                    settingValue: settings[item.id] || false
                })
            )
        )
    );
}

/**
 * Item Card Component
 */
function ItemCard(props) {
    const { item, onSettingChange, settingValue } = props;
    
    return React.createElement('div', { 
        className: 'advset-item',
        'data-id': item.id
    },
        React.createElement('div', { className: 'advset-item-header' },
            React.createElement('h3', null, item.title),
            React.createElement('span', { className: 'advset-item-category' }, item.category)
        ),
        React.createElement('p', null, item.description),
        React.createElement('div', { className: 'advset-item-control' },
            React.createElement(GenericToggle, {
                id: `advset-${item.id.replace(/\./g, '-')}`,
                label: item.label || item.title,
                checked: settingValue,
                onChange: (value) => onSettingChange(item.id, value)
            })
        )
    );
}

/**
 * Generic Toggle Component
 */
function GenericToggle(props) {
    const { id, label, checked, onChange } = props;
    
    return React.createElement('div', { className: 'advset-toggle-container' },
        React.createElement('label', { className: 'advset-toggle', htmlFor: id },
            React.createElement('input', {
                type: 'checkbox',
                id: id,
                className: 'advset-toggle-input',
                checked: checked,
                'data-component': 'generic-toggle',
                'aria-checked': checked ? 'true' : 'false',
                onChange: (e) => onChange(e.target.checked)
            }),
            React.createElement('span', { className: 'advset-toggle-slider' })
        ),
        React.createElement('span', { className: 'advset-toggle-label' }, label || '')
    );
}

// Export for use in other files
window.AdvSetModalApp = AdvSetModalApp; 