<?php

namespace SNSPush;

use InvalidArgumentException;

class Region
{
    /**
     * Supported AWS regions.
     */
    const REGION_EU = 'eu'; // Europe
    const REGION_US = 'us'; // US
    const REGION_CA = 'ca'; // Canada
    const REGION_AP = 'ap'; // Asia Pacific
    const REGION_SA = 'sa'; // South America

    /**
     * List of AWS regions supported by this package.
     *
     * @var array
     */
    protected static $regions = [
        self::REGION_US, self::REGION_EU, self::REGION_AP, self::REGION_CA, self::REGION_SA
    ];

    /**
     * The name of the region.
     *
     * @var string
     */
    protected $name;

    /**
     * The area of the region.
     *
     * @var string
     */
    protected $area;

    /**
     * The number associated with the region.
     *
     * @var integer
     */
    protected $number;

    /**
     * Region constructor.
     *
     * @param $name
     * @param $area
     * @param $number
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($name, $area, $number)
    {
        $this->setName($name);
        $this->setArea($area);
        $this->setNumber($number);
    }

    /**
     * Get the name(area) of the region.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name(area) of the region.
     *
     * @param mixed $name
     *
     * @throws \InvalidArgumentException
     */
    public function setName($name)
    {
        if (!in_array($name, self::$regions, true)) {
            throw new InvalidArgumentException('This region is not supported.');
        }

        $this->name = $name;
    }

    /**
     * Get the area of the region.
     *
     * @return mixed
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set the area of the region.
     *
     * @param mixed $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * Get the number associated with the region.
     *
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set the number for the region.
     *
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * Get a string of the region.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->getName() . '-' . $this->getArea() . '-' . $this->getNumber();
    }

    /**
     * Allow object to be converted to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Parse the region into parts.
     *
     * @param $string
     *
     * @return static
     * @throws \InvalidArgumentException
     */
    public static function parse($string)
    {
        $parts = explode('-', $string);

        if (count($parts) !== 3) {
            throw new InvalidArgumentException('The region is malformed or invalid.');
        }

        return new static($parts[0], $parts[1], $parts[2]);
    }
}