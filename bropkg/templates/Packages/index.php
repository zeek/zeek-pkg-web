<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\Package[]|\Cake\Collection\CollectionInterface $packages
  */
?>
<div class="packages index columns content">
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
            <?= $this->Paginator->first() ?>
            <?= $this->Paginator->prev() ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next() ?>
            <?= $this->Paginator->last() ?>
        </ul>
        <p><?= $this->Paginator->counter('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total') ?></p>
    </div>
</div>
