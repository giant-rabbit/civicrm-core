<?php

/**
 * Class CRM_Event_Cart_Page_CheckoutAJAX
 */
class CRM_Event_Cart_Page_CheckoutAJAX {
  function add_participant_to_cart() {
    $transaction = new CRM_Core_Transaction();
    $cart_id = CRM_Utils_Request::retrieve('cart_id', 'Integer');
    $event_id = CRM_Utils_Request::retrieve('event_id', 'Integer');

    $cart = CRM_Event_Cart_BAO_Cart::find_by_id($cart_id);

    $params = array
    (
      'cart_id' => $cart->id,
      'contact_id' => CRM_Event_Cart_Form_Cart::find_or_create_contact(),
      'event_id' => $event_id,
    );
    $participant = CRM_Event_Cart_BAO_MerParticipant::create($params);
    $participant->save();

    $form = new CRM_Core_Form();
    $pform = new CRM_Event_Cart_Form_MerParticipant($participant);
    $pform->appendQuickForm($form);

    $renderer = $form->getRenderer();

    $config = CRM_Core_Config::singleton();
    $templateDir = $config->templateDir;
    if (is_array($templateDir)) {
      $templateDir = array_pop($templateDir);
    }
    $requiredTemplate = file_get_contents($templateDir . '/CRM/Form/label.tpl');
    $renderer->setRequiredTemplate($requiredTemplate);

    $form->accept($renderer);
    $template = CRM_Core_Smarty::singleton();
    $template->assign('form', $renderer->toArray());
    $template->assign('participant', $participant);
    $output = $template->fetch("CRM/Event/Cart/Form/Checkout/Participant.tpl");
    $transaction->commit();
    echo $output;
    CRM_Utils_System::civiExit();
  }

  function remove_participant_from_cart() {
    $id = CRM_Utils_Request::retrieve('id', 'Integer');
    $participant = CRM_Event_Cart_BAO_MerParticipant::get_by_id($id);
    $cart_id = $participant->cart_id;
    $old_contact_id = $participant->contact_id;
    $participant->delete();

    $cart = CRM_Event_Cart_BAO_Cart::find_by_id($cart_id);
    $cart->load_associations();
    $delete = TRUE;
    foreach ($cart->get_main_event_participants() as $participant)
    {
      if ($participant->contact_id == $old_contact_id)
      {
        $delete = FALSE;
        break;
      }
    }
    if ($delete)
    {
      CRM_Event_Cart_Form_Cart::remove_temporary_contact_if_exists($contact_id);
    }

    CRM_Utils_System::civiExit();
  }
}

