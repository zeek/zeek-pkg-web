<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\Package[]|\Cake\Collection\CollectionInterface $packages
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Navigation') ?></li>
        <li><?= $this->Html->link(__('Home'), '/') ?></li>
        <li><?= $this->Html->link(__('Tags List'), ['controller' => 'Tags', 'action' => 'index']) ?></li>
        <?php if ($userAdmin): ?>
            <li><?= $this->Html->link(__('Users List'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <?php endif; ?>
    </ul>
</nav>
<div class="packages index large-9 medium-8 columns content">
    <h3><?= __('Packages') ?></h3>
    <?php foreach ($packages as $package): ?>
    <div class="packagebox">
        <h4> <?= $this->Html->link($package->short_name, ['action' => 'view', $package->id]) ?>
        </h4>
        <p>
            By <?= $this->Html->link($package->author, ['action' => 'index', '?' =>  ['q' => $package->author]]) ?>
        <p>
        <?= $package->metadatas[0]->description ?>
        </p>
    </div>
    <?php endforeach; ?>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
