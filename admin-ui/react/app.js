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
    const { items, categories, onSettingChange, onCategoryClick, onTagClick, onTabChange, settings, searchQuery, parsedSearchQuery, activeCategory, hiddenItems, activeTab } = props;
    
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

    // Extract all unique tags from all items (both visible and hidden)
    const allTags = new Set();
    const visibleTagCounts = new Map();
    const hiddenTagCounts = new Map();
    
    // Count tags from visible items
    items.forEach(item => {
        if (item.ui_config?.tags) {
            item.ui_config.tags.forEach(tag => {
                allTags.add(tag);
                visibleTagCounts.set(tag, (visibleTagCounts.get(tag) || 0) + 1);
            });
        }
    });
    
    // Count tags from hidden items
    hiddenItems.forEach(item => {
        if (item.ui_config?.tags) {
            item.ui_config.tags.forEach(tag => {
                allTags.add(tag);
                hiddenTagCounts.set(tag, (hiddenTagCounts.get(tag) || 0) + 1);
            });
        }
    });
    
    const sortedTags = Array.from(allTags).sort();

    
    return React.createElement('div', { className: 'advset-react-app' },
        // Notifications container
        React.createElement('div', { className: 'advset-notifications' }),
        
        // Sidebar with tab navigation
        React.createElement('div', { className: 'advset-category-sidebar' },
            // Tab navigation
            React.createElement('div', { className: 'advset-tab-navigation' },
                React.createElement('button', {
                    className: `advset-tab-button ${activeTab === 'categories' ? 'is-active' : ''}`,
                    onClick: () => onTabChange('categories'),
                    disabled: visibleCategories.length === 0
                }, 'Categories'),
                React.createElement('button', {
                    className: `advset-tab-button ${activeTab === 'tags' ? 'is-active' : ''}`,
                    onClick: () => onTabChange('tags'),
                    disabled: sortedTags.length === 0
                }, 'Tags')
            ),
            
            // Categories tab content
            activeTab === 'categories' && visibleCategories.length > 0 && React.createElement('div', { className: 'advset-tab-content' },
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
                                },
                                className: activeCategory === category.id ? 'is-active' : ''
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
            
            // Tags tab content
            activeTab === 'tags' && sortedTags.length > 0 && React.createElement('div', { className: 'advset-tab-content' },
                React.createElement('div', { className: 'advset-tags-list' },
                    sortedTags.map(tag => {
                        const isActive = parsedSearchQuery?.included?.tags?.includes(tag.toLowerCase());
                        const hasVisibleItems = visibleTagCounts.has(tag);
                        const hasHiddenItems = hiddenTagCounts.has(tag);
                        
                        let className = 'advset-tag';
                        if (isActive) className += ' is-active';
                        if (!hasVisibleItems && hasHiddenItems) className += ' is-disabled';
                        
                        return React.createElement('button', {
                            key: tag,
                            className: className,
                            onClick: () => onTagClick(tag),
                            disabled: !hasVisibleItems && hasHiddenItems
                        }, tag);
                    })
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
                                onTagClick: onTagClick,
                                settingValue: settings[item.id] || {},
                                parsedSearchQuery: parsedSearchQuery
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
    const { item, onSettingChange, onTagClick, settingValue, parsedSearchQuery } = props;
    
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
        ),
        // Show tags if item has tags
        item.ui_config?.tags && item.ui_config.tags.length > 0 && React.createElement('div', { 
            className: 'advset-item-tags' 
        },
            item.ui_config.tags.map(tag => 
                React.createElement('button', {
                    key: tag,
                    className: `advset-item-tag ${parsedSearchQuery?.included?.tags?.includes(tag.toLowerCase()) ? 'is-active' : ''}`,
                    onClick: () => onTagClick(tag)
                }, tag)
            )
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
        hiddenItems: [], // Items that are hidden by current filter
        isLoading: false,
        categories: [],
        settings: {}, // Store for settings values
        activeCategory: null, // Track active category for scrolling
        parsedSearchQuery: null, // Cache for parsed search query
        activeTab: 'categories' // Track active tab: 'categories' or 'tags'
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
     * Setup scroll listener for category sections
     */
    setupScrollListener() {
        // Remove existing listener if any
        if (this.scrollContainer && this.scrollHandler) {
            this.scrollContainer.removeEventListener('scroll', this.scrollHandler);
        }

        const container = this.container.querySelector('.advset-results-container');
        if (!container) return;

        this.scrollContainer = container;
        this.checkVisibleCategory = () => {
            const categories = container.querySelectorAll('.advset-category-section');
            let activeCategory = null;
            let smallestTop = -Infinity;

            categories.forEach(category => {
                const rect = category.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                const top = rect.top - containerRect.top;
                const bottom = rect.bottom - containerRect.top;
                const offset = 50;

                // Wenn die Kategorie im oberen Bereich des Containers ist
                if (top <= offset && top > smallestTop && bottom > offset) {
                    smallestTop = top;
                    activeCategory = category.id.replace('category-', '');
                }
            });

            if (activeCategory !== this.state.activeCategory) {
                this.setState({ activeCategory });
            }
        };

        // Initial check
        this.checkVisibleCategory();

        // Create a bound scroll handler
        this.scrollHandler = this.checkVisibleCategory.bind(this);
        
        // Add the scroll listener
        container.addEventListener('scroll', this.scrollHandler);
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
     * Perform local search on items
     * 
     * @param {string} query - The search query
     */
    performLocalSearch(query) {
        const parsedSearchQuery = query ? parseSearchQuery(query) : null;
        const { filteredItems, hiddenItems } = this.filterItems(this.state.allItems, parsedSearchQuery);
        
        this.setState({ 
            searchQuery: query,
            parsedSearchQuery,
            items: filteredItems,
            hiddenItems: hiddenItems
        });
    },

    /**
     * Filter items based on settings and search query
     * 
     * @param {Array} items - The items to filter
     * @param {Object} parsedSearchQuery - The parsed search query
     * @returns {Object} - Object containing filtered and hidden items
     */
    filterItems(items, parsedSearchQuery) {
        const showDeprecated = this.state.settings['advset.features.show_deprecated']?.enable;
        const showExperimental = this.state.settings['advset.features.show_experimental']?.enable;
        
        const filteredItems = [];
        const hiddenItems = [];
        
        items.forEach(item => {
            // Check feature flags first
            let isHiddenByFlags = false;
            if (item.deprecated && !showDeprecated && typeof this.state.settings[item.id] === 'undefined') {
                isHiddenByFlags = true;
            }
            if (item.experimental && !showExperimental && typeof this.state.settings[item.id] === 'undefined') {
                isHiddenByFlags = true;
            }

            // If no search query, include the item based on flags only
            if (!parsedSearchQuery) {
                if (isHiddenByFlags) {
                    hiddenItems.push(item);
                } else {
                    filteredItems.push(item);
                }
                return;
            }

            // Search in ui_config fields and tags
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
            
            // Add tags to searchable text
            if (item.ui_config?.tags) {
                searchTexts.push(...item.ui_config.tags);
            }
            
            const searchText = searchTexts.join(Math.random()).toLowerCase();

            // Check if item matches all required terms
            const matchesIncludedTerms = parsedSearchQuery.included.terms.length === 0 || parsedSearchQuery.included.terms.every(term => 
                searchText.includes(term)
            );

            // Check if item doesn't contain any excluded terms
            const matchesExcludedTerms = !parsedSearchQuery.excluded.terms.some(exclusion => 
                searchText.includes(exclusion)
            );

            // Check tag requirements (now using labels)
            const itemTags = (item.ui_config?.tags || []).map(tag => tag.toLowerCase());
            
            // Item must have ALL required tags
            const matchesIncludedTags = parsedSearchQuery.included.tags.length === 0 || parsedSearchQuery.included.tags.every(tag => 
                itemTags.includes(tag)
            );
            
            // Item must NOT have ANY excluded tags
            const matchesExcludedTags = !parsedSearchQuery.excluded.tags.some(tag => 
                itemTags.includes(tag)
            );

            // Item must match all terms, no exclusions, required tags, and no excluded tags
            const matchesSearch = matchesIncludedTerms && matchesExcludedTerms && matchesIncludedTags && matchesExcludedTags;

            if (isHiddenByFlags || !matchesSearch) {
                hiddenItems.push(item);
            } else {
                filteredItems.push(item);
            }
        });
        
        return { filteredItems, hiddenItems };
    },

    /**
     * Load all features from the API
     */
    async loadAllFeatures() {
        // Dispatch event to show loading
        document.dispatchEvent(new CustomEvent('advset-show-loading'));
        this.setState({ isLoading: true });
        
        try {
            const response = await fetch(wpApiSettings.root + 'advanced-settings/v1/features', {
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
            const { filteredItems, hiddenItems } = this.filterItems(data.features);
            this.setState({
                items: filteredItems,
                hiddenItems: hiddenItems
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
     * Handle tab change
     * 
     * @param {string} tab - The tab to switch to
     */
    handleTabChange(tab) {
        this.setState({ activeTab: tab });
    },

    /**
     * Handle tag click
     * 
     * @param {string} tag - The tag that was clicked
     */
    handleTagClick(tag) {
        const searchInput = document.querySelector('.advset-modal-search input');
        if (!searchInput) return;

        let currentQuery = searchInput.value.trim();
        
        // Handle tags with spaces by adding quotes
        const tagPrefix = tag.includes(' ') ? `tag:"${tag}"` : `tag:${tag}`;
        
        // Check if tag is already in query (handle both quoted and unquoted versions)
        const tagRegex = new RegExp(`\\btag:("${tag.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}"|${tag.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})\\b`, 'g');
        const hasTag = tagRegex.test(currentQuery);
        
        if (hasTag) {
            // Remove tag from query
            currentQuery = currentQuery.replace(tagRegex, '').replace(/\s+/g, ' ').trim();
        } else {
            // Add tag to query
            currentQuery = currentQuery ? `${currentQuery} ${tagPrefix}` : tagPrefix;
        }
        
        // Update search input
        searchInput.value = currentQuery;
        
        // Trigger search
        document.dispatchEvent(new CustomEvent('advset-search', {
            detail: { query: currentQuery }
        }));
    },

    /**
     * Render the application
     */
    render() {
        const { searchQuery, parsedSearchQuery, items, hiddenItems, isLoading, categories, activeTab } = this.state;
        
        // Render the React app
        if (window.React && window.ReactDOM) {
            const appElement = React.createElement(App, {
                items: items,
                categories: categories,
                onSettingChange: this.handleSettingChange.bind(this),
                onCategoryClick: this.scrollToCategory.bind(this),
                onTagClick: this.handleTagClick.bind(this),
                onTabChange: this.handleTabChange.bind(this),
                settings: this.state.settings,
                searchQuery: searchQuery,
                parsedSearchQuery: parsedSearchQuery,
                activeCategory: this.state.activeCategory,
                hiddenItems: hiddenItems,
                activeTab: activeTab
            });
            
            ReactDOM.render(appElement, this.container, () => {
                // Setup scroll listener after React has rendered
                this.setupScrollListener();
            });
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
            const response = await fetch(wpApiSettings.root + 'advanced-settings/v1/settings', {
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

/**
 * Parse search query into terms and exclusions
 * 
 * @param {string} query - The search query
 * @returns {Object} - Object containing terms and exclusions
 */
function parseSearchQuery(query) {
    const result = {
        excluded: {
            tags: [],
            terms: [],
        },
        included: {
            tags: [],
            terms: [],
        },
    };

    // Remove extra spaces and normalize
    query = query.trim().replace(/\s+/g, ' ');

    // Extract terms and phrases (with optional minus)
    query.matchAll(/(\-)?(tag:)?(?:("[^"]+")|([^"\s]+))/g).forEach(match => {
        const isExclusion = match[1] === '-';
        const isTag = match[2] === 'tag:';
        const term = (match[3] ? match[3].slice(1, -1) : match[4]).toLowerCase();

        const targetList = result[isExclusion ? 'excluded' : 'included'][isTag ? 'tags' : 'terms'];
        targetList.push(term);
    });

    return result;
}

// Export for use in other files
export { AdvSetModalApp, App, ItemCard }; 