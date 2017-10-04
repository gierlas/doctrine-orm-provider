<?php


namespace Kora\DataProvider\Doctrine\Orm;

use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\QueryBuilder;
use Kora\DataProvider\AbstractDataProvider;
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
	 * @var string[]
	 */
	private $mappings;

	/**
	 * OrmQueryBuilder constructor.
	 *
	 * @param QueryBuilder                $queryBuilder
	 * @param string[]                    $mappings
	 * @param OperatorImplementationsList $implementationsList
	 */
	public function __construct(QueryBuilder $queryBuilder, array $mappings = [], OperatorImplementationsList $implementationsList)
	{
		parent::__construct($implementationsList);
		$this->queryBuilder = $queryBuilder;
		$this->mappings = $mappings;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function getFieldMapping(string $name): string
	{
		if (isset($this->mappings[$name])) return $this->mappings[$name];

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

		$sql = $this->queryBuilder->getQuery()->getSQL();

		$this->queryBuilder->setMaxResults($limit);
		$this->queryBuilder->setFirstResult($offset);

		$stmt = $this->queryBuilder->getEntityManager()->getConnection()->prepare("
			SELECT COUNT(*) 
			FROM ($sql) AS counter
		");

		$parser = new Parser($this->queryBuilder->getQuery());
		$dqlSqlMapping = $parser->parse()->getParameterMappings();

		foreach ($this->queryBuilder->getParameters()->toArray() as $parameter) {
			/** @var Parameter $parameter */
			$stmt->bindValue($dqlSqlMapping[$parameter->getName()][0] + 1, $parameter->getValue(), $parameter->getType());
		}

		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * @return QueryBuilder
	 */
	public function getQueryBuilder(): QueryBuilder
	{
		return $this->queryBuilder;
	}
}