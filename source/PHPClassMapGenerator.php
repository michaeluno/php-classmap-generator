<?php
/**
 * Helps to generate class maps.
 * 
 * @author       Michael Uno <michael@michaeluno.jp>
 * @copyright    2020 (c) Michael Uno
 * @license      MIT <http://opensource.org/licenses/MIT>
 */

namespace PHPClassMapGenerator;

if ( ! class_exists( 'PHPClassMapGenerator_Base' ) ) {
    require_once( __DIR__ . '/PHPClassMapGenerator_Base.php' );
}

/**
 * Creates a PHP file that defines an array holding file path with the class key.
 *
 * This is meant to be used for the callback function for the spl_autoload_register() function.
 *
 * @remark		The parsed class file must have a name of the class defined in the file.
 * @version		1.0.1
 */
class PHPClassMapGenerator extends PHPClassMapGenerator_Base {

    static protected $_aStructure_Options = array(

        'header_class_name'		=> '',
        'header_class_path'		=> '',
        'output_buffer'			=> true,
        'header_type'			=> 'DOCBLOCK',
        'exclude_classes'		=> array(),
        'base_dir_var'			=> 'CLASS_MAP_BASE_DIR_VAR',
        'output_var_name'		=> '$aClassMap',

        // Search options
        'search'	=>	array(
            'allowed_extensions'	=>	array( 'php' ),	// e.g. array( 'php', 'inc' )
            'exclude_dir_paths'		=>	array(),
            'exclude_dir_names'		=>	array(),
            'is_recursive'			=>	true,
        ),

    );

    /**
     * @param		string			$sBaseDirPath			The base directory path that the inclusion path is relative to.
     * @param		string|array	$asScanDirPaths			The target directory path(s).
     * @param		string			$sOutputFilePath		The destination file path.
     * @param		array			$aOptions				The options array. It takes the following arguments.
     *  - 'header_class_name'	: string	the class name that provides the information for the heading comment of the result output of the minified script.
     *  - 'header_class_path'	: string	(optional) the path to the header class file.
     *  - 'output_buffer'		: boolean	whether or not output buffer should be printed.
     *  - 'exclude_classes' 	: array		an array holding class names to exclude.
     *  - 'base_dir_var'		: string	the variable or constant name that is prefixed before the inclusion path.
     *  - 'search'				: array		the arguments for the directory search options.
     *  - 'header_type'			: string	whether or not to use the docBlock of the header class; otherwise, it will parse the constants of the class. The accepted values are 'CONSTANTS' or 'DOCBLOCK'.
     * ### Example
     * ```
     * array(
     *		'header_class_name'	=>	'HeaderClassForMinifiedVerions',
     *		'output_buffer'	=>	false,
     *		'header_type'	=>	'CONSTANTS',
     *
     * )
     * ```
     *
     * When `CONSTANTS` is passed to the 'header_type' argument, the constants of the header class must include 'Version', 'Name', 'Description', 'URI', 'Author', 'CopyRight', 'License'.
     * ### Example
     * ```
     * class Registry {
     *     const VERSION        = '1.0.0b08';
     *     const NAME           = 'Task Scheduler';
     *     const DESCRIPTION    = 'Provides an enhanced task management system for WordPress.';
     *     const URI            = 'http://en.michaeluno.jp/';
     *     const AUTHOR         = 'miunosoft (Michael Uno)';
     *     const AUTHOR_URI     = 'http://en.michaeluno.jp/';
     *     const COPYRIGHT      = 'Copyright (c) 2014, <Michael Uno>';
     *     const LICENSE        = 'GPL v2 or later';
     *     const CONTRIBUTORS   = '';
     * }
     * ```
     */
    public function __construct( $sBaseDirPath, $asScanDirPaths, $sOutputFilePath, array $aOptions=array() ) {

        parent::__construct();

        $aOptions			= $aOptions + self::$_aStructure_Options;
        $aOptions[ 'search' ]	= $aOptions[ 'search' ] + self::$_aStructure_Options[ 'search' ];

        $_sCarriageReturn	= php_sapi_name() == 'cli' ? PHP_EOL : '<br />';
        $_aScanDirPaths		= ( array ) $asScanDirPaths;
        if ( $aOptions[ 'output_buffer' ] ) {
            echo 'Searching files under the directories: ' . implode( ', ', $_aScanDirPaths ) . $_sCarriageReturn;
        }

        // 1. Store file contents into an array.
        $_aFilePaths	= $this->_getFileLists( $_aScanDirPaths, $aOptions['search'] );
        $_aFiles		= $this->_formatFileArray( $_aFilePaths );
        unset( $_aFiles[ pathinfo( $sOutputFilePath, PATHINFO_FILENAME ) ] );	// it's possible that the minified file also gets loaded but we don't want it.
        if ( $aOptions[ 'output_buffer' ] ) {
            echo sprintf( 'Found %1$s file(s)', count( $_aFiles ) ) . $_sCarriageReturn;
        }

        // 2. Generate the output script header comment
        $_sHeaderComment = $this->_getHeaderComment( $_aFiles, $aOptions );
        if ( $aOptions[ 'output_buffer' ] ) {
            echo( $_sHeaderComment ) . $_sCarriageReturn;
        }

        // 3. Sort the classes - in some PHP versions, parent classes must be defined before extended classes.
        $_aFiles = $this->___sort( $_aFiles, $aOptions[ 'exclude_classes' ] );
        if ( $aOptions[ 'output_buffer' ] ) {
            echo sprintf( 'Sorted %1$s file(s)', count( $_aFiles ) ) . $_sCarriageReturn;
        }

        // 4. Write to a file
        $this->___write(
            $_aFiles,
            $sBaseDirPath,
            $sOutputFilePath,
            $_sHeaderComment,
            $aOptions[ 'output_var_name' ],
            $aOptions[ 'base_dir_var' ]
        );

    }

    private function ___sort( array $aFiles, array $aExcludingClassNames ) {

        $aFiles = $this->___getDefinedObjectConstructsExtracted( $aFiles, $aExcludingClassNames );
        foreach( $aFiles as $_sClassName => $_aFile ) {
            if ( in_array( $_sClassName, $aExcludingClassNames ) ) {
                unset( $aFiles[ $_sClassName ] );
            }
        }
        return $aFiles;

    }
    private function ___getDefinedObjectConstructsExtracted( array $aFiles, array $aExcludingClassNames ) {

        $_aAdditionalClasses = array();
        foreach( $aFiles as $_sClassName => $_aFile ) {
            $_aObjectConstructs = array_merge( $_aFile[ 'classes' ], $_aFile[ 'traits' ], $_aFile[ 'interfaces' ] );
            foreach( $_aObjectConstructs as $_sAdditionalClass ) {
                if ( in_array( $_sAdditionalClass, $aExcludingClassNames ) ) {
                    continue;
                }
                $_aAdditionalClasses[ $_sAdditionalClass ] = $_aFile;
            }
        }
        return $_aAdditionalClasses;

    }

    private function ___write( array $aFiles, $sBaseDirPath, $sOutputFilePath, $sHeadingComment, $sOutputArrayVar, $sBaseDirVar ) {

        $_aData		 = array();

        // Create a heading.
        $_aData[]	 = mb_convert_encoding( '<?php ' . PHP_EOL . $sHeadingComment, 'UTF-8', 'auto' );

        // Start array declaration
        $_aData[]	 = 'return' === $sOutputArrayVar
            ? 'return array( ' . PHP_EOL
            : $sOutputArrayVar . ' = array( ' . PHP_EOL;

        // Insert the data
        $sBaseDirVar = $sBaseDirVar ? $sBaseDirVar : '""';
        foreach( $aFiles as $_sClassName => $_aFile ) {
            $_sPath		= str_replace( '\\', '/', $_aFile[ 'path' ] );
            $_sPath		= $this->___getRelativePath( $sBaseDirPath, $_sPath );
            $_aData[]	= "    " . '"' . $_sClassName . '"' . ' => '
                           . $sBaseDirVar . ' . "' . $_sPath . '", ' . PHP_EOL;
        }

        // Close the array declaration
        $_aData[]	= ');' . PHP_EOL;

        // Remove the existing file.
        if ( file_exists( $sOutputFilePath ) ) {
            unlink( $sOutputFilePath );
        }

        // Write to a file.
        file_put_contents(
            $sOutputFilePath,
            trim( implode( '', $_aData ) ) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

    }

    /**
     * Calculates the relative path from the given path.
     *
     */
    private function ___getRelativePath( $from, $to ) {

        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from     = explode('/', $from);
        $to       = explode('/', $to);
        $relPath  = $to;

        foreach($from as $depth => $dir) {
            // find first non-matching dir
            if($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }

        $relPath = implode( '/', $relPath );
        return ltrim( $relPath, '.' );
    }

}