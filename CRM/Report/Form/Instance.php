<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_Report_Form_Instance extends CRM_Core_Form {

  public $_id;
  public $_title;
  public $_defaults = array();
  public $_navigation = array();
  public $_drilldownReport = array();


  function preProcess() {
    $this->_id = CRM_Report_Utils_Report::getInstanceID();
    $params = array('id' => $this->_id);
    CRM_Core_DAO::commonRetrieve('CRM_Report_DAO_ReportInstance', $params, $this->_defaults);
    if (!empty($this->_submitValues)) {
      $title = $this->_submitValues['title'];
    }
    else {
      $title = $this->_defaults['title'];
    }
    CRM_Utils_System::setTitle("{$title} Report Settings");
    $report_id = $this->_defaults['report_id'];
    $report_class = CRM_Report_Utils_Report::getInstanceClassFromReportId($report_id);
    $report = new $report_class();
    $this->_drilldownReport = $report->_drilldownReport;
  }

  function buildQuickForm() {
    if ($this->_id && !CRM_Report_Utils_Report::isInstanceGroupRoleAllowed($this->_id)) {
      $url = CRM_Utils_System::url('civicrm/report/list', 'reset=1');
      CRM_Core_Error::statusBounce(ts("You do not have permission to access this report's settings."), $url);
    }

    $attributes = CRM_Core_DAO::getAttribute('CRM_Report_DAO_ReportInstance');

    $this->add('text',
      'title',
      ts('Report Title'),
      $attributes['title']
    );

    $this->add('text',
      'description',
      ts('Report Description'),
      $attributes['description']
    );

    $this->add('text',
      'email_subject',
      ts('Subject'),
      $attributes['email_subject']
    );

    $this->add('text',
      'email_to',
      ts('To'),
      $attributes['email_to']
    );

    $this->add('text',
      'email_cc',
      ts('CC'),
      $attributes['email_subject']
    );

    $this->add('text',
      'row_count',
      ts('Limit Dashboard Results'),
      array('maxlength' => 64,
        'size' => 5
      )
    );

    $this->add('textarea',
      'report_header',
      ts('Report Header'),
      $attributes['header']
    );

    $this->add('textarea',
      'report_footer',
      ts('Report Footer'),
      $attributes['footer']
    );

    $this->addElement('checkbox', 'is_navigation', ts('Include Report in Navigation Menu?'), NULL,
      array('onclick' => "return showHideByValue('is_navigation','','navigation_menu','table-row','radio',false);")
    );

    $this->addElement('checkbox', 'addToDashboard', ts('Available for Dashboard?'), NULL,
      array('onclick' => "return showHideByValue('addToDashboard','','limit_result','table-row','radio',false);"));
    $this->addElement('checkbox', 'is_reserved', ts('Reserved Report?'));
    if (!CRM_Core_Permission::check('administer reserved reports')) {
      $this->freeze('is_reserved');
    }
    $this->addYesNo('add_to_my_reports', ts('Add to My Reports'));

    $config = CRM_Core_Config::singleton();
    if ($config->userFramework != 'Joomla' || $config->userFramework != 'WordPress') {
      $this->addElement('select',
        'permission',
        ts('Permission'),
        array('0' => ts('Everyone (includes anonymous)')) + CRM_Core_Permission::basicPermissions()
      );

      if (function_exists('user_roles')) {
        $user_roles_array = user_roles();
        foreach ($user_roles_array as $key => $value) {
          $user_roles[$value] = $value;
        }
        $grouprole = &$this->addElement('advmultiselect',
          'grouprole',
          ts('ACL Group/Role'),
          $user_roles,
          array(
            'size' => 5,
            'style' => 'width:240px',
            'class' => 'advmultiselect',
          )
        );
        $grouprole->setButtonAttributes('add', array('value' => ts('Add >>')));
        $grouprole->setButtonAttributes('remove', array('value' => ts('<< Remove')));
      }
    }

    $parentMenu = CRM_Core_BAO_Navigation::getNavigationList();

    $this->add('select', 'parent_id', ts('Parent Menu'), array('' => ts('-- select --')) + $parentMenu);

    foreach ($this->_drilldownReport as $reportUrl => $drillLabel) {
      $instanceList = CRM_Report_Utils_Report::getInstanceList($reportUrl);
      if (count($instanceList) > 1)
        $this->add('select', 'drilldown_id', $drillLabel, array('' => ts('- select -')) + $instanceList);
      break;
    }

    $this->addButtons(array(
        array(
          'type' => 'submit',
          'name' => ts('Save Report Settings'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
      )
    );

    $this->addFormRule(array('CRM_Report_Form_Instance', 'formRule'), $this);
  }

  static function formRule($fields, $errors, $self) {
    $errors = array();
    if (empty($fields['title'])) {
      $errors['title'] = ts('Title is a required field');
      $self->assign('instanceFormError', TRUE);
    }

    return empty($errors) ? TRUE : $errors;
  }

  /**
   * @param $form
   * @param $defaults
   */
  function setDefaultValues() {
    $defaults = $this->_defaults;
    if (!isset($defaults['permission'])) {
      $permissions = array_flip(CRM_Core_Permission::basicPermissions( ));
      $defaults['permission'] = $permissions['CiviReport: access CiviReport'];
    }
    $config = CRM_Core_Config::singleton();
    $defaults['report_header'] = $report_header = "
      <html>
        <head>
          <title>CiviCRM Report</title>
          <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
          <style type=\"text/css\">@import url({$config->userFrameworkResourceURL}css/print.css);</style>
        </head>
        <body><div id=\"crm-container\">";
    $defaults['report_footer'] = $report_footer = "<p><img src=\"{$config->userFrameworkResourceURL}i/powered_by.png\" /></p></div></body>
</html>
";
    if ($this->_id) {
      $defaults['description'] = CRM_Utils_Array::value('description', $defaults);
      $defaults['report_header'] = CRM_Utils_Array::value('header', $defaults);
      $defaults['report_footer'] = CRM_Utils_Array::value('footer', $defaults);
      if (!empty($defaults['navigation_id'])) {
        $navigationDefaults = array();
        $params = array('id' => $defaults['navigation_id']);
        CRM_Core_BAO_Navigation::retrieve($params, $navigationDefaults);
        $defaults['is_navigation'] = 1;
        $defaults['parent_id'] = CRM_Utils_Array::value('parent_id', $navigationDefaults);
        if (!empty($navigationDefaults['is_active'])) {
          $this->assign('is_navigation', TRUE);
        }
        if (!empty($navigationDefaults['id'])) {
          $this->_navigation['id'] = $navigationDefaults['id'];
          $this->_navigation['parent_id'] = $navigationDefaults['parent_id'];
        }
      }

      if (CRM_Utils_Array::value('grouprole', $defaults)) {
        foreach (explode(CRM_Core_DAO::VALUE_SEPARATOR, $defaults['grouprole']) as $value) {
          $grouproles[] = $value;
        }
        $defaults['grouprole'] = $grouproles;
      }
    }
    $defaults['add_to_my_reports'] = 0;
    if (CRM_Utils_Array::value('owner_id', $defaults) != NULL) {
      $defaults['add_to_my_reports'] = 1;
    }

    return $defaults;
  }

  /**
   * @param $form
   * @param bool $redirect
   */
  function postProcess() {
    $submitValues = $this->_submitValues;
    $id = $this->_id;
    $submitValues['instance_id'] = $id;
    if (!empty($submitValues['is_navigation'])) {
      $submitValues['navigation'] = $this->_navigation;
    }
    if (CRM_Utils_Array::value('add_to_my_reports', $submitValues) == 1) {
      $session = CRM_Core_Session::singleton();
      $contact_id = $session->get('userID');
      $submitValues['owner_id'] = $contact_id;
      $submitValues['permission'] = 'access own private reports';
      $submitValues['grouprole'] = array();
    }
    $instance = CRM_Report_BAO_ReportInstance::create($submitValues);
    CRM_Core_Session::setStatus(ts('"%1" report has been updated.', array(1 => $instance->title)), '', 'success');
    CRM_Utils_System::redirect(CRM_Utils_System::url("civicrm/report/instance/{$instance->id}", "reset=1"));
  }
}
