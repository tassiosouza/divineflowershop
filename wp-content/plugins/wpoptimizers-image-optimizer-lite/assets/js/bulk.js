jQuery(document).ready(function($){
    let total = 0, done = 0;

    // Get total unoptimized images
    $.post(wpio_ajax.ajax_url, {
        action: 'wpio_get_unoptimized',
        nonce: wpio_ajax.nonce
    }, function(res){
        if(res.success){
            total = res.data.total;
            if(total > 0){
                $('#wpio-status').text(total + " images need optimization.");
                $('#wpio-start').prop('disabled', false);
            } else {
                $('#wpio-status').text("All images already optimized.");
            }
        }
    });

    $('#wpio-start').click(function(){
        done = 0;
        $('#wpio-status').text("Optimizing images...");
        processNext();
    });

    function processNext(){
        $.post(wpio_ajax.ajax_url, {
            action: 'wpio_optimize_next',
            nonce: wpio_ajax.nonce
        }, function(res){
            if(res.success){
                done++;
                let percent = Math.round((done / total) * 100);
                $('#wpio-bar').css('width', percent + '%').text(percent + "%");
                if(done < total){
                    processNext();
                } else {
                    $('#wpio-status').text("Done! " + done + " images optimized.");
                }
            }
        });
    }
});
