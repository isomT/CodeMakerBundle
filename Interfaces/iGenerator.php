<?php

/**
 * Created by PhpStorm.
 * User: omar.ismail@sbc.tn
 * Date: 06/02/2018
 * Time: 10:22
 */

namespace SBC\CodeMakerBundle\Interfaces;

interface iGenerator
{
    public static function getFullName();
    public static function getSimpleName();
    public static function getAttributesNameId();
}