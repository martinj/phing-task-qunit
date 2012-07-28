# QUnit Phing Task

This project is a [Phing](https://github.com/phingofficial/phing) build tool task for running [QUnit](https://github.com/jquery/qunit) tests headless with [PhantomJS](https://github.com/ariya/phantomjs)

## Requirements

This project has been tested with

 - [PhantomJS](https://github.com/ariya/phantomjs) 1.6.0
 - [QUnit](https://github.com/jquery/qunit) 1.3

## Example

To use this task, add the classpath where you placed the QunitTask.php in your build.xml file:

	<path id="project.class.path">
		<pathelement dir="dir/to/qunittaskfile/"/>
	</path>

Then include it with a taskdef tag in your build.xml file:

	<taskdef name="qunit" classname="QunitTask">
		<classpath refid="project.class.path"/>
	</taskdef>


You can now use the task

	<target name="qunit" description="Javascript Unit Test">
		<qunit executable="DISPLAY=:0 /path/to/phantomjs" haltonfailure="true" runner="${basedir}/tests/run-qunit.js">
			<fileset dir="${basedir}">
				<include name="tests/runner.html" />
			</fileset>
		</qunit>
	</target>

## Task Attributes

#### Required
_There are no required attributes._

#### Optional
 - **runner** - Specifies the qunit runner, look at [run-qunit-example.js](https://github.com/martinj/phing-task-qunit/blob/master/run-qunit-example.js)
 - **executable** - Path to phantomjs command.
 - **haltonfailure** - If the build should fail if any test fails.

