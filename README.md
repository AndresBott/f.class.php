
#Fclass
Static class with utility methods without dependencies

#License:
licensed under LGPL

#Methods:
### String methods

    stringDeleteFromLeft($string,$amount);

return the ``$string``  without ``$amount`` of letters from the beginning

    stringDeleteFromRight($string,$amount);

return the ``$string``  without ``$amount`` of letters from the end

    stringGetFromLeft($string,$amount);

return ``$amount`` of letters from the beginning of the``$string``

    stringGetFromRight($string,$amount);

return the string ``$amount`` of letters from the beginning of the ``$string``

### Array methods
    removeEmptyArray($array);
will remove array keys if the value is empty so you don't get array items without values

### Files methods
    sanitizePath($path)
Uses a regexp to replace any combination of multiple slashes or dot-slash into only one slash i.e ``/public_html//dir///../dir2/dub.file`` will get  ``/public_html/dir2/dub.file``

     writeIniFile($path,$assoc_arr=false,$has_sections=FALSE)
Write an Array ``$assoc_arr`` into a .ini file on path:  ``$path`` if  ``$has_sections is true`` it will generate the corresponding sections.
the intention is to use it with php function ``parse_ini_file()``

     is_image($file)
check if ``$file`` is an image based on file extension, currently it will return true for jpg, jpeg, and png

... to be continue, check source for more methods