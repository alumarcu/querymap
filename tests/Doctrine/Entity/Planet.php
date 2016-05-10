<?php
namespace QueryMap\Tests\Doctrine\Entity;

use QueryMap\Contrib\Annotation\DoctrineAnnotationMapping as QM;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\QueryMap\Tests\Doctrine\Repository\PlanetRepository")
 * @ORM\Table(name="planets")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Planet
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
     * @ORM\ManyToOne(targetEntity="\QueryMap\Tests\Doctrine\Entity\Race")
     * @ORM\JoinColumn(name="leading_race", referencedColumnName="id", nullable=true)
     */
    protected $leadingRace;

    /**
     * @QM\Filter
     * @ORM\Column(name="atmosphere_type")
     */
    protected $atmosphereType;
}
