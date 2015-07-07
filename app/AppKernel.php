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

            // All of this just to use Sonata Media Bundle (but some good stuff in there, like date picker)
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
            new Sonata\ClassificationBundle\SonataClassificationBundle(),
            new TUI\Toolkit\ClassificationBundle\ClassificationBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\MediaBundle\SonataMediaBundle(),
            new Sonata\IntlBundle\SonataIntlBundle(),
            new TUI\Toolkit\MediaBundle\MediaBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle()
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
}
