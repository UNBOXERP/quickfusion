<?php
require_once(DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php');
require_once(DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
require_once(DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php');
require_once(DOL_DOCUMENT_ROOT . '/compta/paiement/class/paiement.class.php');
require_once(DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php');

class factura extends Facture
{
    public $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function CreatePayment(Facture $factura, $idpago)
    {
        global $conf, $user;

        $error = 0;
        if (!$factura->fk_account) {
            setEventMessage('The Invoice ' . $factura->ref . ' do not have bank account','errors');
            return;
        }
        $this->db->begin();
        $socid = $factura->socid;
        $thirdparty = new Societe($this->db);
        if ($socid > 0) $thirdparty->fetch($socid);

        // Creation of payment line
        $paiement = new Paiement($this->db);
        //TODO Razmi cambiar por fecha actual
        //$paiement->datepaye = $factura->date;
        $now = dol_now();
        $paiement->datepaye = $this->db->idate($now);
        $paiement_qty=$factura->getRemainToPay(0);
        $paiement->amounts = array($factura->id => ($paiement_qty)); // Array with all payments dispatching with invoice id
        $paiement->total_ht = $factura->total_ht; // Array with all payments dispatching with invoice id
        $paiement->total_ttc = $factura->total_ttc; // Array with all payments dispatching with invoice id
        $paiement->multicurrency_amounts = $factura->multicurrency_total_ttc; // Array with all payments dispatching
        //$paiement->paiementid = dol_getIdFromCode($this->db, 'CHQ', 'c_paiement', 'code', 'id', 1);
        $paiement->paiementid = $idpago;
        $paiement->num_payment = '';
        $paiement->note_private = $factura->note_private;

        if (!$error) {
            // Create payment and update this->multicurrency_amounts if this->amounts filled or
            // this->amounts if this->multicurrency_amounts filled.
            //Verify payment before create

            $paiement_id = $paiement->create($user, 1, $thirdparty); // This include closing invoices and regenerating documents
            if ($paiement_id < 0) {
                setEventMessages($paiement->error, $paiement->errors, 'errors');
                $error++;
            }
        }

        if (!$error) {
            $label = '(CustomerInvoicePayment)';
            $result = $paiement->addPaymentToBank($user, 'payment', $label, $factura->fk_account ?? 1, '', '');
            if ($result < 0) {
                setEventMessages($paiement->error, $paiement->errors, 'errors');
                $error++;
            }
        }

        if (!$error) {
            $this->db->commit();
            setEventMessage('Payment '.$paiement->ref.' created successfully','mesgs');

        } else {
            $this->db->rollback();
        }
    }
}