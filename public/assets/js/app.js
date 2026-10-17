/* Lăng Kính – JavaScript giao diện (jQuery) */
$(function () {

  // Nút +/- số lượng
  $(document).on('click', '.qty [data-step]', function () {
    var $in = $(this).closest('.qty').find('input');
    var min = parseInt($in.attr('min') || '1', 10), max = parseInt($in.attr('max') || '99', 10);
    var v = (parseInt($in.val(), 10) || min) + parseInt($(this).data('step'), 10);
    $in.val(Math.min(max, Math.max(min, v))).trigger('change');
  });

  // Trong giỏ hàng: đổi số lượng -> tự gửi form cập nhật
  $('form[data-autosubmit] .qty input').on('change', function () {
    var f = $(this).closest('form');
    clearTimeout(f.data('t'));
    f.data('t', setTimeout(function () { f[0].submit(); }, 450));
  });

  // Thư viện ảnh trong trang chi tiết
  $('.gallery-thumbs button').on('click', function () {
    $('.gallery-thumbs button').removeClass('on');
    $(this).addClass('on');
    $('#galleryMain').attr('src', $(this).data('src'));
  });

  // Xác nhận trước khi thao tác nguy hiểm
  $(document).on('submit', 'form[data-confirm]', function (e) {
    if (!window.confirm($(this).data('confirm'))) { e.preventDefault(); }
  });

  // Chống bấm đúp khi gửi form
  $(document).on('submit', 'form', function () {
    var $f = $(this);
    if ($f.data('sent')) { return false; }
    $f.data('sent', true);
    setTimeout(function () { $f.data('sent', false); }, 4000);
  });

  // Tự ẩn thông báo thành công
  setTimeout(function () { $('.alert-success.auto-hide').fadeOut(400); }, 4500);

  // Xem trước ảnh upload & kiểm tra dung lượng phía trình duyệt (server vẫn kiểm tra lại)
  $('#imagesInput').on('change', function () {
    var $prev = $('#imagesPreview').empty(), tooBig = false;
    Array.prototype.forEach.call(this.files, function (f) {
      if (f.size > 2 * 1024 * 1024) { tooBig = true; return; }
      var url = URL.createObjectURL(f);
      $prev.append('<img src="' + url + '" class="thumb-sm me-2 mb-2" alt="">');
    });
    if (tooBig) { alert('Có ảnh vượt quá 2 MB, ảnh đó sẽ bị từ chối.'); }
  });

  // In hóa đơn
  $('[data-print]').on('click', function () { window.print(); });
});
