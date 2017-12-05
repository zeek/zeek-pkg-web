<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\Tag $tag
  * @var \App\Model\Entity\Package[]|\Cake\Collection\CollectionInterface $packages

  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Navigation') ?></li>
        <li><?= $this->Html->link(__('Home'), '/') ?></li>
        <li><?= $this->Html->link(__('Packages List'), ['controller' => 'Packages', 'action' => 'index']) ?></li>

        <li><?= $this->Html->link(__('Tags List'), ['action' => 'index']) ?></li>
        <?php if ($userAdmin): ?>
            <li><?= $this->Html->link(__('Users List'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <?php endif; ?>
    </ul>
</nav>
<div class="tags view large-9 medium-8 columns content">
    <h3><?= h($tag->name) ?></h3>
    <div class="related">
        <h4><?= __('Related Packages') ?></h4>
        <?php if (!empty($packages)): ?>
        <table cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th scope="col"><?= $this->Paginator->sort('name') ?></th>
                    <th scope="col"><?= $this->Paginator->sort('url') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?= $this->Html->link($package->name, ['controller' => 'Packages', 'action' => 'view', $package->id]) ?></td>
                    <td><?= $this->Html->link($package->url, $package->url, ['target' => '_blank']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
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
