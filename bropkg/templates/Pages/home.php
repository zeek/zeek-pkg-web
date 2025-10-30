<?php
/**
  * @var \App\View\AppView $this
  */

$this->assign('title', 'Home');
?>

  <div class="row mb-3">
    <h1>Zeek Package Browser</h1>
  </div>

  <div class="row">
    <div class="col-sm-4 col-md-3">
      <?= $this->Html->link($this->Html->image('/img/zeekpkgmgr.png',
          ['alt' => 'Zeek Package Manager', 'class' => 'center',
              'width' => '200' ]),
          ['controller' => 'packages'],
          ['escapeTitle' => false,
              'title' => 'View Package List']) ?>
    </div>
    <div class="col-sm-8 col-md-9">
      <div>
        <div class="col mb-3">
          The <?= $this->Html->link(__('Zeek Package Manager'),
              'https://docs.zeek.org/projects/package-manager',
              ['target' => '_blank']) ?>
          enables <?= $this->Html->link(__('Zeek'),
              'https://zeek.org/',
              ['target' => '_blank']) ?> users to install third party
          scripts and plugins. The Zeek Package Manager is a command line
          script which requires Zeek to be installed locally. This site
          allows users to browse the collection of third party scripts and
          plugins available from the <?= $this->Html->link(__('Zeek Package
          Github Repository'), 'https://github.com/zeek/packages',
          ['target' => '_blank']) ?>. Use the links in the navigation panel
          to browse by package names or tags. (Note that the list of packages
          is updated once a day.)
        </div>
      </div>
      <div>
        <div class="col">
          Once you have found a package you want to install, use the
          <?= $this->Html->link(__('Quickstart Guide'),
              'https://docs.zeek.org/projects/package-manager/en/stable/quickstart.html',
              ['target' => '_blank']) ?> to install the <kbd>zkg</kbd> command line
          utility. Then use the <?= $this->Html->link(__('install'),
              'https://docs.zeek.org/projects/package-manager/en/stable/zkg.html#install',
              ['target' => '_blank']) ?> command to install your selected package. For
          example:
        </div>
      </div>
      <div class="my-4">
        <code class="p-3">zkg install logschema</code>
      </div>
    </div>
  </div>

  <hr class="my-4"/>

  <div class="row">
    <div class="text-center">
      <h3>
        <?= $this->Html->link('View List of ' . $packagecount . ' Packages',
          ['controller' => 'packages']) ?>
      </h3>
    </div>
  </div>

  <hr class="my-4"/>

  <div class="row justify-content-center">
    <div class="col-sm-3">
      <h4 class="text-center">Top Watched</h4>
      <?php
        if (!empty($topwatched)) {
          echo '<ul>';
          foreach ($topwatched as $top) {
            echo '<li>', $top->subscribers_count, ' ',
              '<svg viewBox="0 0 16 16" version="1.1"
              width="16" height="16" aria-hidden="true"><path
              fill-rule="evenodd" d="M8.06 2C3 2 0 8 0 8s3 6 8.06 6C13 14 16
              8 16 8s-3-6-7.94-6zM8 12c-2.2 0-4-1.78-4-4 0-2.2 1.8-4 4-4
              2.22 0 4 1.8 4 4 0 2.22-1.78 4-4 4zm2-4c0 1.11-.89 2-2 2-1.11
              0-2-.89-2-2 0-1.11.89-2 2-2 1.11 0 2 .89 2 2z"/></svg> ',
              $this->Html->link($top->short_name,
                ['controller' => 'Packages', 'action' => 'view', $top->id]),
              '</li>';
          }
          echo '</ul>';
        }
      ?>
    </div>

    <div class="col-sm-3">
      <h4 class="text-center">Top Starred</h4>
      <?php
        if (!empty($topstarred)) {
          echo '<ul>';
          foreach ($topstarred as $top) {
            echo '<li>', $top->stargazers_count, ' ',
              '<svg viewBox="0 0 14 16" version="1.1"
              width="14" height="16" aria-hidden="true"><path
              fill-rule="evenodd" d="M14 6l-4.9-.64L7 1 4.9 5.36 0 6l3.6
              3.26L2.67 14 7 11.67 11.33 14l-.93-4.74z"/></svg> ',
                $this->Html->link($top->short_name,
                    ['controller' => 'Packages', 'action' => 'view', $top->id]),
                '</li>';
          }
          echo '<ul>';
        }
      ?>
    </div>

    <div class="col-sm-3">
      <h4 class="text-center">Recent Updates</h4>
      <?php
        if (!empty($lastupdated)) {
          echo '<ul>';
          foreach ($lastupdated as $top) {
            echo '<li>', $top->pushed_at, ' ',
                $this->Html->link($top->short_name,
                    ['controller' => 'Packages', 'action' => 'view', $top->id]),
                '</li>';
          }
          echo '</ul>';
        }
      ?>
    </div>
  </div>
