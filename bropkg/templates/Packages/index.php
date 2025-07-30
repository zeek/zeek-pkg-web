<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\Package[]|\Cake\Collection\CollectionInterface $packages
  */
?>
<div class="packages content">
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
    <div class="paginator row justify-content-md-center">
        <ul class="pagination col-md-auto">
            <?= $this->Paginator->first('<< First') ?>&nbsp;
            <?= $this->Paginator->prev('< Previous') ?>&nbsp;
            <?= $this->Paginator->numbers() ?>&nbsp;
            <?= $this->Paginator->next('Next >') ?>&nbsp;
            <?= $this->Paginator->last('Last >>') ?>
        </ul>
<?
// TODO: text-black-50 is deprecated in bootstrap 5.1.0 and will be removed
// in a later version. it should be replaced with text-black text-opacity-50
// eventually
?>
        <p class="text-black-50 text-end"><?= $this->Paginator->counter('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total') ?></p>
    </div>
</div>
