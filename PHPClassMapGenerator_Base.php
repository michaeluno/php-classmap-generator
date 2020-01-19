<?php
/**
 * Helps to generate class maps.
 * 
 * @author       Michael Uno <michael@michaeluno.jp>
 * @copyright    2020 (c) Michael Uno
 * @license      MIT <http://opensource.org/licenses/MIT>
 */

namespace PHPClassMapGenerator;

/**
 * The base class of script creator.
 * 
 * @version        1.0.0
 */
class PHPClassMapGenerator_Base {

    public function __construct() {
        if ( ! function_exists( 'token_get_all' ) ) {
            echo 'The function token_get_all() is required.' . self::$_aStructure_Options[ 'carriage_return' ];
            exit;
        }
    }

    static protected $_aStructure_Options = array(
    
        'header_class_name' => '',
        'header_class_path' => '',        
        'output_buffer'     => true,
        'header_type'       => 'DOCBLOCK',    
        'exclude_classes'   => array(),
        
        // Search options
        'search'    =>    array(
            'allowed_extensions'    => array( 'php' ),    // e.g. array( 'php', 'inc' )
            'exclude_dir_paths'     => array(),
            'exclude_dir_names'     => array(),         // the directory 'base' name
            'exclude_file_names'    => array(), // 1.0.3+ includes an file extension.
            'is_recursive'          => true,
        ),        

        'carriage_return'   => PHP_EOL,
    );
    
    /**
     * Returns an array holding a list of file paths combined from multiple sources.
     */
    protected function _getFileLists( $asDirPaths, $aSearchOptions ) {
        $_aFiles    = array();
        $asDirPaths = is_array( $asDirPaths ) 
            ? $asDirPaths 
            : array( $asDirPaths );
        foreach( $asDirPaths as $_sDirPath ) {
            $_aFiles = array_merge( 
                $this->_getFileList( $_sDirPath, ( array ) $aSearchOptions + self::$_aStructure_Options['search'] ),
                $_aFiles 
            );
        }
        return array_unique( $_aFiles );
    }
    
        /**
         * Returns an array containing file paths.
         * 
         * @deprecated The directory iterator cannot filter out certain directories in some PHP versions.
         */
        private function ___getFileList( $sDirPath, $sFilePathRegexNeedle ) {
            
            $sDirPath = rtrim( $sDirPath, '\\/' );
            $_aFileList = array();
            if ( ! is_dir( $sDirPath ) ) {
                return $_aFileList;
            }
            $_oDir              = new \RecursiveDirectoryIterator( $sDirPath );
            $_oIterator         = new \RecursiveIteratorIterator( $_oDir );
            $_oRegexIterator    = new \RegexIterator( $_oIterator, $sFilePathRegexNeedle, \RegexIterator::GET_MATCH );
            foreach( $_oRegexIterator as $_aFile ) {
                $_aFileList = array_merge( $_aFileList, $_aFile );
            }
            return $_aFileList;
        }    
        /**
         * Returns an array of scanned file paths.
         * 
         * The returning array structure looks like this:
            array
              0 => string '.../class/MyClass.php'
              1 => string '.../class/MyClass2.php'
              2 => string '.../class/MyClass3.php'
              ...
         * 
         */
        protected function _getFileList( $sDirPath, array $aSearchOptions ) {
            
            $sDirPath            = rtrim( $sDirPath, '\\/' ) . DIRECTORY_SEPARATOR;    // ensures the trailing (back/)slash exists.         
            $_aExcludingDirPaths = $this->_formatPaths( $aSearchOptions['exclude_dir_paths'] );
            
            if ( defined( 'GLOB_BRACE' ) ) {    // in some OSes this flag constant is not available.
                $_sFileExtensionPattern = $this->___getGlobPatternExtensionPart( $aSearchOptions['allowed_extensions'] );
                $_aFilePaths = $aSearchOptions[ 'is_recursive' ]
                    ? $this->___doRecursiveGlob( 
                        $sDirPath . '*.' . $_sFileExtensionPattern, 
                        GLOB_BRACE, 
                        $_aExcludingDirPaths, 
                        ( array ) $aSearchOptions['exclude_dir_names'],
                        $aSearchOptions['exclude_file_names']
                    )
                    : ( array ) glob( $sDirPath . '*.' . $_sFileExtensionPattern, GLOB_BRACE );
                return array_filter( $_aFilePaths );    // drop non-value elements.    
            } 
                
            // For the Solaris operation system.
            $_aFilePaths = array();
            foreach( $aSearchOptions['allowed_extensions'] as $__sAllowedExtension ) {
                $__aFilePaths = $aSearchOptions[ 'is_recursive' ]
                    ? $this->___doRecursiveGlob( 
                        $sDirPath . '*.' . $__sAllowedExtension, 
                        0, 
                        $_aExcludingDirPaths, 
                        ( array ) $aSearchOptions['exclude_dir_names'],
                        $aSearchOptions['exclude_file_names']
                    )
                    : ( array ) glob( $sDirPath . '*.' . $__sAllowedExtension );
                $_aFilePaths = array_merge( $__aFilePaths, $_aFilePaths );
            }
            return array_unique( array_filter( $_aFilePaths ) );
            
        }
            /**
             * Formats the paths.
             * 
             * This is necessary to check excluding paths because the user may pass paths with a forward slash but the system may use backslashes.
             */
            protected function _formatPaths( $asDirPaths ) {
                
                $_aFormattedDirPaths = array();
                $_aDirPaths = is_array( $asDirPaths ) ? $asDirPaths : array( $asDirPaths );
                foreach( $_aDirPaths as $_sPath ) {
                    $_aFormattedDirPaths[] = $this->_getPathFormatted( $_sPath );
                }
                return $_aFormattedDirPaths;
                
            }
                /**
                 * @return      string
                 */
                protected function _getPathFormatted( $sPath ) {
                    return rtrim( str_replace( '\\', '/', $sPath ), '/' );
                }
            /**
             * The recursive version of the glob() function.
             */
            private function ___doRecursiveGlob( $sPathPatten, $nFlags=0, array $aExcludeDirPaths=array(), array $aExcludeDirNames=array(), array $aExcludeFileNames=array() ) {

                $_aFiles    = glob( $sPathPatten, $nFlags );    
                $_aFiles    = is_array( $_aFiles ) ? $_aFiles : array();    // glob() can return false.
                $_aFiles    = $this->___dropExcludingFiles( $_aFiles, $aExcludeFileNames );
                $_aDirs     = glob( 
                    dirname( $sPathPatten ) . DIRECTORY_SEPARATOR . '*', 
                    GLOB_ONLYDIR|GLOB_NOSORT
                );
                $_aDirs     = is_array( $_aDirs ) ? $_aDirs : array();
                foreach ( $_aDirs as $_sDirPath ) {
                    $_sDirPath        = $this->_getPathFormatted( $_sDirPath );
                    if ( in_array( $_sDirPath, $aExcludeDirPaths ) ) { 
                        continue; 
                    }
                    if ( in_array( pathinfo( $_sDirPath, PATHINFO_BASENAME ), $aExcludeDirNames ) ) {
                        continue; 
                    } 
                    $_aFiles    = array_merge( 
                        $_aFiles, 
                        $this->___doRecursiveGlob( 
                            $_sDirPath . DIRECTORY_SEPARATOR . basename( $sPathPatten ), 
                            $nFlags, 
                            $aExcludeDirPaths,
                            $aExcludeDirNames,
                            $aExcludeFileNames
                        )
                    );
                    
                }
                return $_aFiles;
                
            }        
                /**
                 * Removes files from the generated list that is set in the 'exclude_file_names' argument of the searh option array.
                 * @since       1.0.6
                 * @return      array
                 */
                private function ___dropExcludingFiles( array $aFiles, array $aExcludingFileNames=array() ) {
                    if ( empty( $aExcludingFileNames ) ) {
                        return $aFiles;
                    }
                    foreach( $aFiles as $_iIndex => $_sPath ) {
                        $_sFileNameWOExtension = pathinfo( $_sPath, PATHINFO_FILENAME );
                        if ( in_array( $_sFileNameWOExtension, $aExcludingFileNames ) ) {
                            unset( $aFiles[ $_iIndex ] );
                        }
                    }
                    return $aFiles;
                }
            /**
             * Constructs the file pattern of the file extension part used for the glob() function with the given file extensions.
             */
            private function ___getGlobPatternExtensionPart( array $aExtensions=array( 'php', 'inc' ) ) {
                return empty( $aExtensions ) 
                    ? '*'
                    : '{' . implode( ',', $aExtensions ) . '}';
            }

    /**
     * Sets up the array consisting of class paths with the key of file name w/o extension.
     */
    protected function _formatFileArray( array $_aFilePaths ) {
                    
        /*
         * Now the structure of $_aFilePaths looks like:
            array
              0 => string '.../class/MyClass.php'
              1 => string '.../class/MyClass2.php'
              2 => string '.../class/MyClass3.php'
              ...
         * 
         */         
        $_aFiles = array();
        foreach( $_aFilePaths as $_sFilePath ) {
            
            $_sClassName    = pathinfo( $_sFilePath, PATHINFO_FILENAME );
            $_sPHPCode      = $this->_getPHPCode( $_sFilePath );
            $_aFiles[ $_sClassName ] = array(    // the file name without extension will be assigned to the key
                'path'              => $_sFilePath,    
                'code'              => $_sPHPCode ? trim( $_sPHPCode ) : '',
                'dependency'        => $this->_getParentClass( $_sPHPCode ),
            ) + $this->_getDefinedObjectConstructs( '<?php ' . $_sPHPCode );

        }
        return $_aFiles;
            
    }
        /**
         * Retrieves PHP code from the given path.
         * 
         * @remark      Enclosing `<?php ?>` tags will be removed.
         */
        protected function _getPHPCode( $sFilePath ) {
            $_sCode = php_strip_whitespace( $sFilePath );
            $_sCode = preg_replace( '/^<\?php/', '', $_sCode );
            $_sCode = preg_replace( '/\?>\s+?$/', '', $_sCode );
            return $_sCode;
        }
                    

        /**
         * Retrieves defined PHP class names using the `token_get_all` function.
         * 
         * @remark      The passed code must start with the `<?php ` opening tag.
         * @return      array
         */
        protected function _getDefinedObjectConstructs( $sPHPCode ) {

            $_aConstructs       = array(
                'classes'    => array(),
                'interfaces' => array(),
                'traits'     => array(),
                'namespaces' => array(),
            );
            $_aTokens           = token_get_all( $sPHPCode );
            $_iCount            = count( $_aTokens );
            $_sCurrentNameSpace = '';
            for ( $i = 2; $i < $_iCount; $i++ ) {

                // Namespace
                if ( T_NAMESPACE === $_aTokens[ $i ][ 0 ] ) {
                    $_sCurrentNameSpace = $this->___getNamespaceExtractedFromTokens( $_aTokens, $i, $_iCount ) . '\\';
                    $_aConstructs[ 'namespaces' ][] = $_sCurrentNameSpace;
                }

                // Class
                $_sClassName = $this->___getObjectConstructNameExtractedFromToken( $_aTokens, $i, T_CLASS );
                if ( $_sClassName ) {
                    $_aConstructs[ 'classes' ][] = $_sCurrentNameSpace . $_sClassName;
                    if ( ! $_sCurrentNameSpace ) {
                        $_aConstructs[ 'classes' ][] = '\\' . $_sClassName; // global namespace; no heading backslash version is added also for backward-compatibility with PHP v5.2.x.
                    }
                }

                // Interface
                $_sInterface = $this->___getObjectConstructNameExtractedFromToken( $_aTokens, $i, T_INTERFACE );
                if ( $_sInterface ) {
                    $_aConstructs[ 'interfaces' ][] = $_sCurrentNameSpace . $_sInterface;
                    if ( ! $_sCurrentNameSpace ) {
                        $_aConstructs[ 'interfaces' ][] = '\\' . $_sInterface;
                    }
                }

                // Trait
                $_sInterface = $this->___getObjectConstructNameExtractedFromToken( $_aTokens, $i, T_TRAIT );
                if ( $_sInterface ) {
                    $_aConstructs[ 'traits' ][] = $_sCurrentNameSpace . $_sInterface;
                    if ( ! $_sCurrentNameSpace ) {
                        $_aConstructs[ 'traits' ][] = '\\' . $_sInterface;
                    }
                }
            }
            return $_aConstructs;
            
        }
            private function ___getObjectConstructNameExtractedFromToken( array $aTokens, $i, $iObjectConstruct ) {
                if ( $iObjectConstruct !== $aTokens[ $i - 2 ][ 0 ] ) {
                    return '';
                }
                if ( T_WHITESPACE !== $aTokens[ $i - 1 ][ 0 ] ) {
                    return '';
                }
                if ( T_STRING !== $aTokens[ $i ][ 0 ] ) {
                    return '';
                }
                return $aTokens[ $i ][ 1 ];;
            }

            private function ___getNamespaceExtractedFromTokens( array $aTokens, $i, $iCount ) {
                $_sNamespace = '';
                while ( ++$i < $iCount ) {
                    if ( $aTokens [ $i ] === ';') {
                        $_sNamespace = trim( $_sNamespace );
                        break;
                    }
                    $_sNamespace .= is_array( $aTokens[ $i ] )
                        ? $aTokens[ $i ][ 1 ]
                        : $aTokens[ $i ];
                }
                return $_sNamespace;
            }

        /**
         * Returns the parent class
         */
        protected function _getParentClass( $sPHPCode ) {
            if ( ! preg_match( '/class\s+(.+?)\s+extends\s+(.+?)\s+{/i', $sPHPCode, $aMatch ) ) {
                return null;    
            }
            return $aMatch[ 2 ];
        }            
            
    /**
     * Generates the heading comment from the given path or class name.
     */
    protected function _getHeaderComment( $aFiles, $aOptions )     {

        if ( $aOptions['header_class_path'] && $aOptions['header_class_name'] ) {
            return $this->___getHeaderComment( 
                $aOptions['header_class_path'],
                $aOptions['header_class_name'],
                $aOptions['header_type']
            );                
        }
        
        if ( $aOptions['header_class_name'] ) {
            return $this->___getHeaderComment( 
                isset( $aFiles[ $aOptions['header_class_name'] ] ) ? $aFiles[ $aOptions['header_class_name'] ][ 'path' ] : $aOptions['header_class_path'],
                $aOptions['header_class_name'],
                $aOptions['header_type']
            );            
        } 
        
        if ( $aOptions[ 'header_class_path' ] ) {
            $_aConstructs        = $this->_getDefinedObjectConstructs( '<?php ' . $this->_getPHPCode( $aOptions[ 'header_class_path' ] ) );
            $_aDefinedClasses    = $_aConstructs[ 'classes' ];
            $_sHeaderClassName   = isset( $_aDefinedClasses[ 0 ] ) ? $_aDefinedClasses[ 0 ] : '';
            return $this->___getHeaderComment( 
                $aOptions[ 'header_class_path' ],
                $_sHeaderClassName,
                $aOptions[ 'header_type' ]
            );            
        }    
    
    }    
        /**
         * Generates the script heading comment.
         */
        private function ___getHeaderComment( $sFilePath, $sClassName, $sHeaderType='DOCKBLOCK' ) {

            if ( ! file_exists( $sFilePath ) ) { 
                return ''; 
            }
            if ( ! $sClassName ) { 
                return ''; 
            }

            include_once( $sFilePath );
            $_aDeclaredClasses = ( array ) get_declared_classes();
            foreach( $_aDeclaredClasses as $_sClassName ) {
                if ( $sClassName !== $_sClassName ) { 
                    continue; 
                }
                return 'DOCBLOCK' === $sHeaderType
                    ? $this->_getClassDocBlock( $_sClassName )
                    : $this->_generateHeaderComment( $_sClassName );
            }
            return '';
        
        }
        /**
         * Generates the heading comments from the class constants.
         */
        protected function _generateHeaderComment( $sClassName ) {
            
            $_oRC           = new \ReflectionClass( $sClassName );
            $_aConstants    = $_oRC->getConstants();
            $_aConstants    = array_change_key_case( $_aConstants, CASE_UPPER ) + array(
                'NAME'          => '', 'VERSION'        => '',
                'DESCRIPTION'   => '', 'URI'            => '',
                'AUTHOR'        => '', 'AUTHOR_URI'      => '',
                'COPYRIGHT'     => '', 'LICENSE'        => '',
                'CONTRIBUTORS'  => '',
            );
            $_aOutputs      = array();
            $_aOutputs[]    = '/' . '**' . PHP_EOL;
            $_aOutputs[]    = "\t" . $_aConstants['NAME'] . ' '
                . ( $_aConstants['VERSION']   ? 'v' . $_aConstants['VERSION'] . ' '  : '' ) 
                . ( $_aConstants['AUTHOR']    ? 'by ' . $_aConstants['AUTHOR'] . ' ' : ''  )
                . PHP_EOL;
            $_aOutputs[]    = $_aConstants['DESCRIPTION']   ? "\t". $_aConstants['DESCRIPTION'] . PHP_EOL : '';
            $_aOutputs[]    = $_aConstants['URI']           ? "\t". '<' . $_aConstants['URI'] . '>' . PHP_EOL : '';
            $_aOutputs[]    = "\t" . $_aConstants['COPYRIGHT']
                . ( $_aConstants['LICENSE']    ? '; Licensed under ' . $_aConstants['LICENSE'] : '' );
            $_aOutputs[]    = ' */' . PHP_EOL;
            return implode( '', array_filter( $_aOutputs ) );
        }    
        /**
         * Returns the docblock of the specified class
         */
        protected function _getClassDocBlock( $sClassName ) {
            $_oRC = new \ReflectionClass( $sClassName );
            return trim( $_oRC->getDocComment() );
        }

    /**
     * Echoes the passed string.
     * 
     * @since       1.0.0
     * @return      void
     */
    protected function _output( $sText, array $aOptions ) {
        if ( ! $aOptions[ 'output_buffer' ] ) {
            return;
        }
        echo $sText . $aOptions[ 'carriage_return' ];
    }
    
    /**
     * @since       1.0.0
     */
    public function log( $sText ) {
        file_put_contents( 
            dirname( __FILE__ ) . '/output.log', 
            $sText . PHP_EOL,
            FILE_APPEND 
        );           
    }
        
}
