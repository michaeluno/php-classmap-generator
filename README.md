# PHP Class Map Generator
A PHP class that generates class maps for autoload.

## Usage
Instantiate the class with options.

```php
new \PHPClassMapGenerator\PHPClassMapGenerator(
    __DIR__,                        // base dir
    __DIR__ . '/_scandir',          // scan dir path
    __DIR__ . '/class-map.php',     // the result output file
);
```

This creates a class map file looking like a following.
```php
<?php 
$aClassMap = array( 
    "Foo\FooClass" => CLASS_MAP_BASE_DIR_VAR . "/_scandir/FooClass.php", 
    "Foo\FooClass_Base" => CLASS_MAP_BASE_DIR_VAR . "/_scandir/FooClass_Base.php", 
    "Joe\JoeInterface" => CLASS_MAP_BASE_DIR_VAR . "/_scandir/interfaces/JoeInterface.php", 
    "Bar\Bar" => CLASS_MAP_BASE_DIR_VAR . "/_scandir/traits/BarTrait.php", 
);
```

Before include the map file, define the constant `CLASS_MAP_BASE_DIR_VAR`. 

```php
    define( 'CLASS_MAP_BASE_DIR_VAR', __DIR__ )
``` 
or whatever the base directory path should be.

This automatically inserted string `CLASS_MAP_BASE_DIR_VAR` can be changed to whatever string you need with the option argument `base_dir_var`. For more details, see the 4th parameter section. 

Interfaces and traits are also included.

### Parameters
The class accepts four parameters.

#### 1. (string) Base Directory Path
The first parameter accepts the base directory path. 

This is required because scanned and gathered absolute paths are on the system where the script runs. However, the actual users of your programs do not share the same absolute paths depending on their systems. That's why the base directory path will be replaced with a constant or a variable in the output.    

#### 2. (string|array) Scan Directory Paths
The second parameter accepts directory paths to scan. For multiple paths, pass an numerically indexed array holding them.

##### Examples
```php
new \PHPClassMapGenerator\PHPClassMapGenerator(
    __DIR__,                        
    __DIR__ . '/scandir',         
    __DIR__ . '/class-map.php',     
);
```

```php
new \PHPClassMapGenerator\PHPClassMapGenerator(
    __DIR__,                        
    [ __DIR__ . '/scandir', __DIR__ . '/scandir2' ],         
    __DIR__ . '/class-map.php',     
);
```

#### 3. (string) The output PHP file path.
Set a path that the generated list will be written.

#### 4. Options (optional)
This parameter accepts an array holding options.

 - `output_buffer`		: (boolean)	whether output buffer should be printed.     
 - `exclude_classes` 	: (array)   an array holding class names to exclude.
 - `base_dir_var`		: (string)	the variable or constant name that is prefixed before the inclusion path.
 - `output_var_name`	: (string)  The variable string that the map array is assigned to. Default: `$aClassMap`. If `return` is set, the variable will not be set but the file just returns the generated map array. 
 - `search`				: (array)	the arguments for the directory search options.
    - `allowed_extensions`: (array) allowed file extensions to be listed. 
    - `exclude_dir_paths`: (array) directory paths to exclude from the list. 
    - `exclude_dir_names`: (array) directory base names to exclude from the list. 
    - `exclude_file_names`: (array) file names (with extension) to exclude from the list. 
    - `is_recursive`: (boolean) whether to scan sub-directories. 

##### Example    
```php
new \PHPClassMapGenerator\PHPClassMapGenerator(
    dirname( __DIR__ ),
    [ __DIR__ . '/includes', ],
    __DIR__ . '/class-map.php', 
    [       
        'output_buffer'      => true,
        'exclude_classes'    => [ 'TestClass' ],        
        'output_var_name'   => '$classMap',
        'base_dir_var'      => '\MyProject\Registry::$dirPath',
        'search'            => [
            'allowed_extensions'    => [ 'php' ],
            'exclude_dir_paths'     => [ __DIR__ . '/includes/class/admin' ],
            'exclude_dir_names'     => [ '_del', '_bak' ],
            'exclude_file_names'    => [ 'test.php', 'uninsall.php' ],
            'is_recursive'          => true,
        ],
    ]
);
``` 
 
 ## License
 Licensed under [MIT](./LICENSE).