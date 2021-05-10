<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Presenter\Front;

use App\Presenter\ObjectPresenterWrapper;

/**
 * @method getId()
 * @method getName()
 */
class CollectionPresenter extends ObjectPresenterWrapper
{
    /**
     * @var SeriePresenter[]
     */
    public array $series = [];
}
