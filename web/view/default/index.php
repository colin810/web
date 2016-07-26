<?php $this->render('_include/header'); ?>
<div class="container">
	<div class="page-header">
	  <h1>功能列表</small></h1>
	</div>
	<ul class="list-group">
	  <li class="list-group-item list-group-item-info"><a href="<?php echo $this->createUrl('skype/index') ?>">Skpye资费下载</a></li>
	  <li class="list-group-item list-group-item-warning"><a href="<?php echo $this->createUrl('cms/list') ?>">文字管理系统</a></li>
	  <li class="list-group-item list-group-item-danger"><a href="<?php echo $this->createUrl('enneagram/index') ?>">九型人格测试</a></li>
	  <li class="list-group-item list-group-item-info"><a href="<?php echo $this->createUrl('svn/index') ?>">svn輔助工具</a></li>
	</ul>
</div>
<?php $this->render('_include/footer') ?>
