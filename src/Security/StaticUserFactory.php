<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class StaticUserFactory
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * StaticUser constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function create($username, $password)
    {
        $user = new StaticUser($username);
        $user->setPassword($this->encoder->encodePassword($user, $password));
        return $user;
    }
}
