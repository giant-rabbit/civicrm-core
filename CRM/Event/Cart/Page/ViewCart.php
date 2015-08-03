<?php

/**
 * Class CRM_Event_Cart_Page_ViewCart
 */
class CRM_Event_Cart_Page_ViewCart extends CRM_Core_Page {
  function run() {
    $transaction = new CRM_Core_Transaction();

    $cart = CRM_Event_Cart_BAO_Cart::find_or_create_for_current_session();
    $cart->load_associations();

    $events_to_be_removed = self::getEventsWithClosedRegistration($cart->get_main_events_in_carts());

    foreach ($events_to_be_removed as $event_in_cart_id) {
      $removed_event = $cart->remove_event_in_cart($event_in_cart_id);
      $removed_event_title = $removed_event->event->title;
      CRM_Core_Session::setStatus(ts("<b>%1</b> has been removed from your cart because registration is closed for this event.", array(1 => $removed_event_title)), '', 'info');
    }

    $this->assign('events_in_carts', $cart->get_main_events_in_carts());
    $this->assign('events_count', count($cart->get_main_events_in_carts()));

    $transaction->commit();

    return parent::run();
  }

  function getEventsWithClosedRegistration($events_in_carts) {
    $events_to_be_removed = array();
    foreach ($events_in_carts as $event_in_cart) {
      $event = $event_in_cart->event;
      $event_dates = array(
        'end_date' => $event->end_date,
        'registration_start_date' => $event->registration_start_date,
        'registration_end_date' => $event->registration_end_date,
      );
      if (!CRM_Event_BAO_Event::validRegistrationDate($event_dates)) {
        $events_to_be_removed[] = $event_in_cart->id;
      }
    }
    return $events_to_be_removed;
  }
}

