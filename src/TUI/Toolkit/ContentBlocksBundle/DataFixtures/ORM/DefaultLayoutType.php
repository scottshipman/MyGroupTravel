<?php
/**
 * Created jordanschinella
 * Date: 8/11/15
 */

namespace TUI\Toolkit\ContentBlocksBundle\Bundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use TUI\Toolkit\ContentBlocksBundle\Entity\LayoutType;


class DefaultLayoutType implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */

    public function load(ObjectManager $manager)
    {
        // load a default Layout Types Objects in the database.
        $layouttype = new LayoutType();
        $layouttype->setName("image_left");
        $layouttype->setIcon("icon");
        $layouttype->setClassName("image-left");
        $manager->persist($layouttype);
        $manager->flush();

        $layouttype = new LayoutType();
        $layouttype->setName("image_right");
        $layouttype->setIcon("icon2");
        $layouttype->setClassName("image-right");
        $manager->persist($layouttype);
        $manager->flush();

        $layouttype = new LayoutType();
        $layouttype->setName("image_top");
        $layouttype->setIcon("icon3");
        $layouttype->setClassName("image-top");
        $manager->persist($layouttype);
        $manager->flush();

        $layouttype = new LayoutType();
        $layouttype->setName("image_bottom");
        $layouttype->setIcon("icon4");
        $layouttype->setClassName("image-bottom");
        $manager->persist($layouttype);
        $manager->flush();
    }
}