<?php
$pageDescription = 'Zeek Package Manager';

// Save the current page to session so we can redirect if needed
$lastpage = $this->request->getRequestTarget();
$this->request->getSession()->write('lastpage', $lastpage);

?>
<!DOCTYPE html>
<html lang="en" style="font-size: 14px">
<head>
    <?= $this->Html->charset() ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <? echo $this->Html->meta('favicon.ico', '/favicon.ico', ['type' => 'icon']); ?>
    <title>
        <?= $pageDescription ?>:
        <?= $this->fetch('title') ?>
    </title>

    <?
    echo $this->Html->css('BootstrapUI.bootstrap.min');
    echo $this->Html->css(['BootstrapUI./font/bootstrap-icons', 'BootstrapUI./font/bootstrap-icon-sizes']);
    echo $this->Html->script('https://code.jquery.com/jquery-3.7.1.min.js', [],
			  ['integrity' => 'sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=',
			   'crossorigin' => 'anonymous']);
    echo $this->Html->css('zeek')
    ?>
</head>
<body>
  <header class="navbar navbar-expand navbar-light bd-navbar sticky-top px-4">
    <a class="navbar-brand mr-0 mr-md-2" href="/">
      <img src="/img/zeekpkgmgr.png" alt="Zeek Package Manager" title="Home" height=40 width=40/>
    </a>
    <ul class="navbar-nav flex-row flex-wrap bd-navbar-nav">
      <li class="nav-item">
        <?= $this->Html->link('Packages', ['controller' => 'packages'],
            ['class' => (preg_match('%^/packages%', $lastpage) ? 'nav-link active' : 'nav-link')]) ?>
      </li>
      <li class="nav-item">
        <?= $this->Html->link("Tags", ["controller" => "Tags"],
            ['class' => (preg_match('%^/tags%', $lastpage) ? 'nav-link active' : 'nav-link')]) ?>
      </li>
    </ul>
    <?= $this->Form->create(null, [
        'url' => ['controller' => 'Packages', 'action' => 'index'],
        'valueSources' => ['query'],
        'class' => 'navbar-nav flex-row ms-md-auto',
        'inline' => true
        ]) ?>
    <div class="form-group">
        <?= $this->Form->text('q', [
        'placeholder' => 'Search...',
        'maxlength' => '50',
        'size' => '30'
        ]) ?>
    </div>&nbsp;
    <button type="submit" class="btn btn-secondary btn-zeek"><span class="bi bi-search"></span></button>
    <?= $this->Form->end() ?>
  </header>

  <div class="container bd-gutter mt-2 my-md-4">
    <?= $this->fetch('content') ?>
  </div>
</body>
</html>
