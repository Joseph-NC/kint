<?php
class Kint_Decorators_Plain extends Kint
{
	public static function decorate( kintVariableData $kintVar, $level = 0 )
	{
		$output = '';
		if ( $level === 0 && $kintVar->name ) {
			$output .= "#-------------{$kintVar->name}------------#\n";
			$kintVar->name = null;
		}

		$space = str_repeat( $s = '    ', $level );
		$output .= $space . self::_drawHeader( $kintVar );


		if ( $kintVar->extendedValue !== null ) {
			$output .= " [\n";

			if ( is_array( $kintVar->extendedValue ) ) {
				foreach ( $kintVar->extendedValue as $v ) {
					$output .= self::decorate( $v, $level + 1 );
				}
			} elseif ( is_string( $kintVar->extendedValue ) ) {
				$output .= $space . $s . $kintVar->extendedValue . "\n"; # depth too great or similar
			} else {
				$output .= self::decorate( $kintVar->extendedValue, $level + 1 ); //it's kint's container
			}
			$output .= $space . "]\n";
		} else {
			$output .= "\n";
		}

		return $output;
	}

	private static function _drawHeader( kintVariableData $kintVar )
	{
		$output = '';

		if ( $kintVar->access ) {
			$output .= ' ' . $kintVar->access;
		}

		if ( $kintVar->name !== null && $kintVar->name !== '' ) {
			$output .= ' ' . $kintVar->name;
		}

		if ( $kintVar->operator ) {
			$output .= ' ' . $kintVar->operator;
		}


		$output .= ' ' . $kintVar->type;
		if ( $kintVar->subtype ) {
			$output .= " " . $kintVar->subtype;
		}


		if ( $kintVar->size !== null ) {
			$output .= '(' . $kintVar->size . ')';
		}


		if ( $kintVar->value !== null && $kintVar->value !== '' ) {
			$output .= ' ' . $kintVar->value;
		}

		return ltrim( $output );
	}

	protected static function _css()
	{
		return '';
	}


	/**
	 * called for each dump, opens the html tag
	 *
	 * @param array $callee caller information taken from debug backtrace
	 *
	 * @return string
	 */
	protected static function _wrapStart( $callee )
	{
		return "<pre>\n";
	}


	/**
	 * closes Kint::_wrapStart() started html tags and displays callee information
	 *
	 * @param array $callee caller information taken from debug backtrace
	 * @param array $prevCaller previous caller information taken from debug backtrace
	 *
	 * @return string
	 */
	protected static function _wrapEnd( $callee, $prevCaller )
	{
		if ( !Kint::$displayCalledFrom ) {
			return '</pre>';
		}

		$callingFunction = '';
		if ( isset( $prevCaller['class'] ) ) {
			$callingFunction = $prevCaller['class'];
		}
		if ( isset( $prevCaller['type'] ) ) {
			$callingFunction .= $prevCaller['type'];
		}
		if ( isset( $prevCaller['function'] ) && !in_array( $prevCaller['function'], Kint::$_statements ) ) {
			$callingFunction .= $prevCaller['function'] . '()';
		}
		$callingFunction and $callingFunction = " in {$callingFunction}";

		$calleeInfo = null;
		if ( isset( $callee['file'] ) ) {
			list( $url, $shortenedName ) = self::shortenPath( $callee['file'], $callee['line'], false );

			if ( strpos( $url, 'http://' ) === 0 ) {
				$calleeInfo = "<a href=\"#\" onclick=\"" .
					"var ajax = new XMLHttpRequest();" .
					"ajax.open('GET', '{$url}');" .
					"ajax.send(null);" .
					"return false;\">{$shortenedName}</a>";
			} else {
				$calleeInfo = "<a href=\"{$url}\">{$shortenedName}</a>";
			}
		}


		return $calleeInfo || $callingFunction
			? "Called from {$calleeInfo}{$callingFunction}</pre>"
			: "</pre>";
	}
}