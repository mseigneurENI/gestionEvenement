<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->addCity($manager);
        $this->addCampus($manager);
        $this->addStatus($manager);
        $this->addPlace($manager);
        $this->addUser($manager);
        $this->addEvent($manager);
    }

    public function addCity(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $city = new City();
            $city
                ->setName($faker->city())
                ->setZipcode($faker->postcode());
            $manager->persist($city);
        }
        $manager->flush();
    }

    public function addUser(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $campus = $manager->getRepository(Campus::class)->findAll();

        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user
                ->setLastname($faker->lastName())
                ->setFirstname($faker->firstName)
                ->setUsername($faker->userName())
                ->setActive(1)
                ->setRoles(['ROLE_USER'])
                ->setEmail($faker->email())
                ->setPassword(
                    $this->userPasswordHasher->hashPassword($user, '123456')
                )
                ->setCampus($faker->randomElement($campus));


            $manager->persist($user);
        }
        $manager->flush();
    }

    public function addPlace(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $cities = $manager->getRepository(City::class)->findAll();

        for ($i = 0; $i < 5; $i++) {
            $place = new Place();
            $place
                ->setName($faker->text(20))
                ->setCity($faker->randomElement($cities))
                ->setLatitude($faker->latitude())
                ->setLongitude($faker->longitude())
                ->setStreet($faker->streetAddress());
            $manager->persist($place);
        }
        $manager->flush();
    }

    public function addStatus(ObjectManager $manager)
    {
        $statuses = ['En création', 'Ouverte', 'Clôturée', 'En cours', 'Terminée', 'Annulée', 'Historisée'];

        foreach ($statuses as $stat) {

            $status = new Status();
            $status->setDescription($stat);

            $manager->persist($status);
        }
        $manager->flush();
    }

    public function addCampus(ObjectManager $manager)
    {
        $campuses = ['Chartres de Bretagne', 'Saint-Herblain', 'Niort', 'Quimper'];

        foreach ($campuses as $camp) {

            $campus = new Campus();
            $campus->setName($camp);

            $manager->persist($campus);
        }
        $manager->flush();
    }


    public function addEvent(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $campuses = $manager->getRepository(Campus::class)->findAll();
        $statuses = $manager->getRepository(Status::class)->findAll();
        $organiser = $manager->getRepository(User::class)->findAll();
        $places = $manager->getRepository(Place::class)->findAll();

        for ($i = 0; $i < 20; $i++) {
            $event = new Event();
            $event->setName($faker->text(50))
                ->setBeginDateEvent($faker->dateTimeBetween('+0 days', '+1 year'));
            $event->setEndDate($faker->dateTimeBetween($event->getBeginDateEvent(), '+1 year'))
                ->setLimitDateRegistration($faker->dateTimeBetween('-1 year', $event->getBeginDateEvent()))
                ->setRegistrationMaxNb($faker->numberBetween(5, 30))
                ->setDetails($faker->text(200))
                ->setCampus($faker->randomElement($campuses))
                ->setStatus($faker->randomElement($statuses))
                ->setOrganiser($faker->randomElement($organiser))
                ->setPlace($faker->randomElement($places));

            $manager->persist($event);
        }
        $manager->flush();
    }

}
