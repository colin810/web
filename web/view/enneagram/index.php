<?php $this->render('/enneagram/_include/header');?>
<style>
	input[type=radio]:focus{outline:0;}
	dl dt{line-height:25px;padding-bottom:10px;}
	.article{font-weight:normal;}
</style>

<div class="container" id="resultBox" style="display:none;">
	<div class="page-header">
	  <h1>测试结果</h1>
	</div>
	<div id="result">

	</div>
	<button id="back" type="button" class="btn btn-success"> 返 回 </button>
</div>
<div class="container" id="testBox">
	<div class="page-header">
	  <h1>九型人格测试</h1>
	</div>
	<form id="xform" method="post" class="form-inline">
		<?php foreach ($this->list as $key => $value) {?>
		<div class="panel panel-default">
			<div class="panel-heading"><?php echo ($key + 1) . '. ' . $value['title_name'] ?></div>
				<div class="panel-body">
					<div class="radio">
					  <label>
					    <input type="radio" name="enneagram[<?php echo $value['enneagram_id'] ?>][<?php echo $value['title_id'] ?>]" value="1" > 是&nbsp;&nbsp;
					  </label>
					</div>
					<div class="radio">
					  <label>
					    <input type="radio" name="enneagram[<?php echo $value['enneagram_id'] ?>][<?php echo $value['title_id'] ?>]" value="0" > 否&nbsp;&nbsp;
					  </label>
					</div>
				</div>
		</div>
		<?php }?>
		<div class="form-group">
			<input type="text" name="username" class="form-control" id="txtUsername" placeholder="姓名">
		</div>
		<div class="form-group">
			<input type="tel" name="telephone" class="form-control" id="txtTelephone" placeholder="手机号码">
		</div>
		<button type="submit" class="btn btn-success">确认提交</button>
	</form>
	<br/>
	<div class="alert alert-danger" role="alert" style="display:none;" id="error">

	</div>
	<br/>
</div>
<?php $this->render('/enneagram/_include/footer')?>
<script>
$(function(){
	var beforeSubmit = function() {
		var tmpTag;
		var tmpResult;
		var tmpName;
		var result = true;
		var length = $("input[type='radio']").length;
		var username = $.trim($("#txtUsername").val());
		var telephone = $.trim($("#txtTelephone").val());
		$(".panel-danger").removeClass('panel-danger');
		$(".has-error").removeClass('has-error');
		for(var i = 0; i < length; i = i + 2) {
			tmpName = $("input[type='radio']:eq(" + i + ")").attr('name');
			tmpResult = false;
			tmpTag = document.getElementsByName(tmpName);
			if (tmpTag[0].checked || tmpTag[1].checked) {
				tmpResult = true;
			}
			if (!tmpResult) {
				$(".panel:eq(" + (i / 2) + ")").addClass('panel-danger');
				tmpTag[0].focus();
				return false;
			} else{
				$(".panel:eq(" + (i / 2) + ")").addClass('panel-success');
			}
		}

		if (username == '') {
			$("#txtUsername").focus();
			$("#txtUsername").parent().addClass('has-error');
			return false;
		}

		if (telephone == '') {
			$("#txtTelephone").focus();
			$("#txtTelephone").parent().addClass('has-error');
			return false;
		}
		if (result) {
			return true;
		} else {
			return false;
		}
	}

	var success = function(data) {
		if (data.status == 'success') {
			$("#testBox").hide();
			$("#result").empty().append(data.message.common);
			$("#resultBox").show();
		}
		else{
			showError(data.message);
		}
	}

	submitForm({
		beforeSubmit: beforeSubmit,
		success: success,
	});

	$("#back").click(function(){
		window.location.reload();
	});
});

</script>
