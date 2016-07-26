<?php $this->render('/cms/_include/header');?>
<?php $this->render('/cms/_include/top');?>
<div class="container">
  <form class="form-signin" id="xform" method="post">
    <h2 class="form-signin-heading">Please sign in</h2>
    <br/>
    <label for="yespoID" class="sr-only">Yespo ID</label>
    <input type="text" name="yespoID" id="yespoID" class="form-control" placeholder="YesPo IM ID" autofocus>
    <br/>
    <label for="yespoPassword" class="sr-only">Password</label>
    <input type="password" name="yespoPassword" id="yespoPassword" class="form-control" placeholder="Password" >
    <br/>
    <button class="btn btn-lg btn-primary btn-block" type="submit" id="btn_ok">Sign in</button>
	<br/>
    <div class="alert alert-danger" role="alert" style="display: none;">
    	<div class="yespoID_error error"></div>
    	<div class="yespoPassword_error error"></div>
    	<div class="common_error error"></div>
    </div>
  </form>
</div>

<script>
$(function(){
	var beforeSubmit = function(){
		$(".alert").hide();
	}
	var success = function(data) {
		if (data.status == 'success') {
			window.location.href = 'list.html';
		}
		else{
			showError(data.message);
			$(".alert").show();
		}
	}

	submitForm({
		beforeSubmit: beforeSubmit,
		success: success,
		submitBtn: "#btn_ok"
	});
});
</script>
<?php $this->render('/cms/_include/footer')?>
