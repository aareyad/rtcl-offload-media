;(function ($) {
    $(document).ready(function () {

        $(document).on('click', '#rtcl-offload-connection-check', function (e) {
            e.preventDefault();

            let $this = $(this),
                $wrapper = $this.closest('#check_connection'),
                $form = $wrapper.closest('.rtcl-setting-form-wrap'),
                $result = $('#rtcl-offload-connection-result');

            if (!$result.length) {
                $result = $('<span>', {
                    id: 'rtcl-offload-connection-result',
                    css: {marginLeft: '10px'}
                });
                $result.insertAfter($this);
            }

            $.ajax({
                url: rtcl.ajaxurl,
                type: "POST",
                dataType: 'json',
                data: {
                    action: 'rtcl_check_offload_connection',
                    __rtcl_wpnonce: rtcl.__rtcl_wpnonce
                },
                beforeSend: function () {
                    $result.text('🔄 Checking connection...').css({
                        color: 'inherit',
                        fontWeight: '500'
                    });
                    $form.rtclBlock();
                },
                success: function (res) {
                    const color = res.success ? 'green' : 'red';
                    const icon = res.success ? '✅' : '❌';
                    const message = `${icon} ${res.data}`;
                    $result.text(message).css({
                        color: color,
                        marginTop: '6px'
                    });
                    $form.rtclUnblock();
                },
                error: function (xhr, status, error) {
                    $result.text(`❌ Connection check failed: ${error || 'Unknown error'}`).css('color', 'red');
                    $form.rtclUnblock();
                }
            });
        });

    });
}(jQuery));