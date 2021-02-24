<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 70; $i++) {
            $article = new Article();
            $article->setName($faker->words(rand(4, 7), true));
            $article->setText($faker->words(rand(150, 200), true));
            $article->setCreatedAt(new \DateTime($faker->date()));

            $manager->persist($article);
        }

        $manager->flush();
    }
}
