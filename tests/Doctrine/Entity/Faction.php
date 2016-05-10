<?php
namespace QueryMap\Tests\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use QueryMap\Contrib\Annotation\DoctrineAnnotationMapping as QM;

/**
 * @ORM\Entity(repositoryClass="\QueryMap\Tests\Doctrine\Repository\FactionRepository")
 * @ORM\Table(name="factions")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @QM\Map(className="\QueryMap\Tests\Doctrine\QueryMap\FactionQueryMap")
 */
class Faction
{
    /**
     * @QM\Filter
     * @ORM\Column(name="id")
     * @ORM\Id
     */
    protected $id;

    /**
     * @QM\Filter(extraKeys={"faction_name"})
     * @ORM\Column(name="name")
     */
    protected $name;

    /**
     * @QM\Filter
     * @ORM\Column(name="gun_count")
     */
    protected $guns;

    /**
     * @QM\Filter
     * @ORM\Column(name="net_worth")
     */
    protected $netWorth;

    /**
     * @QM\Filter
     * @ORM\ManyToOne(targetEntity="\QueryMap\Tests\Doctrine\Entity\Planet")
     * @ORM\JoinColumn(name="capital", referencedColumnName="id", nullable=true)
     */
    protected $capital;
}