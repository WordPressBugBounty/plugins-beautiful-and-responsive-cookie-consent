<span id="nsc_settings_content">
  <?php if ($displayReview === true) { ?>
    <div id="nsc_bar_notice_please_rate" class="nsc_bar_notice_below_tabs">
      <table>
        <tr>
          <td>
            <img id="nsc_bar_notice_please_rate_image" width="150px"
              src="<?php echo NSC_BAR_PLUGIN_URL ?>/admin/img/rating-stars.png" />
          </td>
          <td>
            <p>Hi, I noticed you use this plugin for a while now. That's awesome! Could you please do me a big favor and
              give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.</p>
            <p target="_blank" class="nsc_bar_notice_links">
              <a id="nsc_bar_notice_link_one" target="_blank" class="button button-primary"
                href="https://wordpress.org/support/plugin/beautiful-and-responsive-cookie-consent/reviews/#new-post">Ok,
                you deserve it</a>
              <a id="nsc_bar_notice_reviewedAlready" class="nsc_bar_notice_link_two" href="#">I already did</a>
            </p>
            <p>If you think we do not deserve a 5-Star rating please contact us to give us a chance to improve, before
              rating.</p>
          </td>
        </tr>
      </table>
    </div>
  <?php }
  if ($newBannerEnabled === false) {
    ?>
    <table class="form-table nsc_bar_language">
      <tbody>
        <tr id="tr_content_language_setter">
          <th scope="row">Language</th>
          <td>
            <fieldset>
              <label><?php echo $form_fields->nsc_bar_get_language_dropdown() ?></label>
              <p class="description"><?php echo wp_kses($objSettings->addon_lang_description, $allowed_html) ?></p>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    <hr>
  <?php } ?>

  <?php echo "<div class='tab_description'>" . wp_kses($objSettings->setting_page_fields->tabs[$active_tab_index]->tab_description, $allowed_html) . "  </div>" ?>

  <form action="" method="post">

    <?php wp_nonce_field("save_cookie_settings_" . $objSettings->plugin_slug . "--" . $objSettings->setting_page_fields->tabs[$active_tab_index]->tab_slug, 'nsc_bar_nonce'); ?>
    <input type="hidden" name="action" value="nsc_bar_cookie_settings_save" />
    <input type="hidden" name="option_page"
      value="<?php echo $objSettings->plugin_slug . $objSettings->setting_page_fields->tabs[$active_tab_index]->tab_slug ?>" />
    <?php submit_button(); ?>

    <table class="form-table">
      <?php foreach ($objSettings->setting_page_fields->tabs[$active_tab_index]->tabfields as $field_configs) {
        if ($newBannerEnabled === true && isset($field_configs->newBanner) && $field_configs->newBanner === false) {
          continue;
        }

        if ($newBannerEnabled === false && isset($field_configs->newBanner) && $field_configs->newBanner === true) {
          continue;
        }
        ?>
        <tr id="tr_<?php echo esc_attr($field_configs->field_slug) ?>">
          <th scope="row">
            <?php echo esc_html($field_configs->name) ?>
          </th>
          <td>
            <fieldset>
              <?php echo $form_fields->nsc_bar_return_form_field($field_configs, $objSettings->plugin_prefix); ?>
              <?php
              if (!empty($field_configs->custom_component)) {
                $rest_url = get_rest_url();
                $nonce = wp_create_nonce('wp_rest');
                $string = str_replace("{{REST_URL_ENCODED}}", urlencode($rest_url), $field_configs->custom_component);
                echo str_replace("{{WP_NONCE}}", $nonce, $string);
              }
              ?>
              <p class="description"><?php echo wp_kses($field_configs->helpertext, $allowed_html) ?></p>
            </fieldset>
          </td>
        </tr>
      <?php } ?>
    </table>
  </form>
</span>
<?php require 'sidebar.php'; ?>