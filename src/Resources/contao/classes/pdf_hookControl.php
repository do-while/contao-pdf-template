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

        // CONTAO 3.2 compatibility
        $root_details->pdfTplSRC = \FilesModel::findByUuid($root_details->pdfTplSRC)->path;

        if( !file_exists(TL_ROOT . '/' . $root_details->pdfTplSRC) ) return;  // template file not found

        //-- Calculating dimensions
        $margins = unserialize($root_details->pdfMargin);                     // Margins as an array
        switch( $margins['unit'] ) {
            case 'cm':  $factor = 10.0;     break;
            default:    $factor = 1.0;
        }
        $dim['top']    = !is_numeric($margins['top'])   ? PDF_MARGIN_TOP    : $margins['top'] * $factor;
        $dim['right']  = !is_numeric($margins['right']) ? PDF_MARGIN_RIGHT  : $margins['right'] * $factor;
        $dim['bottom'] = !is_numeric($margins['top'])   ? PDF_MARGIN_BOTTOM : $margins['bottom'] * $factor;
        $dim['left']   = !is_numeric($margins['left'])  ? PDF_MARGIN_LEFT   : $margins['left'] * $factor;

        // Handle line breaks in preformatted text
        $strArticle = str_replace( array(chr(0xC2).chr(0xA0), chr(0xC2).chr(0xA9), chr(0xC2).chr(0xAE), chr(0xC2).chr(0xB0), chr(0xC3).chr(0x84), chr(0xC3).chr(0x96), chr(0xC2).chr(0x9C), chr(0xC3).chr(0xA4), chr(0xC3).chr(0xB6), chr(0xC3).chr(0xBC), chr(0xC3).chr(0x9F) ),
                                   array(' ', '©', '®', '°', 'Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß'),
                                   $strArticle);
        $strArticle = preg_replace_callback('@(<pre.*</pre>)@Us', 'nl2br_callback', $strArticle);

        // Default PDF export using TCPDF
        $arrSearch = array
        (
            '@<span style="text-decoration: ?underline;?">(.*)</span>@Us',
            '@(<img[^>]+>)@',
            '@(<div[^>]+block[^>]+>)@',
            '@[\n\r\t]+@',
            '@<br /><div class="mod_article@',
            '@href="([^"]+)(pdf=[0-9]*(&|&)?)([^"]*)"@'
        );

        $arrReplace = array
        (
            '<u>$1</u>',
            '<br />$1',
            '<br />$1',
            ' ',
            '<div class="mod_article',
            'href="$1$4"'
        );

        $strArticle = preg_replace($arrSearch, $arrReplace, $strArticle);

        // TCPDF configuration
        $l['a_meta_dir'] = 'ltr';
        $l['a_meta_charset'] = $GLOBALS['TL_CONFIG']['characterSet'];
        $l['a_meta_language'] = $GLOBALS['TL_LANGUAGE'];
        $l['w_page'] = 'page';

        // Include libraries
        require_once(TL_ROOT . '/system/config/tcpdf.php');
        require_once(K_PATH_MAIN . 'tcpdf.php');
        require_once(TL_ROOT . '/system/modules/tcpdf_ext/vendor/fpdi.php');    // FPDI plugin
        require_once(TL_ROOT . '/system/modules/pdf-template/tplpdf.php');

        // Create new PDF document with FPDI extension
        $pdf = new \TPLPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);
        $pdf->setSourceFile( TL_ROOT . '/' . $root_details->pdfTplSRC );          // Set PDF template

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(PDF_AUTHOR);
        $pdf->SetTitle($objArticle->title);
        $pdf->SetSubject($objArticle->title);
        $pdf->SetKeywords($objArticle->keywords);

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

        // Include CSS (TCPDF 5.1.000 an newer)
        if( ($root_details->pdfIgnoreCSS != '1') && file_exists(TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/tcpdf.css') ) {
            $styles = "<style>\n" . $this->css_optimize(file_get_contents(TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/tcpdf.css')) . "\n</style>\n";
            $strArticle = $styles . $strArticle;
        }

        // Write the HTML content
        $pdf->writeHTML($strArticle, true, 0, true, 0);

        // Close and output PDF document
        $pdf->lastPage();
        $pdf->Output(standardize(ampersand($objArticle->title, false)) . '.pdf', 'D');

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
