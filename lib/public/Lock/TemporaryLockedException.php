<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
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

namespace OCP\Lock;

/**
 * Class LockedException
 *
 * @package OCP\Lock
 * @since 18.0.0
 */
class TemporaryLockedException extends LockedException {

	/**
	 * owner of the lock
	 *
	 * @var string
	 */
	private $owner = '';

	/**
	 * estimated timeout for the lock
	 *
	 * @var int
	 * @since 18.0.0
	 */
	private $timeout = -1;

	/**
	 * TemporaryLockedException constructor.
	 *
	 * @param string $path locked path
	 * @param \Exception|null $previous previous exception for cascading
	 * @param string $existingLock
	 * @param string $owner
	 * @param int $timeout
	 *
	 * @since 18.0.0
	 */
	public function __construct(string $path, \Exception $previous = null, string $existingLock = null, string $owner = '', int $timeout = -1) {
		parent::__construct($path, $previous, $existingLock);
		$this->owner = $owner;
		$this->timeout = $timeout;
	}


	/**
	 * @return int
	 * @since 18.0.0
	 */
	public function getTimeout(): int {
		return $this->timeout;
	}

	/**
	 * @return string
	 * @since 18.0.0
	 */
	public function getOwner(): string {
		return $this->owner;
	}

}
