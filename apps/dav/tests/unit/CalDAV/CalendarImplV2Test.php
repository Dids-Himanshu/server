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
use OCA\DAV\CalDAV\Calendar;
use OCA\DAV\CalDAV\CalendarImplV2;
use OCA\DAV\CalDAV\CalendarObjectImplV2;
use PHPUnit\Framework\MockObject\MockObject;
use Sabre\VObject\Component\VCalendar;
use Test\TestCase;

class CalendarImplV2Test extends TestCase {

	/** @var CalendarImplV2 */
	private $calendarImpl;

	/** @var Calendar | MockObject */
	private $calendar;

	/** @var array */
	private $calendarInfo;

	/** @var CalDavBackend | MockObject */
	private $backend;

	protected function setUp(): void {
		parent::setUp();

		$this->calendar = $this->createMock(Calendar::class);
		$this->calendarInfo = [
			'id' => 'fancy_id_123',
			'{DAV:}displayname' => 'user readable name 123',
			'{http://apple.com/ns/ical/}calendar-color' => '#AABBCC',
		];
		$this->backend = $this->createMock(CalDavBackend::class);

		$this->calendarImpl = new CalendarImplV2($this->calendar,
			$this->calendarInfo, $this->backend);
	}


	public function testGetKey() {
		$this->assertEquals($this->calendarImpl->getKey(), 'fancy_id_123');
	}

	public function testGetDisplayname() {
		$this->assertEquals($this->calendarImpl->getDisplayName(),'user readable name 123');
	}

	public function testGetDisplayColor() {
		$this->assertEquals($this->calendarImpl->getDisplayColor(), '#AABBCC');
	}

	public function testSearch() {
		$this->backend->expects($this->once())
			->method('search')
			->with($this->calendarInfo, 'abc', ['def'], ['ghi'], 42, 1337)
			->willReturn(['SEARCHRESULTS']);

		$result = $this->calendarImpl->search('abc', ['def'], ['ghi'], 42, 1337);
		$this->assertEquals($result, ['SEARCHRESULTS']);
	}

	/**
	 * @dataProvider dataTestIsWritable
	 */
	public function testGetIsWritable(array $privileges, bool $result) {
		$this->calendar->expects($this->once())
			->method('getACL')
			->with()
			->willReturn($privileges);

		$this->assertEquals($result, $this->calendarImpl->isWriteable());
	}

	public function dataTestIsWritable(): array {
		return [
			[
				[
					['privilege' => '{DAV:}read']
				],
				false
			],
			[
				[
					['privilege' => '{DAV:}write']
				],
				true
			],
			[
				[
					['privilege' => '{DAV:}read'],
					['privilege' => '{DAV:}write']
				],
				true
			],
			[
				[
					['privilege' => '{DAV:}all']
				],
				true
			]
		];
	}

	public function testGetByUri() {
		$calendarObjectData = ['id' => 'someid', 'calendarid' => 'fancy_id_123'];
		$calendarObject = new CalendarObjectImplV2($calendarObjectData, $this->backend);

		$this->backend->expects($this->once())
			->method('getCalendarObject')
			->willReturn($calendarObjectData);
		$this->assertEquals($calendarObject, $this->calendarImpl->getByUri('uri'));
	}

	public function testGetByUriNotFound() {
		$this->backend->expects($this->once())
			->method('getCalendarObject')
			->willReturn(null);
		$this->assertEquals(null, $this->calendarImpl->getByUri('uri'));
	}

	public function testCreateCalendarObject() {
		$calendarObjectDataObject = new VCalendar();
		$calendarObjectData = ['calendardata' => $calendarObjectDataObject->serialize()];
		$calendarObject = new CalendarObjectImplV2($calendarObjectData, $this->backend);
		$this->backend->expects($this->once())
			->method('createCalendarObject')
			->with(
				'fancy_id_123',
				$this->anything(),
				$calendarObjectDataObject->serialize()
			);
		$this->backend->expects($this->once())
			->method('getCalendarObject')
			->willReturn($calendarObjectData);
		$this->assertEquals($calendarObject, $this->calendarImpl->create($calendarObjectDataObject));
	}
}
