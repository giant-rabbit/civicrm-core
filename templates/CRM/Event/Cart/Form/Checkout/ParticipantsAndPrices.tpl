{include file="CRM/common/TrackingFields.tpl"}

{literal}
<script type="text/javascript">
function append_add_link(event_id) {
  var last_child = cj('#event_' + event_id + '_participants .participant').last().find('.participant-inner');
  cj('#event_' + event_id + '_participants .link-add').appendTo(last_child);
}
</script>
{/literal}
{capture assign='reqMark'}<span class="marker"  title="{ts}This field is required.{/ts}">*</span>{/capture}

{if $contact}
<h4 class="welcome-message">
    {ts 1=$contact.display_name}Welcome, %1{/ts} <span>(<a href="{crmURL p='civicrm/event/cart_checkout' q="&cid=0&reset=1"}" title="{ts}Click here to register a different person for this event.{/ts}">{ts 1=$contact.first_name}Not %1?{/ts}</a>)</span></h4>
{/if}

{foreach from=$events_in_carts key=index item=event_in_cart}
 {if !$event_in_cart.main_conference_event_id}
  {assign var=event_id value=$event_in_cart->event_id}
  <h3 class="event-title">
    {$event_in_cart->event->title} ({if $multiple_day.$event_id.multiple_day_description}{$multiple_day.$event_id.multiple_day_description}{if $multiple_day.$event_id.multiple_time_description} {$multiple_day.$event_id.multiple_time_description}{/if}{else}{$event_in_cart->event->start_date|date_format:"%m/%d/%Y %l:%M%p"}{/if})
  </h3>
  <fieldset class="event_form">
    <div class="participants crm-section" id="event_{$event_in_cart->event_id}_participants">
      {foreach from=$event_in_cart->participants item=participant}
  {include file="CRM/Event/Cart/Form/Checkout/Participant.tpl"}
      {/foreach}
      <a class="link-add" href="#" onclick="add_participant({$event_in_cart->event_cart->id}, {$event_in_cart->event_id}); return false;">{ts}(+) Add Another Participant{/ts}</a>
      {literal}<script
type="text/javascript">append_add_link({/literal}{$event_in_cart->event_id}{literal})</script>{/literal}
    </div>
    {if $event_in_cart->event->is_monetary }
      <div class="price_choices crm-section">
  {foreach from=$price_fields_for_event.$event_id key=price_index item=price_field_name}
  <div class="label">
      {$form.$price_field_name.label}
    </div>
    <div class="content">
      {$form.$price_field_name.html|replace:'/label>&nbsp;':'/label><br>'}
    </div>
  {/foreach}
      </div>
    {else}
      <p>{ts}There is no charge for this event.{/ts}</p>
    {/if}
  </fieldset>
 {/if}
{/foreach}

<div class="crm-submit-buttons-wrapper">
  <div class="crm-submit-buttons">
    {capture assign=cartURL}{crmURL p='civicrm/event/view_cart' q="reset=1"}{/capture}
    {ts 1=$cartURL}<a class='button back' href='%1'><< Back</a>{/ts}
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>

{include file="CRM/Event/Cart/Form/viewCartLink.tpl"}

{literal}
<script type="text/javascript">
//<![CDATA[
function add_stripes() {
  cj('.participants .participant').removeClass('odd-row even-row');
  cj('.participants .participant:even').addClass('odd-row');
  cj('.participants .participant:odd ').addClass('even-row');
}

add_stripes();
function add_participant( cart_id, event_id ) {
  var max_index = 0;
  var matcher = new RegExp("event_" + event_id + "_participant_(\\d+)");

  cj('#event_' + event_id + '_participants .participant').each(
    function(index) {
      matches = matcher.exec(cj(this).attr('id'));
      index = parseInt(matches[1]);
      if (index > max_index)
      {
        max_index = index;
      }
    }
  );

  // FIXME: this get should be a post according to restful standards
  cj.get(CRM.url("civicrm/ajax/event/add_participant_to_cart", {cart_id: cart_id,  event_id: event_id}),
    function(data) {
      cj('#event_' + event_id + '_participants').append(data).trigger('crmLoad');
      append_add_link(event_id);
      add_stripes();
    }
  );
}

function delete_participant( event_id, participant_id )
{
  // FIXME: this get should be a post according to restful standards
  cj('#event_' + event_id + '_participants .link-add').appendTo('#event_' +
event_id + '_participants');
  cj('#event_' + event_id + '_participant_' + participant_id).remove();
  cj.get(CRM.url("civicrm/ajax/event/remove_participant_from_cart", {id: participant_id}));
  append_add_link(event_id);
  add_stripes();
}


//XXX missing
cj('#ajax_error').ajaxError(
  function( e, xrh, settings, exception ) {
    cj(this).append('<div class="error">{/literal}{ts escape='js'}Error adding a participant at{/ts}{literal} ' + settings.url + ': ' + exception);
  }
);
//]]>
</script>
{/literal}
