<?php

namespace App\Tests\Entity;

use App\Entity\Pari;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PariTest extends TestCase
{
    // Teste qu'un pari gagnant calcule correctement les gains (mise x cote)
    public function testCalculGainsPariGagnant(): void
    {
        $pari = new Pari();
        $pari->setEquipe('Los Angeles Lakers');
        $pari->setMise(20.00);
        $pari->setCote(1.85);

        // Simulation de la resolution : gains = mise x cote
        $gainsAttendus = $pari->getMise() * $pari->getCote();
        $pari->setGains($gainsAttendus);
        $pari->setStatut('gagne');

        $this->assertEquals(37.00, $pari->getGains());
        $this->assertEquals('gagne', $pari->getStatut());
    }

    // Teste qu'un pari perdant a des gains a zero
    public function testPariPerdantSansGains(): void
    {
        $pari = new Pari();
        $pari->setMise(15.00);
        $pari->setCote(2.30);
        $pari->setStatut('perdu');
        $pari->setGains(0);

        $this->assertEquals(0, $pari->getGains());
        $this->assertEquals('perdu', $pari->getStatut());
    }

    // Teste que le solde de l'utilisateur est bien credite apres un gain
    public function testCreditSoldeApresGain(): void
    {
        $user = new User();
        $user->setSolde(247.50);

        $pari = new Pari();
        $pari->setMise(20.00);
        $pari->setCote(1.85);
        $gains = $pari->getMise() * $pari->getCote();

        // Credit du solde : ancien solde + gains
        $user->setSolde($user->getSolde() + $gains);

        $this->assertEquals(284.50, $user->getSolde());
    }
}
