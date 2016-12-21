<?php

namespace Egg\Yolk;

class AutoLoader
{
    const DELIMITER_NAMESPACE = '\\';
    const DELIMITER_UNDERSCORE = '_';

    public static function register($directory, $delimiter = self::DELIMITER_NAMESPACE, $extension = 'php')
    {
        $autoLoader = new AutoLoader();
        $autoLoader->directory = $directory;
        $autoLoader->delimiter = $delimiter;
        $autoLoader->extension = $extension;

        spl_autoload_register(array($autoLoader, 'load'));

        return $autoLoader;
    }

	public function load($className)
	{
        $parts = explode($this->delimiter, $className);
        $fileName = $this->directory . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.' . $this->extension;
        if (file_exists($fileName))
        {
            include_once($fileName);
            return true;
        }

		return false;
	}
}