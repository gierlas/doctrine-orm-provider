<?php


namespace Kora\DataProvider\Doctrine\Orm;

use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Kora\DataProvider\AbstractDataProvider;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorImplementationsList;


/**
 * Class OrmQueryBuilder
 * @author Paweł Gierlasiński <gierlasinski.pawel@gmail.com>
 */
class OrmDataProvider extends AbstractDataProvider
{
	/**
	 * @var QueryBuilder
	 */
	private $queryBuilder;

	/**
	 * OrmQueryBuilder constructor.
	 *
	 * @param OperatorImplementationsList $implementationsList
	 * @param QueryBuilder                $queryBuilder
	 * @param Mapper                      $mapper
	 */
	public function __construct(OperatorImplementationsList $implementationsList, QueryBuilder $queryBuilder, Mapper $mapper)
	{
		parent::__construct($implementationsList, $mapper);
		$this->queryBuilder = $queryBuilder;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function getFieldMapping(string $name): string
	{
		$map = $this->mapper->getOperatorFieldMap();
		if (isset($map[$name])) return $map[$name];

		$aliases = $this->queryBuilder->getAllAliases();

		if (empty($aliases)) return $name;

		return current($aliases) . '.' . $name;
	}

	/**
	 * @return array
	 */
	public function fetchFromDataSource(): array
	{
		return $this->queryBuilder->getQuery()->getResult();
	}

	/**
	 * Little dirty, maybe there's better way. It should also handle GROUP BY scenario
	 * @return int
	 */
	public function count(): int
	{
		$limit = $this->queryBuilder->getMaxResults();
		$offset = $this->queryBuilder->getFirstResult();

		$this->queryBuilder->setMaxResults(null);
		$this->queryBuilder->setFirstResult(null);

		$pager = new Paginator($this->queryBuilder->getQuery());

		$this->queryBuilder->setMaxResults($limit);
		$this->queryBuilder->setFirstResult($offset);

		return $pager->count();
	}

	/**
	 * @return QueryBuilder
	 */
	public function getQueryBuilder(): QueryBuilder
	{
		return $this->queryBuilder;
	}
}