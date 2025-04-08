// React App for Advanced Settings Modal
const AdvSetModalApp = {
    state: {
        searchQuery: '',
        items: [],
        allItems: [], // Cache for all items
        isLoading: false,
        categories: []
    },

    init(container) {
        this.container = container;
        this.setupEventListeners();
        this.loadAllFeatures();
        this.render();
    },

    setupEventListeners() {
        document.addEventListener('advset-search', (event) => {
            this.setState({ searchQuery: event.detail.query });
            this.performLocalSearch(event.detail.query);
        });
    },

    setState(newState) {
        this.state = { ...this.state, ...newState };
        this.render();
    },

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
            const data = await response.json();

            this.setState({ 
                allItems: data.features,
                items: data.features, // Initially show all items
                categories: data.categories
            });
        } catch (error) {
            console.error('Failed to load features:', error);
        } finally {
            // Dispatch event to hide loading
            document.dispatchEvent(new CustomEvent('advset-hide-loading'));
            this.setState({ isLoading: false });
        }
    },

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

    renderContent(items) {
        if (!items.length) {
            return '';
        }

        return items.map(item => this.renderItem(item)).join('');
    },

    renderItem(item) {
        return `
            <div class="advset-item" data-id="${item.id}">
                <h3>${item.title}</h3>
                <p>${item.description}</p>
                ${item.label ? `<p class="advset-item-label">${item.label}</p>` : ''}
                <span class="advset-item-category">${item.category}</span>
            </div>
        `;
    }
};

// Export for use in other files
window.AdvSetModalApp = AdvSetModalApp; 