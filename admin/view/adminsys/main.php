<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="google" value="notranslate">
	<title>后台管理</title>
	<script src="/resource/js/jquery.min.js"></script>
	<script src="/resource/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="/resource/css/bootstrap.min.css"></link>
	<link rel="stylesheet" href="/resource/css/dashboard.css"></link>
	<link rel="stylesheet" href="/resource/css/admin.css"></link>
</head>
<body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Project name</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
          	<?php foreach ($this->admin_acl_config as $key => $value) { //class="active" ?>
            <li><a href="#"><?php echo $value['name'] ?></a></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <div class="col-md-1 sidebar">
          <ul class="nav nav-sidebar">
            <li class="active"><a href="#">Overview <span class="sr-only">(current)</span></a></li>
            <li><a href="#">Reports</a></li>
            <li><a href="#">Analytics</a></li>
            <li><a href="#">Export</a></li>
          </ul>
        </div>
        <div class="col-md-11 col-md-offset-1 main">
			<ol class="breadcrumb">
			  <li><a href="#">Home</a></li>
			  <li><a href="#">Library</a></li>
			  <li class="active">Data</li>
			</ol>
        	<form class="form-inline">
			  <div class="form-group">
			    <label for="exampleInputName2">用户名&nbsp;&nbsp;</label>
			    <input type="text" class="form-control" id="exampleInputName2" placeholder="Jane Doe">
			  </div>
			  &nbsp;&nbsp;
			  <div class="form-group">
			    <label for="exampleInputEmail2">密码&nbsp;&nbsp;</label>
			    <input type="email" class="form-control" id="exampleInputEmail2" placeholder="jane.doe@example.com">
			  </div>
			  <button type="submit" class="btn btn-primary">查询</button>
			</form>
			<br/>
			<h6>共有100条记录</h6>
			<table class="table table-bordered mt10">
				<tr>
					<th>序号</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Username</th>
				</tr>
				<tr>
					<td>序号</td>
					<td>First Name</td>
					<td>Last Name</td>
					<td>Username</td>
				</tr>
				<tr>
					<td>序号</td>
					<td>First Name</td>
					<td>Last Name</td>
					<td>Username</td>
				</tr>
			</table>

			<nav>
			  <ul class="pagination">
			    <li>
			      <a href="#" aria-label="Previous">
			        <span aria-hidden="true">&laquo;</span>
			      </a>
			    </li>
			    <li><a href="#">1</a></li>
			    <li><a href="#">2</a></li>
			    <li><a href="#">3</a></li>
			    <li><a href="#">4</a></li>
			    <li><a href="#">5</a></li>
			    <li>
			      <a href="#" aria-label="Next">
			        <span aria-hidden="true">&raquo;</span>
			      </a>
			    </li>
			  </ul>
			</nav>
        </div>
      </div>
    </div>
  </body>
</html>
