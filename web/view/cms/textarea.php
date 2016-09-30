<?php $this->render('/cms/_include/header');?>
<?php $this->render('/cms/_include/top');?>


<form id="xform" method="post" action="<?php echo $this->createUrl('save'); ?>">
<div class="container">
    <h1><?php echo $this->model['key_code'] ?></h1>
    <textarea name="edit_content" style="height: 400px;" class="form-control"><?php echo $this->model['content'] ?></textarea>
    <div class="checkbox">
        <label>
          <input type="checkbox" name="edit_version"> 更新版本
        </label>
    </div>
    <br/>
    <h5>备注：</h5>
    <textarea class="form-control" id="remark" name="edit_remark"></textarea>
    <br/>
    <input type="hidden" name="edit_key_id" value="<?php echo $_GET['ajax_key_id'] ?>" />
    <input type="hidden" name="edit_lang" value="<?php echo $_GET['ajax_lang'] ?>" />
    <button type="submit" class="btn btn-primary" id="save">确定保存</button>
</div>
</form>
<script type="text/javascript">
    $(function(){
        submitForm({
            submitBtn: '#save',
            success: function(data){
                if (data.status == 'success') {
                    window.close();
                } else {
                    alert(data.message);
                }
            },
        });
    });
</script>

<?php $this->render('/cms/_include/footer')?>
