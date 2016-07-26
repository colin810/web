<nav class="navbar navbar-inverse">
  <div class="container">
    <div class="navbar-header">
      <a class="navbar-brand" href="<?php echo $this->createUrl('cms/list') ?>">Yespo CMS</a>
    </div>
    <?php if (isset($_SESSION['yespoID'])) { ?>
    <div id="navbar" class="navbar-collapse collapse">
      <form class="navbar-form navbar-right">
        <h4>
        <a href="<?php echo $this->createUrl('batch') ?>"><span class="label label-primary"><?php echo $_SESSION['yespoID'] ?></span></a>
        <a href="<?php echo $this->createUrl('add') ?>"><span class="label label-danger">Add Key</span></a>
        <a class="label label-success" href="<?php echo $this->createUrl('logout') ?>">Sign Out</a>
        </h4>
      </form>
    </div>
    <?php } ?>
  </div>
</nav>
