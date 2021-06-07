<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Service\Slugify;



class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    private $slugify;

    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 5; $i++) {
            $program = new Program();
            $slug = $this->slugify->generate('Walking dead' . $i);
            $program->setSlug($slug);
            $program->setTitle('Walking dead' . $i);
            $program->setSummary('Des zombies envahissent la terre');
            $program->setPoster('https://cdn.pixabay.com/photo/2021/03/01/09/29/woman-6059236__340.jpg');
            $program->setCountry('Test');
            $program->setYear(2000 + 2 * $i);
            $program->setCategory($this->getReference('category_0'));

            //ici les acteurs sont insérés via une boucle pour être DRY mais ce n'est pas obligatoire
            for ($j = 0; $j < count(ActorFixtures::ACTORS); $j++) {
                $program->addActor($this->getReference('actor_' . $j));
            }
            $this->addReference('program_' . $i, $program);
            $manager->persist($program);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            ActorFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
