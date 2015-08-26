<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Misd\PhoneNumberBundle\MisdPhoneNumberBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new APY\DataGridBundle\APYDataGridBundle(),
            new Webfactory\Bundle\ExceptionsBundle\WebfactoryExceptionsBundle(),
            new Genemu\Bundle\FormBundle\GenemuFormBundle(),
            new Oneup\UploaderBundle\OneupUploaderBundle(),
            new Trsteel\CkeditorBundle\TrsteelCkeditorBundle(),


            // In Toolkit Project ( in /src directory) bundles...

            new AppBundle\AppBundle(),
            new TUI\Toolkit\QuoteBundle\QuoteBundle(),
            new TUI\Toolkit\UserBundle\TUIToolkitUserBundle(),
            new TUI\Toolkit\BrandBundle\BrandBundle(),
            new TUI\Toolkit\BoardBasisBundle\BoardBasisBundle(),
            new TUI\Toolkit\TransportBundle\TransportBundle(),
            new TUI\Toolkit\TripStatusBundle\TripStatusBundle(),
            new TUI\Toolkit\ContentBlocksBundle\ContentBlocksBundle(),
            new TUI\Toolkit\InstitutionBundle\InstitutionBundle(),
            new TUI\Toolkit\CurrencyBundle\CurrencyBundle(),
            new TUI\Toolkit\MediaBundle\MediaBundle(),
            new TUI\Toolkit\PermissionBundle\PermissionBundle(),

            // All of this just to use Sonata Media Bundle (but some good stuff in there, like date picker)
/*            new Sonata\ClassificationBundle\SonataClassificationBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\MediaBundle\SonataMediaBundle(),*/
            new JMS\SerializerBundle\JMSSerializerBundle()

            //Bundles Generated By Sonata
/*            new Application\Sonata\MediaBundle\ApplicationSonataMediaBundle(),
            new Application\Sonata\ClassificationBundle\ApplicationSonataClassificationBundle(),*/,

        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }


    /**
    * Function extended to load the DOMPDF config without having it saved in cache.
    * If it's saved in cache, a PDF can only be generated the first time after
    * the cache is created because constants are defined.
    *
    * @see parent
    */
    public function boot()
    {
        parent::boot();
        require_once __DIR__.'/config/dompdf.php';
    }
    
}
