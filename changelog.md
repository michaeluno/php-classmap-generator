# Change Log

## 1.3.0 - 2022/02/22
- Added the ability to specify a comment header of a specified file.

## 1.2.2 - 2022/02/17
- Fixed a bug that caused an undefined index notice when the `header_class_name` argument was set.

## 1.2.1 - 2020/01/19
- Fixed a bug that class aliases were not parsed properly.

## 1.2.0 - 2020/01/19
- Added the ability to include class aliases.
- Added the `short_array_syntax` argument.

## 1.1.1 - 2020/11/06
- Fixed a redundant white spaces and lines in outputs.
- Fixed a bug that listed directories with a name with an extension that is specified with the search option when only files should be listed.   

## 1.1.0 - 2020/09/14
- Added the `exclude_substtings` search argument.
- Added the `structure` argument.
- Added the `ignore_note_file_names` search argument.
- Added the `do_in_constructor` argument.
- Added some public methods.
- Changed the `exclude_file_names` search option to be a substring of a base name instead of the whole base name without file extension. 
- Supported no namespace classes.
- Fixed a bug that class names starting with t could not properly load due to escape characters.

## 1.0.1 - 2020/01/20
- Fixed a composer configuration error with a namespace.

## 1.0.0 - 2020/01/19
- Released.
