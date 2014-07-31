<?php
require_once 'CiviTest/CiviUnitTestCase.php';

/**
   * Class CRM_Core_BAO_NavigationTest
    */
class CRM_Core_BAO_NavigationTest extends CiviUnitTestCase {
  function testGetNavItemByUrl() {
    $random_string = substr(sha1(rand()), 0, 7);
    $name = "Test Menu Link {$random_string}";
    $url = "civicrm/test/{$random_string}";
    $url_params = "reset=1";
    $params = array(
      'name' => $name,
      'label' => ts($name),
      'url' => "{$url}?{$url_params}",
      'parent_id' => NULL,
      'is_active' => TRUE,
      'permission' => array(
        'access CiviCRM',
      ),
    );
    $nav = CRM_Core_BAO_Navigation::add($params);
    $new_nav = CRM_Core_BAO_Navigation::getNavItemByUrl($url, $url_params);
    $this->assertObjectHasAttribute('id', $new_nav);
    $this->assertNotNull($new_nav->id);
    $new_nav->delete();
  }
}
