<!-- Begin - header -->
<header class="header" role="banner">
  <a class="skipnav" href="#main-content">Gå til sidens indhold</a>

  <!-- Begin - portal header -->
  <div class="portal-header">
    <div class="container portal-header-inner">

      <!-- Begin - logo -->
      <?php if ($logo): ?>
        <a href="javascript:void(0);" class="alert-leave2" href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>">
          <img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" />
        </a>
      <?php endif; ?>

      <?php if (!empty($site_name)): ?>
        <a href="javascript:void(0);" href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>">
          <span class="alert-leave2"><?php print $site_name; ?></span>
        </a>
      <?php endif; ?>
      <!-- End - logo -->

      <!-- Begin - mobile -->
      <button
        class="button button-secondary button-menu-open js-menu-open ml-auto d-print-none"
        aria-haspopup="menu" title="<?php print t('Open mobile navigation'); ?>"><?php print t('Navigation'); ?></button>
      <!-- End - mobile -->

      <!-- Begin - secondary navigation -->
      <div class="portal-info">
        <p class="user"><b class="username">Lone hansen</b> </p>

        <a href="#"
           class="button button-secondary alert-leave d-print-none"
           role="button">
          Log af
        </a>
      </div>
      <!-- End - secondary navigation -->

    </div>
  </div>
  <!-- End - portal header -->

  <!-- Begin - solution header -->
  <div class="solution-header">
    <div class="container solution-header-inner">

      <!-- Begin - title -->
      <?php print render($title_prefix); ?>
      <?php if (!empty($title)): ?>
        <div class="solution-heading">
          <a href="#" title="<?php print t('Home'); ?>"
             aria-label="<?php print t('Home'); ?>"
             class="alert-leave2"><?php print $title; ?></a>
        </div>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
      <!-- End - title -->

      <!-- Begin - authority details -->
      <?php if ($theme_settings['authority_details']['show']): ?>
        <div class="solution-info">

          <?php if ($theme_settings['authority_details']['name']): ?>
            <h6 class="h5 authority-name"><?php print $theme_settings['authority_details']['name']; ?></h6>
          <?php endif; ?>

          <?php if (!empty($theme_settings['authority_details']['text']) || (!empty($theme_settings['authority_details']['phone_system']) && !empty($theme_settings['authority_details']['phone_readable']))) : ?>
            <p>
              <?php print $theme_settings['authority_details']['text']; ?>

              <?php if (!empty($theme_settings['authority_details']['phone_system']) && !empty($theme_settings['authority_details']['phone_readable'])) : ?>
                <?php print '<a href="tel:' . $theme_settings['authority_details']['phone_system'] . '">' . $theme_settings['authority_details']['phone_readable'] . '</a>'; ?>
              <?php endif; ?>
            </p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <!-- End - authority details -->

    </div>
  </div>
  <!-- End - solution header -->

  <!-- Begin - navigation -->
  <?php if (!empty($navigation__primary) || $theme_settings['authority_details']['show']): ?>
    <nav role="navigation" class="nav">

      <!-- Begin - responsive toggle -->
      <?php if (!empty($navigation__primary)): ?>
        <button
          class="button button-secondary button-menu-close js-menu-close"
          title="<?php print t('Close mobile navigation'); ?>">
          <svg class="icon-svg" focusable="false" aria-hidden="true">
            <use xlink:href="#close"></use>
          </svg><?php print t('Close'); ?></button>
      <?php endif; ?>
      <!-- End - responsive toggle -->

      <!-- Begin - main navigation -->
      <?php if (!empty($navigation__primary)): ?>
        <div class="navbar navbar-primary">
          <div class="navbar-inner container">
            <?php print render($navigation__primary); ?>
          </div>
        </div>
      <?php endif; ?>
      <!-- End - main navigation -->

      <!-- Begin - user navigation -->
      <div class="portal-info-mobile">
        <p class="user">
          <b class="username">Christian Emil Vestergaard Christensen</b>
        </p>

        <p>Københavns Urmager og Værksted v/Martin Elsig</p>

        <a href="#" class="button button-secondary alert-leave"
           role="button">
          Log af
        </a>
      </div>
      <!-- End - user navigation -->

      <!-- Begin - authority details -->
      <?php if ($theme_settings['authority_details']['show']): ?>
        <div class="solution-info-mobile">
          <?php if ($theme_settings['authority_details']['name']): ?>
            <h6 class="h5 authority-name"><?php print $theme_settings['authority_details']['name']; ?></h6>
          <?php endif; ?>

          <?php if (!empty($theme_settings['authority_details']['text']) || (!empty($theme_settings['authority_details']['phone_system']) && !empty($theme_settings['authority_details']['phone_readable']))) : ?>
            <p>
              <?php print $theme_settings['authority_details']['text']; ?>

              <?php if (!empty($theme_settings['authority_details']['phone_system']) && !empty($theme_settings['authority_details']['phone_readable'])) : ?>
                <?php print '<a href="tel:' . $theme_settings['authority_details']['phone_system'] . '">' . $theme_settings['authority_details']['phone_readable'] . '</a>'; ?>
              <?php endif; ?>
            </p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <!-- End - authority details -->

    </nav>
  <?php endif; ?>
  <!-- End - navigation -->

</header>
<!-- End - header -->

<!-- Begin - content -->
<!-- End - content -->
<div class="container page-container">
  <div class="row">

    <?php if (!empty($page['content__sidebar_left']) || !empty($breadcrumb)): ?>
      <!-- Begin - sidebar - left -->
      <aside class="col-12 col-lg-3 sidebar-col">
        <?php if (!empty($breadcrumb)): print $breadcrumb; endif; ?>

        <?php if (!empty($page['content__sidebar_left'])): ?>
          <?php print render($page['content__sidebar_left']); ?>
        <?php endif; ?>
      </aside>
      <!-- End - sidebar - left -->
    <?php endif; ?>

    <!-- Begin - content -->
    <main<?php print $content_column_class; ?> id="main-content">

      <?php print $messages; ?>

      <?php if (!empty($tabs)): ?>
        <?php print render($tabs); ?>
      <?php endif; ?>

      <?php if (!empty($page['help'])): ?>
        <?php print render($page['help']); ?>
      <?php endif; ?>

      <?php if (!empty($page['content__center'])): ?>
        <?php print render($page['content__center']); ?>
      <?php endif; ?>

    </main>
    <!-- End - content -->

    <?php if (!empty($page['content__sidebar_right'])): ?>
      <!-- Begin - sidebar - right -->
      <aside class="col-12 col-lg-3 sidebar-col">
        <?php print render($page['content__sidebar_right']); ?>
      </aside>
      <!-- End - sidebar - right -->
    <?php endif; ?>

  </div>
</div>

<?php if (!empty($page['footer__row_1']) || !empty($page['footer__column_1']) || !empty($page['footer__column_2']) || !empty($page['footer__column_3']) || !empty($page['footer__row_2'])): ?>
  <!-- Begin - footer -->
  <footer>
    <div class="footer">
      <div class="container">

        <?php if (!empty($page['footer__row_1'])): ?>
          <!-- Begin - row 1 -->
          <div class="row">
            <div class="col-12 footer-col">
              <section>
                <div class="align-text-left">
                  <?php print render($page['footer__row_1']); ?>
                </div>
              </section>
            </div>
          </div>
          <!-- End - row 1 -->
        <?php endif; ?>

        <?php if (!empty($page['footer__column_1']) || !empty($page['footer__column_2']) || !empty($page['footer__column_3'])): ?>
          <div class="row">

            <?php if (!empty($page['footer__column_1'])): ?>
              <!-- Begin - column 1 -->
              <div class="col-12 col-md-6 footer-col">
                <section>
                  <div class="align-text-left">
                    <?php print render($page['footer__column_1']); ?>
                  </div>
                </section>
              </div>
              <!-- End - column 1 -->
            <?php endif; ?>

            <?php if (!empty($page['footer__column_2'])): ?>
              <!-- Begin - column 2 -->
              <div class="col-12 col-sm-6 col-md-3 footer-col">
                <section>
                  <div class="align-text-left">
                    <?php print render($page['footer__column_2']); ?>
                  </div>
                </section>
              </div>
              <!-- End - column 2 -->
            <?php endif; ?>

            <?php if (!empty($page['footer__column_3'])): ?>
              <!-- Begin - column 3 -->
              <div class="col-12 col-sm-6 col-md-3 footer-col">
                <section>
                  <div class="align-text-left">
                    <?php print render($page['footer__column_3']); ?>
                  </div>
                </section>
              </div>
              <!-- End - column 3 -->
            <?php endif; ?>

          </div>
        <?php endif; ?>

        <?php if (!empty($page['footer__row_2'])): ?>
          <!-- Begin - row 2 -->
          <div class="row">
            <div class="col-12 footer-col">
              <section>
                <div class="align-text-left">
                  <?php print render($page['footer__row_2']); ?>
                </div>
              </section>
            </div>
          </div>
          <!-- End - row 2 -->
        <?php endif; ?>

      </div>
    </div>
  </footer>
  <!-- End - footer -->
<?php endif; ?>
