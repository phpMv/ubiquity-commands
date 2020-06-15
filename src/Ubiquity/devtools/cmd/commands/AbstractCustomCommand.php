<?php
namespace Ubiquity\devtools\cmd\commands;

use Ubiquity\devtools\cmd\Command;

/**
 * Ubiquity\devtools\cmd$AbstractCustomCommand
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 * @package ubiquity.devtools
 *
 */
abstract class AbstractCustomCommand extends AbstractCmd {

	/**
	 *
	 * @var Command
	 */
	private $command;

	public function __construct() {
		$this->command = new Command($this->getName(), $this->getValue(), $this->getDescription(), $this->getAliases(), $this->getParameters(), $this->getExamples());
	}

	/**
	 * Return the command name.
	 *
	 * @return string
	 */
	abstract protected function getName(): string;

	/**
	 * Return the command parameter name.
	 *
	 * @return string
	 */
	abstract protected function getValue(): string;

	/**
	 * Return the command description.
	 *
	 * @return string
	 */
	abstract protected function getDescription(): string;

	/**
	 *
	 * @return array
	 */
	abstract protected function getAliases(): array;

	/**
	 * Return the list of parameters.
	 * Sample: ['d'=>Parameter::create('database', 'The database connection to use', [],'default')].
	 *
	 * @return array
	 */
	abstract protected function getParameters(): array;

	/**
	 * Return a list of examples.
	 * Sample: ['Clear all caches'=>'Ubiquity clear-cache -t=all','Clear models cache'=>'Ubiquity clear-cache -t=models']
	 *
	 * @return array
	 */
	abstract protected function getExamples(): array;

	/**
	 * Return the command informations.
	 *
	 * @return \Ubiquity\devtools\cmd\Command
	 */
	public function getCommand() {
		return $this->command;
	}

	abstract public function run(&$config, $options, $what, ...$otherArgs);
}

