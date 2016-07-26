<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="google" value="notranslate">
	<title>后台管理</title>
	<script src="/resource/js/jquery.min.js"></script>
	<script src="/resource/js/bootstrap.min.js"></script>
	<script src="/resource/js/jquery.form.min.js"></script>
  	<script src="/resource/js/fun.js"></script>
	<link rel="stylesheet" href="/resource/css/bootstrap.min.css"></link>
	<link rel="stylesheet" href="/resource/css/admin.css"></link>
	<style>
	html, body { width: 100%; height: 100%; }
	.error{color:#fff;margin-top:5px;position:absolute;font-size:13px;}
	</style>
</head>
<body>
	<div class="login_bg">
		<div class="login_box_mask"></div>
		<div class="login_box">
			<div class="login_box_title">Sign In</div>
			<div class="login_info_box">
				<form method="post" id="xform">
				<input type="text" id="username" name="username" class="login_input login_username mt30" placeholder="Username" />
				<div class="error username_error" style="display: none;"></div>
				<input type="password" id="password" name="password" class="login_input login_password mt30" placeholder="Password" />
				<div class="error password_error" style="display: none;"></div>
				<input type="submit" value="Log In" class="btn btn-success btn-block mt30" id="submitBtn" />
				<div class="error common_error" style="display: none;text-align: center;"></div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>
<script>
$(function(){
	submitForm({
		success: function(data) {
			if (data.status == 'success') {
				window.location.href = '/admin/adminsys/main';
			}
			else{
				showError(data.message);
			}
		},
		submitBtn: "#submitBtn"
	});
});
</script>
