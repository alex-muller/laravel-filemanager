var amfm = new function () {
  this.init = function () {
    openWindowInit();
    console.log('amfm.init');
  };

  this.tinyMCE = function (field, type) {
    openWindow(type, function (path) {
      return field.value = '/'+amfmPrefix + '/' + path;
    })
  };

  function openWindow(type, callback) {
    var url = '/' + amfmPrefix;
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

  function openWindowInit() {    
    $('body').on('click', '.amfm-link', function () {
      var link = this;
      var type = 'file';
      if($(link).hasClass('amfm-image')){
        type = 'image';
      }
      openWindow(type, function (path) {
        if(type == 'image'){
          $(link).css('background-image', 'url("/'+amfmPrefix+'/'+path+'")');
        }
        $(link).closest('div').find('input').val(path)

      })
    })
  }
};

$(function () {
  amfm.init();
});
