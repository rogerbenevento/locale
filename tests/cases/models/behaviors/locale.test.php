<?php
App::import('Behavior', 'Locale');

/**
 * Testes do Behavior Locale
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @author        Cauan Cabral <cauan@radig.com.br>, José Agripino <jose@radig.com.br>
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class Employee extends CakeTestModel
{
	public $name = 'Employee';
	
	public $validate = array(
		'birthday' => array(
			'rule' => array('date'),
			'allowEmpty' => false,
			'requirerd' => true
		),
		'salary' => array(
			'rule' => array('numeric'),
			'allowEmpty' => false,
			'requirerd' => true
		)
	);
	
	public $actsAs = array('Locale.Locale');
}


class LocaleTest extends CakeTestCase {
	
	public $name = 'Locale';
	
	public $fixtures = array('plugin.locale.employee');
	
	public function startTest()
	{
		$this->Employee =& ClassRegistry::init('Employee');
	}

	public function endTest()
	{
		unset($this->Employee);
	}
	
	/**
	 * Testa uma ação de busca, com o critério tendo
	 * um valor de data localizada
	 */
	public function testFindActionWithDate()
	{	
		$result = $this->Employee->find('all',
			array('conditions' => array('birthday' => '01/03/1987'))
		);
		
		$expected = array(
			array(
				'Employee' => array(
					'id' => 1,
					'birthday' => '1987-03-01',
					'salary' => 559.00
				)
			)
		);
		
		$this->assertEqual($result, $expected);
	}

	/**
	 * Testa uma ação de busca, com o critério tendo
	 * um valor de data (inválida) localizada
	 */
	public function testFindActionWithBogusDate()
	{
		$result = $this->Employee->find('all',
			array('conditions' => array('birthday' => '21/23/1987'))
		);

		$expected = array();

		$this->assertEqual($result, $expected);
	}
	
	/**
	 * Testa uma ação de busca, com o critério tendo
	 * um valor decimal localizado
	 */
	public function testFindActionWithFloat()
	{	
		$result = $this->Employee->find('all',
			array('conditions' => array('salary' => '559.00'))
		);
				
		$expected = array(
			array(
				'Employee' => array(
					'id' => '1',
					'birthday' => '1987-03-01',
					'salary' => 559.00
				)
			)
		);
		
		$this->assertEqual($result, $expected);
	}
	
	/**
	 * Testa uma ação de busca, com o critério sendo um inteiro,
	 * enquanto o banco espera um valor decimal/float
	 */
	public function testFindActionWithFloatWithoutDot()
	{
		$result = $this->Employee->find('all',
			array('conditions' => array('salary' => '559'))
		);

		$expected = array(
			array(
				'Employee' => array(
					'id' => '1',
					'birthday' => '1987-03-01',
					'salary' => 559.00
				)
			)
		);

		$this->assertEqual($result, $expected);
	}
	
	/**
	 * Testa o behavio para a ação save, com dados não localizados
	 * (já no formato alvo - do DB)
	 */
	public function testSaveNonLocalizedDataAction()
	{
		$result = $this->Employee->save(
			array('id' => '2', 'birthday' => '2001-01-01', 'salary' => '650.30')
		);
		
		$this->assertEqual($result, true);
	}
	
	/**
	 * Testa o behavior para a ação save, com dados localizados
	 */
	public function testSaveLocalizedDataAction()
	{
		$result = $this->Employee->save(
			array('id' => '2', 'birthday' => '01-01-2001', 'salary' => '650,30')
		);
		
		$this->assertEqual($result, true);
	}
	
	/**
	 * Testa se o behavior converte todos os dados
	 * salvos em um saveAll
	 */
	public function testSaveAllAction()
	{
		$result = $this->Employee->saveAll(
			array(
				array('id' => '2', 'birthday' => '01/01/2001', 'salary' => '650,30'),
				array('id' => '3', 'birthday' => '29/03/1920', 'salary' => '0,99'),
				array('id' => '4', 'birthday' => '21-04-1975', 'salary' => '0,3')
			)
		);
		$this->assertEqual($result, true);
	}
	
	/**
	 * Testa se os dados enviados pelo usuário serão
	 * convertidos do formato local para um formato padrão (do DB)
	 * e depois recuperados neste formato do DB.
	 */
	public function testSavedData()
	{
		$result = $this->Employee->saveAll(
			array(
				array('id' => '2', 'birthday' => '01/01/2001', 'salary' => '650,30'),
				array('id' => '3', 'birthday' => '29/03/1920', 'salary' => '0,99'),
				array('id' => '4', 'birthday' => '21-04-1975', 'salary' => '0,3')
			)
		);
		
		$expected = array(
			array(
				'Employee' => array(
					'id' => 1,
					'birthday' => '1987-03-01',
					'salary' => 559.00
				)
			),
			array(
				'Employee' => array(
					'id' => 2,
					'birthday' => '2001-01-01',
					'salary' => 650.3
				)
			),
			array(
				'Employee' => array(
					'id' => 3,
					'birthday' => '1920-03-29',
					'salary' => 0.99
				)
			)
			,array(
				'Employee' => array(
					'id' => 4,
					'birthday' => '1975-04-21',
					'salary' => 0.3
				)
			)
		);
		
		$this->assertEqual($result, $expected);
	}
}