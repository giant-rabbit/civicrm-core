<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.5                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2014                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2014
 * $Id$
 *
 */

/**
 * Page for resetting the reports navigation menu to the default.
 */
class CRM_Admin_Page_RebuildDefaultReportsMenu extends CRM_Core_Page {
  
  function run() {
    self::rebuildReportsMenu();
    CRM_Core_Invoke::rebuildMenuAndCaches();
    CRM_Utils_System::redirect(
      CRM_Utils_System::url(
        'civicrm/admin/menu',
        'reset=1'
      )
    );
  }

  function rebuildReportsMenu() {
    $config = CRM_Core_Config::singleton();
    $domain_id = $config->domainID();
    $component_to_nav_name = array(
      'CiviContact' => 'Contact Reports',
      'CiviContribute' => 'Contribution Reports',
      'CiviMember' => 'Membership Reports',
      'CiviEvent' => 'Event Reports',
      'CiviPledge' => 'Pledge Reports',
      'CiviGrant' => 'Grant Reports',
      'CiviMail' => 'Mailing Reports',
      'CiviCampaign' => 'Campaign Reports',
    );

    // Create or update the top level Reports link.
    $reports_nav = self::createOrUpdateTopLevelReportsNavItem();

    // Get all active report instances grouped by component.
    $components = self::getAllActiveReportsByComponent($domain_id);
    foreach ($components as $component_id => $component) {
      // Create or update the per component reports links.
      $component_nav_name = $component['name'];
      if (isset($component_to_nav_name[$component_nav_name])) {
        $component_nav_name = $component_to_nav_name[$component_nav_name];
      }
      $permission = "access {$component['name']}";
      if ($component['name'] === 'CiviContact') {
        $permission = "administer CiviCRM";
      }
      elseif ($component['name'] === 'CiviCampaign') {
        $permission = "access CiviReport";
      }
      $component_nav = self::createOrUpdateReportNavItem($component_nav_name, 'civicrm/report/list', "compid={$component_id}&reset=1", $reports_nav->id, $permission);
      foreach ($component['reports'] as $report_id => $report) {
        // Create or update the report instance links.
        $report_nav = self::createOrUpdateReportNavItem($report['title'], $report['url'], 'reset=1', $component_nav->id, $report['permission']);
        // Update the report instance to include the navigation id.
        $query = "UPDATE civicrm_report_instance SET navigation_id = %1 WHERE id = %2";
        $params = array(
          1 => array($report_nav->id, 'Integer'),
          2 => array($report_id, 'Integer'),
        );
        CRM_Core_DAO::executeQuery($query, $params);
      }
    }
    
    // Create or update the All Reports link.
    $all_reports_nav = self::createOrUpdateReportNavItem('All Reports', 'civicrm/report/list', 'reset=1', $reports_nav->id, 'access CiviReport');
    // Create or update the My Reports link.
    $my_reports_nav = self::createOrUpdateReportNavItem('My Reports', 'civicrm/report/list', 'myreports=1&reset=1', $reports_nav->id, 'access CiviReport');
  }

  function getAllActiveReportsByComponent($domain_id) {
    $sql = "
      SELECT 
        civicrm_report_instance.id, civicrm_report_instance.title, civicrm_report_instance.permission, civicrm_component.name, civicrm_component.id AS component_id
      FROM 
        civicrm_option_group
      LEFT JOIN 
        civicrm_option_value ON civicrm_option_value.option_group_id = civicrm_option_group.id AND civicrm_option_group.name = 'report_template'
      LEFT JOIN 
        civicrm_report_instance ON civicrm_option_value.value = civicrm_report_instance.report_id
      LEFT JOIN 
        civicrm_component ON civicrm_option_value.component_id = civicrm_component.id
      WHERE 
        civicrm_option_value.is_active = 1
      AND 
        civicrm_report_instance.domain_id = %1
      ORDER BY civicrm_option_value.weight";

    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1 => array($domain_id, 'Integer'),
    ));
    $rows = array();
    while ($dao->fetch()) {
      $component_name = is_null($dao->name) ? 'CiviContact' : $dao->name;
      $component_id = is_null($dao->component_id) ? 99 : $dao->component_id;
      $rows[$component_id]['name'] = $component_name;
      $rows[$component_id]['reports'][$dao->id] = array(
        'title' => $dao->title,
        'url' => "civicrm/report/instance/{$dao->id}",
        'permission' => $dao->permission,
      );
    }
    return $rows;
  }

  function createOrUpdateTopLevelReportsNavItem() {
    $id = NULL;
    $query = "SELECT id FROM civicrm_navigation WHERE name = 'Reports'";
    $dao = CRM_Core_DAO::executeQuery($query);
    $dao->fetch();
    if (isset($dao->id)) {
      $id = $dao->id;
    }
    $nav = self::createReportNavItem('Reports', NULL, NULL, NULL, 'access CiviReport', $id);
    return $nav;
  }

  function createOrUpdateReportNavItem($name, $url, $url_params, $parent_id, $permission) {
    $id = NULL;
    $existing_nav = CRM_Core_BAO_Navigation::getNavItemByUrl($url, $url_params);
    if ($existing_nav) {
      $id = $existing_nav->id;
    }
    $nav = self::createReportNavItem($name, $url, $url_params, $parent_id, $permission, $id);
    return $nav;
  }

  function createReportNavItem($name, $url, $url_params, $parent_id, $permission, $id) {
    if ($url !== NULL) {
      $url = "{$url}?{$url_params}";
    }
    $params = array(
      'name' => $name,
      'label' => ts($name),
      'url' => $url,
      'parent_id' => $parent_id,
      'is_active' => TRUE,
      'permission' => array(
        $permission,
      ),
    );
    if ($id !== NULL) {
      $params['id'] = $id;
    }
    $nav = CRM_Core_BAO_Navigation::add($params);
    return $nav;
  }
}
