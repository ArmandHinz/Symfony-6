<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Episode;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;


class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($j = 0; $j < 25; $j++) {
            for ($i = 0; $i < 5; $i++) {
                $episode = new Episode();
                $episode->setTitle('Episode');
                $episode->setNumber($i);
                $episode->setSynopsis('Lorem Ipsum de hezivjdkobzchrenjdizvbrheiovbdhuibvhzreobgyv');
                //ici les acteurs sont insérés via une boucle pour être DRY mais ce n'est pas obligatoire
                $episode->setSeason($this->getReference('season_' . $j));
                $manager->persist($episode);
            }
        }


        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            SeasonFixtures::class,
        ];
    }
}
