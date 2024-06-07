<?php
include_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';

class formu extends Form
{
    public function select_types_paiements($selected = '', $htmlname = 'paiementty...', $filtertype = '', $format = 0, $empty = 1, $noadmininfo = 0, $maxlength = 0, $active = 1, $morecss = '', $nooutput = 0)
    {

        // phpcs:enable
        global $langs, $user, $conf;

        dol_syslog(__METHOD__ . " " . $selected . ", " . $htmlname . ", " . $filtertype . ", " . $format, LOG_DEBUG);

        $filterarray = array();
        if ($filtertype == 'CRDT') $filterarray = array(0, 2, 3);
        elseif ($filtertype == 'DBIT') $filterarray = array(1, 2, 3);
        elseif ($filtertype != '' && $filtertype != '-1') $filterarray = explode(',', $filtertype);

        $this->load_cache_types_paiements();

        // Set default value if not already set by caller
        if (empty($selected) && !empty($conf->global->MAIN_DEFAULT_PAYMENT_TYPE_ID)) $selected = $conf->global->MAIN_DEFAULT_PAYMENT_TYPE_ID;
       // $out='<input type="hidden" name="idlistpaid" id="idlistpaid"/>';
        $out = '<select id="select' . $htmlname . '" class="flat selectpaymenttypes' . ($morecss ? ' ' . $morecss : '') . '" name="' . $htmlname . '">';
        if ($empty) $out .= '<option value="">&nbsp;</option>';
        foreach ($this->cache_types_paiements as $id => $arraytypes) {
            // If not good status
            if ($active >= 0 && $arraytypes['active'] != $active) continue;

            // On passe si on a demande de filtrer sur des modes de paiments particuliers
            if (count($filterarray) && !in_array($arraytypes['type'], $filterarray)) continue;

            // We discard empty line if showempty is on because an empty line has already been output.
            if ($empty && empty($arraytypes['code'])) continue;

            if ($format == 0) $out .= '<option value="' . $id . '"';
            elseif ($format == 1) $out .= '<option value="' . $arraytypes['code'] . '"';
            elseif ($format == 2) $out .= '<option value="' . $arraytypes['code'] . '"';
            elseif ($format == 3) $out .= '<option value="' . $id . '"';
            // Print attribute selected or not
            if ($format == 1 || $format == 2) {
                if ($selected == $arraytypes['code']) $out .= ' selected';
            } else {
                if ($selected == $id) $out .= ' selected';
            }
            $out.= '>';
            if ($format == 0) $value = ($maxlength ? dol_trunc($arraytypes['label'], $maxlength) : $arraytypes['label']);
            elseif ($format == 1) $value = $arraytypes['code'];
            elseif ($format == 2) $value = ($maxlength ? dol_trunc($arraytypes['label'], $maxlength) : $arraytypes['label']);
            elseif ($format == 3) $value = $arraytypes['code'];
            $out .= $value ? $value : '&nbsp;';
            $out .= '</option>';
        }
        $out .= '</select>';
        if ($user->admin && !$noadmininfo) $out .= info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
        $out .= ajax_combobox('select' . $htmlname);
        return $out;
    }
}
