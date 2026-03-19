<?php

namespace App\Tests\Unit;

use App\Entity\Pari;
use PHPUnit\Framework\TestCase;

class PariTest extends TestCase
{
    public function testNewPariHasDefaultValues(): void
    {
        $pari = new Pari();

        $this->assertNotNull($pari->getCreatedAt());
        $this->assertNull($pari->getStatut());
        $this->assertNull($pari->getGains());
    }

    public function testSetAndGetMise(): void
    {
        $pari = new Pari();
        $pari->setMise(50.0);

        $this->assertEquals(50.0, $pari->getMise());
    }

    public function testSetAndGetStatut(): void
    {
        $pari = new Pari();
        $pari->setStatut('gagne');

        $this->assertEquals('gagne', $pari->getStatut());
    }
}
