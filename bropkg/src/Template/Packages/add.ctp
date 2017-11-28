<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Packages'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Metadatas'), ['controller' => 'Metadatas', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Metadata'), ['controller' => 'Metadatas', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="packages form large-9 medium-8 columns content">
    <?= $this->Form->create($package) ?>
    <fieldset>
        <legend><?= __('Add Package') ?></legend>
        <?php
            echo $this->Form->control('name');
            echo $this->Form->control('url');
            echo $this->Form->control('readme');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
