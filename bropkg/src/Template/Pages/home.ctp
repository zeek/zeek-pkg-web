<?php
/**
  * @var \App\View\AppView $this
  */

$this->assign('title', 'Home');
?>

<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Navigation') ?></li>
        <li><?= $this->Html->link(__('List All Packages'), ['controller' => 'Packages', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List All Tags'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="packages index large-9 medium-8 columns content">
    <h1><?= __('Welcome!') ?></h1>
</div>
