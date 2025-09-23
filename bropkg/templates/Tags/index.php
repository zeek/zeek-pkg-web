<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\Tag[]|\Cake\Collection\CollectionInterface $tags
  */
?>
<div class="tags content">
    <h3><?= __('Tags') ?></h3>
    <table cellpadding="0" cellspacing="0" class="table table-sm bg-white">
        <thead class="zeek-tags-table-head">
            <tr>
                <th scope="col"><?= $this->Paginator->sort('name') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tags as $tag): ?>
            <tr>
              <td><?= $this->Html->link($tag->name, ['action' => 'view', $tag->id], ['class' => 'text-decoration-none']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
