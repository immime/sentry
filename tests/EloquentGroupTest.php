<?php
/**
 * Part of the Sentry Package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Sentry
 * @version    2.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Mockery as m;
use Cartalyst\Sentry\Groups\Eloquent\Group;

class EloquentGroupTest extends PHPUnit_Framework_TestCase {

	/**
	 * Setup resources and dependencies.
	 *
	 * @return void
	 */
	public function setUp()
	{
		
	}

	/**
	 * Close mockery.
	 * 
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testGroupId()
	{
		$group = new Group;
		$group->id = 123;

		$this->assertEquals(123, $group->getGroupId());
	}

	public function testGroupName()
	{
		$group = new Group;
		$group->name = 'foo';

		$this->assertEquals('foo', $group->getGroupName());
	}

	public function testValidation()
	{
		$group = m::mock('Cartalyst\Sentry\Groups\Eloquent\Group[findByName]');
		$group->id   = 123;
		$group->name = 'foo';
		$group->shouldReceive('findByName')->with('foo')->once()->andReturn(null);

		$this->assertTrue($group->validate());
	}

	/**
	 * @expectedException Cartalyst\Sentry\Groups\NameFieldRequiredException
	 */
	public function testValidationThrowsExceptionForMissingName()
	{
		$group = new Group;
		$group->validate();
	}

	/**
	 * @expectedException Cartalyst\Sentry\Groups\GroupExistsException
	 */
	public function testValidationThrowsExceptionForDuplicateNameOnNonExistent()
	{
		$persistedGroup = new Group;
		$persistedGroup->id   = 123;
		$persistedGroup->name = 'foo';

		$group = m::mock('Cartalyst\Sentry\Groups\Eloquent\Group[findByName]');
		$group->name = 'foo';
		$group->shouldReceive('findByName')->with('foo')->once()->andReturn($persistedGroup);
		$group->validate();
	}

	/**
	 * @expectedException Cartalyst\Sentry\Groups\GroupExistsException
	 */
	public function testValidationThrowsExceptionForDuplicateNameOnExistent()
	{
		$persistedGroup = new Group;
		$persistedGroup->id   = 123;
		$persistedGroup->name = 'foo';

		$group = m::mock('Cartalyst\Sentry\Groups\Eloquent\Group[findByName]');
		$group->id   = 124;
		$group->name = 'foo';
		$group->shouldReceive('findByName')->with('foo')->once()->andReturn($persistedGroup);
		$group->validate();
	}

	public function testValidationDoesNotThrowAnExceptionIfPersistedGroupIsThisGroup()
	{
		$persistedGroup = new Group;
		$persistedGroup->id   = 123;
		$persistedGroup->name = 'foo';

		$group = m::mock('Cartalyst\Sentry\Groups\Eloquent\Group[findByName]');
		$group->id   = 123;
		$group->name = 'foo';
		$group->shouldReceive('findByName')->with('foo')->once()->andReturn($persistedGroup);

		$this->assertTrue($group->validate());
	}

	public function testSettingPermissions()
	{
		$permissions = array(
			'foo' => 1,
			'bar' => 1,
			'baz' => 1,
			'qux' => 1,
		);

		$group = new Group;

		$expected = '{"foo":1,"bar":1,"baz":1,"qux":1}';

		$this->assertEquals($expected, $group->setPermissions($permissions));
	}

	public function testSettingPermissionsWhenSomeAreSetTo0()
	{
		$permissions = array(
			'foo' => 1,
			'bar' => 1,
			'baz' => 0,
			'qux' => 1,
		);

		$group = new Group;

		$expected = '{"foo":1,"bar":1,"qux":1}';

		$this->assertEquals($expected, $group->setPermissions($permissions));
	}

	public function testPermissionsAreMergedAndRemovedProperly()
	{
		$group = new Group;
		$group->permissions = array(
			'foo' => 1,
			'bar' => 1,
		);

		$group->permissions = array(
			'baz' => 1,
			'qux' => 1,
			'foo' => 0,
		);

		$expected = array(
			'bar' => 1,
			'baz' => 1,
			'qux' => 1,
		);

		$this->assertEquals($expected, $group->permissions);
	}

	public function testPermissionsAreCastAsAnArrayWhenTheModelIs()
	{
		$group = new Group;
		$group->name = 'foo';
		$group->permissions = array(
			'bar' => 1,
			'baz' => 1,
			'qux' => 1,
		);

		$expected = array(
			'name' => 'foo',
			'permissions' => array(
				'bar' => 1,
				'baz' => 1,
				'qux' => 1,
			),
		);

		$this->assertEquals($expected, $group->toArray());
	}

}