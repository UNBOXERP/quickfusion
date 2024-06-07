<?php
/* Copyright (C) 2004-2014 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2008      Raphael Bertrand     <raphael.bertrand@resultic.fr>
 * Copyright (C) 2010-2015 Juanjo Menent	    <jmenent@2byte.es>
 * Copyright (C) 2012      Christophe Battarel   <christophe.battarel@altairis.fr>
 * Copyright (C) 2012      Cedric Salvador      <csalvador@gpcsolutions.fr>
 * Copyright (C) 2015-2017 Ferran Marcet 		<fmarcet@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/core/modules/propale/doc/pdf_discount.modules.php
 *	\ingroup    propale
 *	\brief      Fichier de la classe permettant de generer les propales au modele Discount
 */
require_once DOL_DOCUMENT_ROOT.'/core/modules/propale/doc/pdf_azur.modules.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
dol_include_once('/discounts/class/discount_doc.class.php');


/**
 *	Class to generate PDF proposal Discount
 */
class pdf_discount extends pdf_azur
{
	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		global $conf,$langs,$mysoc;

		parent::__construct($db);

		$langs->load("discounts@discounts");

		$this->name = "discount";
		$this->description = $langs->trans('DocModelDiscountDescription');
	}

	/**
     *  Function to build pdf onto disk
     *
     *  @param		Object		$object				Object to generate
     *  @param		Translate	$outputlangs		Lang output object
     *  @param		string		$srctemplatepath	Full path of source filename for generator using a template file
     *  @param		int			$hidedetails		Do not show line details
     *  @param		int			$hidedesc			Do not show desc
     *  @param		int			$hideref			Do not show ref
     *  @return     int             				1=OK, 0=KO
	 */
    public function write_file($object,$outputlangs,$srctemplatepath='',$hidedetails=0,$hidedesc=0,$hideref=0)
	{
		global $user,$langs,$conf,$mysoc,$db,$hookmanager;

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("bills");
		$outputlangs->load("propal");
		$outputlangs->load("products");
        $outputlangs->load("discounts@discounts");

		$nblignes = count($object->lines);

		// Loop on each lines to detect if there is at least one image to show
		$realpatharray=array();
		if (! empty($conf->global->MAIN_GENERATE_PROPOSALS_WITH_PICTURE))
		{
			$objphoto = new Product($this->db);

			for ($i = 0 ; $i < $nblignes ; $i++)
			{
				if (empty($object->lines[$i]->fk_product)) continue;

				$objphoto->fetch($object->lines[$i]->fk_product);
                //var_dump($objphoto->ref);exit;
				if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))
				{
					$pdir[0] = get_exdir($objphoto->id,2,0,0,$objphoto,'product') . $objphoto->id ."/photos/";
					$pdir[1] = get_exdir(0,0,0,0,$objphoto,'product') . dol_sanitizeFileName($objphoto->ref).'/';
				}
				else
				{
					$pdir[0] = get_exdir(0,0,0,0,$objphoto,'product') . dol_sanitizeFileName($objphoto->ref).'/';				// default
					$pdir[1] = get_exdir($objphoto->id,2,0,0,$objphoto,'product') . $objphoto->id ."/photos/";	// alternative
				}

				$arephoto = false;
				foreach ($pdir as $midir)
				{
					if (! $arephoto)
					{
						$dir = $conf->product->dir_output.'/'.$midir;

						foreach ($objphoto->liste_photos($dir,1) as $key => $obj)
						{
							if (empty($conf->global->CAT_HIGH_QUALITY_IMAGES))		// If CAT_HIGH_QUALITY_IMAGES not defined, we use thumb if defined and then original photo
							{
								if ($obj['photo_vignette'])
								{
									$filename= $obj['photo_vignette'];
								}
								else
								{
									$filename=$obj['photo'];
								}
							}
							else
							{
								$filename=$obj['photo'];
							}

							$realpath = $dir.$filename;
							$arephoto = true;
						}
					}
				}

				if ($realpath && $arephoto) $realpatharray[$i]=$realpath;
			}
		}

		if (count($realpatharray) == 0) $this->posxpicture=$this->posxtva;

		if ($conf->propal->dir_output)
		{
			$object->fetch_thirdparty();

			$deja_regle = 0;

			// Definition of $dir and $file
			if ($object->specimen)
			{
				$dir = $conf->propal->dir_output;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->propal->dir_output . "/" . $objectref;
				$file = $dir . "/" . $objectref . ".pdf";
			}

			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{
				// Add pdfgeneration hook
				if (! is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks

				// Create pdf instance
                $pdf=pdf_getInstance($this->format);
                $default_font_size = pdf_getPDFFontSize($outputlangs);	// Must be after pdf_getInstance
	            $pdf->SetAutoPageBreak(1,0);
	             
                if (class_exists('TCPDF'))
                {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }
                $pdf->SetFont(pdf_getPDFFont($outputlangs));
                // Set path to the background PDF File
                if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->MAIN_ADD_PDF_BACKGROUND))
                {
                    $pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
                    $tplidx = $pdf->importPage(1);
                }

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("CommercialProposal"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("CommercialProposal")." ".$outputlangs->convToOutputCharset($object->thirdparty->name));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right

				// Positionne $this->atleastonediscount si on a au moins une remise
				for ($i = 0 ; $i < $nblignes ; $i++)
				{
					if ($object->lines[$i]->remise_percent)
					{
						$this->atleastonediscount++;
					}
				}
				if (empty($this->atleastonediscount) && empty($conf->global->PRODUCT_USE_UNITS))
				{
					$this->posxpicture+=($this->postotalht - $this->posxdiscount);
					$this->posxtva+=($this->postotalht - $this->posxdiscount);
					$this->posxup+=($this->postotalht - $this->posxdiscount);
					$this->posxqty+=($this->postotalht - $this->posxdiscount);
					$this->posxdiscount+=($this->postotalht - $this->posxdiscount);
					//$this->postotalht;
				}

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				
                $heightforinfotot = 40;	// Height reserved to output the info and total part
                $heightforsignature = empty($conf->global->PROPAL_DISABLE_SIGNATURE)?(pdfGetHeightForHtmlContent($pdf, $outputlangs->transnoentities("ProposalCustomerSignature"))+10):0;
                $heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
	            $heightforfooter = $this->marge_basse + 8;	// Height reserved to output the footer (value include bottom margin)
                //print $heightforinfotot + $heightforsignature + $heightforfreetext + $heightforfooter;exit;      
                
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColor(0,0,0);

				
	            $tab_top = 90;
				$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)?42:10);
				$tab_height = 130;
				$tab_height_newpage = 150;

				// Incoterm
				$height_incoterms = 0;
				if ($conf->incoterm->enabled)
				{
					$desc_incoterms = $object->getIncotermsForPDF();
					if ($desc_incoterms)
					{
						$tab_top = 88;

						$pdf->SetFont('','', $default_font_size - 1);
						$pdf->writeHTMLCell(190, 3, $this->posxdesc-1, $tab_top-1, dol_htmlentitiesbr($desc_incoterms), 0, 1);
						$nexY = $pdf->GetY();
						$height_incoterms=$nexY-$tab_top;

						// Rect prend une longueur en 3eme param
						$pdf->SetDrawColor(192,192,192);
						$pdf->Rect($this->marge_gauche, $tab_top-1, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_incoterms+1);

						$tab_top = $nexY+6;
						$height_incoterms += 4;
					}
				}

				// Affiche notes
				$notetoshow=empty($object->note_public)?'':$object->note_public;
				if (! empty($conf->global->MAIN_ADD_SALE_REP_SIGNATURE_IN_NOTE))
				{
					// Get first sale rep
					if (is_object($object->thirdparty))
					{
						$salereparray=$object->thirdparty->getSalesRepresentatives($user);
						$salerepobj=new User($this->db);
						$salerepobj->fetch($salereparray[0]['id']);
						if (! empty($salerepobj->signature)) $notetoshow=dol_concatdesc($notetoshow, $salerepobj->signature);
					}
				}
				if (! empty($conf->global->MAIN_ADD_CREATOR_IN_NOTE) && $object->user_author_id > 0)
				{
				    $tmpuser=new User($this->db);
				    $tmpuser->fetch($object->user_author_id);
				    $notetoshow.='Affaire suivi par '.$tmpuser->getFullName($langs);
				    if ($tmpuser->email) $notetoshow.=',  Mail: '.$tmpuser->email;
				    if ($tmpuser->office_phone) $notetoshow.=', Tel: '.$tmpuser->office_phone;
				}				
				if ($notetoshow)
				{
					$tab_top = 88 + $height_incoterms;

					$pdf->SetFont('','', $default_font_size - 1);
					$pdf->writeHTMLCell(190, 3, $this->posxdesc-1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
					$nexY = $pdf->GetY();
					$height_note=$nexY-$tab_top;

					// Rect prend une longueur en 3eme param
					$pdf->SetDrawColor(192,192,192);
					$pdf->Rect($this->marge_gauche, $tab_top-1, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_note+1);

					$tab_height = $tab_height - $height_note;
					$tab_top = $nexY+6;
				}
				else
				{
					$height_note=0;
				}

				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexY = $tab_top + 7;

				// Loop on each lines
				for ($i = 0; $i < $nblignes; $i++)
				{
					//2byte.es
					if($are_promo){
						$applypromo = true;
						$are_promo = false;
					}
					$curY = $nexY;
					$pdf->SetFont('','', $default_font_size - 1);   // Into loop to work with multipage
					$pdf->SetTextColor(0,0,0);

					// Define size of image if we need it
					$imglinesize=array();
					if (! empty($realpatharray[$i])) $imglinesize=pdf_getSizeForImage($realpatharray[$i]);

					$pdf->setTopMargin($tab_top_newpage);
					$pdf->setPageOrientation('', 1, $heightforfooter+$heightforfreetext+$heightforsignature+$heightforinfotot);	// The only function to edit the bottom margin of current page to set it.
					$pageposbefore=$pdf->getPage();

					$showpricebeforepagebreak=1;
					$posYAfterImage=0;
					$posYAfterDescription=0;

					// We start with Photo of product line
					if (isset($imglinesize['width']) && isset($imglinesize['height']) && ($curY + $imglinesize['height']) > ($this->page_hauteur-($heightforfooter+$heightforfreetext+$heightforsignature+$heightforinfotot)))	// If photo too high, we moved completely on new page
					{
						$pdf->AddPage('','',true);
						if (! empty($tplidx)) $pdf->useTemplate($tplidx);
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
						$pdf->setPage($pageposbefore+1);

						$curY = $tab_top_newpage;
						$showpricebeforepagebreak=0;
					}

					if (isset($imglinesize['width']) && isset($imglinesize['height']))
					{
						$curX = $this->posxpicture-1;
						$pdf->Image($realpatharray[$i], $curX + (($this->posxtva-$this->posxpicture-$imglinesize['width'])/2), $curY, $imglinesize['width'], $imglinesize['height'], '', '', '', 2, 300);	// Use 300 dpi
						// $pdf->Image does not increase value return by getY, so we save it manually
						$posYAfterImage=$curY+$imglinesize['height'];
					}

					// Description of product line
					$curX = $this->posxdesc-1;

					$showpricebeforepagebreak=1;

					$pdf->startTransaction();
					//2byte.es
					if($applypromo){
						if(isset($promo_desc)){
							$pdf->writeHTMLCell($this->posxpicture-$curX, 3, $curX, $curY, $outputlangs->convToOutputCharset($promo_desc), 0, 1, false, true, 'J',true);
						}else {
							$pdf->writeHTMLCell($this->posxpicture-$curX, 3, $curX, $curY, $outputlangs->convToOutputCharset($outputlangs->convToOutputCharset("InPromo"), 0, 1, false, true, 'J',true));
						}
					}else{
						pdf_writelinedesc($pdf,$object,$i,$outputlangs,$this->posxpicture-$curX,3,$curX,$curY,$hideref,$hidedesc);
					}
					
					$pageposafter=$pdf->getPage();
					if ($pageposafter > $pageposbefore)	// There is a pagebreak
					{
						$pdf->rollbackTransaction(true);
						$pageposafter=$pageposbefore;
						//print $pageposafter.'-'.$pageposbefore;exit;
						$pdf->setPageOrientation('', 1, $heightforfooter);	// The only function to edit the bottom margin of current page to set it.
						//2byte.es
						if($applypromo){
							$pdf->writeHTMLCell($this->posxpicture-$curX, 3, $curX, $curY, $outputlangs->convToOutputCharset($outputlangs->trans("InPromo")), 0, 1, false, true, 'J',true);
						}
						else{
							pdf_writelinedesc($pdf,$object,$i,$outputlangs,$this->posxpicture-$curX,3,$curX,$curY,$hideref,$hidedesc);
						}
						$pageposafter=$pdf->getPage();
						$posyafter=$pdf->GetY();
						//var_dump($posyafter); var_dump(($this->page_hauteur - ($heightforfooter+$heightforfreetext+$heightforinfotot))); exit;
						if ($posyafter > ($this->page_hauteur - ($heightforfooter+$heightforfreetext+$heightforsignature+$heightforinfotot)))	// There is no space left for total+free text
						{
							if ($i == ($nblignes-1))	// No more lines, and no space left to show total, so we create a new page
							{
								$pdf->AddPage('','',true);
								if (! empty($tplidx)) $pdf->useTemplate($tplidx);
								if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
								$pdf->setPage($pageposafter+1);
							}
						}
						else
						{
							// We found a page break
							$showpricebeforepagebreak=0;
						}
					}
					else	// No pagebreak
					{
						$pdf->commitTransaction();
					}
					$posYAfterDescription=$pdf->GetY();

					$nexY = $pdf->GetY();
					$pageposafter=$pdf->getPage();

					$pdf->setPage($pageposbefore);
					$pdf->setTopMargin($this->marge_haute);
					$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.

					// We suppose that a too long description or photo were moved completely on next page
					if ($pageposafter > $pageposbefore && empty($showpricebeforepagebreak)) {
						$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
					}

					$pdf->SetFont('','', $default_font_size - 1);   // On repositionne la police par defaut

					// VAT Rate
					if (empty($conf->global->MAIN_GENERATE_DOCUMENTS_WITHOUT_VAT) && empty($conf->global->MAIN_GENERATE_DOCUMENTS_WITHOUT_VAT_COLUMN))
					{
						//2byte.es
						if($applypromo){
							$vat_rate="";	
						}
						else{
							$vat_rate = pdf_getlinevatrate($object, $i, $outputlangs, $hidedetails);
						}
						$pdf->SetXY($this->posxtva, $curY);
						$pdf->MultiCell($this->posxup-$this->posxtva-0.8, 3, $vat_rate, 0, 'R');
					}

					// Unit price before discount
					//2byte.es
					if($applypromo){
						$up_excl_tax = "";//price(-1 * ($object->lines[$i]->subprice-$subprice_promo), 0, $outputlangs);
					}
					else{
						$dis_doc = new Discounts_doc($this->db);
						$res = $dis_doc->fetch(1,$object->lines[$i]->rowid);
						if($res > 0){
							$subprice_promo = $object->lines[$i]->subprice;
							$totalht_promo = $object->lines[$i]->total_ht;
							$object->lines[$i]->subprice = $dis_doc->ori_subprice;
							$object->lines[$i]->total_ht = $dis_doc->ori_totalht;
							if($dis_doc->descr){
								$promo_desc = "<dl><em><b>".$langs->trans("DiscountsApplied")."</b></em>";
								$promo_desc .= $dis_doc->descr;
							}
							$are_promo = true;
						}
						$up_excl_tax = pdf_getlineupexcltax($object, $i, $outputlangs, $hidedetails);
					}
					$pdf->SetXY($this->posxup, $curY);
					$pdf->MultiCell($this->posxqty-$this->posxup-0.8, 3, $up_excl_tax, 0, 'R', 0);

					// Quantity
					//2byte.es
					if($applypromo){
						$qty = "";
					}
					else{
						$qty = pdf_getlineqty($object, $i, $outputlangs, $hidedetails);
						$pdf->SetXY($this->posxqty, $curY);
						// Enough for 6 chars
						if($conf->global->PRODUCT_USE_UNITS)
						{
							$pdf->MultiCell($this->posxunit-$this->posxqty-0.8, 4, $qty, 0, 'R');
						}
						else
						{
							$pdf->MultiCell($this->posxdiscount-$this->posxqty-0.8, 4, $qty, 0, 'R');
						}
					}

					// Unit
					if($conf->global->PRODUCT_USE_UNITS)
					{
						$unit = pdf_getlineunit($object, $i, $outputlangs, $hidedetails, $hookmanager);
						$pdf->SetXY($this->posxunit, $curY);
						$pdf->MultiCell($this->posxdiscount-$this->posxunit-0.8, 4, $unit, 0, 'L');
					}

					// Discount on line
					$pdf->SetXY($this->posxdiscount, $curY);
					if ($object->lines[$i]->remise_percent)
					{
                        $pdf->SetXY($this->posxdiscount-2, $curY);
                        //2byte.es
						if($applypromo){
                        	$remise_percent = "";
                        }
                        else{
                        	$remise_percent = pdf_getlineremisepercent($object, $i, $outputlangs, $hidedetails);
                        }
					    $pdf->MultiCell($this->postotalht-$this->posxdiscount+2, 3, $remise_percent, 0, 'R');
					}

					// Total HT line
					//2byte.es
					if($applypromo){
						$total_excl_tax = price(-1 * ($object->lines[$i]->total_ht - $totalht_promo), 0, $outputlangs);
						
					}
					else{
						$total_excl_tax = pdf_getlinetotalexcltax($object, $i, $outputlangs, $hidedetails);
					}
					$pdf->SetXY($this->postotalht, $curY);
					$pdf->MultiCell($this->page_largeur-$this->marge_droite-$this->postotalht, 3, $total_excl_tax, 0, 'R', 0);
					//2byte.es
					if($applypromo){
						$applypromo = false;
					}
					else{
						// Collecte des totaux par valeur de tva dans $this->tva["taux"]=total_tva
						$tvaligne=$object->lines[$i]->total_tva;
						$localtax1ligne=$object->lines[$i]->total_localtax1;
						$localtax2ligne=$object->lines[$i]->total_localtax2;
						$localtax1_rate=$object->lines[$i]->localtax1_tx;
						$localtax2_rate=$object->lines[$i]->localtax2_tx;
						$localtax1_type=$object->lines[$i]->localtax1_type;
						$localtax2_type=$object->lines[$i]->localtax2_type;
	
						if ($object->remise_percent) $tvaligne-=($tvaligne*$object->remise_percent)/100;
						if ($object->remise_percent) $localtax1ligne-=($localtax1ligne*$object->remise_percent)/100;
						if ($object->remise_percent) $localtax2ligne-=($localtax2ligne*$object->remise_percent)/100;
	
						$vatrate=(string) $object->lines[$i]->tva_tx;
	
						// Retrieve type from database for backward compatibility with old records
						if ((! isset($localtax1_type) || $localtax1_type=='' || ! isset($localtax2_type) || $localtax2_type=='') // if tax type not defined
						&& (! empty($localtax1_rate) || ! empty($localtax2_rate))) // and there is local tax
						{
							$localtaxtmp_array=getLocalTaxesFromRate($vatrate,0,$object->thirdparty,$mysoc);
							$localtax1_type = $localtaxtmp_array[0];
							$localtax2_type = $localtaxtmp_array[2];
						}
	
					    // retrieve global local tax
						if ($localtax1_type && $localtax1ligne != 0)
							$this->localtax1[$localtax1_type][$localtax1_rate]+=$localtax1ligne;
						if ($localtax2_type && $localtax2ligne != 0)
							$this->localtax2[$localtax2_type][$localtax2_rate]+=$localtax2ligne;
	
						if (($object->lines[$i]->info_bits & 0x01) == 0x01) $vatrate.='*';
						if (! isset($this->tva[$vatrate])) 				$this->tva[$vatrate]='';
						$this->tva[$vatrate] += $tvaligne;
					}
					if ($posYAfterImage > $posYAfterDescription) $nexY=$posYAfterImage;

					// Add line
					if (! empty($conf->global->MAIN_PDF_DASH_BETWEEN_LINES) && $i < ($nblignes - 1) && !$are_promo)
					{
						$pdf->setPage($pageposafter);
						$pdf->SetLineStyle(array('dash'=>'1,1','color'=>array(80,80,80)));
						//$pdf->SetDrawColor(190,190,200);
						$pdf->line($this->marge_gauche, $nexY+1, $this->page_largeur - $this->marge_droite, $nexY+1);
						$pdf->SetLineStyle(array('dash'=>0));
					}

					$nexY+=2;    // Passe espace entre les lignes

					// Detect if some page were added automatically and output _tableau for past pages
					while ($pagenb < $pageposafter)
					{
						$pdf->setPage($pagenb);
						if ($pagenb == 1)
						{
							$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
						}
						else
						{
							$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1, $object->multicurrency_code);
						}
						$this->_pagefoot($pdf,$object,$outputlangs,1);
						$pagenb++;
						$pdf->setPage($pagenb);
						$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
					}
					if (isset($object->lines[$i+1]->pagebreak) && $object->lines[$i+1]->pagebreak)
					{
						if ($pagenb == 1)
						{
							$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
						}
						else
						{
							$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1, $object->multicurrency_code);
						}
						$this->_pagefoot($pdf,$object,$outputlangs,1);
						// New page
						$pdf->AddPage();
						if (! empty($tplidx)) $pdf->useTemplate($tplidx);
						$pagenb++;
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
					}
					//2byte.es restar las i
					if($are_promo)
						$i--;
				}

				// Show square
				if ($pagenb == 1)
				{
					$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforinfotot - $heightforfreetext - $heightforsignature - $heightforfooter, 0, $outputlangs, 0, 0, $object->multicurrency_code);
					$bottomlasttab=$this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforsignature - $heightforfooter + 1;
				}
				else
				{
					$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforinfotot - $heightforfreetext - $heightforsignature - $heightforfooter, 0, $outputlangs, 1, 0, $object->multicurrency_code);
					$bottomlasttab=$this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforsignature - $heightforfooter + 1;
				}

				// Affiche zone infos
				$posy=$this->_tableau_info($pdf, $object, $bottomlasttab, $outputlangs);

				// Affiche zone totaux
				$posy=$this->_tableau_tot($pdf, $object, 0, $bottomlasttab, $outputlangs);

				// Affiche zone versements
				/*
				if ($deja_regle || $amount_credit_notes_included || $amount_deposits_included)
				{
					$posy=$this->_tableau_versements($pdf, $object, $posy, $outputlangs);
				}
				*/

                if (empty($conf->global->PROPAL_DISABLE_SIGNATURE) && version_compare(DOL_VERSION, 4.0) < 0){
                    // Customer signature area
                    $posy=$this->_signature_area($pdf, $object, $posy, $outputlangs);
                }


				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				//If propal merge product PDF is active
				if (!empty($conf->global->PRODUIT_PDF_MERGE_PROPAL))
				{
					require_once DOL_DOCUMENT_ROOT.'/product/class/propalmergepdfproduct.class.php';

					$already_merged = array ();
					foreach ( $object->lines as $line ) {
						if (! empty($line->fk_product) && ! (in_array($line->fk_product, $already_merged))) {
							// Find the desire PDF
							$filetomerge = new Propalmergepdfproduct($this->db);

							if ($conf->global->MAIN_MULTILANGS) {
								$filetomerge->fetch_by_product($line->fk_product, $outputlangs->defaultlang);
							} else {
								$filetomerge->fetch_by_product($line->fk_product);
							}

							$already_merged[] = $line->fk_product;

							$product = new Product($this->db);
							$product->fetch($line->fk_product);

							if ($product->entity!=$conf->entity) {
								$entity_product_file=$product->entity;
							} else {
								$entity_product_file=$conf->entity;
							}

							// If PDF is selected and file is not empty
							if (count($filetomerge->lines) > 0) {
								foreach ( $filetomerge->lines as $linefile ) {
									if (! empty($linefile->id) && ! empty($linefile->file_name)) {


										if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))
										{
											if (! empty($conf->product->enabled)) {
												$filetomerge_dir = $conf->product->multidir_output[$entity_product_file] . '/' . get_exdir($product->id,2,0,0,$product,'product') . $product->id ."/photos";
											} elseif (! empty($conf->service->enabled)) {
												$filetomerge_dir = $conf->service->multidir_output[$entity_product_file] . '/' . get_exdir($product->id,2,0,0,$product,'product') . $product->id ."/photos";
											}
										}
										else
										{
											if (! empty($conf->product->enabled)) {
												$filetomerge_dir = $conf->product->multidir_output[$entity_product_file] . '/' . get_exdir(0,0,0,0,$product,'product') . dol_sanitizeFileName($product->ref);
											} elseif (! empty($conf->service->enabled)) {
												$filetomerge_dir = $conf->service->multidir_output[$entity_product_file] . '/' . get_exdir(0,0,0,0,$product,'product') . dol_sanitizeFileName($product->ref);
											}
										}

										dol_syslog(get_class($this) . ':: upload_dir=' . $filetomerge_dir, LOG_DEBUG);

										$infile = $filetomerge_dir . '/' . $linefile->file_name;
										if (file_exists($infile) && is_readable($infile)) {
											$pagecount = $pdf->setSourceFile($infile);
											for($i = 1; $i <= $pagecount; $i ++) {
												$tplIdx = $pdf->importPage($i);
												if ($tplIdx!==false) {
													$s = $pdf->getTemplatesize($tplIdx);
													$pdf->AddPage($s['h'] > $s['w'] ? 'P' : 'L');
													$pdf->useTemplate($tplIdx);
												} else {
													setEventMessages(null, array($infile.' cannot be added, probably protected PDF'),'warnings');
												}
											}
										}
									}
								}
							}
						}
					}
				}

				$pdf->Close();

				$pdf->Output($file,'F');

				//Add pdfgeneration hook
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('afterPDFCreation',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks

				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;   // Pas d'erreur
			}
			else
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","PROP_OUTPUTDIR");
			return 0;
		}
	}



}

