var amfm = new function () {

  this.init = function () {
    openWindowInit();
    removeImageInit();
    console.log('amfm.init');
  };

  this.tinyMCE = function (field, type) {
    openWindow(type, function (path) {
      return field.value = '/amfm/' + path;
    })
  };

  function openWindow(type, callback) {
    var url = '/amfm';
    var win = window.open(url, "AMFM", "width=800,height=600");
    win.onload = function () {
      var selector = type == 'image' ? 'a[data-type=image]' : 'a[data-type]';
      $(win.document).on('click', selector, function () {
        var path = $(this).data('path');
        callback(path);
        win.close();
      })
    }
  }

  function removeImageInit() {
    $('.amfm-image-close').click(function (e) {
      e.stopImmediatePropagation();
      var $scope = $(this).closest('.amfm');
      $scope.find('.amfm-image').css('background-image', '');
      $scope.find('input').val('');
    })
  }

  function openWindowInit() {    
    $('body').on('click', '.amfm-link', function () {
      var link = this;
      var type = 'file';
      if($(link).hasClass('amfm-image')){
        type = 'image';
      }
      openWindow(type, function (path) {
        if(type == 'image'){
          $(link).css('background-image', 'url("/amfm/'+path+'")');
        }
        console.log(link);
        $(link).closest('.amfm').find('input').val(path)
      })
    })
  }
};

$(function () {
  amfm.init();
});
