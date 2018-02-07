<?php
$pageDescription = 'Bro Package Manager';

// Save the current page to session so we can redirect if needed
$this->request->session()->write('lastpage', $this->request->here());
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $pageDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('base.css') ?>
    <?= $this->Html->css('cake.css') ?>
    <?= $this->Html->css('bro.css') ?>
    <?= $this->Html->css('github.css') ?>
    <?= $this->Html->css('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'); ?>
    <?= $this->Html->script(['jquery']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-bar expanded" data-topbar>
        <ul class="title-area large-3 medium-4 columns">
            <li class="name">
                <h1><?= $this->Html->link($this->fetch('title'),
                ['controller' => $this->request->params['controller']]) ?></h1>
            </li>
        </ul>
        <div class="top-bar-section">
            <?= $this->Form->create(null, [
                'url' => ['controller' => 'Packages', 
                'action' => 'index'],
                'valueSources' => ['query']
            ]); ?>
                <ul class="left">
                    <li class="search">
                    <?= $this->Form->text('q', [
                    'placeholder' => 'Search...',
                    'maxlength' => '50',
                    'size' => '30'
                    ]); ?>
                    </li>
                    <li>
                    <button type="submit"><i class="fa fa-search"></i></button>
                    </li>
                </ul>
            <?= $this->Form->end(); ?>
            <ul class="right">
                <?php if (is_null($userId)): ?>
                    <li class="libutton">
                    <?= $this->Html->link('Login', '/oauth/cilogon') ?>
                    </li>
                <?php else: ?>
                    <li class="user">
                    <?= h($userDisplayName) ?>
                    </li>
                    <li class="libutton">
                    <?= $this->Html->link('Logout', '/users/logout') ?>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <?= $this->Flash->render() ?>
    <div class="container clearfix">
        <?= $this->fetch('content') ?>
    </div>
    <footer>
    </footer>
</body>
</html>
