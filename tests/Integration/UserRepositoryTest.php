<?php

namespace App\Tests\Integration;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    public function testSaveAndFindUser(): void
    {
        self::bootKernel();
        $em = $this->getEntityManager();

        $user = new User();
        $user->setEmail('test-integration@lakers.com');
        $user->setPassword('hashed_password_test');
        $user->setPseudo('TestLaker');

        $em->persist($user);
        $em->flush();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);
        $found = $userRepository->findOneBy(['email' => 'test-integration@lakers.com']);

        $this->assertNotNull($found);
        $this->assertEquals('TestLaker', $found->getPseudo());
        $this->assertEquals(0, $found->getSolde());
    }

    public function testFindUserByEmail(): void
    {
        self::bootKernel();
        $em = $this->getEntityManager();

        $user = new User();
        $user->setEmail('findme@lakers.com');
        $user->setPassword('hashed_password_test');

        $em->persist($user);
        $em->flush();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);
        $found = $userRepository->findOneBy(['email' => 'findme@lakers.com']);

        $this->assertNotNull($found);
        $this->assertEquals('findme@lakers.com', $found->getEmail());
    }

    protected function tearDown(): void
    {
        $em = $this->getEntityManager();

        $em->createQuery('DELETE FROM App\Entity\User u WHERE u.email LIKE :pattern')
            ->setParameter('pattern', '%lakers.com%')
            ->execute();

        $em->flush();
        $em->close();

        parent::tearDown();
    }
}
