<?php

namespace PHPClassMapGenerator\Header;

use PHPClassMapGenerator\Utility\traitCodeParser;

class HeaderGenerator {

    use traitCodeParser;

    public $aItems   = [];
    public $aOptions = [
        'text'               => null,   // the direct comment content text
        'class'              => null,   // the class name tha has the header comment
        'type'               => null,   // the type of header comment to extract, accepts `DOCBLOCK`, `CONSTANTS`, `COMMENT`
        'path'               => null,   // the file path where the header comment to extract from
        'wrap'               => true,   // whether to enclose the comment in the PHP multi-line comment syntax /* */.

        // legacy options
        'header_class_name'  => null,
        'header_class_path'  => null,
        'header_type'        => null,
    ];

    /**
     * HeaderGenerator constructor.
     * @param array $aItems
     * @param array $aOptions
     */
    public function __construct( array $aItems, array $aOptions ) {
        $this->aItems   = $aItems;
        $this->aOptions = $this->___getOptionsFormatted( $aOptions );
    }
        private function ___getOptionsFormatted( array $aOptions ) {
            $_aOptions = $aOptions + $this->aOptions;
            // Backward-compatibility
            $_aLegacyArgumentsToConvert = [
                'header_class_path' => 'path',
                'header_class_name' => 'class',
                'header_type'       => 'type',
            ];
            foreach( $_aLegacyArgumentsToConvert as $_sLegacyKey => $_sCurrentVersionKey ) {
                if ( ! empty( $_aOptions[ $_sLegacyKey ] ) && empty( $_aOptions[ $_sCurrentVersionKey ] ) ) {
                    $_aOptions[ $_sCurrentVersionKey ] = $_aOptions[ $_sLegacyKey ];
                }
            }
            return $_aOptions;
        }

    /**
     *
     * @return string
     * @throws \ReflectionException
     */
    public function get() {

        if ( ! empty( $this->aOptions[ 'text' ] ) ) {
            return $this->aOptions[ 'wrap' ]
                ? $this->getMultilineCommentWrapped( $this->aOptions[ 'text' ] )
                : $this->aOptions[ 'text' ];
        }

        $_sMultilineComment = $this->___getProjectHeaderComment( $this->aItems, $this->aOptions );
        return $this->aOptions[ 'wrap' ] ? $_sMultilineComment : $this->getMultiLineCommentUnwrapped( $_sMultilineComment );

    }

        /**
         * Generates the heading comment from the given path or class name.
         * @param array $aItems
         * @param array $aOptions
         * @throws \ReflectionException
         * @return string
         */
        private function ___getProjectHeaderComment( array $aItems, array $aOptions )     {

            // A pass and a class name is given
            if ( ! empty( $aOptions[ 'path' ] ) && ! empty( $aOptions[ 'class' ] ) ) {
                return $this->___getProjectHeaderCommentGenerated($aOptions[ 'path' ], $aOptions[ 'class' ], $aOptions[ 'type' ]);
            }
            // A class name is given
            if ( ! empty( $aOptions[ 'class' ] ) ) {
                return $this->___getProjectHeaderCommentGenerated(
                    isset( $aItems[ $aOptions[ 'class' ] ] )
                        ? $aItems[ $aOptions[ 'class' ] ][ 'path' ]
                        : $aOptions[ 'path' ],
                    $aOptions[ 'class' ],
                    $aOptions[ 'type' ]
                );
            }

            if ( empty( $aOptions[ 'path' ] ) ) {
                return '';
            }

            // A pass is given.

            /// Get first found comment block from a file.
            $_sCommentBlock      = $this->getFirstFoundMultilineComment( file_get_contents( $aOptions[ 'path' ] ) );
            if ( ! empty( $_sCommentBlock ) ) {
                return $_sCommentBlock;
            }

            /// Extract a comment block from a fist found class doc-block.
            $_aConstructs        = $this->_getDefinedObjectConstructs( '<?php ' . $this->_getPHPCode( $aOptions[ 'path' ] ) );
            $_aDefinedClasses    = $_aConstructs[ 'classes' ];
            $_sHeaderClassName   = isset( $_aDefinedClasses[ 0 ] ) ? $_aDefinedClasses[ 0 ] : '';
            return $this->___getProjectHeaderCommentGenerated(
                $aOptions[ 'path' ],
                $_sHeaderClassName,
                $aOptions[ 'type' ]
            );

        }
            /**
             * Generates the script heading comment.
             * @throws \ReflectionException
             * @param string $sFilePath
             * @param string $sClassName
             * @param string $sHeaderType
             * @return string
             */
            private function ___getProjectHeaderCommentGenerated( $sFilePath, $sClassName, $sHeaderType='DOCKBLOCK' ) {

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
                        : $this->getMultilineCommentWrapped( $this->___generateHeaderComment( $_sClassName ) );
                }
                return '';

            }

            /**
             * Generates the heading comments from the class constants.
             * @throws \ReflectionException
             * @param string $sClassName
             * @return string
             */
            private function ___generateHeaderComment( $sClassName ) {

                $_oRC           = new \ReflectionClass( $sClassName );
                $_aConstants    = $_oRC->getConstants();
                $_aConstants    = array_change_key_case( $_aConstants, CASE_UPPER ) + [
                    'NAME'          => '',  'VERSION'       => '', 'DESCRIPTION'   => '',
                    'URI'           => '',  'AUTHOR'        => '', 'AUTHOR_URI'    => '',
                    'COPYRIGHT'     => '',  'LICENSE'       => '', 'CONTRIBUTORS'  => '',
                    ];
                $_aOutputs      = [];
                $_aOutputs[]    = ( $_aConstants[ 'NAME' ] ? $_aConstants[ 'NAME' ] . ' ' : '' )
                    . ( $_aConstants[ 'VERSION' ]   ? 'v' . $_aConstants[ 'VERSION' ] . ' '  : '' )
                    . ( $_aConstants[ 'AUTHOR' ]    ? 'by ' . $_aConstants[ 'AUTHOR' ] . ' ' : ''  );
                $_aOutputs[]    = $_aConstants[ 'DESCRIPTION' ]   ? $_aConstants[ 'DESCRIPTION' ] : '';
                $_aOutputs[]    = $_aConstants[ 'URI' ]           ? '<' . $_aConstants[ 'URI' ] . '>' : '';
                $_aOutputs[]    = ( $_aConstants[ 'COPYRIGHT' ]   ? $_aConstants[ 'COPYRIGHT' ] : '' )
                    . ( $_aConstants[ 'LICENSE' ]    ? '; Licensed under ' . $_aConstants[ 'LICENSE' ] : '' );
                return implode( PHP_EOL, array_filter( $_aOutputs ) );

            }

}