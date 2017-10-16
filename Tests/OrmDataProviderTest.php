<?php

namespace Kora\DataProvider\Doctrine\Orm\Tests;

use Doctrine\ORM\QueryBuilder;
use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorImplementationsList;
use PHPUnit\Framework\TestCase;
use Mockery as m;

/**
 * Class OrmDataProviderTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class OrmDataProviderTest extends TestCase
{
	/**
	 * @dataProvider fieldMappingProvider
	 * @param QueryBuilder $queryBuilder
	 * @param array        $mapping
	 * @param              $column
	 * @param              $expectedMapping
	 */
	public function testFieldMapping(QueryBuilder $queryBuilder, array $mapping, $column, $expectedMapping)
	{
		$dataProvider = new OrmDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], $mapping));

		$columnMapping = $dataProvider->getFieldMapping($column);

		$this->assertEquals($expectedMapping, $columnMapping);
	}

	public function fieldMappingProvider()
	{
		$rootAlias = 'a';
		$qb = $this->getMockBuilder(QueryBuilder::class)
			->disableOriginalClone()
			->disableOriginalConstructor()
			->getMock();

		$qb
			->method('getAllAliases')
			->willReturn([$rootAlias, 'b']);

		$mapping = [
			'test' => 'a.test',
		];

		return [
			[$qb, $mapping, 'test', 'a.test'],
			[$qb, $mapping, 'foo', $rootAlias.'.foo'],
		];
	}
}
