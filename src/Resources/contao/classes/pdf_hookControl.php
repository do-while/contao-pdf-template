<?php

/**
 * @copyright  Softleister 2011-2017
 * @author     Softleister <info@softleister.de>
 * @package    pdf-template
 * @license    LGPL
 * @see        https://github.com/do-while/contao-pdf-template
 *
 */

namespace Softleister\Pdftemplate;

class pdf_hookControl extends \Backend
{
    //-----------------------------------------------------------------
    // myPrintArticleAsPdf:  create PDF with template file
    //-----------------------------------------------------------------
    public function myPrintArticleAsPdf( $strArticle, $objArticle )
    {
        global $objPage;
        $root_details = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
                                       ->limit( 1 )
                                       ->execute( $objPage->rootId );

        //-- check conditions for a return --
        if($root_details->pdftemplate != '1') return;                         // PDF template == OFF

        // get template pdf
        $root_details->pdfTplSRC = \FilesModel::findByUuid($root_details->pdfTplSRC)->path;
        if( !file_exists(TL_ROOT . '/' . $root_details->pdfTplSRC) ) return;  // template file not found

        // URL decode image paths (see #6411)
        $strArticle = preg_replace_callback('@(src="[^"]+")@', function ($arg) {
            return rawurldecode($arg[0]);
        }, $strArticle);

        // Handle line breaks in preformatted text
        $strArticle = preg_replace_callback('@(<pre.*</pre>)@Us', function ($arg) {
            return str_replace("\n", '<br>', $arg[0]);
        }, $strArticle);

        $strArticle = str_replace( array(chr(0xC2).chr(0xA0), chr(0xC2).chr(0xA9), chr(0xC2).chr(0xAE), chr(0xC2).chr(0xB0), chr(0xC3).chr(0x84), chr(0xC3).chr(0x96), chr(0xC2).chr(0x9C), chr(0xC3).chr(0xA4), chr(0xC3).chr(0xB6), chr(0xC3).chr(0xBC), chr(0xC3).chr(0x9F) ),
                                   array(' ', '©', '®', '°', 'Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß'),
                                   $strArticle);

        // Default PDF export using TCPDF
        $arrSearch = array
        (
            '@<span style="text-decoration: ?underline;?">(.*)</span>@Us',
            '@(<img[^>]+>)@',
            '@(<div[^>]+block[^>]+>)@',
            '@[\n\r\t]+@',
            '@<br( /)?><div class="mod_article@',
            '@href="([^"]+)(pdf=[0-9]*(&|&amp;)?)([^"]*)"@'
        );

        $arrReplace = array
        (
            '<u>$1</u>',
            '<br>$1',
            '<br>$1',
            ' ',
            '<div class="mod_article',
            'href="$1$4"'
        );

        $strArticle = preg_replace($arrSearch, $arrReplace, $strArticle);

        // TCPDF configuration
        $l['a_meta_dir'] = 'ltr';
        $l['a_meta_charset'] = \Config::get('characterSet');
        $l['a_meta_language'] = substr($GLOBALS['TL_LANGUAGE'], 0, 2);
        $l['w_page'] = 'page';

        // Include libraries
        if(file_exists(TL_ROOT . '/vendor/contao/core-bundle/src/Resources/contao/config/tcpdf.php')) {
            require_once(TL_ROOT . '/vendor/contao/core-bundle/src/Resources/contao/config/tcpdf.php');
        } else {
            require_once(TL_ROOT . '/vendor/contao/tcpdf-bundle/src/Resources/contao/config/tcpdf.php');
        }

        //-- Calculating dimensions
        $margins = unserialize($root_details->pdfMargin);                     // Margins as an array
        switch( $margins['unit'] ) {
            case 'cm':  $factor = 10.0;     break;
            default:    $factor = 1.0;
        }
        $dim['top']    = !is_numeric($margins['top'])    ? PDF_MARGIN_TOP    : $margins['top'] * $factor;
        $dim['right']  = !is_numeric($margins['right'])  ? PDF_MARGIN_RIGHT  : $margins['right'] * $factor;
        $dim['bottom'] = !is_numeric($margins['bottom']) ? PDF_MARGIN_BOTTOM : $margins['bottom'] * $factor;
        $dim['left']   = !is_numeric($margins['left'])   ? PDF_MARGIN_LEFT   : $margins['left'] * $factor;

        // Create new PDF document with FPDI extension
        $pdf = new TPLPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);
        $pdf->setSourceFile( TL_ROOT . '/' . $root_details->pdfTplSRC );              // Set PDF template

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(PDF_AUTHOR);
        $pdf->SetTitle($objArticle->title);
        $pdf->SetSubject($objArticle->title);
        $pdf->SetKeywords($objArticle->keywords);

		// Prevent font subsetting (huge speed improvement)
		$pdf->setFontSubsetting(false);

        $pdf->SetDisplayMode('fullwidth', 'OneColumn', 'UseNone');
        $pdf->SetHeaderData( );

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins($dim['left'], $dim['top'], $dim['right']);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, $dim['bottom']);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Set some language-dependent strings
        $pdf->setLanguageArray($l);

        // Initialize document and add a page
        $pdf->getAliasNbPages();
        $pdf->AddPage();

        // Set font
        $pdf->SetFont(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN);

        // Include CSS
        if( ($root_details->pdfIgnoreCSS != '1') && file_exists(TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/tcpdf.css') ) {
            $styles = "<style>\n" . $this->css_optimize(file_get_contents(TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/tcpdf.css')) . "\n</style>\n";
            $strArticle = $styles . $strArticle;
        }

        // Write the HTML content
        $pdf->writeHTML($strArticle, true, 0, true, 0);

        // Close and output PDF document
        $pdf->lastPage();
		$pdf->Output(\StringUtil::standardize(ampersand($objArticle->title, false)) . '.pdf', 'D');

        // Stop script execution
        exit;
    }

    function css_optimize($buffer)
    {
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer); // remove comments
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t"), '', $buffer);  // remove tabs, newlines, etc.
        $buffer = preg_replace('/\s\s+/', ' ', $buffer);                      // remove multiple spaces

        return $buffer;
    }

  //-----------------------------------------------------------------
}
