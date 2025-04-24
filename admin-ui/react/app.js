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
    const { items, categories, onSettingChange, onCategoryClick, settings, searchQuery } = props;
    
    // Group items by category
    const itemsByCategory = {};
    items.forEach(item => {
        if (!itemsByCategory[item.category]) {
            itemsByCategory[item.category] = [];
        }
        itemsByCategory[item.category].push(item);
    });
    
    // Get visible categories (those with items)
    const visibleCategoriesIncludingSeparator = categories.filter(category => 
        itemsByCategory[category.id]?.length > 0 || !category.title
    );

    const visibleCategories = visibleCategoriesIncludingSeparator.filter(category => 
        !!category.title
    );

    
    return React.createElement('div', { className: 'advset-react-app' },
        // Notifications container
        React.createElement('div', { className: 'advset-notifications' }),
        
        // Category sidebar
        visibleCategories.length > 0 && React.createElement('div', { className: 'advset-category-sidebar' },
            React.createElement('ul', { className: 'advset-category-menu' },
                visibleCategoriesIncludingSeparator.map(category => 
                    React.createElement('li', { 
                        key: category.id,
                        className: 'advset-category-menu-item'
                    },
                    category.title ?
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
                        ) : React.createElement('div', {
                            className: 'advset-category-separator',
                            style: {
                                borderTop: '1px solid #ccc',
                            }
                        })
                    )
                )
            )
        ),
        
        // Results area with categorized items
        React.createElement('div', { className: 'advset-results-container' },
            visibleCategories.map(category => 
                category.title && React.createElement('div', { 
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
                                settingValue: settings[item.id] || {}
                            })
                        )
                    )
                )
            ),

            searchQuery !== '' && React.createElement('div', { className: 'advset-category-section advset-feature-request' },
                React.createElement('h2', {
                    className: 'advset-category-title',
                    style: {
                        justifyContent: 'center'
                    }
                },
                    React.createElement('span', {
                        className: 'advset-category-text',
                    }, 'Feature request')
                ),
                React.createElement('div', {
                    className: 'advset-feature-request-content'
                },
                    React.createElement('h3', {}, 'Do you have a feature in mind?'),
                    React.createElement('p', {}, 'Feature requests are very welcome!'),
                    React.createElement('a', {
                        href: 'mailto:ehtmlu' + '@gmail.com?subject=Feature request for Advanced Settings&body=' + encodeURIComponent('Hello,\n\nI would like to request a feature for Advanced Settings. I searched for "' + searchQuery + '" but did not find what I was looking for.\n\nThe feature I have in mind would ...\n\n\n\n'),
                    }, 'Contact us')
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

    // Create badges for deprecated and experimental features
    const badges = [];
    if (item.deprecated) {
        badges.push(
            React.createElement('span', {
                key: 'deprecated',
                className: 'advset-badge advset-badge-deprecated',
                title: 'This feature is deprecated and may be removed in a future version'
            }, React.createElement('span', {}, 'Deprecated'))
        );
    }
    if (item.experimental) {
        badges.push(
            React.createElement('span', {
                key: 'experimental',
                className: 'advset-badge advset-badge-experimental',
                title: 'This is an experimental feature and may change in future versions'
            }, React.createElement('span', {}, 'Experimental'))
        );
    }

    // Create item classes
    const itemClasses = ['advset-item'];
    if (item.deprecated) itemClasses.push('advset-item-deprecated');
    if (item.experimental) itemClasses.push('advset-item-experimental');

    return React.createElement('div', { 
        className: itemClasses.join(' '),
        'data-id': item.id
    },
        React.createElement('div', { className: 'advset-item-header' },
            badges.length > 0 && React.createElement('div', { 
                className: 'advset-item-badges' 
            }, badges)
        ),
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
     * Filter items based on settings and search query
     * 
     * @param {Array} items - The items to filter
     * @param {string} searchQuery - Optional search query
     * @returns {Array} - Filtered items
     */
    filterItems(items, searchQuery = '') {
        const showDeprecated = this.state.settings['advset.features.show_deprecated']?.enable;
        const showExperimental = this.state.settings['advset.features.show_experimental']?.enable;
        
        return items.filter(item => {
            // Filter by feature flags
            if (item.deprecated && !showDeprecated && typeof this.state.settings[item.id] === 'undefined') {
                return false;
            }

            if (item.experimental && !showExperimental && typeof this.state.settings[item.id] === 'undefined') {
                return false;
            }

            // If no search query, include the item
            if (!searchQuery) {
                return true;
            }

            // Search in ui_config fields
            const searchTexts = [];
            
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
            
            const searchText = searchTexts.join(' ').toLowerCase();
            return searchText.includes(searchQuery.toLowerCase());
        });
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
                categories: data.categories,
                settings: data.settings || {}, // Add settings from API response
            });

            // Apply initial filtering
            this.setState({
                items: this.filterItems(data.features),
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
        this.setState({ 
            items: this.filterItems(this.state.allItems, query)
        });
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
        
        // Render the React app
        if (window.React && window.ReactDOM) {
            const appElement = React.createElement(App, {
                items: items,
                categories: categories,
                onSettingChange: this.handleSettingChange.bind(this),
                onCategoryClick: this.scrollToCategory.bind(this),
                settings: this.state.settings,
                searchQuery: searchQuery,
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

        // Reapply filtering with current search query
        this.performLocalSearch(this.state.searchQuery);
        
        // Save the setting to the server
        this.saveSetting(settingId, value);
    },
    
    /**
     * Show an error message to the user
     * 
     * @param {string} message - The error message to display
     */
    showError(message) {
        this.showNotification(message, 'error');
    },

    /**
     * Show a success message to the user
     * 
     * @param {string} message - The success message to display
     */
    showSuccess(message) {
        this.showNotification(message, 'success');
    },

    /**
     * Show a notification message
     * 
     * @param {string} message - The message to display
     * @param {string} type - The type of message ('error' or 'success')
     */
    showNotification(message, type) {
        const container = this.container.querySelector('.advset-notifications');
        if (!container) return;

        const element = document.createElement('div');
        element.className = `advset-message advset-${type}-message`;
        element.textContent = message;
        
        container.appendChild(element);
        
        // Trigger reflow to ensure animation works
        element.offsetHeight;
        
        // Show the notification
        element.classList.add('is-visible');
        
        // Remove after delay
        setTimeout(() => {
            element.classList.remove('is-visible');
            
            // Wait for animation to finish before removing
            setTimeout(() => {
                if (element.parentNode) {
                    element.parentNode.removeChild(element);
                }
            }, 300);
        }, 5000);
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
                return;
            }

            // Update all settings from API response
            if (data.settings) {
                this.setState({
                    settings: data.settings
                });
            }

            this.performLocalSearch(this.state.searchQuery);
            this.showSuccess('Setting saved successfully');
        } catch (error) {
            console.error('Error saving setting:', error);
            this.showError('Failed to save setting. Please try again later.');
        }
    },
};

// Export for use in other files
export { AdvSetModalApp, App, ItemCard }; 