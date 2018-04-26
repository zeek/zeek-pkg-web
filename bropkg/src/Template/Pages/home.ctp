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
        <?= $this->Html->link('View Package List', 
          ['controller' => 'packages']) ?>
      </h3>
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
