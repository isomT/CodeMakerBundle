<?php

namespace SBC\CodeMakerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * Generator
 *
 * @ORM\Table(name="generator")
 * @ORM\Entity(repositoryClass="SBC\CodeMakerBundle\Repository\GeneratorRepository")
 */
class Generator
{

    public function __construct()
    {
        $this->setUpdatedAt(new DateTime('now'));
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var DateTime $updatedAt
     * @ORM\Column(name="updatedAt", type="datetime", precision=6, nullable=true)
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="discriminatorValue", type="string", length=255, unique=true, nullable=true)
     */
    private $discriminatorValue;

    /**
     * @var string
     *
     * @ORM\Column(name="displayName", type="string", length=255, unique=true)
     */
    private $displayName;

    /**
     * @var string
     *
     * @ORM\Column(name="fullEntityName", type="string", length=255)
     */
    private $className;

    /**
     * @var string
     *
     * @ORM\Column(name="pattern", type="string", length=255)
     */
    private $pregPattern;

    /**
     * @var string
     *
     * @ORM\Column(name="simplePattern", type="string", length=255)
     */
    private $simplePattern;

    /**
     * @var boolean
     *
     * @ORM\Column(name="respectPattern", type="boolean", nullable=false)
     */
    private $respectPattern = false;

    /**
     * @var string
     *
     * @ORM\Column(name="lastCode", type="string", length=255, nullable=true)
     */
    private $lastCode;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return string
     */
    public function getDiscriminatorValue()
    {
        return $this->discriminatorValue;
    }

    /**
     * @param string $discriminatorValue
     */
    public function setDiscriminatorValue($discriminatorValue)
    {
        $this->discriminatorValue = $discriminatorValue;
    }

    /**
     * Set entityName
     *
     * @param string $displayName
     *
     * @return Generator
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Get entityName
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set fullEntityName
     *
     * @param string $className
     *
     * @return Generator
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get fullEntityName
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Set pattern
     *
     * @param string $pregPattern
     *
     * @return Generator
     */
    public function setPregPattern($pregPattern)
    {
        $this->pregPattern = $pregPattern;

        return $this;
    }

    /**
     * Get pattern
     *
     * @return string
     */
    public function getPregPattern()
    {
        return $this->pregPattern;
    }

    /**
     * Set lastCode
     *
     * @param string $lastCode
     *
     * @return Generator
     */
    public function setLastCode($lastCode)
    {
        $this->lastCode = $lastCode;

        return $this;
    }

    /**
     * Get lastCode
     *
     * @return string
     */
    public function getLastCode()
    {
        return $this->lastCode;
    }

    /**
     * @return string
     */
    public function getSimplePattern()
    {
        return $this->simplePattern;
    }

    /**
     * @param string $simplePattern
     */
    public function setSimplePattern($simplePattern)
    {
        $this->simplePattern = $simplePattern;
    }

    /**
     * @return bool
     */
    public function isRespectPattern()
    {
        return $this->respectPattern;
    }

    /**
     * @param bool $respectPattern
     */
    public function setRespectPattern($respectPattern)
    {
        $this->respectPattern = $respectPattern;
    }


}

