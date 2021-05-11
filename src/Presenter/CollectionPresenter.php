<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Presenter;

use App\Entity\Collection;

/**
 * @method Collection realObject()
 */
class CollectionPresenter extends ObjectPresenter
{
    /**
     * @var SeriePresenter[]
     */
    public array $series = [];
}
