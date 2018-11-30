<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="/vendor/amfm/css/bootstrap.min.css">
  <link rel="stylesheet" href="/vendor/amfm/css/bootstrap-theme.min.css">
  <link rel="stylesheet" href="/vendor/amfm/css/fontawesome-all.min.css">
  <link rel="stylesheet" href="/vendor/amfm/css/app.css">
  <title>AM File Manager</title>
</head>
<body>
<div id="app" v-cloak>
  <div class="preloader"></div>
  <div class="section section-header">
    <div class="row">
      <div class="col-sm-5">
        <a @click="levelUp" class="btn btn-default"><i class="fa fa-level-up-alt"></i></a>
        <a @click="reload" class="btn btn-default"><i class="fa fa-sync-alt"></i></a>
        <a @click="uploadFiles" class="btn btn-primary"><i class="fa fa-cloud-upload-alt"></i></a>
        <a @click="showCreateFolder" class="btn btn-default"><i class="fa fa-folder"></i></a>
        <a @click="removeItems" class="btn btn-danger"><i class="fa fa-trash-alt"></i></a>
      </div>
      <div id="create-folder" class="create-folder-form">
        <div class="input-group">
          <input v-model="newDirectoryName" type="text" class="form-control" placeholder="Создать папку">
          <span class="input-group-btn">
            <button @click="createDirectory" class="btn btn-primary" type="button">+</button>
          </span>
        </div><!-- /input-group -->
      </div>
      <form id="files-upload" style="display: none">
        {{ csrf_field() }}
        <input type="file" name="files[]" multiple>
      </form>
      <div class="col-sm-7">
        <div class="input-group">
          <input type="text" v-model="searchPhrase" value="" placeholder="Поиск..." class="form-control">
          <span class="input-group-btn">
            <button @click="search" type="button" data-toggle="tooltip" title="" id="button-search" class="btn btn-primary" data-original-title="Поиск"><i class="fa fa-search"></i></button>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="section section-content">
    {{--Breadcrumbs--}}
    <ol class="breadcrumb">
      <li v-for="(breadcrumb, index) in breadcrumbs" :key="index" :class="{active: breadcrumb.isActive}">
        <a @click="changePath(breadcrumb.path)" v-if="!breadcrumb.isActive">@{{ breadcrumb.name }}</a>
        <span v-else>@{{ breadcrumb.name }}</span>
      </li>
    </ol>

    <div v-for="chunk in chunks" class="row">

      <div v-for="item in chunk" class="col-sm-2 col-xs-4 text-center item">
        <a v-if="item.type == 'directory'" class="thumbnail" @click="changePath(item.path)"><i class="fa fa-folder fa-5x"></i></a>
        <a v-else-if="item.type == 'image'" data-type="image" :data-path="item.path" class="thumbnail"><img :src="'{{ config('amfm.prefix') }}/' + item.path"></a>
        <a v-else class="thumbnail" data-type="file" :data-path="item.path"><i class="fa fa-file fa-5x file"></i></a>
        <label>
          <input type="checkbox" :value="item.path" v-model="checked"> @{{ item.name }}
        </label>
      </div>
    </div>
  </div>
  <div class="section section-footer">
    <paginator :pages="pagination.pages" :current-page="pagination.page" v-on:change="changePage"></paginator>
  </div>
</div>

<script type="text/x-template" id="paginator">
  <nav v-if="pages > 1" aria-label="Page navigation" class="text-center">
    <ul class="pagination">
      <li>
        <a aria-label="Previous" @click="goto(currentPage - 20)">
          <span aria-hidden="true">&laquo;&laquo;</span>
        </a>
        <a aria-label="Previous" @click="goto(currentPage - 1)">
          <span aria-hidden="true">&laquo;</span>
        </a>
      </li>
      <li v-if="currentPage > 5" class="disabled"><a>...</a></li>
      <li v-for="page in pages" v-if="(currentPage - page < 5) && (page - currentPage < 5)" :class="{active: isActivePage(page)}"><a @click="goto(page)">@{{ page }}</a></li>
      <li v-if="pages - currentPage > 4" class="disabled"><a @click="goto(page)">...</a></li>
      <li>
        <a aria-label="Next" @click="goto(currentPage * 1 + 1)">
          <span aria-hidden="true">&raquo;</span>
        </a>
        <a aria-label="Next" @click="goto(currentPage * 1 + 20)">
          <span aria-hidden="true">&raquo;&raquo;</span>
        </a>
      </li>
    </ul>
  </nav>
</script>
<script>
  window.addEventListener('load', function () {
    /*Component Paginator*/
    Vue.component('paginator', {
      props   : ['pages', 'currentPage'],
      template: $('#paginator').html(),
      methods : {
        goto        : function (page) {
          if (page < 1) {
            page = 1
          } else if (page > this.pages) {
            page = this.pages
          }
          this.$emit('change', {page: page})
        },
        isActivePage: function (page) {
          return this.currentPage == page
        }
      }
    })
    /*Vue*/
    var vueData = {
      items           : {},
      pagination      : {},
      path            : window.localStorage.getItem('path') ? window.localStorage.getItem('path') : '{{ config('amfm.path') }}',
      newDirectoryName: '',
      checked         : [],
      searchPhrase    : ''
    }
    new Vue({
      el      : '#app',
      data    : vueData,
      mounted : function () {
        this.getItems(this.path)
        this.uploadFilesInit()
      },
      watch   : {
        path: function (path) {
          window.localStorage.setItem('path', path)
        }
      },
      methods : {
        getItems        : function (path, page, search) {
          var vue = this
          var params = {}
          var items
          if (path !== undefined) {
            params.path = path
            this.path = path
          }
          if (search !== undefined) {
            params.search = search
          }
          if (page !== undefined) {
            params.page = page
          }
          $.ajax({
            url       : '{{ route('amfm.get-items') }}',
            data      : params,
            beforeSend: function () {
              vue.preloader(true)
            },
            success   : function (res) {
              vue.preloader(false)
              vue.items = res.items
              vue.pagination = res.pagination
            }
          })
        },
        preloader       : function (status) {
          if (status) {
            $('.preloader').addClass('active')
          } else {
            $('.preloader').removeClass('active')
          }
        },
        changePage      : function (payload) {
          this.getItems(this.path, payload.page, this.searchPhrase)
        },
        changePath      : function (path) {
          this.path = path
          this.getItems(path)
        },
        levelUp         : function () {
          var paths = this.path.split('/')

          if (paths.length > 0) {
            paths.splice(-1, 1)
            url = paths.join('/')
            this.getItems(url, 1)
          }
        },
        showCreateFolder: function () {
          $('#create-folder').fadeToggle()
        },
        createDirectory : function () {
          var vue = this
          var token = '{{ csrf_token() }}'
          $.ajax({
            url       : '{{ route('amfm.create-directory') }}',
            method    : 'post',
            data      : {path: this.path, name: this.newDirectoryName, _token: token},
            beforeSend: function () {
              vue.preloader(true)
            },
            success   : function (res) {
              vue.preloader(false)
              if (res.status == 'success') {
                alert(res.message)
                vue.showCreateFolder()
                vue.newDirectoryName = ''
                vue.reload()
              } else {
                alert('Error')
              }
            }
          })
        },
        reload          : function () {
          this.getItems(this.path)
        },
        uploadFilesInit : function () {
          var vue = this
          var $form = $('#files-upload')
          var $filesInput = $('#files-upload input[type=file]')
          $filesInput.on('change', function () {
            var formData = new FormData($form.get(0))
            formData.append('path', vue.path)
            $.ajax({
              url        : '{{ route('amfm.upload-file') }}',
              method     : 'post',
              contentType: false,
              processData: false,
              data       : formData,
              dataType   : 'json',
              beforeSend : function () {
                vue.preloader(true)
              },
              success    : function (res) {
                vue.preloader(false)
                if (res.status = 'success') {
                  alert(res.message)
                  vue.reload()
                } else {
                  alert('error')
                }
              }
            })
          })
        },
        uploadFiles     : function () {
          var $filesInput = $('#files-upload input[type=file]')
          $filesInput.trigger('click')
        },
        removeItems     : function () {
          if (this.checked.length) {
            var vue = this
            var paths = this.checked
            var token = '{{ csrf_token() }}'
            var result = confirm('Remove selected items?')
            if (result) {
              $.ajax({
                url       : '{{ route('amfm.remove') }}',
                method    : 'post',
                data      : {paths: paths, _token: token},
                beforeSend: function () {
                  vue.preloader(true)
                },
                success   : function (res) {
                  vue.preloader(false)
                  if (res.status == 'success') {
                    alert(res.message)
                  } else {
                    alert('error')
                  }
                  vue.checked = []
                  vue.reload()
                }
              })
            }
          }
        },
        search          : function () {
          this.getItems(this.path, 1, this.searchPhrase)
        }
      },
      computed: {
        chunks     : function () {
          return _.chunk(this.items, 6)
        },
        breadcrumbs: function () {
          var links = []
          var path = ''
          var linkNames = this.path.split('/').filter(function (item) {
            return !!item
          })
          var i = 0
          linkNames.forEach(function (linkName) {
            if (linkName) {
              i++
              path += linkName + '/'
              links.push({name: linkName, path: path, isActive: (i == linkNames.length)})
            }
          })
          return links
        }
      }
    })
  })
</script>

<script src="/vendor/amfm/js/jquery-3.3.1.min.js"></script>
<script src="/vendor/amfm/js/bootstrap.min.js"></script>
<script src="/vendor/amfm/js/lodash.min.js"></script>
<script src="/vendor/amfm/js/vue.js"></script>
{{--<script src="/vendor/amfm/js/vue.min.js"></script>--}}
<script src="/vendor/amfm/js/app.js"></script>
</body>
</html>