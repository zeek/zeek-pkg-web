<?php
/**
  * @var \App\View\AppView $this
  */

$this->assign('title', 'Home');
?>

<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Navigation') ?></li>
        <li><?= $this->Html->link(__('Packages List'), ['controller' => 'Packages', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Tags List'), ['controller' => 'Tags'], ['action' => 'index']) ?></li>
        <?php if ($userAdmin): ?>
            <li><?= $this->Html->link(__('Users List'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <?php endif; ?>
    </ul>
</nav>
<div class="packages index large-9 medium-8 columns content">
    <h1><?= __('Bro Package Browser') ?></h1>
    <p>
    <?= $this->Html->image('/img/bropkgmgr.png', 
    ['alt' => 'Bro Package Manager', 'class' => 'center',
     'width' => '200px' ]) ?>
    </p>
    <p>
    The <?= $this->Html->link(__('Bro Package Manager'),
        'http://bro-package-manager.readthedocs.io',
        ['target' => '_blank']) ?> 
    enables <?= $this->Html->link(__('Bro'), 
        'https://bro.org/',
        ['target' => '_blank']) ?> users to install third party scripts and
        plugins. The Bro Package Manager is a command line script which
        requires Bro to be installed locally. This site allows users to
        browse the colletion of third party scripts and plugins available
        from the <?= $this->Html->link(__('Bro Package Github Repository'),
        'https://github.com/bro/packages',
        ['target' => '_blank']) ?>. Use the links in the Navigation panel
        to browse by package names or tags. 
    </p>
    <p>
    Once you have found a package you want to install, use the 
    <?= $this->Html->link(__('Quickstart Guide'), 
    'http://bro-package-manager.readthedocs.io/en/stable/quickstart.html',
    ['target' => '_blank']) ?> to install the <tt>bro-pkg</tt> command line
    utility. Then use the <?= $this->Html->link(__('install'),
    'http://bro-package-manager.readthedocs.io/en/stable/bro-pkg.html#install',
    ['target' => '_blank']) ?> command to install your selected package. For
    example:
    </p>
    <p>
    <pre>bro-pkg install bro/ncsa/bro-doctor</pre>
    </p>
</div>
