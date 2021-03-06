<?php
namespace AOE\Crawler\Tests\Functional\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 AOE GmbH <dev@aoe.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use AOE\Crawler\Hooks\ProcessCleanUpHook;
use TYPO3\CMS\Core\Tests\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ProcessCleanUpHookTest
 *
 * @package AOE\Crawler\Tests\Functional\Hooks
 */
class ProcessCleanUpHookTest extends FunctionalTestCase
{

    /**
     * @var ProcessCleanUpHook
     */
    protected $subject;

    /**
     * @var \tx_crawler_domain_process_repository
     */
    protected $processRepository;

    /**
     * @var \tx_crawler_domain_queue_repository
     */
    protected $queueRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = array('typo3conf/ext/crawler');

    public function setUp()
    {
        parent::setUp();

        $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var ProcessCleanUpHook subject */
        $this->subject = $this->objectManager->get('AOE\\Crawler\\Hooks\\ProcessCleanUpHook');
        $this->processRepository = $this->objectManager->get('tx_crawler_domain_process_repository');
        $this->queueRepository = $this->objectManager->get('tx_crawler_domain_queue_repository');

        // Include Fixtures
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_crawler_process.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_crawler_queue.xml');

    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function removeProcessFromProcesslistCalledWithProcessThatDoesNotExist()
    {
        $processCountBefore = $this->processRepository->countAll();
        $queueCountBefore = $this->queueRepository->countAll();

        $notExistingProcessId = 23456;
        $this->callInaccessibleMethod($this->subject,'removeProcessFromProcesslist', $notExistingProcessId);

        $processCountAfter = $this->processRepository->countAll();
        $queueCountAfter = $this->queueRepository->countAll();

        $this->assertEquals(
            $processCountBefore,
            $processCountAfter
        );

        $this->assertEquals(
            $queueCountBefore,
            $queueCountAfter
        );
    }

    /**
     * @test
     */
    public function removeProcessFromProcesslistRemoveOneProcessAndNoQueueRecords()
    {
        $expectedProcessesToBeRemoved = 1;
        $expectedQueueRecordsToBeRemoved = 0;

        $processCountBefore = $this->processRepository->countAll();
        $queueCountBefore = $this->queueRepository->countAll();

        $existingProcessId = 1000;
        $this->callInaccessibleMethod($this->subject,'removeProcessFromProcesslist', $existingProcessId);

        $processCountAfter = $this->processRepository->countAll();
        $queueCountAfter = $this->queueRepository->countAll();

        $this->assertEquals(
            $processCountBefore - $expectedProcessesToBeRemoved,
            $processCountAfter
        );

        $this->assertEquals(
            $queueCountBefore - $expectedQueueRecordsToBeRemoved,
            $queueCountAfter
        );
    }

    /**
     * @test
     */
    public function removeProcessFromProcesslistRemoveOneProcessAndOneQueueRecordIsReset()
    {
        $existingProcessId = 1001;
        $expectedProcessesToBeRemoved = 1;

        $processCountBefore = $this->processRepository->countAll();
        $queueCountBefore = $this->queueRepository->countAll('process_id = ' . $existingProcessId);

        $this->callInaccessibleMethod($this->subject,'removeProcessFromProcesslist', $existingProcessId);

        $processCountAfter = $this->processRepository->countAll();
        $queueCountAfter = $this->queueRepository->countAll('process_id = ' . $existingProcessId);

        $this->assertEquals(
            $processCountBefore - $expectedProcessesToBeRemoved,
            $processCountAfter
        );

        $this->assertEquals(
            1,
            $queueCountBefore
        );

        $this->assertEquals(
            0,
            $queueCountAfter
        );
    }

    /**
     * @test
     */
    public function createResponseArrayReturnsEmptyArray()
    {
        $emptyInputString = '';

        $this->assertEquals(
            array(),
            $this->callInaccessibleMethod($this->subject,'createResponseArray', $emptyInputString)
        );
    }

    /**
     * @test
     */
    public function createResponseArrayReturnsArray()
    {
       // Input string has multiple spacing to ensure we don't end up with an array with empty values
        $inputString = '1   2 2 4 5 6 ';
        $expectedOutputArray = array('1', '2', '2', '4', '5', '6');

        $this->assertEquals(
            $expectedOutputArray,
            $this->callInaccessibleMethod($this->subject,'createResponseArray', $inputString)
        );
    }


}