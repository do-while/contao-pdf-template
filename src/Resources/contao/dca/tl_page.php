<?php

/**
 * @copyright  Softleister 2011-2017
 * @author     Softleister <info@softleister.de>
 * @package    pdf-template
 * @license    LGPL
 * @see	       https://github.com/do-while/contao-pdf-template
 *
 */

$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'pdftemplate';
$GLOBALS['TL_DCA']['tl_page']['palettes']['root'] = str_replace('{cache_legend', '{pdf_legend:hide},pdftemplate;{cache_legend', $GLOBALS['TL_DCA']['tl_page']['palettes']['root']);

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
