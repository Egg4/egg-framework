<?php

namespace Egg\Orm\Schema;

class File extends AbstractSchema
{
    protected $data;

    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'filename'          => '',
            'parser'            => null,
        ], $settings));

        $data = $this->settings['parser']->parse(file_get_contents($this->settings['filename']));
        $this->data = array_merge([
            'tables'        => [],
            'columns'       => [],
            'foreign_keys'  => [],
            'unique_keys'   => [],
        ], $data);
    }

    protected function getName()
    {
        return $this->data['name'];
    }

    protected function getTables()
    {
        return $this->data['tables'];
    }

    protected function getColumns()
    {
        return $this->data['columns'];
    }

    protected function getForeignKeys()
    {
        return $this->data['foreign_keys'];
    }

    protected function getUniqueKeys()
    {
        return $this->data['unique_keys'];
    }
}