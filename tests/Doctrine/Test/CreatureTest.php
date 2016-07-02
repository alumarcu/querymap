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

namespace QueryMap\Tests\Doctrine\Test;

use QueryMap\Contrib\MappingHelper\CommonMappingHelper;
use QueryMap\Tests\Doctrine\Entity\Creature;
use QueryMap\Tests\Doctrine\Entity\Race;
use QueryMap\Tests\Doctrine\Service\QueryMapFactoryMockService;

class CreatureTest extends \PHPUnit_Framework_TestCase
{
    /** @var \QueryMap\Contrib\Service\QueryMapFactory */
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = new QueryMapFactoryMockService();
    }

    public function testBasicOperatorsWork()
    {
        // TODO: Improve this test so that it builds the query filters and asserts in a loop
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');

        $query = $creatureQueryMap->query([
            'age__gte' => 16,
            'age__lte' => 30,
            'species__neq' => 'Human',
            'status' => 'alive',
            'netWorth__gt' => 10000,
            'net_worth__lt' => 30000, // test: alias from column field
        ]);

        $dql = $query->getDQL();
        $this->assertContains('cr.age >= :age', $dql, '!gte');
        $this->assertContains('cr.age <= :age', $dql, '!lte');
        $this->assertContains("cr.status = :status", $dql, '!eq');
        $this->assertContains("cr.species != :species", $dql, '!neq');
        $this->assertContains('cr.netWorth > :netWorth', $dql, '!gt');
        $this->assertContains('cr.netWorth < :netWorth', $dql, '!lt');

        $this->assertNotEmpty($query->getParameter('age#gte'));
        $this->assertEquals(16, $query->getParameter('age#gte')->getValue());
        $this->assertNotEmpty($query->getParameter('age#lte'));
        $this->assertEquals(30, $query->getParameter('age#lte')->getValue());
        $this->assertNotEmpty($query->getParameter('species#neq'));
        $this->assertEquals('Human', $query->getParameter('species#neq')->getValue());
        $this->assertNotEmpty($query->getParameter('status#eq'));
        $this->assertEquals('alive', $query->getParameter('status#eq')->getValue());
        $this->assertNotEmpty($query->getParameter('netWorth#gt'));
        $this->assertEquals(10000, $query->getParameter('netWorth#gt')->getValue());
        $this->assertNotEmpty($query->getParameter('netWorth#lt'));
        $this->assertEquals(30000, $query->getParameter('netWorth#lt')->getValue());
    }

    public function testJoinOperatorsCanWorkAsFiltersWithoutJoiningAndTestOtherOperators()
    {
        // use join columns as filters by an id without
        // actually joining also verify other operators

        // (!) this no longer works as it did in zend => no default
        // alias since an entity manager has to be specified anyway
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $query = $creatureQueryMap->query([
            'status__in' => ['alive', 'zombie'],
            'faction_id' => null, // => IS NULL
            'species__eq' => 'Indogene',
            'race' => 1,
            'arrivalDate__neq' => null, // => IS NOT NULL
        ]);

        // test: calling add multiple times appends to filters
        $creatureQueryMap->query(['name__like' => '%Yewkhaji'], true);

        $dql = $query->getDQL();
        $this->assertNotContains('[IGNORED_NULL]', $dql, '!null');
        $this->assertContains("cr.status IN (:status#in)", $dql, '!in');
        $this->assertContains('cr.faction IS NULL', $dql, '!null');
        $this->assertContains("cr.species = :species#eq", $dql, '!eq');
        $this->assertContains('cr.race = :race#eq', $dql, '!eq>join_col');
        $this->assertContains('cr.arrivalDate IS NOT NULL', $dql, '!notnull');
        $this->assertContains("cr.name LIKE :name#like", $dql, '!notnull');
    }

    public function testSeveralSimpleJoinCases()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        // create a query to get all creatures whose race lives in a planet with breathable air
        // test: mix between left and inner joins
        $query = $creatureQueryMap->query([
            'race__ijo' => [ // the alias is expected to be the first two characters of the property: 'ra'. since no explicit one was given
                'name__contains' => 'Omec',
                'homeland__ljo' => ['atmosphereType' => 'N2O2CO2H2O'], // test: the alias for homePlanet => alias will be 'ho'
            ],
        ]);

        $dql = $query->getDQL();
        $this->assertContains('INNER JOIN cr.race ra', $dql, '!ijo');
        $this->assertContains('LEFT JOIN ra.homePlanet ', $dql, '!ljo');
        $this->assertContains("(ra.name LIKE '%Omec%')", $dql, '!any');
        $this->assertContains("(ho.atmosphereType = 'N2O2CO2H2O')", $dql, '!ljo>eq');
    }

    public function testMultipleJoinsAndAliasNamingConflictDoesNotHappen()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->query([
            'faction__ljo' => [
                //the reason to declare id columns as @Filter is to allow
                //joining with a given id.
                'id' => 3,
            ],
        ], $creatureQueryMap::REUSE_FILTERS);

        // since Doctrine picks creates its own aliases and we don't need to
        // specify an alias by which to join in our entities, there is no
        // chance of naming collision here; the joins will be performed separately
        $query = $creatureQueryMap->query([
            'race__ijo' => [
                'leadingFaction__ijo' => [
                    'capital' => 'Bucharest',
                ],
            ],
        ]);

        $dql = $query->getDQL();
        $this->assertContains('LEFT JOIN cr.faction fa', $dql, '!ljo');
        $this->assertContains('INNER JOIN cr.race ra', $dql, '!ijo_1');
        $this->assertContains('INNER JOIN ra.leadingFaction le', $dql, '!ijo_2');
        $this->assertContains('(fa.id = 3)', $dql, '!ljo>eq');
    }

    public function testMultipleJoinsCircularDependency()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');

        //should work as long as there is no aliasing conflict
        $query = $creatureQueryMap->query([
            'name__like' => 'Gigel%',
            'faction__ljo' => [
                'guns__gte' => 500,
                'capital__ijo' => [
                    'leading_race__ijo' => [
                        'home_planet__ijo' => [
                            'name__contains' => 'Chiajna',
                        ],
                    ],
                ],
            ],
        ]);

        $dql = $query->getDQL();
        $this->assertContains('LEFT JOIN cr.faction fa', $dql, '!ljo');
        $this->assertContains('INNER JOIN fa.capital ca', $dql, '!ijo_1');
        $this->assertContains('INNER JOIN ca.leadingRace le', $dql, '!ijo_2');
        $this->assertContains('INNER JOIN le.homePlanet ho', $dql, '!ijo_3');
        $this->assertContains("(cr.name LIKE 'Gigel%')", $dql, '!like');
        $this->assertContains('(fa.guns >= 500)', $dql, '!ljo>gte');
        $this->assertContains("(ho.name LIKE '%Chiajna%')", $dql, '!ijo>like');
    }

    /**
     * Exception in QueryMap\Tests\Zend\QueryMap\CreatureQueryMap: Filter with key "arivalDate_gte" does not exist!
     *
     * @expectedException \QueryMap\Exception\QueryMapException
     */
    public function testErrorOnInvalidFilter()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->query(['arivalDate_gte' => '12-10-2015']);
    }

    /**
     * Invalid operator:"geq" in QueryMap\Tests\Zend\QueryMap\CreatureQueryMap.
     *
     * @expectedException \QueryMap\Exception\QueryMapException
     */
    public function testErrorOnInvalidOperator()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->query(['arrivalDate__geq' => '12-10-2015']);
    }

    public function testNoErrorWhenJoinFilterHasNoQueryMap()
    {
        //should work, and join with the table, except that no filters are made
        //inner
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $query = $creatureQueryMap->query(['secondFaction__ijo' => []]);

        $dql = $query->getDQL();
        $this->assertContains('INNER JOIN cr.secondFaction se', $dql, '!ijo');

        //left
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $query = $creatureQueryMap->query(['secondFaction__ljo' => []]);

        $dql = $query->getDQL();
        $this->assertContains('LEFT JOIN cr.secondFaction se', $dql, '!ijo');
    }

    public function testMethodFiltersReallyAddSomethingToTheQuery()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $query = $creatureQueryMap->query([
            // test: custom 'between' with alias and explicit suffix (optional, just for fun)
            'creatureArrivedBetween__method' => ['12-10-2014', '12-10-2015'],
            'faction__ijo' => [
                'creatureShareGreaterThan' => 10,
                //'creatureShare__gt' > 10  // should it be possible to combine method operator with something else?
            ],
        ]);

        $dql = $query->getDQL();
        $this->assertContains('(cr.arrivalDate BETWEEN :arrivalDateStart AND :arrivalDateEnd)', $dql, '!method');
        $this->assertContains('(((cr.netWorth * 100) / fa.netWorth) > :percent)', $dql, '!method2');

        $this->assertEquals('12-10-2014', $query->getParameter('arrivalDateStart')->getValue());
        $this->assertEquals('12-10-2015', $query->getParameter('arrivalDateEnd')->getValue());
        $this->assertEquals('10', $query->getParameter('percent')->getValue());
    }

    /**
     * MethodFilter returned invalid callback or not initialized!
     *
     * @expectedException \QueryMap\Exception\QueryMapException
     */
    public function testButIfMethodFiltersDoNotReturnAClosureAnExceptionIsRaised()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->query(['badMethodFilter' => 1337]);
    }

    /**
     * When this test is written the alias of subQueryMaps is created
     * from first two initials of the joined column... this might not bode well.
     */
    public function testInternalAliasCollision()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $query = $creatureQueryMap->query([
            'creature_parent__ijo' => [
                'name__like' => '%Wurtt',
                'creature_parent__ljo' => [
                    'name__contains' => 'Grandpa',
                ],
            ],
        ]);

        $dql = $query->getDQL();
        $this->assertContains('INNER JOIN cr.aCreatureParent aC', $dql, '!collision_1');
        $this->assertContains('LEFT JOIN aC.aCreatureParent aC2', $dql, '!collision_2');
        $this->assertContains("(aC.name LIKE '%Wurtt')", $dql, '!collision_3');
        $this->assertContains("(aC2.name LIKE '%Grandpa%')", $dql, '!collision_4');
    }

    /**
     * Operator eq does not support value: QueryMap\Tests\Doctrine\Entity\Race Object.
     *
     * @expectedException \QueryMap\Exception\QueryMapException
     */
    public function testCanFilterByIdGivenEntity()
    {
        $race = new Race();
        $race->setId(12);

        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $creatureQueryMap->query(['race' => $race]);
    }

    /**
     * Checks the expectations for the between operator.
     */
    public function testBetweenOperator()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $query = $creatureQueryMap->query([
            'cash__between' => [10000, 20000],
            'arrivalDate__between' => ['24-12-2015', '31-12-2015'],
        ]);

        $dql = $query->getDQL();
        $this->assertContains('cr.netWorth BETWEEN 10000 AND 20000', $dql, '!between1');
        $this->assertContains("cr.arrivalDate BETWEEN '24-12-2015' AND '31-12-2015'", $dql, '!between2');
    }

    public function testAliasForJoin()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $query = $creatureQueryMap->query([
            'faction__ijo__ftn' => [],
            'race' => 5,     // bonus test: join filter without join
        ]);

        $dql = $query->getDQL();
        $this->assertContains('INNER JOIN cr.faction ftn', $dql);
        $this->assertContains('(cr.race = 5)', $dql);
    }

    public function testCommonMappingHelper()
    {
        $filtersRaw = [
            'arrived-before' => '24-12-2020',
            'cash-at-least' => '10000',
            'has-faction-share-above' => '20%',
        ];

        $mapping = [
            'arrived-before' => [
                'preProcess' => [CommonMappingHelper::TYPE_STRING, CommonMappingHelper::TRANSFORM_TRIM],
                'validate' => [CommonMappingHelper::VALID_DATETIME_STRING],
                'process' => [
                    CommonMappingHelper::TRANSFORM_DATETIME => [
                        CommonMappingHelper::SET_TIME_MAX,
                    ],
                ],
                'key' => ['arrivalDate__lte' => null],
            ],
            'cash-at-least' => [
                'preProcess' => [CommonMappingHelper::TYPE_INT],
                'validate' => [CommonMappingHelper::VALID_NOT_EMPTY],
                'key' => ['cash__gte' => null],
            ],
            'has-faction-share-above' => [
                'preProcess' => [CommonMappingHelper::TYPE_INT],
                'validate' => [CommonMappingHelper::VALID_NOT_EMPTY],
                'key' => [
                    'faction__ijo' => [
                        'creatureShareGreaterThan' => null,
                    ],
                ],
            ],
        ];

        /** @var \QueryMap\Contrib\Map\CommonQueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');

        $filters = $creatureQueryMap->transform($filtersRaw, $mapping);
        $query = $creatureQueryMap->query($filters);

        $dql = $query->getDQL();
        $this->assertContains("cr.arrivalDate <= '2020-12-24 23:59:59", $dql, '!arrivedBefore');
        $this->assertContains('cr.netWorth >= 10000', $dql, '!cashAtLeast');
        $this->assertContains('(((cr.netWorth * 100) / fa.netWorth) > :percent)', $dql, '!shareGt');
        $this->assertEquals('20', $query->getParameter('percent')->getValue());
    }

    public function testSearchInSetUsingBits()
    {
        /** @var \QueryMap\Component\Map\QueryMap $creatureQueryMap */
        $creatureQueryMap = $this->service->create(Creature::class, 'cr');
        $query = $creatureQueryMap->query([
            'flags' => 9 // bit 0 and 3 (2^0 + 2^3)
        ]);

        $dql = $query->getDQL();
        $this->assertContains('(cr.flags = 9)', $dql);

        // TODO: Carefully figure how to organize the bit filter
        // - should move BIT_AND > 0 to contains operator ??
        // --> contains this OR contains that ==> How do we do it??
        // TODO: OR mechanism, multi-usage of same filter
        $query = $creatureQueryMap->query([
            'flags__and' => 1 // flags and 1 > 0
        ]);

        $dql = $query->getDQL();
        $this->assertContains('(cr.flags = 9)', $dql);
    }
}
