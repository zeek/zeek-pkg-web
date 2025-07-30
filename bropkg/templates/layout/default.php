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
    <?= $this->Html->meta('icon') ?>
    <title>
        <?= $pageDescription ?>:
        <?= $this->fetch('title') ?>
    </title>

    <?= $this->Html->css('https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css') ?>
    <?= $this->Html->css('bootstrap-theme.min') ?>
    <?= $this->Html->css('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css') ?>
    <?= $this->Html->css('cake') ?>
    <?= $this->Html->css('zeek') ?>

    <?= $this->Html->script(['https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js']) ?>
    <?= $this->Html->script(['https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js']) ?>
</head>
<body>
    <?= $this->Navbar->create('<img src="/img/zeekpkgmgr.png" alt="Zeek Package Manager" width="40" style="margin-top:-10px;margin-bottom:-10px" title="Home" />',
        ['fixed' => 'top', 'collapse' => false]) ?>
    <?= $this->Navbar->beginMenu() ?>
    <?= $this->Navbar->link('Packages', ['controller' => 'packages'],
        ['class' => (preg_match('%^/packages%', $lastpage) ? 'active' : '')]) ?>
    <?= $this->Navbar->link('Tags', ['controller' => 'tags'],
        ['class' => (preg_match('%^/tags%', $lastpage) ? 'active' : '')]) ?>
    <?= $this->Navbar->endMenu() ?>
    <?= $this->Form->create(null, [
        'url' => ['controller' => 'Packages', 'action' => 'index'],
        'valueSources' => ['query'],
        'class' => 'form-inline navbar-right',
        'inline' => true
        ]) ?>
    <div class="form-group">
        <?= $this->Form->text('q', [
        'placeholder' => 'Search...',
        'maxlength' => '50',
        'size' => '30',
        'class' => 'form-control'
        ]) ?>
    </div>
    <button type="submit" class="btn btn-secondary"><span class="fa fa-search"></span></button>
    <?= $this->Form->end() ?>
    <?= $this->Navbar->end() ?>

    <?= $this->Flash->render() ?>
    <div class="container" style="padding-top: inherit;">
        <?= $this->fetch('content') ?>
    </div>
    <footer>
    </footer>
</body>
</html>
