<?php

namespace App\Tests\Unit;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testNewUserHasDefaultValues(): void
    {
        $user = new User();

        $this->assertNull($user->getId());
        $this->assertEquals(0, $user->getSolde());
        $this->assertNotNull($user->getCreatedAt());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testSetAndGetEmail(): void
    {
        $user = new User();
        $user->setEmail('test@lakers.com');

        $this->assertEquals('test@lakers.com', $user->getEmail());
        $this->assertEquals('test@lakers.com', $user->getUserIdentifier());
    }

    public function testSetAndGetPseudo(): void
    {
        $user = new User();
        $user->setPseudo('LakersKing');

        $this->assertEquals('LakersKing', $user->getPseudo());
    }

    public function testSetAndGetSolde(): void
    {
        $user = new User();
        $user->setSolde(150.50);

        $this->assertEquals(150.50, $user->getSolde());
    }

    public function testRolesContainsRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testSetAndGetDateNaissance(): void
    {
        $user = new User();
        $date = new \DateTime('2003-11-17');
        $user->setDateNaissance($date);

        $this->assertInstanceOf(\DateTime::class, $user->getDateNaissance());
        $this->assertEquals($date, $user->getDateNaissance());
    }
}
