<?php

namespace App\DataFixtures;

use App\Entity\Test;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class TestFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $test1 = new Test();
        $test1->setName('Test 1');
        $manager->persist($test1);

        $test2 = new Test();
        $test2->setName('Test 2');
        $manager->persist($test2);

        $test3 = new Test();
        $test3->setName('Test 3');
        $manager->persist($test3);

        $manager->flush();
    }
}
