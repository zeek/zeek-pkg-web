<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\Package $package
  */

function strNotNull($str) {
    return ((strlen($str) > 0) && (strtolower($str) != 'null'));
}
function strClean($str) {
    return preg_replace('/[^a-zA-Z0-9]/', '_', $str);
}
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Navigation') ?></li>
        <li><?= $this->Html->link(__('List All Packages'), ['controller' => 'Packages', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List All Tags'), ['controller' => 'Tags', 'action' => 'index']) ?></li>
    </ul>
</nav>

<?= $this->Html->scriptStart(); ?>
    $(document).on('change', '.div-toggle', function() {
      var target = $(this).data('target');
      var show = $("option:selected", this).data('show');
      $(target).children().addClass('hide');
      $(show).removeClass('hide');
    });
    $(document).ready(function(){
        $('.div-toggle').trigger('change');
    });
<?= $this->Html->scriptend(); ?>

<div class="packages view large-9 medium-8 columns content">
    <h3><?= h($package->name) ?></h3>

    <div class="row">
        <h4><?= __('URL') ?></h4>
         <?= $this->Html->link($package->url, $package->url, ['target' => '_blank']) ?>
    </div>

    <?php if (!empty($package->metadatas)): ?>

        <?php 
            // Find the latest metadata version for the package
            $selected = '';
            $versions = array();
            foreach ($package->metadatas as $metadata) {
                $versions[] = $metadata->version;
                if (strnatcmp($metadata->version, $selected) > 0) {
                    $selected = $metadata->version;
                }
            }
            natsort($versions);
            $versions = array_reverse($versions);
        ?>

        <div class="row">
            <h4><?= __('Package Version') ?></h4>
            <select class="div-toggle" data-target=".metadata-versions">
                <?php foreach ($versions as $version): ?>
                    <?= '<option value="' . $version . '" data-show="._' . strClean($version) . '"' . 
                        // (($version == $selected) ? ' selected="selected"' : '') .
                        '>' . $version . '</option>' ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="metadata-versions">
            <?php foreach ($package->metadatas as $metadata): ?>
                <?= '<div class="_' . strClean($metadata->version) . ' hide">' ?>
                    <?php if (strNotNull($metadata->description)): ?>
                    <div class="row">
                        <h4><?= __('Description') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->description)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->script_dir)): ?>
                    <div class="row">
                        <h4><?= __('Script Dir') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->script_dir)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->plugin_dir)): ?>
                    <div class="row">
                        <h4><?= __('Plugin Dir') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->plugin_dir)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->build_command)): ?>
                    <div class="row">
                        <h4><?= __('Build Command') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->build_command)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->user_vars)): ?>
                    <div class="row">
                        <h4><?= __('Users Vars') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->user_vars)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->test_command)): ?>
                    <div class="row">
                        <h4><?= __('Test Command') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->test_command)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->config_files)): ?>
                    <div class="row">
                        <h4><?= __('Config Files') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->config_files)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->depends)): ?>
                    <div class="row">
                        <h4><?= __('Depends') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->depends)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->external_depends)): ?>
                    <div class="row">
                        <h4><?= __('External Depends') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->external_depends)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($metadata->tags)): ?>
                    <div class="row">
                        <h4><?= __('Tags') ?></h4>
                        <?php
                            $num = count($metadata->tags);
                            for($i = 0; $i < $num; $i++) {
                                echo $this->Html->link(
                                    $metadata->tags[$i]->name, 
                                    ['controller' => 'Tags', 'action' => 'view', $metadata->tags[$i]->id]
                                );
                                if ($i < $num-1) {
                                    echo ', ';
                                }
                            }
                        ?>
                        <?php foreach ($metadata->tags as $tag): ?>
                            <?= $this->Text->autoParagraph(h($metadata->external_depends)); ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($package->readme)): ?>
        <div class="row">
            <h4><?= __('README') ?></h4>
            <article class="markdown-body entry-content" itemprop="text">
            <?= $this->Markdown->transform($package->readme); ?>
            </article>
        </div>
    <?php endif; ?>

</div>
