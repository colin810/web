<?php $this->render('/cms/_include/header');?>
<?php $this->render('/cms/_include/top');?>

<script type="text/javascript" charset="utf-8" src="/ext/ueditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="/ext/ueditor/ueditor.all.min.js"> </script>
<!--建议手动加在语言，避免在ie下有时因为加载语言失败导致编辑器加载失败-->
<!--这里加载的语言文件会覆盖你在配置项目里添加的语言类型，比如你在配置项目里配置的是英文，这里加载的中文，那最后就是中文-->
<script type="text/javascript" charset="utf-8" src="/ext/ueditor/lang/zh-cn/zh-cn.js"></script>
<form id="xform" method="post" action="<?php echo $this->createUrl('save'); ?>">
<div class="container">
    <h1><?php echo $this->model['key_code'] ?></h1>
    <script id="editor" type="text/plain" style="width:100%;height:450px;"></script>
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
        //实例化编辑器
        //建议使用工厂方法getEditor创建和引用编辑器实例，如果在某个闭包下引用该编辑器，直接调用UE.getEditor('editor')就能拿到相关的实例
        //var ue = UE.getEditor('editor');
        var ue = UE.getEditor('editor', {
            toolbars: [[
                'fullscreen', 'source', '|', 'undo', 'redo', '|',
                'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
                'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
                'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
                'directionalityltr', 'directionalityrtl', 'indent', '|',
                'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
                'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|', 'insertframe', 'insertcode', 'pagebreak', 'template', '|',
                'horizontal', 'date', 'time', 'spechars', '|',
                'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
                'preview', 'searchreplace', 'help', 'drafts'
            ]],
            textarea : 'edit_content',
            autoHeightEnabled: true,
            autoFloatEnabled: true,
            allowDivTransToP: false
        });
        ue.ready(function() {
            ue.setContent('<?php echo $this->model['content'] ?>');
        });
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
