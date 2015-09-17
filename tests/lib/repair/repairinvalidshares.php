<?php
/**
 * Copyright (c) 2015 Vincent Petry <pvince81@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace Test\Repair;

/**
 * Tests for repairing invalid shares
 *
 * @see \OC\Repair\RepairInvalidShares
 */
class RepairInvalidShares extends \Test\TestCase {

	/** @var \OC\RepairStep */
	private $repair;

	/** @var \OCP\IDBConnection */
	private $connection;

	protected function setUp() {
		parent::setUp();

		$config = $this->getMockBuilder('OCP\IConfig')
			->disableOriginalConstructor()
			->getMock();
		$config->expects($this->any())
			->method('getSystemValue')
			->with('version')
			->will($this->returnValue('8.0.0.0'));

		$this->connection = \OC::$server->getDatabaseConnection();

		$this->repair = new \OC\Repair\RepairInvalidShares($config, $this->connection);
	}

	protected function tearDown() {
		$sql = 'DELETE FROM `*PREFIX*share`';
		$this->connection->executeUpdate($sql);

		parent::tearDown();
	}

	/**
	 * Test remove expiration date for non-link shares
	 */
	public function testRemoveExpirationDateForNonLinkShares() {
		$sql =
			'INSERT INTO `*PREFIX*share` ' .
			'(share_type, share_with, uid_owner, item_type, item_source, item_target,' .
			'file_source, file_target, permissions, stime, expiration, token) ' .
			'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

		// user share with bogus expiration date
		$this->connection->executeUpdate(
			$sql,
			[\OC\Share\Constants::SHARE_TYPE_USER, 'recipientuser1', 'user1', 'folder', 123, '/123', 123, '/test', 1, time(), '2015-09-25 00:00:00', null]
		);
		$bogusShareId = $this->connection->lastInsertId();
		// link share with expiration date
		$this->connection->executeUpdate(
			$sql,
			[\OC\Share\Constants::SHARE_TYPE_LINK, null, 'user1', 'folder', 123, '/123', 123, '/test', 1, time(), '2015-09-25 00:00:00', 'abcdefg']
		);

		$this->repair->run();

		$result = $this->connection->executeQuery('SELECT * FROM `*PREFIX*share` ORDER BY `share_type`');
		$results = $result->fetchAll();

		$this->assertCount(2, $results);

		$userShare = $results[0];
		$linkShare = $results[1];
		$this->assertEquals($bogusShareId, $userShare['id'], 'sanity check');
		$this->assertNull($userShare['expiration'], 'bogus expiration date was removed');
		$this->assertNotNull($linkShare['expiration'], 'valid link share expiration date still there');
	}
}

