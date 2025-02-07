<?php

class nsc_bar_on_activation
{

    public function nsc_bar_do_update()
    {
        $db_version = $this->get_database_version();
        $updated = true;
        // if empty we know its version < 2.0. because then was the db version introduced.
        if (empty($db_version)) {
            $updated = $this->do_update_to_20();
        }

        if ($updated === true && NSC_BAR_PLUGIN_VERSION != $db_version) {
            $this->set_database_version();
        }
    }

    private function do_update_to_20()
    {
        $banner_configs = new nsc_bar_banner_configs;
        $config_array = $banner_configs->nsc_bar_get_banner_config_array();
        $save = false;
        if ($config_array["type"] == "info") {
            $banner_configs->nsc_bar_update_banner_setting("revokable", false, "string");
            $banner_configs->nsc_bar_remove_revokeBtn();
            $save = true;
        }

        if (isset($config_array["revokeBtn"])) {
            $banner_configs->nsc_bar_update_banner_setting("revokable", false, "string");
            $banner_configs->nsc_bar_remove_revokeBtn();
            $save = true;
        }

        if ($save) {
            return $banner_configs->nsc_bar_save_banner_settings();
        } else {
            return true;
        }

    }

    private function set_database_version()
    {
        update_option(NSC_BAR_SLUG_DBVERSION, NSC_BAR_PLUGIN_VERSION);
    }

    private function get_database_version()
    {
        return get_option(NSC_BAR_SLUG_DBVERSION, null);
    }
}
