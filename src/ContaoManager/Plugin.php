<?php

/**
 * @copyright  Softleister 2011-2017
 * @author     Softleister <info@softleister.de>
 * @package    pdf-template
 * @license    LGPL
 * @see	       https://github.com/do-while/contao-pdf-template
 *
 */

namespace Softleister\PdftemplateBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Plugin for the Contao Manager.
 *
 * @author Softleister
 */
class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles( ParserInterface $parser )
    {
        return [
            BundleConfig::create( 'Softleister\PdftemplateBundle\SoftleisterPdftemplateBundle' )
                ->setLoadAfter( ['Contao\CoreBundle\ContaoCoreBundle'] )
                ->setReplace( ['pdf-template'] ),
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function getRouteCollection( LoaderResolverInterface $resolver, KernelInterface $kernel )
    {
        return $resolver
            ->resolve( __DIR__ . '/../Resources/config/routing.yml' )
            ->load( __DIR__ . '/../Resources/config/routing.yml' );
    }

}
