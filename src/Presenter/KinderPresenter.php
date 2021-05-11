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

use App\Entity\Kinder;

/**
 * @method Kinder realObject()
 */
class KinderPresenter extends ObjectPresenter
{
    public array    $flag = ['kinder' => false, 'bpz' => false, 'zba' => false];
}
