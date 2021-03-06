<?php
/*
  $Id: extra_fields.php ver 2.3 by Kevin L. Shelton 2010-11-19
  
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  $eid = (isset($HTTP_GET_VARS['eid']) ? $HTTP_GET_VARS['eid'] : '');
  $confirm = (isset($HTTP_GET_VARS['confirm']) ? $HTTP_GET_VARS['confirm'] : '');
  $languages = tep_get_languages();
  $lang = array();
  for ($i=0, $n=sizeof($languages); $i<$n; $i++) { // build array accessed directly by language id
    $lang[$languages[$i]['id']] = array ('name' => $languages[$i]['name'],
                                         'code' => $languages[$i]['code'],
                                         'image' => $languages[$i]['image'],
                                         'directory' => $languages[$i]['directory']);
  }
  $categories = tep_get_category_tree();
  $confirmation_needed = false;
  if (tep_not_null($action)) {
    $messages = array();
    $error = false;
    switch ($action) {
      case 'insert': // validate form
        $all_cats = ($HTTP_POST_VARS['all_cats'] == '0') ? 0 : 1;
        $app_cats = array();
        foreach ($categories as $cat) {
          if (isset($HTTP_POST_VARS['usecat' . $cat['id']]) && ($HTTP_POST_VARS['usecat' . $cat['id']] == $cat['id'])) $app_cats[] = $cat['id'];
        }
        if (empty($app_cats) || in_array(0, $app_cats)) $all_cats = 1; // if no categories selected or TOP category selected set all categories to true
        if ($all_cats)  $app_cats = array(); // force applicable categories to empty if all categories selected
        if (!isset($HTTP_POST_VARS['status'])) {
          $error = true;
          $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_ACTIVATE_NOW;
        } else {
          $status = ($HTTP_POST_VARS['status'] == '0') ? 0 : 1;
        }
        if (!isset($HTTP_POST_VARS['show_admin'])) {
          $error = true;
          $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_SHOW_ADMIN;
        } else {
          $show_admin = ($HTTP_POST_VARS['show_admin'] == '0') ? 0 : 1;
        }
        $order = (isset($HTTP_POST_VARS['sort_order'])) ? tep_db_prepare_input($HTTP_POST_VARS['sort_order']) : 0;
        if (!isset($HTTP_POST_VARS['search'])) {
          $error = true;
          $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_SEARCH;
        } else {
          $search = ($HTTP_POST_VARS['search'] == '0') ? 0 : 1;
        }
        if (!isset($HTTP_POST_VARS['listing'])) {
          $error = true;
          $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_LISTING;
        } else {
          $listing = ($HTTP_POST_VARS['listing'] == '0') ? 0 : 1;
        }
        if (!isset($HTTP_POST_VARS['meta'])) {
          $error = true;
          $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_META;
        } else {
          $meta = ($HTTP_POST_VARS['meta'] == '0') ? 0 : 1;
        }
        if (!isset($HTTP_POST_VARS['value_list'])) {
          $error = true;
          $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_VALUE_LIST;
        } else {
          $uses_list = ($HTTP_POST_VARS['value_list'] == '0') ? 0 : 1;
        }
        if ($uses_list === 0) { // values required only if not using value list
          if (!isset($HTTP_POST_VARS['text_entry'])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_TEXT_ENTRY;
          } else {
            $text_type = ($HTTP_POST_VARS['text_entry'] == '1') ? 1 : 0;
          }
          if ($text_type && $listing) {
            $error = true;
            $messages[] = ERROR_INCOMPATIBLE_TA_PL;
          }
          if (!$text_type) { // field size required only for single line text
            if (!isset($HTTP_POST_VARS['size'])) {
              $error = true;
              $messages[] = ERROR_ENTRY_REQUIRED . TEXT_SIZE;
            } else {
              $size = tep_db_prepare_input($HTTP_POST_VARS['size']);
              if (!is_numeric($size) || ($size < 1) || ($size > 255)) {
                $error = true;
                $messages[] = ERROR_OUTOFRANGE;
              }
            }
          }
        }
        if ($uses_list == 1) { // values required only if using value list
          $size = 64; 
          $text_type = 0;
          if (!isset($HTTP_POST_VARS['list_type'])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . TEXT_USER_SELECTS;
          } else {
            $list_type = ($HTTP_POST_VARS['list_type'] == '1') ? 1 : 0;
          }
          if (!isset($HTTP_POST_VARS['chain'])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . TEXT_SHOW_PARENTS;
          } else {
            $chain = ($HTTP_POST_VARS['chain'] == '0') ? 0 : 1;
          }
          if ($chain && $list_type) {
            $error = true;
            $messages[] = ERROR_INCOMPATIBLE_MS_SC;
          }
          if (!isset($HTTP_POST_VARS['restrict'])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . TEXT_RESTRICTS;
          } else {
            $restrict = ($HTTP_POST_VARS['restrict'] == '0') ? 0 : 1;
          }
          if ($restrict && $list_type) {
            $error = true;
            $messages[] = ERROR_INCOMPATIBLE_MS_RPL;
          }
          if (!isset($HTTP_POST_VARS['quicksearch'])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . TEXT_SEARCH_BOX;
          } else {
            $quicksearch = ($HTTP_POST_VARS['quicksearch'] == '0') ? 0 : 1;
          }
          if ($quicksearch && !$search) {
            $error = true;
            $messages[] = ERROR_AS_REQUIRED;
          }
          if ($quicksearch && $list_type) {
            $error = true;
            $messages[] = ERROR_INCOMPATIBLE_MS_QS;
          }
          if (!isset($HTTP_POST_VARS['checkboxes']) && !$list_type) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . TEXT_SELECTED_BY;
          } else {
            $entry_method = ($HTTP_POST_VARS['checkboxes'] == '1') ? 1 : 0;
          }
          if ($list_type) $entry_method = 1;
          if (!isset($HTTP_POST_VARS['columns'])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . TEXT_COLUMNS;
          } else {
            $columns = tep_db_prepare_input($HTTP_POST_VARS['columns']);
            if (!is_numeric($columns) || ($columns < 1) || ($columns > 255)) {
              $error = true;
              $messages[] = ERROR_COLS_OUTOFRANGE;
            }
          }
          if (!isset($HTTP_POST_VARS['display_type'])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . TEXT_DISPLAY_TYPE;
          } else {
            $display_type = tep_db_prepare_input($HTTP_POST_VARS['display_type']);
            if (!in_array($display_type, array('0', '1', '2'))) {
              $display_type = 0;
            }
          }
        } else { // values that do not apply to text fields 
          $chain = 0;
          $restrict = 0;
          $list_type = 0;
          $entry_method = 0;
          $quicksearch = 0;
          $columns = 1;
          $display_type = 0;
        }
        $labels = array();
        $active = false;
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          if (!isset($HTTP_POST_VARS['langactive_' . $languages[$i]['id']])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_ACTIVE . $languages[$i]['name'];
          } else {
            $lactive = ($HTTP_POST_VARS['langactive_' . $languages[$i]['id']] == '0') ? 0 : 1;
          }
          $lbl = (isset($HTTP_POST_VARS['label_' . $languages[$i]['id']])) ? tep_db_prepare_input($HTTP_POST_VARS['label_' . $languages[$i]['id']]) : '';
          if ($lactive && !tep_not_null($lbl)) {
            $error = true;
            $messages[] = sprintf(ERROR_LABEL, $languages[$i]['name']);
          }
          if ($lactive) $active = true;
          $labels[$languages[$i]['id']] = array('active' => $lactive, 'label' => $lbl);
        }
        if (!$active) {
          $error = true;
          $messages[] = ERROR_ACTIVE;
        }
        if ($error) {  // if error return to entry form
          $action = 'new';
        } else { // otherwise create field
          $data_array = array('epf_order' => (int)$order,
                              'epf_status' => $status,
                              'epf_show_in_admin' => $show_admin,
                              'epf_uses_value_list' => $uses_list,
                              'epf_multi_select' => $list_type,
                              'epf_advanced_search' => $search,
                              'epf_quick_search' => $quicksearch,
                              'epf_show_in_listing' => $listing,
                              'epf_size' => (int)$size,
                              'epf_use_as_meta_keyword' => $meta,
                              'epf_use_to_restrict_listings' => $restrict,
                              'epf_checked_entry' => $entry_method,
                              'epf_show_parent_chain' => $chain,
                              'epf_value_display_type' => $display_type,
                              'epf_num_columns' => (int)$columns,
                              'epf_textarea' => $text_type,
                              'epf_all_categories' => $all_cats,
                              'epf_category_ids' => (!empty($app_cats) ? implode('|', $app_cats) : 'null'));
          tep_db_perform(TABLE_EPF, $data_array);
          $eid = tep_db_insert_id();
          $field = 'extra_value';
          if ($text_type) {
            $mysql_type = ' text default null';
          } else {
            $mysql_type = ' varchar(' . (int)$size . ') default null';
          }
          if ($uses_list) {
            if ($list_type) {
              $field .= '_ms';
              $mysql_type = ' text default null';
            } else {
              $field .= '_id';
              $mysql_type = ' int unsigned not null default 0';
            }
          }
          $field .= $eid;
          tep_db_query('alter table ' . TABLE_PRODUCTS_DESCRIPTION . ' add ' . $field . $mysql_type);
          foreach ($labels as $lid => $value) {
            $label_array = array('epf_id' => $eid,
                                 'languages_id' => $lid,
                                 'epf_label' => ($value['active'] ? $value['label'] : ''),
                                 'epf_active_for_language' => $value['active']);
            tep_db_perform(TABLE_EPF_LABELS, $label_array);
          }
          tep_redirect(tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $eid));
        }
        break;
      case 'update': // validate form
        $all_cats = ($HTTP_POST_VARS['all_cats'] == '0') ? 0 : 1;
        $app_cats = array();
        foreach ($categories as $cat) {
          if (isset($HTTP_POST_VARS['usecat' . $cat['id']]) && ($HTTP_POST_VARS['usecat' . $cat['id']] == $cat['id'])) $app_cats[] = $cat['id'];
        }
        if (empty($app_cats) || in_array(0, $app_cats)) $all_cats = 1; // if no categories selected or TOP category selected set all categories to true
        if ($all_cats)  $app_cats = array(); // force applicable categories to empty if all categories selected
        $query = tep_db_query("select * from " . TABLE_EPF . " where epf_id = " . (int)$eid);
        $field_info = tep_db_fetch_array($query); // retrieve original field information
        if (!isset($HTTP_POST_VARS['status'])) {
          $error = true;
          $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_ACTIVATE_NOW;
        } else {
          $status = ($HTTP_POST_VARS['status'] == '0') ? 0 : 1;
        }
        if (!isset($HTTP_POST_VARS['show_admin'])) {
          $error = true;
          $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_SHOW_ADMIN;
        } else {
          $show_admin = ($HTTP_POST_VARS['show_admin'] == '0') ? 0 : 1;
        }
        $order = (isset($HTTP_POST_VARS['sort_order'])) ? tep_db_prepare_input($HTTP_POST_VARS['sort_order']) : 0;
        if (!isset($HTTP_POST_VARS['search'])) {
          $error = true;
          $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_SEARCH;
        } else {
          $search = ($HTTP_POST_VARS['search'] == '0') ? 0 : 1;
        }
        if (!isset($HTTP_POST_VARS['listing'])) {
          $error = true;
          $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_LISTING;
        } else {
          $listing = ($HTTP_POST_VARS['listing'] == '0') ? 0 : 1;
        }
        if (!isset($HTTP_POST_VARS['meta'])) {
          $error = true;
          $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_META;
        } else {
          $meta = ($HTTP_POST_VARS['meta'] == '0') ? 0 : 1;
        }
        $uses_list = $field_info['epf_uses_value_list'];
        if (($uses_list == 0) && !$field_info['epf_textarea']) { // size required only if standard text field
          if (!isset($HTTP_POST_VARS['size'])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . TEXT_SIZE;
          } else {
            $size = tep_db_prepare_input($HTTP_POST_VARS['size']);
            if (!is_numeric($size) || ($size < 1) || ($size > 255)) {
              $error = true;
              $messages[] = ERROR_OUTOFRANGE;
            }
          }
        }
        if ($uses_list == 1) {
          $size = 64;
          if (!$field_info['epf_multi_select']) {
            if (!isset($HTTP_POST_VARS['chain'])) {
              $error = true;
              $messages[] = ERROR_ENTRY_REQUIRED . TEXT_SHOW_PARENTS;
            } else {
              $chain = ($HTTP_POST_VARS['chain'] == '0') ? 0 : 1;
            }
            if (!isset($HTTP_POST_VARS['restrict'])) {
              $error = true;
              $messages[] = ERROR_ENTRY_REQUIRED . TEXT_RESTRICTS;
            } else {
              $restrict = ($HTTP_POST_VARS['restrict'] == '0') ? 0 : 1;
            }
            if (!isset($HTTP_POST_VARS['quicksearch'])) {
              $error = true;
              $messages[] = ERROR_ENTRY_REQUIRED . TEXT_SEARCH_BOX;
            } else {
              $quicksearch = ($HTTP_POST_VARS['quicksearch'] == '0') ? 0 : 1;
            }
            if ($quicksearch && !$search) {
              $error = true;
              $messages[] = ERROR_AS_REQUIRED;
            }
            if (!isset($HTTP_POST_VARS['checkboxes'])) {
              $error = true;
              $messages[] = ERROR_ENTRY_REQUIRED . TEXT_SELECTED_BY;
            } else {
              $entry_method = ($HTTP_POST_VARS['checkboxes'] == '1') ? 1 : 0;
            }
          } else {
            $chain = 0;
            $restrict = 0;
            $quicksearch = 0;
            $entry_method = 1;
          }
          if (!isset($HTTP_POST_VARS['columns'])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . TEXT_COLUMNS;
          } else {
            $columns = tep_db_prepare_input($HTTP_POST_VARS['columns']);
            if (!is_numeric($columns) || ($columns < 1) || ($columns > 255)) {
              $error = true;
              $messages[] = ERROR_COLS_OUTOFRANGE;
            }
          }
          if (!isset($HTTP_POST_VARS['display_type'])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . TEXT_DISPLAY_TYPE;
          } else {
            $display_type = tep_db_prepare_input($HTTP_POST_VARS['display_type']);
            if (!in_array($display_type, array('0', '1', '2'))) {
              $display_type = 0;
            }
          }
        } else { // values that are never active if not using value list 
          $chain = 0;
          $restrict = 0;
          $entry_method = 0;
          $quicksearch = 0;
          $columns = 1;
          $display_type = 0;
        }
        $labels = array();
        $active = false;
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          if (!isset($HTTP_POST_VARS['langactive_' . $languages[$i]['id']])) {
            $error = true;
            $messages[] = ERROR_ENTRY_REQUIRED . ENTRY_ACTIVE . $languages[$i]['name'];
          } else {
            $lactive = ($HTTP_POST_VARS['langactive_' . $languages[$i]['id']] == '0') ? 0 : 1;
          }
          $lbl = (isset($HTTP_POST_VARS['label_' . $languages[$i]['id']])) ? tep_db_prepare_input($HTTP_POST_VARS['label_' . $languages[$i]['id']]) : '';
          if ($lactive && !tep_not_null($lbl)) {
            $error = true;
            $messages[] = sprintf(ERROR_LABEL, $languages[$i]['name']);
          }
          if ($lactive) $active = true;
          $labels[$languages[$i]['id']] = array('active' => $lactive, 'label' => $lbl);
        }
        if (!$active) { // if no active languages
          $error = true;
          $messages[] = ERROR_ACTIVE;
        }
        if ($error) {  // if error return to entry form
          $action = 'edit';
        } else { // otherwise update field
          $field = 'extra_value';
          $mysql_type = ' varchar(' . (int)$size . ') default null';
          if ($uses_list) {
            if ($field_info['epf_multi_select']) {
              $field .= '_ms';
              $mysql_type = ' text default null';
            } else {
              $field .= '_id';
              $mysql_type = ' int unsigned not null default 0';
            }
          }
          $field .= $eid;
          if (($size < $field_info['epf_size'])) {
            $check_query = tep_db_query("select count(products_id) as total, max(length(" . $field . ")) as maxlen from " . TABLE_PRODUCTS_DESCRIPTION . " where length(" . $field . ") > " . (int)$size);
            $check = tep_db_fetch_array($check_query);
            if ($check['total'] > 1) { // check to see if reducing size will truncate data
              $confirmation_needed = true;
              $messages[] = sprintf(WARNING_TRUNCATE, $check['total'], $check['maxlen']);
            }
          }
          $label_query = tep_db_query("select * from " . TABLE_EPF_LABELS . " where epf_id=" . (int)$eid);
          while ($label_info = tep_db_fetch_array($label_query)) {
            if ($label_info['epf_active_for_language'] > $labels[$label_info['languages_id']]['active']) { // if language being deactivated
              $check_query = tep_db_query("select count(products_id) as total from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = " . (int)$label_info['languages_id'] . " and " . ($uses_list && !$field_info['epf_multi_select'] ? $field . " > 0" : "length(" . $field . ") > 0"));
              $check = tep_db_fetch_array($check_query);
              if ($check['total'] > 0) { // check to see if langauge is used
                $confirmation_needed = true;
                $messages[] = sprintf(WARNING_LANGUAGE_IN_USE, $check['total'], $lang[$label_info['languages_id']]['name']);
              }
            }
          }
          if ((!$confirmation_needed) || ($confirm == 'yes')) { // if confirmation not needed or changes have been confirmed
            $data_array = array('epf_order' => (int)$order,
                                'epf_status' => $status,
                                'epf_show_in_admin' => $show_admin,
                                'epf_advanced_search' => $search,
                                'epf_quick_search' => $quicksearch,
                                'epf_show_in_listing' => $listing,
                                'epf_size' => (int)$size,
                                'epf_use_as_meta_keyword' => $meta,
                                'epf_use_to_restrict_listings' => $restrict,
                                'epf_checked_entry' => $entry_method,
                                'epf_value_display_type' => $display_type,
                                'epf_show_parent_chain' => $chain,
                                'epf_num_columns' => (int)$columns,
                                'epf_all_categories' => $all_cats,
                                'epf_category_ids' => (!empty($app_cats) ? implode('|', $app_cats) : 'null'));
            tep_db_perform(TABLE_EPF, $data_array, 'update', 'epf_id = ' . (int)$eid);
            if (($uses_list == 0) && ($field_info['epf_size'] != $size)) { // if text field size has changed
              tep_db_query('alter table ' . TABLE_PRODUCTS_DESCRIPTION . '  change ' . $field . ' ' . $field . ' varchar(' . (int)$size . ') default null');
            }
            foreach ($labels as $lid => $value) {
              $label_array = array('epf_label' => $value['label'],
                                   'epf_active_for_language' => $value['active']);
              tep_db_perform(TABLE_EPF_LABELS, $label_array, 'update', '(epf_id = ' . (int)$eid . ') and (languages_id = ' . (int)$lid . ')');
            }
            tep_redirect(tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $eid));
          } else { // request confirmation
            $action = 'edit';
          }
        }
        break;
      case 'delete':
        if ($confirm == 'yes') {
          if (isset($HTTP_GET_VARS['used']) && ($HTTP_GET_VARS['used'] > 0)) {
            $double_check = 'yes';
          } else {
            $query = tep_db_query("select epf_uses_value_list, epf_multi_select, epf_has_linked_field, epf_links_to from " . TABLE_EPF . " where epf_id = " . (int)$eid);
            $field_info = tep_db_fetch_array($query); // retrieve field type
            $field = 'extra_value';
            if ($field_info['epf_uses_value_list']) {
              if ($field_info['epf_multi_select']) {
                $field .= '_ms';
              } else {
                $field .= '_id';
              }
            }
            $field .= (int)$eid;
            tep_db_query('alter table ' . TABLE_PRODUCTS_DESCRIPTION . ' drop ' . $field);
            tep_db_query('delete from ' . TABLE_EPF . ' where epf_id = ' . (int)$eid);
            tep_db_query('delete from ' . TABLE_EPF_LABELS . ' where epf_id = ' . (int)$eid);
            if ($field_info['epf_uses_value_list']) {
              if ($field_info['epf_has_linked_field']) {
                $query = tep_db_query("select value_id from " . TABLE_EPF_VALUES . ' where epf_id = ' . (int)$field_info['epf_links_to']);
                $values = array();
                while ($v = tep_db_fetch_array($query)) {
                  $values[] = $v['value_id'];
                }
                if (!empty($values)) {
                  $vlist = implode(',', $values);
                  tep_db_query('update ' . TABLE_EPF_VALUES . ' set value_depends_on = 0 where value_depends_on in (' . $vlist . ')');
                }
                tep_db_query('update ' . TABLE_EPF . ' set epf_has_linked_field = 0, epf_links_to = 0 where epf_id = ' . (int)$field_info['epf_links_to']);
              }
              $query = tep_db_query("select value_id, value_image from " . TABLE_EPF_VALUES . ' where epf_id = ' . (int)$eid);
              $values = array();
              while ($v = tep_db_fetch_array($query)) {
                $values[] = $v['value_id'];
                if (tep_not_null($v['value_image'])) {
                  if (file_exists(DIR_FS_CATALOG_IMAGES . 'epf/' . $v['value_image'])) {
                    @unlink(DIR_FS_CATALOG_IMAGES . 'epf/' . $v['value_image']);
                  }
                }
              }
              if (!empty($values)) {
                $vlist = implode(',', $values);
                tep_db_query('update ' . TABLE_EPF_VALUES . ' set value_depends_on = 0 where value_depends_on in (' . $vlist . ')');
                tep_db_query('delete from ' . TABLE_EPF_EXCLUDE . ' where (value_id1 in (' . $vlist . ')) or (value_id2 in (' . $vlist . '))');
              }
            tep_db_query('delete from ' . TABLE_EPF_VALUES . ' where epf_id = ' . (int)$eid);
            }
            tep_redirect(tep_href_link(FILENAME_EXTRA_FIELDS));
          }
        } else {
          $double_check = 'no';
        }
        break;
      case 'link':
        if ($confirm == 'yes') {
          $error = false;
          $eid = tep_db_prepare_input($HTTP_POST_VARS['epf_id']);
          $link_to = tep_db_prepare_input($HTTP_POST_VARS['link_to']);
          $query = tep_db_query('select * from ' . TABLE_EPF . ' where epf_id = ' . (int)$eid);
          if (tep_db_num_rows($query) != 1) {
            $error = true;
            $messageStack->add_session(ERROR_NO_FIELD . ' ' . $eid, 'error');
            $eid = '';
          }
          $source = tep_db_fetch_array($query);
          $query = tep_db_query('select * from ' . TABLE_EPF . ' where epf_id = ' . (int)$link_to);
          if (tep_db_num_rows($query) != 1) {
            $error = true;
            $messageStack->add_session(ERROR_NO_FIELD . ' ' . $link_to, 'error');
          }
          $dest = tep_db_fetch_array($query);
          if (!($source['epf_uses_value_list'] && $dest['epf_uses_value_list'])) {
            $error = true;
            $messageStack->add_session(ERROR_WRONG_TYPE, 'error');
          }
          if ($source['epf_multi_select'] == $dest['epf_multi_select']) {
            $error = true;
            $messageStack->add_session(ERROR_SAME_TYPE, 'error');
          }
          if ($source['epf_has_linked_field']) {
            $error = true;
            $messageStack->add_session(sprintf(ERROR_ALREADY_LINKED, $eid), 'error');
          }
          if ($dest['epf_has_linked_field']) {
            $error = true;
            $messageStack->add_session(sprintf(ERROR_ALREADY_LINKED, $link_to), 'error');
          }
          $label_query = tep_db_query("select languages_id from " . TABLE_EPF_LABELS . " where (epf_id = " . (int)$eid . ") and epf_active_for_language");
          $eid_active_languages = array();
          while ($label = tep_db_fetch_array($label_query)) {
            $eid_active_languages[] = $label['languages_id'];
          }
          if (empty($eid_active_languages)) {
            $error = true;
            $messageStack->add_session(ERROR_NO_FIELD . ' ' . $eid, 'error');            
          } else {
            $check = tep_db_query("select languages_id from " . TABLE_EPF_LABELS . " where (epf_id = " . (int)$link_to . ") and epf_active_for_language and languages_id in (" . implode(',', $eid_active_languages) . ")");
            if (tep_db_num_rows($check) == 0 ) {
              $error = true;
              $messageStack->add_session(TEXT_NOT_LINKABLE, 'error');
            }
          }
          if (!$error) {
            tep_db_query('update ' . TABLE_EPF . ' set epf_has_linked_field = 1, epf_links_to = ' . (int)$link_to . ' where epf_id = ' . (int)$eid);
            tep_db_query('update ' . TABLE_EPF . ' set epf_has_linked_field = 1, epf_links_to = ' . (int)$eid . ' where epf_id = ' . (int)$link_to);
            $messageStack->add_session(sprintf(TEXT_LINK_SUCCESS, $eid, $link_to), 'success');
          }
          tep_redirect(tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $eid));
        }
        break;
      case 'unlink':
        if ($confirm == 'yes') {
          if (isset($HTTP_GET_VARS['used']) && ($HTTP_GET_VARS['used'] > 0)) {
            $double_check = 'yes';
          } else {
            $query = tep_db_query("select * from " . TABLE_EPF . " where epf_id = " . (int)$eid);
            $field_info = tep_db_fetch_array($query); // retrieve linked field
            if (!$field_info['epf_has_linked_field'] || ($field_info['epf_links_to'] == 0)) {
              $messageStack->add_session(ERROR_NOT_LINKED, 'error');
              tep_redirect(tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $eid));
            }
            $linked_id = $field_info['epf_links_to'];
            $query = tep_db_query("select value_id from " . TABLE_EPF_VALUES . ' where epf_id = ' . (int)$eid);
            $values = array();
            while ($v = tep_db_fetch_array($query)) {
              $values[] = $v['value_id'];
            }
            if (!empty($values)) {
              $vlist = implode(',', $values);
              tep_db_query('update ' . TABLE_EPF_VALUES . ' set value_depends_on = 0 where value_depends_on in (' . $vlist . ')');
            }
            $query = tep_db_query("select value_id from " . TABLE_EPF_VALUES . ' where epf_id = ' . (int)$linked_id);
            $values = array();
            while ($v = tep_db_fetch_array($query)) {
              $values[] = $v['value_id'];
            }
            if (!empty($values)) {
              $vlist = implode(',', $values);
              tep_db_query('update ' . TABLE_EPF_VALUES . ' set value_depends_on = 0 where value_depends_on in (' . $vlist . ')');
            }
            tep_db_query('update ' . TABLE_EPF . ' set epf_has_linked_field = 0, epf_links_to = 0 where epf_id = ' . (int)$eid);
            tep_db_query('update ' . TABLE_EPF . ' set epf_has_linked_field = 0, epf_links_to = 0 where epf_id = ' . (int)$linked_id);
            tep_redirect(tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $eid));
          }
        } else {
          $double_check = 'no';
        }
        break;
      case 'setflag':
        $flag = (isset($HTTP_GET_VARS['flag']) ? $HTTP_GET_VARS['flag'] : 'oops');
        if (!is_numeric($flag) || (($flag != 0) && ($flag != 1))) break; // skip if flag not properly set
        tep_db_query('update ' . TABLE_EPF . ' set epf_status = ' . (int)$flag . ' where epf_id = ' . (int)$eid);
        break;
    }
  }
  
    require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div class="contentText">
<table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <?php if (($action != 'new') && ($action != 'edit')) { ?>
          <td width="100%">
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
              <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
            </tr>
            <tr>
              <td colspan=2 align="right"><?php echo tep_draw_form('new_field', FILENAME_EXTRA_FIELDS, 'action=new') . tep_draw_input_field('new', BUTTON_NEW, 'alt="' . BUTTON_NEW . '"', false, 'submit') . '&nbsp;&nbsp;'; ?></form></td>
            </tr>
          </table></td>
          <?php } else { ?>
              <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>          
<?php
                }
?>

      </tr>
      <?php if (($action == 'new') || ($action =='edit')) {
      if ($action == 'edit') {
        $query = tep_db_query("select * from " . TABLE_EPF . " where epf_id = " . (int)$eid);
        $field = tep_db_fetch_array($query);
        $label_query = tep_db_query("select * from " . TABLE_EPF_LABELS . " where epf_id = " . (int)$eid);
        $epf_label = array();
        while ($label = tep_db_fetch_array($label_query)) {
          $epf_label[$label['languages_id']] = $label;
        }
        $applicable_cats = explode('|', $field['epf_category_ids']);
        echo '<tr><td><p class="pageHeading">' . HEADING_EDIT . $eid . "</p>\n";
      } else {
        echo '<tr><td><p class="pageHeading">' . HEADING_NEW . "</p>\n";
        $applicable_cats = array();
      }
      if (!empty($messages)) {
        echo '<table ' . ($error ? 'class="error"' : 'class="warning"') . ' width="100%">' . "\n";
        foreach ($messages as $message) {
          echo '<tr><td>' . $message . "</td></tr>\n";
        }
        echo "</table>\n";
      }
      echo tep_draw_form('field_entry', FILENAME_EXTRA_FIELDS, 'action=' . (($action == 'new') ? 'insert' : 'update') . '&eid=' . $eid . ($confirmation_needed ? '&confirm=yes' : ''), 'post', 'enctype="multipart/form-data"');
      echo "\n<table>";
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        echo '<tr border=1><td>' . tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . $languages[$i]['name'] . '</td><td>';
        echo ENTRY_LABEL . tep_draw_input_field('label_' . $languages[$i]['id'], $epf_label[$languages[$i]['id']]['epf_label'], "size=64 maxlength=64") . '<br />';
        echo ENTRY_ACTIVE . tep_draw_radio_field('langactive_' . $languages[$i]['id'], '1', false, $epf_label[$languages[$i]['id']]['epf_active_for_language']) . '&nbsp;' . TEXT_YES . '&nbsp;' . tep_draw_radio_field('langactive_' . $languages[$i]['id'], '0', false, $epf_label[$languages[$i]['id']]['epf_active_for_language']) . '&nbsp;' . TEXT_NO . '</td></tr>';
      }
      echo "</table>\n";
      echo '<p>' . ENTRY_ALL_CATEGORIES . tep_draw_radio_field('all_cats', '1', true, $field['epf_all_categories']) . '&nbsp;' . TEXT_YES . '&nbsp;' . tep_draw_radio_field('all_cats', '0', false, $field['epf_all_categories']) . '&nbsp;' . TEXT_NO . "</p>\n";
      echo '<p>' . ENTRY_ACTIVATE_NOW . tep_draw_radio_field('status', '1', false, $field['epf_status']) . '&nbsp;' . TEXT_YES . '&nbsp;' . tep_draw_radio_field('status', '0', false, $field['epf_status']) . '&nbsp;' . TEXT_NO . "</p>\n";
      echo '<p>' . ENTRY_SHOW_ADMIN . tep_draw_radio_field('show_admin', '1', false, $field['epf_show_in_admin']) . '&nbsp;' . TEXT_YES . '&nbsp;' . tep_draw_radio_field('show_admin', '0', false, $field['epf_show_in_admin']) . '&nbsp;' . TEXT_NO . "</p>\n";
      echo '<p>' . ENTRY_ORDER . tep_draw_input_field('sort_order', $field['epf_order']) . "</p>\n";
      echo '<p>' . ENTRY_SEARCH . tep_draw_radio_field('search', '1', false, $field['epf_advanced_search']) . '&nbsp;' . TEXT_YES . '&nbsp;' . tep_draw_radio_field('search', '0', false, $field['epf_advanced_search']) . '&nbsp;' . TEXT_NO . "</p>\n";
      echo '<p>' . ENTRY_LISTING . tep_draw_radio_field('listing', '1', false, $field['epf_show_in_listing']) . '&nbsp;' . TEXT_YES . '&nbsp;' . tep_draw_radio_field('listing', '0', false, $field['epf_show_in_listing']) . '&nbsp;' . TEXT_NO . "</p>\n";
      echo '<p>' . ENTRY_META . tep_draw_radio_field('meta', '1', false, $field['epf_use_as_meta_keyword']) . '&nbsp;' . TEXT_YES . '&nbsp;' . tep_draw_radio_field('meta', '0', false, $field['epf_use_as_meta_keyword']) . '&nbsp;' . TEXT_NO . "</p>\n";
      if (($action == 'new') || (!$field['epf_uses_value_list'] && !$field['epf_textarea'])) echo '<p>' . ENTRY_SIZE . tep_draw_input_field('size', $field['epf_size']) . "</p>\n";
      if ($action == 'new') {
        echo '<p>' . ENTRY_TEXT_ENTRY . tep_draw_radio_field('text_entry', '0') . '&nbsp;' . TEXT_SINGLE_LINE . '&nbsp;' . tep_draw_radio_field('text_entry', '1') . '&nbsp;' . TEXT_MULTILINE . TEXT_LIST_IGNORES . '<br />' . TEXT_TEXTAREA_NOTE . "</p>\n";
        echo '<p>' . ENTRY_VALUE_LIST . tep_draw_radio_field('value_list', '1') . '&nbsp;' . TEXT_YES . '&nbsp;' . tep_draw_radio_field('value_list', '0') . '&nbsp;' . TEXT_NO . "</p>\n";
        echo '<p><b>' . TEXT_APPLIES_LIST_ONLY . "</b></p>\n";
        echo '<p>' . ENTRY_LIST_TYPE . tep_draw_radio_field('list_type', '0', false, $field['epf_multi_select']) . '&nbsp;' . TEXT_SINGLE_VALUE . '&nbsp;' . tep_draw_radio_field('list_type', '1', false, $field['epf_multi_select']) . '&nbsp;' . TEXT_MULTIPLE_VALUE . "</p>\n";
      }
      if (($action == 'new') || ($field['epf_uses_value_list'] && !$field['epf_multi_select'])) {
        echo '<p>' . ENTRY_CHAIN . tep_draw_radio_field('chain', '1', false, $field['epf_show_parent_chain']) . '&nbsp;' . TEXT_YES . '&nbsp;' . tep_draw_radio_field('chain', '0', false, $field['epf_show_parent_chain']) . '&nbsp;' . TEXT_NO . "</p>\n";
        echo '<p>' . ENTRY_RESTRICT . tep_draw_radio_field('restrict', '1', false, $field['epf_use_to_restrict_listings']) . '&nbsp;' . TEXT_YES . '&nbsp;' . tep_draw_radio_field('restrict', '0', false, $field['epf_use_to_restrict_listings']) . '&nbsp;' . TEXT_NO . "</p>\n";
        echo '<p>' . ENTRY_SEARCH_BOX . tep_draw_radio_field('quicksearch', '1', false, $field['epf_quick_search']) . '&nbsp;' . TEXT_YES . '&nbsp;' . tep_draw_radio_field('quicksearch', '0', false, $field['epf_quick_search']) . '&nbsp;' . TEXT_NO . "</p>\n";
        echo '<p>' . ENTRY_CHECKBOX . tep_draw_radio_field('checkboxes', '0', false, $field['epf_checked_entry']) . '&nbsp;' . TEXT_DROPDOWN . '&nbsp;' . tep_draw_radio_field('checkboxes', '1', false, $field['epf_checked_entry']) . '&nbsp;' . TEXT_RADIO . ($action == 'new' ? '<br />' . TEXT_MS_CHECKBOX_NOTE : '') . "</p>\n";
      }
      if (($action == 'new') || ($field['epf_uses_value_list'])) {
        echo '<p>' . ENTRY_COLUMNS . tep_draw_input_field('columns', $field['epf_num_columns']) . "</p>\n";
        echo '<p><table border=0 style="vertical-align:middle"><tr><td>' . ENTRY_DISPLAY_TYPE . tep_draw_radio_field('display_type', '0', false, $field['epf_value_display_type']) . '</td><td><table border=1><tr><td>' . TEXT_TEXT . '</td></tr><tr><td>' . TEXT_SAMPLE . '</td></tr></table></td><td>&nbsp;' . tep_draw_radio_field('display_type', '1', false, $field['epf_value_display_type']) . '</td><td><table border=1><tr><td>' . TEXT_IMAGE . '</td></tr><tr><td>' . tep_image(DIR_WS_IMAGES . 'EPF_Example_Image.jpg', TEXT_SAMPLE) . '</td></tr></table></td><td>&nbsp;' . tep_draw_radio_field('display_type', '2', false, $field['epf_value_display_type']) . '</td><td><table border=1><tr><td>' . TEXT_IMAGE_TEXT . '</td></tr><tr><td><table><tr><td align=center>' . tep_image(DIR_WS_IMAGES . 'EPF_Example_Image.jpg', TEXT_SAMPLE) . '<br />' . TEXT_SAMPLE . "</td></tr></table></td></tr></table></td></tr></table></p>\n";
      }
      echo '<hr><p>' . ENTRY_CHECK_CATEGORIES . "<br />\n";
      foreach ($categories as $category) {
        echo tep_draw_checkbox_field('usecat' . $category['id'], $category['id'], in_array($category['id'], $applicable_cats)) . $category['text'] . "<br />\n";
      }
      echo "</p>\n";
      echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button( IMAGE_CANCEL, 'close', tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $eid)) . "\n";
      ?>
      </form></td></tr>
      <?php } elseif ($action == 'delete') { // Confirm deletion of field
        echo '<tr><td><p class="pageHeading">' . HEADING_DELETE . $eid . "</p>\n";
        $query = tep_db_query("select epf_uses_value_list, epf_multi_select, epf_has_linked_field from " . TABLE_EPF . " where epf_id = " . (int)$eid);
        $field_info = tep_db_fetch_array($query); // retrieve field type
        $field = 'extra_value';
        if ($field_info['epf_uses_value_list']) {
          if ($field_info['epf_multi_select']) {
            $field .= '_ms';
          } else {
            $field .= '_id';
          }
        }
        $field .= $eid;
        $used = 0;
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $label_query = tep_db_query("select * from " . TABLE_EPF_LABELS . " where epf_id = " . (int)$eid . " and languages_id = " . (int)$languages[$i]['id']);
          $label = tep_db_fetch_array($label_query);
          $check_query = tep_db_query("select count(products_id) as total from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = " . (int)$languages[$i]['id'] . " and " . (($field_info['epf_uses_value_list'] && !$field_info['epf_multi_select']) ? $field . " > 0" : "length(" . $field . ") > 0"));
          $check = tep_db_fetch_array($check_query);
          $used += $check['total']; // total how many descriptions use this field
          echo '<p>' . sprintf(TEXT_FIELD_DATA, $languages[$i]['name'], $label['epf_label'], $check['total']) . "</p>\n";
        }
        if ($double_check == 'no') {
          echo '<p>' . TEXT_ARE_SURE . ($field_info['epf_uses_value_list'] ? TEXT_VALUES_GONE : '') . ($field_info['epf_has_linked_field'] ? TEXT_LINKS_DESTROYED : '') . "</p>\n";
          echo '<p>' . tep_draw_form('yes', FILENAME_EXTRA_FIELDS, 'confirm=yes&action=delete&eid=' . $eid . '&used=' . $used) . tep_draw_input_field('yes', TEXT_YES, 'alt="' . TEXT_YES . '"', false, 'submit') . '</form>&nbsp;&nbsp;';
          echo tep_draw_form('no', FILENAME_EXTRA_FIELDS, 'eid=' . $eid) . tep_draw_input_field('no', TEXT_NO, 'alt="' . TEXT_NO . '"', false, 'submit') . "</form></p>\n";
        } else {
          echo '<p><b>' . TEXT_CONFIRM_DELETE . ($field_info['epf_uses_value_list'] ? TEXT_VALUES_GONE : '') . ($field_info['epf_has_linked_field'] ? TEXT_LINKS_DESTROYED : '') . "</b></p>\n";
          echo '<p>' . tep_draw_form('yes', FILENAME_EXTRA_FIELDS, 'confirm=yes&action=delete&eid=' . $eid) . tep_draw_input_field('yes', TEXT_YES, 'alt="' . TEXT_YES . '"', false, 'submit') . '</form>&nbsp;&nbsp;';
          echo tep_draw_form('no', FILENAME_EXTRA_FIELDS, 'eid=' . $eid) . tep_draw_input_field('no', TEXT_NO, 'alt="' . TEXT_NO . '"', false, 'submit') . "</form></p>\n";
        }
        echo "</td></tr>\n";
      } elseif ($action == 'link') { // get field to link to
        $error = false;
        $messages = array();
        echo '<tr><td><p class="pageHeading">' . BUTTON_LINK . "</p>\n";
        echo TABLE_HEADING_ID . $eid . "<br />\n";
        $query = tep_db_query("select * from " . TABLE_EPF . ' where epf_id = ' . (int)$eid);
        $field_info = tep_db_fetch_array($query);
        if ($field_info === false) {
          $error = true;
          $messages[] = ERROR_NO_FIELD;
        }
        $label_query = tep_db_query("select languages_id, epf_label from " . TABLE_EPF_LABELS . " where (epf_id = " . (int)$eid . ") and epf_active_for_language");
        $eid_active_languages = array();
        while ($label = tep_db_fetch_array($label_query)) {
          $eid_active_languages[] = $label['languages_id'];
          echo tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $lang[$label['languages_id']]['directory'] . '/images/' . $lang[$label['languages_id']]['image'], $lang[$label['languages_id']]['name']) . '&nbsp;' . $label['epf_label'] . '<br />';
        }
        if (empty($eid_active_languages)) {
          $error = true;
          $messages[] = ERROR_NO_FIELD;
        }
        echo '<p>' . tep_draw_form('link_fields', FILENAME_EXTRA_FIELDS, 'confirm=yes&action=link');
        echo tep_draw_hidden_field('epf_id', $eid) . '<b>' . TEXT_SELECT_LINK . "</b></p>\n";
        ?>
        <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LABEL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_SELECT; ?></td>
              </tr>
        <?php
$field_query = tep_db_query("select * from " . TABLE_EPF . ' where epf_uses_value_list and !epf_has_linked_field and epf_multi_select = ' . ($field_info['epf_multi_select'] ? '0' : '1') . " order by epf_order");
$any_matching_languages = false;
while ($epf = tep_db_fetch_array($field_query)) {
  $this_matches_languages = false;
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><?php echo $epf['epf_id']; ?></td>
                <td class="dataTableContent">
                <?php $label_query = tep_db_query("select languages_id, epf_label from " . TABLE_EPF_LABELS . " where (epf_id = " . (int)$epf['epf_id'] . ") and epf_active_for_language");
                while ($label = tep_db_fetch_array($label_query)) {
                  if (in_array($label['languages_id'], $eid_active_languages)) {
                    $any_matching_languages = true;
                    $this_matches_languages = true;
                    echo '* ';
                  }
                  echo tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $lang[$label['languages_id']]['directory'] . '/images/' . $lang[$label['languages_id']]['image'], $lang[$label['languages_id']]['name']) . '&nbsp;' . $label['epf_label'] . '<br />';
                }
                ?>
                </td>
                <td class="dataTableContent" align="center">
<?php
      if ($this_matches_languages) {
        echo tep_draw_radio_field('link_to', $epf['epf_id']);
      } else {
        echo TEXT_NOT_LINKABLE;
      }
?>
                </td>
              </tr>
<?php
}
?>
            </table>
<?php
        if (!$any_matching_languages) {
          $error = true;
          $messages[] = ERROR_NONE_LINKABLE;
        }
        if ($error) {
          if (!empty($messages)) {
            echo '<table class="error" width="100%">' . "\n";
            foreach ($messages as $message) {
              echo '<tr><td>' . $message . "</td></tr>\n";
            }
            echo "</table>\n";
          }
        } else {
          echo '<p>' . tep_draw_input_field('', BUTTON_CREATE_LINK, 'alt="' . BUTTON_CREATE_LINK . '"', false, 'submit') . '&nbsp;&nbsp;';
        }
        echo tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $eid) ) . "\n" . "</form></p>\n";
        echo "</td></tr>\n";
      } elseif ($action == 'unlink') { // Confirm field unlink
        $query = tep_db_query("select * from " . TABLE_EPF . " where epf_id = " . (int)$eid);
        $field_info = tep_db_fetch_array($query);
        echo '<tr><td><p class="pageHeading">' . sprintf(HEADING_UNLINK, $eid, $field_info['epf_links_to']) . "</p>\n";
        $used = 0;
        if (!$field_info['epf_has_linked_field'] || ($field_info['epf_links_to'] == 0)) {
          $messageStack->add_session(ERROR_NOT_LINKED, 'error');
          tep_redirect(tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $eid));
        }
        if ($field_info['epf_multi_select']) {
          $check_query = tep_db_query('select count(value_id) as total from ' . TABLE_EPF_VALUES . ' where value_depends_on != 0 and epf_id =' . (int)$eid);
        } else {
          $check_query = tep_db_query('select count(value_id) as total from ' . TABLE_EPF_VALUES . ' where value_depends_on != 0 and epf_id =' . (int)$field_info['epf_links_to']);
        }
        $check = tep_db_fetch_array($check_query);
        $used = $check['total']; // how many values are linked
        echo '<p>' . sprintf(TEXT_NUM_LINKED, $used) . "</p>\n";
        if ($double_check == 'no') {
          echo '<p>' . TEXT_SURE_UNLINK . (($used > 0) ? TEXT_LINKS_GONE : '') . "</p>\n";
          echo '<p>' . tep_draw_form('yes', FILENAME_EXTRA_FIELDS, 'confirm=yes&action=unlink&eid=' . $eid . '&used=' . $used) . tep_draw_input_field('yes', TEXT_YES, 'alt="' . TEXT_YES . '"', false, 'submit') . '</form>&nbsp;&nbsp;';
          echo tep_draw_form('no', FILENAME_EXTRA_FIELDS, 'eid=' . $eid) . tep_draw_input_field('no', TEXT_NO, 'alt="' . TEXT_NO . '"', false, 'submit') . "</form></p>\n";
        } else {
          echo '<p><b>' . TEXT_CONFIRM_UNLINK . "</b></p>\n";
          echo '<p>' . tep_draw_form('yes', FILENAME_EXTRA_FIELDS, 'confirm=yes&action=unlink&eid=' . $eid) . tep_draw_input_field('yes', TEXT_YES, 'alt="' . TEXT_YES . '"', false, 'submit') . '</form>&nbsp;&nbsp;';
          echo tep_draw_form('no', FILENAME_EXTRA_FIELDS, 'eid=' . $eid) . tep_draw_input_field('no', TEXT_NO, 'alt="' . TEXT_NO . '"', false, 'submit') . "</form></p>\n";
        }
        echo "</td></tr>\n";
      } else { /* display list of fields */?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LABEL; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
$field_query = tep_db_query("select * from " . TABLE_EPF . " order by epf_order");
$selected_labels = array();
while ($epf = tep_db_fetch_array($field_query)) {
  if ($eid == '') $eid = $epf['epf_id'];
  if ($epf['epf_id'] == $eid) {
    echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $eid . '&action=edit') . '\'">' . "\n";
    $selected = $epf;
  } else {
    echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $epf['epf_id']) . '\'">' . "\n";
  }
?>
                <td class="dataTableContent"><?php echo $epf['epf_id']; ?></td>
                <td class="dataTableContent">
                <?php $label_query = tep_db_query("select languages_id, epf_label from " . TABLE_EPF_LABELS . " where (epf_id = " . (int)$epf['epf_id'] . ") and epf_active_for_language");
                while ($label = tep_db_fetch_array($label_query)) {
                  if ($epf['epf_id'] == $eid) $selected_labels[] = $label;
                  echo tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $lang[$label['languages_id']]['directory'] . '/images/' . $lang[$label['languages_id']]['image'], $lang[$label['languages_id']]['name']) . '&nbsp;' . $label['epf_label'] . '<br />';
                }
                ?>
                </td>
                <td class="dataTableContent" align="right"><?php echo $epf['epf_order']; ?></td>
                <td class="dataTableContent" align="center">
<?php
      if ($epf['epf_status'] == '1') {
        echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_EXTRA_FIELDS, 'action=setflag&flag=0&eid=' . $epf['epf_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
      } else {
        echo '<a href="' . tep_href_link(FILENAME_EXTRA_FIELDS, 'action=setflag&flag=1&eid=' . $epf['epf_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
      }
?>
                </td>
                <td class="dataTableContent" align="right"><?php if ($epf['epf_id'] == $eid) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $epf['epf_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
}
?>
            </table></td>
<?php // build information box contents
  $heading = array();
  $contents = array();
  if (isset($selected)) {
    $heading[] = array('text' => TABLE_HEADING_ID . ' ' . $selected['epf_id']);
    foreach ($selected_labels as $label) {
      $heading[] = array('text' => $lang[$label['languages_id']]['name'] . ': ' . $label['epf_label']);
      if ($label['languages_id'] == $languages_id) $admin_language = $languages_id;
    }
    $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $eid . '&action=edit')) . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link(FILENAME_EXTRA_FIELDS, 'eid=' . $eid . '&action=delete')));
    if ($selected['epf_uses_value_list']) {
      $contents[] = array('align' => 'center', 'text' => tep_draw_form('edit_values', FILENAME_EXTRA_VALUES, 'list_id=' . $selected['epf_id'] . '_' . $admin_language) . tep_draw_input_field('edit_values', BUTTON_EDIT_VALUES, 'alt="' . BUTTON_EDIT_VALUES . '"', false, 'submit') . '</form>');
    }
    if ($selected['epf_has_linked_field']) {
      $contents[] = array('align' => 'center', 'text' => tep_draw_form('unlink', FILENAME_EXTRA_FIELDS, 'eid=' . $eid . '&action=unlink') . tep_draw_input_field('unlink', BUTTON_UNLINK, 'alt="' . BUTTON_UNLINK . '"', false, 'submit') . '</form>');
    } elseif ($selected['epf_uses_value_list']) { // check to see if a field link can be made before displaying button
      $query = tep_db_query('select count(epf_id) as total from ' . TABLE_EPF . ' where epf_uses_value_list and !epf_has_linked_field and epf_multi_select = ' . ($selected['epf_multi_select'] ? '0' : '1'));
      $check = tep_db_fetch_array($query);
      if ($check['total'] > 0) {
        $contents[] = array('align' => 'center', 'text' => tep_draw_form('link', FILENAME_EXTRA_FIELDS, 'eid=' . $eid . '&action=link') . tep_draw_input_field('link', BUTTON_LINK, 'alt="' . BUTTON_LINK . '"', false, 'submit') . '</form>');      
      }
    }
    $contents[] = array('text' => TABLE_HEADING_STATUS . ': ' . ($selected['epf_status'] ? TEXT_ENABLED : TEXT_DISABLED));
    $contents[] = array('text' => TEXT_ADMIN_AVAILABLE . ': ' . (($selected['epf_status'] || $selected['epf_show_in_admin']) ? TEXT_ENABLED : TEXT_DISABLED));
    $contents[] = array('text' => ENTRY_ORDER . $selected['epf_order']);
    if (!$selected['epf_uses_value_list'] && !$selected['epf_textarea']) $contents[] = array('text' => TEXT_SIZE . $selected['epf_size']);
    $contents[] = array('text' => TEXT_SEARCHABLE . ($selected['epf_advanced_search'] ? TEXT_YES : TEXT_NO));
    $contents[] = array('text' => ENTRY_LISTING . ($selected['epf_show_in_listing'] ? TEXT_YES : TEXT_NO));
    $contents[] = array('text' => TEXT_META . ($selected['epf_use_as_meta_keyword'] ? TEXT_YES : TEXT_NO));   
    $contents[] = array('text' => ENTRY_VALUE_LIST . ($selected['epf_uses_value_list'] ? TEXT_YES : TEXT_NO)); 
    if ($selected['epf_uses_value_list']) {
      $contents[] = array('text' => TEXT_USER_SELECTS . ($selected['epf_multi_select'] ? TEXT_MULTIPLE_VALUE : TEXT_SINGLE_VALUE)); 
      $contents[] = array('text' => TEXT_SELECTED_BY . ($selected['epf_multi_select'] ? ($selected['epf_checked_entry'] ? TEXT_CHECKBOX : TEXT_DROPDOWN) : ($selected['epf_checked_entry'] ? TEXT_RADIO : TEXT_DROPDOWN))); 
      $contents[] = array('text' => TEXT_RESTRICTS . ($selected['epf_use_to_restrict_listings'] ? TEXT_YES : TEXT_NO)); 
      $contents[] = array('text' => TEXT_SHOW_PARENTS . ($selected['epf_show_parent_chain'] ? TEXT_YES : TEXT_NO)); 
      $contents[] = array('text' => TEXT_SEARCH_BOX . ($selected['epf_quick_search'] ? TEXT_YES : TEXT_NO)); 
      $contents[] = array('text' => TEXT_COLUMNS . $selected['epf_num_columns']);
    }
    $display = TEXT_TEXT;
    if ($selected['epf_uses_value_list']) {
      if ($selected['epf_value_display_type'] == 2) {
        $display = TEXT_IMAGE_TEXT;
      } elseif ($selected['epf_value_display_type'] == 1) {
        $display = TEXT_IMAGE;
      }
    } else {
      $contents[] = array('text' => ENTRY_TEXT_ENTRY . ($selected['epf_textarea'] ? TEXT_MULTILINE : TEXT_SINGLE_LINE));      
    }
    $contents[] = array('text' => TEXT_DISPLAY_TYPE . $display);
    if ($selected['epf_has_linked_field']) {
      $contents[] = array('text' => TEXT_LINKED_TO . $selected['epf_links_to']);
    }
  }
// display information box if it exists
  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";
    $box = new box;
    echo $box->infoBox($heading, $contents);
    echo '            </td>' . "\n";
  }
?>
  </tr>
  </table>
</div>
<?php
  }

  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>