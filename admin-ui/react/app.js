/**
 * React App for Advanced Settings Modal
 * 
 * A lightweight React application for managing advanced settings
 * without requiring a build process or JSX
 */

// Import components
import { SettingComponentGeneric } from './components/SettingComponentGeneric.js';
import ComponentRegistry from './ComponentRegistry.js';

// Register components
ComponentRegistry.register('generic', SettingComponentGeneric);

/**
 * Main App Component
 */
function App(props) {
    const { items, categories, onSettingChange, onCategoryClick, settings } = props;
    
    // Group items by category
    const itemsByCategory = {};
    items.forEach(item => {
        if (!itemsByCategory[item.category]) {
            itemsByCategory[item.category] = [];
        }
        itemsByCategory[item.category].push(item);
    });
    
    // Get visible categories (those with items)
    const visibleCategories = categories.filter(category => 
        itemsByCategory[category.id]?.length > 0
    );
    
    return React.createElement('div', { className: 'advset-react-app' },
        // Category sidebar
        React.createElement('div', { className: 'advset-category-sidebar' },
            React.createElement('ul', { className: 'advset-category-menu' },
                visibleCategories.map(category => 
                    React.createElement('li', { 
                        key: category.id,
                        className: 'advset-category-menu-item'
                    },
                        React.createElement('a', {
                            href: `#category-${category.id}`,
                            onClick: (e) => {
                                e.preventDefault();
                                onCategoryClick(category.id);
                            }
                        }, 
                            React.createElement('span', {
                                className: 'advset-category-icon',
                                dangerouslySetInnerHTML: { __html: category.icon || '' }
                            }),
                            React.createElement('span', {
                                className: 'advset-category-text'
                            }, category.title || category.id)
                        )
                    )
                )
            )
        ),
        
        // Results area with categorized items
        React.createElement('div', { className: 'advset-results-container' },
            visibleCategories.map(category => 
                React.createElement('div', { 
                    key: category.id,
                    id: `category-${category.id}`,
                    className: 'advset-category-section'
                },
                    React.createElement('h2', { 
                        className: 'advset-category-title'
                    }, 
                        React.createElement('span', {
                            className: 'advset-category-icon',
                            dangerouslySetInnerHTML: { __html: category.icon || '' }
                        }),
                        React.createElement('span', {
                            className: 'advset-category-text'
                        }, category.title || category.id)
                    ),
                    React.createElement('div', { className: 'advset-results' },
                        itemsByCategory[category.id].map(item => 
                            React.createElement(ItemCard, {
                                key: item.id,
                                item: item,
                                onSettingChange: onSettingChange,
                                settingValue: settings[item.id] || false
                            })
                        )
                    )
                )
            )
        )
    );
}

/**
 * Item Card Component
 */
function ItemCard(props) {
    const { item, onSettingChange, settingValue } = props;
    
    // Get the component from the registry
    const Component = ComponentRegistry.get(item.ui_component || 'generic');
    
    // If no component is found, show an error
    if (!Component) {
        return React.createElement('div', { 
            className: 'advset-item',
            'data-id': item.id
        },
            React.createElement('div', { className: 'advset-component-error' },
                `Component ${item.ui_component} not found`
            )
        );
    }

    return React.createElement('div', { 
        className: 'advset-item',
        'data-id': item.id
    },
        /* React.createElement('div', { className: 'advset-item-header' },
            React.createElement('span', { className: 'advset-item-path' }, item.id.replace(/\./g, ' → ')),
            React.createElement('h3', null, item.title),
        ), */
        React.createElement('div', { className: 'advset-item-control' },
            React.createElement(Component, {
                id: `advset-${item.id.replace(/\./g, '-')}`,
                value: settingValue,
                onChange: (value) => onSettingChange(item.id, value),
                config: item.ui_config || {}
            })
        )
    );
}

/**
 * Advanced Settings Modal App
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
        settings: {}, // Store for settings values
        activeCategory: null // Track active category for scrolling
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
            
            // Dispatch event to indicate data is loaded
            document.dispatchEvent(new CustomEvent('advset-data-loaded'));
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
        
        const searchQuery = query.toLowerCase();
        
        // Filter items locally
        const filteredItems = this.state.allItems.filter(item => {
            // Combine all searchable texts
            const searchTexts = [];
            
            // Add texts from ui_config fields
            if (item.ui_config?.fields) {
                Object.values(item.ui_config.fields).forEach(field => {
                    if (field.label) searchTexts.push(field.label);
                    if (field.description) searchTexts.push(field.description);
                    if (field.options) {
                        Object.values(field.options).forEach(option => {
                            if (option.label) searchTexts.push(option.label);
                            if (option.description) searchTexts.push(option.description);
                        });
                    }
                });
            }
            
            // Join all texts and search
            const searchText = searchTexts.join(' ').toLowerCase();
            return searchText.includes(searchQuery);
        });
        
        this.setState({ items: filteredItems });
    },

    /**
     * Scroll to a specific category
     * 
     * @param {string} categoryId - The category ID to scroll to
     */
    scrollToCategory(categoryId) {
        this.setState({ activeCategory: categoryId });
        
        // Use setTimeout to ensure the DOM has updated
        setTimeout(() => {
            const element = document.getElementById(`category-${categoryId}`);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 0);
    },

    /**
     * Render the application
     */
    render() {
        const { searchQuery, items, isLoading, categories } = this.state;
        
        // Update the no results element
        const noResultsElement = document.querySelector('.advset-no-results');
        if (noResultsElement) {
            noResultsElement.style.display = items.length ? 'none' : 'block';
        }
        
        // Render the React app
        if (window.React && window.ReactDOM) {
            const appElement = React.createElement(App, {
                items: items,
                categories: categories,
                onSettingChange: this.handleSettingChange.bind(this),
                onCategoryClick: this.scrollToCategory.bind(this),
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

// Export for use in other files
export { AdvSetModalApp, App, ItemCard }; 