<?php
/**
 * Admin meta box template for PDF Resource details
 * File: templates/admin-meta-box.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current file info if it exists
$current_file_url = '';
$current_filename = '';
if ($pdf_file_id) {
    $current_file_url = wp_get_attachment_url($pdf_file_id);
    $current_filename = basename(get_attached_file($pdf_file_id));
}
?>

<table class="form-table" role="presentation">
    <tbody>
        <tr>
            <th scope="row">
                <label for="pdf_file"><?php _e('PDF File:', 'sunshineportal-pdf'); ?></label>
            </th>
            <td>
                <input type="hidden" id="pdf_file_id" name="pdf_file_id" value="<?php echo esc_attr($pdf_file_id); ?>">
                
                <div class="pdf-upload-controls">
                    <input type="button" 
                           id="upload_pdf_button" 
                           class="button button-secondary" 
                           value="<?php echo $pdf_file_id ? _e('Change PDF', 'sunshineportal-pdf') : _e('Upload PDF', 'sunshineportal-pdf'); ?>">
                    
                    <?php if ($pdf_file_id): ?>
                    <input type="button" 
                           id="remove_pdf_button" 
                           class="button button-secondary" 
                           value="<?php _e('Remove PDF', 'sunshineportal-pdf'); ?>" 
                           style="margin-left: 10px;">
                    <?php endif; ?>
                </div>
                
                <div id="pdf_preview" style="margin-top: 15px;">
                    <?php if ($pdf_file_id && $current_file_url): ?>
                    <div class="pdf-file-info">
                        <p><strong><?php _e('Current file:', 'sunshineportal-pdf'); ?></strong></p>
                        <div class="pdf-file-details" style="background: #f9f9f9; padding: 10px; border-radius: 4px; margin-top: 5px;">
                            <p>
                                <span class="dashicons dashicons-media-document" style="color: #dc3545; margin-right: 5px;"></span>
                                <a href="<?php echo esc_url($current_file_url); ?>" target="_blank" style="text-decoration: none;">
                                    <?php echo esc_html($current_filename); ?>
                                </a>
                            </p>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #666;">
                                <?php _e('File ID:', 'sunshineportal-pdf'); ?> <?php echo esc_html($pdf_file_id); ?>
                                <?php if ($file_size): ?>
                                | <?php _e('Size:', 'sunshineportal-pdf'); ?> <?php echo esc_html($file_size); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <?php else: ?>
                    <p style="color: #666; font-style: italic;">
                        <?php _e('No PDF file selected. Click "Upload PDF" to choose a file.', 'sunshineportal-pdf'); ?>
                    </p>
                    <?php endif; ?>
                </div>
                
                <p class="description">
                    <?php _e('Upload a PDF file from your media library. Only PDF files are allowed.', 'sunshineportal-pdf'); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label><?php _e('Download Statistics:', 'sunshineportal-pdf'); ?></label>
            </th>
            <td>
                <div class="pdf-stats">
                    <p>
                        <strong><?php _e('Total Downloads:', 'sunshineportal-pdf'); ?></strong> 
                        <span style="color: #007cba; font-weight: bold;"><?php echo esc_html($download_count); ?></span>
                    </p>
                    
                    <?php if ($file_size): ?>
                    <p>
                        <strong><?php _e('File Size:', 'sunshineportal-pdf'); ?></strong> 
                        <?php echo esc_html($file_size); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php
                    $upload_date = get_post_meta(get_the_ID(), '_upload_date', true);
                    if ($upload_date): 
                    ?>
                    <p>
                        <strong><?php _e('Uploaded:', 'sunshineportal-pdf'); ?></strong> 
                        <?php echo esc_html(mysql2date(get_option('date_format'), $upload_date)); ?>
                    </p>
                    <?php endif; ?>
                </div>
                
                <?php if ($download_count > 0): ?>
                <p class="description">
                    <?php printf(
                        _n(
                            'This PDF has been downloaded %d time.',
                            'This PDF has been downloaded %d times.',
                            $download_count,
                            'sunshineportal-pdf'
                        ),
                        $download_count
                    ); ?>
                </p>
                <?php endif; ?>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label><?php _e('Quick Actions:', 'sunshineportal-pdf'); ?></label>
            </th>
            <td>
                <?php if ($pdf_file_id && $current_file_url): ?>
                <div class="pdf-quick-actions">
                    <a href="<?php echo esc_url($current_file_url); ?>" 
                       target="_blank" 
                       class="button button-secondary">
                        <span class="dashicons dashicons-visibility" style="margin-right: 5px;"></span>
                        <?php _e('Preview PDF', 'sunshineportal-pdf'); ?>
                    </a>
                    
                    <a href="<?php echo esc_url($current_file_url); ?>" 
                       download 
                       class="button button-secondary" 
                       style="margin-left: 10px;">
                        <span class="dashicons dashicons-download" style="margin-right: 5px;"></span>
                        <?php _e('Download PDF', 'sunshineportal-pdf'); ?>
                    </a>
                    
                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $pdf_file_id . '&action=edit')); ?>" 
                       class="button button-secondary" 
                       style="margin-left: 10px;">
                        <span class="dashicons dashicons-edit" style="margin-right: 5px;"></span>
                        <?php _e('Edit in Media Library', 'sunshineportal-pdf'); ?>
                    </a>
                </div>
                <?php else: ?>
                <p style="color: #666; font-style: italic;">
                    <?php _e('Upload a PDF file to see quick actions.', 'sunshineportal-pdf'); ?>
                </p>
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
</table>

<style>
.pdf-upload-controls {
    margin-bottom: 10px;
}

.pdf-file-info {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    background: #fff;
}

.pdf-file-details {
    background: #f9f9f9 !important;
    border-left: 4px solid #007cba;
}

.pdf-stats p {
    margin: 8px 0;
}

.pdf-quick-actions {
    margin-top: 10px;
}

.pdf-quick-actions .button {
    display: inline-flex;
    align-items: center;
    margin-bottom: 5px;
}

.pdf-quick-actions .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

#pdf_preview.empty {
    padding: 15px;
    background: #f9f9f9;
    border: 2px dashed #ddd;
    border-radius: 4px;
    text-align: center;
    color: #666;
}
</style>

<script>
jQuery(document).ready(function($) {
    var mediaUploader;
    
    $('#upload_pdf_button').click(function(e) {
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
            
            // Update hidden field
            $('#pdf_file_id').val(attachment.id);
            
            // Update preview
            var previewHtml = '<div class="pdf-file-info">' +
                '<p><strong><?php _e('Selected file:', 'sunshineportal-pdf'); ?></strong></p>' +
                '<div class="pdf-file-details" style="background: #f0f8ff !important; border-left: 4px solid #007cba; padding: 10px; border-radius: 4px; margin-top: 5px;">' +
                '<p>' +
                '<span class="dashicons dashicons-media-document" style="color: #dc3545; margin-right: 5px;"></span>' +
                '<a href="' + attachment.url + '" target="_blank" style="text-decoration: none;">' + attachment.filename + '</a>' +
                '</p>' +
                '<p style="margin: 5px 0 0 0; font-size: 13px; color: #666;">' +
                '<?php _e('File ID:', 'sunshineportal-pdf'); ?> ' + attachment.id + 
                (attachment.filesizeHumanReadable ? ' | <?php _e('Size:', 'sunshineportal-pdf'); ?> ' + attachment.filesizeHumanReadable : '') +
                '</p>' +
                '<p style="margin: 5px 0 0 0; font-size: 13px; color: #28a745;">' +
                '<strong><?php _e('✓ New file selected. Save post to update.', 'sunshineportal-pdf'); ?></strong>' +
                '</p>' +
                '</div>' +
                '</div>';
            
            $('#pdf_preview').html(previewHtml);
            
            // Update button text and show remove button
            $('#upload_pdf_button').val('<?php _e('Change PDF', 'sunshineportal-pdf'); ?>');
            
            // Add remove button if it doesn't exist
            if ($('#remove_pdf_button').length === 0) {
                $('#upload_pdf_button').after(
                    '<input type="button" id="remove_pdf_button" class="button button-secondary" value="<?php _e('Remove PDF', 'sunshineportal-pdf'); ?>" style="margin-left: 10px;">'
                );
            } else {
                $('#remove_pdf_button').show();
            }
        });
        
        mediaUploader.open();
    });
    
    // Handle remove button (for both existing and dynamically created)
    $(document).on('click', '#remove_pdf_button', function() {
        // Clear the hidden field
        $('#pdf_file_id').val('');
        
        // Clear the preview
        $('#pdf_preview').html(
            '<p style="color: #666; font-style: italic;">' +
            '<?php _e('No PDF file selected. Click "Upload PDF" to choose a file.', 'sunshineportal-pdf'); ?>' +
            '</p>'
        ).addClass('empty');
        
        // Update button text and hide remove button
        $('#upload_pdf_button').val('<?php _e('Upload PDF', 'sunshineportal-pdf'); ?>');
        $(this).hide();
    });
});
</script>