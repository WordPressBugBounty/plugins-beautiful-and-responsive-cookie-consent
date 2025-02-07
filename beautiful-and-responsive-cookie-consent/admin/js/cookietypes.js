document.addEventListener("DOMContentLoaded", function(event) {
  var table_config = {
    label: {
      type: "text",
      headline: "Cookie Type Name",
      maxlength: "100",
      validateRegex: "",
      validateErrorMessage: ""
    },
    checked: {
      type: "checkbox",
      headline: "default checked",
      validateRegex: "",
      validateErrorMessage: ""
    },
    disabled: {
      type: "checkbox",
      headline: "Is disabled",
      validateRegex: "",
      validateErrorMessage: ""
    },
    cookie_suffix: {
      type: "text",
      headline: "Cookie Suffix",
      maxlength: "10",
      validateRegex: /^[a-z_]+$/,
      validateErrorMessage: "Only lowercase letters and underscore allowed"
    },
    delete_icon: {
      type: "html",
      element: "span",
      class: ["dashicons", "dashicons-no-alt"],
      headline: "",
      value: "",
      clickHandler: delete_row
    }
  };

  //getting values
  try {
    var cookietypes_json = JSON.parse(
      document.getElementById("ff_nsc_bar_cookietypes").value
    );
  } catch (err) {
    cookietypes_json = [];
  }

  if (!Array.isArray(cookietypes_json) || cookietypes_json.length <= 0) {
    cookietypes_json = [];
  }

  //creating table
  add_table_to_dom(generateTable());

  //methods

  function generateTable() {
    let table = document.createElement("table");

    table.classList.add("nsc_bar_cookietypes_table");
    table.id = "nsc_bar_cookietypes_table";

    table = generateTableHead(table);

    let tbody = table.createTBody();

    cookietypes_json.forEach(function(cookietype_fields) {
      let row = tbody.insertRow();
      Object.keys(cookietype_fields).forEach(function(field_key) {
        let cell = row.insertCell();
        let input_field = create_form(cookietype_fields[field_key], field_key);
        cell.setAttribute("data-colname", input_field.placeholder);
        cell.appendChild(input_field);
      });
      let cell = row.insertCell();
      let delete_icon = create_form("", "delete_icon");
      cell.appendChild(delete_icon);
    });

    return table;
  }

  function generateTableHead(table) {
    let thead = table.createTHead();
    let row = thead.insertRow();
    Object.keys(table_config).forEach(function(field_key) {
      let th = document.createElement("th");
      let text = document.createTextNode(table_config[field_key].headline);
      th.appendChild(text);
      row.appendChild(th);
    });
    return table;
  }

  function create_form(value, field_key) {
    switch (table_config[field_key].type) {
      case "text":
        input_field = create_textfield(create_input_field(value, field_key));
        break;
      case "checkbox":
        input_field = create_checkbox(create_input_field(value, field_key));
        break;
      case "html":
        input_field = create_html(value, field_key);
        break;
    }

    return input_field;
  }

  function onchange_handler(event) {
    field_key = event.target.getAttribute("data-field_key");
    if (validate_field(field_key, event.target.value) == false) {
      var error_message = document.createElement("label");
      error_message.classList.add("nsc_bar_inline_error");
      event.target.classList.add("nsc_bar_inline_error");
      error_message.innerText = table_config[field_key].validateErrorMessage;
      event.target.parentNode.appendChild(error_message);
    } else {
      try {
        var el = event.target.parentNode.getElementsByTagName("label")[0];
        el.parentNode.removeChild(el);
        event.target.classList.remove("nsc_bar_inline_error");
      } catch (er) {}
    }
    build_json_file();
  }

  function create_input_field(value, field_key) {
    var input_field = document.createElement("input");
    input_field.name = "nsc_bar_" + field_key;
    input_field.value = value;
    input_field.classList.add("nsc_bar_" + field_key);
    input_field.setAttribute("data-field_key", field_key);
    field_config = table_config[input_field.getAttribute("data-field_key")];
    input_field.placeholder = field_config.headline;
    input_field.onchange = onchange_handler;
    return input_field;
  }

  function create_textfield(input_field) {
    input_field.setAttribute("type", "text");
    field_config = table_config[input_field.getAttribute("data-field_key")];
    input_field.maxLength = field_config.maxlength;
    return input_field;
  }

  function create_checkbox(input_field) {
    input_field.setAttribute("type", "checkbox");
    switch (input_field.value) {
      case "checked":
        input_field.checked = true;
        break;
      case "disabled":
        input_field.checked = true;
        break;
    }

    return input_field;
  }

  function create_html(value, field_key) {
    html_element = document.createElement(table_config[field_key].element);
    table_config[field_key].class.forEach(function(oneclass) {
      html_element.classList.add(oneclass);
    });
    html_element.onclick = table_config[field_key].clickHandler;
    return html_element;
  }

  function create_add_link() {
    var link = document.createElement("a");
    link.classList.add("nsc_bar_cookietypes_add_new_link");
    link.id = "nsc_bar_cookietypes_add_new_link";
    link.text = "add new cookie type";
    link.onclick = add_empty_row;
    return link;
  }

  function add_empty_row() {
    var tbody = document.querySelector("#nsc_bar_cookietypes_table > tbody");
    var row = tbody.insertRow();
    Object.keys(table_config).forEach(function(field_key) {
      let cell = row.insertCell();
      let input_field = create_form("", field_key);
      cell.appendChild(input_field);
    });
  }

  function delete_row(event) {
    var td = event.target.parentNode;
    var tr = td.parentNode; // the row to be removed
    tr.parentNode.removeChild(tr);
    build_json_file();
  }

  function add_table_to_dom(table) {
    try {
      var textar = document.querySelector("#ff_nsc_bar_cookietypes");
    } catch (er) {}
    if (textar) {
      textar.parentNode.appendChild(table);
      textar.parentNode.appendChild(create_add_link());
    }
  }

  function build_json_file() {
    //creating the highest level of json.
    var new_json = [];

    var cookietype_rows = document.querySelectorAll(
      "#nsc_bar_cookietypes_table > tbody > tr"
    );

    for (var i = 0; i < cookietype_rows.length; i++) {
      var skip_row = false;
      var row = cookietype_rows[i];
      var formfields = row.getElementsByTagName("input");

      var cookietype_fields = {};
      for (var r = 0; r < formfields.length; r++) {
        var field_key = formfields[r].getAttribute("data-field_key");
        if (
          formfields[r].type != "checkbox" &&
          (!formfields[r].value || formfields[r].value == "")
        ) {
          skip_row = true;
          continue;
        }

        switch (formfields[r].type) {
          case "checkbox":
            cookietype_fields[field_key] = get_checkbox_value(
              formfields[r],
              field_key
            );
            break;
          default:
            cookietype_fields[field_key] = formfields[r].value;
        }
      }

      if (skip_row) {
        continue;
      }
      new_json.push(cookietype_fields);
    }
    add_new_json(new_json);
  }

  function get_checkbox_value(formfield, field_key) {
    if (formfield.checked == true) {
      return field_key;
    }
    return "";
  }

  function validate_field(field_key, value) {
    var regex = table_config[field_key].validateRegex;

    if (!regex) {
      return true;
    }
    return regex.test(value);
  }

  function add_new_json(new_json) {
    if (!Array.isArray(new_json) || new_json.length <= 0) {
      return false;
    }

    document.getElementById("ff_nsc_bar_cookietypes").value = JSON.stringify(
      new_json
    );
    //console.log("new json", new_json);
  }
});

document.addEventListener("DOMContentLoaded", function(event) {
  var selector = "[name='nsc_bar_type']";
  nsc_bar_setVisibility_cookie_settings_text();
  nsc_bar_set_element_visibility();
  var nsc_bar_selector_nodes = document.querySelectorAll(selector);

  if (nsc_bar_selector_nodes) {
    for (var i = 0; i < nsc_bar_selector_nodes.length; i++) {
      nsc_bar_selector_nodes[i].addEventListener("change", function() {
        nsc_bar_set_element_visibility();
      });
    }
  }

  var nsc_bar_revokable = document.querySelector("[name='nsc_bar_revokable']");
  if (nsc_bar_revokable) {
    nsc_bar_revokable.addEventListener("click", function() {
      nsc_bar_setVisibility_cookie_settings_text();
    });
  }
});

function nsc_bar_set_element_visibility() {
  var selector = "[name='nsc_bar_type']";
  if (!document.querySelector(selector)) {
    return false;
  }
  var selector_value = document.querySelector(selector).value;
  if (selector_value && selector_value == "detailed") {
    document.getElementById("tr_setDiffDefaultCookiesFirstPV").hidden = false;
    document.getElementById("tr_cookietypes").hidden = false;
  } else {
    document.getElementById("tr_setDiffDefaultCookiesFirstPV").hidden = true;
    document.getElementById("tr_cookietypes").hidden = true;
  }
}

function nsc_bar_setVisibility_cookie_settings_text() {
  var nsc_bar_activate_cookie_settings_tab;
  if (document.querySelector("[name='nsc_bar_revokable']")) {
    nsc_bar_activate_cookie_settings_tab = document.querySelector(
      "[name='nsc_bar_revokable']"
    );
  }
  if (
    nsc_bar_activate_cookie_settings_tab &&
    nsc_bar_activate_cookie_settings_tab.checked
  ) {
    document.getElementById("tr_content_policy").hidden = false;
  } else if (nsc_bar_activate_cookie_settings_tab) {
    document.getElementById("tr_content_policy").hidden = true;
  }
}
