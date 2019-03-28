var amfm = new function () {

  this.init = function (prefix) {
    openWindowInit(prefix);
    //multipleImageInit();
    removeImageInit();
    console.log('amfm.init');
  };

  this.open = function (prefix, callback) {
    openWindow('image', prefix, callback)
  }

  this.tinyMCE = function (field, type) {
    openWindow(type, function (path) {
      return field.value = '/amfm/' + path;
    })
  };

  function openWindow(type, url, callback) {
    console.log(url);
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
      $scope.find('.amfm-preview').css('background-image', '').removeClass('active');
      $scope.find('input').val('');
    })
  }

  function openWindowInit(prefix) {
    $('body').on('click', '.amfm-link', function () {
      var link = this;
      var type = 'file';

      if($(link).hasClass('amfm-image')){
        type = 'image';
      }
      openWindow(type, prefix, function (path) {
        if(type == 'image'){
          var $preview = $(link).closest('.amfm').find('.amfm-preview');
          console.log($preview);
          $preview.css('background-image', 'url("'+prefix+'/'+path+'")');
          $preview.addClass('active')
        }

        selectFile(link, path)
      })
    })
  }

  function selectFile(link, path) {
    $(link).closest('.amfm').find('input').val(path)
  }

};
/*
$(function () {
  amfm.init();
});*/
