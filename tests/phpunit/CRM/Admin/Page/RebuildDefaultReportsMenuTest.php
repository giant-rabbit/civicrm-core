<?php
require_once 'CiviTest/CiviUnitTestCase.php';

/**
 * Class CRM_Admin_Page_RebuildDefaultReportsMenuTest
 */
class CRM_Admin_Page_RebuildDefaultReportsMenuTest extends CiviUnitTestCase {
  function testUpdateExistingReportMenuLink() {
    $url = 'civicrm/report/instance/1';
    $url_params = 'reset=1';
    $existing_nav = CRM_Core_BAO_Navigation::getNavItemByUrl($url, $url_params);
    $existing_nav->parent_id = 1;
    $existing_nav->save();
    CRM_Admin_Page_RebuildDefaultReportsMenu::rebuildReportsMenu();
    $parent_url = 'civicrm/report/list';
    $parent_url_params = 'compid=99&reset=1';
    $parent_nav = CRM_Core_BAO_Navigation::getNavItemByUrl($parent_url, $parent_url_params);
    $this->assertNotEquals($parent_nav->id, 1);
    $changed_existing_nav = new CRM_Core_BAO_Navigation();
    $changed_existing_nav->id = $existing_nav->id;
    $changed_existing_nav->find(TRUE);
    $this->assertEquals($changed_existing_nav->parent_id, $parent_nav->id);
  }

  function testCreateMissingReportMenuItemLink() {
    $url = 'civicrm/report/instance/1';
    $url_params = 'reset=1';
    $existing_nav = CRM_Core_BAO_Navigation::getNavItemByUrl($url, $url_params);
    $existing_nav_id = $existing_nav->id;
    $existing_nav->delete();
    CRM_Admin_Page_RebuildDefaultReportsMenu::rebuildReportsMenu();
    $new_nav = CRM_Core_BAO_Navigation::getNavItemByUrl($url, $url_params);
    $this->assertObjectHasAttribute('id', $new_nav);
    $this->assertNotNull($new_nav->id);
    $this->assertNotEquals($new_nav->id, $existing_nav_id);
  }
}
