<?php
namespace QueryMap\Contrib\Adapter;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use QueryMap\Component\Filter\FilterInterface;
use QueryMap\Component\Map\QueryMapAdapter;
use QueryMap\Component\Reader\Reader;
use QueryMap\Contrib\Filter\AttributeFilter;
use QueryMap\Contrib\Filter\JoinFilter;
use QueryMap\Contrib\Filter\MethodFilter;
use QueryMap\Contrib\Operator\JoinInnerOperator;
use QueryMap\Contrib\Operator\JoinLeftOperator;
use QueryMap\Contrib\Reader\DoctrineReader;
use QueryMap\Exception\QueryMapException;

abstract class DoctrineAdapter extends QueryMapAdapter
{
    /** @var \Doctrine\ORM\QueryBuilder */
    protected $query;

    /** @var \QueryMap\Contrib\Service\QueryMapFactoryInterface */
    protected $configObject;

    /** @var \Doctrine\ORM\EntityRepository */
    protected $repository;

    /**
     * If methods to load/save from cache are not otherwise customized
     * this will be an array cache. It is then assumed cache operations
     * are managed somewhere else.
     * @var array
     */
    protected $tmpCache = array();

    public function __construct($configObject)
    {
        if (!class_exists('\Doctrine\ORM\QueryBuilder')) {
            throw new \Exception('Could not create a DoctrineAdapter instance: cannot find Doctrine libraries!');
        }

        $this->configObject = $configObject;
        $this->repository = $this->configObject->getRepository();
        parent::__construct($this->configObject->getAlias());

        $this->createQuery();
    }

    /**
     * @inheritdoc
     */
    public function getQuerySql()
    {
        return $this->dumpSql($this->query);
    }

    /**
     * @inheritdoc
     */
    public function addToQuery(FilterInterface $filter)
    {
        switch (true) {
            case ($filter instanceof JoinFilter && $filter->isValid()):
                $joinAlias = $filter->getAs();

                if (empty($joinAlias)) {
                    $joinAlias = $this->configObject->getUniqueAlias(substr($filter->getName(), 0, 2));
                }

                $this->joinWith($filter->getOperator(), array($joinAlias, $filter->getName()));
                $filterValue = $filter->getValue();

                if (!empty($filterValue) && is_array($filterValue) && $filter->getQueryMap()) {
                    // For doctrine, use the entity class' annotation
                    // to extract the destination query map
                    $targetEntity = $filter->getQueryMap();

                    if (!class_exists($targetEntity)) {
                        throw new QueryMapException(
                            sprintf(
                                'Could not load QueryMap: %s of field: %s',
                                $targetEntity,
                                $filter->getName()
                            )
                        );
                    }

                    /** @var \QueryMap\Contrib\Map\CommonQueryMap $subQueryMap */
                    $subQueryMap = $this->configObject->createMap($targetEntity, $joinAlias);
                    $subQueryMap->setQuery($this->getQuery())
                        ->add($filterValue)
                        ->make();

                    $this->setQuery($subQueryMap->getQuery());
                } elseif (!empty($filterValue) && is_array($filterValue)) {
                    throw new QueryMapException(
                        sprintf(
                            "You did not correctly define a QueryMap for field: '%s'",
                            $filter->getColumn()
                        )
                    );
                }
                break;
            case ($filter instanceof AttributeFilter):
                $this->getQuery()->andWhere($this->condition($filter));
                break;
            case ($filter instanceof MethodFilter):
                $filter->addToQuery();
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function createQuery()
    {
        $this->query = $this->repository->createQueryBuilder($this->getAlias());
    }

    /**
     * Enables external access to the aliasing logic
     * @param   string $word
     * @return  string
     */
    public static function getWordAlias($word)
    {
        switch ($word) {
            case Reader::WORD_EXTRA_KEYS:
                return 'extraKeys';
        }

        return $word;
    }

    /**
     * @inheritdoc
     */
    public function getWord($word)
    {
        return static::getWordAlias($word);
    }

    /**
     * @inheritdoc
     */
    public function condition(FilterInterface $filter)
    {
        $operator = $filter->getOperator();
        $callback = $operator->getCallback($this);

        if (!is_callable($callback)) {
            throw new QueryMapException(sprintf('Incorrect or missing callback on operator: %s', get_class($operator)));
        }

        $condition = $callback($filter->getName(), $filter->getValue());

        if ($alias = $filter->getAlias()) {
            return sprintf('(%s.%s)', $alias, $condition);
        }

        return sprintf('(%s)', $condition);
    }

    /**
     * @inheritdoc
     */
    public function loadFromCache($key)
    {
        if (array_key_exists($key, $this->tmpCache)) {
            return $this->tmpCache[$key];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function saveToCache($key, $value)
    {
        $this->tmpCache[$key] = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFilterReader()
    {
        /** @var \QueryMap\Contrib\Reader\DoctrineReader $reader */
        $reader = DoctrineReader::getInstance();
        $reader->setAdapter($this);

        return $reader;
    }

    /**
     * @inheritdoc
     */
    protected function joinWith($operator, $name)
    {
        $joinAlias = $name[0];
        $propName = $this->getAlias() . '.' . $name[1];

        switch (true) {
            case ($operator instanceof JoinLeftOperator):
                $this->getQuery()->leftJoin($propName, $joinAlias);
                break;
            case ($operator instanceof JoinInnerOperator):
                $this->getQuery()->innerJoin($propName, $joinAlias);
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function prepare($queryPart, array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                //quote each value individually
                foreach ($value as &$subVal) {
                    $subVal = $this->quote($subVal);
                }
                unset($subVal);
                $quoted = implode(', ', $value);
            } else {
                $quoted = $this->quote($value);
            }

            $queryPart = str_replace(":{$key}", $quoted, $queryPart);
        }

        return $queryPart;
    }

    /**
     * @inheritdoc
     * @return \QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter
     */
    public function getAnnotationAdapter()
    {
        return $this->configObject->getAnnotationAdapter();
    }

    /**
     * Doctrine\DBAL\Connection quotes everything without considering the type;
     * this is only to call the quote method only for strings
     *
     * @param  mixed   $value
     * @return mixed
     */
    protected function quote($value)
    {
        if ('string' === gettype($value)) {
            return $this->configObject->getDb()->quote($value);
        }

        return $value;
    }

    /**
     * Will be removed in future versions
     * @deprecated
     * @param QueryBuilder $query
     * @return String
     */
    protected function dumpSql(QueryBuilder $query)
    {
        $dql = $query->getQuery()->getDQL();
        preg_match_all('/(?<=(?<!Bundle|Bundle:):)[a-zA-Z_]\w+/i', $dql, $results);
        $expectedParams = $results[0];

        $params = array();
        foreach ($expectedParams as $key => $ep) {
            $pp = $query->getParameter($ep);

            $params[$key] = self::processParam($pp);
        }

        //$helper = new DoctrineExtension();
        if (1 || php_sapi_name() === 'cli') {
            //bugged formatter for cli output ... waiting for sqlFormatter update
            \SqlFormatter::$cli = false;

            return \SqlFormatter::format(
                html_entity_decode(
                    strip_tags(
                        $this->replaceQueryParameters(
                            $query->getQuery()->getSQL(),
                            $params
                        )
                    )
                ),
                false
            );
        }

        return \SqlFormatter::format(
            html_entity_decode(
                strip_tags(
                    $this->replaceQueryParameters(
                        $query->getQuery()->getSQL(),
                        $params
                    )
                )
            )
        );
    }

    protected static function processParam($param)
    {
        /** @var $param \Doctrine\ORM\Query\Parameter */
        if (is_object($param) &&
            !$param instanceof \DateTime &&
            !$param instanceof \Doctrine\ORM\Query\Parameter
        ) {
            return $param->getId();
        } elseif ($param instanceof \Doctrine\ORM\Query\Parameter) {
            if ($param->getValue() instanceof \DateTime) {
                return $param->getValue()->format('Y-m-d H:i:s');
            } elseif ($param->getValue() instanceof Collection) {
                $values = array();
                foreach ($param->getValue()->toArray() as $item) {
                    $values[] = self::processParam($item);
                }

                return implode(',', $values);
            } elseif (is_object($param->getValue())) {
                return $param->getValue()->getId();
            } elseif (is_array($param->getValue())) {
                return self::processParam($param->getValue());
            } elseif (is_bool($param->getValue())) {
                return (int)$param->getValue();
            } else {
                return $param->getValue();
            }
        } elseif (is_array($param)) {
            $return = array();
            foreach ($param as $p) {
                $return[] = self::processParam($p);
            }

            return $return;
        } elseif ($param instanceof \DateTime) {
            return $param->format('Y-m-d H:i:s');
        } elseif (!is_object($param)) {
            if (is_bool($param)) {
                return (int)$param;
            }

            return $param;
        } else {
            return $param->getValue();
        }
    }

    protected function replaceQueryParameters($query, $parameters)
    {
        $i = 0;

        $result = preg_replace_callback(
            '/\?|(:[a-zA-Z_]\w+)/i',
            function ($matches) use ($parameters, &$i) {
                $key = substr($matches[0], 1);

                if (!array_key_exists($i, $parameters) && !array_key_exists($key, $parameters)) {
                    return $matches[0];
                }

                $value = array_key_exists($i, $parameters) ? $parameters[$i] : $parameters[$key];
                $result = is_null($value) ? 'NULL' : $this->escapeFunction($value);
                $i++;

                return $result;
            },
            $query
        );

        $result = \SqlFormatter::highlight($result);
        $result = str_replace(array('<pre ', '</pre>'), array('<span ', '</span>'), $result);

        return $result;
    }

    /**
     * Escape parameters of a SQL query
     * DON'T USE THIS FUNCTION OUTSIDE ITS INTENDED SCOPE
     *
     * @internal
     *
     * @param mixed $parameter
     *
     * @return string
     */
    protected function escapeFunction($parameter)
    {
        $result = $parameter;

        switch (true) {
            case is_string($result):
                $result = "'".addslashes($result)."'";
                break;

            case is_array($result):
                foreach ($result as &$value) {
                    $value = $this->escapeFunction($value);
                }

                $result = implode(', ', $result);
                break;

            case is_object($result):
                $result = addslashes((string) $result);
                break;

            case null === $result:
                $result = 'NULL';
                break;

            case is_bool($result):
                $result = $result ? '1' : '0';
                break;
        }

        return $result;
    }
}
