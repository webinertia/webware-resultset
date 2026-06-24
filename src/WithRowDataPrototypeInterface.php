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

use PhpDb\ResultSet\RowPrototypeInterface;

interface WithRowDataPrototypeInterface extends RowPrototypeInterface
{
    /**
     * @param array<array-key, mixed> $withRowData
     */
    public function withRowData(array $withRowData): static;
}
