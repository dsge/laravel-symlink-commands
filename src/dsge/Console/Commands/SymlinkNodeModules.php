<?php namespace dsge\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

class SymlinkNodeModules extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'symlink:node_modules';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Symlinks the node_modules folder to ~/project_node_modules/{project_folder_name}.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get base folder's name
	 * @return string base folder name, e.g. "myproject" for "/var/www/myproject"
	 */
	protected function getBaseFolderName(){
		$process = new Process('printf \'%s\n\' "${PWD##*/}"');
		$process->run();
		if (!$process->isSuccessful()) {
			throw new \RuntimeException($process->getErrorOutput());
		}
		$path = trim($process->getOutput(),"\n");
		return $path;
	}

	protected function removeNodeModulesFolder($folder){
		$process = new Process('rm -r '.$folder.' -f');
		$process->run();
		if (!$process->isSuccessful()) {
			throw new \RuntimeException($process->getErrorOutput());
		}
		return true;
	}

	protected function symlink($source, $target){
		if (!file_exists($source)) mkdir($source, 0755, true);
		$process = new Process('ln -s '.$source.' '.$target);
		$process->run();
		if (!$process->isSuccessful()) {
			throw new \RuntimeException($process->getErrorOutput());
		}
		return true;
	}

	protected function getSourceFolderName(){
		return getenv("HOME")."/project_node_modules/".$this->getBaseFolderName();
	}

	protected function getTargetFolderName(){
		return base_path()."/node_modules";
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$target = $this->getTargetFolderName();
		if (file_exists($target)){
			if (!$this->confirm('node_modules folder already exists. Continue? [Yes|no]', true)){
				$this->info('Aborted');
				return;
			}
			$this->removeNodeModulesFolder($target);
		}
		$source = $this->getSourceFolderName();
		
		$this->symlink($source,$target);
		$this->info("Symlink created (".$source." -> ".$target.")");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			//['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			//['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
