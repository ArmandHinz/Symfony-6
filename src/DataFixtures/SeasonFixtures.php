<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Season;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $count = 0;
        for ($j = 0; $j < 5; $j++) {
            for ($i = 0; $i < 5; $i++) {
                $season = new Season();
                $season->setNumber($i);
                $season->setYear(2000 + 2);
                $season->setDescription('Lorem fjeiocndjiocbreizcizenjze ipsum crjeizbceior');
                $season->setProgram($this->getReference('program_' . $j));
                $manager->persist($season);
                $this->addReference('season_' . $count, $season);
                $count++;
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            ProgramFixtures::class,
        ];
    }
}
