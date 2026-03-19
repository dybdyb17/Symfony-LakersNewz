<?php

namespace App\Tests\Unit;

use App\Entity\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testNewArticleHasDefaultValues(): void
    {
        $article = new Article();

        $this->assertNotNull($article->getCreatedAt());
        $this->assertNull($article->getUpdatedAt());
        $this->assertEquals('Lakers Newz', $article->getAuteur());
    }

    public function testSetAndGetTitre(): void
    {
        $article = new Article();
        $article->setTitre('LeBron James signe un nouveau record');

        $this->assertEquals('LeBron James signe un nouveau record', $article->getTitre());
    }

    public function testSetAndGetCategorie(): void
    {
        $article = new Article();
        $article->setCategorie('rumeurs');

        $this->assertEquals('rumeurs', $article->getCategorie());
    }
}
