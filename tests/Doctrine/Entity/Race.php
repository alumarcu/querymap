<?php

/*
 * The MIT License (MIT)
 * Copyright (c) 2016 Alexandru Marcu <alumarcu@gmail.com>/DMS Team @ eMAG IT Research
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in the
 * Software without restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace QueryMap\Tests\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use QueryMap\Contrib\Annotation\DoctrineAnnotationMapping as QM;

/**
 * @ORM\Entity(repositoryClass="\QueryMap\Tests\Doctrine\Repository\RaceRepository")
 * @ORM\Table(name="races")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Race
{
    /**
     * @QM\Filter
     * @ORM\Column(name="id")
     * @ORM\Id
     */
    protected $id;

    /**
     * @QM\Filter(extraKeys={"race_name"})
     * @ORM\Column(name="name")
     */
    protected $name;

    /**
     * @QM\Filter(extraKeys={"homeland"})
     * @ORM\ManyToOne(targetEntity="\QueryMap\Tests\Doctrine\Entity\Planet")
     * @ORM\JoinColumn(name="home_planet", referencedColumnName="id", nullable=true)
     */
    protected $homePlanet;

    /**
     * This will cause an aliasing conflict if started from Creature.
     *
     * @QM\Filter
     * @ORM\ManyToOne(targetEntity="\QueryMap\Tests\Doctrine\Entity\Faction")
     * @ORM\JoinColumn(name="faction_id", referencedColumnName="id", nullable=true)
     */
    protected $leadingFaction;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHomePlanet()
    {
        return $this->homePlanet;
    }

    /**
     * @param $homePlanet
     *
     * @return $this
     */
    public function setHomePlanet($homePlanet)
    {
        $this->homePlanet = $homePlanet;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLeadingFaction()
    {
        return $this->leadingFaction;
    }

    /**
     * @param $leadingFaction
     *
     * @return $this
     */
    public function setLeadingFaction($leadingFaction)
    {
        $this->leadingFaction = $leadingFaction;

        return $this;
    }
}
