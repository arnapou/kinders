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

use App\Entity\Country;
use App\Entity\Image;
use App\Entity\Kinder;
use App\Entity\Serie;

/**
 * @method ?Country getCountry()
 * @method ?Image   getImage()
 * @method Serie    realObject()
 */
class SeriePresenter extends ObjectPresenter
{
    public bool     $complete = true;
    public ?Country $country = null;
    public ?Image $image = null;
    public array  $stats = ['kinder' => 0, 'bpz' => 0, 'zba' => 0];

    /**
     * @var array<Kinder|KinderPresenter>
     */
    public array  $kinders = [];
}
