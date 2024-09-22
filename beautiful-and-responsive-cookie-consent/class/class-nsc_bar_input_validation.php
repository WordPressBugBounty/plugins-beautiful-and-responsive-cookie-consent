<?php
if (!defined('ABSPATH')) {
    exit;
}

class nsc_bar_input_validation
{
    private $admin_error_obj;
    private $allowedHtml;

    public function __construct()
    {
        $this->admin_error_obj = new nsc_bar_admin_error;
        $this->allowedHtml = array(
            "strong" => array(),
            "i" => array(),
            "a" => array(
                "href" => array(),
                "id" => array(),
                "title" => array(),
                "target" => array(),
                "class" => array(),
            ),
            "div" => array(
                "class" => array(),
                "id" => array(),
            ),
            "span" => array(
                "class" => array(),
                "id" => array(),
            ),
            "p" => array(
                "class" => array(),
                "id" => array(),
            ),
            "br" => array(),
            "ul" => array(
                "class" => array(),
                "id" => array(),
            ),
            "ol" => array(
                "class" => array(),
                "id" => array(),
            ),
            "li" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h1" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h2" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h3" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h4" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h5" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h6" => array(
                "class" => array(),
                "id" => array(),
            ),
            "hr" => array(
                "class" => array(),
                "id" => array(),
            ));
    }

    public function nsc_bar_validate_field_custom_save($tabfield, $input)
    {

        if (isset($tabfield->disabled) && $tabfield->disabled === true) {
            return $tabfield->pre_selected_value;
        }

        $return = $this->nsc_bar_sanitize_input($input);
        $extra_validation_value = $tabfield->extra_validation_name;

        switch ($extra_validation_value) {
            case "nsc_bar_check_input_color_code":
                $return = $this->nsc_bar_check_input_color_code($return);
                break;
            case "nsc_bar_check_input_export_json_string":
                $return = $this->nsc_bar_check_input_export_json_string($return);
                break;
            case "nsc_bar_check_valid_json_string":
                $return = $this->nsc_bar_check_valid_json_string($return);
                break;
            case "nsc_bar_check_cookietypes":
                $return = $this->nsc_bar_check_cookietypes($return);
                break;
            case "nsc_bar_replace_doublequote_with_single":
                $return = $this->nsc_bar_replace_doublequote_with_single($return);
                break;
            case "nsc_bar_integer":
                $return = $this->nsc_bar_integer($return);
                break;
            case "nsc_bara_custom_services":
                $return = $this->nsc_bar_bara_custom_services($return);
                break;
            case "nsc_bar_link_input":
                $return = $this->nsc_bar_link_input($return);
                break;
            case "nsc_bar_text_only":
                $return = $this->nsc_bar_text_only($return);
                break;
        }
        $return = apply_filters('nsc_bar_filter_input_validation', $return, $extra_validation_value);
        return $return;
    }

    public function nsc_bar_sanitize_input($input)
    {
        $cleandValue = stripslashes($input);

        // for backward compatibility
        if (getType($cleandValue) !== "string") {
            return sanitize_text_field($cleandValue);
        }

        // customized. Got from WP function _sanitize_text_fields

        $cleandValue = wp_check_invalid_utf8($cleandValue);
        $cleandValue = wp_kses($cleandValue, $this->allowedHtml);
        $cleandValue = preg_replace('/[\r\n\t ]+/', ' ', $cleandValue);
        $cleandValue = trim($cleandValue);

        // Remove percent-encoded characters.
        $found = false;
        while (preg_match('/%[a-f0-9]{2}/i', $cleandValue, $match)) {
            $cleandValue = str_replace($match[0], '', $cleandValue);
            $found = true;
        }

        if ($found) {
            // Strip out the whitespace that may now exist after removing percent-encoded characters.
            $cleandValue = trim(preg_replace('/ +/', ' ', $cleandValue));
        }

        return $cleandValue;
    }

    public function nsc_bar_link_input($input)
    {
        $urlToTest = trim($input);
        if (stripos($urlToTest, 'http') === false) {
            $urlToTest = "https://d.com/" . ltrim($input, "/");
        }

        if (filter_var($urlToTest, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return trim($input);
    }

    public function nsc_bar_text_only($input)
    {
        $forbidden = "/[^\w\-\.\ ]/";
        $forbidden_chars = preg_match_all($forbidden, $input);

        if (empty($forbidden_chars) === false) {
            return null;
        }

        return $input;
    }

    public function nsc_bar_bara_custom_services($input)
    {

        $testedJson = $this->nsc_bar_check_valid_json_string($input);
        if (empty($testedJson)) {
            return null;
        }

        if (class_exists("nsc_bara_input_validation")) {
            $bara_validation = new nsc_bara_input_validation;
            return $bara_validation->nsc_bara_custom_services($testedJson);
        }
        return null;
    }

    public function nsc_bar_integer($input)
    {
        $valid = preg_match("/^[0-9]*$/", $input);
        if (empty($valid) && $input != "") {
            $this->admin_error_obj->nsc_bar_set_admin_error("Number could not be saved. Please provide an integer. Your input: " . $input);
            $input = null;
        }
        $this->admin_error_obj->nsc_bar_display_errors();
        return $input;
    }

    public function nsc_bar_check_input_color_code($input)
    {
        $forbidden = "/[^\w^,^\.^ ^%^(^)^#]/";
        $forbidden_chars = preg_match_all($forbidden, $input);
        if (empty($forbidden_chars) === false) {
            return null;
        }
        return $input;
    }

    public function nsc_bar_replace_doublequote_with_single($input)
    {
        return str_replace(['"', "\""], "'", $input);
    }

    public function nsc_bar_check_valid_json_string($json_string)
    {
        if (is_numeric($json_string) === true || $json_string === true) {
            return null;
        }

        $tested_json_string = json_encode(json_decode($json_string), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        if (empty($tested_json_string) || $tested_json_string == "null") {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid json string. Data was not saved.");
            return null;
        }

        return $tested_json_string;
    }

    public function nsc_bar_check_cookietypes($input)
    {
        //should be an impossible case, because default settings have cookie types and the frontend js makes it impossible to delete all cookie types.
        if (empty($input)) {
            //$this->admin_error_obj->nsc_bar_set_admin_error("Please provide at least one cookie type.");
            //$this->admin_error_obj->nsc_bar_display_errors();
            //TODO: if all installation are >= v2.0 change this line to "return null" and uncomment lines above.
            $input = '[{"label": "Technical","checked": "checked","disabled":"disabled","cookie_suffix":"tech"}]';
        }

        $valid = $this->nsc_bar_check_valid_json_string($input);
        if (empty($valid)) {
            $this->admin_error_obj->nsc_bar_display_errors();
            return null;
        }

        $arr_cookietypes = json_decode($valid, true);
        foreach ($arr_cookietypes as $arr_cookietype) {
            if (preg_match('/^[a-z_]+$/', $arr_cookietype["cookie_suffix"]) === 0) {
                $this->admin_error_obj->nsc_bar_set_admin_error("Cookie suffix must be only lowercase letter and underscores.");
                return null;
            }
            if (strlen($arr_cookietype["cookie_suffix"]) > 10) {
                $this->admin_error_obj->nsc_bar_set_admin_error("Cookie suffix must only have ten characters.");
                return null;
            }
        }
        return $valid;
    }

    public function nsc_bar_check_input_export_json_string($input)
    {
        if ($input === "") {
            return "";
        }

        $valid = $this->nsc_bar_check_valid_json_string($input);
        if (empty($valid)) {
            $this->admin_error_obj->nsc_bar_display_errors();
            return null;
        }

        $settings = json_decode($input);
        $valid = $this->nsc_bar_check_cookietypes(json_encode($settings->cookietypes, JSON_UNESCAPED_UNICODE));

        if (empty($valid)) {
            $this->admin_error_obj->nsc_bar_display_errors();
            return null;
        }
        return $input;
    }

    public function esc_array_for_js($array_to_escape)
    {
        $escapedArray = array();
        foreach ($array_to_escape as $key => $value) {
            $escKey = esc_js($key);
            if (!is_array($value)) {
                $escValue = esc_js($value);
                $escapedArray[$escKey] = $escValue;
            }

            if (is_array($value)) {
                foreach ($value as $key_of_v => $value_of_v) {
                    $escKey_v = esc_js($key_of_v);
                    $escValue_v = esc_js($value_of_v);
                    $escapedArray[$escKey][$escKey_v] = $escValue_v;
                }
            }
        }
        return $escapedArray;
    }

    public function return_errors_obj()
    {
        return $this->admin_error_obj;
    }

}
