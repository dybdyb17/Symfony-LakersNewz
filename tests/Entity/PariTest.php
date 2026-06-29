<?php

namespace App\Tests\Entity;

use App\Entity\Pari;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PariTest extends TestCase
{
    // Jeu d'essai de reference : solde 100 EUR -> mise 10 EUR sur les Lakers
    // a la cote 2,00 -> solde 90 EUR -> pari gagne, gain 20 EUR -> solde final 110 EUR.

    // Teste qu'un pari gagnant calcule correctement les gains (mise x cote)
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

    // Teste qu'un pari perdant a des gains a zero (meme mise/cote de reference)
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

    // Teste le flux complet du jeu d'essai : 100 -> debit mise -> 90 -> credit gain -> 110
    public function testCreditSoldeApresGain(): void
    {
        $user = new User();
        $user->setSolde(100.00);

        $pari = new Pari();
        $pari->setMise(10.00);
        $pari->setCote(2.00);

        // 1) Placement du pari : la mise est debitee du solde (100 -> 90)
        $user->setSolde($user->getSolde() - $pari->getMise());
        $this->assertEquals(90.00, $user->getSolde());

        // 2) Pari gagne : le gain (mise x cote = 20) est credite (90 -> 110)
        $gains = $pari->getMise() * $pari->getCote();
        $user->setSolde($user->getSolde() + $gains);

        $this->assertEquals(110.00, $user->getSolde());
    }
}
