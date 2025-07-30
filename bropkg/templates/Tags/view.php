<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\Tag $tag
  * @var \App\Model\Entity\Package[]|\Cake\Collection\CollectionInterface $packages

  */
?>
<div class="tags content">
    <h3><?= h($tag->name) ?></h3>
    <div>
        <h4><?= __('Related Packages') ?></h4>
        <?php if (!empty($packages)): ?>
        <table cellpadding="0" cellspacing="0" class="table table-sm bg-white">
            <thead class="zeek-tags-table-head">
                <tr>
                    <th scope="col"><?= $this->Paginator->sort('name') ?></th>
                    <th scope="col"><?= $this->Paginator->sort('url') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?= $this->Html->link($package->name, ['controller' => 'Packages', 'action' => 'view', $package->id], ['class' => 'text-decoration-none']) ?></td>
                    <td><?= $this->Html->link($package->url, $package->url, ['target' => '_blank', 'class' => 'text-decoration-none']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
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
