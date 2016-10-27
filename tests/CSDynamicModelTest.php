<?php
/**
 * Ten plik jest integralną częścią 3e Content Studio / 3e Application Framework.
 * Szczegóły licencji są wyszczególnione w umowie.
 * This file is an integral part of 3e Application Framework 3e Content Studio / 3e Application Framework.
 * License details are specified in contract.
 *
 * @author 3e internet software house
 * @link http://www.3e.pl/
 * @copyright Copyright &copy; 2008-2012 3e internet software house
 */


class CSDynamicModelTest extends CTestCase {

	public function testConstructor() {
		$model = CSDynamicModel::validateData( array( 'name'=>'testname', 'email'=>'test@email.com' ), array(
			array( 'name,email', 'length', 'max' => 64 ),
			array( 'email', 'email' ),
		) );
		$this->assertFalse( $model->hasErrors() );
		
		$model = CSDynamicModel::validateData( array( 'name'=>'testname', 'email'=>'test.email.com' ), array(
			array( 'name,email', 'length', 'max' => 64 ),
			array( 'email', 'email' ),
		) );
		$this->assertTrue( $model->hasErrors() );
		
		$model = CSDynamicModel::validateData( array( 'name'=>'testname', 'email'=>'test@email.com' ), array(
			array( 'name,email', 'length', 'max' => 4 ),
			array( 'email', 'email' ),
		) );
		$this->assertTrue( $model->hasErrors() );

	}

	public function testCode() {
		$model = new CSDynamicModel( array( 'name'=>'testname', 'email'=>'test@email.com' ) );
		$model->addRule( array( 'name,email', 'length', 'max' => 128 ) )
			->addRule( array( 'email', 'email' ) )
			->validate();
		$this->assertFalse( $model->hasErrors() );
    }

	public function testDynamicProperty() {
 		$email = 'invalid';
 		$name = 'long name';
 		$model = new CSDynamicModel( compact( 'name', 'email' ) );
 		$this->assertEquals( $email, $model->email );
 		$this->assertEquals( $name, $model->name );
 		$this->setExpectedException( 'CException' );
 		$age = $model->age;
 	}
	
	public function testMassiveAssignment() {
 		$model = new CSDynamicModel( array( 'name', 'email' ) );
		$model->addRule( array( 'name,email', 'length', 'max' => 128 ) )
			->addRule( array( 'email', 'email' ) );
		$model->attributes = array( 'name'=>'test1', 'email'=>'test@company.com' );
 		$this->assertEquals( 'test@company.com', $model->email );
 		$this->assertEquals( 'test1', $model->name );
		
		$model->validate();
		$this->assertFalse( $model->hasErrors() );
 	}
}