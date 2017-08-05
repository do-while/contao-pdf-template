<?php

/**
 * @copyright  Softleister 2011-2017
 * @author     Softleister <info@softleister.de>
 * @package    pdf-template
 * @license    LGPL
 * @see	       https://github.com/do-while/contao-pdf-template
 *
 */

define('PDFTEMPLATE_VERSION', '1.0');
define('PDFTEMPLATE_BUILD'  , '0');

//-------------------------------------------------------------------------
//  HOOKS
//-------------------------------------------------------------------------
$GLOBALS['TL_HOOKS']['printArticleAsPdf'][] = array('Softleister\Pdftemplate\pdf_hookControl', 'myPrintArticleAsPdf');
