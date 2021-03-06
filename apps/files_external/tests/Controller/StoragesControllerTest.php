<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Vincent Petry <pvince81@owncloud.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files_External\Tests\Controller;

use OCA\Files_External\Controller\GlobalStoragesController;
use OCA\Files_External\Lib\Auth\AuthMechanism;
use OCA\Files_External\Lib\Backend\Backend;

use OCA\Files_External\Lib\StorageConfig;
use OCA\Files_External\NotFoundException;
use OCA\Files_External\Service\GlobalStoragesService;
use OCP\AppFramework\Http;

abstract class StoragesControllerTest extends \Test\TestCase {

	/**
	 * @var GlobalStoragesController
	 */
	protected $controller;

	/**
	 * @var GlobalStoragesService
	 */
	protected $service;

	protected function setUp(): void {
		\OC_Mount_Config::$skipTest = true;
	}

	protected function tearDown(): void {
		\OC_Mount_Config::$skipTest = false;
	}

	/**
	 * @return \OCA\Files_External\Lib\Backend\Backend
	 */
	protected function getBackendMock($class = '\OCA\Files_External\Lib\Backend\SMB', $storageClass = '\OCA\Files_External\Lib\Storage\SMB') {
		$backend = $this->getMockBuilder(Backend::class)
			->disableOriginalConstructor()
			->getMock();
		$backend->method('getStorageClass')
			->willReturn($storageClass);
		$backend->method('getIdentifier')
			->willReturn('identifier:'.$class);
		return $backend;
	}

	/**
	 * @return \OCA\Files_External\Lib\Auth\AuthMechanism
	 */
	protected function getAuthMechMock($scheme = 'null', $class = '\OCA\Files_External\Lib\Auth\NullMechanism') {
		$authMech = $this->getMockBuilder(AuthMechanism::class)
			->disableOriginalConstructor()
			->getMock();
		$authMech->method('getScheme')
			->willReturn($scheme);
		$authMech->method('getIdentifier')
			->willReturn('identifier:'.$class);

		return $authMech;
	}

	public function testAddStorage() {
		$authMech = $this->getAuthMechMock();
		$authMech->method('validateStorage')
			->willReturn(true);
		$authMech->method('isVisibleFor')
			->willReturn(true);
		$backend = $this->getBackendMock();
		$backend->method('validateStorage')
			->willReturn(true);
		$backend->method('isVisibleFor')
			->willReturn(true);

		$storageConfig = new StorageConfig(1);
		$storageConfig->setMountPoint('mount');
		$storageConfig->setBackend($backend);
		$storageConfig->setAuthMechanism($authMech);
		$storageConfig->setBackendOptions([]);

		$this->service->expects($this->once())
			->method('createStorage')
			->will($this->returnValue($storageConfig));
		$this->service->expects($this->once())
			->method('addStorage')
			->will($this->returnValue($storageConfig));

		$response = $this->controller->create(
			'mount',
			'\OCA\Files_External\Lib\Storage\SMB',
			'\OCA\Files_External\Lib\Auth\NullMechanism',
			array(),
			[],
			[],
			[],
			null
		);

		$data = $response->getData();
		$this->assertEquals(Http::STATUS_CREATED, $response->getStatus());
		$this->assertEquals($storageConfig, $data);
	}

	public function testUpdateStorage() {
		$authMech = $this->getAuthMechMock();
		$authMech->method('validateStorage')
			->willReturn(true);
		$authMech->method('isVisibleFor')
			->willReturn(true);
		$backend = $this->getBackendMock();
		$backend->method('validateStorage')
			->willReturn(true);
		$backend->method('isVisibleFor')
			->willReturn(true);

		$storageConfig = new StorageConfig(1);
		$storageConfig->setMountPoint('mount');
		$storageConfig->setBackend($backend);
		$storageConfig->setAuthMechanism($authMech);
		$storageConfig->setBackendOptions([]);

		$this->service->expects($this->once())
			->method('createStorage')
			->will($this->returnValue($storageConfig));
		$this->service->expects($this->once())
			->method('updateStorage')
			->will($this->returnValue($storageConfig));

		$response = $this->controller->update(
			1,
			'mount',
			'\OCA\Files_External\Lib\Storage\SMB',
			'\OCA\Files_External\Lib\Auth\NullMechanism',
			array(),
			[],
			[],
			[],
			null
		);

		$data = $response->getData();
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
		$this->assertEquals($storageConfig, $data);
	}

	function mountPointNamesProvider() {
		return array(
			array(''),
			array('/'),
			array('//'),
		);
	}

	/**
	 * @dataProvider mountPointNamesProvider
	 */
	public function testAddOrUpdateStorageInvalidMountPoint($mountPoint) {
		$storageConfig = new StorageConfig(1);
		$storageConfig->setMountPoint($mountPoint);
		$storageConfig->setBackend($this->getBackendMock());
		$storageConfig->setAuthMechanism($this->getAuthMechMock());
		$storageConfig->setBackendOptions([]);

		$this->service->expects($this->exactly(2))
			->method('createStorage')
			->will($this->returnValue($storageConfig));
		$this->service->expects($this->never())
			->method('addStorage');
		$this->service->expects($this->never())
			->method('updateStorage');

		$response = $this->controller->create(
			$mountPoint,
			'\OCA\Files_External\Lib\Storage\SMB',
			'\OCA\Files_External\Lib\Auth\NullMechanism',
			array(),
			[],
			[],
			[],
			null
		);

		$this->assertEquals(Http::STATUS_UNPROCESSABLE_ENTITY, $response->getStatus());

		$response = $this->controller->update(
			1,
			$mountPoint,
			'\OCA\Files_External\Lib\Storage\SMB',
			'\OCA\Files_External\Lib\Auth\NullMechanism',
			array(),
			[],
			[],
			[],
			null
		);

		$this->assertEquals(Http::STATUS_UNPROCESSABLE_ENTITY, $response->getStatus());
	}

	public function testAddOrUpdateStorageInvalidBackend() {
		$this->service->expects($this->exactly(2))
			->method('createStorage')
			->will($this->throwException(new \InvalidArgumentException()));
		$this->service->expects($this->never())
			->method('addStorage');
		$this->service->expects($this->never())
			->method('updateStorage');

		$response = $this->controller->create(
			'mount',
			'\OC\Files\Storage\InvalidStorage',
			'\OCA\Files_External\Lib\Auth\NullMechanism',
			array(),
			[],
			[],
			[],
			null
		);

		$this->assertEquals(Http::STATUS_UNPROCESSABLE_ENTITY, $response->getStatus());

		$response = $this->controller->update(
			1,
			'mount',
			'\OC\Files\Storage\InvalidStorage',
			'\OCA\Files_External\Lib\Auth\NullMechanism',
			array(),
			[],
			[],
			[],
			null
		);

		$this->assertEquals(Http::STATUS_UNPROCESSABLE_ENTITY, $response->getStatus());
	}

	public function testUpdateStorageNonExisting() {
		$authMech = $this->getAuthMechMock();
		$authMech->method('validateStorage')
			->willReturn(true);
		$authMech->method('isVisibleFor')
			->willReturn(true);
		$backend = $this->getBackendMock();
		$backend->method('validateStorage')
			->willReturn(true);
		$backend->method('isVisibleFor')
			->willReturn(true);

		$storageConfig = new StorageConfig(255);
		$storageConfig->setMountPoint('mount');
		$storageConfig->setBackend($backend);
		$storageConfig->setAuthMechanism($authMech);
		$storageConfig->setBackendOptions([]);

		$this->service->expects($this->once())
			->method('createStorage')
			->will($this->returnValue($storageConfig));
		$this->service->expects($this->once())
			->method('updateStorage')
			->will($this->throwException(new NotFoundException()));

		$response = $this->controller->update(
			255,
			'mount',
			'\OCA\Files_External\Lib\Storage\SMB',
			'\OCA\Files_External\Lib\Auth\NullMechanism',
			array(),
			[],
			[],
			[],
			null
		);

		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}

	public function testDeleteStorage() {
		$this->service->expects($this->once())
			->method('removeStorage');

		$response = $this->controller->destroy(1);
		$this->assertEquals(Http::STATUS_NO_CONTENT, $response->getStatus());
	}

	public function testDeleteStorageNonExisting() {
		$this->service->expects($this->once())
			->method('removeStorage')
			->will($this->throwException(new NotFoundException()));

		$response = $this->controller->destroy(255);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}

	public function testGetStorage() {
		$backend = $this->getBackendMock();
		$authMech = $this->getAuthMechMock();
		$storageConfig = new StorageConfig(1);
		$storageConfig->setMountPoint('test');
		$storageConfig->setBackend($backend);
		$storageConfig->setAuthMechanism($authMech);
		$storageConfig->setBackendOptions(['user' => 'test', 'password', 'password123']);
		$storageConfig->setMountOptions(['priority' => false]);

		$this->service->expects($this->once())
			->method('getStorage')
			->with(1)
			->will($this->returnValue($storageConfig));
		$response = $this->controller->show(1);

		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
		$this->assertEquals($storageConfig, $response->getData());
	}

	public function validateStorageProvider() {
		return [
			[true, true, true],
			[false, true, false],
			[true, false, false],
			[false, false, false]
		];
	}

	/**
	 * @dataProvider validateStorageProvider
	 */
	public function testValidateStorage($backendValidate, $authMechValidate, $expectSuccess) {
		$backend = $this->getBackendMock();
		$backend->method('validateStorage')
			->willReturn($backendValidate);
		$backend->method('isVisibleFor')
			->willReturn(true);

		$authMech = $this->getAuthMechMock();
		$authMech->method('validateStorage')
			->will($this->returnValue($authMechValidate));
		$authMech->method('isVisibleFor')
			->willReturn(true);

		$storageConfig = new StorageConfig();
		$storageConfig->setMountPoint('mount');
		$storageConfig->setBackend($backend);
		$storageConfig->setAuthMechanism($authMech);
		$storageConfig->setBackendOptions([]);

		$this->service->expects($this->once())
			->method('createStorage')
			->will($this->returnValue($storageConfig));

		if ($expectSuccess) {
			$this->service->expects($this->once())
				->method('addStorage')
				->with($storageConfig)
				->will($this->returnValue($storageConfig));
		} else {
			$this->service->expects($this->never())
				->method('addStorage');
		}

		$response = $this->controller->create(
			'mount',
			'\OCA\Files_External\Lib\Storage\SMB',
			'\OCA\Files_External\Lib\Auth\NullMechanism',
			array(),
			[],
			[],
			[],
			null
		);

		if ($expectSuccess) {
			$this->assertEquals(Http::STATUS_CREATED, $response->getStatus());
		} else {
			$this->assertEquals(Http::STATUS_UNPROCESSABLE_ENTITY, $response->getStatus());
		}
	}

}
