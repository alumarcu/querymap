<?php
namespace QueryMap\Tests\Doctrine\Test;

use QueryMap\Contrib\MappingHelper\CommonMappingHelper;
use QueryMap\Tests\Doctrine\Entity\Creature;
use QueryMap\Tests\Doctrine\Entity\Race;
use QueryMap\Tests\Doctrine\Service\QueryMapService;

class CreatureTest extends \PHPUnit_Framework_TestCase
{
    /** @var \QueryMap\Contrib\Service\QueryMapFactoryInterface */
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = new QueryMapService();
    }

    public function testBasicOperatorsWork()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(
            array(
                'age__gte' => 16,
                'age__lte' => 30,
                'species__neq' => 'Human',
                'status' => 'alive',
                'netWorth__gt' => 10000,
                'net_worth__lt' => 30000 // test: alias from column field
            )
        )->make();

        $resultingSql = static::sqlNormalize($creatureQueryMap->getQuerySql());

        $this->assertStringMatchesFormat('%S(%s.age >= 16)%S', $resultingSql, '!gte');
        $this->assertStringMatchesFormat('%S(%s.age <= 30)%S', $resultingSql, '!lte');
        $this->assertStringMatchesFormat("%S(%s.status = 'alive')%S", $resultingSql, '!eq');
        $this->assertStringMatchesFormat("%S(%s.species <> 'Human')%S", $resultingSql, '!neq');
        $this->assertStringMatchesFormat('%S(%s.net_worth > 10000)%S', $resultingSql, '!gt');
        $this->assertStringMatchesFormat('%S(%s.net_worth < 30000)%S', $resultingSql, '!lt');
    }

    public function testJoinOperatorsCanWorkAsFiltersWithoutJoiningAndTestOtherOperators()
    {
        // use join columns as filters by an id without
        // actually joining also verify other operators

        // (!) this no longer works as it did in zend => no default
        // alias since an entity manager has to be specified anyway
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(
            array(
                'status__in' => array('alive', 'zombie'),
                'faction_id' => null, // => IS NULL
                'species__eq' => 'Indogene',
                'race' => 1,
                'arrivalDate__neq' => null // => IS NOT NULL
            )
        );
        // test: calling add multiple times appends to filters
        $creatureQueryMap->add(array('name__like' => '%Yewkhaji'));
        $creatureQueryMap->make();

        $resultingSql = static::sqlNormalize($creatureQueryMap->getQuerySql());

        $this->assertNotContains('[IGNORED_NULL]', $resultingSql, '!null');
        $this->assertStringMatchesFormat("%S(%s.status IN ('alive', 'zombie'))%S", $resultingSql, '!in');
        $this->assertStringMatchesFormat('%S(%s.faction_id IS NULL)%S', $resultingSql, '!null');
        $this->assertStringMatchesFormat("%S(%s.species = 'Indogene')%S", $resultingSql, '!eq');
        $this->assertStringMatchesFormat('%S(%s.race_id = 1)%S', $resultingSql, '!eq>join_col');
        $this->assertStringMatchesFormat('%S(%s.arrival_date IS NOT NULL)%S', $resultingSql, '!notnull');
        $this->assertStringMatchesFormat("%S(%s.name LIKE '%Yewkhaji')%S", $resultingSql, '!notnull');
    }

    public function testSeveralSimpleJoinCases()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        // create a query to get all creatures whose race lives in a planet with breathable air
        // test: mix between left and inner joins
        $creatureQueryMap->add(
            array(
                'race__ijo' => array(
                    'name__contains' => 'Omec',
                    'homeland__ljo' => array(  // test: the alias for homePlanet
                        'atmosphereType' => 'N2O2CO2H2O'
                    )
                )
            )
        )->make();

        $resultingSql = static::sqlNormalize($creatureQueryMap->getQuerySql());

        $this->assertStringMatchesFormat('%SINNER JOIN races %s ON %s.race_id = %s.id%S', $resultingSql, '!ijo');
        $this->assertStringMatchesFormat('%SLEFT JOIN planets %s ON %s.home_planet = %s.id%S', $resultingSql, '!ljo');
        $this->assertStringMatchesFormat("%S(%s.name LIKE '%Omec%')%S", $resultingSql, '!any');
        $this->assertStringMatchesFormat("%S(%s.atmosphere_type = 'N2O2CO2H2O')%S", $resultingSql, '!ljo>eq');
    }

    public function testMultipleJoinsAndAliasNamingConflictDoesNotHappen()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(
            array(
                'faction__ljo' => array(
                    //the reason to declare id columns as @Filter is to allow
                    //joining with a given id.
                    'id' => 3
                )
            )
        )->make();

        // since Doctrine picks creates its own aliases and we don't need to
        // specify an alias by which to join in our entities, there is no
        // chance of naming collision here; the joins will be performed separately
        $creatureQueryMap->add(
            array(
                'race__ijo' => array(
                    'leadingFaction__ijo' => array(
                        'capital' => 'Bucharest'
                    )
                )
            )
        )->make();

        $resultingSql = static::sqlNormalize($creatureQueryMap->getQuerySql());

        $this->assertStringMatchesFormat('%SLEFT JOIN factions %s ON %s.faction_id = %s.id%S', $resultingSql, '!ljo');
        $this->assertStringMatchesFormat('%SINNER JOIN races %s ON %s.race_id = %s.id%S', $resultingSql, '!ijo_1');
        $this->assertStringMatchesFormat('%SINNER JOIN factions %s ON %s.faction_id = %s.id%S', $resultingSql, '!ijo_2');
        $this->assertStringMatchesFormat('%S(%s.id = 3)%S', $resultingSql, '!ljo>eq');
    }

    public function testMultipleJoinsCircularDependency()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');

        //should work as long as there is no aliasing conflict
        $creatureQueryMap->add(
            array(
                'name__like' => 'Gigel%',
                'faction__ljo' => array(
                    //the reason to declare id columns as @Filter is to allow
                    //joining with a given id.
                    'guns__gte' => 500,
                    'capital__ijo' => array(
                        'leading_race__ijo' => array(
                            'home_planet__ijo' => array(
                                'name__contains' => 'Chiajna'
                            )
                        )
                    )
                )
            )
        )->make();

        $resultingSql = static::sqlNormalize($creatureQueryMap->getQuerySql());

        $this->assertStringMatchesFormat('%SLEFT JOIN factions %s ON %s.faction_id = %s.id%S', $resultingSql, '!ljo');
        $this->assertStringMatchesFormat('%SINNER JOIN planets %s ON %s.capital = %s.id%S', $resultingSql, '!ijo_1');
        $this->assertStringMatchesFormat('%SINNER JOIN races %s ON %s.leading_race = %s.id%S', $resultingSql, '!ijo_2');
        $this->assertStringMatchesFormat('%SINNER JOIN planets %s ON %s.home_planet = %s.id%S', $resultingSql, '!ijo_3');
        $this->assertStringMatchesFormat("%S(%s.name LIKE 'Gigel%')%S", $resultingSql, '!like');
        $this->assertStringMatchesFormat('%S(%s.gun_count >= 500)%S', $resultingSql, '!ljo>gte');
        $this->assertStringMatchesFormat("%S(%s.name LIKE '%Chiajna%')%S", $resultingSql, '!ijo>like');
    }

    /**
     * Exception in QueryMap\Tests\Zend\QueryMap\CreatureQueryMap: Filter with key "arivalDate_gte" does not exist!
     * @expectedException \QueryMap\Exception\QueryMapException
     */
    public function testErrorOnInvalidFilter()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(array('arivalDate_gte' => '12-10-2015'))->make();
    }

    /**
     * Invalid operator:"geq" in QueryMap\Tests\Zend\QueryMap\CreatureQueryMap
     * @expectedException \QueryMap\Exception\QueryMapException
     */
    public function testErrorOnInvalidOperator()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(array('arrivalDate__geq' => '12-10-2015'))->make();
    }

    public function testNoErrorWhenJoinFilterHasNoQueryMap()
    {
        //should work, and join with the table, except that no filters are made
        //inner
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(array('secondFaction__ijo' => array()))->make();

        $resultingSql = static::sqlNormalize($creatureQueryMap->getQuerySql());
        $this->assertStringMatchesFormat('%SINNER JOIN factions %s ON %s.second_faction_id = %s.id%S', $resultingSql, '!ijo');

        //left
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(array('secondFaction__ljo' => array()))->make();

        $resultingSql = static::sqlNormalize($creatureQueryMap->getQuerySql());
        $this->assertStringMatchesFormat('%SLEFT JOIN factions %s ON %s.second_faction_id = %s.id%S', $resultingSql, '!ljo');
    }

    public function testMethodFiltersReallyAddSomethingToTheQuery()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(
            array(
                // test: custom 'between' with alias and explicit suffix (optional, just for fun)
                'creatureArrivedBetween__method' => array('12-10-2014', '12-10-2015'),
                'faction__ijo' => array(
                    'creatureShareGreaterThan' => 10
                    //'creatureShare__gt' > 10  // should it be possible to combine method operator with something else?
                )
            )
        )->make();
        $resultingSql = static::sqlNormalize($creatureQueryMap->getQuerySql());

        $this->assertStringMatchesFormat("%S(%s.arrival_date BETWEEN '12-10-2014' AND '12-10-2015')%S", $resultingSql, '!method');
        $this->assertStringMatchesFormat('%S(((%s.net_worth * 100) / %s.net_worth) > 10)%S', $resultingSql, '!method2');
    }

    /**
     * MethodFilter returned invalid callback or not initialized!
     * @expectedException \QueryMap\Exception\QueryMapException
     */
    public function testButIfMethodFiltersDoNotReturnAClosureAnExceptionIsRaised()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(array('badMethodFilter' => 1337))->make();
    }

    /**
     * When this test is written the alias of subQueryMaps is created
     * from first two initials of the joined column... this might not bode well
     */
    public function testInternalAliasCollision()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(
            array(
                'creature_parent__ijo' => array(
                    'name__like' => '%Wurtt',
                    'creature_parent__ljo' => array(
                        'name__contains' => 'Grandpa'
                    )
                )
            )
        )->make();

        $resultingSql = static::sqlNormalize($creatureQueryMap->getQuerySql());

        $this->assertStringMatchesFormat('%SINNER JOIN creatures %s ON %s.creature_parent = %s.id%S', $resultingSql, '!collision_1');
        $this->assertStringMatchesFormat('%SLEFT JOIN creatures %s ON %s.creature_parent = %s.id%S', $resultingSql, '!collision_2');
        $this->assertStringMatchesFormat("%S(%s.name LIKE '%Wurtt')%S", $resultingSql, '!collision_3');
        $this->assertStringMatchesFormat("%S(%s.name LIKE '%Grandpa%')%S", $resultingSql, '!collision_4');
    }

    /**
     * Operator eq does not support value: QueryMap\Tests\Doctrine\Entity\Race Object
     * @expectedException \QueryMap\Exception\QueryMapException
     */
    public function testCanFilterByIdGivenEntity()
    {
        $race = new Race();
        $race->setId(12);

        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(
            array(
                'race' => $race
            )
        )->make();
    }

    /**
     * Checks the expectations for the between operator
     */
    public function testBetweenOperator()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(
            array(
                'cash__between' => array(10000, 20000),
                'arrivalDate__between' => array('24-12-2015', '31-12-2015')
            )
        )->make();

        $resultingSql = static::sqlNormalize($creatureQueryMap->getQuerySql());

        $this->assertStringMatchesFormat('%Snet_worth BETWEEN 10000 AND 20000%S', $resultingSql, '!between1');
        $this->assertStringMatchesFormat("%Sarrival_date BETWEEN '24-12-2015' AND '31-12-2015'%S", $resultingSql, '!between2');
    }

    public function testAliasForJoin()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->add(
            array(
                'faction__ijo__ftn' => array(),
                'race' => 5     // bonus test: join filter without join
            )
        )->make();

        /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $creatureQueryMap->getQuery();
        $resultingDql = $queryBuilder->getDQL();

        $this->assertContains('INNER JOIN cr.faction ftn', $resultingDql);
        $this->assertContains('(cr.race = 5)', $resultingDql);
    }

    public function testCommonMappingHelper()
    {
        $filtersRaw = array(
            'arrived-before' => '24-12-2020',
            'cash-at-least' => '10000',
            'has-faction-share-above' => '20%'
        );

        $mapping = array(
            'arrived-before' => array(
                'preProcess' => array(CommonMappingHelper::TYPE_STRING, CommonMappingHelper::TRANSFORM_TRIM),
                'validate' => array(CommonMappingHelper::VALID_DATETIME_STRING),
                'process' => array(
                    CommonMappingHelper::TRANSFORM_DATETIME => array(
                        CommonMappingHelper::SET_TIME_MAX
                    )
                ),
                'key' => array('arrivalDate__lte' => null)
            ),
            'cash-at-least' => array(
                'preProcess' => array(CommonMappingHelper::TYPE_INT),
                'validate' => array(CommonMappingHelper::VALID_NOT_EMPTY),
                'key' => array('cash__gte' => null)
            ),
            'has-faction-share-above' => array(
                'preProcess' => array(CommonMappingHelper::TYPE_INT),
                'validate' => array(CommonMappingHelper::VALID_NOT_EMPTY),
                'key' => array(
                    'faction__ijo' => array(
                        'creatureShareGreaterThan' => null
                    )
                )
            )
        );

        /** @var \QueryMap\Contrib\Map\CommonQueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');

        $filters = $creatureQueryMap->transform($filtersRaw, $mapping);
        $creatureQueryMap->add($filters)->make();

        $resultingSql = static::sqlNormalize($creatureQueryMap->getQuerySql());

        $this->assertStringMatchesFormat("%Sarrival_date <= '2020-12-24 23:59:59%S", $resultingSql, '!arrivedBefore');
        $this->assertStringMatchesFormat('%Snet_worth >= 10000%S', $resultingSql, '!cashAtLeast');
        $this->assertStringMatchesFormat('%S(((%s.net_worth * 100) / %s.net_worth) > 20)%S', $resultingSql, '!shareGt');
    }

    /**
     * Defancify query
     * @param $sql
     * @return mixed
     */
    static protected function sqlNormalize($sql)
    {
        $sql = str_replace("\n", '', $sql);

        while ($sql !== ($new = str_replace('  ', ' ', $sql))) {
            $sql = $new;
        }

        $sql = str_replace('( ', '(', $sql);
        $sql = str_replace(' )', ')', $sql);
        $sql = str_replace('`', '', $sql);

        return $sql;
    }
}
