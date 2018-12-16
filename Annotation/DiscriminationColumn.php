<?php

namespace SBC\CodeMakerBundle\Annotation;

/**
 * @package SBC\CodeMakerBundle\Annotation
 * @author: Omar Ismail <omarismail.omar@gmail.com>
 *
 * @Annotation
 */
class DiscriminationColumn
{

    /**
     * @var string
     *
     * @DA\Required
     */
    public $value;

    /**
     * @var string
     *
     * @DA\Required
     */
    public $displayName;

}