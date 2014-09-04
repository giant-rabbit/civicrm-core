{*
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
*}
{if !$printOnly} {* NO print section starts *}
  <div class="crm-tasks">
    <table style="border:0;">
      <tr>
        <td>
          <table class="form-layout-compressed">
            <tr>
              {if $mode neq 'template'}
                <td class="crm-actions-list-wrapper">
                  <a class="crm-actions-list-link button" href="#"><span><div class="icon dropdown-icon"></div>Actions</span></a>
                  <div class="crm-actions-list">
                    <ul>
                      {if $csvButton}
                        <li>{$form.$csvButton.html}</li>
                      {/if}
                      <li>{$form.$pdfButton.html}</li>
                      <li>{$form.$printButton.html}</li>
                      {if $tabularButton}
                        <li>{$form.$tabularButton.html}</li>
                      {/if}
                      {if $barChartButton AND $chartSupported}
                        <li>{$form.$barChartButton.html}</li>
                      {/if}
                      {if $pieChartButton AND $chartSupported}
                        <li>{$form.$pieChartButton.html}</li>
                      {/if}
                      {if $settingsUrl}
                        <li><a href="{$settingsUrl}">Edit report settings</a></li>
                      {/if}
                    </ul>
                  </div>
                </td>
                <td class='save'>{$form.$saveButton.html}&nbsp;&nbsp;</td>
                <td>{$form.$copyButton.html}&nbsp;&nbsp;</td>
              {/if}
              <td>{$form.$viewButton.html}&nbsp;&nbsp;</td>
              {if $mode eq 'template'}
                <td>{$form.$createButton.html}&nbsp;&nbsp;</td>
              {/if}
              {if $instanceUrl}
                <td>&nbsp;&nbsp;&raquo;&nbsp;<a href="{$instanceUrl}">{ts}Existing report(s) from this template{/ts}</a></td>
              {/if}
            </tr>
        </table>
      </td>
      <td>
        <table class="form-layout-compressed" align="right">
          {if $form.groups AND $groupButton AND $mode neq 'template'}
            <tr>
              <td>
                {$form.groups.html}{$form.$groupButton.html}
                <script type="text/javascript">
                  {literal}
                    (function($) {
                      $('#groups').val('').change(function() {
                        CRM.confirm({
                          message: ts({/literal}'{ts escape='js' 1='<em>%1</em>'}Add all contacts to %1 group?{/ts}'{literal}, {1: $('option:selected', '#groups').text()})
                        })
                        .on('crmConfirm:yes', function() {
                          $('#groups').siblings(':submit').click();
                        });
                        $('#groups').select2('val', '');
                      });
                    })(CRM.$);
                  {/literal}
                </script>
              </td>
            </tr>
          {/if}
        </table>
      </td>
    </tr>
  </table>
  {$form.charts.html}
</div>
{literal}
  <script type="text/javascript" >
  CRM.$(function($) {
    $('.save input.crm-form-submit').click( function() {
      return window.confirm('{/literal}{ts}Save will overwrite the default criteria for the "{/ts}{$reportTitle}{ts}" report. If you do not wish to do this, click "Cancel" and "Save a Copy" instead.{/ts}{literal}');
    });
  });
  </script>
{/literal}
{/if} {* NO print section ends *}
