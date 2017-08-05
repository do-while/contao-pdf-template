<?php

/**
 * @copyright  Softleister 2011-2017
 * @author     Softleister <info@softleister.de>
 * @package    pdf-template
 * @license    LGPL
 * @see	       https://github.com/do-while/contao-pdf-template
 *
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_page']['pdftemplate']  = array('PDF output with PDF template', 'PDF output with a template design, i.e. with company letterhead.');
$GLOBALS['TL_LANG']['tl_page']['pdfTplSRC']    = array('PDF template file', 'Enter a template file that will be used for PDF output.');
$GLOBALS['TL_LANG']['tl_page']['pdfMargin']    = array('Marginal areas', 'Adjust the margins up, right, bottom and left corresponding to the template file.');
$GLOBALS['TL_LANG']['tl_page']['pdfIgnoreCSS'] = array('Skip '.$GLOBALS['TL_CONFIG']['uploadPath'].'/tcpdf.css', 'Do not include the TCPDF style sheet ('.$GLOBALS['TL_CONFIG']['uploadPath'].'/tcpdf.css).');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_page']['pdf_legend']   = 'PDF template';
