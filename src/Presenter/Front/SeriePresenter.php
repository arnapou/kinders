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

use App\Entity\Country;
use App\Entity\Image;
use App\Presenter\ObjectPresenterWrapper;

/**
 * @method Country getCountry()
 * @method Country getImage()
 */
class SeriePresenter extends ObjectPresenterWrapper
{
    public bool $complete = true;
    public ?Country $country = null;
    public ?Image $image = null;
}
