<?php

namespace Craft;

class Schematic_TagsService extends BaseApplicationComponent
{
    public function import($tags)
    {
        return new Schematic_ResultModel();
    }

    public function export()
    {
        //
    }
}
