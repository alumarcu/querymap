<?php
namespace QueryMap\Tests\Doctrine\Entity;

use QueryMap\Contrib\Annotation\DoctrineAnnotationMapping as QM;
use Doctrine\ORM\Mapping as ORM;

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
     * This is only used for CreatureTest::testNoErrorWhenJoinFilterHasNoQueryMap
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
     * Thievery to see how it cracks on alias collision in our own management (not Doctrine's)
     * @QM\Filter
     * @ORM\ManyToOne(targetEntity="\QueryMap\Tests\Doctrine\Entity\Creature")
     * @ORM\JoinColumn(name="creature_parent", referencedColumnName="id", nullable=true)
     */
    protected $aCreatureParent;
}
