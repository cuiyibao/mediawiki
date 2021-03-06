<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

namespace LocalisationUpdate;

class UpdaterTest extends \PHPUnit_Framework_TestCase {
	public function testIsDirectory() {
		$updater = new Updater();

		$this->assertTrue(
			$updater->isDirectory( '/IP/extensions/Translate/i18n/*.json' ),
			'Extension json files are a file pattern'
		);

		$this->assertFalse(
			$updater->isDirectory( '/IP/extensions/Translate/Translate.i18n.php' ),
			'Extension php file is not a pattern'
		);
	}

	public function testExpandRemotePath() {
		$updater = new Updater();
		$repos = array( 'main' => 'file:///repos/%NAME%/%SOME-VAR%' );

		$info = array(
			'repo' => 'main',
			'name' => 'product',
			'some-var' => 'file',
		);
		$this->assertEquals(
			'file:///repos/product/file',
			$updater->expandRemotePath( $info, $repos ),
			'Variables are expanded correctly'
		);
	}

	public function testReadMessages() {
		$updater = $updater = new Updater();

		$input = array( 'file' => 'Hello World!' );
		$output = array( 'en' => array( 'key' => $input['file'] ) );

		$reader = $this->getMock( 'LocalisationUpdate\Reader' );
		$reader
			->expects( $this->once() )
			->method( 'parse' )
			->will( $this->returnValue( $output ) );

		$factory = $this->getMock( 'LocalisationUpdate\ReaderFactory' );
		$factory
			->expects( $this->once() )
			->method( 'getReader' )
			->will( $this->returnValue( $reader ) );

		$observed = $updater->readMessages( $factory, $input );
		$this->assertEquals( $output, $observed, 'Tries to parse given file' );
	}

	public function testFindChangedTranslations() {
		$updater = $updater = new Updater();

		$origin = array(
			'A' => '1',
			'C' => '3',
			'D' => '4',
		);
		$remote = array(
			'A' => '1', // No change key
			'B' => '2', // New key
			'C' => '33', // Changed key
			'D' => '44', // Blacklisted key
		);
		$blacklist = array( 'D' => 0 );
		$expected = array( 'B' => '2', 'C' => '33' );
		$observed = $updater->findChangedTranslations( $origin, $remote, $blacklist );
		$this->assertEquals( $expected, $observed, 'Changed and new keys returned' );
	}
}
