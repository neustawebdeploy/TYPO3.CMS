<?php
namespace TYPO3\CMS\Extbase\Tests\Unit\Persistence\Generic\Storage;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use Prophecy\Argument;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\InconsistentQuerySettingsException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedOrderException;

class Typo3DbQueryParserTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function addSysLanguageStatementWorksForDefaultLanguage()
    {
        $table = $this->getUniqueId('tx_coretest_table');
        $GLOBALS['TCA'][$table]['ctrl'] = array(
            'languageField' => 'sys_language_uid'
        );
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings|\PHPUnit_Framework_MockObject_MockObject $querySettings */
        $querySettings = $this->getMock(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class);
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $sql = $mockTypo3DbQueryParser->_callRef('getSysLanguageStatement', $table, $table, $querySettings);
        $expectedSql = '(' . $table . '.sys_language_uid IN (0,-1))';
        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @test
     */
    public function addSysLanguageStatementWorksForNonDefaultLanguage()
    {
        $table = $this->getUniqueId('tx_coretest_table');
        $GLOBALS['TCA'][$table]['ctrl'] = array(
            'languageField' => 'sys_language_uid'
        );
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings|\PHPUnit_Framework_MockObject_MockObject $querySettings */
        $querySettings = $this->getMock(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class, array('dummy'));
        $querySettings->setLanguageUid('1');
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $sql = $mockTypo3DbQueryParser->_callRef('getSysLanguageStatement', $table, $table, $querySettings);
        $result = '(' . $table . '.sys_language_uid IN (1,-1))';
        $this->assertSame($result, $sql);
    }

    /**
     * @test
     */
    public function addSysLanguageStatementWorksInBackendContextWithNoGlobalTypoScriptFrontendControllerAvailable()
    {
        $table = $this->getUniqueId('tx_coretest_table');
        $GLOBALS['TCA'][$table]['ctrl'] = array(
            'languageField' => 'sys_language_uid'
        );
        $querySettings = new \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings();
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $sql = $mockTypo3DbQueryParser->_callRef('getSysLanguageStatement', $table, $table, $querySettings);
        $expectedSql = '(' . $table . '.sys_language_uid IN (0,-1))';
        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @test
     */
    public function addSysLanguageStatementWorksForDefaultLanguageWithoutDeleteStatementReturned()
    {
        $table = $this->getUniqueId('tx_coretest_table');
        $GLOBALS['TCA'][$table]['ctrl'] = array(
            'languageField' => 'sys_language_uid',
            'delete' => 'deleted'
        );
        $querySettings = new \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings();
        $querySettings->setLanguageUid(0);
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $sql = $mockTypo3DbQueryParser->_callRef('getSysLanguageStatement', $table, $table, $querySettings);
        $expectedSql = '(' . $table . '.sys_language_uid IN (0,-1))';
        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @test
     */
    public function addSysLanguageStatementWorksForForeignLanguageWithoutSubselection()
    {
        $table = $this->getUniqueId('tx_coretest_table');
        $GLOBALS['TCA'][$table]['ctrl'] = array(
            'languageField' => 'sys_language_uid'
        );
        $querySettings = new \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings();
        $querySettings->setLanguageUid(2);
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $sql = $mockTypo3DbQueryParser->_callRef('getSysLanguageStatement', $table, $table, $querySettings);
        $expectedSql = '(' . $table . '.sys_language_uid IN (2,-1))';
        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @test
     */
    public function addSysLanguageStatementWorksForForeignLanguageWithSubselectionWithoutDeleteStatementReturned()
    {
        $table = $this->getUniqueId('tx_coretest_table');
        $GLOBALS['TCA'][$table]['ctrl'] = array(
            'languageField' => 'sys_language_uid',
            'transOrigPointerField' => 'l10n_parent'
        );
        $querySettings = new \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings();
        $querySettings->setLanguageUid(2);
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $sql = $mockTypo3DbQueryParser->_callRef('getSysLanguageStatement', $table, $table, $querySettings);
        $expectedSql = '(' . $table . '.sys_language_uid IN (2,-1) OR (' . $table . '.sys_language_uid=0 AND ' . $table . '.uid NOT IN (SELECT ' . $table . '.l10n_parent FROM ' . $table . ' WHERE ' . $table . '.l10n_parent>0 AND ' . $table . '.sys_language_uid=2)))';
        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @test
     */
    public function addSysLanguageStatementWorksForForeignLanguageWithSubselectionTakesDeleteStatementIntoAccountIfNecessary()
    {
        $table = $this->getUniqueId('tx_coretest_table');
        $GLOBALS['TCA'][$table]['ctrl'] = array(
            'languageField' => 'sys_language_uid',
            'transOrigPointerField' => 'l10n_parent',
            'delete' => 'deleted'
        );
        $querySettings = new \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings();
        $querySettings->setLanguageUid(2);
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $sql= $mockTypo3DbQueryParser->_callRef('getSysLanguageStatement', $table, $table, $querySettings);
        $expectedSql = '(' . $table . '.sys_language_uid IN (2,-1)' .
                ' OR (' . $table . '.sys_language_uid=0 AND ' . $table . '.uid NOT IN (' .
                'SELECT ' . $table . '.l10n_parent FROM ' . $table .
                ' WHERE ' . $table . '.l10n_parent>0 AND ' .
                $table . '.sys_language_uid=2 AND ' .
                $table . '.deleted=0)))';
        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @test
     */
    public function addSysLanguageStatementWorksInBackendContextWithSubselectionTakesDeleteStatementIntoAccountIfNecessary()
    {
        $table = 'tt_content';
        $GLOBALS['TCA'][$table]['ctrl'] = array(
            'languageField' => 'sys_language_uid',
            'transOrigPointerField' => 'l10n_parent',
            'delete' => 'deleted'
        );
        $querySettings = new \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings();
        $querySettings->setLanguageUid(2);
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $sql = $mockTypo3DbQueryParser->_callRef('getSysLanguageStatement', $table, $table, $querySettings);
        $expectedSql = '(' . $table . '.sys_language_uid IN (2,-1)' .
                ' OR (' . $table . '.sys_language_uid=0 AND ' . $table . '.uid NOT IN (' .
                'SELECT ' . $table . '.l10n_parent FROM ' . $table .
                ' WHERE ' . $table . '.l10n_parent>0 AND ' .
                $table . '.sys_language_uid=2 AND ' .
                $table . '.deleted=0)))';
        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @test
     */
    public function orderStatementGenerationWorks()
    {
        $mockSource = $this->getMock(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\Selector::class, array('getNodeTypeName'), array(), '', false);
        $mockSource->expects($this->any())->method('getNodeTypeName')->will($this->returnValue('Tx_MyExt_ClassName'));
        $mockDataMapper = $this->getMock(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class, array('convertPropertyNameToColumnName', 'convertClassNameToTableName'), array(), '', false);
        $mockDataMapper->expects($this->once())->method('convertClassNameToTableName')->with('Tx_MyExt_ClassName')->will($this->returnValue('tx_myext_tablename'));
        $mockDataMapper->expects($this->once())->method('convertPropertyNameToColumnName')->with('fooProperty', 'Tx_MyExt_ClassName')->will($this->returnValue('converted_fieldname'));
        $sql = array();
        $orderings = array('fooProperty' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING);
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $mockTypo3DbQueryParser->_set('dataMapper', $mockDataMapper);
        $mockTypo3DbQueryParser->_callRef('parseOrderings', $orderings, $mockSource, $sql);
        $expectedSql = array('orderings' => array('tx_myext_tablename.converted_fieldname ASC'));
        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @test
     */
    public function orderStatementGenerationThrowsExceptionOnUnsupportedOrder()
    {
        $this->expectException(UnsupportedOrderException::class);
        $this->expectExceptionCode(1242816074);
        $mockSource = $this->getMock(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\Selector::class, array('getNodeTypeName'), array(), '', false);
        $mockSource->expects($this->never())->method('getNodeTypeName');
        $mockDataMapper = $this->getMock(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class, array('convertPropertyNameToColumnName', 'convertClassNameToTableName'), array(), '', false);
        $mockDataMapper->expects($this->never())->method('convertClassNameToTableName');
        $mockDataMapper->expects($this->never())->method('convertPropertyNameToColumnName');
        $sql = array();
        $orderings = array('fooProperty' => 'unsupported_order');
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $mockTypo3DbQueryParser->_set('dataMapper', $mockDataMapper);
        $mockTypo3DbQueryParser->_callRef('parseOrderings', $orderings, $mockSource, $sql);
    }

    /**
     * @test
     */
    public function orderStatementGenerationWorksWithMultipleOrderings()
    {
        $mockSource = $this->getMock(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\Selector::class, array('getNodeTypeName'), array(), '', false);
        $mockSource->expects($this->any())->method('getNodeTypeName')->will($this->returnValue('Tx_MyExt_ClassName'));
        $mockDataMapper = $this->getMock(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class, array('convertPropertyNameToColumnName', 'convertClassNameToTableName'), array(), '', false);
        $mockDataMapper->expects($this->any())->method('convertClassNameToTableName')->with('Tx_MyExt_ClassName')->will($this->returnValue('tx_myext_tablename'));
        $mockDataMapper->expects($this->any())->method('convertPropertyNameToColumnName')->will($this->returnValue('converted_fieldname'));
        $sql = array();
        $orderings = array(
            'fooProperty' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
            'barProperty' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
        );
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $mockTypo3DbQueryParser->_set('dataMapper', $mockDataMapper);
        $mockTypo3DbQueryParser->_callRef('parseOrderings', $orderings, $mockSource, $sql);
        $expectedSql = array('orderings' => array('tx_myext_tablename.converted_fieldname ASC', 'tx_myext_tablename.converted_fieldname DESC'));
        $this->assertSame($expectedSql, $sql);
    }

    public function providerForVisibilityConstraintStatement()
    {
        return array(
            'in be: include all' => array('BE', true, array(), true, ''),
            'in be: ignore enable fields but do not include deleted' => array('BE', true, array(), false, 'tx_foo_table.deleted_column=0'),
            'in be: respect enable fields but include deleted' => array('BE', false, array(), true, 'tx_foo_table.disabled_column=0 AND (tx_foo_table.starttime_column<=123456789)'),
            'in be: respect enable fields and do not include deleted' => array('BE', false, array(), false, 'tx_foo_table.disabled_column=0 AND (tx_foo_table.starttime_column<=123456789) AND tx_foo_table.deleted_column=0'),
            'in fe: include all' => array('FE', true, array(), true, ''),
            'in fe: ignore enable fields but do not include deleted' => array('FE', true, array(), false, 'tx_foo_table.deleted_column=0'),
            'in fe: ignore only starttime and do not include deleted' => array('FE', true, array('starttime'), false, 'tx_foo_table.deleted_column=0 AND tx_foo_table.disabled_column=0'),
            'in fe: respect enable fields and do not include deleted' => array('FE', false, array(), false, 'tx_foo_table.deleted_column=0 AND tx_foo_table.disabled_column=0 AND tx_foo_table.starttime_column<=123456789')
        );
    }

    /**
     * @test
     * @dataProvider providerForVisibilityConstraintStatement
     */
    public function visibilityConstraintStatementIsGeneratedAccordingToTheQuerySettings($mode, $ignoreEnableFields, $enableFieldsToBeIgnored, $deletedValue, $expectedSql)
    {
        $tableName = 'tx_foo_table';
        $GLOBALS['TCA'][$tableName]['ctrl'] = array(
            'enablecolumns' => array(
                'disabled' => 'disabled_column',
                'starttime' => 'starttime_column'
            ),
            'delete' => 'deleted_column'
        );
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->sys_page = new \TYPO3\CMS\Frontend\Page\PageRepository();
        $GLOBALS['SIM_ACCESS_TIME'] = 123456789;
        $mockQuerySettings = $this->getMock(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class, array('getIgnoreEnableFields', 'getEnableFieldsToBeIgnored', 'getIncludeDeleted'), array(), '', false);
        $mockQuerySettings->expects($this->once())->method('getIgnoreEnableFields')->will($this->returnValue($ignoreEnableFields));
        $mockQuerySettings->expects($this->once())->method('getEnableFieldsToBeIgnored')->will($this->returnValue($enableFieldsToBeIgnored));
        $mockQuerySettings->expects($this->once())->method('getIncludeDeleted')->will($this->returnValue($deletedValue));

        /** @var $mockEnvironmentService \TYPO3\CMS\Extbase\Service\EnvironmentService | \PHPUnit_Framework_MockObject_MockObject */
        $mockEnvironmentService = $this->getMock(\TYPO3\CMS\Extbase\Service\EnvironmentService::class, array('isEnvironmentInFrontendMode'));
        $mockEnvironmentService->expects($this->any())->method('isEnvironmentInFrontendMode')->will($this->returnValue($mode == 'FE'));

        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $mockTypo3DbQueryParser->_set('environmentService', $mockEnvironmentService);
        $resultSql = $mockTypo3DbQueryParser->_callRef('getVisibilityConstraintStatement', $mockQuerySettings, $tableName, $tableName);
        $this->assertSame($expectedSql, $resultSql);
        unset($GLOBALS['TCA'][$tableName]);
    }

    public function providerForRespectEnableFields()
    {
        return array(
            'in be: respectEnableFields=false' => array('BE', false, ''),
            'in be: respectEnableFields=true' => array('BE', true, 'tx_foo_table.disabled_column=0 AND (tx_foo_table.starttime_column<=123456789) AND tx_foo_table.deleted_column=0'),
            'in FE: respectEnableFields=false' => array('FE', false, ''),
            'in FE: respectEnableFields=true' => array('FE', true, 'tx_foo_table.deleted_column=0 AND tx_foo_table.disabled_column=0 AND tx_foo_table.starttime_column<=123456789')
        );
    }

    /**
     * @test
     * @dataProvider providerForRespectEnableFields
     */
    public function respectEnableFieldsSettingGeneratesCorrectStatement($mode, $respectEnableFields, $expectedSql)
    {
        $tableName = 'tx_foo_table';
        $GLOBALS['TCA'][$tableName]['ctrl'] = array(
            'enablecolumns' => array(
                'disabled' => 'disabled_column',
                'starttime' => 'starttime_column'
            ),
            'delete' => 'deleted_column'
        );
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->sys_page = new \TYPO3\CMS\Frontend\Page\PageRepository();
        $GLOBALS['SIM_ACCESS_TIME'] = 123456789;
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings $mockQuerySettings */
        $mockQuerySettings = $this->getMock(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class, array('dummy'), array(), '', false);
        $mockQuerySettings->setIgnoreEnableFields(!$respectEnableFields);
        $mockQuerySettings->setIncludeDeleted(!$respectEnableFields);

        /** @var $mockEnvironmentService \TYPO3\CMS\Extbase\Service\EnvironmentService | \PHPUnit_Framework_MockObject_MockObject */
        $mockEnvironmentService = $this->getMock(\TYPO3\CMS\Extbase\Service\EnvironmentService::class, array('isEnvironmentInFrontendMode'));
        $mockEnvironmentService->expects($this->any())->method('isEnvironmentInFrontendMode')->will($this->returnValue($mode == 'FE'));

        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $mockTypo3DbQueryParser->_set('environmentService', $mockEnvironmentService);
        $actualSql = $mockTypo3DbQueryParser->_callRef('getVisibilityConstraintStatement', $mockQuerySettings, $tableName, $tableName);
        $this->assertSame($expectedSql, $actualSql);
        unset($GLOBALS['TCA'][$tableName]);
    }

    /**
     * @test
     */
    public function visibilityConstraintStatementGenerationThrowsExceptionIfTheQuerySettingsAreInconsistent()
    {
        $this->expectException(InconsistentQuerySettingsException::class);
        $this->expectExceptionCode(1460975922);
        $tableName = 'tx_foo_table';
        $GLOBALS['TCA'][$tableName]['ctrl'] = array(
            'enablecolumns' => array(
                'disabled' => 'disabled_column'
            ),
            'delete' => 'deleted_column'
        );
        $mockQuerySettings = $this->getMock(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class, array('getIgnoreEnableFields', 'getEnableFieldsToBeIgnored', 'getIncludeDeleted'), array(), '', false);
        $mockQuerySettings->expects($this->once())->method('getIgnoreEnableFields')->will($this->returnValue(false));
        $mockQuerySettings->expects($this->once())->method('getEnableFieldsToBeIgnored')->will($this->returnValue(array()));
        $mockQuerySettings->expects($this->once())->method('getIncludeDeleted')->will($this->returnValue(true));

        /** @var $mockEnvironmentService \TYPO3\CMS\Extbase\Service\EnvironmentService | \PHPUnit_Framework_MockObject_MockObject */
        $mockEnvironmentService = $this->getMock(\TYPO3\CMS\Extbase\Service\EnvironmentService::class, array('isEnvironmentInFrontendMode'));
        $mockEnvironmentService->expects($this->any())->method('isEnvironmentInFrontendMode')->will($this->returnValue(true));

        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $mockTypo3DbQueryParser->_set('environmentService', $mockEnvironmentService);
        $mockTypo3DbQueryParser->_callRef('getVisibilityConstraintStatement', $mockQuerySettings, $tableName, $tableName);
        unset($GLOBALS['TCA'][$tableName]);
    }

    /**
     * DataProvider for addPageIdStatement Tests
     */
    public function providerForAddPageIdStatementData()
    {
        $table = $this->getUniqueId('tx_coretest_table');
        return array(
            'set Pid to zero if rootLevel = 1' => array(
                '1',
                $table,
                $table . '.pid = 0'
            ),
            'set Pid to given Pids if rootLevel = 0' => array(
                '0',
                $table,
                $table . '.pid IN (42,27)'
            ),
            'add 0 to given Pids if rootLevel = -1' => array(
                '-1',
                $table,
                $table . '.pid IN (42,27,0)'
            ),
            'set Pid to zero if rootLevel = -1 and no further pids given' => array(
                '-1',
                $table,
                $table . '.pid = 0',
                array()
            ),
            'set no statement for invalid configuration' => array(
                '2',
                $table,
                ''
            )
        );
    }

    /**
     * @test
     * @dataProvider providerForAddPageIdStatementData
     */
    public function addPageIdStatementSetsPidToZeroIfTableDeclaresRootlevel($rootLevel, $table, $expectedSql, $storagePageIds = array(42, 27))
    {
        $GLOBALS['TCA'][$table]['ctrl'] = array(
            'rootLevel' => $rootLevel
        );
        $mockTypo3DbQueryParser = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class, array('dummy'), array(), '', false);
        $mockFrontendVariableCache = $this->getMock(\TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class, array(), array(), '', false);
        $mockDatabaseHandle = $this->prophesize(DatabaseConnection::class);
        $mockDatabaseHandle->cleanIntArray(Argument::cetera())->willReturnArgument(0);
        $mockTypo3DbQueryParser->_set('databaseHandle', $mockDatabaseHandle->reveal());
        $mockTypo3DbQueryParser->_set('tableColumnCache', $mockFrontendVariableCache);
        $mockFrontendVariableCache->expects($this->once())->method('get')->will($this->returnValue(array('pid' => '42')));
        $sql = $mockTypo3DbQueryParser->_callRef('getPageIdStatement', $table, $table, $storagePageIds);

        $this->assertSame($expectedSql, $sql);
    }
}
