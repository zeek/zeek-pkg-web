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
    <?= $this->Html->script(['jquery']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-bar expanded" data-topbar>
        <ul class="title-area large-3 medium-4 columns">
            <li class="name">
                <h1><a href=""><?= $this->fetch('title') ?></a></h1>
            </li>
        </ul>
        <div class="top-bar-section">
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
