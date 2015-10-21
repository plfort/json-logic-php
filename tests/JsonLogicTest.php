<?php

class JsonLogicTest extends PHPUnit_Framework_TestCase{

	/**
     * @expectedException Exception
     */
    public function testInvalidOperator()
    {
		JWadhams\JsonLogic::apply(['fubar'=> [1,2]]);
    }

	/*
	public function testLoggingOperator()
	{
		$string = 'Hello, World';
		$this->expectOutputString($string);	

		$passed = JWadhams\JsonLogic::apply(['log' => [$string]]);
        
		$this->assertEquals($passed, $string);
	}
	*/
	

	public function testHandlesObjectRules(){

		$object_style = json_decode('{"==":["apples", "apples"]}');
        $this->assertEquals(true, JWadhams\JsonLogic::apply($object_style));
    }



	/**
     * @dataProvider passthroughProvider
     */
	public function testPassthrough($logic)
    {
        // Assert
        $this->assertEquals($logic, JWadhams\JsonLogic::apply($logic));
    }

    public function passthroughProvider()
    {
        return [
			//If the "rule" isn't a JSON-object, pass it through
			[ true ], //Always
			[ false ], //Never
			[ 17 ], //Number
			[ "apple" ], //string
			[ null ], //Null
			[ ["a","b"] ], //numeric array
		];
	}


	/**
     * @dataProvider singleProvider
     */
	public function testSingleOperators($logic, $data, $expected)
    {
        // Assert
        $this->assertEquals($expected, JWadhams\JsonLogic::apply($logic, $data));
    }


    public function singleProvider()
    {
        return [
			[ ["==" => [1,1]], [], true ],
			[ ["==" => [1,"1"]], [], true ],
			[ ["==" => [1,2]], [], false ],

			[ ["===" => [1,1]], [], true ],
			[ ["===" => [1,"1"]], [], false ],
			[ ["===" => [1,2]], [], false ],

			[ [ "!=" => [1, 2] ], [], true ],
			[ [ "!=" => [1, 1] ], [], false ],

			[ [ "!==" => [1, 2] ], [], true ],
			[ [ "!==" => [1, 1] ], [], false ],
			[ [ "!==" => [1, "1"] ], [], true ],
			[ [ ">" => [2, 1] ], [], true ],
			[ [ ">" => [1, 1] ], [], false ],
			[ [ ">" => [1, 2] ], [], false ],
			[ [ ">" => ["2", 1] ], [], true ],

			[ [ ">=" => [2, 1] ], [], true ],
			[ [ ">=" => [1, 1] ], [], true ],
			[ [ ">=" => [1, 2] ], [], false ],
			[ [ ">=" => ["2", 1] ], [], true ],

			[ [ "<" => [2, 1] ], [], false ],
			[ [ "<" => [1, 1] ], [], false ],
			[ [ "<" => [1, 2] ], [], true ],
			[ [ "<" => ["1", 2] ], [], true ],

			[ [ "<=" => [2, 1] ], [], false ],
			[ [ "<=" => [1, 1] ], [], true ],
			[ [ "<=" => [1, 2] ], [], true ],
			[ [ "<=" => ["1", 2] ], [], true ],

			[ [ "!" => [false] ], [], true ],
			[ [ "!" => false ], [], true ],
			[ [ "!" => [true] ], [],false ],
			[ [ "!" => true ], [], false ],
			[ [ "!" => 0 ], [], true ],

			[ [ "or" => [true, true] ], [], true ],
			[ [ "or" => [false, true] ], [], true ],
			[ [ "or" => [true, false] ], [], true ],
			[ [ "or" => [false, false] ], [], false ],

			[ [ "or" => [false, false, true] ], [], true ], //More than 2 args
			[ [ "or" => [false] ], [], false ], //less than 2 args
			[ [ "or" => [true] ], [], true ], //less than 2 args

			[ [ "or" => [1,3] ], [], 1 ], //Non-bool args
			[ [ "or" => [3, false] ], [], 3 ], //Non-bool args
			[ [ "or" => [false, 3] ], [], 3 ], //Non-bool args
			



			[ [ "and" => [true, true] ], [], true ],
			[ [ "and" => [false, true] ], [], false ],
			[ [ "and" => [true, false] ], [], false ],
			[ [ "and" => [false, false] ], [], false],

			[ [ "and" => [true, true, false] ], [], false ], //More than 2 args
			[ [ "and" => [false] ], [], false ], //less than 2 args
			[ [ "and" => [true] ], [], true ], //less than 2 args

			[ [ "and" => [1,3] ], [], 3 ], //Non-bool args
			[ [ "and" => [3, false] ], [], false ], //Non-bool args
			[ [ "and" => [false, 3] ], [], false ], //Non-bool args
			

			[ [ "?:" => [true, 1, 2] ], [], 1 ],
			[ [ "?:" => [false, 1, 2] ], [], 2 ],

			[ [ "in" => ['Bart',['Bart', 'Homer', 'Lisa', 'Marge', 'Maggie']] ], [], true ],
			[ [ "in" => ['Milhouse',['Bart', 'Homer', 'Lisa', 'Marge', 'Maggie']] ], [], false ],

			[ [ "in" => ["Spring", "Springfield"] ], [], true ],
			[ [ "in" => ["i", "team"] ], [], false ],

			[ [ "cat" => "ice" ], [], "ice" ],
			[ [ "cat" => ["ice"] ], [], "ice" ],
			[ [ "cat" => ["ice", "cream"] ], [], "icecream" ],
			[ [ "cat" => ["we all scream for ", "ice", "cream"] ], [], "we all scream for icecream" ],

			[ [ "%" => [1,2] ], [], 1 ],
			[ [ "%" => [2,2] ], [], 0 ],
			[ [ "%" => [3,2] ], [], 1 ],
			
		];
    }


	/**
     * @dataProvider compoundProvider
     */
	public function testCompoundLogic($logic, $data, $expected)
    {
        // Assert
        $this->assertEquals($expected, JWadhams\JsonLogic::apply($logic, $data));
    }


    public function compoundProvider()
    {
        return [
			[ ['and'=>[ ['>' => [3,1]], true ]], [], true ],
			[ ['and'=>[ ['>' => [3,1]], false ]], [], false ],

			[ ['and'=>[ ['>' => [3,1]], ['!'=>true] ]], [], false ],

			[ ['and'=>[ ['>' => [3,1]], ['<'=>[1,3]] ]], [], true ],

			[ ['?:'=>[ ['>'=>[3,1]], 'visible', 'hidden' ]], [], 'visible']
		];
    }


	/**
     * @dataProvider dataDrivenProvider
     */
	public function testDataDriven($logic, $data, $expected)
    {
        // Assert
        $this->assertEquals($expected, JWadhams\JsonLogic::apply($logic, $data));
    }


    public function dataDrivenProvider()
    {
        return [
			[ ["var"=>["a"]], ["a"=>1], 1 ],
			[ ["var"=>["a"]], (object)["a"=>1], 1 ], //Data as object
			[ ["var"=>["a"]], json_decode('{"a":1}'), 1 ], //Data as object
			[ ["var"=>["b"]], ["a"=>1], null ],
			[ ["var"=>["a"]], null, null ],
			[ ["var"=>"a"], ["a"=>1], 1 ],
			[ ["var"=>"b"], ["a"=>1], null ],
			[ ["var"=>"a"], null, null ],

			//Depth
			[ ["var"=>"a.b"], ["a"=>["b"=>"c"]], "c" ],
			[ ["var"=>"a.q"], ["a"=>["b"=>"c"]], null ],

			//Array
			[ ["var"=>1], ["apple","banana"], "banana" ],
			[ ["var"=>"1"], ["apple","banana"], "banana" ],
			[ ["var"=>"1.1"], ["apple",["banana", "beer"] ], "beer" ],

			//Compound examples from the docs
			[
				[ "and"=> [
					[ "<"=> [[ "var"=>"temp" ], 110] ],
					[ "=="=> [ [ "var"=>"pie.filling" ], "apple" ] ] ] 
				],
				[ "temp" => 100, "pie" => [ "filling" => "apple" ] ],
				true
			],
			[
				[ "var" => [
					[ "?:" => [
						[ "<" => [[ "var"=>"temp" ], 110] ], "pie.filling", "pie.eta" 
					] ]
				]],
				[ "temp" => 100, "pie" => [ "filling" => "apple", "eta" => "60s" ] ],
				"apple"
			]
		];
    }

}
