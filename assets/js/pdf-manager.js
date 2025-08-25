/**
 * WordPress PDF Manager Frontend JavaScript - FIXED VERSION
 * File: assets/js/pdf-manager.js
 * 
 * FIXES APPLIED:
 * 1. Replaced wpApiSettings with pdfManager (localized in main plugin)
 * 2. Fixed taxonomy loading to use custom endpoint
 * 3. Removed showMoreFilters function (not needed)
 * 4. Added better error handling
 */

class WordPressPDFManager {
    constructor() {
        this.currentFilters = {
            category: [],
            type: [],
            department: []
        };
        this.searchQuery = '';
        this.currentSort = 'date';
        this.currentOrder = 'DESC';
        this.pdfs = [];
        
        this.init();
    }
    
    async init() {
        await this.loadTaxonomies();
        await this.loadPDFs();
        this.bindEvents();
        this.renderFilterOptions();
    }
    
    /**
     * Load taxonomy terms for filter dropdowns
     * FIX #1: Changed from wpApiSettings to pdfManager
     */
    async loadTaxonomies() {
        try {
            // OLD (BROKEN): const response = await fetch(`${wpApiSettings.root}wp/v2/${taxonomy.replace('pdf_', '')}`);
            // NEW (FIXED): Use our custom taxonomies endpoint
            const response = await fetch(`${pdfManager.apiUrl}taxonomies`, {
                headers: {
                    'X-WP-Nonce': pdfManager.nonce
                }
            });
            
            if (!response.ok) throw new Error(`Failed to fetch taxonomies: ${response.status}`);
            
            this.taxonomies = await response.json();
            
        } catch (error) {
            console.error('Error loading taxonomies:', error);
            // FIX #4: Added fallback to prevent crashes
            this.taxonomies = {
                category: [],
                type: [],
                department: []
            };
        }
    }
    
    /**
     * Load PDFs from WordPress REST API
     * FIX #2: Use proper pdfManager localization
     */
    async loadPDFs() {
        try {
            const params = new URLSearchParams({
                search: this.searchQuery,
                orderby: this.currentSort,
                order: this.currentOrder,
                ...this.buildFilterParams()
            });
            
            // FIXED: Use pdfManager.apiUrl instead of undefined wpApiSettings
            const response = await fetch(`${pdfManager.apiUrl}resources?${params}`, {
                headers: {
                    'X-WP-Nonce': pdfManager.nonce
                }
            });
            
            if (!response.ok) throw new Error('Failed to load PDFs');
            
            this.pdfs = await response.json();
            this.renderPDFs();
            this.updateResultsCount();
            
        } catch (error) {
            console.error('Error loading PDFs:', error);
            this.showError('Failed to load PDF resources');
        }
    }
    
    /**
     * Build filter parameters for API request
     */
    buildFilterParams() {
        const params = {};
        
        if (this.currentFilters.category.length > 0) {
            params['category[]'] = this.currentFilters.category;
        }
        if (this.currentFilters.type.length > 0) {
            params['type[]'] = this.currentFilters.type;
        }
        if (this.currentFilters.department.length > 0) {
            params['department[]'] = this.currentFilters.department;
        }
        
        return params;
    }
    
    /**
     * Bind event listeners
     */
    bindEvents() {
        // Search input
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.searchQuery = e.target.value;
                    this.loadPDFs();
                }, 300);
            });
        }
        
        // Sort dropdown
        const sortDropdown = document.querySelector('.sort-dropdown');
        if (sortDropdown) {
            sortDropdown.addEventListener('change', (e) => {
                const [orderby, order] = e.target.value.split('-');
                this.currentSort = orderby;
                this.currentOrder = order.toUpperCase();
                this.loadPDFs();
            });
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.filter-dropdown')) {
                this.closeAllDropdowns();
            }
        });
    }
    
    /**
     * Render filter options from taxonomies
     */
    renderFilterOptions() {
        if (!this.taxonomies) return;
        
        Object.keys(this.taxonomies).forEach(filterType => {
            const dropdown = document.getElementById(`${filterType}Dropdown`);
            if (!dropdown) return;
            
            let optionsContainer = dropdown.querySelector('.filter-options');
            if (!optionsContainer) {
                optionsContainer = document.createElement('div');
                optionsContainer.className = 'filter-options';
                dropdown.appendChild(optionsContainer);
            }
            
            optionsContainer.innerHTML = this.taxonomies[filterType].map(term => `
                <div class="filter-option">
                    <input type="checkbox" 
                           id="${filterType}-${term.slug}" 
                           value="${term.slug}" 
                           onchange="pdfManagerInstance.updateFilters()">
                    <label for="${filterType}-${term.slug}">${term.name}</label>
                </div>
            `).join('');
        });
    }
    
    /**
     * Toggle dropdown visibility
     */
    toggleDropdown(filterType) {
        const dropdown = document.getElementById(filterType + 'Dropdown');
        const button = dropdown.previousElementSibling;
        
        // Close other dropdowns
        this.closeAllDropdowns(dropdown);
        
        dropdown.classList.toggle('show');
        button.classList.toggle('active');
    }
    
    /**
     * Close all dropdowns except the specified one
     */
    closeAllDropdowns(except = null) {
        document.querySelectorAll('.dropdown-content').forEach(content => {
            if (content !== except) {
                content.classList.remove('show');
                content.previousElementSibling.classList.remove('active');
            }
        });
    }
    
    /**
     * Update filters based on checkbox changes
     */
    updateFilters() {
        ['category', 'type', 'department'].forEach(filterType => {
            const checked = [];
            document.querySelectorAll(`#${filterType}Dropdown input[type="checkbox"]:checked`)
                .forEach(checkbox => {
                    checked.push(checkbox.value);
                });
            this.currentFilters[filterType] = checked;
            this.updateFilterLabel(filterType);
        });
        
        this.loadPDFs();
    }
    
    /**
     * Clear specific filter
     */
    clearFilter(filterType) {
        this.currentFilters[filterType] = [];
        
        document.querySelectorAll(`#${filterType}Dropdown input[type="checkbox"]`)
            .forEach(checkbox => {
                checkbox.checked = false;
            });
        
        this.updateFilterLabel(filterType);
        this.loadPDFs();
    }
    
    /**
     * Update filter button label
     */
    updateFilterLabel(filterType) {
        const label = document.getElementById(filterType + 'Label');
        if (!label) return;
        
        const count = this.currentFilters[filterType].length;
        
        if (count === 0) {
            label.textContent = filterType.charAt(0).toUpperCase() + filterType.slice(1);
        } else if (count === 1) {
            // Show the actual term name
            const termSlug = this.currentFilters[filterType][0];
            const term = this.taxonomies[filterType]?.find(t => t.slug === termSlug);
            label.textContent = term ? term.name : termSlug;
        } else {
            label.textContent = `${filterType.charAt(0).toUpperCase() + filterType.slice(1)} (${count})`;
        }
    }
    
    /**
     * Render PDF cards
     */
    renderPDFs() {
        const grid = document.getElementById('pdfGrid');
        if (!grid) return;
        
        if (this.pdfs.length === 0) {
            grid.innerHTML = '<div class="no-results">No PDF resources found matching your criteria.</div>';
            return;
        }
        
        grid.innerHTML = this.pdfs.map(pdf => this.renderPDFCard(pdf)).join('');
    }
    
    /**
     * Render individual PDF card
     */
    renderPDFCard(pdf) {
        const categories = pdf.categories || [];
        const types = pdf.types || [];
        const departments = pdf.departments || [];
        const allTags = [...categories, ...types, ...departments];
        
        return `
            <div class="pdf-card">
                <div class="pdf-icon">PDF</div>
                <div class="pdf-title">${this.escapeHtml(pdf.title)}</div>
                <div class="pdf-meta">
                    ${pdf.file_size || 'Unknown size'} • ${pdf.download_count} downloads
                </div>
                ${allTags.length > 0 ? `
                    <div class="pdf-tags">
                        ${allTags.map(tag => `<span class="pdf-tag">${this.escapeHtml(tag)}</span>`).join('')}
                    </div>
                ` : ''}
                <p style="font-size: 14px; color: #666; margin-bottom: 15px; line-height: 1.4;">
                    ${this.escapeHtml(pdf.description || '')}
                </p>
                <button class="download-btn" onclick="pdfManagerInstance.downloadPDF(${pdf.id})">
                    Download PDF
                </button>
            </div>
        `;
    }
    
    /**
     * Handle PDF download
     * FIX #2: Use pdfManager.apiUrl consistently
     */
    async downloadPDF(id) {
        try {
            const response = await fetch(`${pdfManager.apiUrl}download/${id}`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': pdfManager.nonce,
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) throw new Error('Download failed');
            
            const result = await response.json();
            
            if (result.success && result.download_url) {
                // Open the PDF in a new tab
                window.open(result.download_url, '_blank');
                
                // Update the display to reflect new download count
                this.loadPDFs();
            } else {
                throw new Error('Invalid download response');
            }
            
        } catch (error) {
            console.error('Download error:', error);
            alert('Failed to download PDF. Please try again.');
        }
    }
    
    /**
     * Add new PDF (admin function)
     * FIX #2: Use pdfManager.apiUrl consistently
     */
    async addPDF(formData) {
        try {
            const response = await fetch(`${pdfManager.apiUrl}resources`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': pdfManager.nonce,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            if (!response.ok) throw new Error('Failed to create PDF resource');
            
            const result = await response.json();
            
            if (result.success) {
                alert('PDF resource added successfully!');
                this.loadPDFs(); // Refresh the list
                return true;
            } else {
                throw new Error(result.message || 'Unknown error');
            }
            
        } catch (error) {
            console.error('Error adding PDF:', error);
            alert('Failed to add PDF resource: ' + error.message);
            return false;
        }
    }
    
    /**
     * Update results count display
     */
    updateResultsCount() {
        const countElement = document.getElementById('resultsCount');
        if (countElement) {
            const count = this.pdfs.length;
            countElement.textContent = `${count} resource${count !== 1 ? 's' : ''} found`;
        }
    }
    
    /**
     * Show error message
     */
    showError(message) {
        const grid = document.getElementById('pdfGrid');
        if (grid) {
            grid.innerHTML = `<div class="no-results error">Error: ${this.escapeHtml(message)}</div>`;
        }
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Toggle admin panel
     */
    toggleAdmin() {
        const panel = document.getElementById('adminPanel');
        if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    }
    
    /**
     * Handle admin form submission
     */
    async handleAdminForm(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const pdfData = {
            title: formData.get('title'),
            description: formData.get('description'),
            category: formData.getAll('category'),
            type: formData.getAll('type'),
            department: formData.getAll('department'),
            pdf_file_id: formData.get('pdf_file_id')
        };
        
        const success = await this.addPDF(pdfData);
        
        if (success) {
            event.target.reset();
        }
    }
}

// Global functions for onclick handlers (maintain backward compatibility)
let pdfManagerInstance;

function toggleDropdown(filterType) {
    if (pdfManagerInstance) {
        pdfManagerInstance.toggleDropdown(filterType);
    }
}

function clearFilter(filterType) {
    if (pdfManagerInstance) {
        pdfManagerInstance.clearFilter(filterType);
    }
}

function updateFilters() {
    if (pdfManagerInstance) {
        pdfManagerInstance.updateFilters();
    }
}

function toggleAdmin() {
    if (pdfManagerInstance) {
        pdfManagerInstance.toggleAdmin();
    }
}

function addPDF(event) {
    if (pdfManagerInstance) {
        pdfManagerInstance.handleAdminForm(event);
    }
}

// FIX #3: Removed showMoreFilters function completely (was causing error)

// FIX #4: Added check for pdfManager before initializing
document.addEventListener('DOMContentLoaded', function() {
    // Check if we have the necessary WordPress variables
    if (typeof pdfManager !== 'undefined') {
        pdfManagerInstance = new WordPressPDFManager();
    } else {
        console.error('PDF Manager: WordPress localization data not found. Make sure the plugin is properly activated.');
    }
});

/**
 * WordPress Media Library Integration for Admin Panel
 */
function initMediaUploader() {
    if (typeof wp !== 'undefined' && wp.media) {
        let mediaUploader;
        
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('upload-pdf-btn')) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: 'Choose PDF File',
                    button: {
                        text: 'Use this PDF'
                    },
                    library: {
                        type: 'application/pdf'
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    const attachment = mediaUploader.state().get('selection').first().toJSON();
                    const fileInput = document.getElementById('pdfFileId');
                    const preview = document.getElementById('pdfPreview');
                    
                    if (fileInput) fileInput.value = attachment.id;
                    if (preview) preview.innerHTML = `<p><strong>Selected:</strong> ${attachment.filename}</p>`;
                });
                
                mediaUploader.open();
            }
        });
    }
}

// Initialize media uploader when ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMediaUploader);
} else {
    initMediaUploader();
}