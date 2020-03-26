<?php
/**
 * @copyright 2020, Thomas Citharel <nextcloud@tcit.fr>
 *
 * @author Thomas Citharel <nextcloud@tcit.fr>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\DAV\CalDAV;

use OCP\Calendar\ICalendarObjectV2;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

class CalendarObjectImplV2 implements ICalendarObjectV2 {

	/** @var CalDavBackend */
	private $backend;

	/** @var array */
	private $calendarObjectData;

	/**
	 * CalendarImpl constructor.
	 *
	 * @param array $calendarObjectData
	 * @param CalDavBackend $backend
	 */
	public function __construct(array $calendarObjectData, CalDavBackend $backend) {
		$this->backend = $backend;
		$this->calendarObjectData = $calendarObjectData;
	}

	public function getCalendarKey(): string {
		return $this->calendarObjectData['calendarid'];
	}

	public function getUri(): string {
		return $this->calendarObjectData['uri'];
	}

	public function getVObject(): VCalendar {
		return Reader::read($this->calendarObjectData['calendardata']);
	}

	public function update(VCalendar $data): void {
		$this->backend->updateCalendarObject($this->getCalendarKey(), $this->getUri(), $data->serialize());
	}

	public function delete(): void {
		$this->backend->deleteCalendarObject($this->getCalendarKey(), $this->getUri());
	}
}
