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
 * @ORM\Entity(repositoryClass="\QueryMap\Tests\Doctrine\Repository\CreatureRepository")
 * @ORM\Table(name="creatures")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @QM\Map(className="\QueryMap\Tests\Doctrine\QueryMap\CreatureQueryMap")
 */
class Creature
{
    /**
     * @QM\Filter
     * @ORM\Column(name="id")
     * @ORM\Id
     */
    protected $id;

    /**
     * @QM\Filter
     * @ORM\Column(name="name")
     */
    protected $name;

    /**
     * @QM\Filter
     * @ORM\Column(name="age")
     */
    protected $age;

    /**
     * @QM\Filter
     * @ORM\ManyToOne(targetEntity="\QueryMap\Tests\Doctrine\Entity\Race")
     * @ORM\JoinColumn(name="race_id", referencedColumnName="id", nullable=true)
     */
    protected $race;

    /**
     * @QM\Filter
     * @ORM\Column(name="gender")
     */
    protected $gender;

    /**
     * @QM\Filter
     * @ORM\Column(name="status")
     */
    protected $status;

    /**
     * @QM\Filter
     * @ORM\Column(name="species")
     */
    protected $species;

    /**
     * @QM\Filter
     * @ORM\ManyToOne(targetEntity="\QueryMap\Tests\Doctrine\Entity\Faction")
     * @ORM\JoinColumn(name="faction_id", referencedColumnName="id", nullable=true)
     */
    protected $faction;

    /**
     * This is only used for CreatureTest::testNoErrorWhenJoinFilterHasNoQueryMap.
     *
     * @QM\Filter
     * @ORM\ManyToOne(targetEntity="\QueryMap\Tests\Doctrine\Entity\Faction")
     * @ORM\JoinColumn(name="second_faction_id", referencedColumnName="id", nullable=true)
     */
    protected $secondFaction;

    /**
     * @QM\Filter(extraKeys={"cash"})
     * @ORM\Column(name="net_worth")
     */
    protected $netWorth;

    /**
     * @QM\Filter
     * @ORM\Column(name="arrival_date")
     */
    protected $arrivalDate;

    /**
     * Thievery to see how it cracks on alias collision in our own management (not Doctrine's).
     *
     * @QM\Filter
     * @ORM\ManyToOne(targetEntity="\QueryMap\Tests\Doctrine\Entity\Creature")
     * @ORM\JoinColumn(name="creature_parent", referencedColumnName="id", nullable=true)
     */
    protected $aCreatureParent;
}
