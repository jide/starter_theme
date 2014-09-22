<?php

/**
 * @file
 * template.php
 */

/**
 * Override theme_select_as_checkboxes().
 */
function avant_garde_theme_select_as_checkboxes($vars) {
  $element = $vars['element'];
  if (!empty($element['#bef_nested'])) {
    if (empty($element['#attributes']['class'])) {
      $element['#attributes']['class'] = array();
    }
    $element['#attributes']['class'][] = 'form-checkboxes';
    return theme('select_as_tree', array('element' => $element));
  }

  // The selected keys from #options.
  $selected_options = empty($element['#value']) ? $element['#default_value'] : $element['#value'];
  if (!is_array($selected_options)) {
    $selected_options = array($selected_options);
  }

  // Grab exposed filter description.  We'll put it under the label where it
  // makes more sense.
  $description = '';
  if (!empty($element['#bef_description'])) {
    $description = '<div class="description">' . $element['#bef_description'] . '</div>';
  }

  $output = '<div class="bef-checkboxes">';
  foreach ($element['#options'] as $option => $elem) {
    if ('All' === $option) {
      // TODO: 'All' text is customizable in Views.
      // No need for an 'All' option -- either unchecking or checking all the
      // checkboxes is equivalent.
      continue;
    }

    // Check for Taxonomy-based filters.
    if (is_object($elem)) {
      $slice = array_slice($elem->option, 0, 1, TRUE);
      list($option, $elem) = each($slice);
    }

    // Check for optgroups.  Put subelements in the $element_set array and add
    // a group heading. Otherwise, just add the element to the set.
    $element_set = array();
    $is_optgroup = FALSE;
    if (is_array($elem)) {
      $output .= '<div class="bef-group">';
      $output .= '<div class="bef-group-heading">' . $option . '</div>';
      $output .= '<div class="bef-group-items">';
      $element_set = $elem;
      $is_optgroup = TRUE;
    }
    else {
      $element_set[$option] = $elem;
    }

    foreach ($element_set as $key => $value) {
      $output .= avant_garde_theme_bef_checkbox($element, $key, $value, array_search($key, $selected_options) !== FALSE);
    }

    if ($is_optgroup) {
      // Close group and item <div>s.
      $output .= '</div></div>';
    }

  }
  $output .= '</div>';

  if (!empty($element['#attributes']['class'])
      && FALSE !== ($key = array_search('form-control', $element['#attributes']['class']))) {
    unset($element['#attributes']['class'][$key]);
  }

  // Fake theme_checkboxes() which we can't call because it calls
  // theme_form_element() for each option.
  $attributes['class'] = array('form-checkboxes', 'bef-select-as-checkboxes');
  if (!empty($element['#bef_select_all_none'])) {
    $attributes['class'][] = 'bef-select-all-none';
  }
  if (!empty($element['#bef_select_all_none_nested'])) {
    $attributes['class'][] = 'bef-select-all-none-nested';
  }
  if (!empty($element['#attributes']['class'])) {
    $attributes['class'] = array_merge($element['#attributes']['class'], $attributes['class']);
  }

  return '<div' . drupal_attributes($attributes) . ">$description$output</div>";
}

function avant_garde_theme_bef_checkbox($element, $value, $label, $selected) {
  $value = check_plain($value);
  $label = filter_xss_admin($label);
  $id = drupal_html_id($element['#id'] . '-' . $value);
  // Custom ID for each checkbox based on the <select>'s original ID.
  $properties = array(
    '#required' => FALSE,
    '#id' => $id,
    '#type' => 'bef-checkbox',
    '#name' => $id,
  );

  // Prevent the select-all-none class from cascading to all checkboxes.
  if (!empty($element['#attributes']['class'])
      && FALSE !== ($key = array_search('bef-select-all-none', $element['#attributes']['class']))) {
    unset($element['#attributes']['class'][$key]);
  }

  if (!empty($element['#attributes']['class'])
      && FALSE !== ($key = array_search('form-control', $element['#attributes']['class']))) {
    unset($element['#attributes']['class'][$key]);
  }

  // Unset the name attribute as we are setting it manually.
  unset($element['#attributes']['name']);

  // Unset the multiple attribute as it doesn't apply for checkboxes.
  unset ($element['#attributes']['multiple']);

  $checkbox = '<input type="checkbox" '
    // Brackets are key -- just like select.
    . 'name="' . $element['#name'] . '[]" '
    . 'id="' . $id . '" '
    . 'value="' . $value . '" '
    . ($selected ? 'checked="checked" ' : '')
    . drupal_attributes($element['#attributes']) . ' />';
  $properties['#children'] = "$checkbox <label class='option' for='$id'>$label</label>";
  $output = theme('form_element', array('element' => $properties));
  return $output;
}
