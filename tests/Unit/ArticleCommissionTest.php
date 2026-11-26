<?php

namespace Tests\Unit;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleCommissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_pourcentage(): void
    {
        $article = new Article([
            'prix' => 12000,
            'type_commission' => 'pourcentage',
            'valeur_commission' => 10,
        ]);

        $this->assertSame(1200.0, $article->calculerCommission());
    }

    public function test_commission_fixe(): void
    {
        $article = new Article([
            'prix' => 12000,
            'type_commission' => 'fixe',
            'valeur_commission' => 500,
        ]);

        $this->assertSame(500.0, $article->calculerCommission());
    }
}
