<?php

namespace nathanwooten\SQLExpressions;

class Expression {

	const AND = ' and ';
	const OR = ' or ';

	const ANDS = [ 'AND', 'and', '&&' ];
	const ORS = [ 'OR', 'or', '||' ];

	const COMPARISON = [ '=', '!=', '>', '<', '>=', '<=', '<>', '<=>' ];

	public $parts = [];
	public $expressionKeys = [];
	public $expressions = [];

	public $before = '';
	public $after = '';

	public function __construct( array $parts, $parent = null )
	{

		$parts = $this->andOr( $parts );

		$this->parts = $parts;

		$expressions = [];
		$expressions[] = $this;

		foreach ( $parts as $index => $part )
		{

			if ( is_array( $part ) ) {
				$expressionKeys[] = $index;

				$expression = new Expression( $part, $this );
				$this->parts[ $index ] = $expressions[] = $expression;
				$this->expressionKeys[] = $index;
			} else {

				$this->parts[ $index ] = $part;
			}
		}

		$this->expressions = $expressions;

	}

	public function add( $at, $expression, $position = 'after' ) {

		$this->getExp( $at )->getParent()->add( $expression, $position );

	}

	public function andOr( array $parts )
	{

		reset( $parts );
		$part = current( $parts );
		if ( in_array( $part, static::ANDS ) ) {
			$this->before = static::AND;
			array_shift( $parts );
		}
		if ( in_array( $part, static::ORS ) ) {
			$this->before = static::OR;
			array_shift( $parts );
		}

		end( $parts );
		$part = current( $parts );
		if ( in_array( $part, static::ANDS ) ) {
			$this->after = static::AND;
			array_pop( $parts );
		}
		if ( in_array( $part, static::ORS ) ) {
			$this->after = static::OR;
			array_pop( $parts );
		}

		return $parts;

	}

	public function getExpression( $expressionKey )
	{

		return $this->parts[ $expressionKey ];

	}

	public function getExp( $name ) {

		$expressions = [];

		$thisName = $this->getName();
		if ( $name === $thisName ) {
			return $this;
		}

		foreach ( $this->expressions as $expression ) {

			$expName = $expression->getName();
			if ( $name === $expName ) {
				$expressions[] = $expression;
			}
		}

		if ( empty( $expressions ) ) {
			$this->handleError( new Exception( 'No expression with that name found' ) );
		}

		return $expressions;

	}

	public function getName()
	{

		$parts = array_values( $this->parts );
		$name = $parts[ 0 ];

		if ( is_string( $name ) ) {
			return $name;
		}

		$expression = $parts[ 0 ];
		return $expression->getName();

	}

	public function getPart( int $index )
	{

		return isset( $this->parts[ $index ] ) ? $this->parts[ $index ] : null;

	}

	public function getParts()
	{

		return $this->parts;

	}

	public function getParent()
	{

		return isset( $this->parent ) ? $this->parent : null;

	}

/*
	public function __toString()
	{

		$string = '';

		$expressions = $this->expressions;
		array_shift( $expressions );

		foreach ( $this->expressions as $expression ) {
			$string .= ( string ) $expression;
		}

	}
*/
}
