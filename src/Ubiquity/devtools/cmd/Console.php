<?php
namespace Ubiquity\devtools\cmd;

/**
 * Ubiquity\devtools\cmd$Console
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class Console {

	/**
	 * Read a line from the user input.
	 *
	 * @return string
	 */
	public static function readline() {
		return \rtrim(\fgets(STDIN));
	}

	/**
	 * Ask the user a question and return the answer.
	 *
	 * @param string $prompt
	 * @param ?array $propositions
	 * @return string
	 */
	public static function question($prompt, array $propositions = null, array $options = []) {
		$prompt = ConsoleFormatter::formatHtml(ConsoleFormatter::colorize($prompt, ConsoleFormatter::BLACK, ConsoleFormatter::BG_YELLOW));
		$hiddenProposals = $options['hiddenProposals'] ?? [];
		$continue = function ($rep, $array) {
			return \array_search($rep, $array) === false;
		};
		if (isset($options['default'])) {
			$prompt .= ConsoleFormatter::formatHtml(" (default:<b>" . $options['default'] . "</b>)");
		}
		if (isset($options['ignoreCase'])) {
			$continue = function ($rep, $array) {
				return \array_search(\strtolower($rep), \array_map('strtolower', $array)) === false;
			};
		}
		echo $prompt;
		if (\is_array($propositions)) {
			if (\count($propositions) > 2) {
				$props = "";
				foreach ($propositions as $index => $prop) {
					$dec = 2 - \strlen(($index + 1) . '');
					$props .= "[" . ($index + 1) . "] " . str_repeat(' ', $dec) . $prop . "\n";
				}
				echo ConsoleFormatter::formatContent($props);
				do {
					$answer = self::readline();
				} while ((int) $answer != $answer || ! isset($propositions[(int) $answer - 1]));
				$answer = $propositions[(int) $answer - 1];
			} else {
				echo " (" . implode("/", $propositions) . ")\n";
				$propositions = \array_merge($propositions, $hiddenProposals);
				do {
					$answer = self::readline();
				} while ($continue($answer, $propositions));
			}
		} else {
			$answer = self::readline();
		}
		if (isset($options['default']) && $answer == '') {
			$answer = $options['default'];
		}
		return $answer;
	}

	public static function yesNoQuestion($prompt, array $propositions = [
		'yes',
		'no'
	], array $options = []) {
		return self::question($prompt, $propositions, [
			'ignoreCase' => true,
			'hiddenProposals' => [
				'y',
				'n'
			]
		]);
	}

	public static function explodeResponse(string $response, $callback = 'trim', string $separator = ',') {
		return \array_map($callback, \array_filter(\explode($separator, \trim($response)), 'strlen'));
	}

	/**
	 * Returns true if the answer is yes or y.
	 *
	 * @param string $answer
	 * @return boolean
	 */
	public static function isYes($answer) {
		return \array_search(\trim($answer), [
			'yes',
			'y'
		]) !== false;
	}

	/**
	 * Returns true if the answer is no or n.
	 *
	 * @param string $answer
	 * @return boolean
	 */
	public static function isNo($answer) {
		return \array_search(\trim($answer), [
			'no',
			'n'
		]) !== false;
	}

	/**
	 * Returns true if the answer is cancel or z.
	 *
	 * @param string $answer
	 * @return boolean
	 */
	public static function isCancel($answer) {
		return \array_search(\trim($answer), [
			'cancel',
			'z'
		]) !== false;
	}

	/**
	 * ReExecutes the loaded script.
	 * @return void
	 */
	public static function reExecute(): void {
		global $argv;
		$argv[0]=\realpath($argv[0]);
		\system(PHP_BINARY.' '.\implode(' ',$argv));
		die();
	}
}
