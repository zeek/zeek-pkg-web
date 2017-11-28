<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\User[]|\Cake\Collection\CollectionInterface $users
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New User'), ['action' => 'add']) ?></li>
    </ul>
</nav>

<?= $this->Html->scriptStart(); ?>
    $(document).on("click", "input[type='checkbox']", function() {
        this.form.submit();
    });
<?= $this->Html->scriptend(); ?>

<div class="users index large-9 medium-8 columns content">
    <h3><?= __('Users') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('display_name') ?></th>
                <th scope="col"><?= $this->Paginator->sort('given_name') ?></th>
                <th scope="col"><?= $this->Paginator->sort('family_name') ?></th>
                <th scope="col"><?= $this->Paginator->sort('email') ?></th>
                <th scope="col"><?= $this->Paginator->sort('idp_name') ?></th>
                <th scope="col"><?= $this->Paginator->sort('disabled') ?></th>
                <th scope="col"><?= $this->Paginator->sort('admin') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= h($user->display_name) ?></td>
                    <td><?= h($user->given_name) ?></td>
                    <td><?= h($user->family_name) ?></td>
                    <td><?= h($user->email) ?></td>
                    <td><?= h($user->idp_name) ?></td>
                    <?= $this->Form->create($user) ?>
                    <td><?= $this->Form->control('disabled', ['label' => false]) ?></td>
                    <td><?= $this->Form->control('admin', ['label' => false]) ?></td>
                    <?= $this->Form->end() ?>
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
