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
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('name') ?></th>
                <th scope="col"><?= $this->Paginator->sort('url') ?></th>
            </tr>
        </thead>
        <tbody style="border-bottom: 1px solid teal">
            <?php foreach ($packages as $package): ?>
            <tr>
                <table cellpadding="0" cellspacing="0" style="border: 1px
                solid teal; margin-bottom: 5px">
                <tr>
                <td><?= $this->Html->link($package->name, 
                ['action' => 'view', $package->id]) ?></td>
                <td><?= $this->Html->link($package->url, $package->url, 
                ['target' => '_blank']) ?></td>
                </tr>
                <tr style="border-bottom: 1px solid teal">
                <td colspan="2" style="white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px;">
                <?= $package->metadatas[0]->description ?>
                </td>
                </tr>
                </table>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
