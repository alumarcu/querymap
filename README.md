Provides a simplified interface for building queries with Doctrine.

### Sample usage
```
/** @var \QueryMap\Bundle\QueryMapBundle\Service\QueryMapFactoryService $qmFactory */
$qmFactory = $this->container->get('querymap.factory');

$qm = $qmFactory->create(Team::class, 't');

/** @var $query \Doctrine\ORM\QueryBuilder */
$query = $qm->query([
    'points__gt' => '6',
    'continent' => 'Europe'
]);

echo $query->getDQL();
//SELECT t FROM FootballBundle\Entity\Team t
//WHERE (t.points >= 6) AND (t.continent = 'Europe')

```

### Installation
Edit your composer.json to include the package into your project, and run ```composer update alm/querymap```
```
require {
    ...
    "alm/querymap": "v1.1.*"
    ...
}
```

To your $bundles array in AppKernel.php include the QueryMap bundle and provide to its constructor.
```
$bundles = [
    ...
    new QueryMap\Bundle\QueryMapBundle\QueryMapBundle($this),
    ...
]
```

Next, to your config.yml file you should specify paths for your mapping yml files - if you use this feature - as such:
```
query_map:
    paths:
        - '@FooBundle/Resources/querymap'
        - '@BarBundle/Resources/querymap'

```

For the entities you wish to filter using QueryMap, add the ```@QM\Map``` annotation in the Entity header.
```
use Doctrine\ORM\Mapping as ORM;
use QueryMap\Contrib\Annotation\DoctrineAnnotationMapping as QM;

/**
 * @ORM\Entity(repositoryClass=\MyBundle\Repository\TeamRepository)
 * ...
 * @QM\Map
 */
```

You will now be able to filter by all properties of that entity which are defined as @ORM\Column, @ORM\ManyToOne or @ORM\OneToOne.