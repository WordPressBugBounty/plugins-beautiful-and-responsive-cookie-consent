<?php
if (!defined('ABSPATH')) {
    exit;
}

class nsc_bar_html_formfields
{
    private $field;
    private $prefix;
    private $escFieldId;
    private $escFieldName;

    public function nsc_bar_return_form_field($field, $prefix)
    {
        $this->field = $field;
        $this->prefix = $prefix;
        $this->escFieldId = "ff_" . esc_attr($this->prefix . $this->field->field_slug);
        $this->escFieldName = esc_attr($this->prefix . $this->field->field_slug);

        switch ($this->field->type) {
            case "checkbox":
                return $this->create_checkbox();
                break;
            case "textarea":
                return $this->create_textarea();
                break;
            case "text":
                return $this->create_text();
                break;
            case "longtext":
                return $this->create_text("long");
                break;
            case "extralongtext":
                return $this->create_text("extralong");
                break;
            case "select":
                return $this->create_select();
                break;
            case "radio":
                return $this->create_radio();
                break;
            case "hidden":
                return $this->create_hidden_field();
                break;
            case "masked":
                return $this->create_masked_field();
                break;
            default:
                return esc_attr($this->field->pre_selected_value);
                break;
        }
    }

    public function nsc_bar_get_language_dropdown()
    {
        if (class_exists("nsc_bara_html_formfields_addon") === true) {
            $form_fields_addon = new nsc_bara_html_formfields_addon();
            return $form_fields_addon->nsc_bara_get_language_dropdown();
        }
        return '<select name="nsc_bar_language_selector" id="nsc_bar_countries_select"><option value="xx">Default</option></select>';
    }

    private function create_checkbox()
    {
        $checkbox = '<input ' . $this->nsc_bar_is_disabled($this->field) . ' type="checkbox" name="' . $this->escFieldName . '" id="' . $this->escFieldId . '" value="1" ' . checked(1, $this->field->pre_selected_value, false) . '>';
        if (empty($this->nsc_bar_is_disabled($this->field)) === true) {
            $checkbox = '<input type="hidden" name="' . $this->escFieldName . '_hidden" value="0"/>' . $checkbox;
        }
        return '<label>' . $checkbox . '</label>';
    }

    private function create_textarea()
    {
        return '<label><textarea ' . esc_attr($this->nsc_bar_is_disabled($this->field)) . ' cols="120"  id="' . $this->escFieldId . '" name="' . $this->escFieldName . '" rows="20" class="large-text code" type="textarea">' . esc_textarea($this->convert_to_string($this->field->pre_selected_value)) . '</textarea></label>';
    }

    private function create_hidden_field()
    {
        return "<input type='hidden'  id='" . $this->escFieldId . "' name='" . $this->escFieldName . "_hidden' value='" . esc_attr($this->convert_to_string($this->field->pre_selected_value)) . "'/>";
    }

    private function create_text($length = "short")
    {
        $size = 20;
        $maxLength = 200;
        if ($length == "long") {
            $size = 50;
        }

        if ($length == "extralong") {
            $maxLength = 5000;
            $size = 50;
        }
        return '<label><input ' . $this->nsc_bar_is_disabled($this->field) . ' type="text"  id="' . $this->escFieldId . '" name="' . $this->escFieldName . '" size="' . $size . '" maxlength="' . $maxLength . '" value="' . esc_attr($this->field->pre_selected_value) . '"></label>';
    }

    private function create_masked_field()
    {
        $size = 50;
        $maxLength = 200;
        $showLast = 5;
        $maskedValue = "****-****-*****-" . substr($this->field->pre_selected_value, -5);
        return '<label><input ' . $this->nsc_bar_is_disabled($this->field) . ' type="text"  id="' . $this->escFieldId . '" name="' . $this->escFieldName . '" size="' . $size . '" maxlength="' . $maxLength . '" value="' . esc_attr($maskedValue) . '"></label>';
    }

    private function create_select()
    {

        $html = '<select ' . $this->nsc_bar_is_disabled($this->field) . ' name="' . $this->escFieldName . '" id="' . $this->escFieldId . '">';
        foreach ($this->field->selectable_values as $selectable_value) {
            $select = "";
            if ($selectable_value->value == $this->field->pre_selected_value) {$select = "selected";}
            $html .= '<option value="' . esc_attr($selectable_value->value) . '" ' . $select . '>' . esc_html($selectable_value->name) . '</option>';
        }
        $html .= "</select>";
        return '<label>' . $html . '</label>';
    }

    private function create_radio()
    {
        $html = "";
        foreach ($this->field->selectable_values as $selectable_value) {
            $select = "";
            if ($selectable_value->value == $this->field->pre_selected_value) {$select = "checked";}
            $html .= '<input ' . $this->nsc_bar_is_disabled($this->field) . ' id="' . $this->escFieldId . '"  type="radio" name="' . $this->escFieldName . '" value="' . esc_attr($selectable_value->value) . '" ' . $select . ' > ' . esc_html($selectable_value->name) . ' ';
        }
        return '<label>' . $html . '</label>';
    }

    private function convert_to_string($input)
    {
        if (!is_string($input)) {
            return json_encode($input, JSON_UNESCAPED_UNICODE);
        }
        return $input;
    }

    private function nsc_bar_is_disabled($field)
    {
        if (isset($field->disabled) && $field->disabled === true) {
            return "disabled";
        }

        if (class_exists("nsc_bara_html_formfields_addon") !== true) {
            return "";
        }

        $form_fields_addon = new nsc_bara_html_formfields_addon();
        return $form_fields_addon->nsc_bara_is_disabled($field);
    }

}
