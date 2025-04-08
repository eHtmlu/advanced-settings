// React App for Advanced Settings Modal
const AdvSetModalApp = {
    state: {
        searchQuery: '',
        items: [],
    },

    init(container) {
        this.container = container;
        this.setupEventListeners();
        this.render();
    },

    setupEventListeners() {
        document.addEventListener('advset-search', (event) => {
            this.setState({ searchQuery: event.detail.query });
            this.performSearch(event.detail.query);
        });
    },

    setState(newState) {
        this.state = { ...this.state, ...newState };
        this.render();
    },

    async performSearch(query) {
        // Dispatch event to show loading
        document.dispatchEvent(new CustomEvent('advset-show-loading'));
        
        try {
            const response = await fetch(`/wp-json/advanced-settings/v1/search?query=${encodeURIComponent(query)}`, {
                headers: {
                    'X-WP-Nonce': wpApiSettings.nonce
                }
            });
            const data = await response.json();
            this.setState({ items: data });
        } catch (error) {
            console.error('Search failed:', error);
        } finally {
            // Dispatch event to hide loading
            document.dispatchEvent(new CustomEvent('advset-hide-loading'));
        }
    },

    render() {
        const { searchQuery, items } = this.state;
        
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
            </div>
        `;
    }
};

// Export for use in other files
window.AdvSetModalApp = AdvSetModalApp; 