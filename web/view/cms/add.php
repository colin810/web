<?php $this->render('/cms/_include/header');?>
<?php $this->render('/cms/_include/top');?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            新增 key code
        </div>
        <div class="panel-body">
            <form enctype="multipart/form-data" method="post" class="form-signin" id="xform" style="margin: 0; padding: 0;">
                <div class="table-responsive">
                    <table class="table" align="center">
                        <tr>
                            <td width="15%"><label>系统：</label></td>
                            <td>
                                <select class="form-control" name="edit_system" id="edit_system">
                                    <option value="">请选择系统</option>
                                    <?php foreach ($this->cms_config['system'] as $key => $value) { ?>
                                    <option value="<?php echo $key ?>">
                                        <?php echo $value ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label>key code：</label></td>
                            <td>
                                <input type="text" name="edit_key" id="edit_key" class="form-control" />
                            </td>
                        </tr>
                        <?php foreach ($this->cms_config['lang'] as $key => $value) { ?>
                        <tr>
                            <td><label><?php echo $value ?>：</label></td>
                            <td>
                                <input type="text" name="edit_content[<?php echo $key ?>]" class="form-control" />
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td></td>
                            <td>
                                <button class="btn btn-lg btn-primary btn-block" type="submit" id="btn_ok" value="确定">
                                    确定
                                </button>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <div class="alert alert-danger" role="alert" style="display:none;">
        <div class="error edit_system_error"></div>
        <div class="error edit_key_error"></div>
    </div>
</div>
<script>
$(function(){
    submitForm({
        form: '#xform',
        success: function(data) {
            if (data.status == 'success') {
                alert(data.message);
                window.location.reload();
            } else {
                showError(data.message);
                $(".alert").show();
            }
        },
        submitBtn: "#btn_ok"
    });
});
</script>
<?php $this->render('/cms/_include/footer')?>
