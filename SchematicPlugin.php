<?php

namespace Craft;

class SchematicPlugin extends BasePlugin
{
    public function getName()
    {
        return Craft::t('Schematic');
    }

    public function getVersion()
    {
        return '1.0.0';
    }

    public function getDeveloper()
    {
        return 'Itmundi';
    }

    public function getDeveloperUrl()
    {
        return 'http://www.itmundi.nl';
    }
}
