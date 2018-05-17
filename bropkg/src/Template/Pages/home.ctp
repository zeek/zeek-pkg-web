<?php
/**
  * @var \App\View\AppView $this
  */

$this->assign('title', 'Home');
?>

  <div class="row form-group">
    <div class="col">
      <h1><?= __('Bro Package Browser') ?></h1>
    </div>
  </div>

  <div class="row form-group">
    <div class="col-sm-4 col-md-3">
      <?= $this->Html->link($this->Html->image('/img/bropkgmgr.png', 
          ['alt' => 'Bro Package Manager', 'class' => 'center',
           'width' => '200' ]),
           ['controller' => 'packages'],
           ['escapeTitle' => false,
            'title' => 'View Package List']) ?>
    </div>
    <div class="col-sm-8 col-md-9">
      <div class="row form-group">
        <div class="col">
          The <?= $this->Html->link(__('Bro Package Manager'),
              'http://bro-package-manager.readthedocs.io',
              ['target' => '_blank']) ?> 
          enables <?= $this->Html->link(__('Bro'), 
              'https://bro.org/',
              ['target' => '_blank']) ?> users to install third party
          scripts and plugins. The Bro Package Manager is a command line
          script which requires Bro to be installed locally. This site
          allows users to browse the colletion of third party scripts and
          plugins available from the <?= $this->Html->link(__('Bro Package
          Github Repository'), 'https://github.com/bro/packages',
          ['target' => '_blank']) ?>. Use the links in the Navigation panel
          to browse by package names or tags. (Note that the list of packages
          is updated once a day.)
        </div>
      </div>
      <div class="row form-group">
        <div class="col">
          Once you have found a package you want to install, use the 
          <?= $this->Html->link(__('Quickstart Guide'), 
          'http://bro-package-manager.readthedocs.io/en/stable/quickstart.html',
          ['target' => '_blank']) ?> to install the <kbd>bro-pkg</kbd> command line
          utility. Then use the <?= $this->Html->link(__('install'),
          'http://bro-package-manager.readthedocs.io/en/stable/bro-pkg.html#install',
          ['target' => '_blank']) ?> command to install your selected package. For
          example:
        </div>
      </div>
    </div>
  </div>

  <div class="row form-group">
    <div class="col">
      <pre>bro-pkg install bro/ncsa/bro-doctor</pre>
    </div>
  </div>

  <hr/>

  <div class="row form-group">
    <div class="col-sm-offset-5">
      <h3>
        <?= $this->Html->link('View List of ' . $packagecount . ' Packages', 
          ['controller' => 'packages']) ?>
      </h3>
    </div>
  </div>

  <hr/>

  <div class="row form-group">
    <div class="col-sm-3 col-sm-offset-2">
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


  <hr/>

  <div class="row form-group">
    <div class="col">
      <h4>More Features Coming Soon</h4>
      <?php
      $features = [
          'Nightly Travis CI build and test of packages',
      ];
      echo $this->Html->nestedList($features);
      ?>
    </div>
  </div>
