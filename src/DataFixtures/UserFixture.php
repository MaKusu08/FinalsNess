<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ADMIN USER
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsActive(true);
        $admin->setIsVerified(true);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // STAFF USER
        $staff = new User();
        $staff->setUsername('staff');
        $staff->setRoles(['ROLE_STAFF']);
        $staff->setIsActive(true);
        $staff->setIsVerified(true);
        $staff->setPassword($this->passwordHasher->hashPassword($staff, 'staff123'));
        $manager->persist($staff);

        $manager->flush();
        
        // Debug output
        echo "Users created! Admin ID: " . $admin->getId() . ", Staff ID: " . $staff->getId() . "\n";
    }
}