<section class="upload-manager-wrapper">
  <link rel="stylesheet" href="%DOCUMENT_PATH%/css/kas.upload-manager-1.0.css">
  <div class="well">
    <form id="kas-upload-form" class="form" method="post" action="" enctype="multipart/form-data">
      <a class="file-input-wrapper btn btn-default "><span>Выбрать файл(ы)</span>
      <input id="file" type="file" value="" name="file" multiple>
      </a><span class="label label-primary" data-count="">
      <os-p>0</os-p>
      </span>
    </form>
  </div>
  <div class="upload-manager-err"></div>
  <div class="upload-manager-list">
    <table class="table" style="display:none;">
      <tbody>
        <tr style="display:none;">
          <td data-num=""></td>
          <td data-file=""><div></div>
            <section data-progress="">
              <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100" style=""></div>
              </div>
              <button type="button" class="btn btn-xs btn-danger" data-cansel="" onclick="UMcansel(event)">
              <os-p>Отмена</os-p>
              </button>
            </section></td>
          <td data-wait="" style="opacity: 0"></td>
          <td data-fsize=""></td>
        </tr>
      </tbody>
    </table>
    <img src="%DOCUMENT_PATH%/img/ajx/7.gif" style="display: none" width="120" height="128" data-l-img=""></div>
  <div class="alert alert-success" role="alert" data-download-complete="" style="display: none"><span class="sr-only-ok" aria-hidden="true">OK:</span>
    <os-p data-msg=""></os-p>
  </div>
  <div class="alert alert-danger" style="display: none" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span><span data-error=""></span><span class="glyphicon glyphicon-remove" data-err-close="" onclick="UMerrClose(event)"></span></div>
  <a href="#" class="upload-manager-close">закрыть</a>
  <script type="text/javascript" src="%DOCUMENT_PATH%/js/app/1/jquery.kas.upload-manager-1.0.min.js"></script>
  <script>$('body').fileUploadManager(/**PhpIncludePath*/);</script>
</section>
