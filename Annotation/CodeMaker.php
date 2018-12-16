<?php

namespace SBC\CodeMakerBundle\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @package SBC\CodeMakerBundle\Annotation
 * @author: Omar Ismail <omarismail.omar@gmail.com>
 *
 * @Annotation
 * @Target("CLASS")
 */
class CodeMaker
{

    /**
     *
     * @var string
     */
    public $displayName;

    /**
     * @Required
     *
     * @var string
     */
    public $codeColumn;

    /**
     *
     * @var string
     */
    public $discriminatorColumn;

    /**
     * List of discrimination
     * @var array<SBC\CodeMakerBundle\Annotation\DiscriminationColumn>
     */
    public $discriminations;

    /**
     * @return array
     */
    public function getDiscriminations()
    {
        return $this->discriminations;
    }

    /**
     * @param array $discriminations
     */
    public function setDiscriminations($discriminations)
    {
        $this->discriminations = $discriminations;
    }

    /**
     * @return string
     */
    public function getDiscriminatorColumn()
    {
        return $this->discriminatorColumn;
    }

    /**
     * @param string $discriminatorColumn
     */
    public function setDiscriminatorColumn($discriminatorColumn)
    {
        $this->discriminatorColumn = $discriminatorColumn;
    }


    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * @return string
     */
    public function getCodeColumn()
    {
        return $this->codeColumn;
    }

    /**
     * @param string $codeColumn
     */
    public function setCodeColumn($codeColumn)
    {
        $this->codeColumn = $codeColumn;
    }

}