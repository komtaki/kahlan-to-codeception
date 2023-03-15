<?php

declare(strict_types=1);

namespace Komtaki\KahlanToCodeception;

use PHPUnit\Framework\TestCase;

class KahlanToCodeceptionTest extends TestCase
{
    protected KahlanToCodeception $kahlanToCodeception;

    protected function setUp(): void
    {
        $this->kahlanToCodeception = new KahlanToCodeception();
    }

    public function testIsInstanceOfKahlanToCodeception(): void
    {
        $actual = $this->kahlanToCodeception;
        $this->assertInstanceOf(KahlanToCodeception::class, $actual);
    }
}
