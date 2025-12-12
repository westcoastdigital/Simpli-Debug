jQuery(document).ready(function($) {
    
    // Copy code to clipboard
    $('.simpli-debug-copy-btn').on('click', function() {
        var $btn = $(this);
        var targetId = $btn.data('clipboard-target');
        var $target = $(targetId);
        
        // Create temporary textarea
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val($target.text()).select();
        document.execCommand('copy');
        $temp.remove();
        
        // Update button text
        var originalText = $btn.text();
        $btn.text('Copied!').addClass('copied');
        
        setTimeout(function() {
            $btn.text(originalText).removeClass('copied');
        }, 2000);
    });
    
    // Clear debug log
    $('.simpli-debug-clear-btn').on('click', function() {
        if (!confirm(simpliDebug.confirm_clear)) {
            return;
        }
        
        var $btn = $(this);
        var $container = $('.simpli-debug-log-container');
        
        $btn.prop('disabled', true).addClass('simpli-debug-loading');
        
        $.ajax({
            url: simpliDebug.ajax_url,
            type: 'POST',
            data: {
                action: 'simpli_debug_clear_log',
                nonce: simpliDebug.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Reload page to show success state
                    location.reload();
                } else {
                    alert(response.data.message || 'Failed to clear log');
                    $btn.prop('disabled', false).removeClass('simpli-debug-loading');
                }
            },
            error: function() {
                alert('An error occurred while clearing the log');
                $btn.prop('disabled', false).removeClass('simpli-debug-loading');
            }
        });
    });
    
    // Download debug log
    $('.simpli-debug-download-btn').on('click', function() {
        var downloadUrl = simpliDebug.ajax_url + 
            '?action=simpli_debug_download_log&nonce=' + 
            simpliDebug.nonce;
        
        window.location.href = downloadUrl;
    });
    
    // Refresh page
    $('.simpli-debug-refresh-btn').on('click', function() {
        location.reload();
    });
    
});