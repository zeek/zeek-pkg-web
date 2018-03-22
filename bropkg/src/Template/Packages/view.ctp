<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\Package $package
  * @var \App\Model\Entity\Package[]|\Cake\Collection\CollectionInterface $packages
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
        <li><?= $this->Html->link(__('Home'), '/') ?></li>
        <li><?= $this->Html->link(__('Packages List'), ['controller' => 'Packages', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Tags List'), ['controller' => 'Tags', 'action' => 'index']) ?></li>
        <?php if ($userAdmin): ?>
            <li><?= $this->Html->link(__('Users List'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <?php endif; ?>
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
    <h3><?= h($package->basename) ?></h3>

    <div class="row">
         <?= $this->Html->link($package->url, $package->url, ['target' => '_blank']) ?>
    </div>

    <?php if (!empty($package->readme)): ?>
        <p></p>
        <div class="row">
            <article class="markdown-body entry-content">
            <?= $this->Markdown->transform($package->readme); ?>
            </article>
        </div>
    <?php endif; ?>


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
        
        <p></p>
        <div class="row">
            <h4><?= __('Package Version :') ?></h4>
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
                        <h4><?= __('Description :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->description)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->script_dir)): ?>
                    <div class="row">
                        <h4><?= __('Script Dir :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->script_dir)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->plugin_dir)): ?>
                    <div class="row">
                        <h4><?= __('Plugin Dir :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->plugin_dir)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->build_command)): ?>
                    <div class="row">
                        <h4><?= __('Build Command :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->build_command)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->user_vars)): ?>
                    <div class="row">
                        <h4><?= __('Users Vars :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->user_vars)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->test_command)): ?>
                    <div class="row">
                        <h4><?= __('Test Command :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->test_command)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->config_files)): ?>
                    <div class="row">
                        <h4><?= __('Config Files :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->config_files)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->depends)): ?>
                    <div class="row">
                        <h4><?= __('Depends :') ?></h4>
                        <?php
                        $depends = '';
                        $thelist = explode(PHP_EOL, $metadata->depends);
                        foreach ($thelist as $line) {
                            if (preg_match('%^(https?://[^ ]*)(.*)%', $line, $matches)) {
                                $depends .= $this->Html->link($matches[1], $matches[1]) . $matches[2];
                            } elseif (preg_match('%^([^ ]*/[^ ]*/[^ ]*)(.*)%', $line, $matches)) {
                                $found = false;
                                foreach($packages as $pkg) {
                                    if ($pkg->name == $matches[1]) {
                                        $found = true;
                                        $depends .= $this->Html->link(
                                            $pkg->name,
                                            ['action' => 'view', $pkg->id])
                                            . $matches[2];
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $depends .= $line;
                                }
                            } else {
                                $depends .= $line;
                            }
                            $depends .= "\n";
                        }
                        echo $this->Text->autoParagraph($depends);
                        ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->external_depends)): ?>
                    <div class="row">
                        <h4><?= __('External Depends :') ?></h4>
                        <?php
                        $ext_depends = '';
                        $thelist = explode(PHP_EOL, $metadata->external_depends);
                        foreach ($thelist as $line) {
                            if (preg_match('%^(https?://[^ ]*)(.*)%', $line, $matches)) {
                                $ext_depends .= $this->Html->link($matches[1], $matches[1]) . $matches[2];
                            } elseif (preg_match('%^([^ ]*/[^ ]*/[^ ]*)(.*)%', $line, $matches)) {
                                $found = false;
                                foreach($packages as $pkg) {
                                    if ($pkg->name == $matches[1]) {
                                        $found = true;
                                        $ext_depends .= $this->Html->link(
                                            $pkg->name,
                                            ['action' => 'view', $pkg->id])
                                            . $matches[2];
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $ext_depends .= $line;
                                }
                            } else {
                                $ext_depends .= $line;
                            }
                            $ext_depends .= "\n";
                        }
                        echo $this->Text->autoParagraph($ext_depends);
                        ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->suggests)): ?>
                    <div class="row">
                        <h4><?= __('Suggests :') ?></h4>
                        <?php
                        $suggests = '';
                        $thelist = explode(PHP_EOL, $metadata->suggests);
                        foreach ($thelist as $line) {
                            if (preg_match('%^(https?://[^ ]*)(.*)%', $line, $matches)) {
                                $suggests .= $this->Html->link($matches[1], $matches[1]) . $matches[2];
                            } elseif (preg_match('%^([^ ]*/[^ ]*/[^ ]*)(.*)%', $line, $matches)) {
                                $found = false;
                                foreach($packages as $pkg) {
                                    if ($pkg->name == $matches[1]) {
                                        $found = true;
                                        $suggests .= $this->Html->link(
                                            $pkg->name,
                                            ['action' => 'view', $pkg->id])
                                            . $matches[2];
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $suggests .= $line;
                                }
                            } else {
                                $suggests .= $line;
                            }
                            $suggests .= "\n";
                        }
                        echo $this->Text->autoParagraph($suggests);
                        ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($metadata->tags)): ?>
                    <div class="row">
                        <h4><?= __('Tags :') ?></h4>
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
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
