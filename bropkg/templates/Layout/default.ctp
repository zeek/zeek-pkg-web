<?php
$pageDescription = 'Zeek Package Manager';

// Save the current page to session so we can redirect if needed
$lastpage = $this->request->getRequestTarget();
$this->request->session()->write('lastpage', $lastpage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $this->Html->meta('icon') ?>
    <title>
        <?= $pageDescription ?>:
        <?= $this->fetch('title') ?>
    </title>

    <?= $this->Html->css('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css') ?>
    <?= $this->Html->css('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css') ?>
    <?= $this->Html->css('cake') ?>
    <?= $this->Html->css('zeek') ?>

    <?= $this->Html->script(['https://code.jquery.com/jquery-3.3.1.min.js']) ?>
    <?= $this->Html->script(['https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js']) ?>
</head>
<body>
    <?= $this->Navbar->create('<img src="/img/zeekpkgmgr.png" alt="Zeek Package Manager" width="40" style="margin-top:-10px;" title="Home" />',
        ['fixed' => 'top', 'fluid' => true]) ?>
    <?= $this->Navbar->beginMenu() ?>
    <?= $this->Navbar->link('Packages', ['controller' => 'packages'],
        ['class' => (preg_match('%^/packages%', $lastpage) ? 'active' : '')]) ?>
    <?= $this->Navbar->link('Tags', ['controller' => 'tags'],
        ['class' => (preg_match('%^/tags%', $lastpage) ? 'active' : '')]) ?>
    <?= $this->Navbar->endMenu() ?>
    <?= $this->Form->create(null, [
        'url' => ['controller' => 'Packages', 'action' => 'index'],
        'valueSources' => ['query'],
        'class' => 'navbar-form navbar-right'
        ]) ?>
    <div class="form-group">
        <?= $this->Form->text('q', [
        'placeholder' => 'Search...',
        'maxlength' => '50',
        'size' => '30',
        'class' => 'form-control'
        ]) ?>
    </div>
    <button type="submit" class="btn btn-default"><span class="glyphicon
        glyphicon-search"></span></button>
    <?= $this->Form->end() ?>
    <?= $this->Navbar->end() ?>

    <?= $this->Flash->render() ?>
    <div class="container">
        <?= $this->fetch('content') ?>
    </div>
    <footer>
    </footer>
</body>
</html>
