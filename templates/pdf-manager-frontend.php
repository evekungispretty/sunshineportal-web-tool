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
    <!-- Admin Panel -->
    <button class="admin-toggle" onclick="toggleAdmin()">
        <?php _e('Toggle Admin Panel', 'sunshineportal-pdf'); ?>
    </button>
    
    <div class="admin-panel" id="adminPanel" style="display: none;">
        <h3><?php _e('Add New PDF Resource', 'sunshineportal-pdf'); ?></h3>
        <form class="admin-form" onsubmit="addPDF(event)">
            <div class="form-group">
                <label><?php _e('Title:', 'sunshineportal-pdf'); ?></label>
                <input type="text" name="title" required>
            </div>
            
            <div class="form-group">
                <label><?php _e('Category:', 'sunshineportal-pdf'); ?></label>
                <select name="category" required>
                    <option value=""><?php _e('Select Category', 'sunshineportal-pdf'); ?></option>
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
                <label><?php _e('Type:', 'sunshineportal-pdf'); ?></label>
                <select name="type" required>
                    <option value=""><?php _e('Select Type', 'sunshineportal-pdf'); ?></option>
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
                <label><?php _e('Department:', 'sunshineportal-pdf'); ?></label>
                <select name="department" required>
                    <option value=""><?php _e('Select Department', 'sunshineportal-pdf'); ?></option>
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
            
            <div class="form-group">
                <label><?php _e('Description:', 'sunshineportal-pdf'); ?></label>
                <textarea name="description" rows="3" placeholder="<?php _e('Brief description of the PDF resource...', 'sunshineportal-pdf'); ?>"></textarea>
            </div>
            
            <div class="form-group">
                <label><?php _e('PDF File:', 'sunshineportal-pdf'); ?></label>
                <input type="hidden" name="pdf_file_id" id="pdfFileId">
                
                <div class="pdf-upload-controls">
                    <?php if (is_user_logged_in()): ?>
                        <!-- Logged-in users: Use WordPress Media Library -->
                        <button type="button" class="upload-pdf-btn">
                            <?php _e('Choose from Media Library', 'sunshineportal-pdf'); ?>
                        </button>
                        <button type="button" class="remove-pdf-btn" id="removePdfBtn" style="display: none; margin-left: 10px; background: #dc3545; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer;">
                            <?php _e('Remove File', 'sunshineportal-pdf'); ?>
                        </button>
                    <?php else: ?>
                        <!-- Anonymous users: Direct file upload -->
                        <input type="file" 
                               id="directPdfUpload" 
                               accept=".pdf,application/pdf" 
                               style="display: none;">
                        <button type="button" class="upload-pdf-btn" onclick="document.getElementById('directPdfUpload').click();">
                            <?php _e('Choose PDF File', 'sunshineportal-pdf'); ?>
                        </button>
                        <button type="button" class="remove-pdf-btn" id="removePdfBtn" style="display: none; margin-left: 10px; background: #dc3545; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer;">
                            <?php _e('Remove File', 'sunshineportal-pdf'); ?>
                        </button>
                    <?php endif; ?>
                </div>
                
                <div id="pdfPreview"></div>
                
                <p class="description" style="margin-top: 10px; font-size: 13px; color: #666;">
                    <?php if (is_user_logged_in()): ?>
                        <?php _e('Choose a PDF from your media library or upload a new one.', 'sunshineportal-pdf'); ?>
                    <?php else: ?>
                        <?php _e('Select a PDF file from your computer (max 10MB). Only PDF files are allowed.', 'sunshineportal-pdf'); ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <button type="submit" class="add-pdf-btn">
                <?php _e('Add PDF Resource', 'sunshineportal-pdf'); ?>
            </button>
        </form>
    </div>

    <!-- Filters Section -->
    <div class="filters-container">
        <div class="filter-row">
            <!-- Category Filter -->
            <div class="filter-group">
                <div class="filter-dropdown">
                    <div class="dropdown-button" onclick="toggleDropdown('category')">
                        <span id="categoryLabel"><?php _e('Category', 'sunshineportal-pdf'); ?></span>
                        <span class="dropdown-arrow">▼</span>
                    </div>
                    <div class="dropdown-content" id="categoryDropdown">
                        <div class="dropdown-header">
                            <span><?php _e('Category', 'sunshineportal-pdf'); ?></span>
                            <a href="#" class="clear-filter" onclick="clearFilter('category')"><?php _e('Clear', 'sunshineportal-pdf'); ?></a>
                        </div>
                        <div class="filter-options">
                            <!-- Options will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Type Filter -->
            <div class="filter-group">
                <div class="filter-dropdown">
                    <div class="dropdown-button" onclick="toggleDropdown('type')">
                        <span id="typeLabel"><?php _e('Type', 'sunshineportal-pdf'); ?></span>
                        <span class="dropdown-arrow">▼</span>
                    </div>
                    <div class="dropdown-content" id="typeDropdown">
                        <div class="dropdown-header">
                            <span><?php _e('Type', 'sunshineportal-pdf'); ?></span>
                            <a href="#" class="clear-filter" onclick="clearFilter('type')"><?php _e('Clear', 'sunshineportal-pdf'); ?></a>
                        </div>
                        <div class="filter-options">
                            <!-- Options will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Filter -->
            <div class="filter-group">
                <div class="filter-dropdown">
                    <div class="dropdown-button" onclick="toggleDropdown('department')">
                        <span id="departmentLabel"><?php _e('Department', 'sunshineportal-pdf'); ?></span>
                        <span class="dropdown-arrow">▼</span>
                    </div>
                    <div class="dropdown-content" id="departmentDropdown">
                        <div class="dropdown-header">
                            <span><?php _e('Department', 'sunshineportal-pdf'); ?></span>
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
            <input type="text" 
                   class="search-input" 
                   placeholder="<?php _e('Search PDFs...', 'sunshineportal-pdf'); ?>"
                   aria-label="<?php _e('Search PDF resources', 'sunshineportal-pdf'); ?>">
        </div>
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
</div>

<script>
// Initialize file upload for frontend admin panel
jQuery(document).ready(function($) {
    // Only initialize if admin panel is present
    if ($('#adminPanel').length > 0) {
        var mediaUploader;
        var isLoggedIn = <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;
        
        // Handle WordPress Media Library (logged-in users)
        if (isLoggedIn) {
            $(document).on('click', '.upload-pdf-btn', function(e) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: '<?php _e('Choose PDF File', 'sunshineportal-pdf'); ?>',
                    button: {
                        text: '<?php _e('Use this PDF', 'sunshineportal-pdf'); ?>'
                    },
                    library: {
                        type: 'application/pdf'
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    
                    // Update the hidden field
                    $('#pdfFileId').val(attachment.id);
                    
                    // Update the preview with file info
                    $('#pdfPreview').html(
                        '<div style="background: #f0f8ff; padding: 10px; border-radius: 4px; margin-top: 10px; border-left: 4px solid #007cba;">' +
                        '<p style="margin: 0;"><strong><?php _e('Selected File:', 'sunshineportal-pdf'); ?></strong></p>' +
                        '<p style="margin: 5px 0 0 0;"><span class="dashicons dashicons-media-document" style="color: #dc3545;"></span> ' + 
                        '<a href="' + attachment.url + '" target="_blank" style="text-decoration: none;">' + attachment.filename + '</a></p>' +
                        '<p style="margin: 5px 0 0 0; font-size: 13px; color: #666;">ID: ' + attachment.id + 
                        (attachment.filesizeHumanReadable ? ' | Size: ' + attachment.filesizeHumanReadable : '') + '</p>' +
                        '</div>'
                    );
                    
                    // Show the remove button
                    $('#removePdfBtn').show();
                    
                    // Close the media uploader window
                    mediaUploader.close();
                    
                    // Show success message
                    showSuccessMessage('<?php _e('PDF file selected successfully!', 'sunshineportal-pdf'); ?>');
                });
                
                mediaUploader.open();
            });
        }
        
        // Handle direct file upload (anonymous users)
        $('#directPdfUpload').on('change', function(e) {
            var file = e.target.files[0];
            
            if (!file) return;
            
            // Validate file type
            if (file.type !== 'application/pdf') {
                alert('<?php _e('Please select a PDF file only.', 'sunshineportal-pdf'); ?>');
                $(this).val('');
                return;
            }
            
            // Validate file size (10MB limit)
            if (file.size > 10 * 1024 * 1024) {
                alert('<?php _e('File size must be less than 10MB.', 'sunshineportal-pdf'); ?>');
                $(this).val('');
                return;
            }
            
            // Show upload progress
            $('#pdfPreview').html(
                '<div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin-top: 10px; border-left: 4px solid #ffc107;">' +
                '<p style="margin: 0;"><strong><?php _e('Uploading...', 'sunshineportal-pdf'); ?></strong></p>' +
                '<div style="width: 100%; background: #e9ecef; border-radius: 4px; margin-top: 5px;">' +
                '<div id="uploadProgress" style="width: 0%; height: 4px; background: #007cba; border-radius: 4px; transition: width 0.3s;"></div>' +
                '</div>' +
                '</div>'
            );
            
            // Upload the file
            uploadPdfFile(file);
        });
        
        // Upload function for anonymous users
        function uploadPdfFile(file) {
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
                            $('#uploadProgress').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success) {
                        // Store the file ID
                        $('#pdfFileId').val(response.file_id);
                        
                        // Update preview
                        $('#pdfPreview').html(
                            '<div style="background: #f0f8ff; padding: 10px; border-radius: 4px; margin-top: 10px; border-left: 4px solid #007cba;">' +
                            '<p style="margin: 0;"><strong><?php _e('Uploaded File:', 'sunshineportal-pdf'); ?></strong></p>' +
                            '<p style="margin: 5px 0 0 0;"><span class="dashicons dashicons-media-document" style="color: #dc3545;"></span> ' + 
                            response.filename + '</p>' +
                            '<p style="margin: 5px 0 0 0; font-size: 13px; color: #666;">Size: ' + response.file_size + '</p>' +
                            '</div>'
                        );
                        
                        // Show the remove button
                        $('#removePdfBtn').show();
                        
                        // Show success message
                        showSuccessMessage('<?php _e('PDF file uploaded successfully!', 'sunshineportal-pdf'); ?>');
                    } else {
                        alert('Upload failed: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('<?php _e('Upload failed. Please try again.', 'sunshineportal-pdf'); ?>');
                    console.error('Upload error:', error);
                }
            });
        }
        
        // Helper function to show success messages
        function showSuccessMessage(message) {
            var successMsg = $('<div style="background: #d4edda; color: #155724; padding: 8px; border-radius: 4px; margin-top: 10px;">✓ ' + message + '</div>');
            $('#pdfPreview').append(successMsg);
            
            // Remove success message after 3 seconds
            setTimeout(function() {
                successMsg.fadeOut();
            }, 3000);
        }
        
        // Handle remove PDF file button (both logged-in and anonymous)
        $(document).on('click', '#removePdfBtn', function(e) {
            e.preventDefault();
            
            // Clear the hidden field
            $('#pdfFileId').val('');
            
            // Clear the file input if it exists
            $('#directPdfUpload').val('');
            
            // Clear the preview
            $('#pdfPreview').html('');
            
            // Hide the remove button
            $(this).hide();
            
            // Show confirmation message
            var removeMsg = $('<div style="background: #f8d7da; color: #721c24; padding: 8px; border-radius: 4px; margin-top: 10px;">✓ <?php _e('File removed successfully!', 'sunshineportal-pdf'); ?></div>');
            $('#pdfPreview').html(removeMsg);
            
            // Remove message after 3 seconds
            setTimeout(function() {
                removeMsg.fadeOut();
            }, 3000);
        });
    }
});
</script>