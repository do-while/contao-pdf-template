<?php

/**
 * @copyright  Softleister 2011-2020
 * @author     Softleister <info@softleister.de>
 * @package    pdf-template
 * @license    LGPL
 * @see	       https://github.com/do-while/contao-pdf-template
 *
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'pdftemplate';

PaletteManipulator::create()
	->addLegend('pdf_legend', 'cache_legend')
	->addField('pdftemplate', 'pdf_legend', PaletteManipulator::POSITION_APPEND)
	->applyToPalette('root', 'tl_page')
	->applyToPalette('rootfallback', 'tl_page');

// add subpalette
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['pdftemplate'] = 'pdfTplSRC,pdfMargin,pdfIgnoreCSS';

// add fields
$GLOBALS['TL_DCA']['tl_page']['fields']['pdftemplate'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['pdftemplate'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'		          => array('submitOnChange'=>true),
	'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['pdfTplSRC'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['pdfTplSRC'],
	'exclude'                 => true,
	'inputType'               => 'fileTree',
	'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'clr', 'extensions'=>'pdf'),
	'sql'                     => "binary(16) NULL",
);

$GLOBALS['TL_DCA']['tl_page']['fields']['pdfMargin'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['pdfMargin'],
	'exclude'                 => true,
	'inputType'               => 'trbl',
	'options'                 => array('mm', 'cm'),
	'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
	'sql'                     => "varchar(128) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['pdfIgnoreCSS'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['pdfIgnoreCSS'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50 m12'),
	'sql'                     => "char(1) NOT NULL default ''"
);
