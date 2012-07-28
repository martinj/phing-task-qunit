<?php  
/*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
* A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
* OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
* SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
* LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
* THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
* OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
require_once 'phing/Task.php';
require_once 'phing/util/DataStore.php';
/**
* Qunit Task, for running qunit <https://github.com/jquery/qunit> headless with phantomjs <https://github.com/ariya/phantomjs>
*
* This class is based on Stefan Priebsch JslintTask
* 
* @author Martin Jonsson <martin.jonsson@gmail.com>
*/
class QunitTask extends Task {
	private $file = null;
	private $runner = null;
	private $haltOnFailure = true;    
	private $hasErrors = false;
	private $filesets = array();
    private $executable = 'phantomjs';

	public function setFile(PhingFile $file) {
		$this->file = $file;
	}

	public function setExecutable($executable) {
		$this->executable = $executable;
	}
	
	public function setRunner(PhingFile $runner) {
		$this->runner = $runner;
	}
		
	public function setHaltOnFailure($haltOnFailure) {
		$this->haltOnFailure = $haltOnFailure;
	}
    
	/**
	* Create fileset for this task
	*/
	public function createFileSet() {
		$num = array_push($this->filesets, new FileSet());
		return $this->filesets[$num-1];
	}
  
	public function main() {
		$this->hasErrors = false;
		
		if(!isset($this->file) and count($this->filesets) == 0) {
			throw new BuildException("Missing either a nested fileset or attribute 'file' set");
		}
		exec($this->executable . '  --version', $output);
		if (!preg_match('/\d+\.\d+\.\d+/', implode('', $output))) {
			throw new BuildException('phantomjs command not found');
		}
    
		if($this->file instanceof PhingFile) {
			$this->run($this->file->getPath());
		} else { // process filesets
			$project = $this->getProject();
		
			foreach($this->filesets as $fs) {
				$ds = $fs->getDirectoryScanner($project);
				$files = $ds->getIncludedFiles();
				$dir = $fs->getDir($this->project)->getPath();
				
				foreach($files as $file) {
					$this->run($dir.DIRECTORY_SEPARATOR.$file);
				}
			}
		}
  
		if ($this->haltOnFailure && $this->hasErrors) throw new BuildException('QUnit tests failed');
    }
    
	public function run($file) {
		$command = $this->executable . ( $this->runner ? ' "' . $this->runner . '"' : '' ) . ' "' . $file . '"';

		if(!file_exists($file)) {
			throw new BuildException('File not found: ' . $file);
		}
		
		if(!is_readable($file))	{
			throw new BuildException('Permission denied: ' . $file);
		}

		$messages = array();
		exec($command, $messages);		
		$summary = $messages[sizeof($messages) - 1];
		
		$matches = array();
 		if (preg_match('/(\d+) tests of (\d+) passed, (\d+) failed/', $summary, $matches)) {
			if ($matches[3] > 0) {
				$this->hasErrors = true;
			   	$this->log($matches[3] . ' tests failed on: ' . $file, Project::MSG_ERR);
				$this->logFailedTests($messages);
			} else {
				$this->log($file . ': ' . $matches[0], Project::MSG_INFO);
			}
		}
	}

	private function logFailedTests($messages) {
		if (!is_array($messages)) {
			return;
		}
		
		foreach ($messages as $message) {
 			$matches = array();
			if (preg_match('/\((\d+), (\d+), (\d+)\)/', $message, $matches)) {
				if ($matches[1] > 0) {
					$this->log('Failed test: ' . $message, Project::MSG_ERR);
				}

			}
		}
	}
}