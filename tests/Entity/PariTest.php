<?php

namespace App\Tests\Entity;

use App\Entity\Pari;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PariTest extends TestCase
{
    public function testCalculGainsPariGagnant(): void
    {
        $pari = new Pari();
        $pari->setEquipe('Los Angeles Lakers');
        $pari->setMise(10.00);
        $pari->setCote(2.00);

        $gainsAttendus = $pari->getMise() * $pari->getCote();
        $pari->setGains($gainsAttendus);
        $pari->setStatut('gagne');

        $this->assertEquals(20.00, $pari->getGains());
        $this->assertEquals('gagne', $pari->getStatut());
    }
    public function testPariPerdantSansGains(): void
    {
        $pari = new Pari();
        $pari->setEquipe('Los Angeles Lakers');
        $pari->setMise(10.00);
        $pari->setCote(2.00);
        $pari->setStatut('perdu');
        $pari->setGains(0);

        $this->assertEquals(0, $pari->getGains());
        $this->assertEquals('perdu', $pari->getStatut());
    }

    public function testCreditSoldeApresGain(): void
    {
        $user = new User();
        $user->setSolde(100.00);

        $pari = new Pari();
        $pari->setMise(10.00);
        $pari->setCote(2.00);

        $user->setSolde($user->getSolde() - $pari->getMise());
        $this->assertEquals(90.00, $user->getSolde());

        $gains = $pari->getMise() * $pari->getCote();
        $user->setSolde($user->getSolde() + $gains);

        $this->assertEquals(110.00, $user->getSolde());
    }
}
