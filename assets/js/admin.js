;
(function ($) {
  $(document).ready(function () {
    $(document).on('click', '#rtcl-offload-connection-check', function (e) {
      e.preventDefault();
      var $this = $(this),
        $wrapper = $this.closest('#check_connection'),
        $form = $wrapper.closest('.rtcl-setting-form-wrap'),
        $result = $('#rtcl-offload-connection-result');
      if (!$result.length) {
        $result = $('<span>', {
          id: 'rtcl-offload-connection-result',
          css: {
            marginLeft: '10px'
          }
        });
        $result.insertAfter($this);
      }
      $.ajax({
        url: rtcl.ajaxurl,
        type: "POST",
        dataType: 'json',
        data: {
          action: 'rtcl_r2_check_connection',
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        },
        beforeSend: function beforeSend() {
          $result.text('🔄 Checking connection...').css({
            color: 'inherit',
            fontWeight: '500'
          });
          $form.rtclBlock();
        },
        success: function success(res) {
          var color = res.success ? 'green' : 'red';
          var icon = res.success ? '✅' : '❌';
          var message = "".concat(icon, " ").concat(res.data);
          $result.text(message).css({
            color: color,
            marginTop: '6px'
          });
          $form.rtclUnblock();
        },
        error: function error(xhr, status, _error) {
          $result.text("\u274C Connection check failed: ".concat(_error || 'Unknown error')).css('color', 'red');
          $form.rtclUnblock();
        }
      });
    });
  });
})(jQuery);
