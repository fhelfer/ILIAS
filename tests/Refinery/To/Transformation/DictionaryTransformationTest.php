<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\DictionaryTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use ILIAS\Refinery\Validation\Factory;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class DictionaryTransformationTest extends TestCase
{
	/**
	 * @var \ILIAS\Refinery\Validation\Factory
	 */
	private $validation;

	public function setUp()
	{
		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$dataFactory = new \ILIAS\Data\Factory();

		$validationFactory = new Factory($dataFactory, $language);
		$this->validation = $validationFactory->isArrayOfSameType();
	}

	/**
	 * @throws \ilException
	 */
	public function testDictionaryTransformationValid()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validation);

		$result = $transformation->transform(array('hello' => 'world'));

		$this->assertEquals(array('hello' => 'world'), $result);
	}

	public function testDictionaryTransformationInvalidBecauseKeyIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validation);

		try {
			$result = $transformation->transform(array('world'));
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testDictionaryTransformationInvalidBecauseValueIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validation);

		try {
			$result = $transformation->transform(array('hello' => 1));
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testDictionaryTransformationNonArrayCanNotBeTransformedAndThrowsException()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validation);

		try {
			$result = $transformation->transform(1);
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testDictionaryApplyValid()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validation);

		$result = $transformation->applyTo(new Ok(array('hello' => 'world')));

		$this->assertEquals(array('hello' => 'world'), $result->value());
	}

	public function testDictionaryApplyInvalidBecauseKeyIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validation);

		$result = $transformation->applyTo(new Ok(array('world')));

		$this->assertTrue($result->isError());
	}

	public function testDictionaryApplyInvalidBecauseValueIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validation);

		$result = $transformation->applyTo(new Ok(array('hello' => 1)));

		$this->assertTrue($result->isError());
	}

	public function testDictonaryNonArrayToTransformThrowsException()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validation);

		$result = $transformation->applyTo(new Ok(1));

		$this->assertTrue($result->isError());
	}
}
