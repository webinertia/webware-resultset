<?php

declare(strict_types=1);

/**
 * This file is part of the Webware ResultSet package.
 *
 * Copyright (c) 2026 Joey Smith <jsmith@webinertia.net>
 * and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webware\ResultSetTest;

use ArrayObject;
use InvalidArgumentException;
use Iterator;
use PhpDb\ResultSet\RowPrototypeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use stdClass;
use Webware\ResultSet\WithRowDataPrototypeInterface;
use Webware\ResultSet\WithRowDataResultSet;

#[CoversClass(WithRowDataResultSet::class)]
#[CoversMethod(WithRowDataResultSet::class, '__construct')]
#[CoversMethod(WithRowDataResultSet::class, 'current')]
#[CoversMethod(WithRowDataResultSet::class, 'setRowPrototype')]
#[CoversMethod(WithRowDataResultSet::class, 'getRowPrototype')]
final class WithRowDataResultSetTest extends TestCase
{
    private WithRowDataPrototypeInterface $rowPrototype;

    public function testCurrentReturnsNullWhenParentReturnsNonArray(): void
    {
        $prototype     = $this->createStub(WithRowDataPrototypeInterface::class);
        $nonArrayValue = new stdClass();

        $iterator = $this->createStub(Iterator::class);
        $iterator->method('valid')->willReturn(true);
        $iterator->method('current')->willReturn($nonArrayValue);
        $iterator->method('key')->willReturn(0);

        $resultSet = new WithRowDataResultSet($prototype);
        $resultSet->initialize($iterator);

        static::assertNull($resultSet->current());
    }

    public function testCurrentReturnsWithRowDataWhenParentReturnsArray(): void
    {
        $data      = ['id' => 1, 'name' => 'test'];
        $prototype = $this->createStub(WithRowDataPrototypeInterface::class);
        $prototype->method('withRowData')->willReturn($prototype);

        $resultSet = new WithRowDataResultSet($prototype);
        $resultSet->initialize([$data]);

        static::assertSame($prototype, $resultSet->current());
    }

    public function testGetRowPrototypeReturnsPrototypePassedToConstructor(): void
    {
        $resultSet = new WithRowDataResultSet($this->rowPrototype);

        static::assertSame($this->rowPrototype, $resultSet->getRowPrototype());
    }

    public function testSetRowPrototypeAcceptsWithRowDataPrototypeInterface(): void
    {
        $resultSet    = new WithRowDataResultSet($this->rowPrototype);
        $newPrototype = $this->createStub(WithRowDataPrototypeInterface::class);

        $result = $resultSet->setRowPrototype($newPrototype);

        static::assertSame($newPrototype, $resultSet->getRowPrototype());
        static::assertSame($resultSet, $result);
    }

    public function testSetRowPrototypeThrowsExceptionForArrayObject(): void
    {
        $resultSet = new WithRowDataResultSet($this->rowPrototype);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Row prototype must implement ' . WithRowDataPrototypeInterface::class,
        );

        $resultSet->setRowPrototype(new ArrayObject());
    }

    public function testSetRowPrototypeThrowsExceptionForPlainRowPrototypeInterface(): void
    {
        $resultSet      = new WithRowDataResultSet($this->rowPrototype);
        $plainPrototype = $this->createStub(RowPrototypeInterface::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Row prototype must implement ' . WithRowDataPrototypeInterface::class,
        );

        $resultSet->setRowPrototype($plainPrototype);
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->rowPrototype = $this->createStub(WithRowDataPrototypeInterface::class);
    }
}
