<?php
/**
 * @copyright 2017, Georg Ehrke <oc.list@georgehrke.com>
 *
 * @author Georg Ehrke <oc.list@georgehrke.com>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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

namespace OCA\DAV\Tests\unit\CalDAV;

use OCA\DAV\CalDAV\CalDavBackend;
use OCA\DAV\CalDAV\CalendarObjectImplV2;
use PHPUnit\Framework\MockObject\MockObject;
use Sabre\VObject\Component\VCalendar;
use Test\TestCase;

class CalendarObjectImplV2Test extends TestCase {

	/** @var array */
	private $calendarObjectData;

	/** @var CalDavBackend | MockObject */
	private $backend;
	/**
	 * @var CalendarObjectImplV2
	 */
	private $calendarObjectImpl;

	protected function setUp(): void {
		parent::setUp();

		$this->calendarObjectData = [
			'calendarid' => 'fancy_id_123',
			'uri' => 'something.ics',
			'calendardata' => (new VCalendar())->serialize()
		];
		$this->backend = $this->createMock(CalDavBackend::class);

		$this->calendarObjectImpl = new CalendarObjectImplV2($this->calendarObjectData, $this->backend);
	}


	public function testGetCalendarKey() {
		$this->assertEquals($this->calendarObjectImpl->getCalendarKey(), 'fancy_id_123');
	}

	public function testGetUri() {
		$this->assertEquals($this->calendarObjectImpl->getUri(),'something.ics');
	}

	public function testGetVObject() {
		$this->assertEquals($this->calendarObjectImpl->getVObject()->serialize(), (new VCalendar())->serialize());
	}

	public function testUpdate() {
		$this->backend->expects($this->once())
			->method('updateCalendarObject')
			->with('fancy_id_123', 'something.ics', (new VCalendar())->serialize());

		$this->calendarObjectImpl->update(new VCalendar());
	}

	public function testDelete() {
		$this->backend->expects($this->once())
			->method('deleteCalendarObject')
			->with('fancy_id_123', 'something.ics');

		$this->calendarObjectImpl->delete();
	}
}
