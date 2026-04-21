<?php

namespace Tests\Unit\Services\Catalog\Volatilite;

use App\Services\Catalog\Volatilite\GroupeComparaisonResolver;
use Tests\TestCase;

class GroupeComparaisonResolverTest extends TestCase
{
    private GroupeComparaisonResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new GroupeComparaisonResolver();
    }

    public function test_mediane_retourne_valeur_centrale_pour_effectif_impair(): void
    {
        $this->assertEquals(3.0, $this->resolver->mediane([1.0, 3.0, 5.0]));
        $this->assertEquals(5.0, $this->resolver->mediane([1.0, 5.0, 3.0, 7.0, 9.0]));
    }

    public function test_mediane_retourne_moyenne_des_deux_centraux_pour_effectif_pair(): void
    {
        $this->assertEqualsWithDelta(3.5, $this->resolver->mediane([1.0, 3.0, 4.0, 7.0]), 0.001);
    }

    public function test_mediane_null_si_tableau_vide(): void
    {
        $this->assertNull($this->resolver->mediane([]));
    }

    public function test_mediane_ignore_les_null(): void
    {
        $this->assertEquals(3.0, $this->resolver->mediane([1.0, null, 3.0, null, 5.0]));
    }
}
