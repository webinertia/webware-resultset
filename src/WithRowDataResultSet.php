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
use PhpDb\ResultSet\RowPrototypeInterface;
use Webmozart\Assert\Assert;

final class WithRowDataResultSet extends AbstractResultSet
{
    public function __construct(
        private WithRowDataPrototypeInterface $rowPrototype,
    ) {}

    /**
     * @return null|WithRowDataPrototypeInterface
     */
    #[Override]
    public function current(): ?WithRowDataPrototypeInterface
    {
        $data = parent::current();

        if (is_array($data)) {
            $prototype = $this->getRowPrototype();
            Assert::isInstanceOf(
                $prototype,
                WithRowDataPrototypeInterface::class,
                'Row prototype must implement ' . WithRowDataPrototypeInterface::class,
            );
            return $prototype->withRowData($data);
        }

        return null;
    }

    #[Override]
    public function getRowPrototype(): WithRowDataPrototypeInterface
    {
        return $this->rowPrototype;
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Override]
    public function setRowPrototype(
        ArrayObject|RowPrototypeInterface|WithRowDataPrototypeInterface $rowPrototype,
    ): static {
        Assert::isInstanceOf(
            $rowPrototype,
            WithRowDataPrototypeInterface::class,
            'Row prototype must implement ' . WithRowDataPrototypeInterface::class,
        );
        // @mago-expect analysis:invalid-property-assignment-value
        $this->rowPrototype = $rowPrototype;

        return $this;
    }
}
