<?php $this->render('/cms/_include/header');?>
<?php $this->render('/cms/_include/top');?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            上传语言包
        </div>
        <div class="panel-body">
            <form enctype="multipart/form-data" method="post" class="form-signin" id="xform" action="<?php echo $this->createUrl('import') ?>">
                <label for="yespoID">
                    请选择要上传的系统：
                </label>
                <select class="form-control" name="import_system">
                    <option value="">请选择</option>
                    <?php foreach ($this->cms_config['system'] as $key => $value) { ?>
                    <option value="<?php echo $key ?>">
                        <?php echo $value ?>
                    </option>
                    <?php } ?>
                </select>
                <br/>
                <label for="yespoPassword">
                    请选择语言包文件(ZIP)：
                </label>
                <input type="file" name="lang_zip" id="lang_zip" required/>
                <br/>
                <button class="btn btn-lg btn-primary btn-block" type="submit" id="btn_ok" value="确定">
                    确定
                </button>
                <br/>
                <div class="alert alert-danger" role="alert" style="display:none;">
                    <div class="error import_system_error">
                    </div>
                    <div class="error lang_zip_error">
                    </div>
                    <div class="error common_error">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            下载语言包
        </div>
        <div class="panel-body">
            <form method="post" class="form-signin" action="<?php echo $this->createUrl('download') ?>">
                <label for="yespoID">
                    请选择要下载的系统：
                </label>
                <select class="form-control" name="download_system">
                    <?php foreach ($this->cms_config['download_system'] as $key => $value) { ?>
                    <option value="<?php echo $key ?>">
                        <?php echo $value ?>
                    </option>
                    <?php }?>
                </select>
                <br/>
                <button class="btn btn-lg btn-primary btn-block" type="submit" value="确定">确定</button>
            </form>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            一键备份
        </div>
        <div class="panel-body">
            <form id="bakform" method="post" class="form-signin" action="<?php echo $this->createUrl('backup') ?>">
                <button class="btn btn-lg btn-primary btn-block" type="submit" id="bakBtn" value="一键备份">一键备份</button>
            </form>
        </div>
    </div>
</div>
<script>
$(function(){
    submitForm({
        beforeSubmit: function(){
            $(".alert").hide();
        },
        success: function(data) {
            if (data.status == 'success') {
                alert(data.message);
                window.location.reload();
            }
            else{
                showError(data.message);
                $(".alert").show();
            }
        },
        submitBtn: "#btn_ok"
    });

    submitForm({
        form: '#bakform',
        success: function(data) {
            alert(data.message);
            window.location.reload();
        },
        submitBtn: "#bakBtn"
    });
});
</script>
<?php $this->render('/cms/_include/footer')?>
