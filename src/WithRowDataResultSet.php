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

namespace Webware\ResultSet;

use ArrayObject;
use InvalidArgumentException;
use Override;
use PhpDb\ResultSet\AbstractResultSet;
use PhpDb\ResultSet\ResultSetInterface;
use PhpDb\ResultSet\RowPrototypeInterface;

final class WithRowDataResultSet extends AbstractResultSet
{
    public function __construct(
        private WithRowDataPrototypeInterface $rowPrototype,
    ) {}

    #[Override]
    public function current(): ?WithRowDataPrototypeInterface
    {
        $data = parent::current();

        if (is_array($data)) {
            return $this->getRowPrototype()->withRowData($data);
        }

        return null;
    }

    /**
     * @phpstan-param ArrayObject<int|string, mixed>
     *      |RowPrototypeInterface
     *      |WithRowDataPrototypeInterface $rowPrototype
     *
     * @throws InvalidArgumentException
     */
    #[Override]
    public function setRowPrototype(
        ArrayObject|RowPrototypeInterface|WithRowDataPrototypeInterface $rowPrototype,
    ): ResultSetInterface {
        if (! $rowPrototype instanceof WithRowDataPrototypeInterface) {
            throw new InvalidArgumentException('Row prototype must implement ' . WithRowDataPrototypeInterface::class);
        }

        $this->rowPrototype = $rowPrototype;

        return $this;
    }

    #[Override]
    public function getRowPrototype(): WithRowDataPrototypeInterface
    {
        return $this->rowPrototype;
    }
}
