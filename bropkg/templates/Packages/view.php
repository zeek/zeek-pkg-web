<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\Package $package
  * @var \App\Model\Entity\Package[]|\Cake\Collection\CollectionInterface $packages
  */

function strNotNull($str) {
    return ($str && (strlen($str) > 0) && (strtolower($str) != 'null'));
}
function strClean($str) {
    return preg_replace('/[^a-zA-Z0-9]/', '_', $str);
}
?>
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

<div class="packages content">
    <h3><?= h($package->short_name) ?></h3>

    <div class="row">
         <?= $this->Html->link($package->url, $package->url, ['target' => '_blank']) ?>
    </div>
    <div class="row gx-1">
      <div class="col col-md-auto py-1">
        <?php
        echo '<a href="' . $package->url . '/watchers' .
              '" target="_blank" title="Watchers"
              class="border border-1 rounded-3 bg-white p-1"><svg viewBox="0 0 16 16" version="1.1"
              width="16" height="16" aria-hidden="true"><path
              fill-rule="evenodd" d="M8.06 2C3 2 0 8 0 8s3 6 8.06 6C13 14 16
              8 16 8s-3-6-7.94-6zM8 12c-2.2 0-4-1.78-4-4 0-2.2 1.8-4 4-4
              2.22 0 4 1.8 4 4 0 2.22-1.78 4-4 4zm2-4c0 1.11-.89 2-2 2-1.11
              0-2-.89-2-2 0-1.11.89-2 2-2 1.11 0 2 .89 2 2z"/></svg> ' .
             $package->subscribers_count . "</a>\n";
        ?>
      </div>
      <div class="col col-md-auto py-1">
        <?php
        echo '<a href="' . $package->url . '/stargazers' .
             '" target="_blank" title="Stargazers"
              class="border border-1 rounded-3 bg-white p-1"><svg viewBox="0 0 14 16" version="1.1"
              width="14" height="16" aria-hidden="true"><path
              fill-rule="evenodd" d="M14 6l-4.9-.64L7 1 4.9 5.36 0 6l3.6
              3.26L2.67 14 7 11.67 11.33 14l-.93-4.74z"/></svg> ' .
              $package->stargazers_count . "</a>\n";
        ?>
      </div>
      <div class="col col-md-auto py-1">
        <?php
        echo '<a href="' . $package->url . '/network' .
              '" target="_blank" title="Forks"
              class="border border-1 rounded-3 bg-white p-1"><svg viewBox="0 0 10 16" version="1.1"
              width="10" height="16" aria-hidden="true"><path
              fill-rule="evenodd" d="M8 1a1.993 1.993 0 0 0-1 3.72V6L5 8 3
              6V4.72A1.993 1.993 0 0 0 2 1a1.993 1.993 0 0 0-1 3.72V6.5l3
              3v1.78A1.993 1.993 0 0 0 5 15a1.993 1.993 0 0 0
              1-3.72V9.5l3-3V4.72A1.993 1.993 0 0 0 8 1zM2 4.2C1.34 4.2.8
              3.65.8 3c0-.65.55-1.2 1.2-1.2.65 0 1.2.55 1.2 1.2 0 .65-.55
              1.2-1.2 1.2zm3 10c-.66 0-1.2-.55-1.2-1.2 0-.65.55-1.2
              1.2-1.2.65 0 1.2.55 1.2 1.2 0 .65-.55 1.2-1.2 1.2zm3-10c-.66
              0-1.2-.55-1.2-1.2 0-.65.55-1.2 1.2-1.2.65 0 1.2.55 1.2 1.2 0
              .65-.55 1.2-1.2 1.2z"/></svg> ' .
              $package->forks_count . "</a>\n";
        ?>
      </div>
      <div class="col col-md-auto py-1">
        <?php
        echo '<a href="' . $package->url . '/issues' .
              '" target="_blank" title="Issues"
              class="border border-1 rounded-3 bg-white p-1"><svg viewBox="0 0 14 16" version="1.1"
              width="14" height="16" aria-hidden="true"><path
              fill-rule="evenodd" d="M7 2.3c3.14 0 5.7 2.56 5.7 5.7s-2.56
              5.7-5.7 5.7A5.71 5.71 0 0 1 1.3 8c0-3.14 2.56-5.7 5.7-5.7zM7
              1C3.14 1 0 4.14 0 8s3.14 7 7 7 7-3.14 7-7-3.14-7-7-7zm1
              3H6v5h2V4zm0 6H6v2h2v-2z"/></svg> '.
              $package->open_issues_count . "</a>\n";
        ?>
      </div>
      <div class="col col-md-auto py-1">
        <?php
        echo '<a href="' . $package->url . '/commit' . '" target="_blank"
              class="border border-1 rounded-3 bg-white p-1">Last Push ' .
              $package->pushed_at . "</a>\n";
        ?>
      </div>
    </div>

    <?php if ((!empty($package->readme)) && (!empty($package->readme_name))): ?>
        <p></p>
        <div class="row gx-1">
          <div class="col border border-1 rounded-3 bg-white p-1">
            <article class="markdown-body entry-content">
            <?php if (preg_match('/\.rst$/', $package->readme_name)): ?>
                <?= $this->RstMarkup->parse($package->readme); ?>
            <?php else: ?>
                <?= $this->Markdown->parse($package->readme, $package->url); ?>
            <?php endif; ?>
            </article>
          </div>
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
        <div class="mb-3">
            <h4><?= __('Package Version :') ?></h4>
            <select class="div-toggle" data-target=".metadata-versions">
                <?php foreach ($versions as $version): ?>
                    <?= '<option value="' . $version . '" data-show="._' . strClean($version) . '"' .
                        (($version == $selected) ? ' selected="selected"' : '') .
                        '>' . $version . '</option>' ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="metadata-versions">
            <?php foreach ($package->metadatas as $metadata): ?>
                <?= '<div class="_' . strClean($metadata->version) . ' hide">' ?>
                    <?php if (strNotNull($metadata->description)): ?>
                    <div class="mb-3">
                        <h4><?= __('Description :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->description)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->script_dir)): ?>
                    <div class="mb-3">
                        <h4><?= __('Script Dir :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->script_dir)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->plugin_dir)): ?>
                    <div class="mb-3">
                        <h4><?= __('Plugin Dir :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->plugin_dir)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->build_command)): ?>
                    <div class="mb-3">
                        <h4><?= __('Build Command :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->build_command)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->user_vars)): ?>
                    <div class="mb-3">
                        <h4><?= __('Users Vars :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->user_vars)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->test_command)): ?>
                    <div class="mb-3">
                        <h4><?= __('Test Command :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->test_command)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->config_files)): ?>
                    <div class="mb-3">
                        <h4><?= __('Config Files :') ?></h4>
                        <?= $this->Text->autoParagraph(h($metadata->config_files)); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (strNotNull($metadata->depends)): ?>
                    <div class="mb-3">
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
                    <div class="mb-3">
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
                    <div class="mb-3">
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
                    <div class="mb-3">
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
                    <?php if (strNotNull($metadata->package_ci)): ?>
                    <div class="mb-3">
                        <?php
                        $json = json_decode($metadata->package_ci, true);
                        echo '<h4>';
                        echo __('Package Checks : ');
                        if (array_key_exists('ok', $json)) {
                            echo '<span style="color:' . ($json['ok'] ? 'green">&#9745;' : 'red">&#8999;') . '</span>';
                        }
                        echo '</h4>';
                        if (array_key_exists('checks', $json)) {
                            foreach ($json['checks'] as $check) {
                                if (array_key_exists('name', $check)) {
                                    echo '<div';
                                    if (array_key_exists('description', $check)) {
                                        echo ' title="' . $check['description'] . '"';
                                    }
                                    echo '>' . ucwords(preg_replace('/_/', ' ', $check['name'])) . ': ';
                                    if (array_key_exists('ok', $check)) {
                                        echo '<span style="color:' . ($check['ok'] ? 'green">&#9745;' : 'red">&#8999;') . '</span>';
                                    }
                                    echo '</div>';
                                }
                                foreach (['info', 'warnings', 'errors'] as $val) {
                                    if (array_key_exists($val, $check)) {
                                        if (count($check[$val]) > 0) {
                                            echo '<div style="padding-left:2em">';
                                            echo ucwords($val) . ':';
                                            echo '<div style="padding-left:2em">';
                                            echo implode('<br/>', $check[$val]);
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                    }
                                }

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
