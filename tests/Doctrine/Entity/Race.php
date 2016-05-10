<?php
namespace QueryMap\Tests\Doctrine\Entity;

use QueryMap\Contrib\Annotation\DoctrineAnnotationMapping as QM;
use Doctrine\ORM\Mapping as ORM;

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
     * This will cause an aliasing conflict if started from Creature
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
     * @return $this
     */
    public function setLeadingFaction($leadingFaction)
    {
        $this->leadingFaction = $leadingFaction;

        return $this;
    }
}
