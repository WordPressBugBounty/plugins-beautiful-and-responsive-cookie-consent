<?php
if (!defined('ABSPATH')) {
    exit;
}

?>

<div id="nsc_bar_sidebar">
  <?php if (!empty($objSettings->setting_page_fields->tabs[$active_tab_index]->tab_tipps)) {?>
  <div class="nsc_bar_info_box">
    <h3>Tipps</h3>
    <div class="nsc_bar_inside_text">
      <?php echo $objSettings->setting_page_fields->tabs[$active_tab_index]->tab_tipps ?>
    </div>
  </div>
<?php }?>
<a href="https://beautiful-cookie-banner.com/articles/cookie-banner-test-monitor/" target="_blank"><img width="280px" src="<?php echo NSC_BAR_PLUGIN_URL . 'admin/img/banner3.png' ?>" /></a>
  <div class="nsc_bar_info_box">
    <h3>Share some love</h3>
    <div class="nsc_bar_inside_text">
      <div>
        🤟
        <a
          target="_blank"
          href="https://wordpress.org/support/plugin/beautiful-and-responsive-cookie-consent/reviews/#new-post"
          >Review this plugin
        </a>
      </div>
      <div>
        You enjoy this free plugin? Really happy to hear. Please leave a nice review <a
          target="_blank"
          href="https://wordpress.org/support/plugin/beautiful-and-responsive-cookie-consent/reviews/#new-post"
          >here.
        </a> Thanks a lot.
      </div>
      <br />
      <div>
        🤨
        <a
          target="_blank"
          href="https://wordpress.org/support/plugin/beautiful-and-responsive-cookie-consent/"
          >Don't like this plugin?</a
        >
      </div>
      <div>
        Or you have discovered a bug? Please tell us. It really helps us improving this plugin. Use the
        link above or
        <a target="_blank" href="https://beautiful-cookie-banner.com/contact-form/"
          >this formular.</a
        >
      </div>
      <br />
      <div>
        🖖
        <a target="_blank" href="https://beautiful-cookie-banner.com/">More Features?</a>
      </div>
      <div>
        Or you are able to support the development with money? Have a look at the
        <a target="_blank" href="https://beautiful-cookie-banner.com/">premium add-on.</a>
      </div>
    </div>
  </div>

  <div class="nsc_bar_info_box">
    <h3>Getting Help</h3>
    <div class="nsc_bar_inside_text">
      <div>
        <a target="_blank" href="https://beautiful-cookie-banner.com/documentation/"
          >Official Documentation</a
        >
      </div>
      <div>Here you can find some documentation for this plugin.</div>
      <br />
      <div>
        <a
          target="_blank"
          href="https://wordpress.org/support/plugin/beautiful-and-responsive-cookie-consent/"
          >Wordpress Support Forum</a
        >
      </div>
      <div>
        If the documentation is not helping please have a look at the Forum, maybe your question is
        already answered.
      </div>
    </div>
  </div>
  <div class="nsc_bar_info_box">
    <h3>Description & Credits</h3>
    <div class="nsc_bar_inside_text">
      This plugin uses the very great open source cookie consent banner solution from osano:
      <a href="https://github.com/osano/cookieconsent/tree/master" target="_blank">github link</a>
      BUT only as basis. In this wordpress plugin version there were some adjustements.<br />
      If you have feature requests or problems let me know.
      <p>
        You want to have a link in your data privacy page or anywhere else on your page to show
        banner again? Use this shortcode:
        <strong>[cc_show_cookie_banner_nsc_bar]</strong>
      </p>
    </div>
  </div>
</div>
