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
            )
        );
    }

    public function nsc_bar_validate_field_custom_save($tabfield, $input)
    {

        if (isset($tabfield->disabled) && $tabfield->disabled === true) {
            return $tabfield->pre_selected_value;
        }

        // e.g. "nsc_bar_type[]=x" in a manipulated form
        if (is_array($input) || is_object($input)) {
            $this->admin_error_obj->nsc_bar_set_admin_error("A value could not be saved: the field \"" . esc_html($tabfield->name) . "\" expects a single value.");
            return null;
        }

        $extra_validation_value = $tabfield->extra_validation_name;
        // CSS strings are not HTML: preserve quotes, escapes and punctuation.
        // Their request slashes are removed at the form-save boundary.
        if ($extra_validation_value === 'nsc_bar_font_family') {
            $return = $input;
        } elseif ($extra_validation_value === 'nsc_bar_check_input_export_json_string') {
            // Validate decoded fields below; text cleanup would corrupt CSS
            // escapes, percent signs and whitespace within imported font names.
            $return = $input;
        } else {
            $return = $this->nsc_bar_sanitize_input($input, $extra_validation_value);
        }

        if ($this->nsc_bar_is_not_an_option($tabfield, $return)) {
            $this->admin_error_obj->nsc_bar_set_admin_error("The value of \"" . esc_html($tabfield->name) . "\" could not be saved. It is not one of the available options.");
            return null;
        }

        switch ($extra_validation_value) {
            case 'nsc_bar_font_family':
                $return = $this->nsc_bar_font_family($return);
                break;
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
            case "nsc_bar_cookie_name":
                $return = $this->nsc_bar_cookie_name($return);
                break;
            case "nsc_bar_cookie_domain":
                $return = $this->nsc_bar_cookie_domain($return);
                break;
            case "nsc_bar_datalayer_name":
                $return = $this->nsc_bar_datalayer_name($return);
                break;
            case "nsc_bar_cookie_expiry_days":
                $return = $this->nsc_bar_cookie_expiry_days($return);
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
            case "nsc_bar_text_number_only":
                $return = $this->nsc_bar_text_number_only($return);
                break;
            case "customConsentButtons":
                $return = $this->customConsentButtons($return);
                break;
        }
        $return = apply_filters('nsc_bar_filter_input_validation', $return, $extra_validation_value);
        return $return;
    }

    public function nsc_bar_sanitize_input($input, $validationRule = "")
    {
        $jsonRules = array("nsc_bar_check_valid_json_string", "nsc_bar_check_input_export_json_string", "nsc_bara_new_banner_config", "nsc_bara_validatateNewBannerSave");
        $jsonRules = apply_filters('nsc_bar_filter_json_rules', $jsonRules);

        // for backward compatibility. Checked before stripslashes(): that function
        // requires a string argument and throws a TypeError on anything else, e.g.
        // an array-shaped cookie such as "cookieconsent_status[]=x".
        if (is_string($input) === false) {
            return sanitize_text_field($input);
        }

        $cleandValue = $input;
        if (in_array($validationRule, $jsonRules) === false) {
            $cleandValue = stripslashes($input);
        }

        // customized. Got from WP function _sanitize_text_fields

        $cleandValue = wp_check_invalid_utf8($cleandValue);

        if (in_array($validationRule, $jsonRules) === false) {
            $cleandValue = wp_kses($cleandValue, $this->allowedHtml);
        }
        $cleandValue = preg_replace('/[\r\n\t ]+/', ' ', $cleandValue);
        $cleandValue = trim($cleandValue);

        // Urls and json (which contains urls) need their percent-encoded characters: "datenschutzerkl%C3%A4rung" is a different page than
        // "datenschutzerklrung". Everywhere else they are removed, like sanitize_text_field() does.
        $keepPercentEncoding = in_array($validationRule, $jsonRules) || $validationRule === "nsc_bar_link_input";
        if ($keepPercentEncoding) {
            return $cleandValue;
        }

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

    // selects and radio buttons only offer their selectable_values, so nothing else is a valid value
    private function nsc_bar_is_not_an_option($tabfield, $value)
    {
        if (!isset($tabfield->type) || !in_array($tabfield->type, array("select", "radio"), true)) {
            return false;
        }

        if (empty($tabfield->selectable_values) || !is_array($tabfield->selectable_values)) {
            return false;
        }

        $options = array();
        foreach ($tabfield->selectable_values as $option) {
            $options[] = (string) (is_object($option) ? $option->value : $option["value"]);
        }
        return !in_array((string) $value, $options, true);
    }

    public function nsc_bar_link_input($url)
    {
        if (!is_string($url)) {
            return null;
        }

        $url = trim($url);
        if ('' === $url) {
            return null;
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid url. Yours seems to contain invalid chars.");
            return null;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }

        // A scheme without host ("javascript:/**/alert(1)", "data:text/html,...") must not slip through the check below, which
        // only validates urls WITH a host. Only web addresses and mail links are links.
        $givenScheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : "";
        if ($givenScheme === "mailto") {
            if (preg_match('/^mailto:[^\s<>"\'`\\\\]+$/i', $url) !== 1) {
                $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid url. Yours seems to contain invalid chars.");
                return null;
            }
            return $url;
        }

        if ($givenScheme !== "" && !in_array($givenScheme, array('http', 'https'), true)) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid url. Yours seems to be missing http or https.");
            return null;
        }

        if (empty($parts['scheme']) || empty($parts['host'])) {
            $parts['scheme'] = "https";
            $parts['host'] = "test.com";
        }

        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, array('http', 'https'), true)) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid url. Yours seems to be missing http or https.");
            return null;
        }

        if (!function_exists('idn_to_ascii')) {
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid url.");
                return null;
            }
            return trim($url);
        }

        $host_ascii = idn_to_ascii($parts['host'], 0, INTL_IDNA_VARIANT_UTS46);
        if ($host_ascii === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid url. Yours seems to contain invalid chars.");
            return null;
        }

        $normalized_url = $scheme . '://' . $host_ascii;
        if (isset($parts['port'])) {
            $normalized_url .= ':' . $parts['port'];
        }

        if (isset($parts['path'])) {
            $normalized_url .= $this->encode_non_ascii($parts['path']);
        }

        if (isset($parts['query'])) {
            $normalized_url .= '?' . $this->encode_non_ascii($parts['query']);
        }

        if (isset($parts['fragment'])) {
            $normalized_url .= '#' . $this->encode_non_ascii($parts['fragment']);
        }
        if (filter_var($normalized_url, FILTER_VALIDATE_URL) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid url. Yours seems to contain invalid chars or something else went wrong.");
            return null;
        }
        return $url;
    }

    public function nsc_bar_text_number_only($input)
    {
        $forbidden = "/[^\w\-\.\ 0-9,%]/";
        $forbidden_chars = preg_match_all($forbidden, $input);

        if (empty($forbidden_chars) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Text could not be saved. Please provide only word and number characters in this field, space, - and . are allowed, too.");
            return null;
        }

        return $input;
    }

    public function nsc_bar_text_only($input)
    {
        $forbidden = "/[^\w\-\.\ ,]/";
        $forbidden_chars = preg_match_all($forbidden, $input);

        if (empty($forbidden_chars) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Text could not be saved. Please provide only word characters in this field, space, - and . are allowed, too.");
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
            $this->admin_error_obj->nsc_bar_set_admin_error("Number could not be saved. Please provide an integer. Your input: " . esc_html($input));
            return null;
        }
        return $input;
    }

    // PHP 8 throws a ValueError in setcookie() for names with "=,; \t\r\n\013\014", which would take down every page of the site.
    // The name is also used in a regular expression by the banner javascript, so only plain characters are allowed.
    public function nsc_bar_cookie_name($input)
    {
        if (!is_string($input) || preg_match('/^[A-Za-z0-9_\-\.]+$/', $input) !== 1) {
            $this->admin_error_obj->nsc_bar_set_admin_error("The cookie name could not be saved. Please use only letters, numbers, - . and _ (no spaces).");
            return null;
        }
        return $input;
    }

    // The banner scripts run window[name] = window[name] || [] and window[name].push(...). With "location" that assignment
    // navigates (endless reload), other existing globals throw. So only an identifier that is not a known global is allowed.
    public function nsc_bar_datalayer_name($input)
    {
        if (!self::nsc_bar_is_valid_datalayer_name($input)) {
            $this->admin_error_obj->nsc_bar_set_admin_error("The dataLayer name could not be saved. Please use a JavaScript variable name like dataLayer (letters, numbers, _ and \$, no spaces), which is not a built-in browser object like location or document.");
            return null;
        }
        return $input;
    }

    public static function nsc_bar_is_valid_datalayer_name($input)
    {
        $reserved = array(
            "location", "document", "window", "self", "top", "parent", "frames", "opener", "name", "status", "length", "closed",
            "origin", "history", "navigator", "screen", "globalThis", "localStorage", "sessionStorage", "console", "performance",
            "crypto", "customElements", "__proto__", "constructor", "prototype", "Object", "Array", "Function", "JSON", "Math",
            "undefined", "NaN", "Infinity", "eval", "cookieconsent",
        );
        return is_string($input) && preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $input) === 1 && !in_array($input, $reserved, true);
    }

    // empty is allowed (= current domain). Same reason as for the cookie name: setcookie() throws for spaces, "," and ";".
    public function nsc_bar_cookie_domain($input)
    {
        if ($input === "") {
            return $input;
        }

        if (!is_string($input) || preg_match('/^\.?[A-Za-z0-9\-]+(\.[A-Za-z0-9\-]+)*$/', $input) !== 1) {
            $this->admin_error_obj->nsc_bar_set_admin_error("The cookie domain could not be saved. Please provide only a domain like example.com or .example.com (no protocol, path or spaces), or leave it empty. Use punycode (xn--) for domains with special characters.");
            return null;
        }
        return $input;
    }

    // 60*60*24*"abc" throws a TypeError in PHP 8.
    public function nsc_bar_cookie_expiry_days($input)
    {
        if (!is_string($input) && !is_int($input)) {
            $this->admin_error_obj->nsc_bar_set_admin_error("The cookie expiry could not be saved. Please provide a number of days.");
            return null;
        }

        if (preg_match('/^[1-9][0-9]{0,4}$/', (string) $input) !== 1 || (int) $input > 36500) {
            $this->admin_error_obj->nsc_bar_set_admin_error("The cookie expiry could not be saved. Please provide a whole number of days between 1 and 36500.");
            return null;
        }
        return (string) $input;
    }

    public function nsc_bar_check_input_color_code($input)
    {
        $forbidden = "/[^\w^,^\.^ ^%^(^)^#]/";
        $forbidden_chars = preg_match_all($forbidden, $input);
        if (empty($forbidden_chars) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide valid color value for the color field, like #ffffff or rgba(100,100,100,0.9)");
            return null;
        }
        return $input;
    }

    public function nsc_bar_replace_doublequote_with_single($input)
    {
        return str_replace(['"', "\""], "'", $input);
    }

    /**
     * Accept font-family syntax, not a list of known fonts.
     *
     * Names may be quoted strings or CSS identifiers, with comma-separated
     * fallbacks and var() references. Declarations, rules and other functions
     * are not font names. CSS escapes remain escapes; decoding them and then
     * concatenating the result into a stylesheet would reintroduce injection.
     */
    public function nsc_bar_font_family($input)
    {
        $grammar = <<<'CSS'
~\A (?&ws) (?:(?&list))? (?&ws) \z
(?(DEFINE)
    (?<ws> [\x20\t\r\n\f]* )
    (?<escape> \\ (?: [0-9a-fA-F]{1,6} (?:\r\n|[\x20\t\r\n\f])? | [^\r\n\f0-9a-fA-F\x00] ) )
    (?<start> [_a-zA-Z] | [^\x00-\x7F] | (?&escape) )
    (?<namechar> [_a-zA-Z0-9-] | [^\x00-\x7F] | (?&escape) )
    (?<ident> (?: -- | -? (?&start) ) (?&namechar)* )
    (?<string>
        " (?: [^"\\\r\n\f\x00] | (?&escape) | \\ (?:\r\n|[\r\n\f]) )* "
      | ' (?: [^'\\\r\n\f\x00] | (?&escape) | \\ (?:\r\n|[\r\n\f]) )* '
    )
    (?<variable> var\( (?&ws) -- (?&namechar)+ (?&ws) (?: , (?&ws) (?:(?&list))? (?&ws) )? \) )
    (?<family> (?&string) | (?&variable) | (?&ident) (?: [\x20\t\r\n\f]+ (?&ident) )* )
    (?<list> (?&family) (?: (?&ws) , (?&ws) (?&family) )* )
)
~ux
CSS;

        if (!is_string($input) || preg_match($grammar, $input) !== 1) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a font name or comma-separated font-family list. Quote names containing punctuation; CSS declarations and rules are not allowed.");
            return null;
        }

        // HTML recognizes </style> even inside a CSS string. Escape literal '<'
        // as CSS, including an already backslash-escaped '<', without changing
        // the font's name. Consume other escapes intact (notably escaped '\\').
        return preg_replace_callback(
            '~\\\\(?:[0-9a-fA-F]{1,6}(?:\r\n|[ \t\r\n\f])?|[^\r\n\f0-9a-fA-F])|<~u',
            function ($match) {
                return $match[0] === '<' || $match[0] === '\\<' ? '\\3c ' : $match[0];
            },
            $input
        );
    }

    public function nsc_bar_check_valid_json_string($json_string)
    {
        if (is_numeric($json_string) === true || $json_string === true) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid json string. Seems you provided booleanlike data. Data was not saved.");
            return null;
        }

        if (is_string($json_string) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid json STRING. Your provided a non-string. Data was not saved.");
            return null;
        }

        $tested_json_string = json_encode(json_decode($json_string), JSON_UNESCAPED_UNICODE);

        if (empty($tested_json_string) || $tested_json_string === "null") {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid json string. Data was not saved.");
            return null;
        }

        return $json_string;
    }

    public function customConsentButtons($input)
    {
        if (is_string($input) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a string for the order of buttons. Data was not saved.");
            return null;
        }

        $expectedEmptyString = str_replace(array("{{deny}}", "{{savesettings}}", "{{allowall}}", " ", ",", ";"), "", $input);
        if (empty($expectedEmptyString) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide valid configuration for this field. Only {{deny}}, {{savesettings}} and {{allowall}} are allowed.");
            return null;
        }
        return str_replace(array(" ", ",", ";"), "", $input);
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
            return null;
        }

        if (!is_array(json_decode($valid))) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Cookie types must be a JSON array.");
            return null;
        }

        $arr_cookietypes = json_decode($valid, true);
        if (!is_array($arr_cookietypes) || $arr_cookietypes !== array_values($arr_cookietypes)) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Cookie types must be a list of cookie types.");
            return null;
        }

        foreach ($arr_cookietypes as $arr_cookietype) {
            if (!is_array($arr_cookietype) || !isset($arr_cookietype['label'], $arr_cookietype['cookie_suffix']) ||
                !is_string($arr_cookietype['label']) || !is_string($arr_cookietype['cookie_suffix'])) {
                $this->admin_error_obj->nsc_bar_set_admin_error("Each cookie type must have a text label and cookie suffix.");
                return null;
            }
            foreach (array('checked', 'disabled') as $flag) {
                if (!isset($arr_cookietype[$flag]) || !in_array($arr_cookietype[$flag], array('', $flag), true)) {
                    $this->admin_error_obj->nsc_bar_set_admin_error("Cookie type flags must be empty or their matching checked/disabled keyword.");
                    return null;
                }
            }
            if (preg_match('/^[a-z_]+$/D', $arr_cookietype["cookie_suffix"]) !== 1) {
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
            return null;
        }

        $settings = json_decode($input);
        if (!is_object($settings) || !isset($settings->cookietypes) || !is_array($settings->cookietypes)) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a configuration object with a cookie types array.");
            return null;
        }
        if (property_exists($settings, 'customizedFont') && $this->nsc_bar_font_family($settings->customizedFont) === null) {
            return null;
        }
        $valid = $this->nsc_bar_check_cookietypes(json_encode($settings->cookietypes, JSON_UNESCAPED_UNICODE));

        if (empty($valid)) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid json string with valid cookie types.");
            return null;
        }
        return $input;
    }

    public function esc_array_for_js(array $array_to_escape)
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

    public function escape_json_content(string $json_string)
    {
        $decoded_json = json_decode($json_string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        // Old settings may predate flag validation. Never emit attribute fragments,
        // including when configuration is supplied through a frontend filter.
        if (isset($decoded_json['cookietypes']) && is_array($decoded_json['cookietypes'])) {
            foreach ($decoded_json['cookietypes'] as &$cookietype) {
                if (!is_array($cookietype)) {
                    continue;
                }
                foreach (array('checked', 'disabled') as $flag) {
                    $cookietype[$flag] = isset($cookietype[$flag]) && $cookietype[$flag] === $flag ? $flag : '';
                }
            }
            unset($cookietype);
        }

        $escape_recursive = function ($data, $allowedHtml) use (&$escape_recursive)
        {
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    if ($key === "message" && is_string($value) === true) {
                        $data[$key] = wp_kses($value, $allowedHtml);
                        continue;
                    }
                    $data[$key] = $escape_recursive($value, $allowedHtml);
                }
            } elseif (is_string($data)) {
                $data = stripslashes(esc_js($data));
            }
            return $data;
        };

        $escaped_json = $escape_recursive($decoded_json, $this->allowedHtml);

        return json_encode($escaped_json, JSON_UNESCAPED_UNICODE);
    }

    public function return_errors_obj()
    {
        return $this->admin_error_obj;
    }

    public function nsc_bar_validate_addon()
    {
        if (defined('NSC_BARA_UPDATE_TRANSIENT_NAME') === false) {
            return;
        }

        if (defined('NSC_BARA_PLUGIN_VERSION') === false) {
            return;
        }


        $updateInfos = "";
        if (stripos(NSC_BARA_UPDATE_TRANSIENT_NAME, NSC_BARA_PLUGIN_VERSION) !== false) {
            $updateInfos = get_transient(NSC_BARA_UPDATE_TRANSIENT_NAME);
        }

        if (!empty($updateInfos) && is_object($updateInfos) && empty($updateInfos->new_version) === false && version_compare(NSC_BARA_PLUGIN_VERSION, "3.8.0", '<=')) {
            $slug = "nsc_bar_version_too_outdated_warning_dismiss";
            $version = 1;
            $message = "The Beautiful Cookie Banner Addon is activated but your version " . esc_html(NSC_BARA_PLUGIN_VERSION) . " is very much outdated. Please update to the latest version " . esc_html($updateInfos->new_version) . ". Without updates, your WordPress site may become vulnerable to security risks and the banner might stop working.";
        }

        if (!empty($updateInfos) && is_object($updateInfos) && empty($updateInfos->update_message) === false) {
            $slug = "nsc_bar_no_valid_license_key_warning_dismiss";
            $version = 1;
            $message = "The Beautiful Cookie Banner Addon is activated but there seems to be a problem with your license key. Please double check your <a href=\"/wp-admin/options-general.php?page=nsc_bar-cookie-consent&tab=license&nsc_bara_language_selector=xx\">license key</a> or remove the addon. Without updates, your WordPress site may become vulnerable to security risks and the banner might stop working in the future.";
        }

        if (empty(get_option("nsc_bar_license_key", "")) === true) {
            $slug = "nsc_bar_no_license_key_warning_dismiss";
            $version = 1;
            $message = "The Beautiful Cookie Banner Addon is activated but the license key is missing. Please enter a valid <a href=\"/wp-admin/options-general.php?page=nsc_bar-cookie-consent&tab=license&nsc_bara_language_selector=xx\">license key</a> to receive updates or remove the addon. Without updates, your WordPress site may become vulnerable to security risks and the banner might stop working in the future.";
        }

        if (!empty($updateInfos) && is_object($updateInfos) && empty($updateInfos->global_wp_message) === false) {
            $slug = "nsc_bar_global_remote_warning_dismiss";
            $version = md5($updateInfos->global_wp_message);
            $message = $updateInfos->global_wp_message;
        }

        if (empty($message)) {
            return;
        }

        if (empty($slug)) {
            return;
        }

        if (empty($version)) {
            return;
        }


        $admin_error = new nsc_bar_admin_error;
        $admin_error->nsc_bar_set_global_warning_message($message, $slug, $version);
        add_action('admin_notices', array($admin_error, 'nsc_bar_global_admin_warning'));
        add_action('network_admin_notices', array($admin_error, 'nsc_bar_global_admin_warning'));
        add_action('admin_post_' . $slug, array($admin_error, 'nsc_bar_handle_dismiss'));
    }

    private function encode_non_ascii($string)
    {
        return preg_replace_callback(
            '/[^\x00-\x7F]/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $string
        );
    }
}
