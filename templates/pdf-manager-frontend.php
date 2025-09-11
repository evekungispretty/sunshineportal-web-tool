<?php
/**
 * Frontend template for SunshinePortal PDF Manager
 * File: templates/pdf-manager-frontend.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="pdf-resource-manager">
    <!-- Progress Steps -->
    <div class="steps-container">
        <div class="steps-wrapper">
            <div class="step" id="step-1" data-step="1">
                <div class="step-number">1</div>
                <div class="step-title"><?php _e('Start', 'sunshineportal-pdf'); ?></div>
            </div>
            <div class="step" id="step-2" data-step="2">
                <div class="step-number">2</div>
                <div class="step-title"><?php _e('Filter Resources', 'sunshineportal-pdf'); ?></div>
            </div>
            <div class="step" id="step-3" data-step="3">
                <div class="step-number">3</div>
                <div class="step-title"><?php _e('Browse & Download', 'sunshineportal-pdf'); ?></div>
            </div>
            <div class="step" id="step-4" data-step="4">
                <div class="step-number">4</div>
                <div class="step-title"><?php _e('Upload Resources', 'sunshineportal-pdf'); ?></div>
            </div>
        </div>
    </div>

    <!-- Step Content Container -->
    <div class="step-content-container">
        
        <!-- Step 1: Start/Welcome -->
        <div class="step-content" id="content-step-1" style="display: block;">
            <div class="welcome-card">
                <p>Welcome to the Community Needs Assessment (CNA) Web Tool, developed by the University of Florida’s Anita Zucker Center for Excellence in Early Childhood Studies, Early Childhood Policy Research Group (ECPRG). This tool provides a standardized template to help Florida’s Early Learning Coalitions (ELCs) prepare their annual Community Needs Assessment. These pre-populated reports are designed to meet Florida’s statutory requirements while also incorporating indices and insights from program data and ECPRG's research.</p>
                <p><i>Please note: The template reports are not final. Each ELC must review, update, and complete the reports before submission.</i></p>
                <div class="welcome-features">
                    <div class="feature-item">
                        <span class="dashicons dashicons-search"></span>
                        <div>
                            <h4><?php _e('Search & Filter', 'sunshineportal-pdf'); ?></h4>
                            <p><?php _e('Easily find resources by category, type, department, or search terms.', 'sunshineportal-pdf'); ?></p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="dashicons dashicons-download"></span>
                        <div>
                            <h4><?php _e('Download & Preview', 'sunshineportal-pdf'); ?></h4>
                            <p><?php _e('Preview PDFs before downloading and track download statistics.', 'sunshineportal-pdf'); ?></p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="dashicons dashicons-upload"></span>
                        <div>
                            <h4><?php _e('Upload & Share', 'sunshineportal-pdf'); ?></h4>
                            <p><?php _e('Add new PDF resources and organize them for others to use.', 'sunshineportal-pdf'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="welcome-instructions">
                    <h4><?php _e('Getting Started:', 'sunshineportal-pdf'); ?></h4>
                    <ol>
                        <li><?php _e('Click "Get Started" to begin browsing resources', 'sunshineportal-pdf'); ?></li>
                        <li><?php _e('Use filters to narrow down the resources you need', 'sunshineportal-pdf'); ?></li>
                        <li><?php _e('Browse and download the PDFs that match your needs', 'sunshineportal-pdf'); ?></li>
                        <li><?php _e('Upload new resources to share with others', 'sunshineportal-pdf'); ?></li>
                    </ol>
                </div>

                <button class="step-button step-button-primary" onclick="goToStep(2)">
                    <?php _e('Get Started', 'sunshineportal-pdf'); ?>
                </button>
            </div>
        </div>

        <!-- Step 2: Filters -->
        <div class="step-content" id="content-step-2" style="display: none;">
            <div class="filter-card">
                <h3><?php _e('Filter PDF Resources', 'sunshineportal-pdf'); ?></h3>
                <p><?php _e('Use the filters below to find the specific resources you need. You can combine multiple filters or leave them all empty to see all available resources.', 'sunshineportal-pdf'); ?></p>

                <div class="filters-container">
                    <div class="filter-row">
                        <!-- ELC Filter -->
                        <div class="filter-group">
                            <label><?php _e('ELC', 'sunshineportal-pdf'); ?></label>
                            <div class="filter-dropdown">
                                <div class="dropdown-button" onclick="toggleDropdown('category')">
                                    <span id="categoryLabel"><?php _e('All ELCs', 'sunshineportal-pdf'); ?></span>
                                    <span class="dropdown-arrow">▼</span>
                                </div>
                                <div class="dropdown-content" id="categoryDropdown">
                                    <div class="dropdown-header">
                                        <span><?php _e('ELC', 'sunshineportal-pdf'); ?></span>
                                        <a href="#" class="clear-filter" onclick="clearFilter('category')"><?php _e('Clear', 'sunshineportal-pdf'); ?></a>
                                    </div>
                                    <div class="filter-options">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- County Filter -->
                        <div class="filter-group">
                            <label><?php _e('County', 'sunshineportal-pdf'); ?></label>
                            <div class="filter-dropdown">
                                <div class="dropdown-button" onclick="toggleDropdown('type')">
                                    <span id="typeLabel"><?php _e('All Counties', 'sunshineportal-pdf'); ?></span>
                                    <span class="dropdown-arrow">▼</span>
                                </div>
                                <div class="dropdown-content" id="typeDropdown">
                                    <div class="dropdown-header">
                                        <span><?php _e('County', 'sunshineportal-pdf'); ?></span>
                                        <a href="#" class="clear-filter" onclick="clearFilter('type')"><?php _e('Clear', 'sunshineportal-pdf'); ?></a>
                                    </div>
                                    <div class="filter-options">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Year Filter -->
                        <div class="filter-group">
                            <label><?php _e('Year', 'sunshineportal-pdf'); ?></label>
                            <div class="filter-dropdown">
                                <div class="dropdown-button" onclick="toggleDropdown('department')">
                                    <span id="departmentLabel"><?php _e('All Years', 'sunshineportal-pdf'); ?></span>
                                    <span class="dropdown-arrow">▼</span>
                                </div>
                                <div class="dropdown-content" id="departmentDropdown">
                                    <div class="dropdown-header">
                                        <span><?php _e('Year', 'sunshineportal-pdf'); ?></span>
                                        <a href="#" class="clear-filter" onclick="clearFilter('department')"><?php _e('Clear', 'sunshineportal-pdf'); ?></a>
                                    </div>
                                    <div class="filter-options">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="search-container">
                        <label><?php _e('Search', 'sunshineportal-pdf'); ?></label>
                        <input type="text" 
                               class="search-input" 
                               placeholder="<?php _e('Search PDFs by title or description...', 'sunshineportal-pdf'); ?>"
                               aria-label="<?php _e('Search PDF resources', 'sunshineportal-pdf'); ?>">
                    </div>
                </div>

                <div class="filter-actions">
                    <button class="step-button" onclick="goToStep(1)">
                        <?php _e('Back', 'sunshineportal-pdf'); ?>
                    </button>
                    <button class="step-button step-button-primary" onclick="goToStep(3)">
                        <?php _e('Browse Resources', 'sunshineportal-pdf'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 3: Browse & Download -->
        <div class="step-content" id="content-step-3" style="display: none;">
            <div class="browse-card">
                <h3><?php _e('Browse & Download PDFs', 'sunshineportal-pdf'); ?></h3>
                <p><?php _e('Review the filtered results below. Click on any PDF to preview it, or use the download button to save it to your device.', 'sunshineportal-pdf'); ?></p>

                <!-- Applied Filters Summary -->
                <div id="appliedFilters" class="applied-filters" style="display: none;">
                    <h4><?php _e('Applied Filters:', 'sunshineportal-pdf'); ?></h4>
                    <div id="filterTags"></div>
                    <button class="clear-all-filters" onclick="clearAllFilters()"><?php _e('Clear All Filters', 'sunshineportal-pdf'); ?></button>
                </div>

                <!-- Results Section -->
                <div class="results-container">
                    <div class="results-header">
                        <div class="results-count" id="resultsCount">
                            <?php _e('Loading...', 'sunshineportal-pdf'); ?>
                        </div>
                        <select class="sort-dropdown" aria-label="<?php _e('Sort results', 'sunshineportal-pdf'); ?>">
                            <option value="date-desc"><?php _e('Newest first', 'sunshineportal-pdf'); ?></option>
                            <option value="date-asc"><?php _e('Oldest first', 'sunshineportal-pdf'); ?></option>
                            <option value="title-asc"><?php _e('Title A-Z', 'sunshineportal-pdf'); ?></option>
                            <option value="title-desc"><?php _e('Title Z-A', 'sunshineportal-pdf'); ?></option>
                            <option value="downloads-desc"><?php _e('Most downloaded', 'sunshineportal-pdf'); ?></option>
                        </select>
                    </div>
                    
                    <div class="pdf-grid" id="pdfGrid">
                        <!-- PDF cards will be inserted here by JavaScript -->
                        <div class="loading">
                            <?php _e('Loading PDF resources...', 'sunshineportal-pdf'); ?>
                        </div>
                    </div>
                </div>

                <div class="browse-actions">
                    <button class="step-button" onclick="goToStep(2)">
                        <?php _e('Back to Filters', 'sunshineportal-pdf'); ?>
                    </button>
                    <button class="step-button step-button-primary" onclick="goToStep(4)">
                        <?php _e('Upload New Resource', 'sunshineportal-pdf'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 4: Upload -->
        <div class="step-content" id="content-step-4" style="display: none;">
            <div class="upload-card">
                <h3><?php _e('Upload New PDF Resource', 'sunshineportal-pdf'); ?></h3>
                <p><?php _e('Add a new PDF resource to share with others. Fill out the form below and upload your PDF file.', 'sunshineportal-pdf'); ?></p>

                <form class="upload-form" onsubmit="addPDF(event)">
                    <div class="form-group">
                        <label><?php _e('Title *', 'sunshineportal-pdf'); ?></label>
                        <input type="text" name="title" required placeholder="<?php _e('Enter a descriptive title for your PDF', 'sunshineportal-pdf'); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><?php _e('ELC *', 'sunshineportal-pdf'); ?></label>
                            <select name="category" required>
                                <option value=""><?php _e('Select ELC', 'sunshineportal-pdf'); ?></option>
                                <?php
                                $categories = get_terms(array(
                                    'taxonomy' => 'pdf_category', 
                                    'hide_empty' => false
                                ));
                                foreach ($categories as $category) {
                                    echo '<option value="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><?php _e('County *', 'sunshineportal-pdf'); ?></label>
                            <select name="type" required>
                                <option value=""><?php _e('Select County', 'sunshineportal-pdf'); ?></option>
                                <?php
                                $types = get_terms(array(
                                    'taxonomy' => 'pdf_type', 
                                    'hide_empty' => false
                                ));
                                foreach ($types as $type) {
                                    echo '<option value="' . esc_attr($type->slug) . '">' . esc_html($type->name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><?php _e('Year *', 'sunshineportal-pdf'); ?></label>
                            <select name="department" required>
                                <option value=""><?php _e('Select Year', 'sunshineportal-pdf'); ?></option>
                                <?php
                                $departments = get_terms(array(
                                    'taxonomy' => 'pdf_department', 
                                    'hide_empty' => false
                                ));
                                foreach ($departments as $department) {
                                    echo '<option value="' . esc_attr($department->slug) . '">' . esc_html($department->name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><?php _e('Description', 'sunshineportal-pdf'); ?></label>
                        <textarea name="description" rows="3" placeholder="<?php _e('Brief description of the PDF resource (optional)', 'sunshineportal-pdf'); ?>"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label><?php _e('PDF File *', 'sunshineportal-pdf'); ?></label>
                        <input type="hidden" name="pdf_file_id" id="pdfFileId">
                        
                        <div class="pdf-upload-controls">
                            <input type="file" 
                                   id="directPdfUpload" 
                                   accept=".pdf,application/pdf" 
                                   style="display: none;">
                            <button type="button" class="upload-pdf-btn" onclick="document.getElementById('directPdfUpload').click();">
                                <?php _e('Choose PDF File', 'sunshineportal-pdf'); ?>
                            </button>
                            <button type="button" class="remove-pdf-btn" id="removePdfBtn" style="display: none;">
                                <?php _e('Remove File', 'sunshineportal-pdf'); ?>
                            </button>
                        </div>
                        
                        <div id="pdfPreview"></div>
                        
                        <p class="description">
                            <?php _e('Select a PDF file from your computer (max 10MB). Only PDF files are allowed.', 'sunshineportal-pdf'); ?>
                        </p>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="step-button" onclick="goToStep(3)">
                            <?php _e('Back to Browse', 'sunshineportal-pdf'); ?>
                        </button>
                        <button type="submit" class="step-button step-button-primary add-pdf-btn">
                            <?php _e('Upload PDF Resource', 'sunshineportal-pdf'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help/Instructions Button -->
    <div class="help-button-container">
        <button class="help-button" onclick="toggleInstructions()">
            <span class="dashicons dashicons-editor-help"></span>
            <?php _e('Instructions', 'sunshineportal-pdf'); ?>
        </button>
    </div>

    <!-- Instructions Modal -->
    <div id="instructionsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php _e('How to Use the PDF Resource Manager', 'sunshineportal-pdf'); ?></h3>
                <span class="close-modal" onclick="toggleInstructions()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="instruction-step">
                    <h4><?php _e('Step 1: Select Your ELC', 'sunshineportal-pdf'); ?></h4>
                    <p><?php _e('Read the welcome information and click "Get Started" to begin browsing resources.', 'sunshineportal-pdf'); ?></p>
                </div>
                
                <div class="instruction-step">
                    <h4><?php _e('Step 2: Review the CNA Report Segments', 'sunshineportal-pdf'); ?></h4>
                    <p><?php _e('Use the dropdown filters to narrow down resources by category, type, or department. You can also search by keywords. Leave filters empty to see all resources.', 'sunshineportal-pdf'); ?></p>
                    <ul>
                        <li><strong>Segment A</strong> Demographics: population and demographic characteristics.</li>
                        <li><strong>Segment B</strong> Program Data: program-specific info (e.g., School Readiness, VPK).</li>
                        <li><strong>Segment C </strong>Indices & Insights: additional indices and analyses from ECPRG.</li>


                    </ul>
                </div>
                
                <div class="instruction-step">
                    <h4><?php _e('Step 3: Complete Additional Required Sections', 'sunshineportal-pdf'); ?></h4>
                    <ul>
                        <li><strong>Segment D</strong> Community Resources & Feedback: summarize resources and local feedback.</li>
                        <li><strong>Segment E </strong> Summary & Priorities: provide an overall summary and priorities for the next period. A fillable template for Segment E is available at the provided link.</li>
                    </ul>
                </div>
                
            
            </div>
        </div>
    </div>
</div>

<script>
// Global variables for step management
let currentStep = 1;
let appliedFilters = {
    category: [],
    type: [],
    department: [],
    search: ''
};

// Step navigation functions
function goToStep(stepNumber) {
    // Hide current step
    document.querySelectorAll('.step-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Remove active class from all steps
    document.querySelectorAll('.step').forEach(step => {
        step.classList.remove('active', 'completed');
    });
    
    // Show target step
    document.getElementById(`content-step-${stepNumber}`).style.display = 'block';
    
    // Update step indicators
    for (let i = 1; i <= 4; i++) {
        const stepElement = document.getElementById(`step-${i}`);
        if (i < stepNumber) {
            stepElement.classList.add('completed');
        } else if (i === stepNumber) {
            stepElement.classList.add('active');
        }
    }
    
    currentStep = stepNumber;
    
    // Load data when entering certain steps
    if (stepNumber === 2) {
        loadTaxonomies();
    } else if (stepNumber === 3) {
        loadPDFs();
        updateAppliedFiltersDisplay();
    }
}

// Instructions modal toggle
function toggleInstructions() {
    const modal = document.getElementById('instructionsModal');
    modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('instructionsModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Initialize the component
document.addEventListener('DOMContentLoaded', function() {
    goToStep(1);
});
</script>