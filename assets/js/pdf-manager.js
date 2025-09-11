/**
 * SunshinePortal PDF Manager - Updated with 6 Steps and Completion Page
 * File: assets/js/pdf-manager.js
 */

(function($) {
    'use strict';

    let currentStep = 1;
    let appliedFilters = {
        category: '', // Single value instead of array
        type: '',     // Single value instead of array
        department: '', // Single value instead of array
        search: ''
    };
    let allTaxonomies = {};
    let allPDFs = [];
    let filteredPDFs = [];

    // Initialize the application
    $(document).ready(function() {
        initializePDFManager();
    });

    function initializePDFManager() {
        goToStep(1);
        setupEventListeners();
        setupUploadHandlers();
        loadTaxonomies();
        initializeFilters();
    }

    // Step navigation functions - Updated for 6 steps
    window.goToStep = function(stepNumber) {
        // Validate step number - Updated to support 6 steps
        if (stepNumber < 1 || stepNumber > 6) return;

        // Hide current step
        $('.step-content').hide();
        
        // Remove active classes from all steps
        $('.step').removeClass('active completed');
        
        // Show target step
        $(`#content-step-${stepNumber}`).show();
        
        // Update step indicators - Updated to 6 steps
        for (let i = 1; i <= 6; i++) {
            const $stepElement = $(`#step-${i}`);
            if (i < stepNumber) {
                $stepElement.addClass('completed');
            } else if (i === stepNumber) {
                $stepElement.addClass('active');
            }
        }
        
        currentStep = stepNumber;
        
        // Load data when entering certain steps
        if (stepNumber === 2) {
            loadTaxonomies();
        } else if (stepNumber === 3) {
            loadPDFs();
            updateAppliedFiltersDisplay();
        } else if (stepNumber === 4 || stepNumber === 5) {
            // Pre-populate form fields with filter selections
            prePopulateUploadForm(stepNumber);
        } else if (stepNumber === 6) {
            // Handle completion step
            handleCompletionStep();
        }
    };

    // NEW: Handle completion step logic
    function handleCompletionStep() {
        // Add any completion-specific logic here
        console.log('User reached completion step');
        
        // Optional: Reset filters for next use
        // appliedFilters = {
        //     category: '',
        //     type: '',
        //     department: '',
        //     search: ''
        // };
        
        // Optional: Clear any temporary data
        // Could add analytics tracking here
        if (typeof gtag !== 'undefined') {
            gtag('event', 'completion', {
                'event_category': 'pdf_manager',
                'event_label': 'workflow_completed'
            });
        }
    }

    // NEW: Pre-populate upload form with filter selections
    function prePopulateUploadForm(stepNumber) {
        const prefix = stepNumber === 4 ? 'segmentD' : 'segmentE';
        
        // Wait a moment for the DOM to be ready
        setTimeout(function() {
            // Pre-select category (ELC)
            if (appliedFilters.category) {
                const $categorySelect = $(`#${prefix}-category`);
                if ($categorySelect.length) {
                    $categorySelect.val(appliedFilters.category);
                    console.log(`Pre-selected ${prefix} category:`, appliedFilters.category);
                }
            }
            
            // Pre-select type (County)
            if (appliedFilters.type) {
                const $typeSelect = $(`#${prefix}-type`);
                if ($typeSelect.length) {
                    $typeSelect.val(appliedFilters.type);
                    console.log(`Pre-selected ${prefix} type:`, appliedFilters.type);
                }
            }
            
            // Pre-select department (Year)
            if (appliedFilters.department) {
                const $departmentSelect = $(`#${prefix}-department`);
                if ($departmentSelect.length) {
                    $departmentSelect.val(appliedFilters.department);
                    console.log(`Pre-selected ${prefix} department:`, appliedFilters.department);
                }
            }
            
            // Show filter inheritance message
            showFilterInheritanceMessage(stepNumber);
        }, 100);
    }

    // NEW: Show message about inherited filters
    function showFilterInheritanceMessage(stepNumber) {
        const hasInheritedFilters = appliedFilters.category || appliedFilters.type || appliedFilters.department;
        
        if (hasInheritedFilters) {
            const prefix = stepNumber === 4 ? 'segmentD' : 'segmentE';
            const $form = $(`#content-step-${stepNumber} .upload-form`);
            
            // Remove any existing message
            $form.find('.filter-inheritance-message').remove();
            
            // Add inheritance message
            const inheritedItems = [];
            if (appliedFilters.category) {
                const term = findTermBySlug('category', appliedFilters.category);
                inheritedItems.push(`ELC: ${term ? term.name : appliedFilters.category}`);
            }
            if (appliedFilters.type) {
                const term = findTermBySlug('type', appliedFilters.type);
                inheritedItems.push(`County: ${term ? term.name : appliedFilters.type}`);
            }
            if (appliedFilters.department) {
                const term = findTermBySlug('department', appliedFilters.department);
                inheritedItems.push(`Year: ${term ? term.name : appliedFilters.department}`);
            }
            
            const message = `
                <div class="filter-inheritance-message" style="background: #e3f2fd; padding: 12px; margin-bottom: 20px; border-radius: 4px;">
                    <p style="margin: 0; color: #1976d2;">
                        <strong>📋 Pre-filled from your previous step:</strong> ${inheritedItems.join(', ')}
                    </p>

                </div>
            `;
            
            $form.prepend(message);
        }
    }

    // Setup all event listeners
    function setupEventListeners() {
        // Search input
        $('.search-input').on('input', debounce(function() {
            appliedFilters.search = $(this).val().trim();
            if (currentStep === 3) {
                filterPDFs();
            }
        }, 300));

        // Sort dropdown
        $('.sort-dropdown').on('change', function() {
            if (currentStep === 3) {
                sortPDFs();
            }
        });

        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.filter-dropdown').length) {
                $('.dropdown-content').removeClass('show');
                $('.dropdown-button').removeClass('active');
            }
        });

        // Modal close
        $(document).on('click', '.modal', function(e) {
            if (e.target === this) {
                hideModal();
            }
        });
    }

    // Dropdown functionality
    window.toggleDropdown = function(filterType) {
        const $button = $(`#${filterType}Dropdown`).siblings('.dropdown-button');
        const $dropdown = $(`#${filterType}Dropdown`);
        
        // Close other dropdowns
        $('.dropdown-content').not($dropdown).removeClass('show');
        $('.dropdown-button').not($button).removeClass('active');
        
        // Toggle current dropdown
        $button.toggleClass('active');
        $dropdown.toggleClass('show');
    };

    // Clear individual filter - for single selection
    window.clearFilter = function(filterType) {
        appliedFilters[filterType] = ''; // Clear single value
        updateFilterLabel(filterType);
        if (currentStep === 3) {
            filterPDFs();
        }
        
        // Update UI - remove selected class from all options
        $(`#${filterType}Dropdown .filter-option`).removeClass('selected');
        
        // Close dropdown
        $(`#${filterType}Dropdown`).removeClass('show');
        $(`#${filterType}Dropdown`).siblings('.dropdown-button').removeClass('active');
    };

    // Clear all filters - for single selection
    window.clearAllFilters = function() {
        appliedFilters = {
            category: '',
            type: '',
            department: '',
            search: ''
        };
        
        // Reset UI
        updateFilterLabel('category');
        updateFilterLabel('type');
        updateFilterLabel('department');
        $('.search-input').val('');
        $('.filter-option').removeClass('selected');
        
        if (currentStep === 3) {
            filterPDFs();
            updateAppliedFiltersDisplay();
        }
    };

    // Instructions modal toggle
    window.toggleInstructions = function() {
        const $modal = $('#instructionsModal');
        if ($modal.is(':visible')) {
            hideModal();
        } else {
            showModal();
        }
    };

    function showModal() {
        $('#instructionsModal').show();
        $('body').addClass('modal-open');
    }

    function hideModal() {
        $('#instructionsModal').hide();
        $('body').removeClass('modal-open');
    }

    // Load taxonomies from API
    function loadTaxonomies() {
        if (Object.keys(allTaxonomies).length > 0) {
            populateFilterDropdowns();
            return;
        }

        $.ajax({
            url: pdfManager.apiUrl + 'taxonomies',
            type: 'GET',
            success: function(response) {
                allTaxonomies = response;
                populateFilterDropdowns();
            },
            error: function() {
                console.error('Failed to load taxonomies');
            }
        });
    }

    // Populate filter dropdowns with single-selection behavior
    function populateFilterDropdowns() {
        Object.keys(allTaxonomies).forEach(function(taxonomy) {
            const terms = allTaxonomies[taxonomy];
            const $container = $(`#${taxonomy}Dropdown .filter-options`);
            
            $container.empty();
            
            terms.forEach(function(term) {
                const $option = $('<div>')
                    .addClass('filter-option')
                    .attr('data-value', term.slug)
                    .attr('data-filter-type', taxonomy)
                    .text(term.name)
                    .on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        selectFilterOption(taxonomy, term.slug, term.name, $(this));
                    });
                
                // Check if this option is currently selected
                if (appliedFilters[taxonomy] === term.slug) {
                    $option.addClass('selected');
                }
                
                $container.append($option);
            });
        });
    }

    // Select filter option (single selection)
    function selectFilterOption(filterType, value, label, $element) {
        // Remove selected class from all options in this filter type
        $(`#${filterType}Dropdown .filter-option`).removeClass('selected');
        
        // If clicking the same option that's already selected, clear it
        if (appliedFilters[filterType] === value) {
            appliedFilters[filterType] = '';
        } else {
            // Set new selection
            appliedFilters[filterType] = value;
            $element.addClass('selected');
        }
        
        updateFilterLabel(filterType);
        
        if (currentStep === 3) {
            filterPDFs();
        }
        
        // Close dropdown after selection
        $(`#${filterType}Dropdown`).removeClass('show');
        $(`#${filterType}Dropdown`).siblings('.dropdown-button').removeClass('active');
    }

    // Update filter label for single selection
    function updateFilterLabel(filterType) {
        const value = appliedFilters[filterType];
        const $label = $(`#${filterType}Label`);
        
        if (!value) {
            // No selection - show default text
            const defaultTexts = {
                category: 'All ELCs',
                type: 'All Counties', 
                department: 'All Years'
            };
            $label.text(defaultTexts[filterType] || `All ${capitalize(filterType)}s`);
            $label.removeClass('has-selection');
        } else {
            // Show selected item name
            const term = findTermBySlug(filterType, value);
            $label.text(term ? term.name : value);
            $label.addClass('has-selection');
        }
    }

    // Find term by slug
    function findTermBySlug(taxonomy, slug) {
        if (!allTaxonomies[taxonomy]) return null;
        return allTaxonomies[taxonomy].find(term => term.slug === slug);
    }

    // Capitalize first letter
    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Load PDFs from API with single filter values
    function loadPDFs() {
        $('#pdfGrid').html('<div class="loading">Loading PDF resources...</div>');
        $('#resultsCount').text('Loading...');

        const params = new URLSearchParams();
        
        // Add filters to params - for single values
        if (appliedFilters.search) {
            params.append('search', appliedFilters.search);
        }
        
        // Add single filter values (not arrays)
        ['category', 'type', 'department'].forEach(function(filterType) {
            if (appliedFilters[filterType]) {
                params.append(`${filterType}[]`, appliedFilters[filterType]);
            }
        });

        $.ajax({
            url: pdfManager.apiUrl + 'resources?' + params.toString(),
            type: 'GET',
            success: function(response) {
                allPDFs = response;
                filteredPDFs = [...allPDFs];
                sortPDFs();
                renderPDFs();
                updateResultsCount();
                updateAppliedFiltersDisplay();
            },
            error: function() {
                $('#pdfGrid').html('<div class="error">Failed to load PDF resources. Please try again.</div>');
                $('#resultsCount').text('Error loading results');
            }
        });
    }

    // Filter PDFs based on current filters
    function filterPDFs() {
        loadPDFs(); // Reload with current filters
    }

    // Sort PDFs
    function sortPDFs() {
        const sortBy = $('.sort-dropdown').val();
        
        filteredPDFs.sort(function(a, b) {
            switch (sortBy) {
                case 'date-asc':
                    return new Date(a.date) - new Date(b.date);
                case 'date-desc':
                    return new Date(b.date) - new Date(a.date);
                case 'title-asc':
                    return a.title.localeCompare(b.title);
                case 'title-desc':
                    return b.title.localeCompare(a.title);
                case 'downloads-desc':
                    return b.download_count - a.download_count;
                default:
                    return new Date(b.date) - new Date(a.date);
            }
        });
        
        renderPDFs();
    }

    // Render PDFs in grid
    function renderPDFs() {
        const $grid = $('#pdfGrid');
        
        if (filteredPDFs.length === 0) {
            $grid.html(`
                <div class="no-results">
                    <h4>No PDFs found</h4>
                    <p>Try adjusting your filters or search terms.</p>
                </div>
            `);
            return;
        }

        const cardsHtml = filteredPDFs.map(function(pdf) {
            const categories = pdf.categories.join(', ') || 'Uncategorized';
            const types = pdf.types.join(', ') || 'No type';
            const departments = pdf.departments.join(', ') || 'No department';
            const fileSize = pdf.file_size || 'Unknown size';
            const downloadCount = pdf.download_count || 0;

            return `
                <div class="pdf-card">
                    <h4>${escapeHtml(pdf.title)}</h4>
                    
                    <div class="pdf-meta">
                        <span class="pdf-tag">${escapeHtml(categories)}</span>
                        <span class="pdf-tag">${escapeHtml(types)}</span>
                        <span class="pdf-tag">${escapeHtml(departments)}</span>
                    </div>
                    
                    <div class="pdf-stats">
                        <span>Size: ${escapeHtml(fileSize)}</span>
                        <span>Downloads: ${downloadCount}</span>
                    </div>
                    
                    ${pdf.description ? `<p class="pdf-description">${escapeHtml(pdf.description)}</p>` : ''}
                    
                    <div class="pdf-actions">
                        <button class="pdf-action-btn" onclick="previewPDF('${pdf.url}', '${escapeHtml(pdf.title)}')">
                            Preview
                        </button>
                        <button class="pdf-action-btn primary" onclick="downloadPDF(${pdf.id}, '${pdf.download_url}')">
                            Download
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        $grid.html(cardsHtml);
    }

    // Update results count
    function updateResultsCount() {
        const count = filteredPDFs.length;
        const text = count === 1 ? '1 PDF found' : `${count} PDFs found`;
        $('#resultsCount').text(text);
    }

    // Update applied filters display for single selections
    function updateAppliedFiltersDisplay() {
        const hasFilters = appliedFilters.search || 
                          appliedFilters.category || 
                          appliedFilters.type || 
                          appliedFilters.department;

        const $container = $('#appliedFilters');
        
        if (!hasFilters) {
            $container.hide();
            return;
        }

        const $tags = $('#filterTags');
        $tags.empty();

        // Add search tag
        if (appliedFilters.search) {
            $tags.append(`
                <span class="filter-tag">
                    Search: "${escapeHtml(appliedFilters.search)}"
                    <span class="remove-tag" onclick="removeSearchFilter()">×</span>
                </span>
            `);
        }

        // Add taxonomy tags - for single values
        ['category', 'type', 'department'].forEach(function(filterType) {
            if (appliedFilters[filterType]) {
                const term = findTermBySlug(filterType, appliedFilters[filterType]);
                const label = term ? term.name : appliedFilters[filterType];
                
                $tags.append(`
                    <span class="filter-tag">
                        ${capitalize(filterType)}: ${escapeHtml(label)}
                        <span class="remove-tag" onclick="removeFilterTag('${filterType}')">×</span>
                    </span>
                `);
            }
        });

        $container.show();
    }

    // Remove search filter
    window.removeSearchFilter = function() {
        appliedFilters.search = '';
        $('.search-input').val('');
        if (currentStep === 3) {
            filterPDFs();
        }
    };

    // Remove filter tag for single selection
    window.removeFilterTag = function(filterType) {
        appliedFilters[filterType] = '';
        updateFilterLabel(filterType);
        
        // Update UI
        $(`#${filterType}Dropdown .filter-option`).removeClass('selected');
        
        if (currentStep === 3) {
            filterPDFs();
        }
    };

    // Preview PDF
    window.previewPDF = function(url, title) {
        window.open(url, '_blank');
    };

    // Download PDF
    window.downloadPDF = function(pdfId, downloadUrl) {
        // Track download
        $.ajax({
            url: downloadUrl,
            type: 'POST',
            headers: {
                'X-WP-Nonce': pdfManager.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Open download URL
                    window.open(response.download_url, '_blank');
                    
                    // Update download count in UI
                    updateDownloadCount(pdfId, response.new_count);
                }
            },
            error: function() {
                console.error('Failed to track download');
                // Still allow download
                const pdf = filteredPDFs.find(p => p.id === pdfId);
                if (pdf) {
                    window.open(pdf.url, '_blank');
                }
            }
        });
    };

    // Update download count in UI
    function updateDownloadCount(pdfId, newCount) {
        // Update in data arrays
        const pdfIndex = allPDFs.findIndex(p => p.id === pdfId);
        if (pdfIndex !== -1) {
            allPDFs[pdfIndex].download_count = newCount;
        }
        
        const filteredIndex = filteredPDFs.findIndex(p => p.id === pdfId);
        if (filteredIndex !== -1) {
            filteredPDFs[filteredIndex].download_count = newCount;
        }
        
        // Re-render if currently showing and sorted by downloads
        if (currentStep === 3 && $('.sort-dropdown').val() === 'downloads-desc') {
            sortPDFs();
        }
    }

    // UPDATED: Setup upload handlers for both segments
    function setupUploadHandlers() {
        // Handle direct file upload for Segment D
        $(document).on('change', '#segmentD-directPdfUpload', function(e) {
            handleFileUpload(e, 'segmentD');
        });
        
        // Handle direct file upload for Segment E
        $(document).on('change', '#segmentE-directPdfUpload', function(e) {
            handleFileUpload(e, 'segmentE');
        });
        
        // Handle remove PDF file buttons
        $(document).on('click', '#segmentD-removePdfBtn', function(e) {
            e.preventDefault();
            handleFileRemoval('segmentD');
        });
        
        $(document).on('click', '#segmentE-removePdfBtn', function(e) {
            e.preventDefault();
            handleFileRemoval('segmentE');
        });
    }

    // NEW: Handle file upload for specific segment
    function handleFileUpload(e, segment) {
        var file = e.target.files[0];
        
        if (!file) return;
        
        if (file.type !== 'application/pdf') {
            alert('Please select a PDF file only.');
            $(e.target).val('');
            return;
        }
        
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB.');
            $(e.target).val('');
            return;
        }
        
        showUploadProgress(file.name, segment);
        uploadPdfFile(file, segment);
    }

    // NEW: Handle file removal for specific segment
    function handleFileRemoval(segment) {
        $(`#${segment}-pdfFileId`).val('');
        $(`#${segment}-directPdfUpload`).val('');
        $(`#${segment}-pdfPreview`).html('');
        $(`#${segment}-removePdfBtn`).hide();
        
        showRemoveMessage(segment);
    }

    // UPDATED: Upload progress display for specific segment
    function showUploadProgress(filename, segment) {
        $(`#${segment}-pdfPreview`).html(`
            <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; border-left: 4px solid #ffc107;">
                <p style="margin: 0;"><strong>Uploading: ${escapeHtml(filename)}</strong></p>
                <div style="width: 100%; background: #e9ecef; border-radius: 4px; margin-top: 5px;">
                    <div id="${segment}-uploadProgress" style="width: 0%; height: 4px; background: #007cba; border-radius: 4px; transition: width 0.3s;"></div>
                </div>
            </div>
        `);
    }

    // UPDATED: Upload PDF file for specific segment
    function uploadPdfFile(file, segment) {
        var formData = new FormData();
        formData.append('pdf_file', file);
        
        $.ajax({
            url: pdfManager.apiUrl + 'upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-WP-Nonce': pdfManager.nonce
            },
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percentComplete = (e.loaded / e.total) * 100;
                        $(`#${segment}-uploadProgress`).css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    $(`#${segment}-pdfFileId`).val(response.file_id);
                    
                    $(`#${segment}-pdfPreview`).html(`
                        <div style="background: #f0f8ff; padding: 10px; border-radius: 4px; border-left: 4px solid #007cba;">
                            <p style="margin: 0;"><strong>Uploaded File:</strong></p>
                            <p style="margin: 5px 0 0 0;">
                                <span class="dashicons dashicons-media-document" style="color: #dc3545;"></span> 
                                ${escapeHtml(response.filename)}
                            </p>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #666;">Size: ${escapeHtml(response.file_size)}</p>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #28a745;">
                                <strong>✓ File uploaded successfully!</strong>
                            </p>
                        </div>
                    `);
                    
                    $(`#${segment}-removePdfBtn`).show();
                } else {
                    alert('Upload failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'Upload failed. Please try again.';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = 'Upload failed: ' + response.message;
                    }
                } catch (e) {
                    // Use default error message
                }
                alert(errorMessage);
                console.error('Upload error:', error);
            }
        });
    }

    // UPDATED: Remove message helper for specific segment
    function showRemoveMessage(segment) {
        $(`#${segment}-pdfPreview`).html(`
            <div style="background: #f8d7da; color: #721c24; padding: 8px; border-radius: 4px; margin-top: 10px;">
                ✓ File removed successfully!
            </div>
        `);
        
        setTimeout(function() {
            $(`#${segment}-pdfPreview`).fadeOut(function() {
                $(this).html('').show();
            });
        }, 3000);
    }

    // UPDATED: Add PDF function (form submission) with segment parameter - Updated to go to Step 6 after Segment E
    window.addPDF = function(event, segment) {
        event.preventDefault();
        
        var formData = new FormData(event.target);
        
        var pdfFileId = formData.get('pdf_file_id');
        if (!pdfFileId) {
            alert('Please select a PDF file before submitting.');
            return;
        }
        
        var requiredFields = ['title', 'category', 'type', 'department'];
        var missingFields = [];
        
        requiredFields.forEach(function(field) {
            var value = formData.get(field);
            if (!value || value.trim() === '') {
                missingFields.push(field);
            }
        });
        
        if (missingFields.length > 0) {
            alert('Please fill in all required fields: ' + missingFields.join(', '));
            return;
        }
        
        // Add segment identifier to title if not already present
        var title = formData.get('title').trim();
        var segmentPrefix = segment === 'segment-d' ? 'Segment D - ' : 'Segment E - ';
        if (!title.toLowerCase().includes(segment.replace('-', ' '))) {
            title = segmentPrefix + title;
        }
        
        var pdfData = {
            title: title,
            description: formData.get('description').trim(),
            category: [formData.get('category').trim()],
            type: [formData.get('type').trim()],
            department: [formData.get('department').trim()],
            pdf_file_id: pdfFileId.trim()
        };
        
        var $submitBtn = $(event.target).find('.add-pdf-btn');
        var originalText = $submitBtn.text();
        $submitBtn.text('Creating PDF Resource...').prop('disabled', true);
        
        $.ajax({
            url: pdfManager.apiUrl + 'resources',
            type: 'POST',
            data: JSON.stringify(pdfData),
            contentType: 'application/json',
            headers: {
                'X-WP-Nonce': pdfManager.nonce
            },
            success: function(response) {
                if (response.success) {
                    var segmentName = segment === 'segment-d' ? 'Segment D' : 'Segment E';
                    alert(`${segmentName} uploaded successfully!`);
                    
                    // Reset form
                    event.target.reset();
                    var prefix = segment === 'segment-d' ? 'segmentD' : 'segmentE';
                    $(`#${prefix}-pdfFileId`).val('');
                    $(`#${prefix}-pdfPreview`).html('');
                    $(`#${prefix}-removePdfBtn`).hide();
                    
                    var directUpload = document.getElementById(`${prefix}-directPdfUpload`);
                    if (directUpload) {
                        directUpload.value = '';
                    }
                    
                    // Navigate based on segment - UPDATED to go to Step 6 after Segment E
                    if (segment === 'segment-d') {
                        // Go to Segment E
                        goToStep(5);
                    } else {
                        // Go to completion step (Step 6) after Segment E
                        goToStep(6);
                    }
                } else {
                    alert('Failed to create PDF resource: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Failed to create PDF resource. Please try again.');
            },
            complete: function() {
                $submitBtn.text(originalText).prop('disabled', false);
            }
        });
    };

    // Initialize filters functionality
    function initializeFilters() {
        // Ensure single selection behavior on page load
        $(document).on('click', '.filter-option', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const filterType = $(this).attr('data-filter-type');
            const value = $(this).attr('data-value');
            const label = $(this).text();
            
            if (filterType && value) {
                selectFilterOption(filterType, value, label, $(this));
            }
        });
        
        // Prevent dropdown from closing when clicking inside
        $(document).on('click', '.dropdown-content', function(e) {
            e.stopPropagation();
        });
        
        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.filter-dropdown').length) {
                $('.dropdown-content').removeClass('show');
                $('.dropdown-button').removeClass('active');
            }
        });
    }

    // Utility functions
    function escapeHtml(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function debounce(func, wait) {
        var timeout;
        return function executedFunction() {
            var context = this;
            var args = arguments;
            var later = function() {
                timeout = null;
                func.apply(context, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

})(jQuery);