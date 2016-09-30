<?php $this->render('/cms/_include/header');?>
<?php $this->render('/cms/_include/top');?>

<link rel="stylesheet" type="text/css" href="/resource/wangEditor/css/wangEditor.min.css">
<script type="text/javascript" charset="utf-8" src="/resource/wangEditor/js/wangEditor.min.js"></script>

<form id="xform" method="post" action="<?php echo $this->createUrl('save'); ?>">
<div class="container">
    <h1><?php echo $this->model['key_code'] ?></h1>
    <div id="editor-trigger" style="height: 500px;"><?php echo $this->model['content'] ?></div>
    <div class="checkbox">
        <label>
          <input type="checkbox" name="edit_version"> 更新版本
        </label>
    </div>
    <br/>
    <h5>备注：</h5>
    <textarea class="form-control" id="remark" name="edit_remark"></textarea>
    <br/>
    <input type="hidden" name="edit_content" value="<?php echo $this->model['content'] ?>" id="edit_content" />
    <input type="hidden" name="edit_key_id" value="<?php echo $_GET['ajax_key_id'] ?>" />
    <input type="hidden" name="edit_lang" value="<?php echo $_GET['ajax_lang'] ?>" />
    <button type="submit" class="btn btn-primary" id="save">确定保存</button>
</div>
</form>
<script type="text/javascript">
    $(function(){
    	// 阻止输出log
	    // wangEditor.config.printLog = false;

	    var editor = new wangEditor('editor-trigger');

	    // 上传图片
	    editor.config.uploadImgUrl = '/web/cms/upload.html';
	    editor.config.uploadImgFileName = 'file';

	    // 只排除某几个菜单（兼容IE低版本，不支持ES5的浏览器），支持ES5的浏览器可直接用 [].map 方法
	    editor.config.menus = $.map(wangEditor.config.menus, function(item, key) {
	        if (item === 'emotion') {
	            return null;
	        }
	        if (item === 'video') {
	            return null;
	        }
	        if (item === 'location') {
	            return null;
	        }
	        return item;
	    });

	    // onchange 事件
	    editor.onchange = function () {
	        $("#edit_content").val(this.$txt.html());
	    };

	    // 取消过滤js
	    editor.config.jsFilter = false;

	    // 取消粘贴过来
	    editor.config.pasteFilter = false;

	    editor.create();

        submitForm({
            submitBtn: '#save',
            success: function(data){
                if (data.status == 'success') {
                    // window.close();
                } else {
                    alert(data.message);
                }
            },
        });
    });
</script>

<?php $this->render('/cms/_include/footer')?>
