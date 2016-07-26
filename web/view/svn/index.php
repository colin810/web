<?php $this->render('/svn/_include/header');?>
<?php $this->render('/svn/_include/top');?>

<div class="container">
	<form class="xform" method="post" id="xform">
		<table width="100%">
		<tr>
			<td>
				<label>输入svn信息</label>
			</td>
		</tr>
		<tr>
			<td>
			    <textarea name="content" class="form-control" rows="10"></textarea>
			</td>
		</tr>
		<tr>
			<td valign="middle">
				<div class="checkbox">
					<button class="btn btn-default pull-right btn-primary" type="submit" id="btn_submit" value="输出svn信息">输出svn信息</button>
				</div>
			</td>
		</tr>
		</table>
	</form>

	<div id="resultBox" class="alert alert-success" role="alert" style="margin-top: 10px;display: none;">

	</div>
</div>
<?php $this->render('/svn/_include/footer')?>
<script>
	var html ;
	submitForm({
        submitBtn: '#btn_submit',
        success: function(data){
            if (data.status == 'success') {
            	html = '';
            	$.each(data.message, function(index, value) {
            		html += value + "<br/>";
            	});
            	$("#resultBox").empty().html(html).show();
            } else {
                alert(data.message);
            }
        },
    });
</script>
