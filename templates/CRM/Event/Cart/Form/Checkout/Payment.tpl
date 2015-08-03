{include file="CRM/common/TrackingFields.tpl"}

<table class="cart__checkout-events">
  <thead>
    <tr>
      <th class="event-title">
  {ts}Event{/ts}
      </th>
      <th class="participants-column">
  {ts}Participant(s){/ts}
      </th>
      <th class="cost">
  {ts}Price{/ts}
      </th>
      <th class="amount">
  {ts}Total{/ts}
      </th>
    </tr>
  </thead>
  <tbody>
    {foreach from=$line_items item=line_item}
      <tr class="event-line-item {$line_item.class}">
  <td class="event-title">
    {$line_item.event->title} ({if $line_item.multiple_day.multiple_day_description}{$line_item.multiple_day.multiple_day_description}{if $line_item.multiple_day.multiple_time_description} {$line_item.multiple_day.multiple_time_description}{/if}{else}{$line_item.event->start_date}{/if})
  </td>
  <td class="participants-column">
    {$line_item.num_participants}<br/>
    {if $line_item.num_participants > 0}
      <div class="participants" style="padding-left: 10px;">
        {foreach from=$line_item.participants item=participant}
      {$participant.display_name}<br />
        {/foreach}
      </div>
    {/if}
    {if $line_item.num_waiting_participants > 0}
      {ts}Waitlisted:{/ts}<br/>
      <div class="participants" style="padding-left: 10px;">
        {foreach from=$line_item.waiting_participants item=participant}
      {$participant.display_name}<br />
        {/foreach}
      </div>
    {/if}
  </td>
  <td class="cost">
    {$line_item.cost|crmMoney:$currency|string_format:"%10s"}
  </td>
  <td class="amount">
    &nbsp;{$line_item.amount|crmMoney:$currency|string_format:"%10s"}
  </td>
      </tr>
    {/foreach}
  </tbody>
  <tfoot>
  {if $discounts}
    <tr>
      <td>
      </td>
      <td>
      </td>
      <td>
  {ts}Subtotal:{/ts}
      </td>
      <td>
  &nbsp;{$sub_total|crmMoney:$currency|string_format:"%10s"}
      </td>
    </tr>
  {foreach from=$discounts key=myId item=i}
    <tr>
      <td>{$i.title}
      </td>
      <td>
      </td>
      <td>
      </td>
      <td>
   -{$i.amount|crmMoney:$currency|string_format:"%10s"}
      </td>
    </tr>
   {/foreach}
   {/if}
    <tr class="total">
      <td>
      </td>
      <td>
      </td>
      <td class="total">
  {ts}Total:{/ts}
      </td>
      <td class="total">
  &nbsp;{$total|crmMoney:$currency|string_format:"%10s"}
      </td>
    </tr>
  </tfoot>
</table>
{if $payment_required == true}
{include file='CRM/Core/BillingBlock.tpl'}
{/if}
{if $collect_billing_email == true}

<div class="{$form.billing_contact_email.name}-group">
  <div class="crm-section  {$form.billing_contact_email.name}-section">
    <div class="label">{$form.billing_contact_email.label}</div>
    <div class="content">{$form.billing_contact_email.html}</div>
    <div class="clear"></div>
  </div>
</div>

{if $administrator}
<div class="admin_only-group">
<h4>{ts}Staff use only{/ts}</h4>
<div class="crm-section {$form.pay_by_check.name}-section">
  <div class="label">{$form.pay_by_check.label}</div>
  <div class="content">{$form.pay_by_check.html}
  </div>
  <div class="clear"></div>
</div>
<div class="crm-section {$form.payment_completed.name}-section">
  <div class="label">{$form.payment_completed.label}</div>
  <div class="content">{$form.payment_completed.html}</div>
  <div class="clear"></div>
</div>
<div class="crm-section {$form.check_number.name}-section">
  <div class="label">{$form.check_number.label}</div>
  <div class="content">{$form.check_number.html}</div>
  <div class="clear"></div>
</div>
</div>
{/if}

{/if}

<script type="text/javascript">
var pay_later_sel = "input#{$form.is_pay_later.name}";
{literal}
CRM.$(function($) {
  function togglePayLater() {
    var is_pay_later = $(pay_later_sel).prop("checked");
    $(".credit_card_info-group").toggle(!is_pay_later);
    $(".check_number-section, .payment_completed-section").toggle(is_pay_later);
  }
  $(pay_later_sel).change(function() {
    togglePayLater();
  });
  togglePayLater();
});
{/literal}
</script>

<div class="crm-submit-buttons-wrapper">
  <div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>

{include file="CRM/Event/Cart/Form/viewCartLink.tpl"}
