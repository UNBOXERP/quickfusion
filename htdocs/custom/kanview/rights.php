<?php

if (!function_exists('hasPermissionForKanbanView')) {

	function hasPermissionForKanbanView($kanbanView, $returnPermsCode = false) {
		global $user, $conf;
		$rightsOK_part1_code = ''; // rights if not use advanced perms
		$rightsOK_part2_code = ''; // rights if use advanced perms
		$rightsOK_code			 = ''; // rights

		$rightsOK_part1	 = 0; // rights if not use advanced perms
		$rightsOK_part2	 = 0; // rights if use advanced perms
		$rightsOK				 = 0; // rights

		if (!$user->rights->kanview->canuse)
			return false;

		switch ($kanbanView) {
			// ------------------------------------------ 1 - projets
			case 'projets':
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->projet->enabled && $user->rights->projet->lire';
				$rightsOK_part2_code = $rightsOK_part1_code . ' && $user->rights->kanview->kanview_advance->canuse_projects';
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->projet->enabled && $user->rights->projet->lire;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_projects;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// ---------------------------------------- 2 - tasks
			case 'tasks':
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->projet->enabled && $user->rights->projet->lire';
				$rightsOK_part2_code = $rightsOK_part1_code . ' && $user->rights->kanview->kanview_advance->canuse_tasks';
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->projet->enabled && $user->rights->projet->lire;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_tasks;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// --------------------------------------- 3 - propals
			case 'propals':
				if (compareVersions(DOL_VERSION, '14.0.0') == -1) { // < 14
					$propale_cloturer_code				 = '$user->rights->propale->cloturer';
					$propale_cloturer_advance_code = '1';
					$propale_cloturer							 = $user->rights->propale->cloturer;
					$propale_cloturer_advance			 = 1;
				}
				else { // >= 14
					$propale_cloturer_code				 = '1';
					$propale_cloturer_advance_code = '$user->rights->propal_advance->close';
					$propale_cloturer							 = 1;
					$propale_cloturer_advance			 = $user->rights->propal_advance->close;
				}
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->propal->enabled && $user->rights->propale->lire && $user->rights->propale->creer && ' . $propale_cloturer_code;
				$rightsOK_part2_code = $rightsOK_part1_code . ' && $user->rights->kanview->kanview_advance->canuse_propals && $user->rights->propale->propal_advance->validate && ' . $propale_cloturer_advance_code;
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->propal->enabled && $user->rights->propale->lire && $user->rights->propale->creer && $propale_cloturer;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_propals && $user->rights->propale->propal_advance->validate && $propale_cloturer_advance;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// --------------------------------------- 4 - orders
			case 'orders':
				if (compareVersions(DOL_VERSION, '12.0.0') == -1) {
					$orderCloseRight		 = '&& $user->rights->commande->cloturer';
					$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->commande->enabled && $user->rights->commande->lire && $user->rights->commande->creer ' . $orderCloseRight;
					$rightsOK_part2_code = $rightsOK_part1_code . ' && $user->rights->kanview->kanview_advance->canuse_orders && $user->rights->commande->order_advance->validate && $user->rights->commande->order_advance->annuler';
					$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';
				}
				else {
					$orderCloseRightAdvance	 = '&& $user->rights->commande->order_advance->close';
					$rightsOK_part1_code		 = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->commande->enabled && $user->rights->commande->lire && $user->rights->commande->creer';
					$rightsOK_part2_code		 = $rightsOK_part1_code . ' && $user->rights->kanview->kanview_advance->canuse_orders && $user->rights->commande->order_advance->validate && $user->rights->commande->order_advance->annuler ' . $orderCloseRightAdvance;
					$rightsOK_code					 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';
				}

				if (compareVersions(DOL_VERSION, '12.0.0') == -1) {
					$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->commande->enabled && $user->rights->commande->lire && $user->rights->commande->creer && $user->rights->commande->cloturer;
					$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_orders && $user->rights->commande->order_advance->validate && $user->rights->commande->order_advance->annuler;
					$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);
				}
				else {
					$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->commande->enabled && $user->rights->commande->lire && $user->rights->commande->creer;
					$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_orders && $user->rights->commande->order_advance->validate && $user->rights->commande->order_advance->annuler && $user->rights->commande->order_advance->close;
					$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);
				}

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// ------------------------------------ 5 - invoices
			case 'invoices':
				if (compareVersions(DOL_VERSION, '14.0.0') == -1) { // < 14
					$canreopen_code_advanced	 = '1';
					$canreopen_advanced			 = 1;
				}
				else { // >= 14
					$canreopen_code_advanced	 = '$user->rights->facture->invoice_advance->reopen';
					$canreopen_advanced			 = $user->rights->facture->invoice_advance->reopen;
					if (!empty($conf->global->INVOICE_DISALLOW_REOPEN)) {
						$canreopen_code_advanced = '0';
						$canreopen_advanced = false;
					}
				}
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->facture->enabled && $user->rights->facture->lire && $user->rights->facture->creer && $user->rights->facture->paiement';
				$rightsOK_part2_code = $rightsOK_part1_code . ' && $user->rights->kanview->kanview_advance->canuse_invoices && $user->rights->facture->invoice_advance->unvalidate && $user->rights->facture->invoice_advance->validate && ' . $canreopen_code_advanced;
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->facture->enabled && $user->rights->facture->lire && $user->rights->facture->creer && $user->rights->facture->paiement;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_invoices && $user->rights->facture->invoice_advance->unvalidate && $user->rights->facture->invoice_advance->validate && $canreopen_advanced;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// ------------------------------------ 6 - prospects
			case 'prospects':
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->societe->enabled && $user->rights->societe->lire && $user->rights->societe->creer';
				$rightsOK_part2_code = $rightsOK_part1_code . ' && $user->rights->kanview->kanview_advance->canuse_prospects';
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->societe->enabled && $user->rights->societe->lire && $user->rights->societe->creer;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_prospects;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// ------------------------------------ 7 - invoices_suppliers
			case 'invoices_suppliers':
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->fournisseur->enabled && $user->rights->fournisseur->facture->lire && $user->rights->fournisseur->facture->creer';
				$rightsOK_part2_code = $rightsOK_part1_code . ' && $user->rights->kanview->kanview_advance->canuse_invoices_suppliers && $user->rights->fournisseur->supplier_invoice_advance->validate';
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->fournisseur->enabled && $user->rights->fournisseur->facture->lire && $user->rights->fournisseur->facture->creer;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_invoices_suppliers && $user->rights->fournisseur->supplier_invoice_advance->validate;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			default:
				return false;
				break;
		}
	}

}